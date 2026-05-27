<?php declare(strict_types=1);

namespace VeyluneTheme\Command;

use Shopware\Core\Framework\Log\Package;
use VeyluneTheme\Governance\GovernanceAuditService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'veylune:semantic:authoring-audit', description: 'Runs the internal Veylune semantic authoring workflow gate.')]
#[Package('storefront')]
final class SemanticAuthoringAuditCommand extends Command
{
    public function __construct(
        private readonly GovernanceAuditService $governanceAuditService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = $this->governanceAuditService->auditSemanticAuthoringWorkflow();

        if (!$result->passed()) {
            $output->writeln('Veylune semantic authoring audit failed.');
            foreach ($result->violations() as $violation) {
                $output->writeln('- ' . $violation);
            }

            return Command::FAILURE;
        }

        $output->writeln('Veylune semantic authoring audit passed.');
        $output->writeln('Blocked invalid contribution violations: ' . ($result->internalObservability()['blockedViolationCount'] ?? 0));

        return Command::SUCCESS;
    }
}
