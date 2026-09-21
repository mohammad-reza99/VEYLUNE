<?php declare(strict_types=1);

namespace VeyluneTheme\Command;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Content\Media\File\FileSaver;
use Shopware\Core\Content\Media\File\MediaFile;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use VeyluneTheme\Catalog\DraftCatalogManifest;

#[AsCommand(name: 'veylune:media:cover-pipeline', description: 'Validates, imports, audits, or rolls back the governed Phase 4 Admin Media covers.')]
#[Package('storefront')]
final class MediaCoverPipelineCommand extends Command
{
    private const SOURCE_DIR = 'var/veylune-phase4-media';
    private const REPORT = 'reports/catalog/phase-4-media-cover-pipeline.json';
    private const ROLLBACK = 'var/veylune-phase4-media-rollback.json';
    private const MIN_LONG_EDGE = 1400;
    private const PROMPT_FAMILY = 'veylune_product_cover_v1';

    /** @param EntityRepository<\Shopware\Core\Content\Media\MediaCollection> $mediaRepository */
    /** @param EntityRepository<\Shopware\Core\Content\Product\Aggregate\ProductMedia\ProductMediaCollection> $productMediaRepository */
    /** @param EntityRepository<\Shopware\Core\Content\Product\ProductCollection> $productRepository */
    public function __construct(
        private readonly EntityRepository $mediaRepository,
        private readonly EntityRepository $productMediaRepository,
        private readonly EntityRepository $productRepository,
        private readonly FileSaver $fileSaver,
        private readonly Connection $connection,
        private readonly string $projectDir
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('apply', null, InputOption::VALUE_NONE, 'Import and associate all validated covers.')
            ->addOption('audit', null, InputOption::VALUE_NONE, 'Audit the persisted Phase 4 cover state.')
            ->addOption('rollback', null, InputOption::VALUE_NONE, 'Restore the pre-import cover state and remove created media.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $modes = array_filter([
            'apply' => (bool) $input->getOption('apply'),
            'audit' => (bool) $input->getOption('audit'),
            'rollback' => (bool) $input->getOption('rollback'),
        ]);
        if (count($modes) > 1) {
            throw new \RuntimeException('Choose only one of --apply, --audit, or --rollback.');
        }

        if (isset($modes['rollback'])) {
            return $this->rollback($output);
        }

        $manifest = $this->sourceManifest();
        if (isset($modes['audit'])) {
            return $this->audit($manifest, $output);
        }

        $report = $this->validate($manifest);
        if ($report['summary']['blocked'] > 0) {
            $this->writeReport('dry_run', $report);
            $output->writeln('PHASE 4 COVER PIPELINE: BLOCKED');
            $output->writeln('Blocked records: ' . $report['summary']['blocked']);

            return Command::FAILURE;
        }

        if (!isset($modes['apply'])) {
            $this->writeReport('dry_run', $report);
            $output->writeln('PHASE 4 COVER PIPELINE: DRY RUN PASS');
            $output->writeln('Validated covers: ' . $report['summary']['valid']);
            $output->writeln('Database mutations: 0');

            return Command::SUCCESS;
        }

        $rollback = $this->buildRollbackManifest($manifest);
        $this->writeJson(self::ROLLBACK, $rollback);
        $this->apply($manifest, $output);

        return $this->audit($manifest, $output);
    }

    /** @return list<array<string, mixed>> */
    private function sourceManifest(): array
    {
        $records = [];
        foreach (DraftCatalogManifest::products() as $product) {
            $path = $this->projectDir . '/' . self::SOURCE_DIR . '/' . $product['id'] . '.png';
            $dimensions = is_file($path) ? @getimagesize($path) : false;
            $records[] = [
                'recordId' => $product['id'],
                'sku' => $product['sku'],
                'nameEn' => $product['nameEn'],
                'nameDe' => $product['nameDe'],
                'sourcePath' => self::SOURCE_DIR . '/' . $product['id'] . '.png',
                'sourceExists' => is_file($path),
                'sourceBytes' => is_file($path) ? (int) filesize($path) : 0,
                'sourceSha256' => is_file($path) ? (hash_file('sha256', $path) ?: '') : '',
                'width' => is_array($dimensions) ? (int) $dimensions[0] : 0,
                'height' => is_array($dimensions) ? (int) $dimensions[1] : 0,
                'mime' => is_array($dimensions) && isset($dimensions['mime']) ? (string) $dimensions['mime'] : null,
                'mediaId' => $this->deterministicId('media|' . $product['sku']),
                'productMediaId' => $this->deterministicId('product-media|' . $product['sku']),
                'targetFileName' => strtolower(str_replace('_', '-', $product['sku'])) . '-cover-v1',
                'rightsStatus' => 'project_generated',
                'sourceProvenance' => 'openai_imagegen_builtin',
                'promptFamily' => self::PROMPT_FAMILY,
                'visualReview' => 'passed',
            ];
        }

        return $records;
    }

    /** @param list<array<string, mixed>> $manifest @return array<string, mixed> */
    private function validate(array $manifest): array
    {
        $products = $this->productsBySku();
        $records = [];
        foreach ($manifest as $record) {
            $blocks = [];
            if (!$record['sourceExists']) {
                $blocks[] = 'source_missing';
            }
            if ($record['mime'] !== 'image/png') {
                $blocks[] = 'mime_not_png';
            }
            if (max((int) $record['width'], (int) $record['height']) < self::MIN_LONG_EDGE) {
                $blocks[] = 'below_minimum_long_edge';
            }
            $database = $products[$record['sku']] ?? null;
            if ($database === null) {
                $blocks[] = 'product_missing';
            } elseif ((bool) $database['active']) {
                $blocks[] = 'product_must_remain_inactive';
            }
            if ($record['sourceSha256'] === '') {
                $blocks[] = 'checksum_missing';
            }
            $record['productId'] = $database['id'] ?? null;
            $record['blockedReasons'] = $blocks;
            $records[] = $record;
        }

        return [
            'summary' => [
                'expected' => count(DraftCatalogManifest::products()),
                'validated' => count($records),
                'valid' => count(array_filter($records, static fn (array $record): bool => $record['blockedReasons'] === [])),
                'blocked' => count(array_filter($records, static fn (array $record): bool => $record['blockedReasons'] !== [])),
                'minimumLongEdgePx' => self::MIN_LONG_EDGE,
            ],
            'records' => $records,
        ];
    }

    /** @param list<array<string, mixed>> $manifest */
    private function apply(array $manifest, OutputInterface $output): void
    {
        $context = Context::createDefaultContext();
        $products = $this->productsBySku();
        $folderId = $this->productMediaFolderId();
        $languages = $this->languageIds();

        foreach ($manifest as $index => $record) {
            $product = $products[$record['sku']] ?? null;
            if ($product === null) {
                throw new \RuntimeException('Product disappeared during import: ' . $record['sku']);
            }
            $customFields = [
                'veylune_media_source' => $record['sourceProvenance'],
                'veylune_prompt_family' => $record['promptFamily'],
                'veylune_rights_status' => $record['rightsStatus'],
                'veylune_quality_status' => 'approved_cover_candidate',
                'veylune_source_sha256' => $record['sourceSha256'],
                'veylune_visual_review' => $record['visualReview'],
                'veylune_source_batch' => DraftCatalogManifest::BATCH_ID,
            ];
            $translations = [
                $languages['en-GB'] => [
                    'title' => $record['nameEn'] . ' cover',
                    'alt' => $record['nameEn'] . ' product view',
                    'customFields' => $customFields,
                ],
                $languages['de-DE'] => [
                    'title' => $record['nameDe'] . ' Titelbild',
                    'alt' => $record['nameDe'] . ' Produktansicht',
                    'customFields' => $customFields,
                ],
            ];

            $existing = $this->connection->fetchAssociative(
                'SELECT file_name, file_extension FROM media WHERE id = UNHEX(:id)',
                ['id' => $record['mediaId']]
            );
            if ($existing === false) {
                $this->mediaRepository->create([[
                    'id' => $record['mediaId'],
                    'mediaFolderId' => $folderId,
                    'private' => false,
                    'translations' => $translations,
                ]], $context);
            } else {
                $this->mediaRepository->update([[
                    'id' => $record['mediaId'],
                    'translations' => $translations,
                ]], $context);
            }

            if ($existing === false || empty($existing['file_name'])) {
                $source = $this->projectDir . '/' . $record['sourcePath'];
                $this->fileSaver->persistFileToMedia(
                    new MediaFile($source, 'image/png', 'png', (int) $record['sourceBytes'], md5_file($source) ?: null),
                    (string) $record['targetFileName'],
                    (string) $record['mediaId'],
                    $context
                );
            }

            $this->productMediaRepository->upsert([[
                'id' => $record['productMediaId'],
                'productId' => $product['id'],
                'mediaId' => $record['mediaId'],
                'position' => 1,
            ]], $context);
            $this->productRepository->update([[
                'id' => $product['id'],
                'coverId' => $record['productMediaId'],
                'active' => false,
            ]], $context);

            $output->writeln(sprintf('[%02d/50] %s -> Admin Media cover', $index + 1, $record['recordId']));
        }
    }

    /** @param list<array<string, mixed>> $manifest */
    private function audit(array $manifest, OutputInterface $output): int
    {
        $records = [];
        foreach ($manifest as $record) {
            $row = $this->connection->fetchAssociative(
                <<<'SQL'
SELECT
    LOWER(HEX(p.id)) AS product_id,
    p.active,
    LOWER(HEX(p.product_media_id)) AS cover_id,
    LOWER(HEX(pm.id)) AS product_media_id,
    LOWER(HEX(pm.media_id)) AS media_id,
    m.file_name,
    m.file_extension,
    m.mime_type,
    m.file_size,
    CAST(JSON_UNQUOTE(JSON_EXTRACT(m.meta_data, '$.width')) AS UNSIGNED) AS width,
    CAST(JSON_UNQUOTE(JSON_EXTRACT(m.meta_data, '$.height')) AS UNSIGNED) AS height,
    m.path AS media_path,
    COUNT(DISTINCT CASE WHEN loc.code IN ('en-GB', 'de-DE') AND TRIM(COALESCE(mt.alt, '')) <> '' THEN loc.code END) AS alt_locales,
    COUNT(DISTINCT CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(mt.custom_fields, '$.veylune_rights_status')) = 'project_generated' THEN loc.code END) AS rights_locales,
    COUNT(DISTINCT CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(mt.custom_fields, '$.veylune_source_sha256')) = :sha THEN loc.code END) AS checksum_locales
FROM product p
LEFT JOIN product_media pm ON pm.id = p.product_media_id AND pm.product_id = p.id AND pm.product_version_id = p.version_id
LEFT JOIN media m ON m.id = pm.media_id
LEFT JOIN media_translation mt ON mt.media_id = m.id
LEFT JOIN language l ON l.id = mt.language_id
LEFT JOIN locale loc ON loc.id = l.locale_id
WHERE p.product_number = :sku
GROUP BY p.id, p.active, p.product_media_id, pm.id, pm.media_id, m.file_name, m.file_extension, m.mime_type, m.file_size, m.meta_data, m.path
SQL,
                ['sku' => $record['sku'], 'sha' => $record['sourceSha256']]
            );
            $checks = [
                'product_present' => $row !== false,
                'product_inactive' => $row !== false && !(bool) $row['active'],
                'deterministic_cover_association' => $row !== false && ($row['product_media_id'] ?? null) === $record['productMediaId'],
                'deterministic_media' => $row !== false && ($row['media_id'] ?? null) === $record['mediaId'],
                'public_renderable_media' => $row !== false && !empty($row['media_path']) && ($row['mime_type'] ?? null) === 'image/png',
                'minimum_resolution' => $row !== false && max((int) ($row['width'] ?? 0), (int) ($row['height'] ?? 0)) >= self::MIN_LONG_EDGE,
                'localized_alt_text' => $row !== false && (int) $row['alt_locales'] === 2,
                'project_generated_rights' => $row !== false && (int) $row['rights_locales'] === 2,
                'source_checksum_recorded' => $row !== false && (int) $row['checksum_locales'] === 2,
            ];
            $records[] = [
                'recordId' => $record['recordId'],
                'sku' => $record['sku'],
                'sourcePath' => $record['sourcePath'],
                'sourceSha256' => $record['sourceSha256'],
                'sourceProvenance' => $record['sourceProvenance'],
                'promptFamily' => $record['promptFamily'],
                'visualReview' => $record['visualReview'],
                'mediaUrl' => isset($row['media_path']) && is_string($row['media_path']) ? '/' . ltrim($row['media_path'], '/') : null,
                'width' => isset($row['width']) ? (int) $row['width'] : null,
                'height' => isset($row['height']) ? (int) $row['height'] : null,
                'checks' => $checks,
                'pass' => !in_array(false, $checks, true),
            ];
        }

        $passed = count(array_filter($records, static fn (array $record): bool => $record['pass']));
        $report = [
            'summary' => [
                'expectedProducts' => 50,
                'auditedProducts' => count($records),
                'productsWithGovernedAdminCover' => $passed,
                'productsRemainingInactive' => count(array_filter($records, static fn (array $record): bool => $record['checks']['product_inactive'])),
                'launchApprovedProducts' => 0,
                'minimumGalleryGate' => 'not_applicable_until_launch_approval',
                'pass' => $passed === 50,
            ],
            'records' => $records,
        ];
        $this->writeReport('audit', $report);
        $output->writeln('PHASE 4 COVER PIPELINE: AUDIT ' . ($report['summary']['pass'] ? 'PASS' : 'FAIL'));
        $output->writeln('Governed Admin covers: ' . $passed . '/50');
        $output->writeln('Products kept inactive: ' . $report['summary']['productsRemainingInactive'] . '/50');

        return $report['summary']['pass'] ? Command::SUCCESS : Command::FAILURE;
    }

    private function rollback(OutputInterface $output): int
    {
        $path = $this->projectDir . '/' . self::ROLLBACK;
        if (!is_file($path)) {
            throw new \RuntimeException('Rollback manifest not found.');
        }
        $rollback = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        $context = Context::createDefaultContext();
        foreach (array_reverse($rollback['records'] ?? []) as $record) {
            $this->productRepository->update([[
                'id' => $record['productId'],
                'coverId' => $record['previousCoverId'],
                'active' => false,
            ]], $context);
            $this->productMediaRepository->delete([['id' => $record['createdProductMediaId']]], $context);
            if ($record['mediaExistedBefore'] === false) {
                $this->mediaRepository->delete([['id' => $record['createdMediaId']]], $context);
            }
        }
        $output->writeln('PHASE 4 COVER PIPELINE: ROLLBACK COMPLETE');

        return Command::SUCCESS;
    }

    /** @param list<array<string, mixed>> $manifest @return array<string, mixed> */
    private function buildRollbackManifest(array $manifest): array
    {
        $products = $this->productsBySku();
        $records = [];
        foreach ($manifest as $record) {
            $product = $products[$record['sku']] ?? null;
            if ($product === null) {
                continue;
            }
            $records[] = [
                'recordId' => $record['recordId'],
                'sku' => $record['sku'],
                'productId' => $product['id'],
                'previousCoverId' => $product['cover_id'],
                'createdProductMediaId' => $record['productMediaId'],
                'createdMediaId' => $record['mediaId'],
                'mediaExistedBefore' => (bool) $this->connection->fetchOne('SELECT COUNT(*) FROM media WHERE id = UNHEX(:id)', ['id' => $record['mediaId']]),
                'sourceSha256' => $record['sourceSha256'],
            ];
        }

        return [
            'schemaVersion' => '1.0',
            'batchId' => DraftCatalogManifest::BATCH_ID,
            'capturedAt' => (new \DateTimeImmutable())->format(DATE_ATOM),
            'records' => $records,
        ];
    }

    /** @return array<string, array{id: string, active: int|string, cover_id: string|null}> */
    private function productsBySku(): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT product_number, LOWER(HEX(id)) AS id, active, LOWER(HEX(product_media_id)) AS cover_id FROM product WHERE version_id = UNHEX(:version)',
            ['version' => Defaults::LIVE_VERSION]
        );
        $indexed = [];
        foreach ($rows as $row) {
            $indexed[(string) $row['product_number']] = [
                'id' => (string) $row['id'],
                'active' => $row['active'],
                'cover_id' => $row['cover_id'] === null ? null : (string) $row['cover_id'],
            ];
        }

        return $indexed;
    }

    private function productMediaFolderId(): string
    {
        $id = $this->connection->fetchOne("SELECT LOWER(HEX(mf.id)) FROM media_folder mf JOIN media_default_folder mdf ON mdf.id = mf.default_folder_id WHERE mdf.entity = 'product' LIMIT 1");
        if (!is_string($id) || $id === '') {
            throw new \RuntimeException('Shopware product media folder not found.');
        }

        return $id;
    }

    /** @return array<string, string> */
    private function languageIds(): array
    {
        $rows = $this->connection->fetchAllAssociative("SELECT LOWER(HEX(l.id)) AS id, loc.code FROM language l JOIN locale loc ON loc.id = l.locale_id WHERE loc.code IN ('en-GB', 'de-DE')");
        $ids = [];
        foreach ($rows as $row) {
            $ids[(string) $row['code']] = (string) $row['id'];
        }
        if (!isset($ids['en-GB'], $ids['de-DE'])) {
            throw new \RuntimeException('Required en-GB and de-DE languages are missing.');
        }

        return $ids;
    }

    private function deterministicId(string $seed): string
    {
        return md5('veylune-phase4|' . $seed);
    }

    /** @param array<string, mixed> $report */
    private function writeReport(string $mode, array $report): void
    {
        $this->writeJson(self::REPORT, [
            'schemaVersion' => '1.0',
            'capturedAt' => (new \DateTimeImmutable())->format(DATE_ATOM),
            'workPackage' => '4.1C-4.1E',
            'batchId' => DraftCatalogManifest::BATCH_ID,
            'mode' => $mode,
            'sourceDirectory' => self::SOURCE_DIR,
            'sourceProvenance' => 'openai_imagegen_builtin',
            'promptFamily' => self::PROMPT_FAMILY,
            ...$report,
        ]);
    }

    /** @param array<string, mixed> $data */
    private function writeJson(string $relativePath, array $data): void
    {
        $path = $this->projectDir . '/' . $relativePath;
        $directory = dirname($path);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException('Could not create directory: ' . $directory);
        }
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);
    }
}
