<?php declare(strict_types=1);

namespace VeyluneTheme\Subscriber;

use Shopware\Core\Framework\Log\Package;
use Shopware\Storefront\Event\StorefrontRenderEvent;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Storefront\Page\Navigation\NavigationPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use VeyluneTheme\Catalog\PublicCatalogService;
use VeyluneTheme\Discovery\ProductExposureService;

#[Package('storefront')]
final class ProductExposurePageSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ProductExposureService $productExposureService,
        private readonly PublicCatalogService $publicCatalogService,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            NavigationPageLoadedEvent::class => 'onNavigationPageLoaded',
            StorefrontRenderEvent::class => 'onStorefrontRender',
        ];
    }

    public function onNavigationPageLoaded(NavigationPageLoadedEvent $event): void
    {
        if ($event->getRequest()->attributes->get('_route') !== 'frontend.home.page') {
            return;
        }

        $event->getPage()->addExtension(
            'veyluneProductExposure',
            new ArrayStruct($this->productExposureService->homepageProducts($event->getSalesChannelContext()))
        );

        $event->getPage()->addExtension('veyluneMarketplace', new ArrayStruct([
            'rails' => $this->publicCatalogService->homepageRails($event->getSalesChannelContext()),
            'categories' => [
                'furniture' => 'Furniture',
                'lighting' => 'Lighting',
                'decor-objects' => 'Decor & Objects',
                'textiles-rugs' => 'Textiles & Rugs',
                'dining-kitchen' => 'Dining & Kitchen',
                'outdoor' => 'Outdoor',
            ],
            'rooms' => [
                'living-room' => 'Living Room',
                'dining-room' => 'Dining Room',
                'bedroom' => 'Bedroom',
                'workspace' => 'Workspace',
                'hallway' => 'Hallway',
            ],
            'collections' => [
                'founder-selection' => 'Founder Selection',
                'new-arrivals' => 'New Arrivals',
            ],
        ]));
    }

    public function onStorefrontRender(StorefrontRenderEvent $event): void
    {
        $event->setParameter(
            'veyluneCanonicalCatalogEnabled',
            true
        );
        $event->setParameter(
            'veylunePublicSurfaces',
            $this->publicCatalogService->surfaceAvailability($event->getSalesChannelContext())
        );
    }
}
