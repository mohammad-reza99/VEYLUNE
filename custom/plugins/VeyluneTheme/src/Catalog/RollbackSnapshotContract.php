<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class RollbackSnapshotContract
{
    public const OWNER_ROLLBACK_GOVERNANCE = 'rollback_governance';

    private const REQUIRED_FIELDS = [
        'snapshot_id',
        'batch_id',
        'checkpoint',
        'owner',
        'created_at',
        'catalog_reference',
        'sku_mapping_reference',
        'property_mapping_reference',
        'media_reference',
        'restore_notes',
    ];

    /**
     * @return list<string>
     */
    public static function requiredFields(): array
    {
        return self::REQUIRED_FIELDS;
    }

    /**
     * @param array<string, mixed> $snapshot
     *
     * @return list<string>
     */
    public static function missingFields(array $snapshot): array
    {
        return \array_values(\array_filter(
            self::REQUIRED_FIELDS,
            static fn (string $field): bool => !\array_key_exists($field, $snapshot) || $snapshot[$field] === null || $snapshot[$field] === ''
        ));
    }

    /**
     * @param array<string, mixed> $snapshot
     */
    public static function hasValidCheckpoint(array $snapshot): bool
    {
        return \in_array((string) ($snapshot['checkpoint'] ?? ''), RollbackGovernanceContract::checkpoints(), true);
    }
}
