<?php declare(strict_types=1);

return [
    'cohort_id' => 'stage-b1-first-10-level3',
    'owner' => 'product_governance',
    'founder_gate' => 'required_before_publication',
    'products' => [
        ['sku' => 'VLS-SOF-001', 'name' => 'Aurelia Modular Sofa', 'lane' => 'legacy_quarantine', 'expected_active' => false],
        ['sku' => 'VLS-SOF-003', 'name' => 'Calma Travertine Table', 'lane' => 'legacy_quarantine', 'expected_active' => false],
        ['sku' => 'VLS-SOF-004', 'name' => 'Atelier Stone Vessel', 'lane' => 'legacy_remediation', 'expected_active' => true],
        ['sku' => 'VLS-SOF-002', 'name' => 'Nocturne Floor Lamp', 'lane' => 'legacy_remediation', 'expected_active' => true],
        ['sku' => 'VLS-DEC-000003', 'name' => 'Tectona Travertine Vessel', 'lane' => 'governed_draft', 'expected_active' => false],
        ['sku' => 'VLS-DEC-000004', 'name' => 'Meridian Cast-Metal Sculpture', 'lane' => 'governed_draft', 'expected_active' => false],
        ['sku' => 'VLS-DEC-000006', 'name' => 'Strata Marble Valet Tray', 'lane' => 'governed_draft', 'expected_active' => false],
        ['sku' => 'VLS-TEX-000001', 'name' => 'Tactile Hand-Knotted Wool Rug', 'lane' => 'governed_draft', 'expected_active' => false],
        ['sku' => 'VLS-LGT-000003', 'name' => 'Lumen Ceramic Table Lamp', 'lane' => 'governed_draft', 'expected_active' => false],
        ['sku' => 'VLS-FUR-000009', 'name' => 'Stillwater Upholstered Oak Bench', 'lane' => 'governed_draft', 'expected_active' => false],
    ],
    'external_evidence' => [
        'supplier_master' => 'missing',
        'supplier_sku_mapping' => 'missing',
        'approved_source_batch' => 'missing',
        'pricing_authority' => 'missing',
        'availability_authority' => 'missing',
        'specification_pack' => 'missing',
        'media_rights_schedule' => 'missing',
        'material_evidence' => 'missing',
    ],
];
