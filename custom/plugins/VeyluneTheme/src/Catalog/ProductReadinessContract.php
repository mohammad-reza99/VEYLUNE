<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class ProductReadinessContract
{
    public const OWNER_PRODUCT_GOVERNANCE = 'product_governance';

    private const REVIEW_REQUIREMENTS = [
        'identity' => ['veylune_sku', 'supplier_id', 'supplier_sku', 'source_batch'],
        'content' => ['en_title', 'de_title', 'en_description', 'de_description'],
        'media' => ['primary_image', 'detail_image', 'image_rights', 'en_alt_text', 'de_alt_text'],
        'physical' => ['width', 'height', 'depth', 'weight', 'dimensions_unit'],
        'materials' => ['primary_material', 'finish', 'color'],
        'taxonomy' => ['department', 'category', 'product_type', 'room'],
        'commerce' => ['price', 'tax', 'lead_time', 'delivery_class', 'returns_class'],
        'seo' => ['en_seo_title', 'de_seo_title', 'en_meta_description', 'de_meta_description'],
        'governance' => ['quality_status', 'reviewer', 'rollback_target'],
    ];

    /**
     * @return array<string, list<string>>
     */
    public static function reviewRequirements(): array
    {
        return self::REVIEW_REQUIREMENTS;
    }

    /**
     * @return list<string>
     */
    public static function requirementsForDomain(string $domain): array
    {
        return self::REVIEW_REQUIREMENTS[$domain] ?? [];
    }

    public static function owner(): string
    {
        return self::OWNER_PRODUCT_GOVERNANCE;
    }
}
