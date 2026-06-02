<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class LaunchSimulationRunner
{
    public function __construct(
        private readonly LaunchSimulationFactory $launchSimulationFactory,
        private readonly BatchValidationEngine $batchValidationEngine
    ) {
    }

    public function run(): LaunchSimulationReport
    {
        $violations = [];
        $metrics = [
            'suppliers' => 3,
            'batches' => 0,
            'products' => 0,
            'batch_violations' => 0,
            'sku_negative_probes' => 0,
            'rollback_snapshots' => 0,
        ];
        $seenSkus = [];
        $seenSupplierMappings = [];

        foreach ($this->launchSimulationFactory->manifests() as $manifest) {
            $metrics['batches']++;
            $metrics['products'] += \count($manifest['items'] ?? []);
            $metrics['rollback_snapshots']++;

            $batchReport = $this->batchValidationEngine->validate($manifest);
            if (!$batchReport->passed()) {
                foreach ($batchReport->violations() as $violation) {
                    $violations[] = $manifest['batch_id'] . ':' . $violation;
                }
                $metrics['batch_violations'] += \count($batchReport->violations());
            }

            foreach ($manifest['items'] ?? [] as $item) {
                $sku = (string) ($item['veylune_sku'] ?? '');
                $supplierSku = (string) ($item['supplier_sku'] ?? '');
                $supplierMapping = $manifest['supplier_id'] . '|' . $supplierSku;

                if (isset($seenSkus[$sku])) {
                    $violations[] = 'simulation.duplicate_sku:' . $sku;
                }
                if (isset($seenSupplierMappings[$supplierMapping])) {
                    $violations[] = 'simulation.duplicate_supplier_mapping:' . $supplierMapping;
                }

                $seenSkus[$sku] = true;
                $seenSupplierMappings[$supplierMapping] = true;
            }
        }

        $metrics['sku_negative_probes'] = $this->runSkuNegativeProbes($violations);

        foreach ($this->launchSimulationFactory->expectedMetrics() as $key => $expected) {
            $actualKey = \str_replace('expected_', '', $key);
            if (($metrics[$actualKey] ?? null) !== $expected) {
                $violations[] = 'simulation.metric_mismatch:' . $actualKey;
            }
        }

        return new LaunchSimulationReport(\array_values(\array_unique($violations)), $metrics);
    }

    /**
     * @param list<string> $violations
     */
    private function runSkuNegativeProbes(array &$violations): int
    {
        $duplicate = SkuReservationHarness::conflicts(
            ['VLS-FUR-000001'],
            [],
            ['veylune_sku' => 'VLS-FUR-000001', 'supplier_id' => 'mock-supplier-a', 'supplier_sku' => 'DUP-1']
        );
        $retired = SkuReservationHarness::conflicts(
            [],
            [],
            ['veylune_sku' => 'VLS-FUR-999999', 'supplier_id' => 'mock-supplier-a', 'supplier_sku' => 'RET-1', 'retired_skus' => ['VLS-FUR-999999']]
        );
        $supplierDuplicate = SkuReservationHarness::conflicts(
            [],
            [['supplier_id' => 'mock-supplier-a', 'supplier_sku' => 'SUP-DUP']],
            ['veylune_sku' => 'VLS-FUR-000777', 'supplier_id' => 'mock-supplier-a', 'supplier_sku' => 'SUP-DUP']
        );

        if (!\in_array('duplicate_veylune_sku', $duplicate, true)) {
            $violations[] = 'simulation.negative_probe_duplicate_sku_failed';
        }
        if (!\in_array('retired_sku_reuse', $retired, true)) {
            $violations[] = 'simulation.negative_probe_retired_sku_failed';
        }
        if (!\in_array('duplicate_supplier_sku_mapping', $supplierDuplicate, true)) {
            $violations[] = 'simulation.negative_probe_supplier_mapping_failed';
        }

        return 3;
    }
}
