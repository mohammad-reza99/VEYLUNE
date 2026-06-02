<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class ProductLifecycleContract
{
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
        self::STATE_SUSPENDED => [self::STATE_APPROVED, self::STATE_PUBLISHED, self::STATE_ARCHIVED],
        self::STATE_ARCHIVED => [],
    ];

    /**
     * @return list<string>
     */
    public static function states(): array
    {
        return self::STATES;
    }

    public static function isGovernedState(string $state): bool
    {
        return \in_array($state, self::STATES, true);
    }

    public static function isPubliclyEligible(string $state): bool
    {
        return $state === self::STATE_PUBLISHED;
    }

    public static function canTransition(string $from, string $to): bool
    {
        return \in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }
}
