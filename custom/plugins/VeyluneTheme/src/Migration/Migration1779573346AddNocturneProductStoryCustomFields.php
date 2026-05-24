<?php declare(strict_types=1);

namespace VeyluneTheme\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;

class Migration1779573346AddNocturneProductStoryCustomFields extends MigrationStep
{
    private const FIELD_SET_ID = '019e5f12c4df7d559407529f2e67529f';

    public function getCreationTimestamp(): int
    {
        return 1779573346;
    }

    public function update(Connection $connection): void
    {
        $createdAt = (new \DateTimeImmutable())->format('Y-m-d H:i:s.v');

        foreach ($this->getFields() as $field) {
            $this->upsertCustomField($connection, $field, $createdAt);
        }
    }

    public function updateDestructive(Connection $connection): void
    {
    }

    /**
     * @param array{name: string, id: string, label: string, helpText: string, position: int} $field
     */
    private function upsertCustomField(Connection $connection, array $field, string $createdAt): void
    {
        $config = [
            'label' => [
                'en-GB' => $field['label'],
            ],
            'helpText' => [
                'en-GB' => $field['helpText'],
            ],
            'componentName' => 'sw-textarea-field',
            'customFieldType' => 'text',
            'customFieldPosition' => $field['position'],
        ];

        $connection->executeStatement(
            <<<'SQL'
                INSERT INTO `custom_field`
                    (`id`, `name`, `type`, `config`, `active`, `set_id`, `created_at`, `allow_customer_write`, `allow_cart_expose`, `store_api_aware`, `include_in_search`)
                VALUES
                    (:id, :name, 'text', :config, 1, :setId, :createdAt, 0, 0, 1, 0)
                ON DUPLICATE KEY UPDATE
                    `type` = VALUES(`type`),
                    `config` = VALUES(`config`),
                    `active` = VALUES(`active`),
                    `set_id` = VALUES(`set_id`),
                    `allow_customer_write` = VALUES(`allow_customer_write`),
                    `allow_cart_expose` = VALUES(`allow_cart_expose`),
                    `store_api_aware` = VALUES(`store_api_aware`),
                    `include_in_search` = VALUES(`include_in_search`),
                    `updated_at` = VALUES(`created_at`)
            SQL,
            [
                'id' => Uuid::fromHexToBytes($field['id']),
                'name' => $field['name'],
                'config' => json_encode($config, \JSON_THROW_ON_ERROR),
                'setId' => Uuid::fromHexToBytes(self::FIELD_SET_ID),
                'createdAt' => $createdAt,
            ]
        );
    }

    /**
     * @return list<array{name: string, id: string, label: string, helpText: string, position: int}>
     */
    private function getFields(): array
    {
        return [
            [
                'id' => '019e6251010170008000000000000001',
                'name' => 'veylune_spatial_presence',
                'label' => 'Spatial Presence',
                'helpText' => 'Lighting-specific spatial role: how the object occupies, anchors, or defines the surrounding space. Avoid generic atmosphere claims.',
                'position' => 9,
            ],
            [
                'id' => '019e6251010170008000000000000002',
                'name' => 'veylune_illumination_character',
                'label' => 'Illumination Character',
                'helpText' => 'Quality of light, glow, shadow behavior, diffusion, or reflected light. Keep the description precise and materially grounded.',
                'position' => 10,
            ],
            [
                'id' => '019e6251010170008000000000000003',
                'name' => 'veylune_evening_atmosphere',
                'label' => 'Evening Atmosphere',
                'helpText' => 'Restrained evening atmosphere created by the lighting object. Avoid cinematic, emotional, or decorative exaggeration.',
                'position' => 11,
            ],
        ];
    }
}
