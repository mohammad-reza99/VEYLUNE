<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class ProductQualityGateContract
{
    public const OWNER_PRODUCT_GOVERNANCE = 'product_governance';

    private const APPROVAL_REQUIREMENTS = [
        'content_quality' => ['en_de_parity', 'premium_tone_review', 'care_guidance_review'],
        'media_quality' => ['primary_image_quality', 'detail_image_quality', 'rights_status_verified'],
        'taxonomy_validation' => ['canonical_department', 'canonical_category', 'category_lifecycle_approved'],
        'property_validation' => ['controlled_material', 'controlled_finish', 'controlled_color', 'controlled_room'],
        'commerce_validation' => ['price_review', 'tax_review', 'delivery_review', 'returns_review'],
        'supplier_validation' => ['supplier_active', 'supplier_sku_mapping', 'batch_approved'],
        'governance_validation' => ['publication_state_review', 'rollback_target_review', 'reviewer_approval'],
    ];

    /**
     * @return array<string, list<string>>
     */
    public static function approvalRequirements(): array
    {
        return self::APPROVAL_REQUIREMENTS;
    }

    /**
     * @return list<string>
     */
    public static function requirementsForGate(string $gate): array
    {
        return self::APPROVAL_REQUIREMENTS[$gate] ?? [];
    }

    public static function owner(): string
    {
        return self::OWNER_PRODUCT_GOVERNANCE;
    }
}
