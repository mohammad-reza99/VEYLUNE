<?php declare(strict_types=1);

namespace VeyluneTheme\Command;

use Shopware\Core\Framework\Log\Package;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use VeyluneTheme\Catalog\SupplierEvidenceContract;

#[AsCommand(name: 'veylune:catalog:level1-readiness-evaluate', description: 'Evaluates Level 1 evidence readiness for the fixed first-ten cohort without catalog writes.')]
#[Package('storefront')]
final class Level1ReadinessEvaluationCommand extends Command
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $intake = require $this->projectDir . '/custom/plugins/VeyluneTheme/src/Resources/config/level3_supplier_intake.php';
        $records = \is_array($intake['records'] ?? null) ? $intake['records'] : [];
        $ready = 0;
        $blocked = 0;
        $missingTotal = 0;

        $output->writeln('LEVEL 1 EVALUATION COMPLETE');
        foreach ($records as $record) {
            $missing = \array_values(\array_filter(
                SupplierEvidenceContract::requiredFields(),
                static fn (string $field): bool => !\is_string($record[$field] ?? null) || \trim($record[$field]) === ''
            ));
            $sku = (string) ($record['veylune_sku'] ?? 'unknown');
            if ($missing === [] && ($record['status'] ?? '') === SupplierEvidenceContract::STATUS_ACCEPTED) {
                ++$ready;
                $output->writeln($sku . ': READY');
                continue;
            }

            ++$blocked;
            $missingTotal += \count($missing);
            $output->writeln($sku . ': BLOCKED (' . \implode(', ', $missing) . ')');
        }

        $output->writeln('Level 1 ready: ' . $ready);
        $output->writeln('Blocked: ' . $blocked);
        $output->writeln('Missing evidence cells: ' . $missingTotal);

        return Command::SUCCESS;
    }
}
