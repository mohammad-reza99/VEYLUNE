<?php declare(strict_types=1);

namespace VeyluneTheme\Storefront;

final class StorefrontRouteOwnershipPolicy
{
    public const STATE_PUBLIC = 'public';
    public const STATE_GOVERNED_PUBLIC = 'governed_public';
    public const STATE_ACTIVATION_PENDING = 'activation_pending';

    public const OWNER_STOREFRONT_COMMERCE = 'storefront_commerce';
    public const OWNER_PRODUCT_PUBLICATION_POLICY = 'product_publication_policy';
    public const OWNER_CATEGORY_PUBLICATION_POLICY = 'category_publication_policy';
    public const OWNER_COLLECTION_POLICY = 'collection_policy';
    public const OWNER_SEARCH_ARCHITECTURE = 'search_architecture';
    public const OWNER_NATIVE_COMMERCE = 'native_commerce';
    public const OWNER_COMMERCE_POLICY = 'commerce_policy';
    public const OWNER_ACQUISITION_POLICY = 'acquisition_policy';
    public const OWNER_EDITION_GOVERNANCE = 'edition_governance';

    public const SURFACE_HOMEPAGE = 'homepage';
    public const SURFACE_PRODUCTS = 'products';
    public const SURFACE_CATEGORIES = 'categories';
    public const SURFACE_COLLECTIONS = 'collections';
    public const SURFACE_SEARCH = 'search';
    public const SURFACE_CART = 'cart';
    public const SURFACE_CHECKOUT = 'checkout';
    public const SURFACE_ACCOUNT = 'account';
    public const SURFACE_WISHLIST = 'wishlist';
    public const SURFACE_CONSULTATION = 'consultation';
    public const SURFACE_TRADE = 'trade';
    public const SURFACE_EDITIONS = 'editions';

    private const COLLECTIONS_CATEGORY_ID = '019e47255c4f7a8f947adc9f036f367f';
    private const CONSULTATION_CATEGORY_ID = '019e4718d96272ac9fbbe508ccc6c6a6';
    private const TRADE_CATEGORY_ID = '019e4718ad6c79efb2b928230271e814';

    private const OWNERSHIP = [
        self::SURFACE_HOMEPAGE => [
            'state' => self::STATE_PUBLIC,
            'owner' => self::OWNER_STOREFRONT_COMMERCE,
            'activationPrerequisites' => [],
        ],
        self::SURFACE_PRODUCTS => [
            'state' => self::STATE_ACTIVATION_PENDING,
            'owner' => self::OWNER_PRODUCT_PUBLICATION_POLICY,
            'activationPrerequisites' => ['product_publication_policy', 'catalog_quality_gate', 'pdp_runtime_verification'],
        ],
        self::SURFACE_CATEGORIES => [
            'state' => self::STATE_ACTIVATION_PENDING,
            'owner' => self::OWNER_CATEGORY_PUBLICATION_POLICY,
            'activationPrerequisites' => ['category_publication_policy', 'taxonomy_quality_gate', 'navigation_runtime_verification'],
        ],
        self::SURFACE_COLLECTIONS => [
            'state' => self::STATE_ACTIVATION_PENDING,
            'owner' => self::OWNER_COLLECTION_POLICY,
            'activationPrerequisites' => ['collection_publication_policy', 'collection_taxonomy_gate', 'listing_runtime_verification'],
        ],
        self::SURFACE_SEARCH => [
            'state' => self::STATE_ACTIVATION_PENDING,
            'owner' => self::OWNER_SEARCH_ARCHITECTURE,
            'activationPrerequisites' => ['search_governance', 'indexing_readiness', 'search_runtime_verification'],
        ],
        self::SURFACE_CART => [
            'state' => self::STATE_ACTIVATION_PENDING,
            'owner' => self::OWNER_NATIVE_COMMERCE,
            'activationPrerequisites' => ['sellability_policy', 'cart_runtime_verification'],
        ],
        self::SURFACE_CHECKOUT => [
            'state' => self::STATE_ACTIVATION_PENDING,
            'owner' => self::OWNER_NATIVE_COMMERCE,
            'activationPrerequisites' => ['cart_activation', 'payment_shipping_readiness', 'checkout_runtime_verification'],
        ],
        self::SURFACE_ACCOUNT => [
            'state' => self::STATE_ACTIVATION_PENDING,
            'owner' => self::OWNER_NATIVE_COMMERCE,
            'activationPrerequisites' => ['customer_account_policy', 'account_runtime_verification'],
        ],
        self::SURFACE_WISHLIST => [
            'state' => self::STATE_ACTIVATION_PENDING,
            'owner' => self::OWNER_COMMERCE_POLICY,
            'activationPrerequisites' => ['wishlist_business_decision', 'wishlist_runtime_verification'],
        ],
        self::SURFACE_CONSULTATION => [
            'state' => self::STATE_PUBLIC,
            'owner' => self::OWNER_ACQUISITION_POLICY,
            'activationPrerequisites' => [],
        ],
        self::SURFACE_TRADE => [
            'state' => self::STATE_ACTIVATION_PENDING,
            'owner' => self::OWNER_ACQUISITION_POLICY,
            'activationPrerequisites' => ['trade_workflow_policy', 'trade_runtime_verification'],
        ],
        self::SURFACE_EDITIONS => [
            'state' => self::STATE_GOVERNED_PUBLIC,
            'owner' => self::OWNER_EDITION_GOVERNANCE,
            'activationPrerequisites' => ['publication_state_policy', 'identity_retrieval_mediation'],
        ],
    ];

    /**
     * @return array{state: string, owner: string, activationPrerequisites: list<string>}|null
     */
    public static function ownershipForSurface(string $surface): ?array
    {
        return self::OWNERSHIP[$surface] ?? null;
    }

    public static function surfaceForRoute(string $routeName, ?string $navigationId = null): ?string
    {
        if ($routeName === 'frontend.home.page') {
            return self::SURFACE_HOMEPAGE;
        }

        if ($routeName === 'frontend.navigation.page') {
            return match ($navigationId) {
                self::COLLECTIONS_CATEGORY_ID => self::SURFACE_COLLECTIONS,
                self::CONSULTATION_CATEGORY_ID => self::SURFACE_CONSULTATION,
                self::TRADE_CATEGORY_ID => self::SURFACE_TRADE,
                default => self::SURFACE_CATEGORIES,
            };
        }

        if ($routeName === 'frontend.detail.page' || str_starts_with($routeName, 'frontend.detail.')) {
            return self::SURFACE_PRODUCTS;
        }

        if (str_starts_with($routeName, 'frontend.search.')) {
            return self::SURFACE_SEARCH;
        }

        if ($routeName === 'frontend.checkout.cart.page' || str_contains($routeName, 'line-item')) {
            return self::SURFACE_CART;
        }

        if (str_starts_with($routeName, 'frontend.checkout.')) {
            return self::SURFACE_CHECKOUT;
        }

        if (str_starts_with($routeName, 'frontend.account.')) {
            return self::SURFACE_ACCOUNT;
        }

        if (str_starts_with($routeName, 'frontend.wishlist.')) {
            return self::SURFACE_WISHLIST;
        }

        if (str_starts_with($routeName, 'frontend.veylune.editions.')) {
            return self::SURFACE_EDITIONS;
        }

        return null;
    }

    public static function isActivationPendingRoute(string $routeName, ?string $navigationId = null): bool
    {
        $surface = self::surfaceForRoute($routeName, $navigationId);

        return $surface !== null && (self::OWNERSHIP[$surface]['state'] ?? null) === self::STATE_ACTIVATION_PENDING;
    }
}
