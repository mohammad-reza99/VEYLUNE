<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class CanonicalSkuContract
{
    public const OWNER_SKU_GOVERNANCE = 'sku_governance';

    public const STATE_RESERVED = 'reserved';
    public const STATE_ASSIGNED = 'assigned';
    public const STATE_PUBLISHED = 'published';
    public const STATE_RETIRED = 'retired';

    private const STATES = [
        self::STATE_RESERVED,
        self::STATE_ASSIGNED,
        self::STATE_PUBLISHED,
        self::STATE_RETIRED,
    ];

    private const STRUCTURE = [
        'prefix' => 'VLS',
        'department_segment' => 'three_uppercase_letters',
        'sequence_segment' => 'six_digits',
        'variant_segment' => 'optional_two_digits',
        'examples' => ['VLS-FUR-000001', 'VLS-FUR-000001-01'],
    ];

    private const DUPLICATE_PREVENTION_RULES = [
        'veylune_sku_is_globally_unique',
        'supplier_id_and_supplier_sku_pair_is_unique',
        'supplier_sku_never_becomes_public_identity',
        'retired_sku_is_never_reused',
        'variant_sku_must_reference_parent_sku',
        'sku_assignment_requires_batch_reference',
    ];

    private const RETIREMENT_RULES = [
        'retirement_requires_sku_governance_approval',
        'retired_sku_remains_reserved_forever',
        'supplier_mapping_history_must_be_retained',
        'published_product_url_impact_must_be_reviewed',
        'rollback_target_must_reference_pre_retirement_state',
    ];

    /**
     * @return list<string>
     */
    public static function states(): array
    {
        return self::STATES;
    }

    /**
     * @return array{prefix: string, department_segment: string, sequence_segment: string, variant_segment: string, examples: list<string>}
     */
    public static function structure(): array
    {
        return self::STRUCTURE;
    }

    /**
     * @return list<string>
     */
    public static function duplicatePreventionRules(): array
    {
        return self::DUPLICATE_PREVENTION_RULES;
    }

    /**
     * @return list<string>
     */
    public static function retirementRules(): array
    {
        return self::RETIREMENT_RULES;
    }

    public static function isCanonicalSku(string $sku): bool
    {
        return \preg_match('/^VLS-[A-Z]{3}-[0-9]{6}(?:-[0-9]{2})?$/', $sku) === 1;
    }

    public static function isPublicIdentityOwnedByVeylune(string $field): bool
    {
        return $field === 'veylune_sku';
    }
}
