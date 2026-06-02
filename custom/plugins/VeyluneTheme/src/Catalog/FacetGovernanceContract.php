<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class FacetGovernanceContract
{
    public const OWNER_FACET_GOVERNANCE = 'facet_governance';

    public const STATE_DRAFT = 'draft';
    public const STATE_REVIEW = 'review';
    public const STATE_APPROVED = 'approved';
    public const STATE_PUBLISHED = 'published';
    public const STATE_SUSPENDED = 'suspended';
    public const STATE_RETIRED = 'retired';

    private const STATES = [
        self::STATE_DRAFT,
        self::STATE_REVIEW,
        self::STATE_APPROVED,
        self::STATE_PUBLISHED,
        self::STATE_SUSPENDED,
        self::STATE_RETIRED,
    ];

    private const PUBLIC_FACET_CANDIDATES = [
        PropertyDictionaryGovernanceContract::DICTIONARY_MATERIAL,
        PropertyDictionaryGovernanceContract::DICTIONARY_FINISH,
        PropertyDictionaryGovernanceContract::DICTIONARY_COLOR,
        PropertyDictionaryGovernanceContract::DICTIONARY_ROOM,
        PropertyDictionaryGovernanceContract::DICTIONARY_STYLE,
        PropertyDictionaryGovernanceContract::DICTIONARY_COLLECTION,
    ];

    private const ELIGIBILITY_REQUIREMENTS = [
        'facet_owner_approval',
        'published_facet_state',
        'canonical_dictionary_source',
        'bounded_controlled_values',
        'en_de_label_parity',
        'category_relevance',
        'minimum_result_coverage',
        'zero_result_behavior_review',
        'analytics_review_before_expansion',
    ];

    /**
     * @return list<string>
     */
    public static function states(): array
    {
        return self::STATES;
    }

    /**
     * @return list<string>
     */
    public static function publicFacetCandidates(): array
    {
        return self::PUBLIC_FACET_CANDIDATES;
    }

    /**
     * @return list<string>
     */
    public static function eligibilityRequirements(): array
    {
        return self::ELIGIBILITY_REQUIREMENTS;
    }

    public static function isPubliclyEligible(string $state): bool
    {
        return $state === self::STATE_PUBLISHED;
    }

    public static function isCanonicalFacetCandidate(string $dictionary): bool
    {
        return \in_array($dictionary, self::PUBLIC_FACET_CANDIDATES, true);
    }
}
