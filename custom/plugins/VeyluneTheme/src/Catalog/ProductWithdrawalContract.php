<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class ProductWithdrawalContract
{
    public const OWNER_PRODUCT_GOVERNANCE = 'product_governance';

    public const STATE_SUSPENDED = 'suspended';
    public const STATE_ARCHIVED = 'archived';

    private const WITHDRAWAL_STATES = [
        self::STATE_SUSPENDED,
        self::STATE_ARCHIVED,
    ];

    private const BEHAVIOR = [
        self::STATE_SUSPENDED => [
            'publicly_renderable' => false,
            'sellable' => false,
            'rule' => 'immediate_fail_closed_withdrawal',
            'rollback_allowed' => true,
        ],
        self::STATE_ARCHIVED => [
            'publicly_renderable' => false,
            'sellable' => false,
            'rule' => 'terminal_historical_record',
            'rollback_allowed' => false,
        ],
    ];

    /**
     * @return list<string>
     */
    public static function withdrawalStates(): array
    {
        return self::WITHDRAWAL_STATES;
    }

    /**
     * @return array{publicly_renderable: bool, sellable: bool, rule: string, rollback_allowed: bool}|null
     */
    public static function behaviorForState(string $state): ?array
    {
        return self::BEHAVIOR[$state] ?? null;
    }

    public static function owner(): string
    {
        return self::OWNER_PRODUCT_GOVERNANCE;
    }

    public static function isWithdrawn(string $state): bool
    {
        return isset(self::BEHAVIOR[$state]);
    }
}
