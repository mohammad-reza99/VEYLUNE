<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class LaunchSimulationFactory
{
    private const BATCHES = [
        ['id' => 'mock-launch-batch-1', 'supplier_id' => 'mock-supplier-a', 'count' => 30, 'department' => 'furniture', 'prefix' => 'FUR'],
        ['id' => 'mock-launch-batch-2', 'supplier_id' => 'mock-supplier-b', 'count' => 22, 'department' => 'lighting', 'prefix' => 'LIG'],
        ['id' => 'mock-launch-batch-3', 'supplier_id' => 'mock-supplier-c', 'count' => 28, 'department' => 'decor_objects', 'prefix' => 'DEC'],
        ['id' => 'mock-launch-batch-4', 'supplier_id' => 'mock-supplier-a', 'count' => 20, 'department' => 'furniture', 'prefix' => 'FUR'],
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function manifests(): array
    {
        $sequence = 1;
        $manifests = [];

        foreach (self::BATCHES as $batch) {
            $items = [];
            for ($i = 1; $i <= $batch['count']; $i++) {
                $items[] = $this->item($batch, $sequence);
                $sequence++;
            }

            $manifests[] = [
                'batch_id' => $batch['id'],
                'supplier_id' => $batch['supplier_id'],
                'source_reference' => 'phase-9-' . $batch['id'],
                'source_hash' => 'mock-hash-' . $batch['id'],
                'received_at' => '2026-06-02',
                'owner' => 'batch_governance',
                'rollback_target' => [
                    'snapshot_id' => 'snapshot-' . $batch['id'],
                    'batch_id' => $batch['id'],
                    'checkpoint' => 'pre_import_application',
                    'owner' => 'rollback_governance',
                    'created_at' => '2026-06-02',
                    'catalog_reference' => 'mock-catalog-' . $batch['id'],
                    'sku_mapping_reference' => 'mock-sku-map-' . $batch['id'],
                    'property_mapping_reference' => 'mock-property-map-' . $batch['id'],
                    'media_reference' => 'mock-media-map-' . $batch['id'],
                    'restore_notes' => 'Mock launch simulation snapshot only.',
                ],
                'items' => $items,
            ];
        }

        return $manifests;
    }

    /**
     * @return array{expected_products: int, expected_suppliers: int, expected_batches: int}
     */
    public function expectedMetrics(): array
    {
        return [
            'expected_products' => 100,
            'expected_suppliers' => 3,
            'expected_batches' => 4,
        ];
    }

    /**
     * @param array{id: string, supplier_id: string, count: int, department: string, prefix: string} $batch
     *
     * @return array<string, mixed>
     */
    private function item(array $batch, int $sequence): array
    {
        $sku = \sprintf('VLS-%s-%06d', $batch['prefix'], $sequence);
        $supplierSku = \sprintf('%s-MOCK-%03d', \strtoupper($batch['supplier_id']), $sequence);
        $category = $this->categoryForDepartment($batch['department'], $sequence);

        return [
            'veylune_sku' => $sku,
            'supplier_sku' => $supplierSku,
            'publication_state' => 'draft',
            'sellability_state' => 'not_sellable',
            'taxonomy' => [
                'department' => $batch['department'],
                'category' => $category,
                'product_type' => $category,
                'room' => 'living_room',
            ],
            'properties' => [
                'primary_material' => $sequence % 3 === 0 ? 'travertine' : 'wood',
                'finish' => $sequence % 2 === 0 ? 'matte' : 'natural',
                'color' => $sequence % 3 === 0 ? 'stone' : 'warm_neutral',
                'room' => 'living_room',
            ],
            'media' => $this->media($sku),
            'content' => [
                'en_title' => 'Mock Product ' . $sequence,
                'de_title' => 'Mock Produkt ' . $sequence,
                'en_description' => 'Mock English description for launch simulation product ' . $sequence . '.',
                'de_description' => 'Mock German description for launch simulation product ' . $sequence . '.',
                'en_seo_title' => 'Mock Product ' . $sequence . ' | Veylune',
                'de_seo_title' => 'Mock Produkt ' . $sequence . ' | Veylune',
                'en_meta_description' => 'Mock English SEO description for launch simulation product ' . $sequence . '.',
                'de_meta_description' => 'Mock German SEO description for launch simulation product ' . $sequence . '.',
            ],
            'physical' => [
                'width' => '120',
                'height' => '80',
                'depth' => '60',
                'weight' => '20',
                'dimensions_unit' => 'cm',
            ],
            'commerce' => [
                'price' => '1200.00',
                'tax' => 'standard',
                'lead_time' => '4 weeks',
                'delivery_class' => 'furniture_delivery',
                'returns_class' => 'standard_return',
            ],
            'seo' => [
                'en_seo_title' => 'Mock Product ' . $sequence . ' | Veylune',
                'de_seo_title' => 'Mock Produkt ' . $sequence . ' | Veylune',
                'en_meta_description' => 'Mock English SEO description for launch simulation product ' . $sequence . '.',
                'de_meta_description' => 'Mock German SEO description for launch simulation product ' . $sequence . '.',
            ],
            'governance' => [
                'quality_status' => 'approved',
                'reviewer' => 'phase_9_simulation',
                'rollback_target' => 'snapshot-' . $batch['id'],
            ],
        ];
    }

    private function categoryForDepartment(string $department, int $sequence): string
    {
        $categories = [
            'furniture' => ['sofas', 'lounge_chairs', 'dining_tables', 'coffee_tables', 'side_tables', 'consoles'],
            'lighting' => ['floor_lamps', 'table_lamps', 'pendant_lights', 'wall_lighting'],
            'decor_objects' => ['vessels', 'sculptural_objects', 'ceramics', 'stone_pieces', 'rugs', 'throws', 'serveware'],
        ];
        $options = $categories[$department] ?? ['general'];

        return $options[$sequence % \count($options)];
    }

    /**
     * @return array<string, mixed>
     */
    private function media(string $sku): array
    {
        $images = [];
        for ($i = 1; $i <= 5; $i++) {
            $images[] = [
                'type' => $i === 1 ? 'primary' : 'detail',
                'en_alt' => $sku . ' image ' . $i,
                'de_alt' => $sku . ' Bild ' . $i,
                'rights_owner' => 'Veylune mock rights',
                'crop' => '4:5',
                'quality' => 'approved',
            ];
        }

        return [
            'primary_image' => $sku . '-primary',
            'detail_image' => $sku . '-detail',
            'image_rights' => 'Veylune mock rights',
            'en_alt_text' => $sku . ' image',
            'de_alt_text' => $sku . ' Bild',
            'images' => $images,
        ];
    }
}
