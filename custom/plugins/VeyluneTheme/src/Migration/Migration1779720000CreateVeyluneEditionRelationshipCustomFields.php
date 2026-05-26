<?php declare(strict_types=1);

namespace VeyluneTheme\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;

class Migration1779720000CreateVeyluneEditionRelationshipCustomFields extends MigrationStep
{
    private const FIELD_SET_NAME = 'veylune_edition_relationship';
    private const FIELD_SET_ID = '019e7a14000070008000000000000001';
    private const FIELD_SET_RELATION_ID = '019e7a14000070008000000000000002';

    public function getCreationTimestamp(): int
    {
        return 1779720000;
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
     * @param array{
     *     id: string,
     *     name: string,
     *     type: string,
     *     componentName: string,
     *     customFieldType: string,
     *     label: array<string, string>,
     *     helpText: array<string, string>,
     *     position: int,
     *     options?: list<array{value: string, label: array<string, string>}>
     * } $field
     */
    private function upsertCustomField(Connection $connection, array $field, string $createdAt): void
    {
        $config = [
            'label' => $field['label'],
            'helpText' => $field['helpText'],
            'componentName' => $field['componentName'],
            'customFieldType' => $field['customFieldType'],
            'customFieldPosition' => $field['position'],
        ];

        if ($field['customFieldType'] === 'text') {
            $config['type'] = 'text';
        }

        if ($field['customFieldType'] === 'checkbox') {
            $config['type'] = 'checkbox';
        }

        if (isset($field['options'])) {
            $config['options'] = $field['options'];
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
                'en-GB' => 'Veylune Edition Relationship',
                'de-DE' => 'Veylune Edition-Beziehung',
            ],
            'translated' => true,
            'customFieldPosition' => 2,
        ];

        $connection->executeStatement(
            <<<'SQL'
                INSERT INTO `custom_field_set`
                    (`id`, `name`, `config`, `active`, `position`, `global`, `created_at`)
                VALUES
                    (:id, :name, :config, 1, 2, 0, :createdAt)
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
                'id' => Uuid::fromHexToBytes(self::FIELD_SET_RELATION_ID),
                'setId' => Uuid::fromHexToBytes(self::FIELD_SET_ID),
                'createdAt' => $createdAt,
            ]
        );
    }

    /**
     * @return list<array{
     *     id: string,
     *     name: string,
     *     type: string,
     *     componentName: string,
     *     customFieldType: string,
     *     label: array<string, string>,
     *     helpText: array<string, string>,
     *     position: int,
     *     options?: list<array{value: string, label: array<string, string>}>
     * }>
     */
    private function getFields(): array
    {
        return [
            [
                'id' => '019e7a14000070008000000000000010',
                'name' => 'veylune_edition_enabled',
                'type' => 'bool',
                'componentName' => 'sw-field',
                'customFieldType' => 'checkbox',
                'label' => [
                    'en-GB' => 'Edition relationship enabled',
                    'de-DE' => 'Edition-Beziehung aktiviert',
                ],
                'helpText' => [
                    'en-GB' => 'Marks that this product participates in an approved Edition relationship. This does not create an Edition page or render frontend content.',
                    'de-DE' => 'Kennzeichnet, dass dieses Produkt zu einer freigegebenen Edition-Beziehung gehört. Dadurch wird keine Editionsseite erstellt und kein Frontend-Inhalt ausgegeben.',
                ],
                'position' => 1,
            ],
            [
                'id' => '019e7a14000070008000000000000011',
                'name' => 'veylune_edition_reference',
                'type' => 'text',
                'componentName' => 'sw-field',
                'customFieldType' => 'text',
                'label' => [
                    'en-GB' => 'Edition reference key',
                    'de-DE' => 'Edition-Referenzschlüssel',
                ],
                'helpText' => [
                    'en-GB' => 'Stable internal Edition key or slug. Do not store translated titles, campaign copy, supplier prose, or archive descriptions here.',
                    'de-DE' => 'Stabiler interner Edition-Schlüssel oder Slug. Keine übersetzten Titel, Kampagnentexte, Lieferantenprosa oder Archivbeschreibungen eintragen.',
                ],
                'position' => 2,
            ],
            [
                'id' => '019e7a14000070008000000000000012',
                'name' => 'veylune_edition_release_state',
                'type' => 'select',
                'componentName' => 'sw-single-select',
                'customFieldType' => 'select',
                'label' => [
                    'en-GB' => 'Edition release state',
                    'de-DE' => 'Editionsstatus',
                ],
                'helpText' => [
                    'en-GB' => 'Controlled lifecycle state for the product’s Edition relationship. This is metadata only and must not be used for urgency mechanics.',
                    'de-DE' => 'Kontrollierter Lebenszyklusstatus der Edition-Beziehung des Produkts. Dies sind reine Metadaten und darf nicht für Dringlichkeitsmechaniken genutzt werden.',
                ],
                'position' => 3,
                'options' => [
                    [
                        'value' => 'active',
                        'label' => [
                            'en-GB' => 'Active',
                            'de-DE' => 'Aktiv',
                        ],
                    ],
                    [
                        'value' => 'private_preview',
                        'label' => [
                            'en-GB' => 'Private preview',
                            'de-DE' => 'Private Vorschau',
                        ],
                    ],
                    [
                        'value' => 'archive_record',
                        'label' => [
                            'en-GB' => 'Archive record',
                            'de-DE' => 'Archiveintrag',
                        ],
                    ],
                    [
                        'value' => 'selected_works_available',
                        'label' => [
                            'en-GB' => 'Selected works available',
                            'de-DE' => 'Ausgewählte Werke verfügbar',
                        ],
                    ],
                    [
                        'value' => 'closed',
                        'label' => [
                            'en-GB' => 'Closed',
                            'de-DE' => 'Geschlossen',
                        ],
                    ],
                ],
            ],
            [
                'id' => '019e7a14000070008000000000000013',
                'name' => 'veylune_edition_release_type',
                'type' => 'select',
                'componentName' => 'sw-single-select',
                'customFieldType' => 'select',
                'label' => [
                    'en-GB' => 'Edition release type',
                    'de-DE' => 'Edition-Veröffentlichungstyp',
                ],
                'helpText' => [
                    'en-GB' => 'Controlled release type. Use only when the product belongs to a governed Edition context.',
                    'de-DE' => 'Kontrollierter Veröffentlichungstyp. Nur verwenden, wenn das Produkt zu einem geregelten Edition-Kontext gehört.',
                ],
                'position' => 4,
                'options' => [
                    [
                        'value' => 'atelier_collaboration',
                        'label' => [
                            'en-GB' => 'Atelier collaboration',
                            'de-DE' => 'Atelier-Kollaboration',
                        ],
                    ],
                    [
                        'value' => 'material_study',
                        'label' => [
                            'en-GB' => 'Material study',
                            'de-DE' => 'Materialstudie',
                        ],
                    ],
                    [
                        'value' => 'room_edition',
                        'label' => [
                            'en-GB' => 'Room edition',
                            'de-DE' => 'Raumedition',
                        ],
                    ],
                    [
                        'value' => 'small_series',
                        'label' => [
                            'en-GB' => 'Small series',
                            'de-DE' => 'Kleinserie',
                        ],
                    ],
                    [
                        'value' => 'private_preview',
                        'label' => [
                            'en-GB' => 'Private preview',
                            'de-DE' => 'Private Vorschau',
                        ],
                    ],
                ],
            ],
            [
                'id' => '019e7a14000070008000000000000014',
                'name' => 'veylune_edition_context_focus',
                'type' => 'select',
                'componentName' => 'sw-single-select',
                'customFieldType' => 'select',
                'label' => [
                    'en-GB' => 'Edition context focus',
                    'de-DE' => 'Edition-Kontextfokus',
                ],
                'helpText' => [
                    'en-GB' => 'Primary relationship logic for the Edition assignment. Keep supplier, material, and spatial explanations in the governed Edition CMS layer.',
                    'de-DE' => 'Primäre Beziehungslogik der Edition-Zuordnung. Lieferanten-, Material- und Raumbegründungen bleiben in der geregelten Edition-CMS-Ebene.',
                ],
                'position' => 5,
                'options' => [
                    [
                        'value' => 'supplier_collaboration',
                        'label' => [
                            'en-GB' => 'Supplier collaboration',
                            'de-DE' => 'Lieferantenkollaboration',
                        ],
                    ],
                    [
                        'value' => 'material_logic',
                        'label' => [
                            'en-GB' => 'Material logic',
                            'de-DE' => 'Materiallogik',
                        ],
                    ],
                    [
                        'value' => 'spatial_logic',
                        'label' => [
                            'en-GB' => 'Spatial logic',
                            'de-DE' => 'Räumliche Logik',
                        ],
                    ],
                    [
                        'value' => 'cross_collection',
                        'label' => [
                            'en-GB' => 'Cross-collection relationship',
                            'de-DE' => 'Kollektionenübergreifende Beziehung',
                        ],
                    ],
                    [
                        'value' => 'archive_continuity',
                        'label' => [
                            'en-GB' => 'Archive continuity',
                            'de-DE' => 'Archivkontinuität',
                        ],
                    ],
                ],
            ],
        ];
    }
}
