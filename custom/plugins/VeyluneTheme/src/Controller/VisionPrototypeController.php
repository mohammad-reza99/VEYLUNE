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

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StorefrontRouteScope::ID]])]
#[Package('storefront')]
final class VisionPrototypeController extends StorefrontController
{
    private const SURFACES = ['home', 'header', 'department', 'listing', 'product'];

    private const PRODUCTS = [
        ['id' => 'F01', 'name' => 'Aurelia Modular Sofa', 'type' => 'Modular sofa', 'material' => 'Upholstery fabric', 'price' => '€4,900', 'colors' => 8, 'asset' => 'f01-aurelia-modular-sofa-v1.webp'],
        ['id' => 'F02', 'name' => 'Liora Curved Sofa', 'type' => 'Curved sofa', 'material' => 'Upholstery fabric', 'price' => '€4,200', 'colors' => 6, 'asset' => 'f02-liora-curved-sofa-v1.webp'],
        ['id' => 'F10', 'name' => 'Elara Travertine Table', 'type' => 'Coffee table', 'material' => 'Travertine', 'price' => '€2,200', 'colors' => 2, 'asset' => 'f10-elara-travertine-coffee-table-v1.webp'],
        ['id' => 'F13', 'name' => 'Portico Console', 'type' => 'Console', 'material' => 'Travertine', 'price' => '€2,750', 'colors' => 2, 'asset' => 'f13-portico-travertine-console-v1.webp'],
        ['id' => 'F15', 'name' => 'Serein Platform Bed', 'type' => 'Platform bed', 'material' => 'Upholstery fabric', 'price' => '€3,600', 'colors' => 5, 'asset' => 'f15-serein-platform-bed-v1.webp'],
        ['id' => 'F07', 'name' => 'Forma Desk Chair', 'type' => 'Office chair', 'material' => 'Leather and oak', 'price' => '€1,350', 'colors' => 4, 'asset' => 'f07-forma-desk-chair-v1.webp'],
        ['id' => 'F17', 'name' => 'Canto Tall Cabinet', 'type' => 'Storage cabinet', 'material' => 'Oak and glass', 'price' => '€3,200', 'colors' => 3, 'asset' => 'f17-canto-tall-cabinet-v1.webp'],
        ['id' => 'F03', 'name' => 'Oris Lounge Chair', 'type' => 'Lounge chair', 'material' => 'Leather and oak', 'price' => '€2,250', 'colors' => 5, 'asset' => 'f03-oris-leather-lounge-chair-v1.webp'],
    ];

    private const DEPARTMENTS = [
        ['name' => 'Living Room', 'caption' => 'Sofas, tables, seating', 'asset' => 'veylune-room-living-v1.webp'],
        ['name' => 'Bedroom', 'caption' => 'Beds, lighting, storage', 'asset' => 'veylune-room-bedroom-v1.webp'],
        ['name' => 'Dining', 'caption' => 'Tables, chairs, objects', 'asset' => 'veylune-room-dining-v1.webp'],
        ['name' => 'Workspace', 'caption' => 'Desks, chairs, storage', 'asset' => 'veylune-room-workspace-v1.webp'],
        ['name' => 'Outdoor', 'caption' => 'Seating, tables, planters', 'asset' => 'veylune-room-outdoor-v1.webp'],
        ['name' => 'Lighting', 'caption' => 'Floor, table, pendant', 'asset' => 'veylune-category-lighting-v1.webp'],
    ];

    public function __construct(
        private readonly GenericPageLoader $genericPageLoader,
        private readonly string $environment
    ) {
    }

    #[Route(path: '/__veylune-vision/{surface}', name: 'frontend.veylune.vision.prototype', requirements: ['surface' => 'home|header|department|listing|product'], defaults: ['surface' => 'home'], methods: [Request::METHOD_GET])]
    public function page(string $surface, Request $request, SalesChannelContext $context): Response
    {
        if ($this->environment !== 'dev' || !\in_array($surface, self::SURFACES, true)) {
            throw new NotFoundHttpException();
        }

        $page = $this->genericPageLoader->load($request, $context);
        $page->getMetaInformation()?->setMetaTitle('The Living Index — ' . ucfirst($surface) . ' Prototype');
        $page->getMetaInformation()?->setMetaDescription('Development-only Veylune Vision System prototype.');
        $page->getMetaInformation()?->setRobots('noindex,nofollow');

        return $this->renderStorefront('@Storefront/storefront/veylune/vision-prototype.html.twig', [
            'page' => $page,
            'veyluneVisionSurface' => $surface,
            'veyluneVisionProducts' => self::PRODUCTS,
            'veyluneVisionDepartments' => self::DEPARTMENTS,
        ]);
    }
}
