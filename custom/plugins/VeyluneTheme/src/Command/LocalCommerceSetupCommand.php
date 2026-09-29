<?php declare(strict_types=1);

namespace VeyluneTheme\Command;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Theme\ThemeService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use VeyluneTheme\Catalog\DraftCatalogManifest;
use VeyluneTheme\Testing\LocalCommerce;
use VeyluneTheme\Testing\LocalPaymentHandler;

#[AsCommand(name: 'veylune:test-commerce:setup', description: 'Create isolated local Shopware commerce fixtures, without publishing supplier candidates.')]
final class LocalCommerceSetupCommand extends Command
{
    public function __construct(
        private readonly Connection $db,
        private readonly DefinitionInstanceRegistry $registry,
        private readonly SystemConfigService $config,
        private readonly ThemeService $themes,
        private readonly string $environment
    ) { parent::__construct(); }

    protected function configure(): void
    {
        $this->addOption('apply', null, InputOption::VALUE_NONE, 'Create local fixtures; existing fixtures are preserved.');
        $this->addOption('reset-race-stock', null, InputOption::VALUE_NONE, 'Reset only the independent Sand/Charcoal race fixtures to 1/0 stock; requires --apply.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->environment !== 'dev' || getenv('DDEV_PROJECT') !== 'veylune-shopware') {
            throw new \RuntimeException('This setup is restricted to the Veylune DDEV development project.');
        }
        $context = Context::createDefaultContext();
        $sourceId = LocalCommerce::CANONICAL_CHANNEL;
        $source = $this->registry->getRepository('sales_channel')->search(new Criteria([$sourceId]), $context)->first();
        $domain = $this->db->fetchAssociative('SELECT LOWER(HEX(snippet_set_id)) snippet, LOWER(HEX(language_id)) language FROM sales_channel_domain WHERE url = :url', ['url' => 'https://veylune-shopware.ddev.site']);
        if (!$source || !$domain) { throw new \RuntimeException('Source storefront missing.'); }
        if (!$input->getOption('apply')) {
            $output->writeln('Plan: isolated local channel, 10 cloned test products plus one parent/two variant fixtures, local success/decline payment, fixed shipping and customer cancellation. Supplier products are preserved.');
            return Command::SUCCESS;
        }
        $write = fn (string $entity, array $data) => $this->registry->getRepository($entity)->upsert([$data], $context);
        $exists = fn (string $entity, string $id) => $this->registry->getRepository($entity)->search(new Criteria([$id]), $context)->first() !== null;
        $currency = $source->getCurrencyId();
        $tax = $this->db->fetchOne('SELECT LOWER(HEX(id)) FROM tax WHERE tax_rate = 19 LIMIT 1');
        $cms = $this->db->fetchOne("SELECT LOWER(HEX(id)) FROM cms_page WHERE type = 'product_detail' LIMIT 1");
        if (!$tax || !$cms) { throw new \RuntimeException('Test tax or product CMS template missing.'); }
        if (!$exists('category', LocalCommerce::ROOT)) {
            $write('category', ['id' => LocalCommerce::ROOT, 'name' => 'Veylune test catalog', 'active' => true, 'type' => 'page']);
        }
        foreach ([LocalCommerce::PAYMENT => 'Test payment - success (no charge)', LocalCommerce::DECLINED => 'Test payment - declined (no charge)'] as $id => $name) {
            if (!$exists('payment_method', $id)) {
                $write('payment_method', ['id' => $id, 'name' => $name, 'technicalName' => 'veylune_test_' . $id, 'active' => true, 'afterOrderEnabled' => true, 'handlerIdentifier' => LocalPaymentHandler::class]);
            }
        }
        if (!$exists('shipping_method', LocalCommerce::SHIPPING)) {
            $delivery = $this->db->fetchOne('SELECT LOWER(HEX(delivery_time_id)) FROM shipping_method WHERE id = UNHEX(:id)', ['id' => $source->getShippingMethodId()]);
            $rule = $this->db->fetchOne('SELECT LOWER(HEX(availability_rule_id)) FROM shipping_method WHERE id = UNHEX(:id)', ['id' => $source->getShippingMethodId()]);
            $write('shipping_method', ['id' => LocalCommerce::SHIPPING, 'name' => 'Test delivery - EUR 9.90', 'technicalName' => 'veylune_local_test_shipping', 'active' => true, 'deliveryTimeId' => $delivery, 'availabilityRuleId' => $rule, 'prices' => [['id' => md5('veylune-test-shipping-price'), 'calculation' => 1, 'quantityStart' => 0, 'currencyPrice' => [['currencyId' => $currency, 'gross' => 9.90, 'net' => 9.90 / 1.19, 'linked' => true]]]]]);
        }
        if (!$exists('sales_channel', LocalCommerce::CHANNEL)) {
            $write('sales_channel', [
                'id' => LocalCommerce::CHANNEL, 'name' => 'Veylune local commerce test',
                'typeId' => $source->getTypeId(), 'languageId' => $domain['language'],
                'customerGroupId' => $source->getCustomerGroupId(), 'currencyId' => $currency,
                'paymentMethodId' => LocalCommerce::PAYMENT, 'shippingMethodId' => LocalCommerce::SHIPPING,
                'countryId' => $source->getCountryId(), 'navigationCategoryId' => LocalCommerce::ROOT,
                'accessKey' => Uuid::randomHex(), 'active' => true, 'homeEnabled' => false,
                'languages' => [['id' => $domain['language']]], 'currencies' => [['id' => $currency]],
                'countries' => [['id' => $source->getCountryId()]],
                'paymentMethods' => [['id' => LocalCommerce::PAYMENT], ['id' => LocalCommerce::DECLINED]],
                'shippingMethods' => [['id' => LocalCommerce::SHIPPING]],
                'domains' => [['id' => LocalCommerce::DOMAIN, 'url' => LocalCommerce::URL, 'languageId' => $domain['language'], 'currencyId' => $currency, 'snippetSetId' => $domain['snippet']]],
            ]);
        }
        // The canonical DDEV storefront uses the same no-charge methods so its
        // real Shopware checkout can be tested without an external provider.
        $write('sales_channel', [
            'id' => $sourceId,
            'paymentMethodId' => LocalCommerce::PAYMENT,
            'shippingMethodId' => LocalCommerce::SHIPPING,
            'paymentMethods' => [
                ['id' => LocalCommerce::PAYMENT],
                ['id' => LocalCommerce::DECLINED],
            ],
            'shippingMethods' => [['id' => LocalCommerce::SHIPPING]],
        ]);
        foreach (array_slice(DraftCatalogManifest::products(), 0, 10) as $index => $candidate) {
            $id = LocalCommerce::productId($index);
            $canonicalVisibility = [
                'id' => md5('veylune-canonical-test-visibility-' . $index),
                'salesChannelId' => $sourceId,
                'visibility' => 30,
            ];
            if ($exists('product', $id)) {
                $write('product', ['id' => $id, 'visibilities' => [$canonicalVisibility]]);
                continue;
            }
            $mediaId = $this->db->fetchOne('SELECT LOWER(HEX(pm.media_id)) FROM product p JOIN product_media pm ON p.product_media_id = pm.id AND p.product_media_version_id = pm.version_id WHERE p.product_number = :sku AND p.version_id = UNHEX(:version)', ['sku' => $candidate['sku'], 'version' => Defaults::LIVE_VERSION]);
            if (!$mediaId) { throw new \RuntimeException('Missing source media: ' . $candidate['sku']); }
            $association = md5('veylune-test-media-' . $index);
            $write('product', [
                'id' => $id, 'productNumber' => 'VLT-TEST-' . $candidate['id'],
                'name' => $candidate['nameEn'] . ' - Test product',
                'description' => '<p>Local test product. Images, price, stock and delivery are test fixtures. No purchase or fulfillment takes place.</p>',
                'active' => true, 'stock' => $index === 9 ? 0 : 20, 'isCloseout' => true,
                'taxId' => $tax, 'cmsPageId' => $cms, 'minPurchase' => 1, 'purchaseSteps' => 1,
                'price' => [['currencyId' => $currency, 'gross' => $candidate['price'], 'net' => $candidate['price'] / 1.19, 'linked' => true]],
                'categories' => [['id' => LocalCommerce::ROOT]],
                'visibilities' => [
                    ['id' => md5('veylune-test-visibility-' . $index), 'salesChannelId' => LocalCommerce::CHANNEL, 'visibility' => 30],
                    $canonicalVisibility,
                ],
                'media' => [['id' => $association, 'mediaId' => $mediaId, 'position' => 0]], 'coverId' => $association,
            ]);
        }
        // Independent variant fixtures: never reset stock on a repeated setup.
        $group = md5('veylune-local-variant-finish');
        $sand = md5('veylune-local-variant-sand');
        $charcoal = md5('veylune-local-variant-charcoal');
        $parent = md5('veylune-local-variant-parent');
        if (!$exists('property_group', $group)) {
            $write('property_group', ['id' => $group, 'name' => 'Test finish', 'displayType' => 'text', 'sortingType' => 'alphanumeric',
                'options' => [['id' => $sand, 'name' => 'Sand'], ['id' => $charcoal, 'name' => 'Charcoal']]]);
        }
        if (!$exists('product', $parent)) {
            $media = $this->db->fetchOne('SELECT LOWER(HEX(pm.media_id)) FROM product p JOIN product_media pm ON p.product_media_id=pm.id AND p.product_media_version_id=pm.version_id WHERE p.id=UNHEX(:id)', ['id' => LocalCommerce::productId(0)]);
            $cover = md5('veylune-local-variant-cover');
            $write('product', ['id' => $parent, 'productNumber' => 'VLT-TEST-VARIANT',
                'name' => 'Variant test sofa', 'description' => '<p>Local test fixture. No real purchase or delivery.</p>',
                'active' => true, 'stock' => 0, 'isCloseout' => true, 'taxId' => $tax, 'cmsPageId' => $cms,
                'price' => [['currencyId' => $currency, 'gross' => 100, 'net' => 100 / 1.19, 'linked' => true]],
                'media' => [['id' => $cover, 'mediaId' => $media, 'position' => 0]], 'coverId' => $cover,
                'configuratorSettings' => [['optionId' => $sand], ['optionId' => $charcoal]],
                'categories' => [['id' => LocalCommerce::ROOT]],
                'visibilities' => [['salesChannelId' => LocalCommerce::CHANNEL, 'visibility' => 30]]]);
        }
        foreach ([$sand => ['sand', 3, 100], $charcoal => ['charcoal', 0, 120]] as $option => [$key, $stock, $gross]) {
            $id = md5('veylune-local-variant-' . $key . '-product');
            if (!$exists('product', $id)) {
                $write('product', ['id' => $id, 'parentId' => $parent,
                    'productNumber' => 'VLT-TEST-V-' . strtoupper($key), 'active' => true,
                    'stock' => $stock, 'isCloseout' => true, 'minPurchase' => 1, 'purchaseSteps' => 1,
                    'options' => [['id' => $option]],
                    'price' => [['currencyId' => $currency, 'gross' => $gross, 'net' => $gross / 1.19, 'linked' => true]],
                    'visibilities' => [['salesChannelId' => LocalCommerce::CHANNEL, 'visibility' => 30]]]);
            }
        }
        if ($input->getOption('reset-race-stock')) {
            $write('product', ['id' => md5('veylune-local-variant-sand-product'), 'stock' => 1]);
            $write('product', ['id' => md5('veylune-local-variant-charcoal-product'), 'stock' => 0]);
            $output->writeln('Race fixtures reset explicitly: Sand=1, Charcoal=0. Supplier candidates were not changed.');
        }
        $this->config->set('core.basicInformation.shopName', 'Veylune - local commerce test', LocalCommerce::CHANNEL);
        $this->config->set('core.basicInformation.email', 'test@veylune.invalid', LocalCommerce::CHANNEL);
        $this->config->set('core.basicInformation.shopName', 'Veylune', $sourceId);
        $this->config->set('core.basicInformation.email', 'test@veylune.invalid', $sourceId);
        $this->config->set('core.loginRegistration.doubleOptInRegistration', false, LocalCommerce::CHANNEL);
        $this->config->set('core.loginRegistration.doubleOptInRegistration', false, $sourceId);
        // Shopware's setting enables customer order cancellation, not money refunds.
        $this->config->set('core.cart.enableOrderRefunds', true, LocalCommerce::CHANNEL);
        $theme = $this->db->fetchOne('SELECT LOWER(HEX(theme_id)) FROM theme_sales_channel WHERE sales_channel_id = UNHEX(:id)', ['id' => $sourceId]);
        $this->themes->assignTheme($theme, LocalCommerce::CHANNEL, $context);
        $output->writeln('Local commerce ready: ' . LocalCommerce::URL . '/test-products');
        $output->writeln('10 catalog fixtures plus Sand/Charcoal variants (?fixtures=variants). Last catalog product and Charcoal are out of stock. Local payment success/decline; cancellation is not a refund. No external payment provider contacted.');
        return Command::SUCCESS;
    }
}
