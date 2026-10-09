<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

use Shopware\Core\Content\Product\SalesChannel\Search\AbstractProductSearchRoute;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\AndFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\PrefixFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

final class PublicCatalogService
{
    private const HOMEPAGE_RAIL_LIMITS = [
        'new-arrivals' => 16,
        'founder-selection' => 10,
        'living-room' => 12,
    ];
    private const STOREFRONT_TEST_COHORT = ['F02', 'F03', 'F05', 'F10'];

    /**
     * @param SalesChannelRepository<\Shopware\Core\Content\Product\SalesChannel\SalesChannelProductCollection> $productRepository
     */
    public function __construct(
        private readonly SalesChannelRepository $productRepository,
        private readonly AbstractProductSearchRoute $productSearchRoute,
        private readonly CatalogProductProjector $projector,
        private readonly string $environment,
    ) {
    }

    /** @return list<array<string, mixed>> */
    public function products(SalesChannelContext $context): array
    {
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('active', true))
            ->addFilter(new EqualsFilter('parentId', null))
            ->addFilter($this->sourceFilter())
            ->addAssociation('properties.group')
            ->addAssociation('cover.media')
            ->addAssociation('media.media')
            ->addSorting(new FieldSorting('productNumber'));

        $products = [];
        foreach ($this->productRepository->search($criteria, $context)->getEntities() as $product) {
            $projected = $this->projector->project($product, true);
            if ($projected['recordId'] !== '') {
                $products[] = $projected;
            }
        }

        return $products;
    }

    /** @return list<array<string, mixed>> */
    public function forCategory(string $categoryKey, SalesChannelContext $context): array
    {
        return $this->filter($context, static fn (array $product): bool => $product['department'] === $categoryKey);
    }

    /** @return list<array<string, mixed>> */
    public function forRoom(string $roomKey, SalesChannelContext $context): array
    {
        return $this->filter($context, static fn (array $product): bool => \in_array($roomKey, $product['rooms'], true));
    }

    /** @return list<array<string, mixed>> */
    public function forCollection(string $collectionKey, SalesChannelContext $context): array
    {
        return $this->filter($context, static fn (array $product): bool => \in_array($collectionKey, $product['collections'], true));
    }

    /** @return array<string, mixed>|null */
    public function forRecordId(string $recordId, SalesChannelContext $context): ?array
    {
        foreach ($this->products($context) as $product) {
            if (\strtoupper((string) $product['recordId']) === \strtoupper(\trim($recordId))) {
                return $product;
            }
        }

        return null;
    }

    /** @return list<array<string, mixed>> */
    public function search(string $query, SalesChannelContext $context): array
    {
        $query = \trim($query);
        if ($query === '') {
            return [];
        }

        $criteria = (new Criteria())
            ->addFilter($this->sourceFilter())
            ->addFilter(new EqualsFilter('parentId', null))
            ->addAssociation('properties.group')
            ->addAssociation('cover.media')
            ->addAssociation('media.media');
        $request = new Request(['search' => $query, 'limit' => 50]);
        $result = $this->productSearchRoute->load($request, $context, $criteria)->getListingResult();
        $products = [];
        foreach ($result->getEntities() as $product) {
            $projected = $this->projector->project($product, true);
            if ($projected['recordId'] !== '') {
                $products[] = $projected;
            }
        }

        return $products;
    }

    /** @return array<string, list<array<string, mixed>>> */
    public function homepageRails(SalesChannelContext $context): array
    {
        $products = $this->products($context);
        $byRecord = [];
        foreach ($products as $product) {
            $byRecord[$product['recordId']] = $product;
        }

        return [
            'storefront-test-cohort' => \array_values(\array_filter(\array_map(
                static fn (string $recordId): ?array => $byRecord[$recordId] ?? null,
                self::STOREFRONT_TEST_COHORT
            ))),
            'new-arrivals' => \array_slice(\array_values(\array_filter(
                $products,
                static fn (array $product): bool => \in_array('New Arrivals', $product['rails'], true)
            )), 0, self::HOMEPAGE_RAIL_LIMITS['new-arrivals']),
            'founder-selection' => \array_slice(\array_values(\array_filter(
                $products,
                static fn (array $product): bool => \in_array('founder_selection', $product['collections'], true)
            )), 0, self::HOMEPAGE_RAIL_LIMITS['founder-selection']),
            'living-room' => \array_slice(\array_values(\array_filter(
                $products,
                static fn (array $product): bool => \in_array('living_room', $product['rooms'], true)
            )), 0, self::HOMEPAGE_RAIL_LIMITS['living-room']),
        ];
    }

    /** @return array<string, bool> */
    public function surfaceAvailability(SalesChannelContext $context): array
    {
        $products = $this->products($context);
        $availability = [
            'catalog' => $products !== [],
            'rooms' => false,
            'collections' => false,
        ];
        $surfaces = [
            'category:furniture' => ['department', 'furniture'],
            'category:lighting' => ['department', 'lighting'],
            'category:decor-objects' => ['department', 'decor_objects'],
            'category:textiles-rugs' => ['department', 'textiles_rugs'],
            'category:dining-kitchen' => ['department', 'dining_kitchen'],
            'category:outdoor' => ['department', 'outdoor'],
            'room:living-room' => ['rooms', 'living_room'],
            'room:dining-room' => ['rooms', 'dining_room'],
            'room:bedroom' => ['rooms', 'bedroom'],
            'room:workspace' => ['rooms', 'home_office'],
            'room:hallway' => ['rooms', 'hallway'],
            'collection:founder-selection' => ['collections', 'founder_selection'],
            'collection:new-arrivals' => ['rails', 'New Arrivals'],
        ];

        foreach ($surfaces as $surface => [$field, $value]) {
            $availability[$surface] = \array_any($products, static function (array $product) use ($field, $value): bool {
                $candidate = $product[$field] ?? null;

                return \is_array($candidate) ? \in_array($value, $candidate, true) : $candidate === $value;
            });
            if ($availability[$surface] && \str_starts_with($surface, 'room:')) {
                $availability['rooms'] = true;
            }
            if ($availability[$surface] && \str_starts_with($surface, 'collection:')) {
                $availability['collections'] = true;
            }
        }

        return $availability;
    }

    /** @return list<array<string, mixed>> */
    private function filter(SalesChannelContext $context, callable $predicate): array
    {
        return \array_values(\array_filter($this->products($context), $predicate));
    }

    private function localFixturesAllowed(): bool
    {
        return $this->environment === 'dev' && \getenv('DDEV_PROJECT') === 'veylune-shopware';
    }

    private function sourceFilter(): OrFilter
    {
        $sources = [
            new AndFilter([
                new EqualsFilter('customFields.veylune_source_batch', DraftCatalogManifest::BATCH_ID),
                new EqualsFilter('customFields.veylune_publication_state', 'published'),
            ]),
        ];
        if ($this->localFixturesAllowed()) {
            $sources[] = new PrefixFilter('productNumber', 'VLT-TEST-');
        }

        return new OrFilter($sources);
    }
}
