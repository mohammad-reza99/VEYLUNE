<?php declare(strict_types=1);

$skus = [
    'VLS-SOF-001', 'VLS-SOF-003', 'VLS-SOF-004', 'VLS-SOF-002',
    'VLS-DEC-000003', 'VLS-DEC-000004', 'VLS-DEC-000006',
    'VLS-TEX-000001', 'VLS-LGT-000003', 'VLS-FUR-000009',
];

$emptyEvidence = static fn (string $sku): array => [
    'veylune_sku' => $sku,
    'status' => match ($sku) {
        'VLS-SOF-001' => 'blocked_identity_conflict',
        'VLS-SOF-003' => 'blocked_identity_conflict',
        default => 'blocked_external_evidence',
    },
    'supplier_id' => null,
    'supplier_legal_name' => null,
    'supplier_sku' => null,
    'source_batch' => null,
    'pricing_authority_reference' => null,
    'availability_authority_reference' => null,
    'specification_pack_reference' => null,
    'media_rights_schedule_reference' => null,
    'material_evidence_reference' => null,
    'source_owner' => null,
    'reviewed_at' => null,
    'reviewer' => null,
];

return [
    'intake_id' => 'stage-b1-1-first-10-supplier-evidence',
    'accepted_status' => 'accepted',
    'records' => array_map($emptyEvidence, $skus),
];
