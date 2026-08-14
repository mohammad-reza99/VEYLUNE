<?php declare(strict_types=1);

namespace VeyluneTheme\Command;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(name: 'veylune:catalog:level3-baseline-audit', description: 'Audits the fixed first-ten Level 3 cohort without changing catalog state.')]
#[Package('storefront')]
final class Level3CohortBaselineAuditCommand extends Command
{
    public function __construct(
        private readonly Connection $connection,
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $register = require $this->projectDir . '/custom/plugins/VeyluneTheme/src/Resources/config/level3_cohort.php';
        $products = \is_array($register['products'] ?? null) ? $register['products'] : [];
        $violations = [];
        $seen = [];
        $legacy = 0;
        $drafts = 0;

        if (\count($products) !== 10) {
            $violations[] = 'cohort.must_contain_exactly_10_products';
        }

        foreach ($products as $candidate) {
            $sku = (string) ($candidate['sku'] ?? '');
            if ($sku === '' || isset($seen[$sku])) {
                $violations[] = 'cohort.invalid_or_duplicate_sku:' . $sku;
                continue;
            }
            $seen[$sku] = true;

            $rows = $this->connection->fetchAllAssociative(
                'SELECT LOWER(HEX(p.id)) id, p.active, p.stock, COUNT(DISTINCT pv.id) visibility_count
                 FROM product p
                 LEFT JOIN product_visibility pv ON pv.product_id = p.id AND pv.product_version_id = p.version_id
                 WHERE p.product_number = :sku
                 GROUP BY p.id, p.active, p.stock',
                ['sku' => $sku]
            );

            if (\count($rows) !== 1) {
                $violations[] = 'product.not_exactly_one:' . $sku;
                continue;
            }

            $row = $rows[0];
            $expectedActive = (bool) ($candidate['expected_active'] ?? false);
            if ((bool) $row['active'] !== $expectedActive) {
                $violations[] = 'product.active_state_drift:' . $sku;
            }

            if (($candidate['lane'] ?? '') === 'governed_draft') {
                ++$drafts;
                if ((int) $row['stock'] !== 0 || (int) $row['visibility_count'] !== 0) {
                    $violations[] = 'draft.not_fail_closed:' . $sku;
                }
            } else {
                ++$legacy;
            }
        }

        $evidence = \is_array($register['external_evidence'] ?? null) ? $register['external_evidence'] : [];
        $missingEvidence = \count(\array_filter($evidence, static fn (mixed $status): bool => $status === 'missing'));
        if ($missingEvidence !== 8) {
            $violations[] = 'evidence.baseline_must_remain_explicit';
        }

        $passed = $violations === [];
        $output->writeln($passed ? 'BASELINE PASS' : 'BASELINE FAIL');
        $output->writeln('Cohort products: ' . \count($products));
        $output->writeln('Legacy remediation: ' . $legacy);
        $output->writeln('Governed drafts: ' . $drafts);
        $output->writeln('Level 3 ready: 0');
        $output->writeln('Supplier/evidence blockers: ' . $missingEvidence);
        foreach ($violations as $violation) {
            $output->writeln('- ' . $violation);
        }

        return $passed ? Command::SUCCESS : Command::FAILURE;
    }
}
