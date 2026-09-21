<?php declare(strict_types=1);

namespace VeyluneTheme\Command;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use VeyluneTheme\Catalog\DraftCatalogManifest;

#[AsCommand(name: 'veylune:media:baseline', description: 'Captures the governed product-media source-of-truth baseline without changing catalog data.')]
#[Package('storefront')]
final class MediaSourceBaselineCommand extends Command
{
    private const MINIMUM_GALLERY_SLOTS = 5;
    private const PRODUCT_ASSET_DIRECTORY = 'custom/plugins/VeyluneTheme/src/Resources/app/storefront/src/assets/products';
    private const PRODUCT_MEDIA_STYLESHEET = 'custom/plugins/VeyluneTheme/src/Resources/app/storefront/src/scss/component/_marketplace-product-media-closure.scss';
    private const JSON_REPORT = 'reports/catalog/phase-4-1a-media-baseline.json';
    private const CSV_REPORT = 'reports/catalog/phase-4-1a-media-baseline.csv';

    public function __construct(
        private readonly Connection $connection,
        private readonly string $projectDir
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $databaseProducts = $this->databaseProducts();
        $stylesheet = $this->readFile(self::PRODUCT_MEDIA_STYLESHEET);
        $records = [];

        foreach (DraftCatalogManifest::products() as $product) {
            $asset = $this->themeAsset($product['id']);
            $database = $databaseProducts[$product['sku']] ?? null;
            $adminMediaCount = (int) ($database['admin_media_count'] ?? 0);
            $hasAdminCover = (bool) ($database['has_admin_cover'] ?? false);
            $themeAssetPath = $asset['path'] ?? null;
            $themeAssetLongEdge = $asset === null ? null : max((int) ($asset['width'] ?? 0), (int) ($asset['height'] ?? 0));
            $qualityStatus = $asset === null
                ? 'not_available'
                : ($themeAssetLongEdge >= 1600 ? 'minimum_resolution_met' : 'below_minimum_long_edge');
            $hasCssMapping = $themeAssetPath !== null
                && str_contains($stylesheet, sprintf('[data-preview-record="%s"]', $product['id']))
                && str_contains($stylesheet, basename($themeAssetPath));

            $currentSource = $adminMediaCount > 0
                ? 'shopware_admin_media'
                : ($themeAssetPath !== null ? 'theme_css_background' : 'none');
            $migrationAction = $adminMediaCount > 0
                ? 'validate_admin_gallery'
                : ($themeAssetPath !== null
                    ? 'review_rights_and_source_quality_before_admin_import'
                    : 'source_governed_media');

            $records[] = [
                'recordId' => $product['id'],
                'sku' => $product['sku'],
                'name' => $product['nameEn'],
                'department' => $product['department'],
                'productType' => $product['productType'],
                'databaseProductPresent' => $database !== null,
                'databaseProductId' => $database['product_id'] ?? null,
                'adminMediaCount' => $adminMediaCount,
                'hasAdminCover' => $hasAdminCover,
                'adminAltLocales' => (int) ($database['alt_locales'] ?? 0),
                'themeAsset' => $asset,
                'themeCssMappingPresent' => $hasCssMapping,
                'currentSource' => $currentSource,
                'semanticProductImageReady' => false,
                'rightsStatus' => 'unverified',
                'qualityStatus' => $qualityStatus,
                'minimumGallerySlots' => self::MINIMUM_GALLERY_SLOTS,
                'missingAdminGallerySlots' => max(0, self::MINIMUM_GALLERY_SLOTS - $adminMediaCount),
                'migrationAction' => $migrationAction,
                'launchMediaReady' => false,
            ];
        }

        $summary = $this->summary($records);
        $report = [
            'schemaVersion' => '1.0',
            'capturedAt' => (new \DateTimeImmutable())->format(DATE_ATOM),
            'workPackage' => '4.1A',
            'batchId' => DraftCatalogManifest::BATCH_ID,
            'mode' => 'read_only',
            'canonicalTarget' => 'shopware_admin_media',
            'minimumGallerySlots' => self::MINIMUM_GALLERY_SLOTS,
            'summary' => $summary,
            'records' => $records,
        ];

        $this->writeJson($report);
        $this->writeCsv($records);

        $output->writeln('PHASE 4.1A MEDIA SOURCE BASELINE');
        $output->writeln('Draft products: ' . $summary['manifestProducts']);
        $output->writeln('Database products present: ' . $summary['databaseProductsPresent']);
        $output->writeln('Products with Admin media: ' . $summary['productsWithAdminMedia']);
        $output->writeln('Products with Admin cover: ' . $summary['productsWithAdminCover']);
        $output->writeln('Admin media associations: ' . $summary['adminMediaAssociations']);
        $output->writeln('Theme-only product assets: ' . $summary['themeProductAssets']);
        $output->writeln('Products without a dedicated asset: ' . $summary['productsWithoutDedicatedAsset']);
        $output->writeln('Launch-media-ready products: ' . $summary['launchMediaReadyProducts']);
        $output->writeln('Report: ' . self::JSON_REPORT);

        return $summary['databaseProductsPresent'] === $summary['manifestProducts'] ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * @return array<string, array{product_id: string, admin_media_count: int|string, has_admin_cover: int|string, alt_locales: int|string}>
     */
    private function databaseProducts(): array
    {
        $rows = $this->connection->fetchAllAssociative(
            <<<'SQL'
SELECT
    p.product_number,
    LOWER(HEX(p.id)) AS product_id,
    COUNT(DISTINCT pm.id) AS admin_media_count,
    CASE WHEN p.product_media_id IS NULL THEN 0 ELSE 1 END AS has_admin_cover,
    COUNT(DISTINCT CASE WHEN mt.alt IS NOT NULL AND TRIM(mt.alt) <> '' THEN mt.language_id END) AS alt_locales
FROM product p
LEFT JOIN product_media pm
    ON pm.product_id = p.id
    AND pm.product_version_id = p.version_id
LEFT JOIN media_translation mt
    ON mt.media_id = pm.media_id
WHERE EXISTS (
    SELECT 1
    FROM product_translation pt
    WHERE pt.product_id = p.id
      AND pt.product_version_id = p.version_id
      AND pt.custom_fields LIKE :batch
)
GROUP BY p.id, p.product_number, p.product_media_id
ORDER BY p.product_number
SQL,
            ['batch' => '%' . DraftCatalogManifest::BATCH_ID . '%']
        );

        $indexed = [];
        foreach ($rows as $row) {
            $indexed[(string) $row['product_number']] = $row;
        }

        return $indexed;
    }

    /**
     * @return array{path: string, filename: string, bytes: int, sha256: string, width: int|null, height: int|null, mime: string|null}|null
     */
    private function themeAsset(string $recordId): ?array
    {
        $pattern = $this->projectDir . '/' . self::PRODUCT_ASSET_DIRECTORY . '/' . strtolower($recordId) . '-*.webp';
        $matches = glob($pattern) ?: [];

        if ($matches === []) {
            return null;
        }

        sort($matches, SORT_STRING);
        $absolutePath = $matches[0];
        $dimensions = @getimagesize($absolutePath);

        return [
            'path' => self::PRODUCT_ASSET_DIRECTORY . '/' . basename($absolutePath),
            'filename' => basename($absolutePath),
            'bytes' => (int) filesize($absolutePath),
            'sha256' => hash_file('sha256', $absolutePath) ?: '',
            'width' => is_array($dimensions) ? (int) $dimensions[0] : null,
            'height' => is_array($dimensions) ? (int) $dimensions[1] : null,
            'mime' => is_array($dimensions) && isset($dimensions['mime']) ? (string) $dimensions['mime'] : null,
        ];
    }

    /**
     * @param list<array<string, mixed>> $records
     *
     * @return array<string, int|array<string, int>>
     */
    private function summary(array $records): array
    {
        $departments = [];
        foreach ($records as $record) {
            $departments[$record['department']] = ($departments[$record['department']] ?? 0) + 1;
        }
        ksort($departments);

        return [
            'manifestProducts' => count($records),
            'databaseProductsPresent' => count(array_filter($records, static fn (array $record): bool => $record['databaseProductPresent'])),
            'productsWithAdminMedia' => count(array_filter($records, static fn (array $record): bool => $record['adminMediaCount'] > 0)),
            'productsWithAdminCover' => count(array_filter($records, static fn (array $record): bool => $record['hasAdminCover'])),
            'adminMediaAssociations' => array_sum(array_column($records, 'adminMediaCount')),
            'productsWithLocalizedAltText' => count(array_filter($records, static fn (array $record): bool => $record['adminAltLocales'] >= 2)),
            'themeProductAssets' => count(array_filter($records, static fn (array $record): bool => $record['themeAsset'] !== null)),
            'themeAssetsMappedByCss' => count(array_filter($records, static fn (array $record): bool => $record['themeCssMappingPresent'])),
            'themeAssetsMeetingMinimumLongEdge' => count(array_filter($records, static fn (array $record): bool => $record['qualityStatus'] === 'minimum_resolution_met')),
            'themeAssetsBelowMinimumLongEdge' => count(array_filter($records, static fn (array $record): bool => $record['qualityStatus'] === 'below_minimum_long_edge')),
            'semanticProductImageReady' => count(array_filter($records, static fn (array $record): bool => $record['semanticProductImageReady'])),
            'productsWithoutDedicatedAsset' => count(array_filter($records, static fn (array $record): bool => $record['themeAsset'] === null && $record['adminMediaCount'] === 0)),
            'launchMediaReadyProducts' => count(array_filter($records, static fn (array $record): bool => $record['launchMediaReady'])),
            'departments' => $departments,
        ];
    }

    private function readFile(string $relativePath): string
    {
        $contents = file_get_contents($this->projectDir . '/' . $relativePath);

        if ($contents === false) {
            throw new \RuntimeException('Unable to read ' . $relativePath);
        }

        return $contents;
    }

    /** @param array<string, mixed> $report */
    private function writeJson(array $report): void
    {
        $path = $this->projectDir . '/' . self::JSON_REPORT;
        $this->ensureDirectory(dirname($path));
        $encoded = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        file_put_contents($path, $encoded . PHP_EOL);
    }

    /** @param list<array<string, mixed>> $records */
    private function writeCsv(array $records): void
    {
        $path = $this->projectDir . '/' . self::CSV_REPORT;
        $this->ensureDirectory(dirname($path));
        $handle = fopen($path, 'wb');

        if ($handle === false) {
            throw new \RuntimeException('Unable to write ' . self::CSV_REPORT);
        }

        fputcsv($handle, [
            'record_id', 'sku', 'name', 'department', 'product_type', 'database_product_present',
            'admin_media_count', 'has_admin_cover', 'admin_alt_locales', 'theme_asset', 'theme_asset_width',
            'theme_asset_height', 'theme_css_mapping', 'current_source', 'rights_status', 'quality_status',
            'missing_admin_gallery_slots', 'migration_action', 'launch_media_ready',
        ]);

        foreach ($records as $record) {
            fputcsv($handle, [
                $record['recordId'],
                $record['sku'],
                $record['name'],
                $record['department'],
                $record['productType'],
                $record['databaseProductPresent'] ? 'yes' : 'no',
                $record['adminMediaCount'],
                $record['hasAdminCover'] ? 'yes' : 'no',
                $record['adminAltLocales'],
                $record['themeAsset']['path'] ?? '',
                $record['themeAsset']['width'] ?? '',
                $record['themeAsset']['height'] ?? '',
                $record['themeCssMappingPresent'] ? 'yes' : 'no',
                $record['currentSource'],
                $record['rightsStatus'],
                $record['qualityStatus'],
                $record['missingAdminGallerySlots'],
                $record['migrationAction'],
                $record['launchMediaReady'] ? 'yes' : 'no',
            ]);
        }

        fclose($handle);
    }

    private function ensureDirectory(string $directory): void
    {
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException('Unable to create ' . $directory);
        }
    }
}
