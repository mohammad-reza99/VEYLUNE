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
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StorefrontRouteScope::ID]])]
#[Package('storefront')]
final class LegalPageController extends StorefrontController
{
    private const COPY = [
        'privacy' => ['Privacy notice', 'We use personal information only to operate the studio, respond to inquiries, and provide requested services. We do not sell personal information.', 'The complete jurisdiction-specific privacy policy is in legal review and will be published before public commerce activation.'],
        'imprint' => ['Imprint', 'Verified company and responsible-party details are being prepared for publication.', 'Public commerce remains disabled until the complete legal disclosure has been reviewed and approved.'],
        'terms' => ['Terms of service', 'Public commerce terms are in legal review and will be published before orders are enabled.', 'The current storefront is an editorial and private-preview environment; it does not accept public purchase orders.'],
        'payment-delivery' => ['Payment and delivery', 'Public payment and delivery options are not active.', 'Verified methods, regions, costs, and delivery estimates will be published before the public order flow is enabled.'],
        'cancellation' => ['Cancellation information', 'The jurisdiction-specific cancellation notice will be published with the approved public terms before orders are enabled.', 'No public purchase contract can currently be completed through this storefront.'],
    ];

    public function __construct(
        private readonly GenericPageLoader $genericPageLoader,
        private readonly TranslatorInterface $translator
    )
    {
    }

    #[Route(path: '/legal/{document}', name: 'frontend.veylune.legal.page', requirements: ['document' => 'privacy|imprint|terms|payment-delivery|cancellation'], methods: [Request::METHOD_GET])]
    public function page(string $document, Request $request, SalesChannelContext $context): Response
    {
        $copy = self::COPY[$document] ?? throw new NotFoundHttpException();
        $page = $this->genericPageLoader->load($request, $context);
        $snippetBase = 'veylune.marketplace.legal.' . $document;
        $page->getMetaInformation()?->setMetaTitle($this->translator->trans($snippetBase . '.title') . ' | VEYLUNE STUDIO');
        $page->getMetaInformation()?->setMetaDescription($this->translator->trans($snippetBase . '.one'));
        $page->getMetaInformation()?->setCanonical($request->getSchemeAndHttpHost() . $request->getPathInfo());

        return $this->renderStorefront('@Storefront/storefront/veylune/legal-page.html.twig', [
            'page' => $page,
            'veyluneLegalDocument' => $document,
            'veyluneLegalCopy' => $copy,
        ]);
    }
}
