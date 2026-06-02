<?php declare(strict_types=1);

namespace VeyluneTheme\Storefront;

final class StorefrontRoleRegistry
{
    public const ROLE_CANONICAL_PUBLIC_STOREFRONT = 'canonical_public_storefront';
    public const ROLE_HEADLESS = 'headless';
    public const ROLE_IDENTITY_FOUNDATION = 'identity_foundation';
    public const ROLE_ACQUISITION_FOUNDATION = 'acquisition_foundation';
    public const ROLE_PRIVATE_COMMERCE_FOUNDATION = 'private_commerce_foundation';

    public const CANONICAL_PUBLIC_STOREFRONT_ID = '019e3bf9c220717884d2a4eaca77c2d1';
    public const HEADLESS_ID = '98432def39fc4624b33213a56b8c944d';
    public const IDENTITY_FOUNDATION_ID = '019e9e8f000070008000000000000001';
    public const ACQUISITION_FOUNDATION_ID = '019e9e8f000070008000000000000002';
    public const PRIVATE_COMMERCE_FOUNDATION_ID = '019e9e8f000070008000000000000003';

    private const ROLES = [
        self::CANONICAL_PUBLIC_STOREFRONT_ID => self::ROLE_CANONICAL_PUBLIC_STOREFRONT,
        self::HEADLESS_ID => self::ROLE_HEADLESS,
        self::IDENTITY_FOUNDATION_ID => self::ROLE_IDENTITY_FOUNDATION,
        self::ACQUISITION_FOUNDATION_ID => self::ROLE_ACQUISITION_FOUNDATION,
        self::PRIVATE_COMMERCE_FOUNDATION_ID => self::ROLE_PRIVATE_COMMERCE_FOUNDATION,
    ];

    public static function roleForSalesChannelId(string $salesChannelId): ?string
    {
        return self::ROLES[$salesChannelId] ?? null;
    }

    public static function isCanonicalPublicStorefront(string $salesChannelId): bool
    {
        return self::roleForSalesChannelId($salesChannelId) === self::ROLE_CANONICAL_PUBLIC_STOREFRONT;
    }
}
