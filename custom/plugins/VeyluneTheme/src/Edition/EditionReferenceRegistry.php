<?php declare(strict_types=1);

namespace VeyluneTheme\Edition;

class EditionReferenceRegistry
{
    public const STATE_UNRESOLVED = 'unresolved';
    public const STATE_REGISTRY_VALID = 'registry_valid';
    public const STATE_DESTINATION_READY = 'destination_ready';
    public const STATE_PUBLICATION_BLOCKED = 'publication_blocked';
    public const STATE_RENDERING_DISABLED = 'rendering_disabled';
    public const STATE_PUBLICLY_RENDERABLE = 'publicly_renderable';

    private const PUBLIC_RELEASE_STATES = [
        'active',
        'archive_record',
        'selected_works_available',
    ];

    private const REQUIRED_CMS_BLOCKS = [
        'edition_record_header',
        'edition_metadata_panel',
        'acquisition_panel',
        'governance_statement',
        'material_spatial_logic',
        'supplier_atelier_context',
    ];

    private const REQUIRED_ACQUISITION_STATES = [
        'active',
        'private_preview',
        'selected_works_available',
        'archive_record',
        'closed',
    ];

    private const GUARDED_RENDERING_PAYLOAD_KEYS = [
        'reference',
        'locale',
        'canonicalRoute',
        'releaseState',
        'acquisitionState',
        'cmsDestination',
        'archiveContinuity',
        'displayTitle',
        'summaryLabel',
        'materialContext',
        'spatialContext',
        'governanceNote',
    ];

    /**
     * @var array<string, array<string, mixed>>|null
     */
    private ?array $references = null;

    public function has(string $reference): bool
    {
        return isset($this->all()[$reference]);
    }

    /**
     * @return list<string>
     */
    public function validateRelationship(string $reference, string $releaseState): array
    {
        $record = $this->all()[$reference] ?? null;

        if ($record === null) {
            return ['Edition reference is not approved in the governed Edition registry.'];
        }

        $violations = [];

        if (($record['destinationApproved'] ?? false) !== true) {
            $violations[] = 'Edition reference exists but does not have an approved destination.';
        }

        if (($record['destinationApproved'] ?? false) === true && !$this->hasDestinationRoutes($record)) {
            $violations[] = 'Approved Edition references require stable EN and DE destination route entries.';
        }

        $allowedStates = $record['allowedProductReleaseStates'] ?? [];
        if (!\is_array($allowedStates) || !\in_array($releaseState, $allowedStates, true)) {
            $violations[] = 'Product Edition release state is not allowed for the approved Edition reference.';
        }

        $canonicalState = $record['releaseState'] ?? null;
        if ($canonicalState === 'archive_record' && $releaseState === 'active') {
            $violations[] = 'Archived Edition references cannot be used with active product Edition metadata.';
        }

        if ($releaseState === 'archive_record' && ($record['archiveContinuity'] ?? false) !== true) {
            $violations[] = 'Archive product metadata requires archive continuity on the approved Edition reference.';
        }

        if (\in_array($releaseState, self::PUBLIC_RELEASE_STATES, true) && !$this->isMultilingualReady($record)) {
            $violations[] = 'Public Edition metadata requires EN, DE, and SEO readiness on the approved Edition reference.';
        }

        return $violations;
    }

    /**
     * @return list<string>
     */
    public function validatePublicDestinationReadiness(string $reference): array
    {
        $record = $this->all()[$reference] ?? null;

        if ($record === null) {
            return ['Edition reference is not approved in the governed Edition registry.'];
        }

        $violations = [];

        if (($record['destinationApproved'] ?? false) !== true) {
            $violations[] = 'Public Edition readiness requires an approved destination.';
        }

        if (!$this->hasDestinationRoutes($record)) {
            $violations[] = 'Public Edition readiness requires stable EN and DE canonical routes.';
        }

        if (!$this->isCmsBlueprintReady($record)) {
            $violations[] = 'Public Edition readiness requires the approved Edition CMS blueprint and all required structural blocks.';
        }

        if (!$this->isMultilingualReady($record)) {
            $violations[] = 'Public Edition readiness requires EN, DE, and SEO readiness.';
        }

        if (!$this->isSeoReady($record)) {
            $violations[] = 'Public Edition readiness requires meta title, meta description, canonical stability, and archive continuity expectations.';
        }

        if (!$this->isAcquisitionReady($record)) {
            $violations[] = 'Public Edition readiness requires governed acquisition-state rules for every approved release state.';
        }

        if (($record['archiveContinuity'] ?? false) !== true) {
            $violations[] = 'Public Edition readiness requires archive continuity support.';
        }

        if (($record['publicRenderingEnabled'] ?? false) === true) {
            $violations[] = 'Public rendering must remain disabled until a rendering phase is approved.';
        }

        return $violations;
    }

    /**
     * @return list<string>
     */
    public function validateDetailDestinationGate(string $reference): array
    {
        $record = $this->all()[$reference] ?? null;

        if ($record === null) {
            return ['Edition reference is not approved in the governed Edition registry.'];
        }

        $violations = [];
        $detailDestination = $record['detailDestination'] ?? [];

        if (!\is_array($detailDestination)) {
            return ['Edition detail destination gate requires a detailDestination contract.'];
        }

        if (($detailDestination['contractApproved'] ?? false) !== true) {
            $violations[] = 'Edition detail destination gate requires an approved route contract.';
        }

        if (!$this->hasDetailRouteContract($reference, $record)) {
            $violations[] = 'Edition detail destination gate requires /editions/{reference} and /de/editionen/{reference} route contracts.';
        }

        if (($detailDestination['identityStable'] ?? false) !== true) {
            $violations[] = 'Edition detail destination gate requires stable destination identity.';
        }

        if (($detailDestination['authority'] ?? null) !== 'edition_destination') {
            $violations[] = 'Edition detail destination must remain the editorial authority.';
        }

        if (($detailDestination['productAuthority'] ?? null) !== 'pdp') {
            $violations[] = 'PDP must remain the product authority.';
        }

        if (($detailDestination['metadataAuthority'] ?? null) !== 'relationship_only') {
            $violations[] = 'Edition metadata must remain relationship-only.';
        }

        if (!$this->isDetailCmsAssignmentReady($detailDestination) || !$this->isCmsBlueprintReady($record)) {
            $violations[] = 'Edition detail destination gate requires approved CMS assignment and required blueprint blocks.';
        }

        if (!$this->isCanonicalMetadataReady($detailDestination)) {
            $violations[] = 'Edition detail destination gate requires canonical title, release state, routes, SEO, acquisition state, and archive continuity metadata readiness.';
        }

        if (!$this->isMultilingualReady($record)) {
            $violations[] = 'Edition detail destination gate requires EN, DE, and SEO readiness.';
        }

        if (!$this->isSeoReady($record)) {
            $violations[] = 'Edition detail destination gate requires SEO readiness.';
        }

        if (!$this->isAcquisitionReady($record)) {
            $violations[] = 'Edition detail destination gate requires acquisition-state governance.';
        }

        if (($record['archiveContinuity'] ?? false) !== true) {
            $violations[] = 'Edition detail destination gate requires archive continuity.';
        }

        $publication = $detailDestination['publication'] ?? [];
        if (!\is_array($publication)
            || !\array_key_exists('publishEligible', $publication)
            || !\array_key_exists('publicRenderingEnabled', $publication)
            || !\array_key_exists('renderingPhaseApproved', $publication)
        ) {
            $violations[] = 'Edition detail destination publication toggles must be explicitly governed.';
        }

        return $violations;
    }

    /**
     * @return array{state: string, exposureAllowed: bool, violations: list<string>}
     */
    public function resolveDetailRouteState(string $reference, string $locale): array
    {
        $record = $this->all()[$reference] ?? null;

        if ($record === null) {
            return $this->resolution(self::STATE_UNRESOLVED, ['Edition reference is not approved in the governed Edition registry.']);
        }

        if (!\in_array($locale, ['en', 'de'], true)) {
            return $this->resolution(self::STATE_UNRESOLVED, ['Edition detail route resolution supports only EN and DE locales.']);
        }

        if (!$this->hasDetailRouteContract($reference, $record)) {
            return $this->resolution(self::STATE_UNRESOLVED, ['Edition detail route contract is missing or unstable.']);
        }

        $readinessViolations = $this->getDetailReadinessViolations($record);

        if ($readinessViolations !== []) {
            return $this->resolution(self::STATE_REGISTRY_VALID, $readinessViolations);
        }

        $publication = $this->getPublication($record);

        if (($publication['publishEligible'] ?? false) !== true) {
            return $this->resolution(self::STATE_DESTINATION_READY, ['Edition destination is ready but not publication eligible.']);
        }

        if (($publication['renderingPhaseApproved'] ?? false) !== true || ($publication['publicRenderingEnabled'] ?? false) !== true) {
            return $this->resolution(self::STATE_PUBLICATION_BLOCKED, ['Edition publication is eligible but public rendering is not approved.']);
        }

        return [
            'state' => self::STATE_PUBLICLY_RENDERABLE,
            'exposureAllowed' => true,
            'violations' => [],
        ];
    }

    /**
     * @return array{
     *     reference: string,
     *     locale: string,
     *     canonicalRoute: string,
     *     releaseState: string,
     *     acquisitionState: array{inquiryAllowed: bool, ctaAllowed: bool},
     *     cmsDestination: array{authority: 'edition_destination', blueprint: 'edition_detail'},
     *     archiveContinuity: bool,
     *     displayTitle: string,
     *     summaryLabel: string,
     *     materialContext: string,
     *     spatialContext: string,
     *     governanceNote: string
     * }|null
     */
    public function buildGuardedRenderingPayload(string $reference, string $locale): ?array
    {
        $resolution = $this->resolveDetailRouteState($reference, $locale);

        if ($resolution['state'] !== self::STATE_PUBLICLY_RENDERABLE || $resolution['exposureAllowed'] !== true) {
            return null;
        }

        $record = $this->all()[$reference] ?? null;

        if ($record === null) {
            return null;
        }

        $detailDestination = $record['detailDestination'] ?? [];
        $cmsAssignment = \is_array($detailDestination) ? ($detailDestination['cmsAssignment'] ?? []) : [];
        $releaseState = $this->stringValue($record['releaseState'] ?? null);

        if (!\is_array($cmsAssignment) || $releaseState === null) {
            return null;
        }

        $acquisitionState = $this->getAcquisitionState($record, $releaseState);

        if ($acquisitionState === null) {
            return null;
        }

        $displayTitle = $this->localizedString($record, 'displayTitle', $locale);
        $summaryLabel = $this->localizedString($record, 'summaryLabel', $locale);
        $materialContext = $this->localizedString($record, 'materialContext', $locale);
        $spatialContext = $this->localizedString($record, 'spatialContext', $locale);
        $governanceNote = $this->localizedString($record, 'governanceNote', $locale);

        if ($displayTitle === null
            || $summaryLabel === null
            || $materialContext === null
            || $spatialContext === null
            || $governanceNote === null
        ) {
            return null;
        }

        $payload = [
            'reference' => $reference,
            'locale' => $locale,
            'canonicalRoute' => $this->canonicalRoute($record, $locale),
            'releaseState' => $releaseState,
            'acquisitionState' => $acquisitionState,
            'cmsDestination' => [
                'authority' => 'edition_destination',
                'blueprint' => 'edition_detail',
            ],
            'archiveContinuity' => ($record['archiveContinuity'] ?? false) === true,
            'displayTitle' => $displayTitle,
            'summaryLabel' => $summaryLabel,
            'materialContext' => $materialContext,
            'spatialContext' => $spatialContext,
            'governanceNote' => $governanceNote,
        ];

        return $this->isGuardedRenderingPayload($payload) ? $payload : null;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function all(): array
    {
        if ($this->references !== null) {
            return $this->references;
        }

        $references = require __DIR__ . '/../Resources/config/edition_references.php';

        if (!\is_array($references)) {
            return $this->references = [];
        }

        $this->references = [];

        foreach ($references as $key => $record) {
            if (!\is_string($key) || !\is_array($record)) {
                continue;
            }

            $this->references[$key] = $record;
        }

        return $this->references;
    }

    /**
     * @param array<string, mixed> $record
     */
    private function isMultilingualReady(array $record): bool
    {
        $multilingual = $record['multilingual'] ?? [];

        if (!\is_array($multilingual)) {
            return false;
        }

        return ($multilingual['en'] ?? false) === true
            && ($multilingual['de'] ?? false) === true
            && ($multilingual['seo'] ?? false) === true;
    }

    /**
     * @param array<string, mixed> $record
     */
    private function isCmsBlueprintReady(array $record): bool
    {
        $cmsBlueprint = $record['cmsBlueprint'] ?? [];

        if (!\is_array($cmsBlueprint) || ($cmsBlueprint['approved'] ?? false) !== true) {
            return false;
        }

        $requiredBlocks = $cmsBlueprint['requiredBlocks'] ?? [];

        if (!\is_array($requiredBlocks)) {
            return false;
        }

        foreach (self::REQUIRED_CMS_BLOCKS as $block) {
            if (!\in_array($block, $requiredBlocks, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, mixed> $record
     */
    private function isSeoReady(array $record): bool
    {
        $seo = $record['seo'] ?? [];

        if (!\is_array($seo)) {
            return false;
        }

        return ($seo['metaTitle'] ?? false) === true
            && ($seo['metaDescription'] ?? false) === true
            && ($seo['canonicalStable'] ?? false) === true
            && ($seo['archiveContinuity'] ?? false) === true;
    }

    /**
     * @param array<string, mixed> $record
     */
    private function isAcquisitionReady(array $record): bool
    {
        $acquisition = $record['acquisition'] ?? [];

        if (!\is_array($acquisition)) {
            return false;
        }

        foreach (self::REQUIRED_ACQUISITION_STATES as $state) {
            if (!isset($acquisition[$state]) || !\is_array($acquisition[$state])) {
                return false;
            }

            $stateRules = $acquisition[$state];

            if (!\array_key_exists('inquiryAllowed', $stateRules) || !\array_key_exists('ctaAllowed', $stateRules)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, mixed> $detailDestination
     */
    private function isDetailCmsAssignmentReady(array $detailDestination): bool
    {
        $cmsAssignment = $detailDestination['cmsAssignment'] ?? [];

        if (!\is_array($cmsAssignment)) {
            return false;
        }

        return ($cmsAssignment['approved'] ?? false) === true
            && ($cmsAssignment['blueprint'] ?? null) === 'edition_detail';
    }

    /**
     * @param array<string, mixed> $detailDestination
     */
    private function isCanonicalMetadataReady(array $detailDestination): bool
    {
        $metadata = $detailDestination['canonicalMetadata'] ?? [];

        if (!\is_array($metadata)) {
            return false;
        }

        foreach (['canonicalTitle', 'releaseState', 'canonicalRoutes', 'seo', 'acquisitionState', 'archiveContinuity'] as $field) {
            if (($metadata[$field] ?? false) !== true) {
                return false;
            }
        }

        return true;
    }

    private function stringValue(mixed $value): ?string
    {
        if (!\is_string($value)) {
            return null;
        }

        return $value === '' ? null : $value;
    }

    /**
     * @param array<string, mixed> $record
     */
    private function localizedString(array $record, string $field, string $locale): ?string
    {
        $values = $record[$field] ?? null;

        if (!\is_array($values)) {
            return null;
        }

        $value = $this->stringValue($values[$locale] ?? null);

        return $value;
    }

    /**
     * @param array<string, mixed> $record
     *
     * @return list<string>
     */
    private function getDetailReadinessViolations(array $record): array
    {
        $detailDestination = $record['detailDestination'] ?? [];

        if (!\is_array($detailDestination)) {
            return ['Edition detail route resolution requires a detailDestination contract.'];
        }

        $violations = [];

        if (($record['destinationApproved'] ?? false) !== true) {
            $violations[] = 'Edition detail route resolution requires an approved destination.';
        }

        if (($detailDestination['contractApproved'] ?? false) !== true) {
            $violations[] = 'Edition detail route resolution requires an approved route contract.';
        }

        if (($detailDestination['identityStable'] ?? false) !== true) {
            $violations[] = 'Edition detail route resolution requires stable destination identity.';
        }

        if (($detailDestination['authority'] ?? null) !== 'edition_destination') {
            $violations[] = 'Edition detail route resolution requires Edition destination editorial authority.';
        }

        if (($detailDestination['productAuthority'] ?? null) !== 'pdp') {
            $violations[] = 'Edition detail route resolution requires PDP product authority.';
        }

        if (($detailDestination['metadataAuthority'] ?? null) !== 'relationship_only') {
            $violations[] = 'Edition detail route resolution requires relationship-only metadata authority.';
        }

        if (!$this->isDetailCmsAssignmentReady($detailDestination) || !$this->isCmsBlueprintReady($record)) {
            $violations[] = 'Edition detail route resolution requires approved CMS assignment and blueprint readiness.';
        }

        if (!$this->isCanonicalMetadataReady($detailDestination)) {
            $violations[] = 'Edition detail route resolution requires canonical metadata readiness.';
        }

        if (!$this->isMultilingualReady($record)) {
            $violations[] = 'Edition detail route resolution requires EN, DE, and SEO readiness.';
        }

        if (!$this->isSeoReady($record)) {
            $violations[] = 'Edition detail route resolution requires SEO readiness.';
        }

        if (!$this->isAcquisitionReady($record)) {
            $violations[] = 'Edition detail route resolution requires acquisition-state governance.';
        }

        if (($record['archiveContinuity'] ?? false) !== true) {
            $violations[] = 'Edition detail route resolution requires archive continuity.';
        }

        return $violations;
    }

    /**
     * @param array<string, mixed> $record
     */
    private function hasDestinationRoutes(array $record): bool
    {
        $routes = $record['routes'] ?? [];

        if (!\is_array($routes)) {
            return false;
        }

        return \is_string($routes['en'] ?? null)
            && $routes['en'] !== ''
            && \is_string($routes['de'] ?? null)
            && $routes['de'] !== '';
    }

    /**
     * @param array<string, mixed> $record
     */
    private function hasDetailRouteContract(string $reference, array $record): bool
    {
        $routes = $record['routes'] ?? [];

        if (!\is_array($routes)) {
            return false;
        }

        return ($routes['en'] ?? null) === '/editions/' . $reference
            && ($routes['de'] ?? null) === '/de/editionen/' . $reference;
    }

    /**
     * @param array<string, mixed> $record
     *
     * @return array{inquiryAllowed: bool, ctaAllowed: bool}|null
     */
    private function getAcquisitionState(array $record, string $releaseState): ?array
    {
        $acquisition = $record['acquisition'] ?? [];

        if (!\is_array($acquisition)) {
            return null;
        }

        $state = $acquisition[$releaseState] ?? null;

        if (!\is_array($state)
            || !\array_key_exists('inquiryAllowed', $state)
            || !\array_key_exists('ctaAllowed', $state)
        ) {
            return null;
        }

        return [
            'inquiryAllowed' => $state['inquiryAllowed'] === true,
            'ctaAllowed' => $state['ctaAllowed'] === true,
        ];
    }

    /**
     * @param array<string, mixed> $record
     */
    private function canonicalRoute(array $record, string $locale): string
    {
        $routes = $record['routes'] ?? [];

        if (!\is_array($routes)) {
            return '';
        }

        $route = $routes[$locale] ?? '';

        return \is_string($route) ? $route : '';
    }

    /**
     * @param list<string> $violations
     *
     * @return array{state: string, exposureAllowed: false, violations: list<string>}
     */
    private function resolution(string $state, array $violations): array
    {
        return [
            'state' => $state,
            'exposureAllowed' => false,
            'violations' => $violations,
        ];
    }

    /**
     * @param array<string, mixed> $record
     *
     * @return array<string, mixed>
     */
    private function getPublication(array $record): array
    {
        $detailDestination = $record['detailDestination'] ?? [];

        if (!\is_array($detailDestination)) {
            return [];
        }

        $publication = $detailDestination['publication'] ?? [];

        return \is_array($publication) ? $publication : [];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function isGuardedRenderingPayload(array $payload): bool
    {
        if (\array_keys($payload) !== self::GUARDED_RENDERING_PAYLOAD_KEYS) {
            return false;
        }

        return \is_string($payload['reference'])
            && \is_string($payload['locale'])
            && \is_string($payload['canonicalRoute'])
            && \is_string($payload['releaseState'])
            && \is_bool($payload['archiveContinuity'])
            && \is_string($payload['displayTitle'])
            && \is_string($payload['summaryLabel'])
            && \is_string($payload['materialContext'])
            && \is_string($payload['spatialContext'])
            && \is_string($payload['governanceNote'])
            && $this->isAcquisitionPayload($payload['acquisitionState'])
            && $this->isCmsDestinationPayload($payload['cmsDestination']);
    }

    private function isAcquisitionPayload(mixed $payload): bool
    {
        return \is_array($payload)
            && \array_keys($payload) === ['inquiryAllowed', 'ctaAllowed']
            && \is_bool($payload['inquiryAllowed'])
            && \is_bool($payload['ctaAllowed']);
    }

    private function isCmsDestinationPayload(mixed $payload): bool
    {
        return \is_array($payload)
            && \array_keys($payload) === ['authority', 'blueprint']
            && $payload['authority'] === 'edition_destination'
            && $payload['blueprint'] === 'edition_detail';
    }
}
