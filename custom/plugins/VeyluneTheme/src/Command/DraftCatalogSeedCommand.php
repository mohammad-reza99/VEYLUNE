<?php declare(strict_types=1);

namespace VeyluneTheme\Command;

use Shopware\Core\Framework\Log\Package;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use VeyluneTheme\Catalog\DraftCatalogSeeder;

#[AsCommand(
    name: 'veylune:catalog:draft-seed',
    description: 'Dry-runs, seeds, audits, or rolls back the private WP-CAT-04 draft catalog.'
)]
#[Package('storefront')]
final class DraftCatalogSeedCommand extends Command
{
    public function __construct(
        private readonly DraftCatalogSeeder $seeder
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('action', InputArgument::REQUIRED, 'One of: dry-run, seed, audit, rollback.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $action = (string) $input->getArgument('action');

        try {
            $report = match ($action) {
                'dry-run' => $this->seeder->dryRun(),
                'seed' => $this->seeder->seed(),
                'audit' => $this->seeder->audit(),
                'rollback' => $this->seeder->rollback(),
                default => throw new \InvalidArgumentException('Unknown action. Use dry-run, seed, audit, or rollback.'),
            };
        } catch (\Throwable $exception) {
            $output->writeln('FAIL');
            $output->writeln($exception->getMessage());

            return Command::FAILURE;
        }

        $output->writeln('PASS');
        $output->writeln('Action: ' . $action);
        foreach ($report as $metric => $value) {
            $output->writeln($metric . ': ' . $value);
        }

        return Command::SUCCESS;
    }
}
