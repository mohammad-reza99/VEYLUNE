<?php declare(strict_types=1);

namespace VeyluneTheme\Command;

use Doctrine\DBAL\Connection;
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
use VeyluneTheme\Preview\EditorialMediaRegistry;

#[AsCommand(name: 'veylune:media:editorial-pipeline', description: 'Validates, imports, audits, or rolls back governed Phase 4 editorial Admin Media.')]
#[Package('storefront')]
final class EditorialMediaPipelineCommand extends Command
{
    private const REPORT = 'reports/catalog/phase-4-editorial-media-pipeline.json';
    private const ROLLBACK = 'var/veylune-phase4-editorial-media-rollback.json';

    /** @param EntityRepository<\Shopware\Core\Content\Media\MediaCollection> $mediaRepository */
    public function __construct(
        private readonly EntityRepository $mediaRepository,
        private readonly FileSaver $fileSaver,
        private readonly Connection $connection,
        private readonly string $projectDir
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('apply', null, InputOption::VALUE_NONE, 'Import all validated editorial media.')
            ->addOption('audit', null, InputOption::VALUE_NONE, 'Audit the persisted editorial media state.')
            ->addOption('rollback', null, InputOption::VALUE_NONE, 'Remove editorial media created by this pipeline.');
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

        $manifest = $this->manifest();
        if (isset($modes['audit'])) {
            return $this->audit($manifest, $output);
        }

        $invalid = array_filter($manifest, static fn (array $record): bool => $record['blockedReasons'] !== []);
        if ($invalid !== []) {
            $this->writeReport('dry_run', $manifest, false);
            $output->writeln('PHASE 4 EDITORIAL MEDIA: BLOCKED (' . count($invalid) . ' records)');

            return Command::FAILURE;
        }
        if (!isset($modes['apply'])) {
            $this->writeReport('dry_run', $manifest, true);
            $output->writeln('PHASE 4 EDITORIAL MEDIA: DRY RUN PASS');
            $output->writeln('Validated destinations: ' . count($manifest));
            $output->writeln('Database mutations: 0');

            return Command::SUCCESS;
        }

        $this->writeJson(self::ROLLBACK, [
            'schemaVersion' => '1.0',
            'capturedAt' => (new \DateTimeImmutable())->format(DATE_ATOM),
            'records' => array_map(fn (array $record): array => [
                'destinationId' => $record['destinationId'],
                'mediaId' => $record['mediaId'],
                'mediaExistedBefore' => (bool) $this->connection->fetchOne('SELECT COUNT(*) FROM media WHERE id = UNHEX(:id)', ['id' => $record['mediaId']]),
            ], $manifest),
        ]);
        $this->apply($manifest, $output);

        return $this->audit($manifest, $output);
    }

    /** @return list<array<string, mixed>> */
    private function manifest(): array
    {
        $records = [];
        foreach (EditorialMediaRegistry::destinations() as $destinationId => $definition) {
            $relative = EditorialMediaRegistry::SOURCE_DIRECTORY . '/' . $definition['source'];
            $source = $this->projectDir . '/' . $relative;
            $dimensions = is_file($source) ? @getimagesize($source) : false;
            $blocks = [];
            if (!is_file($source)) {
                $blocks[] = 'source_missing';
            }
            if (!is_array($dimensions) || ($dimensions['mime'] ?? null) !== 'image/webp') {
                $blocks[] = 'mime_not_webp';
            }
            if (!is_array($dimensions) || max((int) $dimensions[0], (int) $dimensions[1]) < EditorialMediaRegistry::MINIMUM_LONG_EDGE) {
                $blocks[] = 'below_minimum_long_edge';
            }
            $sha = is_file($source) ? (hash_file('sha256', $source) ?: '') : '';
            if ($sha === '') {
                $blocks[] = 'checksum_missing';
            }
            $records[] = [
                'destinationId' => $destinationId,
                'mediaId' => EditorialMediaRegistry::mediaId($destinationId),
                'targetFileName' => EditorialMediaRegistry::targetFileName($destinationId),
                'sourcePath' => $relative,
                'sourceSha256' => $sha,
                'sourceBytes' => is_file($source) ? (int) filesize($source) : 0,
                'width' => is_array($dimensions) ? (int) $dimensions[0] : 0,
                'height' => is_array($dimensions) ? (int) $dimensions[1] : 0,
                'titleEn' => $definition['titleEn'],
                'titleDe' => $definition['titleDe'],
                'altEn' => $definition['altEn'],
                'altDe' => $definition['altDe'],
                'blockedReasons' => $blocks,
            ];
        }

        return $records;
    }

    /** @param list<array<string, mixed>> $manifest */
    private function apply(array $manifest, OutputInterface $output): void
    {
        $context = Context::createDefaultContext();
        $folderId = $this->cmsMediaFolderId();
        $languages = $this->languageIds();
        foreach ($manifest as $index => $record) {
            $customFields = [
                'veylune_media_source' => 'theme_asset_migration',
                'veylune_rights_status' => 'project_existing',
                'veylune_quality_status' => 'approved_editorial',
                'veylune_source_sha256' => $record['sourceSha256'],
                'veylune_visual_review' => 'passed',
                'veylune_editorial_destination' => $record['destinationId'],
                'veylune_source_batch' => DraftCatalogManifest::BATCH_ID,
            ];
            $translations = [
                $languages['en-GB'] => ['title' => $record['titleEn'], 'alt' => $record['altEn'], 'customFields' => $customFields],
                $languages['de-DE'] => ['title' => $record['titleDe'], 'alt' => $record['altDe'], 'customFields' => $customFields],
            ];
            $existing = $this->connection->fetchAssociative('SELECT file_name FROM media WHERE id = UNHEX(:id)', ['id' => $record['mediaId']]);
            $payload = ['id' => $record['mediaId'], 'private' => false, 'translations' => $translations];
            if ($existing === false) {
                $payload['mediaFolderId'] = $folderId;
                $this->mediaRepository->create([$payload], $context);
            } else {
                $this->mediaRepository->update([$payload], $context);
            }
            if ($existing === false || empty($existing['file_name'])) {
                $source = $this->projectDir . '/' . $record['sourcePath'];
                $this->fileSaver->persistFileToMedia(
                    new MediaFile($source, 'image/webp', 'webp', (int) $record['sourceBytes'], md5_file($source) ?: null),
                    (string) $record['targetFileName'],
                    (string) $record['mediaId'],
                    $context
                );
            }
            $output->writeln(sprintf('[%02d/%02d] %s -> Admin Media', $index + 1, count($manifest), $record['destinationId']));
        }
    }

    /** @param list<array<string, mixed>> $manifest */
    private function audit(array $manifest, OutputInterface $output): int
    {
        $records = [];
        foreach ($manifest as $record) {
            $row = $this->connection->fetchAssociative(
                <<<'SQL'
SELECT m.private, m.mime_type, m.path,
       CAST(JSON_UNQUOTE(JSON_EXTRACT(m.meta_data, '$.width')) AS UNSIGNED) AS width,
       CAST(JSON_UNQUOTE(JSON_EXTRACT(m.meta_data, '$.height')) AS UNSIGNED) AS height,
       COUNT(DISTINCT CASE WHEN loc.code IN ('en-GB', 'de-DE') AND TRIM(COALESCE(mt.alt, '')) <> '' THEN loc.code END) AS alt_locales,
       COUNT(DISTINCT CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(mt.custom_fields, '$.veylune_editorial_destination')) = :destination THEN loc.code END) AS destination_locales,
       COUNT(DISTINCT CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(mt.custom_fields, '$.veylune_source_sha256')) = :sha THEN loc.code END) AS checksum_locales
FROM media m
LEFT JOIN media_translation mt ON mt.media_id = m.id
LEFT JOIN language l ON l.id = mt.language_id
LEFT JOIN locale loc ON loc.id = l.locale_id
WHERE m.id = UNHEX(:id)
GROUP BY m.id, m.private, m.mime_type, m.path, m.meta_data
SQL,
                ['destination' => $record['destinationId'], 'sha' => $record['sourceSha256'], 'id' => $record['mediaId']]
            );
            $checks = [
                'media_present' => $row !== false,
                'public_renderable_media' => $row !== false && !(bool) $row['private'] && !empty($row['path']) && ($row['mime_type'] ?? null) === 'image/webp',
                'minimum_resolution' => $row !== false && max((int) ($row['width'] ?? 0), (int) ($row['height'] ?? 0)) >= EditorialMediaRegistry::MINIMUM_LONG_EDGE,
                'localized_alt_text' => $row !== false && (int) $row['alt_locales'] === 2,
                'destination_metadata' => $row !== false && (int) $row['destination_locales'] === 2,
                'source_checksum_recorded' => $row !== false && (int) $row['checksum_locales'] === 2,
            ];
            $records[] = [
                'destinationId' => $record['destinationId'],
                'mediaId' => $record['mediaId'],
                'mediaUrl' => isset($row['path']) && is_string($row['path']) ? '/' . ltrim($row['path'], '/') : null,
                'checks' => $checks,
                'pass' => !in_array(false, $checks, true),
            ];
        }
        $passed = count(array_filter($records, static fn (array $record): bool => $record['pass']));
        $pass = $passed === count(EditorialMediaRegistry::destinations());
        $this->writeJson(self::REPORT, [
            'schemaVersion' => '1.0',
            'capturedAt' => (new \DateTimeImmutable())->format(DATE_ATOM),
            'workPackage' => '4.1E',
            'mode' => 'audit',
            'summary' => ['expectedDestinations' => 13, 'adminEditorialMedia' => $passed, 'pass' => $pass],
            'records' => $records,
        ]);
        $output->writeln('PHASE 4 EDITORIAL MEDIA: AUDIT ' . ($pass ? 'PASS' : 'FAIL'));
        $output->writeln('Admin-managed destinations: ' . $passed . '/13');

        return $pass ? Command::SUCCESS : Command::FAILURE;
    }

    private function rollback(OutputInterface $output): int
    {
        $path = $this->projectDir . '/' . self::ROLLBACK;
        if (!is_file($path)) {
            throw new \RuntimeException('Editorial rollback manifest not found.');
        }
        $rollback = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        $context = Context::createDefaultContext();
        foreach (array_reverse($rollback['records'] ?? []) as $record) {
            if (($record['mediaExistedBefore'] ?? true) === false) {
                $this->mediaRepository->delete([['id' => $record['mediaId']]], $context);
            }
        }
        $output->writeln('PHASE 4 EDITORIAL MEDIA: ROLLBACK COMPLETE');

        return Command::SUCCESS;
    }

    private function cmsMediaFolderId(): string
    {
        $id = $this->connection->fetchOne("SELECT LOWER(HEX(mf.id)) FROM media_folder mf JOIN media_default_folder mdf ON mdf.id = mf.default_folder_id WHERE mdf.entity = 'cms_page' LIMIT 1");
        if (!is_string($id) || $id === '') {
            throw new \RuntimeException('Shopware CMS media folder not found.');
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

    /** @param list<array<string, mixed>> $manifest */
    private function writeReport(string $mode, array $manifest, bool $pass): void
    {
        $this->writeJson(self::REPORT, [
            'schemaVersion' => '1.0',
            'capturedAt' => (new \DateTimeImmutable())->format(DATE_ATOM),
            'workPackage' => '4.1E',
            'mode' => $mode,
            'summary' => ['expectedDestinations' => 13, 'validatedDestinations' => count($manifest), 'pass' => $pass],
            'records' => $manifest,
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
