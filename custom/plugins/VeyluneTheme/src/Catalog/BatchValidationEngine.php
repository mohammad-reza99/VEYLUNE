<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class BatchValidationEngine
{
    public function __construct(
        private readonly SupplierStagingRegistry $supplierStagingRegistry,
        private readonly SkuReservationRegistry $skuReservationRegistry
    ) {
    }

    /**
     * @param array<string, mixed> $manifest
     */
    public function validate(array $manifest): BatchValidationReport
    {
        $violations = [];
        $items = \is_array($manifest['items'] ?? null) ? \array_values($manifest['items']) : [];
        $supplierId = (string) ($manifest['supplier_id'] ?? '');
        $batchId = (string) ($manifest['batch_id'] ?? '');

        foreach (BatchManifestContract::missingFields($manifest) as $field) {
            $violations[] = 'manifest.missing_' . $field;
        }

        if ($supplierId !== '') {
            if ($this->supplierStagingRegistry->supplier($supplierId) === null) {
                $violations[] = 'supplier.not_staged:' . $supplierId;
            } elseif (!$this->supplierStagingRegistry->isStagingEligible($supplierId)) {
                $violations[] = 'supplier.not_staging_eligible:' . $supplierId;
            }

            foreach ($this->supplierStagingRegistry->missingRequirements($supplierId) as $field) {
                $violations[] = 'supplier.missing_' . $field . ':' . $supplierId;
            }
        }

        $rollbackTarget = \is_array($manifest['rollback_target'] ?? null) ? $manifest['rollback_target'] : [];
        foreach (RollbackSnapshotContract::missingFields($rollbackTarget) as $field) {
            $violations[] = 'rollback_snapshot.missing_' . $field;
        }
        if ($rollbackTarget !== [] && !RollbackSnapshotContract::hasValidCheckpoint($rollbackTarget)) {
            $violations[] = 'rollback_snapshot.invalid_checkpoint';
        }

        $seenSkus = [];
        $seenSupplierMappings = [];

        foreach ($items as $index => $item) {
            if (!\is_array($item)) {
                $violations[] = 'item.' . $index . '.not_object';
                continue;
            }

            foreach (BatchManifestContract::missingItemFields($item) as $field) {
                $violations[] = 'item.' . $index . '.missing_' . $field;
            }

            $veyluneSku = (string) ($item['veylune_sku'] ?? '');
            $supplierSku = (string) ($item['supplier_sku'] ?? '');
            $supplierMappingKey = $supplierId . '|' . $supplierSku;

            if (isset($seenSkus[$veyluneSku])) {
                $violations[] = 'item.' . $index . '.duplicate_manifest_veylune_sku:' . $veyluneSku;
            }
            if ($veyluneSku !== '') {
                $seenSkus[$veyluneSku] = true;
            }

            if ($supplierSku !== '' && isset($seenSupplierMappings[$supplierMappingKey])) {
                $violations[] = 'item.' . $index . '.duplicate_manifest_supplier_sku:' . $supplierSku;
            }
            if ($supplierSku !== '') {
                $seenSupplierMappings[$supplierMappingKey] = true;
            }

            foreach ($this->skuReservationRegistry->conflicts([
                'veylune_sku' => $veyluneSku,
                'supplier_id' => $supplierId,
                'supplier_sku' => $supplierSku,
            ]) as $conflict) {
                $violations[] = 'item.' . $index . '.sku_' . $conflict . ':' . $veyluneSku;
            }

            $violations = \array_merge($violations, $this->validateTaxonomy($index, $item['taxonomy'] ?? null));
            $violations = \array_merge($violations, $this->validateProperties($index, $item['properties'] ?? null));
            $violations = \array_merge($violations, $this->validateReadiness($index, $batchId, $supplierId, $item));
            $violations = \array_merge($violations, $this->prefixViolations('item.' . $index . '.media_', MediaQualityAuditHarness::violations(\is_array($item['media'] ?? null) ? $item['media'] : [])));
            $violations = \array_merge($violations, $this->prefixViolations('item.' . $index . '.content_', ContentQualityAuditHarness::violations(\is_array($item['content'] ?? null) ? $item['content'] : [])));
        }

        return new BatchValidationReport(\array_values(\array_unique($violations)), [
            'items' => \count($items),
            'violations' => \count(\array_unique($violations)),
        ]);
    }

    /**
     * @return list<string>
     */
    private function validateTaxonomy(int $index, mixed $taxonomy): array
    {
        if (!\is_array($taxonomy)) {
            return ['item.' . $index . '.taxonomy_not_object'];
        }

        $violations = [];
        if (!DepartmentContract::isCanonicalDepartment((string) ($taxonomy['department'] ?? ''))) {
            $violations[] = 'item.' . $index . '.taxonomy_invalid_department';
        }

        foreach (['category', 'product_type', 'room'] as $field) {
            if (($taxonomy[$field] ?? '') === '') {
                $violations[] = 'item.' . $index . '.taxonomy_missing_' . $field;
            }
        }

        return $violations;
    }

    /**
     * @return list<string>
     */
    private function validateProperties(int $index, mixed $properties): array
    {
        if (!\is_array($properties)) {
            return ['item.' . $index . '.properties_not_object'];
        }

        $violations = [];
        foreach (['material' => 'primary_material', 'finish' => 'finish', 'color' => 'color', 'room' => 'room'] as $dictionary => $field) {
            $value = (string) ($properties[$field] ?? '');
            if ($value === '' || ControlledVocabularyContract::normalizeSupplierValue($dictionary, $value) === null) {
                $violations[] = 'item.' . $index . '.properties_invalid_' . $field;
            }
        }

        return $violations;
    }

    /**
     * @param array<string, mixed> $item
     *
     * @return list<string>
     */
    private function validateReadiness(int $index, string $batchId, string $supplierId, array $item): array
    {
        $content = \is_array($item['content'] ?? null) ? $item['content'] : [];
        $media = \is_array($item['media'] ?? null) ? $item['media'] : [];
        $physical = \is_array($item['physical'] ?? null) ? $item['physical'] : [];
        $properties = \is_array($item['properties'] ?? null) ? $item['properties'] : [];
        $taxonomy = \is_array($item['taxonomy'] ?? null) ? $item['taxonomy'] : [];
        $commerce = \is_array($item['commerce'] ?? null) ? $item['commerce'] : [];
        $seo = \is_array($item['seo'] ?? null) ? $item['seo'] : [];
        $governance = \is_array($item['governance'] ?? null) ? $item['governance'] : [];

        $candidate = [
            'identity' => [
                'veylune_sku' => (string) ($item['veylune_sku'] ?? ''),
                'supplier_id' => $supplierId,
                'supplier_sku' => (string) ($item['supplier_sku'] ?? ''),
                'source_batch' => $batchId,
            ],
            'content' => $content,
            'media' => [
                'primary_image' => $media['primary_image'] ?? (($media['images'][0]['type'] ?? '') === 'primary' ? 'present' : ''),
                'detail_image' => $media['detail_image'] ?? (\count($media['images'] ?? []) > 1 ? 'present' : ''),
                'image_rights' => $media['image_rights'] ?? ($media['images'][0]['rights_owner'] ?? ''),
                'en_alt_text' => $media['en_alt_text'] ?? ($media['images'][0]['en_alt'] ?? ''),
                'de_alt_text' => $media['de_alt_text'] ?? ($media['images'][0]['de_alt'] ?? ''),
            ],
            'physical' => $physical,
            'materials' => [
                'primary_material' => $properties['primary_material'] ?? '',
                'finish' => $properties['finish'] ?? '',
                'color' => $properties['color'] ?? '',
            ],
            'taxonomy' => $taxonomy,
            'commerce' => $commerce,
            'seo' => $seo,
            'governance' => $governance,
        ];

        $violations = [];
        foreach (ProductReadinessAuditHarness::missingRequirements($candidate) as $domain => $fields) {
            foreach ($fields as $field) {
                $violations[] = 'item.' . $index . '.readiness_missing_' . $domain . '_' . $field;
            }
        }

        return $violations;
    }

    /**
     * @param list<string> $violations
     *
     * @return list<string>
     */
    private function prefixViolations(string $prefix, array $violations): array
    {
        return \array_map(static fn (string $violation): string => $prefix . $violation, $violations);
    }
}
