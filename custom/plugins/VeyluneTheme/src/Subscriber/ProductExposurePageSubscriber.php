<?php declare(strict_types=1);

namespace VeyluneTheme\Subscriber;

use Shopware\Core\Framework\Log\Package;
use Shopware\Storefront\Event\StorefrontRenderEvent;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Storefront\Page\Navigation\NavigationPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use VeyluneTheme\Discovery\ProductExposureService;

#[Package('storefront')]
final class ProductExposurePageSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ProductExposureService $productExposureService
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
    }

    public function onStorefrontRender(StorefrontRenderEvent $event): void
    {
        $event->setParameter(
            'veylunePublicSurfaces',
            $this->productExposureService->publicSurfaceAvailability($event->getSalesChannelContext())
        );
    }
}
