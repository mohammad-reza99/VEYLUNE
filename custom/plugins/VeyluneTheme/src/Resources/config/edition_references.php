<?php declare(strict_types=1);

/**
 * Governed Edition reference registry.
 *
 * Required record schema:
 * - destinationApproved: true only after the destination is governance-approved
 * - routes.en / routes.de: stable canonical detail destination paths
 * - detailDestination.*: route contract, destination identity, and CMS assignment gates
 * - releaseState: canonical Edition lifecycle state
 * - allowedProductReleaseStates: product metadata states allowed for this reference
 * - multilingual.en / multilingual.de / multilingual.seo: readiness gates
 * - cmsBlueprint.approved / cmsBlueprint.requiredBlocks: structural CMS readiness
 * - seo.*: meta and canonical readiness gates
 * - acquisition.*: release-state inquiry and CTA eligibility rules
 * - archiveContinuity: whether archive metadata may preserve this reference
 * - publicRenderingEnabled: must remain false until a rendering phase is approved
 *
 * Future rendering may consume only the minimal payload returned by
 * EditionReferenceRegistry::buildGuardedRenderingPayload(). It must not render
 * raw registry records, raw product entities, or uncontrolled custom fields.
 *
 * Product metadata may reference only keys present in this registry.
 * This registry is not a publication system and does not render frontend content.
 */
return [
    'material-study-travertine-volume-01' => [
        'destinationApproved' => true,
        'routes' => [
            'en' => '/editions/material-study-travertine-volume-01',
            'de' => '/de/editionen/material-study-travertine-volume-01',
        ],
        'detailDestination' => [
            'contractApproved' => true,
            'identityStable' => true,
            'authority' => 'edition_destination',
            'productAuthority' => 'pdp',
            'metadataAuthority' => 'relationship_only',
            'cmsAssignment' => [
                'approved' => true,
                'blueprint' => 'edition_detail',
            ],
            'canonicalMetadata' => [
                'canonicalTitle' => true,
                'releaseState' => true,
                'canonicalRoutes' => true,
                'seo' => true,
                'acquisitionState' => true,
                'archiveContinuity' => true,
            ],
            'publication' => [
                'publishEligible' => true,
                'publicRenderingEnabled' => true,
                'renderingPhaseApproved' => true,
            ],
        ],
        'releaseState' => 'active',
        'displayTitle' => [
            'en' => 'Travertine Material Study, Volume 01',
            'de' => 'Travertin-Materialstudie, Volumen 01',
        ],
        'summaryLabel' => [
            'en' => 'Governed public Edition record describing approved material, proportion, and spatial context.',
            'de' => 'Gesteuertes oeffentliches Editionsdokument mit freigegebenem Material-, Proportions- und Raumkontext.',
        ],
        'materialContext' => [
            'en' => 'Travertine surface, mineral variation, and edge alignment',
            'de' => 'Travertinoberflaeche, mineralische Variation und Kantenausrichtung',
        ],
        'spatialContext' => [
            'en' => 'Interior scale, wall adjacency, and horizontal placement without use-case framing',
            'de' => 'Innenraummassstab, Wandbezug und horizontale Platzierung ohne Nutzungsszenario',
        ],
        'governanceNote' => [
            'en' => 'This record remains bounded by approved Edition context, stable route identity, and public-record boundaries.',
            'de' => 'Dieses Dokument bleibt an freigegebenen Editionskontext, stabile Routenidentitaet und oeffentliche Dokumentgrenzen gebunden.',
        ],
        'allowedProductReleaseStates' => [
            'active',
            'private_preview',
            'selected_works_available',
            'archive_record',
        ],
        'multilingual' => [
            'en' => true,
            'de' => true,
            'seo' => true,
        ],
        'cmsBlueprint' => [
            'approved' => true,
            'requiredBlocks' => [
                'edition_record_header',
                'edition_metadata_panel',
                'acquisition_panel',
                'governance_statement',
                'material_spatial_logic',
                'supplier_atelier_context',
            ],
        ],
        'seo' => [
            'metaTitle' => true,
            'metaDescription' => true,
            'canonicalStable' => true,
            'archiveContinuity' => true,
        ],
        'acquisition' => [
            'active' => [
                'inquiryAllowed' => true,
                'ctaAllowed' => true,
            ],
            'private_preview' => [
                'inquiryAllowed' => true,
                'ctaAllowed' => true,
            ],
            'selected_works_available' => [
                'inquiryAllowed' => true,
                'ctaAllowed' => true,
            ],
            'archive_record' => [
                'inquiryAllowed' => false,
                'ctaAllowed' => false,
            ],
            'closed' => [
                'inquiryAllowed' => false,
                'ctaAllowed' => false,
            ],
        ],
        'archiveContinuity' => true,
        'publicRenderingEnabled' => false,
        'internalValidationReference' => true,
    ],
];
