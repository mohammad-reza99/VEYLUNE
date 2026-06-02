<?php declare(strict_types=1);

namespace VeyluneTheme\Sitemap;

use Shopware\Core\Content\Sitemap\Provider\AbstractUrlProvider;
use Shopware\Core\Content\Sitemap\Struct\Url;
use Shopware\Core\Content\Sitemap\Struct\UrlResult;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use VeyluneTheme\Edition\EditionReferenceRegistry;
use VeyluneTheme\Publication\PublicationStatePolicy;

final class IdentityUrlProvider extends AbstractUrlProvider
{
    public const IDENTITY_SALES_CHANNEL_ID = '019e3bf9c220717884d2a4eaca77c2d1';

    private const CHANGE_FREQ = 'weekly';
    private const HOMEPAGE_PRIORITY = 1.0;
    private const EDITION_PRIORITY = 0.7;
    private const REFERENCE_PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';
    private const CANDIDATE_KEYS = [
        'reference',
        'locale',
        'canonicalRoute',
        'publicationState',
        'sitemapEligible',
    ];

    public function __construct(
        private readonly EditionReferenceRegistry $editionReferenceRegistry
    ) {
    }

    public function getDecorated(): AbstractUrlProvider
    {
        throw new DecorationPatternException(self::class);
    }

    public function getName(): string
    {
        return 'veylune_identity';
    }

    public function getUrls(SalesChannelContext $context, int $limit, ?int $offset = null): UrlResult
    {
        $offset ??= 0;
        $locale = $this->resolveLocale($context);

        if ($context->getSalesChannelId() !== self::IDENTITY_SALES_CHANNEL_ID
            || $locale === null
            || $limit < 1
            || $offset < 0
        ) {
            return new UrlResult([], null);
        }

        $urls = [$this->buildUrl('', 'homepage', self::HOMEPAGE_PRIORITY)];
        $emittedRoutes = ['' => true];

        foreach ($this->editionReferenceRegistry->sitemapCandidates($locale) as $candidate) {
            if (!$this->isEligibleCandidate($candidate, $locale)) {
                continue;
            }

            $route = $candidate['canonicalRoute'];

            if (isset($emittedRoutes[$route])) {
                continue;
            }

            $emittedRoutes[$route] = true;
            $urls[] = $this->buildUrl($route, $candidate['reference'], self::EDITION_PRIORITY);
        }

        $page = \array_slice($urls, $offset, $limit);
        $nextOffset = $offset + \count($page);

        return new UrlResult($page, $nextOffset < \count($urls) ? $nextOffset : null);
    }

    private function resolveLocale(SalesChannelContext $context): ?string
    {
        $locale = strtolower($context->getLanguageInfo()->localeCode);

        return match (true) {
            $locale === 'en' || str_starts_with($locale, 'en-') => 'en',
            $locale === 'de' || str_starts_with($locale, 'de-') => 'de',
            default => null,
        };
    }

    /**
     * @param array<string, mixed> $candidate
     */
    private function isEligibleCandidate(array $candidate, string $locale): bool
    {
        if (\array_keys($candidate) !== self::CANDIDATE_KEYS
            || !\is_string($candidate['reference'])
            || !\is_string($candidate['locale'])
            || !\is_string($candidate['canonicalRoute'])
            || !\is_string($candidate['publicationState'])
            || !\is_bool($candidate['sitemapEligible'])
            || preg_match(self::REFERENCE_PATTERN, $candidate['reference']) !== 1
            || $candidate['locale'] !== $locale
            || $candidate['publicationState'] !== PublicationStatePolicy::STATE_PUBLISHED
            || $candidate['sitemapEligible'] !== true
        ) {
            return false;
        }

        $expectedRoute = $locale === 'en'
            ? '/editions/' . $candidate['reference']
            : '/de/editionen/' . $candidate['reference'];

        return $candidate['canonicalRoute'] === $expectedRoute;
    }

    private function buildUrl(string $route, string $identifier, float $priority): Url
    {
        $url = new Url();
        $url->setLoc($route);
        $url->setLastmod(new \DateTimeImmutable('today'));
        $url->setChangefreq(self::CHANGE_FREQ);
        $url->setPriority($priority);
        $url->setResource($this->getName());
        $url->setIdentifier($identifier);

        return $url;
    }
}
