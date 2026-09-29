<?php declare(strict_types=1);

namespace VeyluneTheme\Controller;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Framework\Routing\StorefrontRouteScope;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StorefrontRouteScope::ID]])]
#[Package('storefront')]
final class DiscoveryDestinationController extends StorefrontController
{
    private const CANONICAL_CATEGORIES = [
        'furniture',
        'lighting',
        'decor-objects',
        'textiles-rugs',
        'dining-kitchen',
        'outdoor',
    ];

    private const CATEGORY_SEARCH_TERMS = [
        'bedding-bath' => 'bedding bath',
        'mattresses' => 'mattress',
        'rugs' => 'rug',
        'organization' => 'storage',
    ];

    #[Route(path: '/rooms/{roomKey}', name: 'frontend.veylune.discovery.room', requirements: ['roomKey' => 'living-room|dining-room|bedroom|workspace|hallway|outdoor'], methods: [Request::METHOD_GET])]
    public function room(string $roomKey, Request $request, SalesChannelContext $context): Response
    {
        unset($context);

        if ($roomKey === 'outdoor') {
            return $this->redirectToRoute(
                'frontend.veylune.catalog.category',
                array_merge($this->listingQuery($request), ['categoryKey' => 'outdoor']),
                Response::HTTP_MOVED_PERMANENTLY
            );
        }

        return $this->redirectToRoute(
            'frontend.veylune.catalog.room',
            array_merge($this->listingQuery($request), ['roomKey' => $roomKey]),
            Response::HTTP_MOVED_PERMANENTLY
        );
    }

    #[Route(path: '/collections/permanent', name: 'frontend.veylune.discovery.collection.permanent', defaults: ['collectionKey' => 'permanent-collections'], methods: [Request::METHOD_GET])]
    #[Route(path: '/collections/editorial', name: 'frontend.veylune.discovery.collection.editorial', defaults: ['collectionKey' => 'editorial-collections'], methods: [Request::METHOD_GET])]
    #[Route(path: '/collections/{collectionKey}', name: 'frontend.veylune.discovery.collection', requirements: ['collectionKey' => 'founder-selection|new-arrivals|best-sellers|sale|permanent-collections|editorial-collections'], methods: [Request::METHOD_GET])]
    public function collection(string $collectionKey, Request $request, SalesChannelContext $context): Response
    {
        unset($context);

        if (in_array($collectionKey, ['founder-selection', 'new-arrivals'], true)) {
            return $this->redirectToRoute(
                'frontend.veylune.catalog.collection',
                array_merge($this->listingQuery($request), ['collectionKey' => $collectionKey]),
                Response::HTTP_MOVED_PERMANENTLY
            );
        }

        if ($collectionKey === 'editorial-collections') {
            return $this->redirectToRoute('frontend.veylune.editions.page', [], Response::HTTP_MOVED_PERMANENTLY);
        }

        $parameters = in_array($collectionKey, ['best-sellers', 'sale'], true)
            ? ['from' => $collectionKey]
            : [];

        return $this->redirectToRoute(
            'frontend.veylune.catalog.home',
            $parameters,
            Response::HTTP_MOVED_PERMANENTLY
        );
    }

    #[Route(path: '/categories/{categoryKey}', name: 'frontend.veylune.discovery.category', requirements: ['categoryKey' => 'furniture|lighting|decor-objects|textiles-rugs|dining-kitchen|outdoor|bedding-bath|mattresses|rugs|organization'], methods: [Request::METHOD_GET])]
    public function category(string $categoryKey, Request $request, SalesChannelContext $context): Response
    {
        unset($context);

        if (in_array($categoryKey, self::CANONICAL_CATEGORIES, true)) {
            return $this->redirectToRoute(
                'frontend.veylune.catalog.category',
                array_merge($this->listingQuery($request), ['categoryKey' => $categoryKey]),
                Response::HTTP_MOVED_PERMANENTLY
            );
        }

        return $this->redirectToRoute(
            'frontend.veylune.catalog.search',
            ['q' => self::CATEGORY_SEARCH_TERMS[$categoryKey]],
            Response::HTTP_MOVED_PERMANENTLY
        );
    }

    /**
     * @return array<string, string>
     */
    private function listingQuery(Request $request): array
    {
        $query = [];

        foreach (['material', 'sort'] as $key) {
            $value = trim((string) $request->query->get($key, ''));

            if ($value !== '') {
                $query[$key] = $value;
            }
        }

        return $query;
    }
}
