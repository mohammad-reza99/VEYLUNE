<?php declare(strict_types=1);

namespace VeyluneTheme\Command;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'veylune:catalog:demo-cleanup',
    description: 'Preflights or executes the exact allowlisted Shopware demo-residue cleanup.'
)]
#[Package('storefront')]
final class DemoResidueCleanupCommand extends Command
{
    private const CONFIRMATION = 'DELETE-ALLOWLISTED-DEMO-RESIDUE';

    public function __construct(
        #[Autowire(service: 'product.repository')] private readonly EntityRepository $productRepository,
        #[Autowire(service: 'category.repository')] private readonly EntityRepository $categoryRepository,
        #[Autowire(service: 'property_group_option.repository')] private readonly EntityRepository $propertyGroupOptionRepository,
        #[Autowire(service: 'property_group.repository')] private readonly EntityRepository $propertyGroupRepository,
        #[Autowire(service: 'product_manufacturer.repository')] private readonly EntityRepository $manufacturerRepository,
        #[Autowire(service: 'media.repository')] private readonly EntityRepository $mediaRepository,
        #[Autowire(service: 'seo_url.repository')] private readonly EntityRepository $seoUrlRepository,
        private readonly Connection $connection,
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('action', InputArgument::REQUIRED, 'One of: preflight, execute.');
        $this->addOption('confirm', null, InputOption::VALUE_REQUIRED, 'Required destructive-operation confirmation.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $action = (string) $input->getArgument('action');

        try {
            $inventory = $this->inventory();
            $metrics = $this->preflight($inventory);

            if ($action === 'preflight') {
                $this->writeReport($output, 'PREFLIGHT PASS', $metrics);

                return Command::SUCCESS;
            }

            if ($action !== 'execute') {
                throw new \InvalidArgumentException('Unknown action. Use preflight or execute.');
            }

            if ((string) $input->getOption('confirm') !== self::CONFIRMATION) {
                throw new \RuntimeException('Destructive confirmation is missing or invalid.');
            }

            $result = $this->cleanup($inventory);
            $this->writeReport($output, 'CLEANUP PASS', [...$metrics, ...$result]);

            return Command::SUCCESS;
        } catch (\Throwable $exception) {
            $output->writeln('FAIL');
            $output->writeln($exception->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function inventory(): array
    {
        $path = $this->projectDir . '/custom/plugins/VeyluneTheme/src/Resources/config/demo_quarantine_inventory.php';
        $inventory = require $path;

        if (!\is_array($inventory)) {
            throw new \RuntimeException('Demo quarantine inventory is invalid.');
        }

        return $inventory;
    }

    /**
     * @param array<string, mixed> $inventory
     *
     * @return array<string, int>
     */
    private function preflight(array $inventory): array
    {
        if ((int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM plugin WHERE name = :name AND active = 0',
            ['name' => 'SwagPlatformDemoData']
        ) !== 1) {
            throw new \RuntimeException('SwagPlatformDemoData must be installed and inactive.');
        }

        $residue = $inventory['residue'] ?? [];
        $products = [...($residue['products']['variant_ids'] ?? []), ...($residue['products']['parent_ids'] ?? [])];
        $categories = $residue['categories']['ids'] ?? [];
        $manufacturers = $residue['manufacturers']['ids'] ?? [];
        $groups = $residue['property_groups']['remove_ids'] ?? [];
        $options = $residue['material_property_options']['remove_ids'] ?? [];
        $media = $residue['media']['remove_ids'] ?? [];
        $seoUrls = $residue['seo_urls']['ids'] ?? [];

        $this->assertCount('products', 'product', $products);
        $this->assertCount('categories', 'category', $categories);
        $this->assertCount('manufacturers', 'product_manufacturer', $manufacturers);
        $this->assertCount('property groups', 'property_group', $groups);
        $this->assertCount('property options', 'property_group_option', $options);
        $this->assertCount('media', 'media', $media);
        $this->assertCount('SEO URLs', 'seo_url', $seoUrls);

        $nonDemoProducts = (int) $this->connection->fetchOne(
            "SELECT COUNT(*) FROM product WHERE LOWER(HEX(id)) IN (:ids) AND product_number NOT LIKE 'SWDEMO%'",
            ['ids' => array_map('strtolower', $products)],
            ['ids' => ArrayParameterType::STRING]
        );
        if ($nonDemoProducts !== 0) {
            throw new \RuntimeException('Allowlisted product IDs include a non-demo SKU.');
        }

        $externalChildren = (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM category WHERE LOWER(HEX(parent_id)) IN (:ids) AND LOWER(HEX(id)) NOT IN (:ids)',
            ['ids' => array_map('strtolower', $categories)],
            ['ids' => ArrayParameterType::STRING]
        );
        if ($externalChildren !== 0) {
            throw new \RuntimeException('An allowlisted category has a non-allowlisted child.');
        }

        $nonDemoPropertyReferences = (int) $this->connection->fetchOne(
            "SELECT COUNT(*)
             FROM product_property pp
             INNER JOIN product p ON p.id = pp.product_id AND p.version_id = pp.product_version_id
             INNER JOIN property_group_option pgo ON pgo.id = pp.property_group_option_id
             WHERE (LOWER(HEX(pgo.id)) IN (:optionIds) OR LOWER(HEX(pgo.property_group_id)) IN (:groupIds))
               AND LOWER(HEX(p.id)) NOT IN (:productIds)",
            [
                'optionIds' => array_map('strtolower', $options),
                'groupIds' => array_map('strtolower', $groups),
                'productIds' => array_map('strtolower', $products),
            ],
            [
                'optionIds' => ArrayParameterType::STRING,
                'groupIds' => ArrayParameterType::STRING,
                'productIds' => ArrayParameterType::STRING,
            ]
        );
        if ($nonDemoPropertyReferences !== 0) {
            throw new \RuntimeException('Allowlisted properties are referenced by non-demo products.');
        }

        $nonDemoMediaReferences = (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM product_media WHERE LOWER(HEX(media_id)) IN (:mediaIds) AND LOWER(HEX(product_id)) NOT IN (:productIds)',
            [
                'mediaIds' => array_map('strtolower', $media),
                'productIds' => array_map('strtolower', $products),
            ],
            [
                'mediaIds' => ArrayParameterType::STRING,
                'productIds' => ArrayParameterType::STRING,
            ]
        );
        $categoryMediaReferences = (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM category WHERE LOWER(HEX(media_id)) IN (:mediaIds)',
            ['mediaIds' => array_map('strtolower', $media)],
            ['mediaIds' => ArrayParameterType::STRING]
        );
        if ($nonDemoMediaReferences !== 0 || $categoryMediaReferences !== 0) {
            throw new \RuntimeException('Allowlisted media has a non-demo reference.');
        }

        return [
            'products' => \count($products),
            'categories' => \count($categories),
            'manufacturers' => \count($manufacturers),
            'propertyGroups' => \count($groups),
            'propertyOptions' => \count($options),
            'media' => \count($media),
            'seoUrls' => \count($seoUrls),
        ];
    }

    /**
     * @param array<string, mixed> $inventory
     *
     * @return array<string, int>
     */
    private function cleanup(array $inventory): array
    {
        $residue = $inventory['residue'];
        $context = Context::createDefaultContext();

        $this->delete($this->seoUrlRepository, $residue['seo_urls']['ids'], $context);
        $this->delete($this->productRepository, $residue['products']['variant_ids'], $context);
        $this->delete($this->productRepository, $residue['products']['parent_ids'], $context);

        $categoryIds = $this->connection->fetchFirstColumn(
            'SELECT LOWER(HEX(id)) FROM category WHERE LOWER(HEX(id)) IN (:ids) ORDER BY level DESC',
            ['ids' => array_map('strtolower', $residue['categories']['ids'])],
            ['ids' => ArrayParameterType::STRING]
        );
        $this->delete($this->categoryRepository, $categoryIds, $context);

        $this->delete($this->propertyGroupOptionRepository, $residue['material_property_options']['remove_ids'], $context);
        $this->delete($this->propertyGroupRepository, $residue['property_groups']['remove_ids'], $context);
        $this->delete($this->manufacturerRepository, $residue['manufacturers']['ids'], $context);
        $this->delete($this->mediaRepository, $residue['media']['remove_ids'], $context);

        return [
            'remainingDemoProducts' => (int) $this->connection->fetchOne("SELECT COUNT(*) FROM product WHERE product_number LIKE 'SWDEMO%'"),
            'remainingAllowlistedCategories' => $this->countIds('category', $residue['categories']['ids']),
            'remainingAllowlistedManufacturers' => $this->countIds('product_manufacturer', $residue['manufacturers']['ids']),
            'remainingAllowlistedPropertyGroups' => $this->countIds('property_group', $residue['property_groups']['remove_ids']),
            'remainingAllowlistedPropertyOptions' => $this->countIds('property_group_option', $residue['material_property_options']['remove_ids']),
            'remainingAllowlistedMedia' => $this->countIds('media', $residue['media']['remove_ids']),
            'remainingAllowlistedSeoUrls' => $this->countIds('seo_url', $residue['seo_urls']['ids']),
        ];
    }
    /**
     * @param list<string> $ids
     */
    private function assertCount(string $label, string $table, array $ids): void
    {
        $actual = $this->countIds($table, $ids);
        if ($actual > \count($ids)) {
            throw new \RuntimeException(\sprintf('%s allowlist drifted: expected at most %d, found %d.', $label, \count($ids), $actual));
        }
    }

    /**
     * @param list<string> $ids
     */
    private function countIds(string $table, array $ids): int
    {
        return (int) $this->connection->fetchOne(
            "SELECT COUNT(*) FROM `{$table}` WHERE LOWER(HEX(id)) IN (:ids)",
            ['ids' => array_map('strtolower', $ids)],
            ['ids' => ArrayParameterType::STRING]
        );
    }

    /**
     * @param list<string> $ids
     */
    private function delete(EntityRepository $repository, array $ids, Context $context): void
    {
        $ids = $this->connection->fetchFirstColumn(
            "SELECT LOWER(HEX(id)) FROM `{$repository->getDefinition()->getEntityName()}` WHERE LOWER(HEX(id)) IN (:ids)",
            ['ids' => array_map('strtolower', $ids)],
            ['ids' => ArrayParameterType::STRING]
        );

        if ($ids === []) {
            return;
        }

        $repository->delete(
            \array_map(static fn (string $id): array => ['id' => $id], $ids),
            $context
        );
    }

    /**
     * @param array<string, int> $metrics
     */
    private function writeReport(OutputInterface $output, string $title, array $metrics): void
    {
        $output->writeln($title);
        foreach ($metrics as $name => $value) {
            $output->writeln(\sprintf('%s: %d', $name, $value));
        }
    }
}
