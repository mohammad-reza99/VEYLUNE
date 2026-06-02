<?php declare(strict_types=1);

namespace VeyluneTheme\Governance;

use Doctrine\DBAL\Connection;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\StorageAttributes;
use Shopware\Core\Framework\Uuid\Uuid;
use VeyluneTheme\Edition\EditionReferenceRegistry;
use VeyluneTheme\Publication\PublicationStatePolicy;
use VeyluneTheme\Semantic\SemanticAuditResult;
use VeyluneTheme\Sitemap\IdentityUrlProvider;

final class SitemapGovernanceAuditService
{
    public function __construct(
        private readonly FilesystemOperator $sitemapFilesystem,
        private readonly Connection $connection,
        private readonly EditionReferenceRegistry $editionReferenceRegistry
    ) {
    }

    public function auditIdentityArtifacts(): SemanticAuditResult
    {
        $violations = [];
        $checkedArtifacts = 0;
        $checkedUrls = 0;

        try {
            $artifacts = $this->identityArtifactPaths();
            $trackedArtifacts = [];

            foreach ($this->identityDomains() as $domain) {
                $locale = $this->resolveLocale($domain['localeCode']);

                if ($locale === null) {
                    $violations[] = 'Identity sitemap domain has unsupported locale: ' . $domain['localeCode'];

                    continue;
                }

                $prefix = sprintf(
                    'sitemap/salesChannel-%s-%s/%s-%s-sitemap',
                    IdentityUrlProvider::IDENTITY_SALES_CHANNEL_ID,
                    $domain['languageId'],
                    IdentityUrlProvider::IDENTITY_SALES_CHANNEL_ID,
                    $domain['domainId']
                );
                $matches = array_values(array_filter(
                    $artifacts,
                    static fn (string $path): bool => str_starts_with($path, $prefix) && str_ends_with($path, '.xml.gz')
                ));
                natsort($matches);
                $matches = array_values($matches);

                if ($matches === []) {
                    $violations[] = 'Identity sitemap domain requires at least one generated artifact: ' . $domain['url'];
                }

                $domainUrls = [];
                foreach ($matches as $path) {
                    $trackedArtifacts[$path] = true;
                    ++$checkedArtifacts;

                    $urls = $this->artifactUrls($path, $violations);
                    $checkedUrls += \count($urls);
                    $domainUrls = [...$domainUrls, ...$urls];
                }

                $this->validateUrls($domain['url'], $domainUrls, $this->expectedUrls($domain['url'], $locale), $violations);
            }

            foreach ($artifacts as $path) {
                if (!isset($trackedArtifacts[$path])) {
                    $violations[] = 'Unexpected or stale identity sitemap artifact: ' . $path;
                }
            }
        } catch (\Throwable $exception) {
            $violations[] = 'Identity sitemap audit failed closed: ' . $exception->getMessage();
        }

        return new SemanticAuditResult($violations === [], $violations, [], [
            'scope' => 'identity-sitemap',
            'checkedArtifacts' => $checkedArtifacts,
            'checkedUrls' => $checkedUrls,
        ]);
    }

    /**
     * @return list<array{domainId: string, languageId: string, url: string, localeCode: string}>
     */
    private function identityDomains(): array
    {
        /** @var list<array{domainId: string, languageId: string, url: string, localeCode: string}> $domains */
        $domains = $this->connection->fetchAllAssociative(
            'SELECT LOWER(HEX(scd.id)) AS domainId,
                    LOWER(HEX(scd.language_id)) AS languageId,
                    scd.url,
                    locale.code AS localeCode
             FROM sales_channel_domain scd
             INNER JOIN language ON language.id = scd.language_id
             INNER JOIN locale ON locale.id = language.locale_id
             WHERE scd.sales_channel_id = :salesChannelId
             ORDER BY scd.language_id, scd.url',
            ['salesChannelId' => Uuid::fromHexToBytes(IdentityUrlProvider::IDENTITY_SALES_CHANNEL_ID)]
        );

        if ($domains === []) {
            throw new \RuntimeException('Identity sales channel has no configured sitemap domains.');
        }

        return $domains;
    }

    /**
     * @return list<string>
     */
    private function identityArtifactPaths(): array
    {
        $paths = [];
        $prefix = 'sitemap/salesChannel-' . IdentityUrlProvider::IDENTITY_SALES_CHANNEL_ID . '-';

        foreach ($this->sitemapFilesystem->listContents('sitemap', true) as $attributes) {
            if (!$attributes instanceof StorageAttributes || !$attributes->isFile()) {
                continue;
            }

            $path = $attributes->path();

            if (str_starts_with($path, $prefix) && (str_ends_with($path, '.xml') || str_ends_with($path, '.xml.gz'))) {
                $paths[] = $path;
            }
        }

        sort($paths, \SORT_STRING);

        return $paths;
    }

    /**
     * @param list<string> $violations
     *
     * @return list<string>
     */
    private function artifactUrls(string $path, array &$violations): array
    {
        $contents = gzdecode($this->sitemapFilesystem->read($path));

        if ($contents === false) {
            $violations[] = 'Identity sitemap artifact is not valid gzip: ' . $path;

            return [];
        }

        $previous = libxml_use_internal_errors(true);
        $document = new \DOMDocument();
        $loaded = $document->loadXML($contents, \LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (!$loaded || $document->documentElement?->localName !== 'urlset') {
            $violations[] = 'Identity sitemap artifact is not a valid sitemap XML urlset: ' . $path;

            return [];
        }

        $urlNodes = $document->getElementsByTagName('url');
        $locNodes = $document->getElementsByTagName('loc');

        if ($urlNodes->count() !== $locNodes->count()) {
            $violations[] = 'Identity sitemap artifact contains malformed URL entries: ' . $path;
        }

        $urls = [];
        foreach ($locNodes as $locNode) {
            $urls[] = trim($locNode->textContent);
        }

        return $urls;
    }

    /**
     * @return list<string>
     */
    private function expectedUrls(string $domainUrl, string $locale): array
    {
        $urls = [rtrim($domainUrl, '/') . '/'];

        foreach ($this->editionReferenceRegistry->sitemapCandidates($locale) as $candidate) {
            if ($candidate['publicationState'] !== PublicationStatePolicy::STATE_PUBLISHED || $candidate['sitemapEligible'] !== true) {
                continue;
            }

            $urls[] = $this->absoluteUrl($domainUrl, $candidate['canonicalRoute']);
        }

        return $urls;
    }

    private function absoluteUrl(string $domainUrl, string $canonicalRoute): string
    {
        $domainPath = (string) (parse_url($domainUrl, \PHP_URL_PATH) ?? '');

        if ($domainPath !== '' && str_starts_with($canonicalRoute, rtrim($domainPath, '/') . '/')) {
            $canonicalRoute = substr($canonicalRoute, \strlen(rtrim($domainPath, '/')));
        }

        return rtrim($domainUrl, '/') . '/' . ltrim($canonicalRoute, '/');
    }

    /**
     * @param list<string> $urls
     * @param list<string> $expectedUrls
     * @param list<string> $violations
     */
    private function validateUrls(string $path, array $urls, array $expectedUrls, array &$violations): void
    {
        if (\count($urls) !== \count(array_unique($urls))) {
            $violations[] = 'Identity sitemap artifact contains duplicate URLs: ' . $path;
        }

        foreach (array_diff($urls, $expectedUrls) as $url) {
            $violations[] = 'Identity sitemap artifact leaked a non-governed URL: ' . $url;
        }

        foreach (array_diff($expectedUrls, $urls) as $url) {
            $violations[] = 'Identity sitemap artifact is missing a governed URL: ' . $url;
        }

        if ($urls !== $expectedUrls) {
            $violations[] = 'Identity sitemap artifact ordering or allowlist parity drifted: ' . $path;
        }
    }

    private function resolveLocale(string $localeCode): ?string
    {
        $locale = strtolower($localeCode);

        return match (true) {
            $locale === 'en' || str_starts_with($locale, 'en-') => 'en',
            $locale === 'de' || str_starts_with($locale, 'de-') => 'de',
            default => null,
        };
    }
}
