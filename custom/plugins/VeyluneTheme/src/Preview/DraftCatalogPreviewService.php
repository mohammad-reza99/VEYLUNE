<?php declare(strict_types=1);

namespace VeyluneTheme\Preview;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use VeyluneTheme\Catalog\CatalogProductProjector;
use VeyluneTheme\Catalog\DraftCatalogManifest;

final class DraftCatalogPreviewService
{
    private const HOMEPAGE_RAIL_LIMITS = [
        'new-arrivals' => 16,
        'founder-selection' => 10,
        'living-room' => 12,
    ];
    private const STOREFRONT_TEST_COHORT = [
        'F02', // Liora Curved Sofa
        'F03', // Oris Leather Lounge Chair
        'F05', // Edda Dining Chair
        'F10', // Elara Travertine Coffee Table
    ];
    /**
     * @param EntityRepository<\Shopware\Core\Content\Product\ProductCollection> $productRepository
     * @param EntityRepository<\Shopware\Core\Content\Media\MediaCollection> $mediaRepository
     */
    public function __construct(
        private readonly EntityRepository $productRepository,
        private readonly EntityRepository $mediaRepository,
        private readonly CatalogProductProjector $projector,
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
            ->addAssociation('properties.group')
            ->addAssociation('cover.media')
            ->addAssociation('media.media');
        $criteria->addSorting(new \Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting('productNumber'));

        $products = [];
        foreach ($this->productRepository->search($criteria, Context::createDefaultContext())->getEntities() as $product) {
            $products[] = $this->projector->project($product, false);
        }

        return $products;
    }

    /**
     * @return array<string, array{imageUrl: string, imageAlt: string}>
     */
    public function selectionMediaManifest(): array
    {
        $manifest = [];
        foreach ($this->products() as $product) {
            $recordId = (string) ($product['recordId'] ?? '');
            $coverUrl = (string) ($product['coverUrl'] ?? '');
            if ($recordId === '' || $coverUrl === '') {
                continue;
            }

            $manifest[$recordId] = [
                'imageUrl' => $coverUrl,
                'imageAlt' => (string) ($product['coverAlt'] ?? $product['name'] ?? ''),
            ];
        }

        return $manifest;
    }

    /**
     * @return array{url: string, alt: string, width: int|null, height: int|null}|null
     */
    public function editorialMedia(string $destinationId): ?array
    {
        if (!isset(EditorialMediaRegistry::destinations()[$destinationId])) {
            return null;
        }

        $media = $this->mediaRepository->search(
            new Criteria([EditorialMediaRegistry::mediaId($destinationId)]),
            Context::createDefaultContext()
        )->first();
        if ($media === null || $media->getUrl() === null) {
            return null;
        }
        $translated = $media->getTranslated();
        $metadata = $media->getMetaData() ?? [];

        return [
            'url' => $media->getUrl(),
            'alt' => (string) ($translated['alt'] ?? ''),
            'width' => isset($metadata['width']) ? (int) $metadata['width'] : null,
            'height' => isset($metadata['height']) ? (int) $metadata['height'] : null,
        ];
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
     * @return array<string, mixed>|null
     */
    public function forRecordId(string $recordId): ?array
    {
        foreach ($this->products() as $product) {
            if (\strtoupper(\trim((string) $product['recordId'])) === \strtoupper(\trim($recordId))) {
                return $product;
            }
        }

        return null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function search(string $query): array
    {
        $normalizedQuery = $this->normalizeSearchText($query);

        if ($normalizedQuery === '') {
            return [];
        }

        $tokens = \array_values(\array_filter(\explode(' ', $normalizedQuery)));
        $matches = [];

        foreach ($this->products() as $position => $product) {
            $name = $this->normalizeSearchText((string) ($product['name'] ?? ''));
            $recordId = $this->normalizeSearchText((string) ($product['recordId'] ?? ''));
            $material = $this->normalizeSearchText((string) ($product['materialLabel'] ?? ''));
            $searchable = $this->normalizeSearchText(\implode(' ', [
                (string) ($product['recordId'] ?? ''),
                (string) ($product['productNumber'] ?? ''),
                (string) ($product['name'] ?? ''),
                \strip_tags((string) ($product['description'] ?? '')),
                (string) ($product['department'] ?? ''),
                (string) ($product['productType'] ?? ''),
                (string) ($product['materialLabel'] ?? ''),
                \implode(' ', (array) ($product['materials'] ?? [])),
                \implode(' ', (array) ($product['rooms'] ?? [])),
                \implode(' ', (array) ($product['collections'] ?? [])),
                \implode(' ', (array) ($product['rails'] ?? [])),
            ]));

            if (!\array_all($tokens, static fn (string $token): bool => \str_contains($searchable, $token))) {
                continue;
            }

            $score = 0;
            $score += $recordId === $normalizedQuery ? 500 : 0;
            $score += $name === $normalizedQuery ? 300 : 0;
            $score += \str_starts_with($name, $normalizedQuery) ? 180 : 0;
            $score += \str_contains($name, $normalizedQuery) ? 120 : 0;
            $score += \str_contains($material, $normalizedQuery) ? 60 : 0;

            foreach ($tokens as $token) {
                $score += \str_contains($name, $token) ? 30 : 0;
                $score += \str_contains($material, $token) ? 10 : 0;
            }

            $matches[] = ['score' => $score, 'position' => $position, 'product' => $product];
        }

        \usort($matches, static fn (array $left, array $right): int =>
            $right['score'] <=> $left['score'] ?: $left['position'] <=> $right['position']
        );

        return \array_values(\array_map(
            static fn (array $match): array => $match['product'],
            $matches
        ));
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    public function homepageRails(): array
    {
        return [
            'storefront-test-cohort' => $this->storefrontTestCohort(),
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
     * @return list<array<string, mixed>>
     */
    private function storefrontTestCohort(): array
    {
        $productsByRecord = [];
        foreach ($this->products() as $product) {
            $productsByRecord[$product['recordId']] = $product;
        }

        return \array_values(\array_filter(\array_map(
            static fn (string $recordId): ?array => $productsByRecord[$recordId] ?? null,
            self::STOREFRONT_TEST_COHORT
        )));
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

    private function normalizeSearchText(string $value): string
    {
        $value = \mb_strtolower(\html_entity_decode($value, \ENT_QUOTES | \ENT_HTML5, 'UTF-8'));
        $value = \str_replace(['_', '-', '/', '&'], ' ', $value);

        return \trim((string) \preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value));
    }
}
