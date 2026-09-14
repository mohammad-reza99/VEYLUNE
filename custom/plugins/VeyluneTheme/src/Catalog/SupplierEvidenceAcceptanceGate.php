<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class SupplierEvidenceAcceptanceGate
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $records = [];

    public function __construct(string $projectDir)
    {
        $intake = require $projectDir . '/custom/plugins/VeyluneTheme/src/Resources/config/level3_supplier_intake.php';
        $records = \is_array($intake['records'] ?? null) ? $intake['records'] : [];

        foreach ($records as $record) {
            $productNumber = strtoupper(trim((string) ($record['veylune_sku'] ?? '')));

            if ($productNumber !== '') {
                $this->records[$productNumber] = $record;
            }
        }
    }

    /**
     * @return array{status: string, accepted: bool, missing: list<string>, reasons: list<string>}
     */
    public function review(string $productNumber): array
    {
        $productNumber = strtoupper(trim($productNumber));
        $record = $this->records[$productNumber] ?? null;

        if ($record === null) {
            return [
                'status' => 'missing',
                'accepted' => false,
                'missing' => SupplierEvidenceContract::requiredFields(),
                'reasons' => ['supplier evidence record is missing'],
            ];
        }

        $status = \is_string($record['status'] ?? null) ? trim($record['status']) : '';
        $missing = \array_values(\array_filter(
            SupplierEvidenceContract::requiredFields(),
            static fn (string $field): bool => !\is_string($record[$field] ?? null) || trim($record[$field]) === ''
        ));
        $reasons = [];

        if ($status !== SupplierEvidenceContract::STATUS_ACCEPTED) {
            $reasons[] = 'supplier evidence status is ' . ($status !== '' ? $status : 'missing');
        }

        if ($missing !== []) {
            $reasons[] = 'supplier evidence is incomplete';
        }

        return [
            'status' => $status,
            'accepted' => $status === SupplierEvidenceContract::STATUS_ACCEPTED && $missing === [],
            'missing' => $missing,
            'reasons' => $reasons,
        ];
    }

    public function isAccepted(string $productNumber): bool
    {
        return $this->review($productNumber)['accepted'];
    }

    /**
     * @return list<string>
     */
    public function rejectionReasons(string $productNumber): array
    {
        return $this->review($productNumber)['reasons'];
    }
}
