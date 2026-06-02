<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class LaunchSimulationReport
{
    /**
     * @param list<string> $violations
     * @param array<string, int> $metrics
     */
    public function __construct(
        private readonly array $violations,
        private readonly array $metrics
    ) {
    }

    public function passed(): bool
    {
        return $this->violations === [];
    }

    /**
     * @return list<string>
     */
    public function violations(): array
    {
        return $this->violations;
    }

    /**
     * @return array<string, int>
     */
    public function metrics(): array
    {
        return $this->metrics;
    }
}
