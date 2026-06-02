<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class StructuredProductContract
{
    public const GROUP_IDENTITY = 'identity';
    public const GROUP_CLASSIFICATION = 'classification';
    public const GROUP_MATERIALS = 'materials';
    public const GROUP_PHYSICAL = 'physical';
    public const GROUP_COMMERCE = 'commerce';
    public const GROUP_CONTENT = 'content';
    public const GROUP_MEDIA = 'media';
    public const GROUP_GOVERNANCE = 'governance';

    private const REQUIRED_FACTS = [
        self::GROUP_IDENTITY => ['veylune_sku', 'supplier_id', 'supplier_sku', 'manufacturer'],
        self::GROUP_CLASSIFICATION => ['department', 'category', 'product_type', 'room'],
        self::GROUP_MATERIALS => ['primary_material', 'finish', 'color'],
        self::GROUP_PHYSICAL => ['width', 'height', 'depth', 'weight', 'assembly_requirements'],
        self::GROUP_COMMERCE => ['price', 'tax', 'stock', 'sellability', 'lead_time', 'delivery_class', 'returns_class'],
        self::GROUP_CONTENT => ['en_title', 'de_title', 'en_description', 'de_description', 'en_seo_metadata', 'de_seo_metadata', 'care_guidance'],
        self::GROUP_MEDIA => ['primary_image', 'detail_image', 'en_alt_text', 'de_alt_text', 'rights_status'],
        self::GROUP_GOVERNANCE => ['publication_state', 'quality_status', 'reviewer', 'source_batch', 'rollback_target'],
    ];

    private const OPTIONAL_FACTS = [
        self::GROUP_IDENTITY => ['ean'],
        self::GROUP_CLASSIFICATION => ['subcategory', 'commercial_collection', 'editorial_collection', 'style'],
        self::GROUP_MATERIALS => ['secondary_material'],
        self::GROUP_PHYSICAL => [],
        self::GROUP_COMMERCE => [],
        self::GROUP_CONTENT => ['editorial_story'],
        self::GROUP_MEDIA => ['context_image', 'dimensions_diagram'],
        self::GROUP_GOVERNANCE => [],
    ];

    /**
     * @return array<string, list<string>>
     */
    public static function requiredFacts(): array
    {
        return self::REQUIRED_FACTS;
    }

    /**
     * @return list<string>
     */
    public static function requiredFactsForGroup(string $group): array
    {
        return self::REQUIRED_FACTS[$group] ?? [];
    }

    /**
     * @return array<string, list<string>>
     */
    public static function optionalFacts(): array
    {
        return self::OPTIONAL_FACTS;
    }
}
