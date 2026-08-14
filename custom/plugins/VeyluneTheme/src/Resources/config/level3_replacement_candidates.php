<?php declare(strict_types=1);

return [
    'reviewed_at' => '2026-08-14',
    'status' => 'founder_approved_outreach_pending',
    'candidates' => [
        'aurelia_primary' => [
            'replaces_sku' => 'VLS-SOF-001',
            'manufacturer' => 'Ethnicraft',
            'product' => 'N701 Modular Sofa - 3 seater - Moss',
            'manufacturer_item_number' => '20256',
            'dimensions_cm' => ['width' => 210, 'depth' => 91, 'height' => 76],
            'materials' => '56% recycled cotton, 26% acrylic, 18% polyester',
            'official_reference' => 'https://inspiration.ethnicraft.com/hubfs/2.%20WEBSITE%20CATALOGUES/Commercial%20catalogue.pdf',
            'compliance_reference' => 'https://media.ethnicraft.com/sys-master/s3_product_medias/h6e/hf7/8900714954782/Declaration%20form%20-%20N701%20STANDARD%20FABRIC_.pdf',
            'status' => 'supplier_outreach_required',
        ],
        'aurelia_alternate' => [
            'replaces_sku' => 'VLS-SOF-001',
            'manufacturer' => 'Muuto',
            'product' => 'Connect Modular Sofa',
            'manufacturer_item_number' => null,
            'official_reference' => 'https://www.muuto.com/product/Connect-Modular-Sofa--p2030/p2030/',
            'order_guide_reference' => 'https://content.muuto.com/Perfion/File.aspx?action=save&id=fe601cbd-30b0-4e9d-9625-466473079777',
            'status' => 'configuration_and_supplier_outreach_required',
        ],
        'calma_primary' => [
            'replaces_sku' => 'VLS-SOF-003',
            'manufacturer' => 'GUBI',
            'product' => 'Epic Dining Table - Elliptical - Neutral White - 240 x 120 cm',
            'manufacturer_item_number' => '10059274',
            'dimensions_cm' => ['width' => 240, 'depth' => 120],
            'materials' => 'Italian white travertine',
            'official_reference' => 'https://gubi.com/en/us/products/epic-dining-table-elliptical',
            'status' => 'supplier_outreach_required',
        ],
    ],
    'unresolved_acceptance_gates' => [
        'authorized_supplier_or_reseller',
        'territory_specific_wholesale_price_and_tax',
        'current_availability_and_lead_time',
        'returns_and_delivery_terms',
        'media_license_for_veylune_channels_and_crops',
        'exact_variant_or_configuration_confirmation',
        'source_owner_and_reviewer',
    ],
];
