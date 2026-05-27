<?php declare(strict_types=1);

namespace VeyluneTheme\Command;

use Shopware\Core\Framework\Log\Package;
use VeyluneTheme\Governance\GovernanceAuditService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'veylune:semantic:audit', description: 'Runs the internal Veylune semantic deployment gate.')]
#[Package('storefront')]
final class SemanticAuditCommand extends Command
{
    public function __construct(
        private readonly GovernanceAuditService $governanceAuditService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = $this->governanceAuditService->auditSemanticReferences();

        if ($result->passed()) {
            $output->writeln('Veylune semantic audit passed.');

            return Command::SUCCESS;
        }

        $output->writeln('Veylune semantic audit failed.');

        foreach ($result->violations() as $violation) {
            $output->writeln('- ' . $violation);
        }

        return Command::FAILURE;
    }
}
