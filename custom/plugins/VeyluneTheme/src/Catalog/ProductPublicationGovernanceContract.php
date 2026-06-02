<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class ProductPublicationGovernanceContract
{
    public const OWNER_PRODUCT_GOVERNANCE = 'product_governance';

    public const STATE_DRAFT = 'draft';
    public const STATE_REVIEW = 'review';
    public const STATE_APPROVED = 'approved';
    public const STATE_PUBLISHED = 'published';
    public const STATE_SUSPENDED = 'suspended';
    public const STATE_ARCHIVED = 'archived';

    private const STATES = [
        self::STATE_DRAFT,
        self::STATE_REVIEW,
        self::STATE_APPROVED,
        self::STATE_PUBLISHED,
        self::STATE_SUSPENDED,
        self::STATE_ARCHIVED,
    ];

    private const TRANSITIONS = [
        self::STATE_DRAFT => [self::STATE_REVIEW, self::STATE_ARCHIVED],
        self::STATE_REVIEW => [self::STATE_DRAFT, self::STATE_APPROVED, self::STATE_ARCHIVED],
        self::STATE_APPROVED => [self::STATE_DRAFT, self::STATE_REVIEW, self::STATE_PUBLISHED, self::STATE_ARCHIVED],
        self::STATE_PUBLISHED => [self::STATE_SUSPENDED, self::STATE_ARCHIVED],
        self::STATE_SUSPENDED => [self::STATE_REVIEW, self::STATE_APPROVED, self::STATE_ARCHIVED],
        self::STATE_ARCHIVED => [],
    ];

    private const INDEPENDENCE_RULES = [
        'shopware_active_never_implies_publication',
        'stock_never_implies_publication',
        'visibility_never_implies_publication',
        'supplier_status_never_implies_publication',
        'sellability_never_implies_publication',
        'import_approval_never_implies_publication',
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
    public static function independenceRules(): array
    {
        return self::INDEPENDENCE_RULES;
    }

    public static function owner(): string
    {
        return self::OWNER_PRODUCT_GOVERNANCE;
    }

    public static function canTransition(string $from, string $to): bool
    {
        return \in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    public static function isPubliclyEligible(string $state): bool
    {
        return $state === self::STATE_PUBLISHED;
    }
}
