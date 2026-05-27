<?php declare(strict_types=1);

namespace VeyluneTheme\Governance;

use VeyluneTheme\Edition\EditionReferenceRegistry;
use VeyluneTheme\Semantic\SemanticAuditResult;
use VeyluneTheme\Semantic\SemanticRegistry;

final class GovernanceAuditService
{
    private const LIVE_REFERENCES = [
        'material-study-travertine-volume-01',
        'material-study-basalt-plane',
    ];

    private const SIMULATED_REFERENCES = [
        'material-study-limestone-field',
        'material-study-granite-surface',
        'material-study-slate-line',
        'material-study-porphyry-plane',
        'material-study-tuff-surface',
    ];

    private const ADJACENCY_TERMS = [
        'next',
        'previous',
        'related',
        'featured',
        'group',
        'collection',
        'recommend',
        'recommended',
        'other editions',
        'archive',
        'sequence',
    ];

    private const STOREFRONT_TERMS = [
        'next',
        'previous',
        'related',
        'featured',
        'group',
        'collection',
        'catalog',
        'shop',
        'browse',
        'recommend',
        'recommended',
        'other editions',
        'archive',
        'sequence',
        'series',
    ];

    public function __construct(
        private readonly EditionReferenceRegistry $editionReferenceRegistry,
        private readonly SemanticRegistry $semanticRegistry
    ) {
    }

    public function auditSemanticReferences(): SemanticAuditResult
    {
        return $this->editionReferenceRegistry->auditApprovedSemanticReferences();
    }

    public function auditSemanticAuthoringWorkflow(): SemanticAuditResult
    {
        $valid = $this->semanticRegistry->reviewSemanticContribution($this->validAuthoringFixture());
        $blocked = $this->semanticRegistry->reviewSemanticContribution($this->blockedAuthoringFixture());
        $violations = [];

        if (!$valid->passed()) {
            $violations[] = 'Valid semantic authoring contribution was rejected.';
            $violations = [...$violations, ...$valid->violations()];
        }

        if ($blocked->passed()) {
            $violations[] = 'Invalid semantic authoring contribution was accepted.';
        }

        return new SemanticAuditResult($violations === [], $violations, [], [
            'validContribution' => $valid->internalObservability(),
            'blockedContribution' => $blocked->internalObservability(),
            'blockedViolationCount' => \count($blocked->violations()),
        ]);
    }

    public function auditDistributedRuntime(): SemanticAuditResult
    {
        $payloads = $this->livePayloads();
        $violations = [];

        foreach (self::LIVE_REFERENCES as $reference) {
            foreach (['en', 'de'] as $locale) {
                if (!isset($payloads[$reference][$locale])) {
                    $violations[] = sprintf('%s:%s is not publicly renderable under governed runtime.', $reference, $locale);
                }
            }
        }

        if (\count($this->editionReferenceRegistry->approvedReferences()) !== 2) {
            $violations[] = 'Distributed runtime rehearsal requires exactly two governed Edition references.';
        }

        $violations = [
            ...$violations,
            ...$this->auditStructuralParity($payloads),
            ...$this->auditSemanticDensityParity($payloads, ['displayTitle', 'summaryLabel', 'materialContext', 'spatialContext', 'governanceNote'], 4, 'two-route semantic density parity'),
            ...$this->auditTopologyNeutrality($payloads, false),
            ...$this->auditRouteNetworkVisibility($payloads),
            ...$this->auditForbiddenTextTerms($payloads, self::ADJACENCY_TERMS, 'adjacency-sensitive language'),
        ];

        return $this->result($violations, [
            'scope' => 'distributed-runtime',
            'liveReferences' => self::LIVE_REFERENCES,
        ]);
    }

    public function auditTopologyPressure(): SemanticAuditResult
    {
        $pressurePayloads = array_replace($this->livePayloads(), $this->simulatedPayloads());
        $validViolations = [
            ...$this->auditScaleBoundary($pressurePayloads),
            ...$this->auditStructuralParity($pressurePayloads),
            ...$this->auditSemanticDensityParity($pressurePayloads, ['summaryLabel', 'materialContext', 'spatialContext', 'governanceNote'], 6, 'semantic density drift under topology pressure'),
            ...$this->auditTopologyNeutrality($pressurePayloads, true),
            ...$this->auditStorefrontEmergence($pressurePayloads),
            ...$this->auditRouteNetworkVisibility($pressurePayloads),
        ];

        $failureCases = [
            'implied-route-sequence' => $this->withMutatedField($pressurePayloads, 'material-study-limestone-field', 'en', 'canonicalRoute', '/editions/material-study-limestone-field-02'),
            'implied-grouping' => $this->withMutatedField($pressurePayloads, 'material-study-granite-surface', 'en', 'summaryLabel', 'Governed public Edition collection group for related material records.'),
            'route-count-implication' => $this->withMutatedField($pressurePayloads, 'material-study-slate-line', 'en', 'governanceNote', 'This record is one of seven governed public Edition records.'),
            'recommendation-implication' => $this->withMutatedField($pressurePayloads, 'material-study-porphyry-plane', 'en', 'summaryLabel', 'Recommended alongside other Editions in a material sequence.'),
            'route-network-implication' => $this->withMutatedField($pressurePayloads, 'material-study-tuff-surface', 'en', 'summaryLabel', 'Browse the Edition catalog through this governed route network.'),
        ];

        $failedInjectionDetections = [];
        foreach ($failureCases as $name => $payloads) {
            $violations = [
                ...$this->auditTopologyNeutrality($payloads, true),
                ...$this->auditStorefrontEmergence($payloads),
                ...$this->auditRouteNetworkVisibility($payloads),
            ];

            if ($violations === []) {
                $failedInjectionDetections[] = $name;
            }
        }

        foreach ($failedInjectionDetections as $case) {
            $validViolations[] = 'Topology failure injection was not detected: ' . $case;
        }

        return $this->result($validViolations, [
            'scope' => 'topology-pressure',
            'simulatedRouteCandidates' => \count(self::SIMULATED_REFERENCES),
            'topologyFailureInjections' => \count($failureCases),
        ]);
    }

    /**
     * @return array<string, array<string, array<string, mixed>>>
     */
    private function livePayloads(): array
    {
        $payloads = [];

        foreach (self::LIVE_REFERENCES as $reference) {
            foreach (['en', 'de'] as $locale) {
                $payload = $this->editionReferenceRegistry->buildGuardedRenderingPayload($reference, $locale);

                if ($payload !== null) {
                    $payloads[$reference][$locale] = $payload;
                }
            }
        }

        return $payloads;
    }

    /**
     * @return array<string, array<string, array<string, mixed>>>
     */
    private function simulatedPayloads(): array
    {
        $payloads = [];

        foreach (self::SIMULATED_REFERENCES as $reference) {
            $title = $this->titleFromReference($reference);
            $material = $this->materialFromReference($reference);

            $payloads[$reference] = [
                'en' => $this->payload(
                    $reference,
                    'en',
                    '/editions/' . $reference,
                    $title,
                    $material . ' surface, mineral variation, and edge alignment'
                ),
                'de' => $this->payload(
                    $reference,
                    'de',
                    '/de/editionen/' . $reference,
                    $title,
                    $material . 'oberflaeche, mineralische Variation und Kantenausrichtung'
                ),
            ];
        }

        return $payloads;
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(string $reference, string $locale, string $route, string $title, string $materialContext): array
    {
        return [
            'reference' => $reference,
            'locale' => $locale,
            'canonicalRoute' => $route,
            'releaseState' => 'active',
            'acquisitionState' => [
                'inquiryAllowed' => true,
                'ctaAllowed' => true,
            ],
            'cmsDestination' => [
                'authority' => 'edition_destination',
                'blueprint' => 'edition_detail',
            ],
            'archiveContinuity' => true,
            'displayTitle' => $title,
            'summaryLabel' => $locale === 'de'
                ? 'Gesteuertes oeffentliches Editionsdokument mit freigegebenem Material-, Proportions- und Raumkontext.'
                : 'Governed public Edition record describing approved material, proportion, and spatial context.',
            'materialContext' => $materialContext,
            'spatialContext' => $locale === 'de'
                ? 'Innenraummassstab, Wandbezug und horizontale Platzierung ohne Nutzungsszenario'
                : 'Interior scale, wall adjacency, and horizontal placement without use-case framing',
            'governanceNote' => $locale === 'de'
                ? 'Dieses Dokument bleibt an freigegebenen Editionskontext, stabile Routenidentitaet und oeffentliche Dokumentgrenzen gebunden.'
                : 'This record remains bounded by approved Edition context, stable route identity, and public-record boundaries.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validAuthoringFixture(): array
    {
        return [
            'id' => 'authoring-valid-18-8',
            'state' => 'semantic_approval',
            'targetState' => 'deployment_ready',
            'contributorRole' => 'semantic_contributor',
            'reviewerRole' => 'semantic_reviewer',
            'approverRole' => 'semantic_approver',
            'semanticVersionId' => 'sem-authoring-18-8-valid',
            'rollbackTarget' => 'sem-18-6-centralized-registry-001',
            'template' => 'edition_scalar_context',
            'routes' => ['/editions/material-study-travertine-volume-01', '/de/editionen/material-study-travertine-volume-01'],
            'locales' => ['en', 'de'],
            'fields' => [
                'displayTitle.en' => 'Travertine Material Study, Volume 01',
                'displayTitle.de' => 'Travertin-Materialstudie, Volumen 01',
                'summaryLabel.en' => 'Governed public Edition record describing approved material, proportion, and spatial context.',
                'summaryLabel.de' => 'Gesteuertes oeffentliches Editionsdokument mit freigegebenem Material-, Proportions- und Raumkontext.',
                'materialContext.en' => 'Travertine surface, mineral variation, and edge alignment',
                'materialContext.de' => 'Travertinoberflaeche, mineralische Variation und Kantenausrichtung',
                'spatialContext.en' => 'Interior scale, wall adjacency, and horizontal placement without use-case framing',
                'spatialContext.de' => 'Innenraummassstab, Wandbezug und horizontale Platzierung ohne Nutzungsszenario',
                'governanceNote.en' => 'This record remains bounded by approved Edition context, stable route identity, and public-record boundaries.',
                'governanceNote.de' => 'Dieses Dokument bleibt an freigegebenen Editionskontext, stabile Routenidentitaet und oeffentliche Dokumentgrenzen gebunden.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function blockedAuthoringFixture(): array
    {
        return [
            'id' => 'authoring-blocked-18-8',
            'state' => 'draft',
            'targetState' => 'deployed',
            'contributorRole' => 'semantic_contributor',
            'reviewerRole' => 'semantic_contributor',
            'approverRole' => 'semantic_contributor',
            'deploymentRole' => 'semantic_contributor',
            'semanticVersionId' => 'sem-authoring-18-8-blocked',
            'rollbackTarget' => '',
            'template' => 'edition_scalar_context',
            'routes' => ['/editions/material-study-travertine-volume-01'],
            'locales' => ['en'],
            'directProductionMutation' => true,
            'routeLocalOwnership' => true,
            'freeformStructure' => true,
            'introducesNewTerminology' => true,
            'introducesNewSemanticStructure' => true,
            'bypassRequested' => true,
            'fields' => [
                'summaryLabel.en' => 'Exclusive curated story for refined living and private access.',
            ],
        ];
    }

    /**
     * @param array<string, array<string, array<string, mixed>>> $payloads
     *
     * @return list<string>
     */
    private function auditScaleBoundary(array $payloads): array
    {
        if (\count($payloads) !== \count(self::LIVE_REFERENCES) + \count(self::SIMULATED_REFERENCES)) {
            return ['Topology-pressure simulation did not preserve expected internal scale boundary.'];
        }

        foreach (self::SIMULATED_REFERENCES as $reference) {
            if ($this->editionReferenceRegistry->has($reference)) {
                return ['Internal simulated route candidate became a governed public registry entry.'];
            }
        }

        return [];
    }

    /**
     * @param array<string, array<string, array<string, mixed>>> $payloads
     *
     * @return list<string>
     */
    private function auditStructuralParity(array $payloads): array
    {
        $violations = [];
        $expectedKeys = null;

        foreach ($payloads as $reference => $localizedPayloads) {
            foreach ($localizedPayloads as $locale => $payload) {
                $keys = array_keys($payload);

                if ($expectedKeys === null) {
                    $expectedKeys = $keys;
                    continue;
                }

                if ($keys !== $expectedKeys) {
                    $violations[] = sprintf('%s:%s has structural payload parity drift.', $reference, $locale);
                }
            }
        }

        return $violations;
    }

    /**
     * @param array<string, array<string, array<string, mixed>>> $payloads
     * @param list<string> $fields
     *
     * @return list<string>
     */
    private function auditSemanticDensityParity(array $payloads, array $fields, int $allowedDelta, string $message): array
    {
        $violations = [];

        foreach (['en', 'de'] as $locale) {
            foreach ($fields as $field) {
                $counts = [];

                foreach ($payloads as $reference => $localizedPayloads) {
                    $value = $localizedPayloads[$locale][$field] ?? '';
                    $counts[$reference] = \is_string($value) ? str_word_count($value) : 0;
                }

                if ($counts !== [] && max($counts) - min($counts) > $allowedDelta) {
                    $violations[] = sprintf('%s:%s exceeds %s.', $locale, $field, $message);
                }
            }
        }

        return $violations;
    }

    /**
     * @param array<string, array<string, array<string, mixed>>> $payloads
     *
     * @return list<string>
     */
    private function auditTopologyNeutrality(array $payloads, bool $pressureMode): array
    {
        $violations = [];
        $routes = [];

        foreach ($payloads as $reference => $localizedPayloads) {
            foreach (['en', 'de'] as $locale) {
                $route = $localizedPayloads[$locale]['canonicalRoute'] ?? '';
                $expectedPrefix = $locale === 'de' ? '/de/editionen/' : '/editions/';

                if (!\is_string($route) || !str_starts_with($route, $expectedPrefix . $reference)) {
                    $violations[] = sprintf('%s:%s has route isolation drift.', $reference, $locale);
                    continue;
                }

                if ($pressureMode && preg_match('/(?:^|-)0?\d+$/', $reference) === 1 && !\in_array($reference, self::LIVE_REFERENCES, true)) {
                    $violations[] = sprintf('%s:%s creates sequence-sensitive route naming under pressure.', $reference, $locale);
                }

                if ($pressureMode && preg_match('/(?:^|-)0?\d+$/', basename($route)) === 1 && $route !== ($expectedPrefix . $reference)) {
                    $violations[] = sprintf('%s:%s creates sequence-sensitive route topology under pressure.', $reference, $locale);
                }

                $routes[] = $route;
            }
        }

        if (\count($routes) !== \count(array_unique($routes))) {
            $violations[] = 'Distributed runtime contains duplicate canonical routes.';
        }

        return $violations;
    }

    /**
     * @param array<string, array<string, array<string, mixed>>> $payloads
     *
     * @return list<string>
     */
    private function auditStorefrontEmergence(array $payloads): array
    {
        return $this->auditForbiddenTextTerms($payloads, self::STOREFRONT_TERMS, 'storefront-emergence language');
    }

    /**
     * @param array<string, array<string, array<string, mixed>>> $payloads
     * @param list<string> $terms
     *
     * @return list<string>
     */
    private function auditForbiddenTextTerms(array $payloads, array $terms, string $message): array
    {
        $violations = [];

        foreach ($payloads as $reference => $localizedPayloads) {
            foreach ($localizedPayloads as $locale => $payload) {
                $publicText = strtolower(implode(' ', array_filter($payload, 'is_string')));

                foreach ($terms as $term) {
                    if (preg_match('/(?<![a-z0-9])' . preg_quote($term, '/') . '(?![a-z0-9])/i', $publicText) === 1) {
                        $violations[] = sprintf('%s:%s creates %s.', $reference, $locale, $message);
                    }
                }
            }
        }

        return $violations;
    }

    /**
     * @param array<string, array<string, array<string, mixed>>> $payloads
     *
     * @return list<string>
     */
    private function auditRouteNetworkVisibility(array $payloads): array
    {
        $violations = [];
        $references = array_keys($payloads);

        foreach ($payloads as $reference => $localizedPayloads) {
            foreach ($localizedPayloads as $locale => $payload) {
                $publicText = strtolower(implode(' ', array_filter($payload, 'is_string')));

                foreach ($references as $otherReference) {
                    if ($reference !== $otherReference && str_contains($publicText, $otherReference)) {
                        $violations[] = sprintf('%s:%s exposes route-network visibility.', $reference, $locale);
                    }
                }

                if (preg_match('/\b(?:one|two|three|four|five|six|seven|eight|nine|\d+)\s+of\s+(?:one|two|three|four|five|six|seven|eight|nine|\d+)\b/i', $publicText) === 1
                    || preg_match('/\b(?:one|two|three|four|five|six|seven|eight|nine|\d+)\s+(?:routes|editions|records)\b/i', $publicText) === 1
                ) {
                    $violations[] = sprintf('%s:%s exposes route-count or system-scale language.', $reference, $locale);
                }
            }
        }

        return $violations;
    }

    /**
     * @param array<string, array<string, array<string, mixed>>> $payloads
     *
     * @return array<string, array<string, array<string, mixed>>>
     */
    private function withMutatedField(array $payloads, string $reference, string $locale, string $field, string $value): array
    {
        $payloads[$reference][$locale][$field] = $value;

        return $payloads;
    }

    /**
     * @param list<string> $violations
     * @param array<string, mixed> $observability
     */
    private function result(array $violations, array $observability): SemanticAuditResult
    {
        $violations = array_values(array_unique($violations));

        return new SemanticAuditResult($violations === [], $violations, [], $observability);
    }

    private function titleFromReference(string $reference): string
    {
        $parts = explode('-', str_replace('material-study-', '', $reference));

        return ucfirst($parts[0] ?? 'Material') . ' Material Study';
    }

    private function materialFromReference(string $reference): string
    {
        $parts = explode('-', str_replace('material-study-', '', $reference));

        return ucfirst($parts[0] ?? 'Material');
    }
}
