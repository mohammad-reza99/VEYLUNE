<?php declare(strict_types=1);

namespace VeyluneTheme\Command;

use Shopware\Core\Framework\Log\Package;
use VeyluneTheme\Governance\GovernanceAuditService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'veylune:publication-state:audit', description: 'Runs the internal Veylune publication-state enforcement gate.')]
#[Package('storefront')]
final class PublicationStateAuditCommand extends Command
{
    public function __construct(
        private readonly GovernanceAuditService $governanceAuditService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = $this->governanceAuditService->auditPublicationStates();

        if (!$result->passed()) {
            $output->writeln('Veylune publication-state audit failed.');
            foreach ($result->violations() as $violation) {
                $output->writeln('- ' . $violation);
            }

            return Command::FAILURE;
        }

        $output->writeln('Veylune publication-state audit passed.');

        return Command::SUCCESS;
    }
}
