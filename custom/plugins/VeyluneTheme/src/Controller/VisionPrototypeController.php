<?php declare(strict_types=1);

namespace VeyluneTheme\Controller;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\PlatformRequest;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Framework\Routing\StorefrontRouteScope;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StorefrontRouteScope::ID]])]
#[Package('storefront')]
final class VisionPrototypeController extends StorefrontController
{
    /**
     * The five VS-02 surfaces are retired. Their development URLs remain as
     * short-lived compatibility bridges so saved review links do not dead-end.
     *
     * @var array<string, array{route: string, parameters: array<string, string>}>
     */
    private const DESTINATIONS = [
        'home' => ['route' => 'frontend.home.page', 'parameters' => []],
        'header' => ['route' => 'frontend.home.page', 'parameters' => []],
        'department' => ['route' => 'frontend.veylune.discovery.category', 'parameters' => ['categoryKey' => 'furniture']],
        'listing' => ['route' => 'frontend.veylune.discovery.category', 'parameters' => ['categoryKey' => 'furniture']],
        'product' => ['route' => 'frontend.veylune.discovery.category', 'parameters' => ['categoryKey' => 'furniture']],
    ];

    public function __construct(private readonly string $environment)
    {
    }

    #[Route(
        path: '/__veylune-vision/{surface}',
        name: 'frontend.veylune.vision.prototype',
        requirements: ['surface' => 'home|header|department|listing|product'],
        defaults: ['surface' => 'home'],
        methods: [Request::METHOD_GET]
    )]
    public function page(string $surface, Request $request): Response
    {
        if ($this->environment !== 'dev' || !str_ends_with($request->getHost(), '.ddev.site')) {
            throw new NotFoundHttpException();
        }

        $destination = self::DESTINATIONS[$surface] ?? null;

        if ($destination === null) {
            throw new NotFoundHttpException();
        }

        $response = $this->redirectToRoute(
            $destination['route'],
            $destination['parameters'],
            Response::HTTP_FOUND
        );
        $response->headers->set('Cache-Control', 'private, no-store, max-age=0');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
        $response->headers->set('X-Veylune-Prototype-State', 'retired');

        return $response;
    }
}
