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
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StorefrontRouteScope::ID]])]
#[Package('storefront')]
class EditionsController extends StorefrontController
{
    public function __construct(
        private readonly GenericPageLoader $genericPageLoader,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route(path: '/editions', name: 'frontend.veylune.editions.page', methods: [Request::METHOD_GET])]
    #[Route(path: '/editionen', name: 'frontend.veylune.editions.page.de', methods: [Request::METHOD_GET])]
    public function page(Request $request, SalesChannelContext $context): Response
    {
        $page = $this->genericPageLoader->load($request, $context);
        $page->getMetaInformation()?->setMetaTitle($this->translator->trans('veylune.editions.seo.title'));
        $page->getMetaInformation()?->setMetaDescription($this->translator->trans('veylune.editions.seo.description'));

        return $this->renderStorefront('@Storefront/storefront/veylune/editions-page.html.twig', [
            'page' => $page,
            'veylunePageType' => 'editions',
        ]);
    }
}
