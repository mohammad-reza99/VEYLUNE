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

#[AsCommand(name: 'veylune:catalog:calma-quarantine', description: 'Fail-closes the identity-conflicted Calma product without deleting its catalog data.')]
#[Package('storefront')]
final class CalmaRuntimeQuarantineCommand extends Command
{
    private const SKU = 'VLS-SOF-003';
    private const ID = '019e4c40e3bd7d46810671c170490cd5';
    private const CONFIRM = 'QUARANTINE-CALMA-RUNTIME';

    public function __construct(
        #[Autowire(service: 'product.repository')] private readonly EntityRepository $productRepository,
        #[Autowire(service: 'product_visibility.repository')] private readonly EntityRepository $visibilityRepository,
        private readonly Connection $connection
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('execute', null, InputOption::VALUE_NONE, 'Apply the quarantine.');
        $this->addOption('confirm', null, InputOption::VALUE_REQUIRED, 'Required execution confirmation.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $row = $this->connection->fetchAssociative(
            'SELECT LOWER(HEX(id)) id, active, stock FROM product WHERE product_number = :sku',
            ['sku' => self::SKU]
        );
        if ($row === false || $row['id'] !== self::ID) {
            $output->writeln('FAIL: Calma identity drift');
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
            $output->writeln('SKU: ' . self::SKU);
            $output->writeln('Active: ' . (int) $row['active']);
            $output->writeln('Stock preserved: ' . (int) $row['stock']);
            $output->writeln('Visibility rows: ' . \count($visibilityIds));
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

        $remaining = (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM product_visibility pv
             INNER JOIN product p ON p.id = pv.product_id AND p.version_id = pv.product_version_id
             WHERE p.product_number = :sku',
            ['sku' => self::SKU]
        );
        $active = (int) $this->connection->fetchOne('SELECT active FROM product WHERE id = UNHEX(:id)', ['id' => self::ID]);
        $passed = $active === 0 && $remaining === 0;
        $output->writeln($passed ? 'QUARANTINE PASS' : 'QUARANTINE FAIL');
        $output->writeln('Active: ' . $active);
        $output->writeln('Visibility rows: ' . $remaining);
        $output->writeln('Stock preserved: ' . (int) $row['stock']);

        return $passed ? Command::SUCCESS : Command::FAILURE;
    }
}
