<?php declare(strict_types=1);

namespace VeyluneTheme\Discovery;

use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

#[Package('storefront')]
final class ProductExposureService
{
    /**
     * @var array<string, array{
     *     approved: bool,
     *     founderApproved: bool,
     *     newArrivalUntil?: string,
     *     categories: list<string>,
     *     rooms: list<string>,
     *     collections: list<string>,
     *     materials: list<string>,
     *     consultation: bool
     * }>
     */
    private const EXPOSURE_REGISTRY = [
        'VLS-SOF-001' => [
            'approved' => true,
            'founderApproved' => true,
            'newArrivalUntil' => '2026-07-20',
            'categories' => ['furniture'],
            'rooms' => ['living-room'],
            'collections' => ['founder-selection', 'new-arrivals'],
            'materials' => ['fabric'],
            'consultation' => true,
        ],
        'VLS-SOF-003' => [
            'approved' => true,
            'founderApproved' => true,
            'newArrivalUntil' => '2026-07-20',
            'categories' => ['furniture', 'dining-kitchen'],
            'rooms' => ['living-room', 'dining-room'],
            'collections' => ['founder-selection', 'new-arrivals'],
            'materials' => ['travertine'],
            'consultation' => true,
        ],
        'VLS-SOF-002' => [
            'approved' => false,
            'founderApproved' => false,
            'categories' => ['lighting'],
            'rooms' => ['living-room'],
            'collections' => [],
            'materials' => [],
            'consultation' => false,
        ],
        'VLS-SOF-004' => [
            'approved' => false,
            'founderApproved' => false,
            'categories' => ['decor-objects'],
            'rooms' => ['living-room'],
            'collections' => [],
            'materials' => [],
            'consultation' => false,
        ],
    ];

    private const GOVERNED_MATERIAL_OPTIONS = [
        'Polsterstoff' => 'fabric',
        'Upholstery Fabric' => 'fabric',
        'Travertin' => 'travertine',
        'Travertine' => 'travertine',
    ];

    /**
     * @param SalesChannelRepository<\Shopware\Core\Content\Product\SalesChannel\SalesChannelProductCollection> $productRepository
     */
    public function __construct(
        private readonly SalesChannelRepository $productRepository
    ) {
    }

    /**
     * @return list<SalesChannelProductEntity>
     */
    public function productsForSurface(string $surfaceType, string $surfaceKey, SalesChannelContext $context): array
    {
        $products = $this->loadRegistryProducts($context);
        $eligible = [];

        foreach ($products as $product) {
            if (!$this->isEligibleForSurface($product, $surfaceType, $surfaceKey)) {
                continue;
            }

            $eligible[] = $product;
        }

        return $eligible;
    }

    /**
     * @return array<string, list<SalesChannelProductEntity>>
     */
    public function homepageProducts(SalesChannelContext $context): array
    {
        return [
            'founder-selection' => $this->productsForSurface('collection', 'founder-selection', $context),
            'new-arrivals' => $this->productsForSurface('collection', 'new-arrivals', $context),
        ];
    }

    /**
     * @return array<string, array{eligible: int, rejected: int, reasons: list<string>}>
     */
    public function surfaceAudit(SalesChannelContext $context): array
    {
        $surfaces = [
            'Founder Selection' => ['collection', 'founder-selection'],
            'New Arrivals' => ['collection', 'new-arrivals'],
            'Furniture' => ['category', 'furniture'],
            'Lighting' => ['category', 'lighting'],
            'Decor Objects' => ['category', 'decor-objects'],
            'Textiles & Rugs' => ['category', 'textiles-rugs'],
            'Dining & Kitchen' => ['category', 'dining-kitchen'],
            'Outdoor Category' => ['category', 'outdoor'],
            'Living Room' => ['room', 'living-room'],
            'Dining Room' => ['room', 'dining-room'],
            'Bedroom' => ['room', 'bedroom'],
            'Workspace' => ['room', 'workspace'],
            'Hallway' => ['room', 'hallway'],
            'Outdoor Room' => ['room', 'outdoor'],
        ];

        $products = $this->loadRegistryProducts($context);
        $audit = [];

        foreach ($surfaces as $label => [$type, $key]) {
            $eligible = 0;
            $reasons = [];

            foreach ($products as $product) {
                $surfaceReasons = $this->rejectionReasons($product, $type, $key);

                if ($surfaceReasons === []) {
                    ++$eligible;
                    continue;
                }

                foreach ($surfaceReasons as $reason) {
                    $reasons[$reason] = $reason;
                }
            }

            $audit[$label] = [
                'eligible' => $eligible,
                'rejected' => \count($products) - $eligible,
                'reasons' => array_values($reasons),
            ];
        }

        return $audit;
    }

    /**
     * @return list<SalesChannelProductEntity>
     */
    private function loadRegistryProducts(SalesChannelContext $context): array
    {
        $criteria = (new Criteria())
            ->addFilter(new EqualsAnyFilter('productNumber', array_keys(self::EXPOSURE_REGISTRY)))
            ->addFilter(new EqualsFilter('active', true))
            ->addFilter(new EqualsFilter('available', true))
            ->addAssociation('cover.media')
            ->addAssociation('manufacturer')
            ->addAssociation('categories')
            ->addAssociation('properties.group')
            ->addAssociation('options.group');

        $criteria->getAssociation('visibilities')
            ->addFilter(new EqualsFilter('salesChannelId', $context->getSalesChannelId()))
            ->addFilter(new EqualsFilter('visibility', ProductVisibilityDefinition::VISIBILITY_ALL));

        return array_values($this->productRepository->search($criteria, $context)->getEntities()->getElements());
    }

    private function isEligibleForSurface(SalesChannelProductEntity $product, string $surfaceType, string $surfaceKey): bool
    {
        return $this->rejectionReasons($product, $surfaceType, $surfaceKey) === [];
    }

    /**
     * @return list<string>
     */
    private function rejectionReasons(SalesChannelProductEntity $product, string $surfaceType, string $surfaceKey): array
    {
        $productNumber = (string) $product->getProductNumber();
        $registry = self::EXPOSURE_REGISTRY[$productNumber] ?? null;
        $reasons = [];

        if ($registry === null || !($registry['approved'] ?? false)) {
            $reasons[] = 'missing exposure approval';
        }

        if (!str_starts_with($productNumber, 'VLS-')) {
            $reasons[] = 'demo or non-Veylune product';
        }

        if (!$product->getActive()) {
            $reasons[] = 'inactive product';
        }

        if (!$product->getAvailable()) {
            $reasons[] = 'unverified availability';
        }

        if ($product->getCalculatedPrice() === null || $product->getCalculatedPrice()->getUnitPrice() <= 0) {
            $reasons[] = 'missing valid price';
        }

        if ($product->getCover()?->getMedia() === null) {
            $reasons[] = 'missing primary image';
        }

        if ($product->getCategories() === null || $product->getCategories()->count() === 0) {
            $reasons[] = 'missing category attribution';
        }

        if ($this->materialKeys($product) === []) {
            $reasons[] = 'missing governed material attribution';
        }

        if ($registry !== null && array_diff($registry['materials'], $this->materialKeys($product)) !== []) {
            $reasons[] = 'registry material mismatch';
        }

        if ($surfaceType === 'collection') {
            if (!\in_array($surfaceKey, $registry['collections'] ?? [], true)) {
                $reasons[] = 'surface not approved';
            }

            if ($surfaceKey === 'founder-selection' && !($registry['founderApproved'] ?? false)) {
                $reasons[] = 'missing founder approval';
            }

            if ($surfaceKey === 'new-arrivals' && !$this->isInsideArrivalWindow($registry['newArrivalUntil'] ?? null)) {
                $reasons[] = 'outside approved arrival window';
            }
        }

        if ($surfaceType === 'category' && !\in_array($surfaceKey, $registry['categories'] ?? [], true)) {
            $reasons[] = 'category not approved';
        }

        if ($surfaceType === 'room' && !\in_array($surfaceKey, $registry['rooms'] ?? [], true)) {
            $reasons[] = 'room relevance not approved';
        }

        return array_values(array_unique($reasons));
    }

    /**
     * @return list<string>
     */
    private function materialKeys(SalesChannelProductEntity $product): array
    {
        $materials = [];
        $properties = $product->getProperties();

        if ($properties === null) {
            return [];
        }

        foreach ($properties as $property) {
            $groupName = $property->getGroup()?->getTranslated()['name'] ?? $property->getGroup()?->getName();

            if ($groupName !== 'Material') {
                continue;
            }

            $materialName = $property->getTranslated()['name'] ?? $property->getName();

            if (!\is_string($materialName)) {
                continue;
            }

            $materialKey = self::GOVERNED_MATERIAL_OPTIONS[$materialName] ?? null;

            if ($materialKey !== null) {
                $materials[$materialKey] = $materialKey;
            }
        }

        return array_values($materials);
    }

    private function isInsideArrivalWindow(?string $arrivalUntil): bool
    {
        if ($arrivalUntil === null) {
            return false;
        }

        return new \DateTimeImmutable($arrivalUntil) >= new \DateTimeImmutable('today');
    }
}
