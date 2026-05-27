<?php declare(strict_types=1);

use VeyluneTheme\Edition\EditionReferenceRegistry;
use VeyluneTheme\Governance\GovernanceAuditService;
use VeyluneTheme\Semantic\SemanticAuditResult;
use VeyluneTheme\Semantic\SemanticRegistry;

require __DIR__ . '/../src/Semantic/SemanticAuditResult.php';
require __DIR__ . '/../src/Semantic/SemanticRegistry.php';
require __DIR__ . '/../src/Edition/EditionReferenceRegistry.php';
require __DIR__ . '/../src/Governance/GovernanceAuditService.php';

$failures = [];

$registry = new SemanticRegistry();
$editionRegistry = new EditionReferenceRegistry($registry);
$auditService = new GovernanceAuditService($editionRegistry, $registry);

$pass = static function (string $label) use (&$failures): void {
    echo '[OK] ' . $label . PHP_EOL;
};

$fail = static function (string $label, array $details = []) use (&$failures): void {
    $message = '[FAIL] ' . $label;
    if ($details !== []) {
        $message .= ': ' . implode(' | ', $details);
    }

    $failures[] = $message;
    echo $message . PHP_EOL;
};

$expectPass = static function (string $label, SemanticAuditResult $result) use ($pass, $fail): void {
    if ($result->passed()) {
        $pass($label);
        return;
    }

    $fail($label, $result->violations());
};

$expectFail = static function (string $label, SemanticAuditResult $result) use ($pass, $fail): void {
    if (!$result->passed()) {
        $pass($label . ' blocked');
        return;
    }

    $fail($label . ' was accepted');
};

$approvedFields = [
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
];

$routes = [
    '/editions/material-study-travertine-volume-01',
    '/de/editionen/material-study-travertine-volume-01',
];
$locales = ['en', 'de'];

$expectPass(
    'semantic approved state',
    $registry->auditSemanticChange('sem-regression-approved-001', 'sem-18-6-centralized-registry-001', $approvedFields, $routes, $locales)
);

$semanticFailureCases = [
    'forbidden vocabulary' => ['summaryLabel.en' => 'Exclusive curated collectible exceptional record.'],
    'synonym bypass' => ['summaryLabel.en' => 'Elevated rare distinguished sought-after private access record.'],
    'commercial implication' => ['summaryLabel.en' => 'Governed public record with price, purchase, checkout, and own language.'],
    'relationship implication' => ['summaryLabel.en' => 'Governed record paired with official partner atelier context.'],
    'discoverability implication' => ['summaryLabel.en' => 'Discover more and explore more through searchable public index language.'],
    'multilingual divergence' => [
        'summaryLabel.en' => 'Governed public Edition record.',
        'summaryLabel.de' => 'Aspirational luxury exceptional exclusive refined prestigious desirable dreamlike collectible signature campaign story.',
    ],
];

foreach ($semanticFailureCases as $label => $mutations) {
    $fields = array_replace($approvedFields, $mutations);
    $expectFail(
        $label,
        $registry->auditSemanticChange('sem-regression-failure-001', 'sem-18-6-centralized-registry-001', $fields, $routes, $locales)
    );
}

$expectFail(
    'missing semantic version',
    $registry->auditSemanticChange('', 'sem-18-6-centralized-registry-001', $approvedFields, $routes, $locales)
);
$expectFail(
    'missing rollback target',
    $registry->auditSemanticChange('sem-regression-missing-rollback', '', $approvedFields, $routes, $locales)
);

$validContribution = [
    'id' => 'regression-authoring-valid',
    'state' => 'semantic_approval',
    'targetState' => 'deployment_ready',
    'contributorRole' => 'semantic_contributor',
    'reviewerRole' => 'semantic_reviewer',
    'approverRole' => 'semantic_approver',
    'semanticVersionId' => 'sem-authoring-regression-valid',
    'rollbackTarget' => 'sem-18-6-centralized-registry-001',
    'template' => 'edition_scalar_context',
    'routes' => $routes,
    'locales' => $locales,
    'fields' => $approvedFields,
];

$expectPass('valid authoring lifecycle', $registry->reviewSemanticContribution($validContribution));

$authoringFailureCases = [
    'invalid human-origin mutation' => [
        'fields' => array_replace($approvedFields, ['summaryLabel.en' => 'Exclusive campaign story for refined living.']),
        'directProductionMutation' => true,
    ],
    'unauthorized contributor transition' => [
        'state' => 'draft',
        'targetState' => 'deployed',
        'deploymentRole' => 'semantic_contributor',
    ],
    'unapproved terminology' => ['introducesNewTerminology' => true],
    'missing approval authority' => ['approverRole' => 'semantic_contributor'],
    'route-local ownership' => ['routeLocalOwnership' => true],
    'deployment bypass' => ['bypassRequested' => true],
];

foreach ($authoringFailureCases as $label => $mutations) {
    $expectFail($label, $registry->reviewSemanticContribution(array_replace($validContribution, $mutations)));
}

$expectPass('approved semantic command fixture', $auditService->auditSemanticReferences());
$expectPass('authoring audit command fixture', $auditService->auditSemanticAuthoringWorkflow());
$expectPass('distributed runtime approved state', $auditService->auditDistributedRuntime());
$expectPass('topology pressure approved state', $auditService->auditTopologyPressure());

$approvedPayload = $editionRegistry->buildGuardedRenderingPayload('material-study-travertine-volume-01', 'en');
$relatedPayload = $editionRegistry->buildGuardedRenderingPayload('material-study-basalt-plane', 'en');

if ($approvedPayload === null || $relatedPayload === null) {
    $fail('route-local corruption isolation baseline', ['approved payloads were not available']);
} else {
    $corruptedFields = array_replace($approvedFields, [
        'summaryLabel.en' => 'Recommended alongside an Edition collection sequence.',
    ]);
    $corruptionResult = $registry->auditSemanticChange(
        'sem-regression-isolated-corruption',
        'sem-18-6-centralized-registry-001',
        $corruptedFields,
        ['/editions/material-study-travertine-volume-01'],
        $locales
    );

    if (!$corruptionResult->passed() && $relatedPayload['reference'] === 'material-study-basalt-plane') {
        $pass('route-local corruption blocks isolated fixture without collapsing unrelated payload');
    } else {
        $fail('route-local corruption isolation');
    }
}

if ($failures !== []) {
    fwrite(STDERR, PHP_EOL . 'Governance regression checks failed.' . PHP_EOL);
    exit(1);
}

echo 'Governance regression checks passed.' . PHP_EOL;
