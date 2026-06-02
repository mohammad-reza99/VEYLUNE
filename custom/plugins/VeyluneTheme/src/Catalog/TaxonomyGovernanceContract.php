<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class TaxonomyGovernanceContract
{
    public const LAYER_DEPARTMENTS = 'departments';
    public const LAYER_CATEGORIES = 'categories';
    public const LAYER_SUBCATEGORIES = 'subcategories';
    public const LAYER_COMMERCIAL_COLLECTIONS = 'commercial_collections';
    public const LAYER_EDITORIAL_COLLECTIONS = 'editorial_collections';

    public const OWNER_TAXONOMY_GOVERNANCE = 'taxonomy_governance';
    public const OWNER_COLLECTION_GOVERNANCE = 'collection_governance';
    public const OWNER_EDITORIAL_GOVERNANCE = 'editorial_governance';

    private const LAYERS = [
        self::LAYER_DEPARTMENTS => [
            'owner' => self::OWNER_TAXONOMY_GOVERNANCE,
            'purpose' => 'stable_shopping_navigation',
        ],
        self::LAYER_CATEGORIES => [
            'owner' => self::OWNER_TAXONOMY_GOVERNANCE,
            'purpose' => 'primary_product_classification',
        ],
        self::LAYER_SUBCATEGORIES => [
            'owner' => self::OWNER_TAXONOMY_GOVERNANCE,
            'purpose' => 'assortment_depth_when_supported',
        ],
        self::LAYER_COMMERCIAL_COLLECTIONS => [
            'owner' => self::OWNER_COLLECTION_GOVERNANCE,
            'purpose' => 'conversion_merchandising',
        ],
        self::LAYER_EDITORIAL_COLLECTIONS => [
            'owner' => self::OWNER_EDITORIAL_GOVERNANCE,
            'purpose' => 'brand_and_discovery_context',
        ],
    ];

    /**
     * @return array{owner: string, purpose: string}|null
     */
    public static function governanceForLayer(string $layer): ?array
    {
        return self::LAYERS[$layer] ?? null;
    }
}
