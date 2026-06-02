<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class ImportGovernanceContract
{
    public const OWNER_IMPORT_GOVERNANCE = 'import_governance';

    public const REVIEW_STATE_RECEIVED = 'received';
    public const REVIEW_STATE_MAPPED = 'mapped';
    public const REVIEW_STATE_VALIDATED = 'validated';
    public const REVIEW_STATE_APPROVED = 'approved';
    public const REVIEW_STATE_REJECTED = 'rejected';

    private const REVIEW_STATES = [
        self::REVIEW_STATE_RECEIVED,
        self::REVIEW_STATE_MAPPED,
        self::REVIEW_STATE_VALIDATED,
        self::REVIEW_STATE_APPROVED,
        self::REVIEW_STATE_REJECTED,
    ];

    private const APPROVAL_REQUIREMENTS = [
        'active_supplier_status',
        'canonical_sku_assignment',
        'supplier_sku_duplicate_check',
        'taxonomy_mapping_review',
        'property_dictionary_mapping_review',
        'media_rights_review',
        'commerce_fact_review',
        'content_quality_review',
        'rollback_target_defined',
    ];

    private const PUBLICATION_INDEPENDENCE_RULES = [
        'import_approval_never_publishes_product',
        'supplier_active_status_never_publishes_product',
        'shopware_active_flag_never_implies_product_publication',
        'stock_availability_never_implies_product_publication',
        'visibility_assignment_never_implies_product_publication',
        'product_lifecycle_state_is_separate_authority',
    ];

    /**
     * @return list<string>
     */
    public static function reviewStates(): array
    {
        return self::REVIEW_STATES;
    }

    /**
     * @return list<string>
     */
    public static function approvalRequirements(): array
    {
        return self::APPROVAL_REQUIREMENTS;
    }

    /**
     * @return list<string>
     */
    public static function publicationIndependenceRules(): array
    {
        return self::PUBLICATION_INDEPENDENCE_RULES;
    }

    public static function owner(): string
    {
        return self::OWNER_IMPORT_GOVERNANCE;
    }

    public static function isApprovedForApplication(string $reviewState): bool
    {
        return $reviewState === self::REVIEW_STATE_APPROVED;
    }
}
