<?php declare(strict_types=1);

namespace VeyluneTheme\Subscriber;

use Shopware\Core\Content\Sitemap\Event\SitemapQueryEvent;
use Shopware\Core\Content\Sitemap\Provider\CategoryUrlProvider;
use Shopware\Core\Content\Sitemap\Provider\LandingPageUrlProvider;
use Shopware\Core\Content\Sitemap\Provider\ProductUrlProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use VeyluneTheme\Storefront\StorefrontRoleRegistry;

final class IdentitySitemapSourceFilterSubscriber implements EventSubscriberInterface
{
    private const CONTAINED_QUERY_EVENTS = [
        ProductUrlProvider::QUERY_EVENT_NAME,
        CategoryUrlProvider::QUERY_EVENT_NAME,
        LandingPageUrlProvider::QUERY_EVENT_NAME,
    ];

    public static function getSubscribedEvents(): array
    {
        return [
            SitemapQueryEvent::class => 'suppressIdentityNativeSource',
        ];
    }

    public function suppressIdentityNativeSource(SitemapQueryEvent $event): void
    {
        if (!StorefrontRoleRegistry::isCanonicalPublicStorefront($event->getSalesChannelContext()->getSalesChannelId())
            || !\in_array($event->getName(), self::CONTAINED_QUERY_EVENTS, true)
        ) {
            return;
        }

        $event->query->andWhere('1 = 0');
    }
}
