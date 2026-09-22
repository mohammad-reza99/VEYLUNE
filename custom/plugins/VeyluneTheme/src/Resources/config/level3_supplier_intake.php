<?php declare(strict_types=1);

$cohort = require __DIR__ . '/level3_cohort.php';
$skus = array_values(array_filter(array_map(
    static fn (array $product): string => trim((string) ($product['sku'] ?? '')),
    is_array($cohort['products'] ?? null) ? $cohort['products'] : []
)));

$emptyEvidence = static fn (string $sku): array => [
    'veylune_sku' => $sku,
    'status' => 'blocked_external_evidence',
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
    'intake_id' => 'phase-7-launch-candidate-10-supplier-evidence',
    'accepted_status' => 'accepted',
    'records' => array_map($emptyEvidence, $skus),
];
