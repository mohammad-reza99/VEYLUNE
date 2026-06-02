<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class SupplierStagingContract
{
    public const OWNER_SUPPLIER_GOVERNANCE = 'supplier_governance';

    public const STATE_PROSPECT = 'prospect';
    public const STATE_REVIEW = 'review';
    public const STATE_APPROVED = 'approved';
    public const STATE_ACTIVE = 'active';

    private const STATES = [
        self::STATE_PROSPECT,
        self::STATE_REVIEW,
        self::STATE_APPROVED,
        self::STATE_ACTIVE,
    ];

    private const TRANSITIONS = [
        self::STATE_PROSPECT => [self::STATE_REVIEW],
        self::STATE_REVIEW => [self::STATE_PROSPECT, self::STATE_APPROVED],
        self::STATE_APPROVED => [self::STATE_REVIEW, self::STATE_ACTIVE],
        self::STATE_ACTIVE => [self::STATE_REVIEW],
    ];

    private const STAGING_REQUIREMENTS = [
        'supplier_id',
        'legal_name',
        'display_name',
        'primary_contact',
        'commercial_terms_owner',
        'compliance_owner',
        'source_system',
        'media_rights_policy',
        'returns_policy',
        'lead_time_policy',
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
    public static function stagingRequirements(): array
    {
        return self::STAGING_REQUIREMENTS;
    }

    public static function canTransition(string $from, string $to): bool
    {
        return \in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    public static function isStagingEligible(string $state): bool
    {
        return $state === self::STATE_APPROVED || $state === self::STATE_ACTIVE;
    }
}
