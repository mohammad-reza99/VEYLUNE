<?php declare(strict_types=1);

namespace VeyluneTheme\Controller;

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
        'permanent-collections' => ['title' => 'veylune.destination.collections.permanentCollections.title', 'text' => 'veylune.destination.collections.permanentCollections.text'],
        'editorial-collections' => ['title' => 'veylune.destination.collections.editorialCollections.title', 'text' => 'veylune.destination.collections.editorialCollections.text'],
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
    ];

    private const CATEGORY_SHORTCUTS = [
        ['key' => 'furniture', 'label' => 'veylune.home.discovery.categories.furniture', 'routeKey' => 'furniture'],
    ];

    private const CATEGORY_SHORTCUT_LABELS = [
        'cushions' => 'veylune.destination.categoryShortcuts.cushions',
        'floor-lamps' => 'veylune.destination.categoryShortcuts.floorLamps',
        'mirrors' => 'veylune.destination.categoryShortcuts.mirrors',
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

        if ($products === []) {
            throw new NotFoundHttpException();
        }

        $page = $this->genericPageLoader->load($request, $context);
        $page->getMetaInformation()?->setMetaTitle($this->translator->trans($destination['title']));
        $page->getMetaInformation()?->setMetaDescription($this->translator->trans('veylune.destination.meta.description'));

        return $this->renderStorefront('@Storefront/storefront/veylune/discovery-destination.html.twig', [
            'page' => $page,
            'veyluneDestinationType' => 'room',
            'veyluneDestinationKey' => $roomKey,
            'veyluneDestination' => $destination,
            'veyluneCategoryShortcuts' => self::CATEGORY_SHORTCUTS,
            'veyluneExposedProducts' => $products,
        ]);
    }

    #[Route(path: '/collections/permanent', name: 'frontend.veylune.discovery.collection.permanent', defaults: ['collectionKey' => 'permanent-collections'], methods: [Request::METHOD_GET])]
    #[Route(path: '/collections/editorial', name: 'frontend.veylune.discovery.collection.editorial', defaults: ['collectionKey' => 'editorial-collections'], methods: [Request::METHOD_GET])]
    #[Route(path: '/collections/{collectionKey}', name: 'frontend.veylune.discovery.collection', requirements: ['collectionKey' => 'founder-selection|new-arrivals|permanent-collections|editorial-collections'], methods: [Request::METHOD_GET])]
    public function collection(string $collectionKey, Request $request, SalesChannelContext $context): Response
    {
        $destination = self::COLLECTIONS[$collectionKey] ?? null;

        if ($destination === null) {
            throw new NotFoundHttpException();
        }

        $products = $this->productExposureService->productsForSurface('collection', $collectionKey, $context);

        if ($products === []) {
            throw new NotFoundHttpException();
        }

        $page = $this->genericPageLoader->load($request, $context);
        $page->getMetaInformation()?->setMetaTitle($this->translator->trans($destination['title']));
        $page->getMetaInformation()?->setMetaDescription($this->translator->trans('veylune.destination.meta.description'));

        return $this->renderStorefront('@Storefront/storefront/veylune/discovery-destination.html.twig', [
            'page' => $page,
            'veyluneDestinationType' => 'collection',
            'veyluneDestinationKey' => $collectionKey,
            'veyluneDestination' => $destination,
            'veyluneCategoryShortcuts' => [],
            'veyluneExposedProducts' => $products,
        ]);
    }

    #[Route(path: '/categories/{categoryKey}', name: 'frontend.veylune.discovery.category', requirements: ['categoryKey' => 'furniture|lighting|decor-objects|textiles-rugs|dining-kitchen|outdoor'], methods: [Request::METHOD_GET])]
    public function category(string $categoryKey, Request $request, SalesChannelContext $context): Response
    {
        $destination = self::CATEGORIES[$categoryKey] ?? null;

        if ($destination === null) {
            throw new NotFoundHttpException();
        }

        $products = $this->productExposureService->productsForSurface('category', $categoryKey, $context);

        if ($products === []) {
            throw new NotFoundHttpException();
        }

        $page = $this->genericPageLoader->load($request, $context);
        $page->getMetaInformation()?->setMetaTitle($this->translator->trans($destination['title']));
        $page->getMetaInformation()?->setMetaDescription($this->translator->trans('veylune.destination.meta.description'));

        return $this->renderStorefront('@Storefront/storefront/veylune/discovery-destination.html.twig', [
            'page' => $page,
            'veyluneDestinationType' => 'category',
            'veyluneDestinationKey' => $categoryKey,
            'veyluneDestination' => $destination,
            'veyluneCategoryShortcuts' => self::CATEGORY_SHORTCUTS,
            'veyluneDestinationShortcuts' => $this->buildDestinationShortcuts($destination['shortcuts']),
            'veyluneMaterialKeys' => $destination['materials'],
            'veyluneExposedProducts' => $products,
        ]);
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
}
