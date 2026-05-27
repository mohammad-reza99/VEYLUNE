<?php declare(strict_types=1);

namespace VeyluneTheme\Semantic;

final class SemanticRegistry
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $registry = null;

    /**
     * @param array<string, string> $localizedFields
     * @param list<string> $routes
     * @param list<string> $locales
     */
    public function auditSemanticChange(
        string $semanticVersionId,
        string $rollbackTarget,
        array $localizedFields,
        array $routes,
        array $locales
    ): SemanticAuditResult {
        $registry = $this->registry();
        $violations = [];
        $warnings = [];
        $observability = [
            'semanticVersionId' => $semanticVersionId,
            'rollbackTarget' => $rollbackTarget,
            'affectedRoutes' => $routes,
            'affectedLocales' => $locales,
            'deploymentBlocking' => true,
            'copyChangesAreGovernanceEvents' => ($registry['policy']['copyChangesAreGovernanceEvents'] ?? false) === true,
            'scanResults' => [],
        ];

        if ($semanticVersionId === '') {
            $violations[] = 'Semantic version ID is required.';
        }

        if ($rollbackTarget === '') {
            $violations[] = 'Semantic rollback target is required.';
        }

        if (($registry['policy']['routeLocalCopyOwnershipAllowed'] ?? true) !== false) {
            $violations[] = 'Route-local semantic ownership must remain disabled.';
        }

        foreach ($this->validateRegistryIntegrity($registry) as $violation) {
            $violations[] = $violation;
        }

        foreach ($localizedFields as $field => $value) {
            $fieldViolations = $this->auditField($field, $value);
            $observability['scanResults'][$field] = $fieldViolations['observability'];
            $violations = [...$violations, ...$fieldViolations['violations']];
            $warnings = [...$warnings, ...$fieldViolations['warnings']];
        }

        $parityViolations = $this->auditLocaleParity($localizedFields, $locales);
        $violations = [...$violations, ...$parityViolations];

        return new SemanticAuditResult($violations === [], $violations, $warnings, $observability);
    }

    /**
     * @param array{
     *     id?: string,
     *     state?: string,
     *     targetState?: string,
     *     contributorRole?: string,
     *     reviewerRole?: string,
     *     approverRole?: string,
     *     deploymentRole?: string,
     *     rollbackRole?: string,
     *     semanticVersionId?: string,
     *     rollbackTarget?: string,
     *     template?: string,
     *     fields?: array<string, string>,
     *     routes?: list<string>,
     *     locales?: list<string>,
     *     directProductionMutation?: bool,
     *     routeLocalOwnership?: bool,
     *     freeformStructure?: bool,
     *     introducesNewTerminology?: bool,
     *     introducesNewSemanticStructure?: bool,
     *     bypassRequested?: bool
     * } $contribution
     */
    public function reviewSemanticContribution(array $contribution): SemanticAuditResult
    {
        $registry = $this->registry();
        $violations = [];
        $warnings = [];

        $state = $this->stringValue($contribution['state'] ?? null);
        $targetState = $this->stringValue($contribution['targetState'] ?? null);
        $template = $this->stringValue($contribution['template'] ?? null);
        $fields = \is_array($contribution['fields'] ?? null) ? $contribution['fields'] : [];
        $routes = \is_array($contribution['routes'] ?? null) ? array_values(array_filter($contribution['routes'], 'is_string')) : [];
        $locales = \is_array($contribution['locales'] ?? null) ? array_values(array_filter($contribution['locales'], 'is_string')) : [];

        foreach ($this->validateAuthoringWorkflowIntegrity($registry) as $violation) {
            $violations[] = $violation;
        }

        if (!$this->isAllowedTransition($state, $targetState)) {
            $violations[] = 'Semantic authoring lifecycle transition is not allowed.';
        }

        if (!$this->hasRoleCapability($this->stringValue($contribution['contributorRole'] ?? null), 'mayAuthorDraft')) {
            $violations[] = 'Semantic contribution requires an authorized semantic contributor.';
        }

        if (\in_array($targetState, ['semantic_audit', 'semantic_approval'], true)
            && !$this->hasRoleCapability($this->stringValue($contribution['reviewerRole'] ?? null), 'mayReview')
        ) {
            $violations[] = 'Semantic review requires an authorized semantic reviewer.';
        }

        if (\in_array($targetState, ['deployment_ready', 'deployed'], true)
            && !$this->hasRoleCapability($this->stringValue($contribution['approverRole'] ?? null), 'mayApprove')
        ) {
            $violations[] = 'Semantic approval requires centralized semantic approval authority.';
        }

        if ($targetState === 'deployed'
            && !$this->hasRoleCapability($this->stringValue($contribution['deploymentRole'] ?? null), 'mayDeploy')
        ) {
            $violations[] = 'Semantic deployment requires deployment authority.';
        }

        if ($targetState === 'rollback'
            && !$this->hasRoleCapability($this->stringValue($contribution['rollbackRole'] ?? null), 'mayRollback')
        ) {
            $violations[] = 'Semantic rollback requires rollback authority.';
        }

        if (($contribution['directProductionMutation'] ?? false) === true
            && ($registry['policy']['directProductionMutationAllowed'] ?? true) !== true
        ) {
            $violations[] = 'Direct production semantic mutation is forbidden.';
        }

        if (($contribution['routeLocalOwnership'] ?? false) === true
            && ($registry['policy']['routeLocalCopyOwnershipAllowed'] ?? true) !== true
        ) {
            $violations[] = 'Route-local semantic ownership is forbidden.';
        }

        if (($contribution['freeformStructure'] ?? false) === true
            && ($registry['policy']['freeformEditorialCopyAllowed'] ?? true) !== true
        ) {
            $violations[] = 'Freeform semantic expansion is forbidden.';
        }

        if (($contribution['introducesNewTerminology'] ?? false) === true
            && ($registry['policy']['newTerminologyWithoutRegistryApprovalAllowed'] ?? true) !== true
        ) {
            $violations[] = 'New terminology requires registry approval before contribution.';
        }

        if (($contribution['introducesNewSemanticStructure'] ?? false) === true
            && ($registry['policy']['newSemanticStructuresWithoutConstitutionalApprovalAllowed'] ?? true) !== true
        ) {
            $violations[] = 'New semantic structures require constitutional authorization.';
        }

        if (($contribution['bypassRequested'] ?? false) === true
            && ($registry['policy']['semanticReviewBypassAllowed'] ?? true) !== true
        ) {
            $violations[] = 'Semantic review bypass is forbidden.';
        }

        foreach ($this->validateContributionTemplate($template, $fields, $locales) as $violation) {
            $violations[] = $violation;
        }

        $audit = $this->auditSemanticChange(
            $this->stringValue($contribution['semanticVersionId'] ?? null) ?? '',
            $this->stringValue($contribution['rollbackTarget'] ?? null) ?? '',
            $fields,
            $routes,
            $locales
        );

        $violations = [...$violations, ...$audit->violations()];
        $warnings = [...$warnings, ...$audit->warnings()];

        return new SemanticAuditResult($violations === [], array_values(array_unique($violations)), $warnings, [
            'authoringContributionId' => $this->stringValue($contribution['id'] ?? null) ?? '',
            'state' => $state,
            'targetState' => $targetState,
            'template' => $template,
            'approvalTraceable' => $this->stringValue($contribution['approverRole'] ?? null) !== null,
            'rollbackTraceable' => $this->stringValue($contribution['rollbackTarget'] ?? null) !== null,
            'semanticAudit' => $audit->internalObservability(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function authoringWorkflowSummary(): array
    {
        $registry = $this->registry();

        return [
            'workflow' => $registry['authoringWorkflow'] ?? [],
            'contributorRoles' => $registry['contributorRoles'] ?? [],
            'semanticTemplates' => $registry['semanticTemplates'] ?? [],
            'directProductionMutationAllowed' => ($registry['policy']['directProductionMutationAllowed'] ?? true) === true,
            'semanticReviewBypassAllowed' => ($registry['policy']['semanticReviewBypassAllowed'] ?? true) === true,
        ];
    }

    /**
     * @param array<string, mixed> $registry
     *
     * @return list<string>
     */
    private function validateRegistryIntegrity(array $registry): array
    {
        $violations = [];

        foreach (['semanticApprover', 'rollbackAuthority', 'auditAuthority', 'deploymentBlockingAuthority'] as $owner) {
            if (!\is_string($registry['ownership'][$owner] ?? null) || $registry['ownership'][$owner] === '') {
                $violations[] = 'Semantic registry ownership is incomplete.';
                break;
            }
        }

        if (!\is_string($registry['versioning']['currentSemanticVersionId'] ?? null) || $registry['versioning']['currentSemanticVersionId'] === '') {
            $violations[] = 'Semantic registry version ID is missing.';
        }

        if (!\is_string($registry['versioning']['rollbackTarget'] ?? null) || $registry['versioning']['rollbackTarget'] === '') {
            $violations[] = 'Semantic registry rollback target is missing.';
        }

        foreach (['en', 'de'] as $locale) {
            if (!isset($registry['localeMappings'][$locale]) || !\is_array($registry['localeMappings'][$locale])) {
                $violations[] = 'Semantic registry locale mapping is incomplete.';
                break;
            }
        }

        foreach (($registry['controlledSynonyms'] ?? []) as $sets) {
            if (!\is_array($sets) || !isset($sets['approved'], $sets['forbidden']) || !\is_array($sets['approved']) || !\is_array($sets['forbidden'])) {
                $violations[] = 'Semantic registry synonym mapping is invalid.';
                break;
            }
        }

        return array_values(array_unique($violations));
    }

    /**
     * @param array<string, mixed> $registry
     *
     * @return list<string>
     */
    private function validateAuthoringWorkflowIntegrity(array $registry): array
    {
        $violations = [];

        foreach (['authoringWorkflow', 'contributorRoles', 'semanticTemplates'] as $key) {
            if (!isset($registry[$key]) || !\is_array($registry[$key])) {
                $violations[] = 'Semantic authoring workflow registry is incomplete.';
                return $violations;
            }
        }

        foreach (($registry['authoringWorkflow']['requiredReviewGates'] ?? []) as $gate) {
            if (!\is_string($gate) || $gate === '') {
                $violations[] = 'Semantic authoring review gate registry is invalid.';
                break;
            }
        }

        foreach (['semantic_contributor', 'semantic_reviewer', 'semantic_approver', 'semantic_deployment_authority', 'semantic_rollback_authority', 'semantic_governance_authority'] as $role) {
            if (!isset($registry['contributorRoles'][$role]) || !\is_array($registry['contributorRoles'][$role])) {
                $violations[] = 'Semantic contributor role registry is incomplete.';
                break;
            }
        }

        return $violations;
    }

    private function isAllowedTransition(?string $state, ?string $targetState): bool
    {
        if ($state === null || $targetState === null) {
            return false;
        }

        $workflow = $this->registry()['authoringWorkflow']['allowedTransitions'] ?? [];

        return \is_array($workflow)
            && isset($workflow[$state])
            && \is_array($workflow[$state])
            && \in_array($targetState, $workflow[$state], true);
    }

    private function hasRoleCapability(?string $role, string $capability): bool
    {
        if ($role === null) {
            return false;
        }

        $roles = $this->registry()['contributorRoles'] ?? [];

        return \is_array($roles)
            && isset($roles[$role])
            && \is_array($roles[$role])
            && ($roles[$role][$capability] ?? false) === true;
    }

    /**
     * @param array<string, string> $fields
     * @param list<string> $locales
     *
     * @return list<string>
     */
    private function validateContributionTemplate(?string $template, array $fields, array $locales): array
    {
        $templates = $this->registry()['semanticTemplates'] ?? [];

        if ($template === null || !\is_array($templates) || !isset($templates[$template]) || !\is_array($templates[$template])) {
            return ['Semantic contribution requires an approved semantic template.'];
        }

        $templateConfig = $templates[$template];
        $violations = [];
        $allowedFields = \is_array($templateConfig['fields'] ?? null) ? $templateConfig['fields'] : [];
        $allowedLocales = \is_array($templateConfig['locales'] ?? null) ? $templateConfig['locales'] : [];

        if (\count($fields) > (int) ($templateConfig['maxFields'] ?? 0) * max(1, \count($allowedLocales))) {
            $violations[] = 'Semantic contribution exceeds approved template field count.';
        }

        foreach ($fields as $field => $value) {
            if (!\is_string($value)) {
                $violations[] = 'Semantic contribution field values must be scalar strings.';
                continue;
            }

            $parts = explode('.', $field);
            $fieldName = $parts[0] ?? '';
            $locale = $parts[1] ?? '';

            if (!\in_array($fieldName, $allowedFields, true) || !\in_array($locale, $allowedLocales, true)) {
                $violations[] = 'Semantic contribution contains a field outside the approved template.';
            }
        }

        foreach ($allowedLocales as $locale) {
            if (!\in_array($locale, $locales, true)) {
                $violations[] = 'Semantic contribution locale set does not match approved template.';
            }
        }

        return array_values(array_unique($violations));
    }

    /**
     * @return array<string, mixed>
     */
    public function governanceSummary(): array
    {
        $registry = $this->registry();

        return [
            'registryVersion' => $registry['registryVersion'] ?? '',
            'currentSemanticVersionId' => $registry['versioning']['currentSemanticVersionId'] ?? '',
            'rollbackTarget' => $registry['versioning']['rollbackTarget'] ?? '',
            'ownership' => $registry['ownership'] ?? [],
            'deploymentStatus' => $registry['versioning']['deploymentStatus'] ?? '',
            'publicClassificationExposureAllowed' => ($registry['policy']['publicClassificationExposureAllowed'] ?? true) === true,
        ];
    }

    /**
     * @return array{violations: list<string>, warnings: list<string>, observability: array<string, mixed>}
     */
    private function auditField(string $field, string $value): array
    {
        $registry = $this->registry();
        $normalized = $this->normalize($value);
        $violations = [];
        $warnings = [];
        $observability = [
            'wordCount' => str_word_count($value),
            'forbiddenLanguage' => [],
            'unapprovedSynonyms' => [],
            'implications' => [],
            'emotionalIntensity' => 0,
            'prestigeSensitivity' => 0,
            'escalationSensitivity' => 0,
        ];

        $maxWords = (int) ($registry['limits']['maxWordsPerField'] ?? 18);
        if ($observability['wordCount'] > $maxWords) {
            $violations[] = sprintf('Semantic field "%s" exceeds bounded density limit.', $field);
        }

        foreach ($registry['forbiddenTerms'] ?? [] as $term) {
            if ($this->containsTerm($normalized, (string) $term)) {
                $observability['forbiddenLanguage'][] = $term;
                $violations[] = sprintf('Semantic field "%s" contains forbidden vocabulary.', $field);
            }
        }

        foreach (($registry['controlledSynonyms'] ?? []) as $base => $sets) {
            if (!\is_array($sets)) {
                continue;
            }

            foreach (($sets['forbidden'] ?? []) as $term) {
                if ($this->containsTerm($normalized, (string) $term)) {
                    $observability['unapprovedSynonyms'][] = ['base' => $base, 'term' => $term];
                    $violations[] = sprintf('Semantic field "%s" contains an unapproved controlled synonym.', $field);
                }
            }
        }

        foreach (($registry['classificationTerms'] ?? []) as $classification => $terms) {
            if (!\is_array($terms)) {
                continue;
            }

            foreach ($terms as $term) {
                if ($this->containsTerm($normalized, (string) $term)) {
                    $observability['implications'][] = $classification;
                    $severity = $registry['implicationClassifications'][$classification]['severity'] ?? 'blocking';
                    if ($severity === 'blocking') {
                        $violations[] = sprintf('Semantic field "%s" creates %s implication.', $field, $classification);
                    }
                }
            }
        }

        foreach (['emotionalIntensity', 'prestigeSensitivity', 'escalationSensitivity'] as $dimension) {
            $termsKey = $dimension . 'Terms';
            $limitKey = 'max' . ucfirst($dimension);
            $max = 0;

            foreach (($registry[$termsKey] ?? []) as $term => $score) {
                if ($this->containsTerm($normalized, (string) $term)) {
                    $max = max($max, (int) $score);
                }
            }

            $observability[$dimension] = $max;
            $limit = (int) ($registry['limits'][$limitKey] ?? 1);

            if ($max > $limit) {
                $violations[] = sprintf('Semantic field "%s" exceeds %s ceiling.', $field, $dimension);
            }
        }

        if ($violations === [] && $observability['wordCount'] > 14) {
            $warnings[] = sprintf('Semantic field "%s" is near the density ceiling.', $field);
        }

        return [
            'violations' => array_values(array_unique($violations)),
            'warnings' => $warnings,
            'observability' => $observability,
        ];
    }

    /**
     * @param array<string, string> $localizedFields
     * @param list<string> $locales
     *
     * @return list<string>
     */
    private function auditLocaleParity(array $localizedFields, array $locales): array
    {
        if (!\in_array('en', $locales, true) || !\in_array('de', $locales, true)) {
            return ['Semantic audit requires EN and DE locale parity.'];
        }

        $violations = [];
        $grouped = [];

        foreach ($localizedFields as $field => $value) {
            if (!str_ends_with($field, '.en') && !str_ends_with($field, '.de')) {
                continue;
            }

            $base = substr($field, 0, -3);
            $locale = substr($field, -2);
            $grouped[$base][$locale] = str_word_count($value);
        }

        foreach ($grouped as $base => $counts) {
            if (!isset($counts['en'], $counts['de'])) {
                $violations[] = sprintf('Semantic field "%s" is missing EN/DE parity.', $base);
                continue;
            }

            if (abs($counts['en'] - $counts['de']) > 8) {
                $violations[] = sprintf('Semantic field "%s" has multilingual density divergence.', $base);
            }
        }

        return $violations;
    }

    private function containsTerm(string $normalized, string $term): bool
    {
        $term = $this->normalize($term);

        if ($term === '') {
            return false;
        }

        return preg_match('/(?<![a-z0-9])' . preg_quote($term, '/') . '(?![a-z0-9])/i', $normalized) === 1;
    }

    private function normalize(string $value): string
    {
        return mb_strtolower($value);
    }

    private function stringValue(mixed $value): ?string
    {
        return \is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function registry(): array
    {
        if ($this->registry !== null) {
            return $this->registry;
        }

        $registry = require __DIR__ . '/../Resources/config/semantic_registry.php';

        return $this->registry = \is_array($registry) ? $registry : [];
    }
}
