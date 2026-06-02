<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class BatchGovernanceContract
{
    public const OWNER_BATCH_GOVERNANCE = 'batch_governance';

    public const STATE_RECEIVED = 'received';
    public const STATE_MAPPED = 'mapped';
    public const STATE_REVIEW = 'review';
    public const STATE_APPROVED = 'approved';
    public const STATE_APPLIED = 'applied';
    public const STATE_ROLLED_BACK = 'rolled_back';
    public const STATE_ARCHIVED = 'archived';

    private const STATES = [
        self::STATE_RECEIVED,
        self::STATE_MAPPED,
        self::STATE_REVIEW,
        self::STATE_APPROVED,
        self::STATE_APPLIED,
        self::STATE_ROLLED_BACK,
        self::STATE_ARCHIVED,
    ];

    private const IDENTITY_FIELDS = [
        'batch_id',
        'supplier_id',
        'source_reference',
        'received_at',
        'source_hash',
        'review_owner',
        'rollback_target',
        'batch_state',
    ];

    private const ROLLBACK_TARGET_RULES = [
        'rollback_target_required_before_apply',
        'rollback_target_must_reference_previous_catalog_snapshot',
        'rollback_target_must_include_sku_mapping_snapshot',
        'rollback_target_must_include_media_reference_snapshot',
        'rollback_target_must_include_property_mapping_snapshot',
    ];

    /**
     * @return list<string>
     */
    public static function states(): array
    {
        return self::STATES;
    }

    /**
     * @return list<string>
     */
    public static function identityFields(): array
    {
        return self::IDENTITY_FIELDS;
    }

    /**
     * @return list<string>
     */
    public static function rollbackTargetRules(): array
    {
        return self::ROLLBACK_TARGET_RULES;
    }

    public static function owner(): string
    {
        return self::OWNER_BATCH_GOVERNANCE;
    }

    public static function isApplyEligible(string $state): bool
    {
        return $state === self::STATE_APPROVED;
    }
}
