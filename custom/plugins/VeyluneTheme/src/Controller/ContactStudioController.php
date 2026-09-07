<?php declare(strict_types=1);

namespace VeyluneTheme\Controller;

use Shopware\Core\Content\Cms\SalesChannel\AbstractCmsRoute;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Framework\Routing\StorefrontRouteScope;
use Shopware\Storefront\Page\GenericPageLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StorefrontRouteScope::ID]])]
#[Package('storefront')]
final class ContactStudioController extends StorefrontController
{
    private const CONTACT_CMS_PAGE_ID = '019e3bf907a971b3a48974fb8e7f7fbe';

    private const PROJECT_SOURCES = ['consultation', 'selection', 'discover', 'object', 'trade'];

    public function __construct(
        private readonly GenericPageLoader $genericPageLoader,
        private readonly AbstractCmsRoute $cmsRoute
    ) {
    }

    #[Route(path: '/contact-studio', name: 'frontend.veylune.contact.page', methods: [Request::METHOD_GET])]
    public function page(Request $request, SalesChannelContext $context): Response
    {
        $page = $this->genericPageLoader->load($request, $context);
        $cmsPage = $this->cmsRoute->load(self::CONTACT_CMS_PAGE_ID, $request, $context)->getCmsPage();
        $source = strtolower(trim($request->query->getString('from')));
        if (!in_array($source, self::PROJECT_SOURCES, true)) {
            $source = 'direct';
        }

        $searchQuery = $this->cleanContext($request->query->getString('q'), 100);
        $objectReference = $this->cleanContext($request->query->getString('object'), 64);
        $page->getMetaInformation()?->setMetaTitle('Contact the studio | VEYLUNE STUDIO');
        $page->getMetaInformation()?->setMetaDescription('Send a private design, sourcing, press, or client-care inquiry to Veylune Studio.');
        $page->getMetaInformation()?->setCanonical($request->getSchemeAndHttpHost() . $request->getPathInfo());

        return $this->renderStorefront('@Storefront/storefront/veylune/contact-studio-page.html.twig', [
            'page' => $page,
            'cmsPage' => $cmsPage,
            'veyluneProjectBrief' => [
                'source' => $source,
                'query' => $searchQuery,
                'object' => $objectReference,
            ],
        ]);
    }

    private function cleanContext(string $value, int $maximumLength): string
    {
        $value = trim((string) preg_replace('/\\s+/u', ' ', strip_tags($value)));

        return mb_substr($value, 0, $maximumLength);
    }
}
