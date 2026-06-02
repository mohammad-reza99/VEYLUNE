<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class DemoResidueQuarantineContract
{
    public const CLASSIFICATION_RETAIN = 'retain';
    public const CLASSIFICATION_REVIEW = 'review';
    public const CLASSIFICATION_REMOVE = 'remove';

    private const CLASSIFICATIONS = [
        self::CLASSIFICATION_RETAIN,
        self::CLASSIFICATION_REVIEW,
        self::CLASSIFICATION_REMOVE,
    ];

    private const SAFE_REMOVAL_SEQUENCE = [
        'capture_database_backup_and_verify_inventory',
        'deactivate_swag_platform_demo_data',
        'remove_demo_seo_urls',
        'remove_demo_product_dependency_rows',
        'remove_demo_variants_before_parent_products',
        'remove_demo_parent_products',
        'remove_demo_categories_leaf_first',
        'remove_demo_only_property_options_then_groups',
        'remove_demo_only_manufacturers',
        'remove_demo_only_media_after_reference_scan',
        'review_unclassified_media_and_cms_content_manually',
        'run_governance_and_runtime_regression_suite',
    ];

    private const ROLLBACK_CHECKPOINTS = [
        'pre_cleanup_database_backup',
        'post_plugin_deactivation',
        'post_product_cleanup',
        'post_taxonomy_cleanup',
        'post_media_cleanup',
        'post_regression_verification',
    ];

    /**
     * @return list<string>
     */
    public static function classifications(): array
    {
        return self::CLASSIFICATIONS;
    }

    public static function isValidClassification(string $classification): bool
    {
        return \in_array($classification, self::CLASSIFICATIONS, true);
    }

    /**
     * @return list<string>
     */
    public static function safeRemovalSequence(): array
    {
        return self::SAFE_REMOVAL_SEQUENCE;
    }

    /**
     * @return list<string>
     */
    public static function rollbackCheckpoints(): array
    {
        return self::ROLLBACK_CHECKPOINTS;
    }
}
