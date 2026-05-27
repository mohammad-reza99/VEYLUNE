<?php declare(strict_types=1);

namespace VeyluneTheme\Command;

use Shopware\Core\Framework\Log\Package;
use VeyluneTheme\Governance\GovernanceAuditService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'veylune:runtime:topology-pressure-audit', description: 'Runs internal topology-pressure and storefront-emergence resistance checks.')]
#[Package('storefront')]
final class TopologyPressureAuditCommand extends Command
{
    public function __construct(
        private readonly GovernanceAuditService $governanceAuditService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = $this->governanceAuditService->auditTopologyPressure();

        if (!$result->passed()) {
            $output->writeln('Veylune topology-pressure audit failed.');
            foreach ($result->violations() as $violation) {
                $output->writeln('- ' . $violation);
            }

            return Command::FAILURE;
        }

        $output->writeln('Veylune topology-pressure audit passed.');
        $output->writeln('Internal simulated route candidates: ' . ($result->internalObservability()['simulatedRouteCandidates'] ?? 0));
        $output->writeln('Topology failure injections detected: ' . ($result->internalObservability()['topologyFailureInjections'] ?? 0));

        return Command::SUCCESS;
    }
}
