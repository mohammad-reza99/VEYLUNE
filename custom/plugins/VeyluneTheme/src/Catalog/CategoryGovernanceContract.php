<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class CategoryGovernanceContract
{
    public const OWNER_TAXONOMY_GOVERNANCE = 'taxonomy_governance';

    public const STATE_DRAFT = 'draft';
    public const STATE_REVIEW = 'review';
    public const STATE_APPROVED = 'approved';
    public const STATE_PUBLISHED = 'published';
    public const STATE_RETIRED = 'retired';

    private const STATES = [
        self::STATE_DRAFT,
        self::STATE_REVIEW,
        self::STATE_APPROVED,
        self::STATE_PUBLISHED,
        self::STATE_RETIRED,
    ];

    private const TRANSITIONS = [
        self::STATE_DRAFT => [self::STATE_REVIEW],
        self::STATE_REVIEW => [self::STATE_DRAFT, self::STATE_APPROVED],
        self::STATE_APPROVED => [self::STATE_REVIEW, self::STATE_PUBLISHED],
        self::STATE_PUBLISHED => [self::STATE_REVIEW, self::STATE_RETIRED],
        self::STATE_RETIRED => [],
    ];

    private const EXPANSION_RULES = [
        'department_must_be_canonical',
        'owner_must_be_taxonomy_governance',
        'customer_intent_must_be_distinct',
        'en_de_labels_and_seo_metadata_required',
        'subcategory_requires_sustained_assortment_depth',
        'supplier_input_never_creates_public_taxonomy',
        'collections_never_replace_stable_shopping_taxonomy',
    ];

    private const RETIREMENT_RULES = [
        'retirement_requires_taxonomy_governance_approval',
        'retired_category_is_not_publicly_eligible',
        'product_reclassification_required_before_retirement',
        'canonical_url_impact_must_be_reviewed_before_retirement',
        'historical_mapping_must_be_retained_for_rollback',
    ];

    /**
     * @return list<string>
     */
    public static function states(): array
    {
        return self::STATES;
    }

    public static function owner(): string
    {
        return self::OWNER_TAXONOMY_GOVERNANCE;
    }

    public static function isPubliclyEligible(string $state): bool
    {
        return $state === self::STATE_PUBLISHED;
    }

    public static function canTransition(string $from, string $to): bool
    {
        return \in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    /**
     * @return list<string>
     */
    public static function expansionRules(): array
    {
        return self::EXPANSION_RULES;
    }

    /**
     * @return list<string>
     */
    public static function retirementRules(): array
    {
        return self::RETIREMENT_RULES;
    }
}
