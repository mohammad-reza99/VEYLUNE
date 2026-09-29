<?php declare(strict_types=1);

namespace VeyluneTheme\Controller;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Page\GenericPageLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use VeyluneTheme\Testing\LocalCommerce;

#[Route(defaults: ['_routeScope' => ['storefront']])]
final class LocalCommerceController extends StorefrontController
{
    public function __construct(private readonly GenericPageLoader $pages, private readonly SalesChannelRepository $products, private readonly string $environment) {}

    #[Route(path: '/test-products', name: 'frontend.veylune.local_commerce', methods: ['GET'])]
    public function catalog(Request $request, SalesChannelContext $context): Response
    {
        if (!LocalCommerce::matches($request, $this->environment)) { throw $this->createNotFoundException(); }
        $ids = $request->query->get('fixtures') === 'variants'
            ? [md5('veylune-local-variant-sand-product'), md5('veylune-local-variant-charcoal-product')]
            : array_map(LocalCommerce::productId(...), range(0, 9));
        $criteria = (new Criteria($ids))->addAssociation('cover.media');
        return $this->renderStorefront('@VeyluneTheme/storefront/veylune/local-commerce.html.twig', [
            'page' => $this->pages->load($request, $context),
            'testProducts' => $this->products->search($criteria, $context),
        ]);
    }
}
