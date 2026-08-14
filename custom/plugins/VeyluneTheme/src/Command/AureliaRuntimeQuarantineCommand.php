<?php declare(strict_types=1);

namespace VeyluneTheme\Command;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(name: 'veylune:catalog:aurelia-quarantine', description: 'Fail-closes the identity-conflicted Aurelia product without deleting catalog data.')]
#[Package('storefront')]
final class AureliaRuntimeQuarantineCommand extends Command
{
    private const SKU = 'VLS-SOF-001';
    private const ID = '019e4c3232987add9b4d98318913cc4f';
    private const CONFIRM = 'QUARANTINE-AURELIA-RUNTIME';

    public function __construct(
        #[Autowire(service: 'product.repository')] private readonly EntityRepository $productRepository,
        #[Autowire(service: 'product_visibility.repository')] private readonly EntityRepository $visibilityRepository,
        private readonly Connection $connection
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('execute', null, InputOption::VALUE_NONE);
        $this->addOption('confirm', null, InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $row = $this->connection->fetchAssociative(
            'SELECT LOWER(HEX(id)) id, active, stock FROM product WHERE product_number = :sku',
            ['sku' => self::SKU]
        );
        if ($row === false || $row['id'] !== self::ID) {
            $output->writeln('FAIL: Aurelia identity drift');
            return Command::FAILURE;
        }

        $visibilityIds = $this->connection->fetchFirstColumn(
            'SELECT LOWER(HEX(pv.id)) FROM product_visibility pv
             INNER JOIN product p ON p.id = pv.product_id AND p.version_id = pv.product_version_id
             WHERE p.product_number = :sku',
            ['sku' => self::SKU]
        );

        if (!$input->getOption('execute')) {
            $output->writeln('PREFLIGHT PASS');
            $output->writeln('Active: ' . (int) $row['active']);
            $output->writeln('Visibility rows: ' . \count($visibilityIds));
            $output->writeln('Stock preserved: ' . (int) $row['stock']);
            return Command::SUCCESS;
        }

        if ((string) $input->getOption('confirm') !== self::CONFIRM) {
            $output->writeln('FAIL: invalid confirmation');
            return Command::FAILURE;
        }

        $context = Context::createDefaultContext();
        $this->productRepository->update([['id' => self::ID, 'active' => false]], $context);
        if ($visibilityIds !== []) {
            $this->visibilityRepository->delete(
                \array_map(static fn (string $id): array => ['id' => $id], $visibilityIds),
                $context
            );
        }

        $active = (int) $this->connection->fetchOne('SELECT active FROM product WHERE id = UNHEX(:id)', ['id' => self::ID]);
        $remaining = (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM product_visibility pv INNER JOIN product p ON p.id = pv.product_id AND p.version_id = pv.product_version_id WHERE p.product_number = :sku',
            ['sku' => self::SKU]
        );
        $passed = $active === 0 && $remaining === 0;
        $output->writeln($passed ? 'QUARANTINE PASS' : 'QUARANTINE FAIL');
        $output->writeln('Active: ' . $active);
        $output->writeln('Visibility rows: ' . $remaining);
        $output->writeln('Stock preserved: ' . (int) $row['stock']);
        return $passed ? Command::SUCCESS : Command::FAILURE;
    }
}
