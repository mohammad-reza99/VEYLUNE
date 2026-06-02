<?php declare(strict_types=1);

return [
    'suppliers' => [
        'mock-supplier-a' => [
            'supplier_id' => 'mock-supplier-a',
            'legal_name' => 'Mock Supplier A GmbH',
            'display_name' => 'Mock Supplier A',
            'primary_contact' => 'mock-a@example.invalid',
            'commercial_terms_owner' => 'supplier_governance',
            'compliance_owner' => 'supplier_governance',
            'source_system' => 'phase_9_launch_simulation',
            'media_rights_policy' => 'mock_rights_verified',
            'returns_policy' => 'mock_returns_policy',
            'lead_time_policy' => 'mock_lead_time_policy',
            'status' => 'active',
        ],
        'mock-supplier-b' => [
            'supplier_id' => 'mock-supplier-b',
            'legal_name' => 'Mock Supplier B GmbH',
            'display_name' => 'Mock Supplier B',
            'primary_contact' => 'mock-b@example.invalid',
            'commercial_terms_owner' => 'supplier_governance',
            'compliance_owner' => 'supplier_governance',
            'source_system' => 'phase_9_launch_simulation',
            'media_rights_policy' => 'mock_rights_verified',
            'returns_policy' => 'mock_returns_policy',
            'lead_time_policy' => 'mock_lead_time_policy',
            'status' => 'active',
        ],
        'mock-supplier-c' => [
            'supplier_id' => 'mock-supplier-c',
            'legal_name' => 'Mock Supplier C GmbH',
            'display_name' => 'Mock Supplier C',
            'primary_contact' => 'mock-c@example.invalid',
            'commercial_terms_owner' => 'supplier_governance',
            'compliance_owner' => 'supplier_governance',
            'source_system' => 'phase_9_launch_simulation',
            'media_rights_policy' => 'mock_rights_verified',
            'returns_policy' => 'mock_returns_policy',
            'lead_time_policy' => 'mock_lead_time_policy',
            'status' => 'active',
        ],
    ],
    'sku_reservations' => [],
    'retired_skus' => [
        'VLS-FUR-999999',
    ],
];
