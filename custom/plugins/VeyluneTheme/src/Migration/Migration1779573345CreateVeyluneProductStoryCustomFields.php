<?php declare(strict_types=1);

namespace VeyluneTheme\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;

class Migration1779573345CreateVeyluneProductStoryCustomFields extends MigrationStep
{
    private const FIELD_SET_NAME = 'veylune_product_story';
    private const FIELD_SET_ID = '019e5f12c4df7d559407529f2e67529f';

    public function getCreationTimestamp(): int
    {
        return 1779573345;
    }

    public function update(Connection $connection): void
    {
        $createdAt = (new \DateTimeImmutable())->format('Y-m-d H:i:s.v');

        $this->upsertFieldSet($connection, $createdAt);
        $this->upsertFieldSetRelation($connection, $createdAt);

        foreach ($this->getFields() as $field) {
            $this->upsertCustomField($connection, $field, $createdAt);
        }
    }

    public function updateDestructive(Connection $connection): void
    {
    }

    /**
     * @param array{name: string, id: string, type: string, componentName: string, customFieldType: string, label: string, helpText: string, position: int} $field
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
            'componentName' => $field['componentName'],
            'customFieldType' => $field['customFieldType'],
            'customFieldPosition' => $field['position'],
        ];

        if ($field['componentName'] === 'sw-field') {
            $config['type'] = 'text';
        }

        $connection->executeStatement(
            <<<'SQL'
                INSERT INTO `custom_field`
                    (`id`, `name`, `type`, `config`, `active`, `set_id`, `created_at`, `allow_customer_write`, `allow_cart_expose`, `store_api_aware`, `include_in_search`)
                VALUES
                    (:id, :name, :type, :config, 1, :setId, :createdAt, 0, 0, 1, 0)
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
                'type' => $field['type'],
                'config' => json_encode($config, \JSON_THROW_ON_ERROR),
                'setId' => Uuid::fromHexToBytes(self::FIELD_SET_ID),
                'createdAt' => $createdAt,
            ]
        );
    }

    private function upsertFieldSet(Connection $connection, string $createdAt): void
    {
        $config = [
            'label' => [
                'en-GB' => 'Veylune Product Story',
            ],
            'translated' => true,
            'customFieldPosition' => 1,
        ];

        $connection->executeStatement(
            <<<'SQL'
                INSERT INTO `custom_field_set`
                    (`id`, `name`, `config`, `active`, `position`, `global`, `created_at`)
                VALUES
                    (:id, :name, :config, 1, 1, 0, :createdAt)
                ON DUPLICATE KEY UPDATE
                    `config` = VALUES(`config`),
                    `active` = VALUES(`active`),
                    `position` = VALUES(`position`),
                    `global` = VALUES(`global`),
                    `updated_at` = VALUES(`created_at`)
            SQL,
            [
                'id' => Uuid::fromHexToBytes(self::FIELD_SET_ID),
                'name' => self::FIELD_SET_NAME,
                'config' => json_encode($config, \JSON_THROW_ON_ERROR),
                'createdAt' => $createdAt,
            ]
        );
    }

    private function upsertFieldSetRelation(Connection $connection, string $createdAt): void
    {
        $connection->executeStatement(
            <<<'SQL'
                INSERT INTO `custom_field_set_relation`
                    (`id`, `set_id`, `entity_name`, `created_at`)
                VALUES
                    (:id, :setId, 'product', :createdAt)
                ON DUPLICATE KEY UPDATE
                    `updated_at` = VALUES(`created_at`)
            SQL,
            [
                'id' => Uuid::fromHexToBytes('019e5f12e5277863b836fa213954f98c'),
                'setId' => Uuid::fromHexToBytes(self::FIELD_SET_ID),
                'createdAt' => $createdAt,
            ]
        );
    }

    /**
     * @return list<array{name: string, id: string, type: string, componentName: string, customFieldType: string, label: string, helpText: string, position: int}>
     */
    private function getFields(): array
    {
        return [
            [
                'id' => '019e5f1325bc7fe4840eef63b8b542c7',
                'name' => 'veylune_editorial_lead',
                'type' => 'text',
                'componentName' => 'sw-textarea-field',
                'customFieldType' => 'text',
                'label' => 'Editorial Lead',
                'helpText' => 'Short opening line that defines the product’s spatial role. Avoid specs, care, delivery, or generic luxury claims.',
                'position' => 1,
            ],
            [
                'id' => '019e5f13450d76859b89e7fba3305963',
                'name' => 'veylune_signature_detail',
                'type' => 'text',
                'componentName' => 'sw-field',
                'customFieldType' => 'text',
                'label' => 'Signature Detail',
                'helpText' => 'One concise distinguishing detail for quick scanning. Leave empty if it repeats the product name or description.',
                'position' => 2,
            ],
            [
                'id' => '019e5f1366f3762f9bf7f3aec833e023',
                'name' => 'veylune_craftsmanship_note',
                'type' => 'text',
                'componentName' => 'sw-textarea-field',
                'customFieldType' => 'text',
                'label' => 'Craftsmanship Note',
                'helpText' => 'Construction, proportion, making, tailoring, shaping, or finish logic. Avoid vague claims like "highest quality".',
                'position' => 3,
            ],
            [
                'id' => '019e5f138ad77438be04f7190ae45d7a',
                'name' => 'veylune_material_story',
                'type' => 'text',
                'componentName' => 'sw-textarea-field',
                'customFieldType' => 'text',
                'label' => 'Material Story',
                'helpText' => 'Sensory or visual behavior of the primary material. Do not simply repeat property values.',
                'position' => 4,
            ],
            [
                'id' => '019e5f13a8dc74b589f3342cf5c3ee3f',
                'name' => 'veylune_care_guidance',
                'type' => 'text',
                'componentName' => 'sw-textarea-field',
                'customFieldType' => 'text',
                'label' => 'Care Guidance',
                'helpText' => 'Short practical care guidance in a calm tone. Keep manuals and legal disclaimers elsewhere.',
                'position' => 5,
            ],
            [
                'id' => '019e5f13c6847f9f8fe6287120f3601d',
                'name' => 'veylune_consultation_note',
                'type' => 'text',
                'componentName' => 'sw-textarea-field',
                'customFieldType' => 'text',
                'label' => 'Consultation Note',
                'helpText' => 'Optional: boutique support for sizing, placement, material selection, or pairing. Avoid sales pressure.',
                'position' => 6,
            ],
            [
                'id' => '019e5f13e3057e9cb9bd2a2f9850e7df',
                'name' => 'veylune_collectible_identity',
                'type' => 'text',
                'componentName' => 'sw-textarea-field',
                'customFieldType' => 'text',
                'label' => 'Collectible Identity',
                'helpText' => 'Optional: design significance or collectible character. Use only when there is a real argument.',
                'position' => 7,
            ],
            [
                'id' => '019e5f140bd7791e95a83578215b9087',
                'name' => 'veylune_lead_time_note',
                'type' => 'text',
                'componentName' => 'sw-field',
                'customFieldType' => 'text',
                'label' => 'Lead-Time Note',
                'helpText' => 'Optional: editorial context for native delivery time. Do not duplicate exact delivery dates or stock data.',
                'position' => 8,
            ],
        ];
    }
}
