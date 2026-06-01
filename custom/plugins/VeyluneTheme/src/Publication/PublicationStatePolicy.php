<?php declare(strict_types=1);

namespace VeyluneTheme\Publication;

final class PublicationStatePolicy
{
    public const STATE_DRAFT = 'draft';
    public const STATE_REVIEW = 'review';
    public const STATE_APPROVED = 'approved';
    public const STATE_PUBLISHED = 'published';
    public const STATE_SUSPENDED = 'suspended';
    public const STATE_ARCHIVED = 'archived';

    private const STATES = [
        self::STATE_DRAFT,
        self::STATE_REVIEW,
        self::STATE_APPROVED,
        self::STATE_PUBLISHED,
        self::STATE_SUSPENDED,
        self::STATE_ARCHIVED,
    ];

    private const TRANSITIONS = [
        self::STATE_DRAFT => [self::STATE_REVIEW, self::STATE_ARCHIVED],
        self::STATE_REVIEW => [self::STATE_DRAFT, self::STATE_APPROVED, self::STATE_ARCHIVED],
        self::STATE_APPROVED => [self::STATE_DRAFT, self::STATE_REVIEW, self::STATE_PUBLISHED, self::STATE_ARCHIVED],
        self::STATE_PUBLISHED => [self::STATE_SUSPENDED, self::STATE_ARCHIVED],
        self::STATE_SUSPENDED => [self::STATE_APPROVED, self::STATE_PUBLISHED, self::STATE_ARCHIVED],
        self::STATE_ARCHIVED => [],
    ];

    /**
     * @return array{state: string, exposureAllowed: bool, violations: list<string>}
     */
    public function resolve(mixed $state, bool $archiveContinuity): array
    {
        if (!\is_string($state) || !\in_array($state, self::STATES, true)) {
            return $this->blocked('invalid', ['Publication state must be explicitly governed.']);
        }

        if ($state === self::STATE_ARCHIVED) {
            if (!$archiveContinuity) {
                return $this->blocked($state, ['Archived identity records require archive continuity.']);
            }

            return $this->blocked($state, ['Archived identity records are retained but not publicly renderable.']);
        }

        if ($state !== self::STATE_PUBLISHED) {
            return $this->blocked($state, ['Identity record is not explicitly published.']);
        }

        return [
            'state' => $state,
            'exposureAllowed' => true,
            'violations' => [],
        ];
    }

    public function canTransition(string $from, string $to): bool
    {
        return \in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    /**
     * @return list<string>
     */
    public function auditPolicy(): array
    {
        $violations = [];

        foreach (self::STATES as $state) {
            $resolution = $this->resolve($state, true);
            $expectedExposure = $state === self::STATE_PUBLISHED;

            if ($resolution['exposureAllowed'] !== $expectedExposure) {
                $violations[] = sprintf('Publication state "%s" has incorrect public exposure.', $state);
            }
        }

        if ($this->resolve('active', true)['exposureAllowed']) {
            $violations[] = 'Shopware-style activation state incorrectly implies publication.';
        }

        if ($this->resolve(self::STATE_ARCHIVED, false)['exposureAllowed']) {
            $violations[] = 'Archived identity record without continuity did not fail closed.';
        }

        if (!$this->canTransition(self::STATE_PUBLISHED, self::STATE_SUSPENDED)
            || $this->resolve(self::STATE_SUSPENDED, true)['exposureAllowed']
        ) {
            $violations[] = 'Suspension does not remove public exposure immediately.';
        }

        if (!$this->canTransition(self::STATE_SUSPENDED, self::STATE_PUBLISHED)
            || !$this->resolve(self::STATE_PUBLISHED, true)['exposureAllowed']
        ) {
            $violations[] = 'Suspension rollback contract is invalid.';
        }

        if ($this->canTransition(self::STATE_ARCHIVED, self::STATE_PUBLISHED)) {
            $violations[] = 'Archived identity records must remain terminal.';
        }

        return $violations;
    }

    /**
     * @param list<string> $violations
     *
     * @return array{state: string, exposureAllowed: false, violations: list<string>}
     */
    private function blocked(string $state, array $violations): array
    {
        return [
            'state' => $state,
            'exposureAllowed' => false,
            'violations' => $violations,
        ];
    }
}
