<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class CatalogOwnershipContract
{
    public const RESOURCE_PRODUCTS = 'products';
    public const RESOURCE_CATEGORIES = 'categories';
    public const RESOURCE_COLLECTIONS = 'collections';
    public const RESOURCE_PROPERTIES = 'properties';
    public const RESOURCE_MEDIA = 'media';

    public const OWNER_PRODUCT_GOVERNANCE = 'product_governance';
    public const OWNER_TAXONOMY_GOVERNANCE = 'taxonomy_governance';
    public const OWNER_COLLECTION_GOVERNANCE = 'collection_governance';
    public const OWNER_PROPERTY_DICTIONARY_GOVERNANCE = 'property_dictionary_governance';
    public const OWNER_MEDIA_GOVERNANCE = 'media_governance';

    private const OWNERSHIP = [
        self::RESOURCE_PRODUCTS => self::OWNER_PRODUCT_GOVERNANCE,
        self::RESOURCE_CATEGORIES => self::OWNER_TAXONOMY_GOVERNANCE,
        self::RESOURCE_COLLECTIONS => self::OWNER_COLLECTION_GOVERNANCE,
        self::RESOURCE_PROPERTIES => self::OWNER_PROPERTY_DICTIONARY_GOVERNANCE,
        self::RESOURCE_MEDIA => self::OWNER_MEDIA_GOVERNANCE,
    ];

    public static function ownerForResource(string $resource): ?string
    {
        return self::OWNERSHIP[$resource] ?? null;
    }
}
