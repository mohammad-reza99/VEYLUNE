<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class SupplierStagingRegistry
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $suppliers;

    public function __construct()
    {
        $registry = require __DIR__ . '/../Resources/config/staging_registries.php';
        $this->suppliers = \is_array($registry['suppliers'] ?? null) ? $registry['suppliers'] : [];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function supplier(string $supplierId): ?array
    {
        return $this->suppliers[$supplierId] ?? null;
    }

    public function status(string $supplierId): ?string
    {
        $supplier = $this->supplier($supplierId);

        return \is_string($supplier['status'] ?? null) ? $supplier['status'] : null;
    }

    public function isStagingEligible(string $supplierId): bool
    {
        $status = $this->status($supplierId);

        return $status !== null && SupplierStagingContract::isStagingEligible($status);
    }

    /**
     * @return list<string>
     */
    public function missingRequirements(string $supplierId): array
    {
        $supplier = $this->supplier($supplierId);

        if ($supplier === null) {
            return SupplierStagingContract::stagingRequirements();
        }

        return \array_values(\array_filter(
            SupplierStagingContract::stagingRequirements(),
            static fn (string $field): bool => !isset($supplier[$field]) || $supplier[$field] === ''
        ));
    }
}
