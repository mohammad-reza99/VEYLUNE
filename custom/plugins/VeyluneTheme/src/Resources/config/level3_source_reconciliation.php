<?php declare(strict_types=1);

return [
    'reviewed_at' => '2026-08-14',
    'records' => [
        'VLS-SOF-001' => [
            'status' => 'blocked_identity_conflict',
            'finding' => 'The three assigned media files depict different sofa designs; one carries a MENTE FURNITURE watermark, while no result establishes a single product identity, Veylune SKU mapping, supplier, or rights chain.',
            'accepted_supplier_match' => null,
        ],
        'VLS-SOF-003' => [
            'status' => 'blocked_identity_conflict',
            'finding' => 'Local media filename identifies Scott Keramik Cattelan Italia while the Veylune record claims Calma Travertine Table.',
            'official_reference' => 'https://www.cattelanitalia.com/en/products/68179469-05FE-4F6B-99B5-7FC3FF9FC169?c=15',
            'official_material' => 'marble-effect ceramic top with steel base',
            'conflicting_claim' => 'travertine table',
        ],
    ],
];
