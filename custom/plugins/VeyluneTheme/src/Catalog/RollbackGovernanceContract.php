<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class RollbackGovernanceContract
{
    public const OWNER_ROLLBACK_GOVERNANCE = 'rollback_governance';

    public const SCOPE_SUPPLIER = 'supplier';
    public const SCOPE_BATCH = 'batch';
    public const SCOPE_SKU_MAPPING = 'sku_mapping';
    public const SCOPE_PROPERTY_MAPPING = 'property_mapping';
    public const SCOPE_MEDIA_MAPPING = 'media_mapping';
    public const SCOPE_CATALOG_RECORDS = 'catalog_records';

    private const SCOPES = [
        self::SCOPE_SUPPLIER,
        self::SCOPE_BATCH,
        self::SCOPE_SKU_MAPPING,
        self::SCOPE_PROPERTY_MAPPING,
        self::SCOPE_MEDIA_MAPPING,
        self::SCOPE_CATALOG_RECORDS,
    ];

    private const CHECKPOINTS = [
        'pre_supplier_activation',
        'pre_batch_mapping',
        'pre_import_application',
        'post_import_application',
        'pre_publication_review',
        'post_regression_verification',
    ];

    private const AUTHORITY_RULES = [
        'rollback_governance_owns_execution_authority',
        'supplier_governance_must_approve_supplier_status_rollback',
        'sku_governance_must_approve_sku_mapping_rollback',
        'property_dictionary_governance_must_approve_property_mapping_rollback',
        'media_governance_must_approve_media_mapping_rollback',
        'product_governance_must_approve_catalog_record_rollback',
    ];

    /**
     * @return list<string>
     */
    public static function scopes(): array
    {
        return self::SCOPES;
    }

    /**
     * @return list<string>
     */
    public static function checkpoints(): array
    {
        return self::CHECKPOINTS;
    }

    /**
     * @return list<string>
     */
    public static function authorityRules(): array
    {
        return self::AUTHORITY_RULES;
    }

    public static function owner(): string
    {
        return self::OWNER_ROLLBACK_GOVERNANCE;
    }

    public static function isGovernedScope(string $scope): bool
    {
        return \in_array($scope, self::SCOPES, true);
    }
}
