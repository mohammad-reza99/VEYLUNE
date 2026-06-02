<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class SkuReservationHarness
{
    /**
     * @param list<string> $existingVeyluneSkus
     * @param list<array{supplier_id: string, supplier_sku: string}> $existingSupplierMappings
     * @param array{veylune_sku: string, supplier_id: string, supplier_sku: string, retired_skus?: list<string>} $candidate
     *
     * @return list<string>
     */
    public static function conflicts(array $existingVeyluneSkus, array $existingSupplierMappings, array $candidate): array
    {
        $conflicts = [];
        $veyluneSku = (string) ($candidate['veylune_sku'] ?? '');
        $supplierId = (string) ($candidate['supplier_id'] ?? '');
        $supplierSku = (string) ($candidate['supplier_sku'] ?? '');

        if (!CanonicalSkuContract::isCanonicalSku($veyluneSku)) {
            $conflicts[] = 'invalid_veylune_sku_format';
        }

        if (\in_array($veyluneSku, $existingVeyluneSkus, true)) {
            $conflicts[] = 'duplicate_veylune_sku';
        }

        if (\in_array($veyluneSku, $candidate['retired_skus'] ?? [], true)) {
            $conflicts[] = 'retired_sku_reuse';
        }

        foreach ($existingSupplierMappings as $mapping) {
            if (($mapping['supplier_id'] ?? '') === $supplierId && ($mapping['supplier_sku'] ?? '') === $supplierSku) {
                $conflicts[] = 'duplicate_supplier_sku_mapping';
                break;
            }
        }

        return $conflicts;
    }

    /**
     * @param list<string> $existingVeyluneSkus
     * @param list<array{supplier_id: string, supplier_sku: string}> $existingSupplierMappings
     * @param array{veylune_sku: string, supplier_id: string, supplier_sku: string, retired_skus?: list<string>} $candidate
     */
    public static function canReserve(array $existingVeyluneSkus, array $existingSupplierMappings, array $candidate): bool
    {
        return self::conflicts($existingVeyluneSkus, $existingSupplierMappings, $candidate) === [];
    }
}
