<?php declare(strict_types=1);

namespace VeyluneTheme\Command;

use Shopware\Core\Framework\Log\Package;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use VeyluneTheme\Governance\SitemapGovernanceAuditService;

#[AsCommand(name: 'veylune:sitemap:audit', description: 'Runs the internal Veylune identity sitemap governance gate.')]
#[Package('storefront')]
final class SitemapAuditCommand extends Command
{
    public function __construct(
        private readonly SitemapGovernanceAuditService $sitemapGovernanceAuditService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = $this->sitemapGovernanceAuditService->auditIdentityArtifacts();

        if (!$result->passed()) {
            $output->writeln('Veylune identity sitemap audit failed.');
            foreach ($result->violations() as $violation) {
                $output->writeln('- ' . $violation);
            }

            return Command::FAILURE;
        }

        $output->writeln('Veylune identity sitemap audit passed.');
        $output->writeln('Checked artifacts: ' . ($result->internalObservability()['checkedArtifacts'] ?? 0));
        $output->writeln('Checked URLs: ' . ($result->internalObservability()['checkedUrls'] ?? 0));

        return Command::SUCCESS;
    }
}
