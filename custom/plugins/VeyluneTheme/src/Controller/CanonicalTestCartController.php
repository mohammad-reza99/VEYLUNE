<?php declare(strict_types=1);

namespace VeyluneTheme\Controller;

use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItemFactoryRegistry;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Framework\Routing\StorefrontRouteScope;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use VeyluneTheme\Testing\LocalCommerce;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StorefrontRouteScope::ID]])]
final class CanonicalTestCartController extends StorefrontController
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly LineItemFactoryRegistry $lineItemFactory,
        private readonly string $environment
    ) {
    }

    #[Route(
        path: '/catalog/test-cart/add/{recordId}',
        name: 'frontend.veylune.catalog.test_cart.add',
        requirements: ['recordId' => 'F(0[1-9]|10)'],
        methods: [Request::METHOD_GET]
    )]
    public function add(string $recordId, Cart $cart, Request $request, SalesChannelContext $context): RedirectResponse
    {
        if ($this->environment !== 'dev'
            || getenv('DDEV_PROJECT') !== 'veylune-shopware'
            || $request->getHost() !== 'veylune-shopware.ddev.site') {
            throw new NotFoundHttpException();
        }

        $index = ((int) substr($recordId, 1)) - 1;
        $productId = LocalCommerce::productId($index);
        $lineItem = $this->lineItemFactory->create([
            'id' => $productId,
            'type' => LineItem::PRODUCT_LINE_ITEM_TYPE,
            'referencedId' => $productId,
            'quantity' => 1,
            'stackable' => true,
            'removable' => true,
        ], $context);

        $this->cartService->add($cart, $lineItem, $context);
        $this->addFlash(self::SUCCESS, 'Test product added to cart. No real charge or supplier reservation was created.');

        return $this->redirectToRoute('frontend.checkout.cart.page');
    }
}
