<?php declare(strict_types=1);

namespace VeyluneTheme\Command;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use VeyluneTheme\Catalog\SupplierEvidenceContract;
use VeyluneTheme\Discovery\ProductExposureService;

#[AsCommand(name: 'veylune:catalog:phase7-readiness-audit', description: 'Audits Phase 7 launch evidence and preserves fail-closed public activation.')]
#[Package('storefront')]
final class Phase7CommercialReadinessAuditCommand extends Command
{
    public function __construct(
        private readonly Connection $connection,
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('report', null, InputOption::VALUE_REQUIRED, 'Project-relative JSON report path', 'reports/commercial/phase-7-readiness.json')
            ->addOption('require-exit', null, InputOption::VALUE_NONE, 'Fail unless every external gate and runtime activation condition is complete');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cohort = require $this->projectDir . '/custom/plugins/VeyluneTheme/src/Resources/config/level3_cohort.php';
        $intake = require $this->projectDir . '/custom/plugins/VeyluneTheme/src/Resources/config/level3_supplier_intake.php';
        $operational = $this->readJson($this->projectDir . '/config/veylune-phase-7-operational-evidence.json');
        $products = \is_array($cohort['products'] ?? null) ? $cohort['products'] : [];
        $records = \is_array($intake['records'] ?? null) ? $intake['records'] : [];
        $gates = \is_array($operational['gates'] ?? null) ? $operational['gates'] : [];
        $minimumCohort = (int) ($operational['launchCohortMinimum'] ?? 10);
        $minimumGallery = (int) ($operational['galleryMinimumPerProduct'] ?? 5);
        $allowedStates = \is_array($operational['allowedStates'] ?? null) ? $operational['allowedStates'] : [];
        $technicalViolations = [];

        $cohortSkus = \array_values(\array_map(static fn (array $product): string => (string) ($product['sku'] ?? ''), $products));
        $recordSkus = \array_values(\array_map(static fn (array $record): string => (string) ($record['veylune_sku'] ?? ''), $records));
        $runtimeSkus = ProductExposureService::governedProductNumbers();

        if (\count($cohortSkus) !== $minimumCohort || \count(\array_unique($cohortSkus)) !== $minimumCohort) {
            $technicalViolations[] = 'cohort.must_contain_exactly_' . $minimumCohort . '_unique_products';
        }
        if ($cohortSkus !== $recordSkus) {
            $technicalViolations[] = 'supplier_intake.cohort_identity_drift';
        }
        if ($cohortSkus !== $runtimeSkus) {
            $technicalViolations[] = 'runtime_registry.cohort_identity_drift';
        }

        $acceptedSupplierEvidence = 0;
        $missingEvidenceCells = 0;
        foreach ($records as $record) {
            $missing = \array_values(\array_filter(
                SupplierEvidenceContract::requiredFields(),
                static fn (string $field): bool => !\is_string($record[$field] ?? null) || \trim($record[$field]) === ''
            ));
            $missingEvidenceCells += \count($missing);
            if (($record['status'] ?? null) === SupplierEvidenceContract::STATUS_ACCEPTED && $missing === []) {
                ++$acceptedSupplierEvidence;
            }
        }

        $productRows = [];
        $galleryReady = 0;
        $failClosedProducts = 0;
        $activeProducts = 0;
        $visibleProducts = 0;

        foreach ($cohortSkus as $sku) {
            $rows = $this->connection->fetchAllAssociative(
                'SELECT p.product_number, p.active, p.stock, p.product_media_id AS cover_id,
                        COUNT(DISTINCT pm.id) media_count,
                        COUNT(DISTINCT pv.id) visibility_count
                 FROM product p
                 LEFT JOIN product_media pm ON pm.product_id = p.id AND pm.product_version_id = p.version_id
                 LEFT JOIN product_visibility pv ON pv.product_id = p.id AND pv.product_version_id = p.version_id
                 WHERE p.product_number = :sku AND p.version_id = UNHEX(:liveVersion)
                 GROUP BY p.id, p.product_number, p.active, p.stock, p.product_media_id',
                ['sku' => $sku, 'liveVersion' => Defaults::LIVE_VERSION]
            );

            if (\count($rows) !== 1) {
                $technicalViolations[] = 'catalog.product_not_exactly_one:' . $sku;
                continue;
            }

            $row = $rows[0];
            $isActive = (bool) $row['active'];
            $stock = (int) $row['stock'];
            $mediaCount = (int) $row['media_count'];
            $visibilityCount = (int) $row['visibility_count'];
            $hasCover = $row['cover_id'] !== null;
            $isGalleryReady = $hasCover && $mediaCount >= $minimumGallery;
            $isFailClosed = !$isActive && $stock === 0 && $visibilityCount === 0;

            $galleryReady += (int) $isGalleryReady;
            $failClosedProducts += (int) $isFailClosed;
            $activeProducts += (int) $isActive;
            $visibleProducts += (int) ($visibilityCount > 0);

            if ($acceptedSupplierEvidence < $minimumCohort && !$isFailClosed) {
                $technicalViolations[] = 'catalog.unapproved_candidate_not_fail_closed:' . $sku;
            }

            $productRows[] = [
                'sku' => $sku,
                'active' => $isActive,
                'stock' => $stock,
                'visibilityCount' => $visibilityCount,
                'mediaCount' => $mediaCount,
                'hasCover' => $hasCover,
                'galleryReady' => $isGalleryReady,
                'failClosed' => $isFailClosed,
            ];
        }

        $operationalAccepted = 0;
        $externalBlockers = [];
        $seenGateIds = [];
        foreach ($gates as $gate) {
            $gateId = trim((string) ($gate['id'] ?? ''));
            $state = trim((string) ($gate['state'] ?? ''));
            $owner = trim((string) ($gate['owner'] ?? ''));
            $evidenceReference = trim((string) ($gate['evidenceReference'] ?? ''));

            if ($gateId === '' || isset($seenGateIds[$gateId])) {
                $technicalViolations[] = 'operational_gate.invalid_or_duplicate_id:' . $gateId;
                continue;
            }
            $seenGateIds[$gateId] = true;

            if ($owner === '' || !\in_array($state, $allowedStates, true)) {
                $technicalViolations[] = 'operational_gate.invalid_contract:' . $gateId;
                continue;
            }

            if ($state === 'accepted') {
                $normalizedReference = \strtolower($evidenceReference);
                if ($evidenceReference === '' || \str_contains($normalizedReference, 'placeholder') || \str_contains($normalizedReference, 'mock')) {
                    $technicalViolations[] = 'operational_gate.accepted_without_real_evidence:' . $gateId;
                    continue;
                }
                ++$operationalAccepted;
                continue;
            }

            $externalBlockers[] = $gateId . ':' . $state;
        }

        $commerceInventory = [
            'activePaymentMethods' => (int) $this->connection->fetchOne('SELECT COUNT(*) FROM payment_method WHERE active = 1'),
            'activeShippingMethods' => (int) $this->connection->fetchOne('SELECT COUNT(*) FROM shipping_method WHERE active = 1'),
            'taxRules' => (int) $this->connection->fetchOne('SELECT COUNT(*) FROM tax'),
            'configuredCurrencies' => (int) $this->connection->fetchOne('SELECT COUNT(*) FROM currency'),
        ];

        $runtimeActivationAllowed = $acceptedSupplierEvidence === $minimumCohort
            && $galleryReady === $minimumCohort
            && $operationalAccepted === \count($gates)
            && $technicalViolations === [];
        $phaseComplete = $runtimeActivationAllowed
            && $activeProducts === $minimumCohort
            && $visibleProducts === $minimumCohort;
        $phaseStatus = $phaseComplete
            ? 'complete'
            : ($runtimeActivationAllowed ? 'ready_for_activation_review' : 'blocked_external_inputs');

        $report = [
            'schemaVersion' => '1.0',
            'capturedAt' => (string) ($operational['baselineDate'] ?? ''),
            'phase' => '7',
            'status' => $phaseStatus,
            'auditStatus' => $technicalViolations === [] ? 'pass' : 'fail',
            'cohort' => [
                'id' => (string) ($cohort['cohort_id'] ?? ''),
                'products' => \count($cohortSkus),
                'acceptedSupplierEvidence' => $acceptedSupplierEvidence,
                'missingSupplierEvidenceCells' => $missingEvidenceCells,
                'galleryReadyProducts' => $galleryReady,
                'failClosedProducts' => $failClosedProducts,
                'activeProducts' => $activeProducts,
                'visibleProducts' => $visibleProducts,
            ],
            'operations' => [
                'acceptedGates' => $operationalAccepted,
                'totalGates' => \count($gates),
                'externalBlockers' => $externalBlockers,
                'commerceInventory' => $commerceInventory,
            ],
            'runtimeActivationAllowed' => $runtimeActivationAllowed,
            'technicalViolations' => $technicalViolations,
            'products' => $productRows,
        ];

        $reportPath = $this->projectDir . '/' . ltrim((string) $input->getOption('report'), '/');
        $reportDir = \dirname($reportPath);
        if (!\is_dir($reportDir) && !\mkdir($reportDir, 0775, true) && !\is_dir($reportDir)) {
            throw new \RuntimeException('Unable to create report directory: ' . $reportDir);
        }
        $encoded = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
        if (file_put_contents($reportPath, $encoded) === false) {
            throw new \RuntimeException('Unable to write Phase 7 report: ' . $reportPath);
        }

        $output->writeln($technicalViolations === [] ? 'PHASE 7 READINESS AUDIT PASS' : 'PHASE 7 READINESS AUDIT FAIL');
        $output->writeln('Phase state: ' . strtoupper($phaseStatus));
        $output->writeln('Launch candidates: ' . \count($cohortSkus));
        $output->writeln('Supplier evidence accepted: ' . $acceptedSupplierEvidence . '/' . $minimumCohort);
        $output->writeln('Gallery ready: ' . $galleryReady . '/' . $minimumCohort);
        $output->writeln('Operational gates accepted: ' . $operationalAccepted . '/' . \count($gates));
        $output->writeln('Fail-closed products: ' . $failClosedProducts . '/' . $minimumCohort);
        $output->writeln('Runtime activation allowed: ' . ($runtimeActivationAllowed ? 'yes' : 'no'));
        $output->writeln('Report: ' . str_replace($this->projectDir . '/', '', $reportPath));

        foreach ($technicalViolations as $violation) {
            $output->writeln('- ' . $violation);
        }

        if ($technicalViolations !== []) {
            return Command::FAILURE;
        }
        if ((bool) $input->getOption('require-exit') && !$phaseComplete) {
            $output->writeln('PHASE 7 EXIT BLOCKED: accepted external evidence and controlled runtime activation are still required.');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    private function readJson(string $path): array
    {
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new \RuntimeException('Unable to read JSON contract: ' . $path);
        }

        $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        if (!\is_array($decoded)) {
            throw new \RuntimeException('JSON contract must decode to an object: ' . $path);
        }

        return $decoded;
    }
}
