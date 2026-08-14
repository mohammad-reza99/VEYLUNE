<?php declare(strict_types=1);

namespace VeyluneTheme\Command;

use Shopware\Core\Framework\Log\Package;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(name: 'veylune:catalog:level3-intake-audit', description: 'Audits supplier evidence intake for the fixed first-ten Level 3 cohort.')]
#[Package('storefront')]
final class Level3SupplierIntakeAuditCommand extends Command
{
    private const REQUIRED_EVIDENCE = [
        'supplier_id', 'supplier_legal_name', 'supplier_sku', 'source_batch',
        'pricing_authority_reference', 'availability_authority_reference',
        'specification_pack_reference', 'media_rights_schedule_reference',
        'material_evidence_reference', 'source_owner', 'reviewed_at', 'reviewer',
    ];

    public function __construct(
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cohort = require $this->projectDir . '/custom/plugins/VeyluneTheme/src/Resources/config/level3_cohort.php';
        $intake = require $this->projectDir . '/custom/plugins/VeyluneTheme/src/Resources/config/level3_supplier_intake.php';
        $cohortSkus = \array_column($cohort['products'] ?? [], 'sku');
        $records = \is_array($intake['records'] ?? null) ? $intake['records'] : [];
        $recordSkus = \array_column($records, 'veylune_sku');
        $violations = [];
        $accepted = 0;
        $blocked = 0;

        if ($cohortSkus !== $recordSkus || \count(\array_unique($recordSkus)) !== 10) {
            $violations[] = 'intake.cohort_identity_mismatch';
        }

        foreach ($records as $record) {
            $sku = (string) ($record['veylune_sku'] ?? 'unknown');
            $status = (string) ($record['status'] ?? '');
            $supplierId = \strtolower((string) ($record['supplier_id'] ?? ''));
            if (\str_contains($supplierId, 'mock') || \str_contains($supplierId, 'placeholder')) {
                $violations[] = 'intake.synthetic_supplier_forbidden:' . $sku;
            }

            $missing = \array_values(\array_filter(
                self::REQUIRED_EVIDENCE,
                static fn (string $field): bool => !\is_string($record[$field] ?? null) || \trim($record[$field]) === ''
            ));

            if ($status === 'accepted') {
                ++$accepted;
                if ($missing !== []) {
                    $violations[] = 'intake.accepted_with_missing_evidence:' . $sku;
                }
            } else {
                ++$blocked;
                if (!in_array($status, ['blocked_external_evidence', 'blocked_no_verifiable_source', 'blocked_identity_conflict'], true)) {
                    $violations[] = 'intake.invalid_status:' . $sku;
                }
            }
        }

        $passed = $violations === [];
        $output->writeln($passed ? 'INTAKE BASELINE PASS' : 'INTAKE BASELINE FAIL');
        $output->writeln('Records: ' . \count($records));
        $output->writeln('Accepted: ' . $accepted);
        $output->writeln('Blocked: ' . $blocked);
        $output->writeln('Synthetic suppliers: forbidden');
        foreach ($violations as $violation) {
            $output->writeln('- ' . $violation);
        }

        return $passed ? Command::SUCCESS : Command::FAILURE;
    }
}
