<?php declare(strict_types=1);

namespace VeyluneTheme\Command;

use Shopware\Core\Framework\Log\Package;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use VeyluneTheme\Catalog\LaunchSimulationRunner;

#[AsCommand(name: 'veylune:catalog:launch-simulation', description: 'Runs the mock-only 100 product launch dry-run simulation.')]
#[Package('storefront')]
final class LaunchSimulationCommand extends Command
{
    public function __construct(
        private readonly LaunchSimulationRunner $launchSimulationRunner
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $report = $this->launchSimulationRunner->run();

        $output->writeln($report->passed() ? 'PASS' : 'FAIL');
        foreach ($report->metrics() as $metric => $value) {
            $output->writeln($metric . ': ' . $value);
        }

        foreach ($report->violations() as $violation) {
            $output->writeln('- ' . $violation);
        }

        return $report->passed() ? Command::SUCCESS : Command::FAILURE;
    }
}
