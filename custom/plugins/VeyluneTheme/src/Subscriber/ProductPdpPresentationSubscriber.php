<?php declare(strict_types=1);

namespace VeyluneTheme\Subscriber;

use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use VeyluneTheme\Product\PdpPresentationService;

final class ProductPdpPresentationSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly PdpPresentationService $presentationService)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [ProductPageLoadedEvent::class => 'onProductPageLoaded'];
    }

    public function onProductPageLoaded(ProductPageLoadedEvent $event): void
    {
        $event->getPage()->addExtension(
            'veylunePdpPresentation',
            new ArrayStruct($this->presentationService->forProduct($event->getPage()->getProduct()))
        );
    }
}
