<?php declare(strict_types=1);

namespace VeyluneTheme\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;

class Migration1780228800CreateChannelTopologyFoundation extends MigrationStep
{
    private const SOURCE_STOREFRONT_CHANNEL_ID = '019e3bf9c220717884d2a4eaca77c2d1';

    /**
     * The foundations remain inactive and domainless until their dedicated
     * isolation work packages are implemented and approved.
     *
     * @var list<array{id: string, rootId: string, name: string, shortName: string}>
     */
    private const FOUNDATIONS = [
        [
            'id' => '019e9e8f000070008000000000000001',
            'rootId' => '019e9e8f000070008000000000000011',
            'name' => 'VEYLUNE Identity Foundation',
            'shortName' => 'Veylune Identity',
        ],
        [
            'id' => '019e9e8f000070008000000000000002',
            'rootId' => '019e9e8f000070008000000000000012',
            'name' => 'VEYLUNE Acquisition Foundation',
            'shortName' => 'Veylune Acquisition',
        ],
        [
            'id' => '019e9e8f000070008000000000000003',
            'rootId' => '019e9e8f000070008000000000000013',
            'name' => 'VEYLUNE Private Commerce Foundation',
            'shortName' => 'Veylune Commerce',
        ],
    ];

    public function getCreationTimestamp(): int
    {
        return 1780228800;
    }

    public function update(Connection $connection): void
    {
        $createdAt = (new \DateTimeImmutable())->format('Y-m-d H:i:s.v');
        $liveVersionId = Uuid::fromHexToBytes(Defaults::LIVE_VERSION);

        foreach (self::FOUNDATIONS as $foundation) {
            $this->createInactiveNavigationRoot($connection, $foundation, $liveVersionId, $createdAt);
            $this->createInactiveDomainlessChannel($connection, $foundation, $liveVersionId, $createdAt);
            $this->cloneChannelTranslations($connection, $foundation, $createdAt);
            $this->cloneChannelAssociations($connection, $foundation);
        }
    }

    public function updateDestructive(Connection $connection): void
    {
    }

    /**
     * @param array{id: string, rootId: string, name: string, shortName: string} $foundation
     */
    private function createInactiveNavigationRoot(Connection $connection, array $foundation, string $liveVersionId, string $createdAt): void
    {
        $connection->executeStatement(
            <<<'SQL'
                INSERT INTO `category`
                    (`id`, `version_id`, `active`, `visible`, `type`, `created_at`)
                VALUES
                    (:id, :versionId, 0, 0, 'page', :createdAt)
                ON DUPLICATE KEY UPDATE
                    `active` = 0,
                    `visible` = 0,
                    `updated_at` = VALUES(`created_at`)
            SQL,
            [
                'id' => Uuid::fromHexToBytes($foundation['rootId']),
                'versionId' => $liveVersionId,
                'createdAt' => $createdAt,
            ]
        );

        $connection->executeStatement(
            <<<'SQL'
                INSERT INTO `category_translation`
                    (`category_id`, `category_version_id`, `language_id`, `name`, `created_at`)
                SELECT
                    :categoryId,
                    :versionId,
                    `id`,
                    :name,
                    :createdAt
                FROM `language`
                ON DUPLICATE KEY UPDATE
                    `name` = VALUES(`name`),
                    `updated_at` = VALUES(`created_at`)
            SQL,
            [
                'categoryId' => Uuid::fromHexToBytes($foundation['rootId']),
                'versionId' => $liveVersionId,
                'name' => $foundation['name'] . ' Root',
                'createdAt' => $createdAt,
            ]
        );
    }

    /**
     * @param array{id: string, rootId: string, name: string, shortName: string} $foundation
     */
    private function createInactiveDomainlessChannel(Connection $connection, array $foundation, string $liveVersionId, string $createdAt): void
    {
        $connection->executeStatement(
            <<<'SQL'
                INSERT INTO `sales_channel`
                    (`id`, `type_id`, `short_name`, `configuration`, `access_key`, `language_id`, `currency_id`,
                     `payment_method_id`, `shipping_method_id`, `country_id`, `navigation_category_id`,
                     `navigation_category_version_id`, `navigation_category_depth`, `hreflang_active`, `active`,
                     `maintenance`, `maintenance_ip_whitelist`, `customer_group_id`, `mail_header_footer_id`,
                     `payment_method_ids`, `tax_calculation_type`, `home_cms_page_id`, `home_cms_page_version_id`,
                     `measurement_units`, `created_at`)
                SELECT
                    :id, `type_id`, :shortName, `configuration`, :accessKey, `language_id`, `currency_id`,
                    `payment_method_id`, `shipping_method_id`, `country_id`, :navigationCategoryId,
                    :navigationCategoryVersionId, `navigation_category_depth`, 0, 0,
                    0, `maintenance_ip_whitelist`, `customer_group_id`, `mail_header_footer_id`,
                    `payment_method_ids`, `tax_calculation_type`, `home_cms_page_id`, `home_cms_page_version_id`,
                    `measurement_units`, :createdAt
                FROM `sales_channel`
                WHERE `id` = :sourceId
                ON DUPLICATE KEY UPDATE
                    `short_name` = VALUES(`short_name`),
                    `navigation_category_id` = VALUES(`navigation_category_id`),
                    `navigation_category_version_id` = VALUES(`navigation_category_version_id`),
                    `hreflang_active` = 0,
                    `active` = 0,
                    `maintenance` = 0,
                    `updated_at` = VALUES(`created_at`)
            SQL,
            [
                'id' => Uuid::fromHexToBytes($foundation['id']),
                'shortName' => $foundation['shortName'],
                'accessKey' => 'SWSC' . strtoupper(Uuid::randomHex()),
                'navigationCategoryId' => Uuid::fromHexToBytes($foundation['rootId']),
                'navigationCategoryVersionId' => $liveVersionId,
                'createdAt' => $createdAt,
                'sourceId' => Uuid::fromHexToBytes(self::SOURCE_STOREFRONT_CHANNEL_ID),
            ]
        );
    }

    /**
     * @param array{id: string, rootId: string, name: string, shortName: string} $foundation
     */
    private function cloneChannelTranslations(Connection $connection, array $foundation, string $createdAt): void
    {
        $connection->executeStatement(
            <<<'SQL'
                INSERT INTO `sales_channel_translation`
                    (`sales_channel_id`, `language_id`, `name`, `home_keywords`, `home_meta_description`,
                     `home_meta_title`, `home_name`, `home_enabled`, `home_slot_config`, `custom_fields`, `created_at`)
                SELECT
                    :id, `language_id`, :name, `home_keywords`, `home_meta_description`,
                    `home_meta_title`, `home_name`, `home_enabled`, `home_slot_config`, `custom_fields`, :createdAt
                FROM `sales_channel_translation`
                WHERE `sales_channel_id` = :sourceId
                ON DUPLICATE KEY UPDATE
                    `name` = VALUES(`name`),
                    `updated_at` = VALUES(`created_at`)
            SQL,
            [
                'id' => Uuid::fromHexToBytes($foundation['id']),
                'name' => $foundation['name'],
                'createdAt' => $createdAt,
                'sourceId' => Uuid::fromHexToBytes(self::SOURCE_STOREFRONT_CHANNEL_ID),
            ]
        );
    }

    /**
     * @param array{id: string, rootId: string, name: string, shortName: string} $foundation
     */
    private function cloneChannelAssociations(Connection $connection, array $foundation): void
    {
        foreach ([
            'sales_channel_country' => 'country_id',
            'sales_channel_currency' => 'currency_id',
            'sales_channel_language' => 'language_id',
            'sales_channel_payment_method' => 'payment_method_id',
            'sales_channel_shipping_method' => 'shipping_method_id',
        ] as $table => $foreignKey) {
            $connection->executeStatement(
                sprintf(
                    'INSERT IGNORE INTO `%s` (`sales_channel_id`, `%s`) SELECT :id, `%s` FROM `%s` WHERE `sales_channel_id` = :sourceId',
                    $table,
                    $foreignKey,
                    $foreignKey,
                    $table
                ),
                [
                    'id' => Uuid::fromHexToBytes($foundation['id']),
                    'sourceId' => Uuid::fromHexToBytes(self::SOURCE_STOREFRONT_CHANNEL_ID),
                ]
            );
        }
    }
}
