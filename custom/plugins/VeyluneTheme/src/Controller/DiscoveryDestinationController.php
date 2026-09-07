<?php declare(strict_types=1);

namespace VeyluneTheme\Controller;

use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Framework\Routing\StorefrontRouteScope;
use Shopware\Storefront\Page\GenericPageLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use VeyluneTheme\Discovery\ProductExposureService;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StorefrontRouteScope::ID]])]
#[Package('storefront')]
class DiscoveryDestinationController extends StorefrontController
{
    private const ROOMS = [
        'living-room' => ['title' => 'veylune.destination.rooms.livingRoom.title', 'text' => 'veylune.destination.rooms.livingRoom.text'],
        'dining-room' => ['title' => 'veylune.destination.rooms.diningRoom.title', 'text' => 'veylune.destination.rooms.diningRoom.text'],
        'bedroom' => ['title' => 'veylune.destination.rooms.bedroom.title', 'text' => 'veylune.destination.rooms.bedroom.text'],
        'workspace' => ['title' => 'veylune.destination.rooms.workspace.title', 'text' => 'veylune.destination.rooms.workspace.text'],
        'hallway' => ['title' => 'veylune.destination.rooms.hallway.title', 'text' => 'veylune.destination.rooms.hallway.text'],
        'outdoor' => ['title' => 'veylune.destination.rooms.outdoor.title', 'text' => 'veylune.destination.rooms.outdoor.text'],
    ];

    private const COLLECTIONS = [
        'founder-selection' => ['title' => 'veylune.destination.collections.founderSelection.title', 'text' => 'veylune.destination.collections.founderSelection.text'],
        'new-arrivals' => ['title' => 'veylune.destination.collections.newArrivals.title', 'text' => 'veylune.destination.collections.newArrivals.text'],
        'best-sellers' => ['title' => 'veylune.destination.collections.bestSellers.title', 'text' => 'veylune.destination.collections.bestSellers.text'],
        'sale' => ['title' => 'veylune.destination.collections.sale.title', 'text' => 'veylune.destination.collections.sale.text'],
        'permanent-collections' => ['title' => 'veylune.destination.collections.permanentCollections.title', 'text' => 'veylune.destination.collections.permanentCollections.text'],
        'editorial-collections' => ['title' => 'veylune.destination.collections.editorialCollections.title', 'text' => 'veylune.destination.collections.editorialCollections.text'],
    ];

    private const ROOM_SCENE_ASSETS = [
        'living-room' => 'veylune-room-living-v1.webp',
        'dining-room' => 'veylune-room-dining-v1.webp',
        'bedroom' => 'veylune-room-bedroom-v1.webp',
        'workspace' => 'veylune-room-workspace-v1.webp',
        'hallway' => 'veylune-category-decor-v1.webp',
        'outdoor' => 'veylune-room-outdoor-v1.webp',
    ];

    private const COLLECTION_SCENE_ASSETS = [
        'founder-selection' => 'veylune-promo-living-room-v1.webp',
        'new-arrivals' => 'veylune-home-hero-architectural-warm-v1.webp',
        'best-sellers' => 'veylune-room-living-v1.webp',
        'sale' => 'veylune-category-dining-v1.webp',
        'permanent-collections' => 'veylune-room-living-v1.webp',
        'editorial-collections' => 'veylune-promo-living-room-v1.webp',
    ];

    private const CATEGORIES = [
        'furniture' => [
            'title' => 'veylune.destination.categories.furniture.title',
            'text' => 'veylune.destination.categories.furniture.text',
            'shortcuts' => ['sofas', 'tables', 'storage', 'seating'],
            'materials' => ['wood', 'fabric', 'metal', 'travertine'],
        ],
        'lighting' => [
            'title' => 'veylune.destination.categories.lighting.title',
            'text' => 'veylune.destination.categories.lighting.text',
            'shortcuts' => ['pendants', 'floor-lamps', 'table-lamps'],
            'materials' => ['metal', 'fabric', 'stone'],
        ],
        'decor-objects' => [
            'title' => 'veylune.destination.categories.decorObjects.title',
            'text' => 'veylune.destination.categories.decorObjects.text',
            'shortcuts' => ['vessels', 'sculptural-objects', 'mirrors'],
            'materials' => ['stone', 'wood', 'metal', 'travertine'],
        ],
        'textiles-rugs' => [
            'title' => 'veylune.destination.categories.textilesRugs.title',
            'text' => 'veylune.destination.categories.textilesRugs.text',
            'shortcuts' => ['rugs', 'throws', 'cushions'],
            'materials' => ['fabric'],
        ],
        'dining-kitchen' => [
            'title' => 'veylune.destination.categories.diningKitchen.title',
            'text' => 'veylune.destination.categories.diningKitchen.text',
            'shortcuts' => ['tables', 'seating', 'tableware'],
            'materials' => ['stone', 'wood', 'metal', 'travertine'],
        ],
        'outdoor' => [
            'title' => 'veylune.destination.categories.outdoor.title',
            'text' => 'veylune.destination.categories.outdoor.text',
            'shortcuts' => ['outdoor-seating', 'outdoor-tables', 'planters'],
            'materials' => ['stone', 'wood', 'metal', 'travertine'],
        ],
        'bedding-bath' => [
            'title' => 'veylune.destination.categories.beddingBath.title',
            'text' => 'veylune.destination.categories.beddingBath.text',
            'shortcuts' => ['throws', 'cushions'],
            'materials' => ['fabric'],
        ],
        'mattresses' => [
            'title' => 'veylune.destination.categories.mattresses.title',
            'text' => 'veylune.destination.categories.mattresses.text',
            'shortcuts' => ['mattresses'],
            'materials' => ['fabric'],
        ],
        'rugs' => [
            'title' => 'veylune.destination.categories.rugs.title',
            'text' => 'veylune.destination.categories.rugs.text',
            'shortcuts' => ['rugs'],
            'materials' => ['fabric'],
        ],
        'organization' => [
            'title' => 'veylune.destination.categories.organization.title',
            'text' => 'veylune.destination.categories.organization.text',
            'shortcuts' => ['storage'],
            'materials' => ['wood', 'metal'],
        ],
    ];

    private const CATEGORY_SHORTCUTS = [
        ['key' => 'furniture', 'label' => 'veylune.home.discovery.categories.furniture', 'routeKey' => 'furniture'],
    ];

    private const CATEGORY_SHORTCUT_LABELS = [
        'cushions' => 'veylune.destination.categoryShortcuts.cushions',
        'floor-lamps' => 'veylune.destination.categoryShortcuts.floorLamps',
        'mirrors' => 'veylune.destination.categoryShortcuts.mirrors',
        'mattresses' => 'veylune.destination.categoryShortcuts.mattresses',
        'outdoor-seating' => 'veylune.destination.categoryShortcuts.outdoorSeating',
        'outdoor-tables' => 'veylune.destination.categoryShortcuts.outdoorTables',
        'pendants' => 'veylune.destination.categoryShortcuts.pendants',
        'planters' => 'veylune.destination.categoryShortcuts.planters',
        'rugs' => 'veylune.destination.categoryShortcuts.rugs',
        'sculptural-objects' => 'veylune.destination.categoryShortcuts.sculpturalObjects',
        'seating' => 'veylune.destination.categoryShortcuts.seating',
        'sofas' => 'veylune.destination.categoryShortcuts.sofas',
        'storage' => 'veylune.destination.categoryShortcuts.storage',
        'table-lamps' => 'veylune.destination.categoryShortcuts.tableLamps',
        'tables' => 'veylune.destination.categoryShortcuts.tables',
        'tableware' => 'veylune.destination.categoryShortcuts.tableware',
        'throws' => 'veylune.destination.categoryShortcuts.throws',
        'vessels' => 'veylune.destination.categoryShortcuts.vessels',
    ];

    public function __construct(
        private readonly GenericPageLoader $genericPageLoader,
        private readonly TranslatorInterface $translator,
        private readonly ProductExposureService $productExposureService
    ) {
    }

    #[Route(path: '/rooms/{roomKey}', name: 'frontend.veylune.discovery.room', requirements: ['roomKey' => 'living-room|dining-room|bedroom|workspace|hallway|outdoor'], methods: [Request::METHOD_GET])]
    public function room(string $roomKey, Request $request, SalesChannelContext $context): Response
    {
        $destination = self::ROOMS[$roomKey] ?? null;

        if ($destination === null) {
            throw new NotFoundHttpException();
        }

        $products = $this->productExposureService->productsForSurface('room', $roomKey, $context);
        $listing = $this->buildListingState($products, $request);

        $page = $this->genericPageLoader->load($request, $context);
        $page->getMetaInformation()?->setMetaTitle($this->translator->trans($destination['title']));
        $page->getMetaInformation()?->setMetaDescription($this->translator->trans('veylune.destination.meta.description'));
        $page->getMetaInformation()?->setCanonical($request->getSchemeAndHttpHost() . $request->getPathInfo());

        return $this->renderStorefront('@Storefront/storefront/veylune/discovery-destination.html.twig', [
            'page' => $page,
            'veyluneDestinationType' => 'room',
            'veyluneDestinationKey' => $roomKey,
            'veyluneDestination' => $destination,
            'veyluneCategoryShortcuts' => self::CATEGORY_SHORTCUTS,
            'veyluneExposedProducts' => $listing['products'],
            'veyluneListingTotal' => \count($products),
            'veyluneListingSort' => $listing['sort'],
            'veyluneListingMaterial' => $listing['material'],
            'veyluneListingMaterialOptions' => $listing['materialOptions'],
            'veyluneListingHasActiveFilter' => $listing['material'] !== '',
            'veyluneScene' => $this->buildSceneModel('room', $roomKey, $this->translator->trans($destination['title'])),
        ]);
    }

    #[Route(path: '/collections/permanent', name: 'frontend.veylune.discovery.collection.permanent', defaults: ['collectionKey' => 'permanent-collections'], methods: [Request::METHOD_GET])]
    #[Route(path: '/collections/editorial', name: 'frontend.veylune.discovery.collection.editorial', defaults: ['collectionKey' => 'editorial-collections'], methods: [Request::METHOD_GET])]
    #[Route(path: '/collections/{collectionKey}', name: 'frontend.veylune.discovery.collection', requirements: ['collectionKey' => 'founder-selection|new-arrivals|best-sellers|sale|permanent-collections|editorial-collections'], methods: [Request::METHOD_GET])]
    public function collection(string $collectionKey, Request $request, SalesChannelContext $context): Response
    {
        $destination = self::COLLECTIONS[$collectionKey] ?? null;

        if ($destination === null) {
            throw new NotFoundHttpException();
        }

        $products = $this->productExposureService->productsForSurface('collection', $collectionKey, $context);
        $listing = $this->buildListingState($products, $request);

        $page = $this->genericPageLoader->load($request, $context);
        $page->getMetaInformation()?->setMetaTitle($this->translator->trans($destination['title']));
        $page->getMetaInformation()?->setMetaDescription($this->translator->trans('veylune.destination.meta.description'));
        $page->getMetaInformation()?->setCanonical($request->getSchemeAndHttpHost() . $request->getPathInfo());

        return $this->renderStorefront('@Storefront/storefront/veylune/discovery-destination.html.twig', [
            'page' => $page,
            'veyluneDestinationType' => 'collection',
            'veyluneDestinationKey' => $collectionKey,
            'veyluneDestination' => $destination,
            'veyluneCategoryShortcuts' => [],
            'veyluneExposedProducts' => $listing['products'],
            'veyluneListingTotal' => \count($products),
            'veyluneListingSort' => $listing['sort'],
            'veyluneListingMaterial' => $listing['material'],
            'veyluneListingMaterialOptions' => $listing['materialOptions'],
            'veyluneListingHasActiveFilter' => $listing['material'] !== '',
            'veyluneScene' => $this->buildSceneModel('collection', $collectionKey, $this->translator->trans($destination['title'])),
        ]);
    }

    #[Route(path: '/categories/{categoryKey}', name: 'frontend.veylune.discovery.category', requirements: ['categoryKey' => 'furniture|lighting|decor-objects|textiles-rugs|dining-kitchen|outdoor|bedding-bath|mattresses|rugs|organization'], methods: [Request::METHOD_GET])]
    public function category(string $categoryKey, Request $request, SalesChannelContext $context): Response
    {
        $destination = self::CATEGORIES[$categoryKey] ?? null;

        if ($destination === null) {
            throw new NotFoundHttpException();
        }

        $products = $this->productExposureService->productsForSurface('category', $categoryKey, $context);
        $listing = $this->buildListingState($products, $request);

        $page = $this->genericPageLoader->load($request, $context);
        $page->getMetaInformation()?->setMetaTitle($this->translator->trans($destination['title']));
        $page->getMetaInformation()?->setMetaDescription($this->translator->trans('veylune.destination.meta.description'));
        $page->getMetaInformation()?->setCanonical($request->getSchemeAndHttpHost() . $request->getPathInfo());

        return $this->renderStorefront('@Storefront/storefront/veylune/discovery-destination.html.twig', [
            'page' => $page,
            'veyluneDestinationType' => 'category',
            'veyluneDestinationKey' => $categoryKey,
            'veyluneDestination' => $destination,
            'veyluneCategoryShortcuts' => self::CATEGORY_SHORTCUTS,
            'veyluneDestinationShortcuts' => $this->buildDestinationShortcuts($destination['shortcuts']),
            'veyluneMaterialKeys' => $destination['materials'],
            'veyluneExposedProducts' => $listing['products'],
            'veyluneListingTotal' => \count($products),
            'veyluneListingSort' => $listing['sort'],
            'veyluneListingMaterial' => $listing['material'],
            'veyluneListingMaterialOptions' => $listing['materialOptions'],
            'veyluneListingHasActiveFilter' => $listing['material'] !== '',
        ]);
    }

    /**
     * @param list<SalesChannelProductEntity> $products
     * @return array{
     *     products: list<SalesChannelProductEntity>,
     *     sort: string,
     *     material: string,
     *     materialOptions: array<string, int>
     * }
     */
    private function buildListingState(array $products, Request $request): array
    {
        $materialOptions = [];

        foreach ($products as $product) {
            foreach ($this->productMaterialKeys($product) as $materialKey) {
                $materialOptions[$materialKey] = ($materialOptions[$materialKey] ?? 0) + 1;
            }
        }

        ksort($materialOptions);

        $requestedMaterial = trim((string) $request->query->get('material', ''));
        $material = isset($materialOptions[$requestedMaterial]) ? $requestedMaterial : '';
        $sort = (string) $request->query->get('sort', 'curated');

        if (!\in_array($sort, ['curated', 'name', 'price-asc', 'price-desc'], true)) {
            $sort = 'curated';
        }

        if ($material !== '') {
            $products = array_values(array_filter(
                $products,
                fn (SalesChannelProductEntity $product): bool => \in_array($material, $this->productMaterialKeys($product), true)
            ));
        }

        if ($sort === 'name') {
            usort($products, static fn (SalesChannelProductEntity $left, SalesChannelProductEntity $right): int => strnatcasecmp(
                (string) ($left->getTranslation('name') ?? ''),
                (string) ($right->getTranslation('name') ?? '')
            ));
        }

        if ($sort === 'price-asc' || $sort === 'price-desc') {
            usort($products, static function (SalesChannelProductEntity $left, SalesChannelProductEntity $right) use ($sort): int {
                $comparison = ($left->getCalculatedPrice()?->getUnitPrice() ?? 0.0)
                    <=> ($right->getCalculatedPrice()?->getUnitPrice() ?? 0.0);

                return $sort === 'price-desc' ? -$comparison : $comparison;
            });
        }

        return [
            'products' => $products,
            'sort' => $sort,
            'material' => $material,
            'materialOptions' => $materialOptions,
        ];
    }

    /**
     * @return list<string>
     */
    private function productMaterialKeys(SalesChannelProductEntity $product): array
    {
        $keys = [];

        foreach ($product->getProperties() ?? [] as $property) {
            $groupName = $property->getGroup()?->getTranslated()['name'] ?? $property->getGroup()?->getName();

            if ($groupName !== 'Material') {
                continue;
            }

            $materialName = $property->getTranslated()['name'] ?? $property->getName();
            $materialKey = match ($materialName) {
                'Polsterstoff', 'Upholstery Fabric' => 'fabric',
                'Travertin', 'Travertine' => 'travertine',
                default => null,
            };

            if ($materialKey !== null) {
                $keys[$materialKey] = $materialKey;
            }
        }

        return array_values($keys);
    }

    /**
     * @param list<string> $shortcutKeys
     * @return list<array{key: string, label: string}>
     */
    private function buildDestinationShortcuts(array $shortcutKeys): array
    {
        return array_map(static fn (string $shortcutKey): array => [
            'key' => $shortcutKey,
            'label' => self::CATEGORY_SHORTCUT_LABELS[$shortcutKey],
        ], $shortcutKeys);
    }

    /**
     * @return array{
     *     asset: string,
     *     paths: list<array{key: string, label: string, text: string, route: string, routeParams: array<string, string>}>
     * }
     */
    private function buildSceneModel(string $surfaceType, string $surfaceKey, string $queryLabel): array
    {
        if ($surfaceType === 'room') {
            return [
                'asset' => self::ROOM_SCENE_ASSETS[$surfaceKey],
                'paths' => [
                    $this->scenePath('seating', 'room', 'frontend.veylune.discovery.category', ['categoryKey' => 'furniture']),
                    $this->scenePath('tables-storage', 'room', 'frontend.veylune.discovery.category', ['categoryKey' => 'organization']),
                    $this->scenePath('lighting', 'room', 'frontend.veylune.discovery.category', ['categoryKey' => 'lighting']),
                    $this->scenePath('textiles-objects', 'room', 'frontend.veylune.discovery.category', ['categoryKey' => 'textiles-rugs']),
                ],
            ];
        }

        return [
            'asset' => self::COLLECTION_SCENE_ASSETS[$surfaceKey],
            'paths' => [
                $this->scenePath('form', 'collection', 'frontend.veylune.discovery.category', ['categoryKey' => 'furniture']),
                $this->scenePath('material', 'collection', 'frontend.veylune.editions.page'),
                $this->scenePath('room', 'collection', 'frontend.veylune.discovery.room', ['roomKey' => 'living-room']),
                $this->scenePath('project', 'collection', 'frontend.veylune.consultation.page', ['context' => $queryLabel]),
            ],
        ];
    }

    /**
     * @param array<string, string> $routeParams
     * @return array{key: string, label: string, text: string, route: string, routeParams: array<string, string>}
     */
    private function scenePath(string $key, string $surfaceType, string $route, array $routeParams = []): array
    {
        $prefix = 'veylune.destination.livingIndex.scene.' . $surfaceType . 'Paths.' . $key;

        return [
            'key' => $key,
            'label' => $prefix . '.label',
            'text' => $prefix . '.text',
            'route' => $route,
            'routeParams' => $routeParams,
        ];
    }
}
