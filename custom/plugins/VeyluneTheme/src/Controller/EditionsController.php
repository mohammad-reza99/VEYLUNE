<?php declare(strict_types=1);

namespace VeyluneTheme\Controller;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Framework\Routing\StorefrontRouteScope;
use Shopware\Storefront\Page\GenericPageLoader;
use VeyluneTheme\Edition\EditionReferenceRegistry;
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
        private readonly TranslatorInterface $translator,
        private readonly EditionReferenceRegistry $editionReferenceRegistry
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

    #[Route(
        path: '/editions/{reference}',
        name: 'frontend.veylune.editions.detail.guard',
        requirements: ['reference' => '[a-z0-9]+(?:-[a-z0-9]+)*'],
        methods: [Request::METHOD_GET]
    )]
    #[Route(
        path: '/editionen/{reference}',
        name: 'frontend.veylune.editions.detail.guard.de',
        requirements: ['reference' => '[a-z0-9]+(?:-[a-z0-9]+)*'],
        methods: [Request::METHOD_GET]
    )]
    public function guardedDetail(string $reference, Request $request, SalesChannelContext $context): Response
    {
        $locale = str_contains($request->getPathInfo(), '/editionen/') ? 'de' : 'en';
        $resolution = $this->editionReferenceRegistry->resolveDetailRouteState($reference, $locale);

        if ($resolution['state'] !== EditionReferenceRegistry::STATE_PUBLICLY_RENDERABLE || $resolution['exposureAllowed'] !== true) {
            return $this->denyEditionDetail();
        }

        $payload = $this->editionReferenceRegistry->buildGuardedRenderingPayload($reference, $locale);

        if ($payload === null) {
            return $this->denyEditionDetail();
        }

        $page = $this->genericPageLoader->load($request, $context);

        return $this->renderStorefront('@Storefront/storefront/veylune/edition-detail-skeleton.html.twig', [
            'page' => $page,
            'veyluneEdition' => $payload,
        ]);
    }

    private function denyEditionDetail(): Response
    {
        return new Response('', Response::HTTP_NOT_FOUND);
    }
}
