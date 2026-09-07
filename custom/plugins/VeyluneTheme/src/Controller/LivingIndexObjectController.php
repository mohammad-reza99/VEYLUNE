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
use VeyluneTheme\Discovery\ProductExposureService;
use VeyluneTheme\Preview\DraftCatalogPreviewService;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StorefrontRouteScope::ID]])]
#[Package('storefront')]
final class LivingIndexObjectController extends StorefrontController
{
    private const ROOM_SCENE_ASSETS = [
        'living-room' => 'veylune-room-living-v1.webp',
        'dining-room' => 'veylune-room-dining-v1.webp',
        'bedroom' => 'veylune-room-bedroom-v1.webp',
        'workspace' => 'veylune-room-workspace-v1.webp',
        'hallway' => 'veylune-category-decor-v1.webp',
        'outdoor' => 'veylune-room-outdoor-v1.webp',
    ];

    private const PUBLIC_COLLECTION_KEYS = [
        'founder-selection',
        'new-arrivals',
        'best-sellers',
        'sale',
        'permanent-collections',
        'editorial-collections',
    ];

    public function __construct(
        private readonly GenericPageLoader $genericPageLoader,
        private readonly ProductExposureService $productExposureService,
        private readonly DraftCatalogPreviewService $draftCatalogPreviewService,
        private readonly string $environment
    ) {
    }

    #[Route(
        path: '/objects/{productNumber}',
        name: 'frontend.veylune.object.page',
        requirements: ['productNumber' => '[A-Z0-9][A-Z0-9-]{5,63}'],
        methods: [Request::METHOD_GET]
    )]
    public function object(string $productNumber, Request $request, SalesChannelContext $context): Response
    {
        $product = $this->productExposureService->publicProductByNumber($productNumber, $context);

        if ($product === null) {
            throw new NotFoundHttpException();
        }

        $viewModel = $this->publicViewModel($product);
        $page = $this->genericPageLoader->load($request, $context);
        $page->getMetaInformation()?->setMetaTitle($viewModel['name'] . ' | Veylune');
        $page->getMetaInformation()?->setMetaDescription($viewModel['metaDescription']);
        $page->getMetaInformation()?->setRobots('index,follow');

        return $this->renderStorefront('@VeyluneTheme/storefront/veylune/living-index-object.html.twig', [
            'page' => $page,
            'veyluneObject' => $viewModel,
            'veyluneObjectPreviewMode' => false,
        ]);
    }

    #[Route(
        path: '/__veylune-vision/object/{recordId}',
        name: 'frontend.veylune.vision.object',
        requirements: ['recordId' => 'F01|F10'],
        methods: [Request::METHOD_GET]
    )]
    public function visionObject(string $recordId, Request $request, SalesChannelContext $context): Response
    {
        if ($this->environment !== 'dev' || !str_ends_with($request->getHost(), '.ddev.site')) {
            throw new NotFoundHttpException();
        }

        $draft = $this->draftCatalogPreviewService->forRecordId($recordId);

        if ($draft === null || !\is_string($draft['coverAsset'] ?? null)) {
            throw new NotFoundHttpException();
        }

        $viewModel = $this->draftViewModel($draft);
        $page = $this->genericPageLoader->load($request, $context);
        $page->getMetaInformation()?->setMetaTitle($viewModel['name'] . ' Object Mode Probe');
        $page->getMetaInformation()?->setMetaDescription('Development-only visual verification for the Veylune Living Index Object Mode.');
        $page->getMetaInformation()?->setRobots('noindex,nofollow,noarchive,nosnippet');

        $response = $this->renderStorefront('@VeyluneTheme/storefront/veylune/living-index-object.html.twig', [
            'page' => $page,
            'veyluneObject' => $viewModel,
            'veyluneObjectPreviewMode' => true,
        ]);
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive, nosnippet');
        $response->headers->set('Cache-Control', 'private, no-store, max-age=0');

        return $response;
    }

    /**
     * @return array<string, mixed>
     */
    private function publicViewModel(SalesChannelProductEntity $product): array
    {
        $translated = $product->getTranslated();
        $translatedCustomFields = $translated['customFields'] ?? null;
        $customFields = \is_array($translatedCustomFields) ? $translatedCustomFields : ($product->getCustomFields() ?? []);
        $name = (string) ($translated['name'] ?? $product->getName() ?? 'Veylune Object');
        $media = [];

        if ($product->getCover()?->getMedia() !== null) {
            $cover = $product->getCover()->getMedia();
            $media[$cover->getId()] = [
                'url' => $cover->getUrl(),
                'alt' => (string) ($cover->getTranslated()['alt'] ?? $name),
            ];
        }

        foreach ($product->getMedia() ?? [] as $productMedia) {
            $mediaEntity = $productMedia->getMedia();

            if ($mediaEntity === null || $mediaEntity->getUrl() === null) {
                continue;
            }

            $media[$mediaEntity->getId()] = [
                'url' => $mediaEntity->getUrl(),
                'alt' => (string) ($mediaEntity->getTranslated()['alt'] ?? $name),
            ];
        }

        $materials = [];
        $rooms = [];

        foreach ($product->getProperties() ?? [] as $property) {
            $groupName = strtolower((string) ($property->getGroup()?->getTranslated()['name'] ?? $property->getGroup()?->getName() ?? ''));
            $label = (string) ($property->getTranslated()['name'] ?? $property->getName() ?? '');

            if ($label === '') {
                continue;
            }

            if (str_contains($groupName, 'material')) {
                $materials[$label] = $label;
            }

            if (str_contains($groupName, 'room')) {
                $rooms[$label] = $label;
            }
        }

        $productNumber = (string) $product->getProductNumber();
        $categoryKey = $this->productExposureService->primaryCategoryKeyForProductNumber($productNumber) ?? 'furniture';
        $primaryRoomKey = $this->productExposureService->primaryRoomKeyForProductNumber($productNumber) ?? 'living-room';
        $primaryCollectionKey = $this->productExposureService->primaryCollectionKeyForProductNumber($productNumber);
        $description = trim((string) ($translated['description'] ?? ''));
        $metaDescription = trim((string) ($translated['metaDescription'] ?? ''));

        return [
            'name' => $name,
            'productNumber' => $productNumber,
            'recordId' => $productNumber,
            'departmentKey' => $categoryKey,
            'departmentLabel' => ucwords(str_replace('-', ' ', $categoryKey)),
            'productTypeLabel' => (string) ($product->getCategories()?->first()?->getTranslated()['name'] ?? 'Curated Object'),
            'description' => $description,
            'metaDescription' => mb_substr($metaDescription !== '' ? $metaDescription : strip_tags($description), 0, 240),
            'price' => $product->getCalculatedPrice()?->getUnitPrice() ?? 0.0,
            'available' => $product->getAvailable(),
            'stock' => $product->getStock(),
            'manufacturerName' => (string) ($product->getManufacturer()?->getTranslated()['name'] ?? 'Veylune Studio'),
            'materials' => array_values($materials),
            'rooms' => array_values($rooms),
            'primaryRoomKey' => $primaryRoomKey,
            'primaryCollectionKey' => $primaryCollectionKey,
            'roomSceneAsset' => $this->roomSceneAsset($primaryRoomKey),
            'media' => array_values($media),
            'coverAsset' => null,
            'width' => $product->getWidth(),
            'height' => $product->getHeight(),
            'length' => $product->getLength(),
            'weight' => $product->getWeight(),
            'customFields' => $customFields,
            'publicationState' => 'published',
            'statusLabel' => 'Published object',
        ];
    }

    /**
     * @param array<string, mixed> $draft
     * @return array<string, mixed>
     */
    private function draftViewModel(array $draft): array
    {
        $departmentKey = str_replace('_', '-', (string) ($draft['department'] ?? 'furniture'));
        $draftRooms = \is_array($draft['rooms'] ?? null) ? $draft['rooms'] : [];
        $draftCollections = \is_array($draft['collections'] ?? null) ? $draft['collections'] : [];
        $primaryRoomKey = str_replace('_', '-', (string) ($draftRooms[0] ?? 'living-room'));
        $publicCollectionKeys = array_values(array_filter(
            array_map(static fn (string $collection): string => str_replace('_', '-', $collection), $draftCollections),
            static fn (string $collection): bool => \in_array($collection, self::PUBLIC_COLLECTION_KEYS, true)
        ));
        $primaryCollectionKey = $publicCollectionKeys[0] ?? null;
        $description = trim((string) ($draft['description'] ?? ''));

        if ($description === '') {
            $description = sprintf(
                '%s is shown here as a development-only object study for proportion, material evidence, and decision hierarchy.',
                (string) ($draft['name'] ?? 'This object')
            );
        }

        return [
            'name' => (string) ($draft['name'] ?? 'Veylune Object'),
            'productNumber' => (string) ($draft['productNumber'] ?? ''),
            'recordId' => (string) ($draft['recordId'] ?? ''),
            'departmentKey' => $departmentKey,
            'departmentLabel' => ucwords(str_replace('-', ' ', $departmentKey)),
            'productTypeLabel' => ucwords(str_replace('_', ' ', (string) ($draft['productType'] ?? 'object'))),
            'description' => $description,
            'metaDescription' => mb_substr(strip_tags($description), 0, 240),
            'price' => (float) ($draft['targetPrice'] ?? 0.0),
            'available' => false,
            'stock' => 0,
            'manufacturerName' => 'Veylune Studio',
            'materials' => array_values(array_unique(array_filter(array_merge(
                [(string) ($draft['materialLabel'] ?? '')],
                array_map(static fn (string $material): string => ucwords(str_replace('_', ' ', $material)), $draft['materials'] ?? [])
            )))),
            'rooms' => array_map(static fn (string $room): string => ucwords(str_replace('_', ' ', $room)), $draft['rooms'] ?? []),
            'primaryRoomKey' => $primaryRoomKey,
            'primaryCollectionKey' => $primaryCollectionKey,
            'roomSceneAsset' => $this->roomSceneAsset($primaryRoomKey),
            'media' => [],
            'coverAsset' => $draft['coverAsset'],
            'width' => $draft['width'] ?? null,
            'height' => $draft['height'] ?? null,
            'length' => $draft['length'] ?? null,
            'weight' => $draft['weight'] ?? null,
            'customFields' => \is_array($draft['customFields'] ?? null) ? $draft['customFields'] : [],
            'publicationState' => 'draft',
            'statusLabel' => 'Development probe - not public',
        ];
    }

    private function roomSceneAsset(string $roomKey): string
    {
        return self::ROOM_SCENE_ASSETS[$roomKey] ?? self::ROOM_SCENE_ASSETS['living-room'];
    }
}
