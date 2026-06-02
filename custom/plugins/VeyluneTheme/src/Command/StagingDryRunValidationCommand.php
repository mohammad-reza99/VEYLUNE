<?php declare(strict_types=1);

namespace VeyluneTheme\Command;

use Shopware\Core\Framework\Log\Package;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use VeyluneTheme\Catalog\BatchValidationEngine;

#[AsCommand(name: 'veylune:catalog:staging-dry-run', description: 'Validates a staged catalog batch manifest without importing products.')]
#[Package('storefront')]
final class StagingDryRunValidationCommand extends Command
{
    public function __construct(
        private readonly BatchValidationEngine $batchValidationEngine
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('manifest', InputArgument::REQUIRED, 'Path to a JSON batch manifest.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = (string) $input->getArgument('manifest');
        if (!\is_file($path) || !\is_readable($path)) {
            $output->writeln('FAIL');
            $output->writeln('- manifest.not_readable');

            return Command::FAILURE;
        }

        $decoded = \json_decode((string) \file_get_contents($path), true);
        if (!\is_array($decoded)) {
            $output->writeln('FAIL');
            $output->writeln('- manifest.invalid_json');

            return Command::FAILURE;
        }

        $report = $this->batchValidationEngine->validate($decoded);

        $output->writeln($report->passed() ? 'PASS' : 'FAIL');
        $output->writeln('Items: ' . ($report->metrics()['items'] ?? 0));
        $output->writeln('Violations: ' . ($report->metrics()['violations'] ?? 0));

        foreach ($report->violations() as $violation) {
            $output->writeln('- ' . $violation);
        }

        return $report->passed() ? Command::SUCCESS : Command::FAILURE;
    }
}
