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
use VeyluneTheme\Catalog\DraftCatalogManifest;
use VeyluneTheme\Preview\DraftCatalogPreviewAccess;
use VeyluneTheme\Preview\DraftCatalogPreviewService;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StorefrontRouteScope::ID]])]
#[Package('storefront')]
final class DraftCatalogPreviewController extends StorefrontController
{
    private const CATEGORIES = [
        'furniture' => 'Furniture',
        'lighting' => 'Lighting',
        'decor-objects' => 'Decor & Objects',
        'textiles-rugs' => 'Textiles & Rugs',
        'dining-kitchen' => 'Dining & Kitchen',
        'outdoor' => 'Outdoor',
    ];

    private const CATEGORY_KEYS = [
        'decor-objects' => 'decor_objects',
        'textiles-rugs' => 'textiles_rugs',
        'dining-kitchen' => 'dining_kitchen',
    ];

    private const ROOMS = [
        'living-room' => 'Living Room',
        'dining-room' => 'Dining Room',
        'bedroom' => 'Bedroom',
        'workspace' => 'Workspace',
        'hallway' => 'Hallway',
    ];

    private const ROOM_KEYS = [
        'living-room' => 'living_room',
        'dining-room' => 'dining_room',
        'workspace' => 'home_office',
    ];

    private const COLLECTIONS = [
        'founder-selection' => 'Founder Selection',
        'new-arrivals' => 'New Arrivals',
    ];

    private const COLLECTION_KEYS = [
        'founder-selection' => 'founder_selection',
        'new-arrivals' => 'new_arrivals',
    ];

    public function __construct(
        private readonly GenericPageLoader $genericPageLoader,
        private readonly DraftCatalogPreviewAccess $access,
        private readonly DraftCatalogPreviewService $previewService
    ) {
    }

    #[Route(path: '/__veylune-preview/catalog', name: 'frontend.veylune.preview.catalog.home', methods: [Request::METHOD_GET])]
    public function home(Request $request, SalesChannelContext $context): Response
    {
        $this->denyUnlessAllowed($request);
        $page = $this->page($request, $context, 'Draft Catalog Preview');

        return $this->previewResponse('@Storefront/storefront/veylune/catalog-preview-home.html.twig', [
            'page' => $page,
            'veylunePreviewToken' => $this->access->token(),
            'veylunePreviewRails' => $this->previewService->homepageRails(),
            'veylunePreviewCategories' => self::CATEGORIES,
            'veylunePreviewRooms' => self::ROOMS,
            'veylunePreviewCollections' => self::COLLECTIONS,
        ]);
    }

    #[Route(path: '/__veylune-preview/catalog/category/{categoryKey}', name: 'frontend.veylune.preview.catalog.category', requirements: ['categoryKey' => 'furniture|lighting|decor-objects|textiles-rugs|dining-kitchen|outdoor'], methods: [Request::METHOD_GET])]
    public function category(string $categoryKey, Request $request, SalesChannelContext $context): Response
    {
        return $this->destination(
            $request,
            $context,
            'category',
            $categoryKey,
            self::CATEGORIES[$categoryKey],
            $this->previewService->forCategory(self::CATEGORY_KEYS[$categoryKey] ?? $categoryKey)
        );
    }

    #[Route(path: '/__veylune-preview/catalog/room/{roomKey}', name: 'frontend.veylune.preview.catalog.room', requirements: ['roomKey' => 'living-room|dining-room|bedroom|workspace|hallway'], methods: [Request::METHOD_GET])]
    public function room(string $roomKey, Request $request, SalesChannelContext $context): Response
    {
        return $this->destination(
            $request,
            $context,
            'room',
            $roomKey,
            self::ROOMS[$roomKey],
            $this->previewService->forRoom(self::ROOM_KEYS[$roomKey] ?? $roomKey)
        );
    }

    #[Route(path: '/__veylune-preview/catalog/collection/{collectionKey}', name: 'frontend.veylune.preview.catalog.collection', requirements: ['collectionKey' => 'founder-selection|new-arrivals'], methods: [Request::METHOD_GET])]
    public function collection(string $collectionKey, Request $request, SalesChannelContext $context): Response
    {
        $canonicalKey = self::COLLECTION_KEYS[$collectionKey];
        $products = $collectionKey === 'new-arrivals'
            ? $this->previewService->homepageRails()['new-arrivals']
            : $this->previewService->forCollection($canonicalKey);

        return $this->destination(
            $request,
            $context,
            'collection',
            $collectionKey,
            self::COLLECTIONS[$collectionKey],
            $products
        );
    }

    /**
     * @param list<array<string, mixed>> $products
     */
    private function destination(
        Request $request,
        SalesChannelContext $context,
        string $type,
        string $key,
        string $title,
        array $products
    ): Response {
        $this->denyUnlessAllowed($request);

        return $this->previewResponse('@Storefront/storefront/veylune/catalog-preview-destination.html.twig', [
            'page' => $this->page($request, $context, $title . ' Preview'),
            'veylunePreviewToken' => $this->access->token(),
            'veylunePreviewType' => $type,
            'veylunePreviewKey' => $key,
            'veylunePreviewTitle' => $title,
            'veylunePreviewProducts' => $products,
        ]);
    }

    private function denyUnlessAllowed(Request $request): void
    {
        if (!$this->access->isAllowed($request)) {
            throw new NotFoundHttpException();
        }
    }

    private function page(Request $request, SalesChannelContext $context, string $title): \Shopware\Storefront\Page\Page
    {
        $page = $this->genericPageLoader->load($request, $context);
        $page->getMetaInformation()?->setMetaTitle($title);
        $page->getMetaInformation()?->setRobots('noindex,nofollow,noarchive,nosnippet');

        return $page;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function previewResponse(string $view, array $parameters): Response
    {
        $response = $this->renderStorefront($view, $parameters);
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive, nosnippet');
        $response->headers->set('Cache-Control', 'private, no-store, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('X-Veylune-Preview', DraftCatalogManifest::BATCH_ID);

        return $response;
    }
}
