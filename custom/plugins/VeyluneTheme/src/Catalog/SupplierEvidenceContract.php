<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class SupplierEvidenceContract
{
    public const STATUS_ACCEPTED = 'accepted';

    private const REQUIRED_FIELDS = [
        'supplier_id',
        'supplier_legal_name',
        'supplier_sku',
        'source_batch',
        'pricing_authority_reference',
        'availability_authority_reference',
        'specification_pack_reference',
        'media_rights_schedule_reference',
        'material_evidence_reference',
        'source_owner',
        'reviewed_at',
        'reviewer',
    ];

    private const BLOCKED_STATUSES = [
        'blocked_external_evidence',
        'blocked_no_verifiable_source',
        'blocked_identity_conflict',
    ];

    /**
     * @return list<string>
     */
    public static function requiredFields(): array
    {
        return self::REQUIRED_FIELDS;
    }

    /**
     * @return list<string>
     */
    public static function blockedStatuses(): array
    {
        return self::BLOCKED_STATUSES;
    }
}
