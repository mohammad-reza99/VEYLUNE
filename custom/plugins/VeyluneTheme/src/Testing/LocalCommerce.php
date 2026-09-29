<?php declare(strict_types=1);

namespace VeyluneTheme\Testing;

use Shopware\Core\PlatformRequest;
use Symfony\Component\HttpFoundation\Request;

final class LocalCommerce
{
    public const CANONICAL_CHANNEL = '019e3bf9c220717884d2a4eaca77c2d1';
    public const CHANNEL = '019f7a00000070008000000000000001';
    public const ROOT = '019f7a00000070008000000000000002';
    public const PAYMENT = '019f7a00000070008000000000000003';
    public const DECLINED = '019f7a00000070008000000000000004';
    public const SHIPPING = '019f7a00000070008000000000000005';
    public const DOMAIN = '019f7a00000070008000000000000006';
    public const URL = 'https://veylune-shopware.ddev.site/__commerce-test';

    public static function matches(Request $request, string $environment): bool
    {
        return $environment === 'dev'
            && getenv('DDEV_PROJECT') === 'veylune-shopware'
            && $request->getHost() === 'veylune-shopware.ddev.site'
            && $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID) === self::CHANNEL;
    }

    public static function productId(int $index): string
    {
        return md5('veylune-local-commerce-product-' . $index);
    }

    public static function isTestCheckoutChannel(string $salesChannelId): bool
    {
        return \in_array($salesChannelId, [self::CANONICAL_CHANNEL, self::CHANNEL], true);
    }
}
