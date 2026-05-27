<?php declare(strict_types=1);

namespace VeyluneTheme\Semantic;

final class SemanticAuditResult
{
    /**
     * @param list<string> $violations
     * @param list<string> $warnings
     * @param array<string, mixed> $internalObservability
     */
    public function __construct(
        private readonly bool $passed,
        private readonly array $violations,
        private readonly array $warnings,
        private readonly array $internalObservability
    ) {
    }

    public function passed(): bool
    {
        return $this->passed;
    }

    /**
     * @return list<string>
     */
    public function violations(): array
    {
        return $this->violations;
    }

    /**
     * @return list<string>
     */
    public function warnings(): array
    {
        return $this->warnings;
    }

    /**
     * @return array<string, mixed>
     */
    public function internalObservability(): array
    {
        return $this->internalObservability;
    }
}
