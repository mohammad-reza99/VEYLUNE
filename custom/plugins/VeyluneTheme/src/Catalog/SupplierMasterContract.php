<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class SupplierMasterContract
{
    public const OWNER_SUPPLIER_GOVERNANCE = 'supplier_governance';

    public const STATUS_PROSPECT = 'prospect';
    public const STATUS_REVIEW = 'review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_RETIRED = 'retired';

    private const STATUSES = [
        self::STATUS_PROSPECT,
        self::STATUS_REVIEW,
        self::STATUS_APPROVED,
        self::STATUS_ACTIVE,
        self::STATUS_SUSPENDED,
        self::STATUS_RETIRED,
    ];

    private const TRANSITIONS = [
        self::STATUS_PROSPECT => [self::STATUS_REVIEW, self::STATUS_RETIRED],
        self::STATUS_REVIEW => [self::STATUS_PROSPECT, self::STATUS_APPROVED, self::STATUS_RETIRED],
        self::STATUS_APPROVED => [self::STATUS_REVIEW, self::STATUS_ACTIVE, self::STATUS_RETIRED],
        self::STATUS_ACTIVE => [self::STATUS_SUSPENDED, self::STATUS_RETIRED],
        self::STATUS_SUSPENDED => [self::STATUS_REVIEW, self::STATUS_ACTIVE, self::STATUS_RETIRED],
        self::STATUS_RETIRED => [],
    ];

    private const IDENTITY_FIELDS = [
        'supplier_id',
        'legal_name',
        'display_name',
        'country',
        'primary_contact',
        'commercial_terms_owner',
        'compliance_owner',
        'source_system',
        'supplier_status',
    ];

    /**
     * @return list<string>
     */
    public static function statuses(): array
    {
        return self::STATUSES;
    }

    /**
     * @return list<string>
     */
    public static function identityFields(): array
    {
        return self::IDENTITY_FIELDS;
    }

    public static function owner(): string
    {
        return self::OWNER_SUPPLIER_GOVERNANCE;
    }

    public static function canTransition(string $from, string $to): bool
    {
        return \in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    public static function isImportEligible(string $status): bool
    {
        return $status === self::STATUS_ACTIVE;
    }
}
