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
class PartnershipController extends StorefrontController
{
    public function __construct(
        private readonly GenericPageLoader $genericPageLoader,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route(path: '/atelier-partnerships', name: 'frontend.veylune.partnership.page', methods: [Request::METHOD_GET])]
    public function page(Request $request, SalesChannelContext $context): Response
    {
        $page = $this->genericPageLoader->load($request, $context);
        $page->getMetaInformation()?->setMetaTitle($this->translator->trans('veylune.partnership.seo.title'));
        $page->getMetaInformation()?->setMetaDescription($this->translator->trans('veylune.partnership.seo.description'));

        return $this->renderStorefront('@Storefront/storefront/veylune/partnership-page.html.twig', [
            'page' => $page,
            'veylunePageType' => 'partnership',
        ]);
    }

    #[Route(path: '/private-consultation', name: 'frontend.veylune.consultation.page', methods: [Request::METHOD_GET])]
    public function consultation(Request $request, SalesChannelContext $context): Response
    {
        $page = $this->genericPageLoader->load($request, $context);
        $page->getMetaInformation()?->setMetaTitle('Private Consultation | VEYLUNE STUDIO');
        $page->getMetaInformation()?->setMetaDescription('A private design conversation for considered interiors, sourcing, and material direction.');

        return $this->renderStorefront('@Storefront/storefront/veylune/consultation-page.html.twig', [
            'page' => $page,
            'veylunePageType' => 'consultation',
        ]);
    }

    #[Route(path: '/trade-program', name: 'frontend.veylune.trade.page', methods: [Request::METHOD_GET])]
    public function trade(Request $request, SalesChannelContext $context): Response
    {
        $page = $this->genericPageLoader->load($request, $context);
        $page->getMetaInformation()?->setMetaTitle('Trade Program | VEYLUNE STUDIO');
        $page->getMetaInformation()?->setMetaDescription('Professional sourcing and studio support for designers, architects, and considered projects.');

        return $this->renderStorefront('@Storefront/storefront/veylune/consultation-page.html.twig', [
            'page' => $page,
            'veylunePageType' => 'trade',
        ]);
    }

    #[Route(path: '/about-studio', name: 'frontend.veylune.studio.page', methods: [Request::METHOD_GET])]
    public function studio(Request $request, SalesChannelContext $context): Response
    {
        $page = $this->genericPageLoader->load($request, $context);
        $page->getMetaInformation()?->setMetaTitle('Studio | VEYLUNE STUDIO');
        $page->getMetaInformation()?->setMetaDescription('The design point of view, material direction, and interior philosophy behind Veylune Studio.');

        return $this->renderStorefront('@Storefront/storefront/veylune/consultation-page.html.twig', [
            'page' => $page,
            'veylunePageType' => 'about',
        ]);
    }
}
