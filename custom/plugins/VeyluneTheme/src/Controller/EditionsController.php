<?php declare(strict_types=1);

namespace VeyluneTheme\Controller;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Framework\Routing\StorefrontRouteScope;
use Shopware\Storefront\Framework\Routing\RequestTransformer;
use Shopware\Storefront\Page\GenericPageLoader;
use VeyluneTheme\Retrieval\IdentityRetrievalMediator;
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
        private readonly IdentityRetrievalMediator $identityRetrievalMediator
    ) {
    }

    #[Route(path: '/editions', name: 'frontend.veylune.editions.page', methods: [Request::METHOD_GET])]
    #[Route(path: '/editionen', name: 'frontend.veylune.editions.page.de', methods: [Request::METHOD_GET])]
    public function page(Request $request, SalesChannelContext $context): Response
    {
        $page = $this->genericPageLoader->load($request, $context);
        $page->getMetaInformation()?->setMetaTitle($this->translator->trans('veylune.editions.seo.title'));
        $page->getMetaInformation()?->setMetaDescription($this->translator->trans('veylune.editions.seo.description'));
        $page->getMetaInformation()?->setCanonical($request->getSchemeAndHttpHost() . $request->getPathInfo());

        return $this->renderStorefront('@Storefront/storefront/veylune/editions-page.html.twig', [
            'page' => $page,
            'veylunePageType' => 'editions',
        ]);
    }

    #[Route(path: '/journal', name: 'frontend.veylune.editions.journal.alias', methods: [Request::METHOD_GET])]
    #[Route(path: '/inspiration', name: 'frontend.veylune.editions.inspiration.alias', methods: [Request::METHOD_GET])]
    public function editorialAlias(): Response
    {
        return $this->redirectToRoute(
            'frontend.veylune.editions.page',
            [],
            Response::HTTP_MOVED_PERMANENTLY
        );
    }

    #[Route(
        path: '/editions/{reference}',
        name: 'frontend.veylune.editions.detail.guard',
        requirements: ['reference' => '[^/]+'],
        methods: [Request::METHOD_GET]
    )]
    #[Route(
        path: '/editionen/{reference}',
        name: 'frontend.veylune.editions.detail.guard.de',
        requirements: ['reference' => '[^/]+'],
        methods: [Request::METHOD_GET]
    )]
    public function guardedDetail(string $reference, Request $request, SalesChannelContext $context): Response
    {
        $retrieval = $this->identityRetrievalMediator->retrieve(
            $context->getSalesChannelId(),
            (string) $request->attributes->get('_route'),
            (string) $request->attributes->get(RequestTransformer::ORIGINAL_REQUEST_URI, $request->getRequestUri()),
            $reference,
            $request->getLocale()
        );

        if ($retrieval['status'] !== 'renderable') {
            return $this->denyEditionDetail();
        }

        $page = $this->genericPageLoader->load($request, $context);

        return $this->renderStorefront('@Storefront/storefront/veylune/edition-detail-skeleton.html.twig', [
            'page' => $page,
            'veyluneEdition' => $retrieval['payload'],
        ]);
    }

    private function denyEditionDetail(): Response
    {
        return new Response('', Response::HTTP_NOT_FOUND);
    }
}
