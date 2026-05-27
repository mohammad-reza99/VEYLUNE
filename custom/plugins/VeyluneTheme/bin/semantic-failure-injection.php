<?php declare(strict_types=1);

use VeyluneTheme\Semantic\SemanticRegistry;

require __DIR__ . '/../src/Semantic/SemanticAuditResult.php';
require __DIR__ . '/../src/Semantic/SemanticRegistry.php';

$registry = new SemanticRegistry();

$cases = [
    'forbidden-vocabulary' => [
        'version' => 'sem-test-18-7',
        'rollback' => 'sem-rollback-test',
        'fields' => [
            'summaryLabel.en' => 'exclusive curated collectible record to discover more and reserve own coveted limited exceptional form',
            'summaryLabel.de' => 'exclusive curated collectible record to discover more and reserve own coveted limited exceptional form',
        ],
    ],
    'synonym-bypass' => [
        'version' => 'sem-test-18-7',
        'rollback' => 'sem-rollback-test',
        'fields' => [
            'summaryLabel.en' => 'elevated rare distinguished sought-after private access record',
            'summaryLabel.de' => 'elevated rare distinguished sought-after private access record',
        ],
    ],
    'emotional-escalation' => [
        'version' => 'sem-test-18-7',
        'rollback' => 'sem-rollback-test',
        'fields' => [
            'summaryLabel.en' => 'aspirational exceptional dreamlike prestige rhythm for desirable refined living',
            'summaryLabel.de' => 'aspirational exceptional dreamlike prestige rhythm for desirable refined living',
        ],
    ],
    'relationship-commercial-implication' => [
        'version' => 'sem-test-18-7',
        'rollback' => 'sem-rollback-test',
        'fields' => [
            'summaryLabel.en' => 'paired with part of a collection available soon explore more recommended alongside',
            'summaryLabel.de' => 'paired with part of a collection available soon explore more recommended alongside',
        ],
    ],
    'multilingual-divergence' => [
        'version' => 'sem-test-18-7',
        'rollback' => 'sem-rollback-test',
        'fields' => [
            'summaryLabel.en' => 'governed public record with material context',
            'summaryLabel.de' => 'luxury exclusive rare aspirational emotional exceptional prestige record',
        ],
    ],
    'missing-semantic-version' => [
        'version' => '',
        'rollback' => 'sem-rollback-test',
        'fields' => [
            'summaryLabel.en' => 'governed public record with material context',
            'summaryLabel.de' => 'governed public record with material context',
        ],
    ],
    'missing-rollback-target' => [
        'version' => 'sem-test-18-7',
        'rollback' => '',
        'fields' => [
            'summaryLabel.en' => 'governed public record with material context',
            'summaryLabel.de' => 'governed public record with material context',
        ],
    ],
];

foreach ($cases as $name => $case) {
    $result = $registry->auditSemanticChange(
        $case['version'],
        $case['rollback'],
        $case['fields'],
        ['/editions/test', '/de/editionen/test'],
        ['en', 'de']
    );

    echo 'CASE ' . $name . ' ' . ($result->passed() ? 'PASS' : 'FAIL') . PHP_EOL;

    foreach ($result->violations() as $violation) {
        echo '- ' . $violation . PHP_EOL;
    }

    echo '--' . PHP_EOL;
}
