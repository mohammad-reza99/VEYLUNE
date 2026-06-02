<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class SkuReservationRegistry
{
    /**
     * @var list<array{veylune_sku: string, supplier_id: string, supplier_sku: string, state?: string}>
     */
    private array $reservations;

    /**
     * @var list<string>
     */
    private array $retiredSkus;

    public function __construct()
    {
        $registry = require __DIR__ . '/../Resources/config/staging_registries.php';
        $this->reservations = \is_array($registry['sku_reservations'] ?? null) ? \array_values($registry['sku_reservations']) : [];
        $this->retiredSkus = \is_array($registry['retired_skus'] ?? null) ? \array_values($registry['retired_skus']) : [];
    }

    /**
     * @return list<string>
     */
    public function reservedSkus(): array
    {
        return \array_values(\array_filter(
            \array_map(static fn (array $reservation): string => (string) ($reservation['veylune_sku'] ?? ''), $this->reservations),
            static fn (string $sku): bool => $sku !== ''
        ));
    }

    /**
     * @return list<array{supplier_id: string, supplier_sku: string}>
     */
    public function supplierMappings(): array
    {
        $mappings = [];

        foreach ($this->reservations as $reservation) {
            $supplierId = (string) ($reservation['supplier_id'] ?? '');
            $supplierSku = (string) ($reservation['supplier_sku'] ?? '');

            if ($supplierId !== '' && $supplierSku !== '') {
                $mappings[] = ['supplier_id' => $supplierId, 'supplier_sku' => $supplierSku];
            }
        }

        return $mappings;
    }

    /**
     * @return list<string>
     */
    public function retiredSkus(): array
    {
        return $this->retiredSkus;
    }

    /**
     * @param array{veylune_sku: string, supplier_id: string, supplier_sku: string} $candidate
     *
     * @return list<string>
     */
    public function conflicts(array $candidate): array
    {
        $candidate['retired_skus'] = $this->retiredSkus;

        return SkuReservationHarness::conflicts($this->reservedSkus(), $this->supplierMappings(), $candidate);
    }
}
