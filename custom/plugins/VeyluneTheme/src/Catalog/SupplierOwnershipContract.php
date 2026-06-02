<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class SupplierOwnershipContract
{
    public const OWNER_SUPPLIER = 'supplier';
    public const OWNER_VEYLUNE = 'veylune';

    private const SUPPLIER_OWNED_FIELDS = [
        'supplier_id',
        'supplier_sku',
        'source_product_facts',
        'source_stock',
        'source_cost',
        'source_price_input',
        'source_lead_time',
        'source_media',
        'media_usage_rights',
        'compliance_data',
    ];

    private const VEYLUNE_OWNED_FIELDS = [
        'veylune_sku',
        'canonical_product_identity',
        'taxonomy',
        'property_dictionary_mapping',
        'public_copy',
        'seo_metadata',
        'publication_state',
        'merchandising',
        'final_pricing_policy',
        'customer_experience',
    ];

    /**
     * @return list<string>
     */
    public static function supplierOwnedFields(): array
    {
        return self::SUPPLIER_OWNED_FIELDS;
    }

    /**
     * @return list<string>
     */
    public static function veyluneOwnedFields(): array
    {
        return self::VEYLUNE_OWNED_FIELDS;
    }

    public static function ownerForField(string $field): ?string
    {
        if (\in_array($field, self::SUPPLIER_OWNED_FIELDS, true)) {
            return self::OWNER_SUPPLIER;
        }

        if (\in_array($field, self::VEYLUNE_OWNED_FIELDS, true)) {
            return self::OWNER_VEYLUNE;
        }

        return null;
    }
}
