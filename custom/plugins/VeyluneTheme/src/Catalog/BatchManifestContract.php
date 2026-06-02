<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class BatchManifestContract
{
    public const OWNER_BATCH_GOVERNANCE = 'batch_governance';

    private const REQUIRED_FIELDS = [
        'batch_id',
        'supplier_id',
        'source_reference',
        'source_hash',
        'received_at',
        'owner',
        'rollback_target',
        'items',
    ];

    private const REQUIRED_ITEM_FIELDS = [
        'veylune_sku',
        'supplier_sku',
        'publication_state',
        'sellability_state',
        'taxonomy',
        'properties',
        'media',
        'content',
        'commerce',
        'seo',
    ];

    /**
     * @return list<string>
     */
    public static function requiredFields(): array
    {
        return self::REQUIRED_FIELDS;
    }

    /**
     * @return list<string>
     */
    public static function requiredItemFields(): array
    {
        return self::REQUIRED_ITEM_FIELDS;
    }

    /**
     * @param array<string, mixed> $manifest
     *
     * @return list<string>
     */
    public static function missingFields(array $manifest): array
    {
        return \array_values(\array_filter(
            self::REQUIRED_FIELDS,
            static fn (string $field): bool => !\array_key_exists($field, $manifest) || $manifest[$field] === null || $manifest[$field] === ''
        ));
    }

    /**
     * @param array<string, mixed> $item
     *
     * @return list<string>
     */
    public static function missingItemFields(array $item): array
    {
        return \array_values(\array_filter(
            self::REQUIRED_ITEM_FIELDS,
            static fn (string $field): bool => !\array_key_exists($field, $item) || $item[$field] === null || $item[$field] === ''
        ));
    }
}
