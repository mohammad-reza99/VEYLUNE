<?php declare(strict_types=1);

namespace VeyluneTheme\Command;

use Shopware\Core\Framework\Log\Package;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use VeyluneTheme\Catalog\SupplierEvidenceAcceptanceGate;
use VeyluneTheme\Discovery\ProductExposureService;

#[AsCommand(name: 'veylune:catalog:activation-audit', description: 'Reports the fail-closed public catalog activation gate without changing product state.')]
#[Package('storefront')]
final class CatalogActivationAuditCommand extends Command
{
    public function __construct(
        private readonly SupplierEvidenceAcceptanceGate $supplierEvidenceGate
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $ready = 0;
        $blocked = 0;

        $output->writeln('CATALOG ACTIVATION GATE');

        foreach (ProductExposureService::governedProductNumbers() as $productNumber) {
            $review = $this->supplierEvidenceGate->review($productNumber);

            if ($review['accepted']) {
                ++$ready;
                $output->writeln($productNumber . ': READY');
                continue;
            }

            ++$blocked;
            $details = $review['reasons'];

            if ($review['missing'] !== []) {
                $details[] = 'missing ' . implode(', ', $review['missing']);
            }

            $output->writeln($productNumber . ': BLOCKED (' . implode('; ', $details) . ')');
        }

        $output->writeln('Activation ready: ' . $ready);
        $output->writeln('Blocked: ' . $blocked);
        $output->writeln($ready > 0 ? 'Public activation may proceed to runtime review.' : 'Public exposure remains fail-closed.');

        return Command::SUCCESS;
    }
}
