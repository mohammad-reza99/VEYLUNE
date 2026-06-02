<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class SellabilityGovernanceContract
{
    public const OWNER_SELLABILITY_GOVERNANCE = 'sellability_governance';

    public const STATUS_SELLABLE = 'sellable';
    public const STATUS_NOT_SELLABLE = 'not_sellable';

    private const STATUSES = [
        self::STATUS_SELLABLE,
        self::STATUS_NOT_SELLABLE,
    ];

    private const REQUIREMENTS = [
        'pricing' => ['gross_price', 'tax_class', 'currency', 'pricing_owner_review'],
        'media' => ['primary_image', 'image_rights', 'alt_text_ready'],
        'content' => ['en_content', 'de_content', 'care_guidance'],
        'taxonomy' => ['canonical_department', 'canonical_category', 'property_dictionary_mapping'],
        'supplier' => ['active_supplier', 'supplier_sku_mapping', 'lead_time'],
        'compliance' => ['media_rights', 'returns_class', 'delivery_class'],
    ];

    private const INDEPENDENCE_RULES = [
        'sellable_never_implies_published',
        'published_never_implies_sellable',
        'stock_never_implies_sellable',
        'shopware_active_never_implies_sellable',
    ];

    /**
     * @return list<string>
     */
    public static function statuses(): array
    {
        return self::STATUSES;
    }

    /**
     * @return array<string, list<string>>
     */
    public static function requirements(): array
    {
        return self::REQUIREMENTS;
    }

    /**
     * @return list<string>
     */
    public static function independenceRules(): array
    {
        return self::INDEPENDENCE_RULES;
    }

    public static function owner(): string
    {
        return self::OWNER_SELLABILITY_GOVERNANCE;
    }

    public static function isSellable(string $status): bool
    {
        return $status === self::STATUS_SELLABLE;
    }
}
