<?php declare(strict_types=1);

namespace VeyluneTheme\Preview;

use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use VeyluneTheme\Catalog\DraftCatalogManifest;

final class DraftCatalogPreviewService
{
    private const EUR_CURRENCY_ID = 'b7d2554b0ce847cd82f3ac9bd1c0dfca';
    private const HOMEPAGE_RAIL_LIMITS = [
        'new-arrivals' => 16,
        'founder-selection' => 10,
        'living-room' => 12,
    ];

    /**
     * @param EntityRepository<\Shopware\Core\Content\Product\ProductCollection> $productRepository
     */
    public function __construct(
        private readonly EntityRepository $productRepository
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function products(): array
    {
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('customFields.veylune_source_batch', DraftCatalogManifest::BATCH_ID))
            ->addFilter(new EqualsFilter('active', false))
            ->addAssociation('properties.group');
        $criteria->addSorting(new \Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting('productNumber'));

        $products = [];
        foreach ($this->productRepository->search($criteria, Context::createDefaultContext())->getEntities() as $product) {
            $products[] = $this->project($product);
        }

        return $products;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function forCategory(string $categoryKey): array
    {
        return $this->filter(static fn (array $product): bool => $product['department'] === $categoryKey);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function forRoom(string $roomKey): array
    {
        return $this->filter(static fn (array $product): bool => \in_array($roomKey, $product['rooms'], true));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function forCollection(string $collectionKey): array
    {
        return $this->filter(static fn (array $product): bool => \in_array($collectionKey, $product['collections'], true));
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    public function homepageRails(): array
    {
        return [
            'new-arrivals' => \array_slice(
                $this->filter(static fn (array $product): bool => \in_array('New Arrivals', $product['rails'], true)),
                0,
                self::HOMEPAGE_RAIL_LIMITS['new-arrivals']
            ),
            'founder-selection' => \array_slice(
                $this->forCollection('founder_selection'),
                0,
                self::HOMEPAGE_RAIL_LIMITS['founder-selection']
            ),
            'living-room' => \array_slice(
                $this->forRoom('living_room'),
                0,
                self::HOMEPAGE_RAIL_LIMITS['living-room']
            ),
        ];
    }

    /**
     * @param callable(array<string, mixed>): bool $predicate
     *
     * @return list<array<string, mixed>>
     */
    private function filter(callable $predicate): array
    {
        return \array_values(\array_filter($this->products(), $predicate));
    }

    /**
     * @return array<string, mixed>
     */
    private function project(ProductEntity $product): array
    {
        $customFields = $product->getCustomFields() ?? [];
        $price = $product->getPrice()?->getCurrencyPrice(self::EUR_CURRENCY_ID);
        $materials = [];
        $rooms = [];
        $collections = [];

        foreach ($product->getProperties() ?? [] as $property) {
            $groupName = $property->getGroup()?->getTranslated()['name'] ?? $property->getGroup()?->getName();
            $key = $property->getCustomFieldsValue('veylune_canonical_key');

            if (!\is_string($key)) {
                continue;
            }

            if ($groupName === 'Veylune Material') {
                $materials[] = $key;
            } elseif ($groupName === 'Veylune Room') {
                $rooms[] = $key;
            } elseif ($groupName === 'Veylune Collection') {
                $collections[] = $key;
            }
        }

        $primaryMaterial = (string) ($customFields['veylune_primary_material_key'] ?? 'material');

        return [
            'recordId' => (string) ($customFields['veylune_catalog_record_id'] ?? ''),
            'name' => (string) ($product->getTranslated()['name'] ?? $product->getName() ?? ''),
            'targetPrice' => $price?->getGross() ?? (float) ($customFields['veylune_target_price_gross'] ?? 0),
            'status' => (string) ($customFields['veylune_status_copy'] ?? 'Supplier Selection'),
            'department' => (string) ($customFields['veylune_department_key'] ?? ''),
            'productType' => (string) ($customFields['veylune_product_type_key'] ?? ''),
            'material' => $primaryMaterial,
            'materialLabel' => DraftCatalogManifest::materials()[$primaryMaterial]['en'] ?? \ucwords(\str_replace('_', ' ', $primaryMaterial)),
            'materials' => $materials,
            'rooms' => $rooms,
            'collections' => $collections,
            'rails' => $this->decodeList($customFields['veylune_rail_candidates'] ?? null),
        ];
    }

    /**
     * @return list<string>
     */
    private function decodeList(mixed $value): array
    {
        if (!\is_string($value) || $value === '') {
            return [];
        }

        $decoded = \json_decode($value, true);

        return \is_array($decoded) ? \array_values(\array_filter($decoded, 'is_string')) : [];
    }
}
