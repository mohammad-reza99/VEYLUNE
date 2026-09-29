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
final class LivingIndexUtilityController extends StorefrontController
{
    #[Route(path: '/discover', name: 'frontend.veylune.living_index.search', methods: [Request::METHOD_GET])]
    public function search(Request $request, SalesChannelContext $context): Response
    {
        unset($context);
        $query = trim(mb_substr((string) $request->query->get('q', ''), 0, 120));

        return $this->redirectToRoute(
            $query === '' ? 'frontend.veylune.catalog.home' : 'frontend.veylune.catalog.search',
            $query === '' ? [] : ['q' => $query],
            Response::HTTP_MOVED_PERMANENTLY
        );
    }

    #[Route(path: '/selection', name: 'frontend.veylune.living_index.selection', methods: [Request::METHOD_GET])]
    public function selection(Request $request, SalesChannelContext $context): Response
    {
        unset($request, $context);

        return $this->redirectToRoute(
            'frontend.checkout.cart.page',
            ['source' => 'selection'],
            Response::HTTP_MOVED_PERMANENTLY
        );
    }
}
