<?php declare(strict_types=1);

namespace VeyluneTheme\Command;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use VeyluneTheme\Catalog\DraftCatalogManifest;

#[AsCommand(name: 'veylune:media:intake-plan', description: 'Builds a fail-closed, read-only Shopware Media intake plan for existing product assets.')]
#[Package('storefront')]
final class MediaIntakePlanCommand extends Command
{
    private const BASELINE_REPORT = 'reports/catalog/phase-4-1a-media-baseline.json';
    private const REVIEW_FILE = 'config/veylune-phase-4-media-intake-review.json';
    private const JSON_REPORT = 'reports/catalog/phase-4-1b-media-intake-plan.json';
    private const CSV_REPORT = 'reports/catalog/phase-4-1b-media-intake-plan.csv';
    private const TARGET_FOLDER = 'Veylune/Catalog Intake/WP-CAT-04-DRAFT-50';

    public function __construct(
        private readonly Connection $connection,
        private readonly string $projectDir
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'write-review-template',
            null,
            InputOption::VALUE_NONE,
            'Create the initial per-asset review input without overwriting an existing review file.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $baseline = $this->readJson(self::BASELINE_REPORT);
        $candidates = array_values(array_filter(
            $baseline['records'] ?? [],
            static fn (array $record): bool => ($record['themeAsset'] ?? null) !== null
        ));

        if (count($candidates) !== 19) {
            throw new \RuntimeException('Phase 4.1B requires exactly 19 inventoried theme assets.');
        }

        if ((bool) $input->getOption('write-review-template')) {
            $created = $this->writeReviewTemplate($candidates);
            $output->writeln($created
                ? 'Review template created: ' . self::REVIEW_FILE
                : 'Review template retained without overwrite: ' . self::REVIEW_FILE);
        }

        $review = $this->readJson(self::REVIEW_FILE);
        $reviewsBySku = [];
        foreach ($review['records'] ?? [] as $record) {
            $reviewsBySku[(string) ($record['sku'] ?? '')] = $record;
        }

        $plans = [];
        foreach ($candidates as $candidate) {
            $sku = (string) $candidate['sku'];
            $decision = $reviewsBySku[$sku] ?? null;
            if (!is_array($decision)) {
                throw new \RuntimeException('Missing intake review input for ' . $sku);
            }

            $asset = $candidate['themeAsset'];
            $targetFileName = $this->targetFileName($candidate);
            $existing = $this->existingMedia($targetFileName);
            $blockedReasons = $this->blockedReasons($candidate, $decision, $existing);
            $plannedAction = $this->plannedAction($blockedReasons, $existing);

            $plans[] = [
                'recordId' => $candidate['recordId'],
                'sku' => $sku,
                'name' => $candidate['name'],
                'sourcePath' => $asset['path'],
                'sourceSha256' => $asset['sha256'],
                'sourceWidth' => $asset['width'],
                'sourceHeight' => $asset['height'],
                'sourceMime' => $asset['mime'],
                'targetFolder' => self::TARGET_FOLDER,
                'targetFileName' => $targetFileName,
                'targetExtension' => 'webp',
                'targetPrivate' => true,
                'idempotencyKey' => hash('sha256', $sku . '|' . $asset['sha256'] . '|' . $targetFileName),
                'rightsStatus' => $decision['rights']['status'] ?? 'missing',
                'qualityStatus' => $decision['quality']['status'] ?? 'missing',
                'approvedForPrivateIntake' => (bool) ($decision['approvedForPrivateIntake'] ?? false),
                'existingMedia' => $existing,
                'blockedReasons' => $blockedReasons,
                'plannedAction' => $plannedAction,
                'productAssociationPlanned' => false,
                'adminCoverPlanned' => false,
                'cssFallbackRetained' => true,
            ];
        }

        $summary = $this->summary($plans);
        $report = [
            'schemaVersion' => '1.0',
            'capturedAt' => (new \DateTimeImmutable())->format(DATE_ATOM),
            'workPackage' => '4.1B',
            'batchId' => DraftCatalogManifest::BATCH_ID,
            'mode' => 'read_only_dry_run',
            'targetFolder' => self::TARGET_FOLDER,
            'databaseMutation' => false,
            'productAssociationsCreated' => 0,
            'adminCoversCreated' => 0,
            'cssFallbackRemoved' => false,
            'summary' => $summary,
            'records' => $plans,
        ];

        $this->writeJson(self::JSON_REPORT, $report);
        $this->writeCsv($plans);

        $output->writeln('PHASE 4.1B MEDIA INTAKE DRY RUN');
        $output->writeln('Candidates: ' . $summary['candidates']);
        $output->writeln('Approved for private intake: ' . $summary['approvedForPrivateIntake']);
        $output->writeln('Held by governance: ' . $summary['heldByGovernance']);
        $output->writeln('Would import private media: ' . $summary['wouldImportPrivateMedia']);
        $output->writeln('Existing private candidates: ' . $summary['existingPrivateCandidates']);
        $output->writeln('Name collisions: ' . $summary['nameCollisions']);
        $output->writeln('Database mutations: 0');
        $output->writeln('Report: ' . self::JSON_REPORT);

        return Command::SUCCESS;
    }

    /** @param list<array<string, mixed>> $candidates */
    private function writeReviewTemplate(array $candidates): bool
    {
        $path = $this->projectDir . '/' . self::REVIEW_FILE;
        if (is_file($path)) {
            return false;
        }

        $productsBySku = [];
        foreach (DraftCatalogManifest::products() as $product) {
            $productsBySku[$product['sku']] = $product;
        }

        $records = [];
        foreach ($candidates as $candidate) {
            $asset = $candidate['themeAsset'];
            $product = $productsBySku[$candidate['sku']] ?? null;
            $records[] = [
                'recordId' => $candidate['recordId'],
                'sku' => $candidate['sku'],
                'sourceCandidate' => [
                    'path' => $asset['path'],
                    'sha256' => $asset['sha256'],
                    'width' => $asset['width'],
                    'height' => $asset['height'],
                    'mime' => $asset['mime'],
                ],
                'rights' => [
                    'status' => 'pending',
                    'owner' => null,
                    'licenseReference' => null,
                    'sourceProvenance' => 'legacy_theme_asset',
                    'reviewedBy' => null,
                    'reviewedAt' => null,
                ],
                'quality' => [
                    'status' => 'blocked_below_minimum_long_edge',
                    'requiredLongEdgePx' => 1600,
                    'measuredLongEdgePx' => max((int) $asset['width'], (int) $asset['height']),
                    'decision' => 'replace_with_higher_quality_source',
                    'reviewedBy' => null,
                    'reviewedAt' => null,
                ],
                'localizedMetadataDraft' => [
                    'enAlt' => $candidate['name'] . ' product view',
                    'deAlt' => ($product['nameDe'] ?? $candidate['name']) . ' Produktansicht',
                    'reviewed' => false,
                ],
                'approvedForPrivateIntake' => false,
                'approvalNote' => null,
            ];
        }

        $template = [
            'schemaVersion' => '1.0',
            'workPackage' => '4.1B',
            'batchId' => DraftCatalogManifest::BATCH_ID,
            'policy' => [
                'defaultDecision' => 'hold',
                'unknownRightsMayImport' => false,
                'belowMinimumQualityMayBecomeCover' => false,
                'privateIntakeRequiresExplicitApproval' => true,
                'productAssociationAllowedIn41B' => false,
                'adminCoverAllowedIn41B' => false,
            ],
            'records' => $records,
        ];

        $this->writeJson(self::REVIEW_FILE, $template);

        return true;
    }

    /** @param array<string, mixed> $candidate */
    private function targetFileName(array $candidate): string
    {
        $sku = strtolower(str_replace('_', '-', (string) $candidate['sku']));
        $recordId = strtolower((string) $candidate['recordId']);
        $hash = substr((string) $candidate['themeAsset']['sha256'], 0, 12);

        return sprintf('veylune-%s-primary-candidate-%s-%s', $sku, $recordId, $hash);
    }

    /** @return array{id: string, fileName: string, extension: string|null, mime: string|null, bytes: int|null, private: bool}|null */
    private function existingMedia(string $targetFileName): ?array
    {
        $row = $this->connection->fetchAssociative(
            <<<'SQL'
SELECT LOWER(HEX(id)) AS id, file_name, file_extension, mime_type, file_size, private
FROM media
WHERE file_name = :fileName
LIMIT 1
SQL,
            ['fileName' => $targetFileName]
        );

        if ($row === false) {
            return null;
        }

        return [
            'id' => (string) $row['id'],
            'fileName' => (string) $row['file_name'],
            'extension' => $row['file_extension'] === null ? null : (string) $row['file_extension'],
            'mime' => $row['mime_type'] === null ? null : (string) $row['mime_type'],
            'bytes' => $row['file_size'] === null ? null : (int) $row['file_size'],
            'private' => (bool) $row['private'],
        ];
    }

    /**
     * @param array<string, mixed> $candidate
     * @param array<string, mixed> $decision
     * @param array<string, mixed>|null $existing
     *
     * @return list<string>
     */
    private function blockedReasons(array $candidate, array $decision, ?array $existing): array
    {
        $reasons = [];
        $asset = $candidate['themeAsset'];
        $source = $decision['sourceCandidate'] ?? [];

        if (($source['sha256'] ?? null) !== $asset['sha256']) {
            $reasons[] = 'source_checksum_mismatch';
        }

        $rights = $decision['rights'] ?? [];
        if (($rights['status'] ?? null) !== 'approved'
            || empty($rights['owner'])
            || empty($rights['licenseReference'])
            || empty($rights['reviewedBy'])
            || empty($rights['reviewedAt'])) {
            $reasons[] = 'rights_not_approved';
        }

        $quality = $decision['quality'] ?? [];
        if (!in_array($quality['status'] ?? null, ['approved_source', 'accepted_exception'], true)
            || empty($quality['reviewedBy'])
            || empty($quality['reviewedAt'])) {
            $reasons[] = 'quality_not_approved';
        }

        if (($decision['localizedMetadataDraft']['reviewed'] ?? false) !== true) {
            $reasons[] = 'localized_metadata_not_reviewed';
        }

        if (($decision['approvedForPrivateIntake'] ?? false) !== true) {
            $reasons[] = 'private_intake_not_approved';
        }

        if ($existing !== null && ($existing['private'] ?? false) !== true) {
            $reasons[] = 'public_media_name_collision';
        }

        return array_values(array_unique($reasons));
    }

    /** @param list<string> $blockedReasons @param array<string, mixed>|null $existing */
    private function plannedAction(array $blockedReasons, ?array $existing): string
    {
        if (in_array('public_media_name_collision', $blockedReasons, true)) {
            return 'blocked_public_name_collision';
        }

        if ($existing !== null && ($existing['private'] ?? false) === true) {
            return 'already_imported_private_candidate';
        }

        return $blockedReasons === [] ? 'would_import_private_media' : 'hold_for_review';
    }

    /** @param list<array<string, mixed>> $plans @return array<string, int> */
    private function summary(array $plans): array
    {
        return [
            'candidates' => count($plans),
            'uniqueTargetNames' => count(array_unique(array_column($plans, 'targetFileName'))),
            'uniqueIdempotencyKeys' => count(array_unique(array_column($plans, 'idempotencyKey'))),
            'approvedForPrivateIntake' => count(array_filter($plans, static fn (array $plan): bool => $plan['approvedForPrivateIntake'])),
            'heldByGovernance' => count(array_filter($plans, static fn (array $plan): bool => $plan['plannedAction'] === 'hold_for_review')),
            'wouldImportPrivateMedia' => count(array_filter($plans, static fn (array $plan): bool => $plan['plannedAction'] === 'would_import_private_media')),
            'existingPrivateCandidates' => count(array_filter($plans, static fn (array $plan): bool => $plan['plannedAction'] === 'already_imported_private_candidate')),
            'nameCollisions' => count(array_filter($plans, static fn (array $plan): bool => $plan['plannedAction'] === 'blocked_public_name_collision')),
            'productAssociationsPlanned' => count(array_filter($plans, static fn (array $plan): bool => $plan['productAssociationPlanned'])),
            'adminCoversPlanned' => count(array_filter($plans, static fn (array $plan): bool => $plan['adminCoverPlanned'])),
        ];
    }

    /** @return array<string, mixed> */
    private function readJson(string $relativePath): array
    {
        $path = $this->projectDir . '/' . $relativePath;
        if (!is_file($path)) {
            throw new \RuntimeException('Missing file: ' . $relativePath);
        }

        $decoded = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Invalid JSON object: ' . $relativePath);
        }

        return $decoded;
    }

    /** @param array<string, mixed> $data */
    private function writeJson(string $relativePath, array $data): void
    {
        $path = $this->projectDir . '/' . $relativePath;
        $directory = dirname($path);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException('Unable to create ' . $directory);
        }

        $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        file_put_contents($path, $encoded . PHP_EOL);
    }

    /** @param list<array<string, mixed>> $plans */
    private function writeCsv(array $plans): void
    {
        $path = $this->projectDir . '/' . self::CSV_REPORT;
        $handle = fopen($path, 'wb');
        if ($handle === false) {
            throw new \RuntimeException('Unable to write ' . self::CSV_REPORT);
        }

        fputcsv($handle, [
            'record_id', 'sku', 'source_path', 'source_sha256', 'target_folder', 'target_file_name',
            'target_private', 'rights_status', 'quality_status', 'approved_for_private_intake',
            'planned_action', 'blocked_reasons', 'product_association_planned', 'admin_cover_planned',
            'css_fallback_retained', 'idempotency_key',
        ]);

        foreach ($plans as $plan) {
            fputcsv($handle, [
                $plan['recordId'], $plan['sku'], $plan['sourcePath'], $plan['sourceSha256'],
                $plan['targetFolder'], $plan['targetFileName'], $plan['targetPrivate'] ? 'yes' : 'no',
                $plan['rightsStatus'], $plan['qualityStatus'], $plan['approvedForPrivateIntake'] ? 'yes' : 'no',
                $plan['plannedAction'], implode('|', $plan['blockedReasons']),
                $plan['productAssociationPlanned'] ? 'yes' : 'no', $plan['adminCoverPlanned'] ? 'yes' : 'no',
                $plan['cssFallbackRetained'] ? 'yes' : 'no', $plan['idempotencyKey'],
            ]);
        }

        fclose($handle);
    }
}
