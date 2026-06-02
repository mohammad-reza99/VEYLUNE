<?php declare(strict_types=1);

namespace VeyluneTheme\Retrieval;

use VeyluneTheme\Edition\EditionReferenceRegistry;
use VeyluneTheme\Storefront\StorefrontRoleRegistry;

final class IdentityRetrievalMediator
{
    public const DENIAL_INVALID_LOCALE = 'invalid_locale';
    public const DENIAL_MALFORMED_REFERENCE = 'malformed_reference';
    public const DENIAL_ROUTE_MISMATCH = 'route_mismatch';
    public const DENIAL_UNPUBLISHED_RECORD = 'unpublished_record';
    public const DENIAL_UNSUPPORTED_STATE = 'unsupported_state';
    public const DENIAL_REGISTRY_FAILURE = 'registry_failure';

    private const REFERENCE_PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    private const ROUTE_LOCALES = [
        'frontend.veylune.editions.detail.guard' => 'en',
        'frontend.veylune.editions.detail.guard.de' => 'de',
    ];

    private const CONFIGURED_LOCALE_MAPPINGS = [
        'en' => 'en',
        'en-gb' => 'en',
        'de' => 'de',
        'de-de' => 'de',
    ];

    public function __construct(
        private readonly EditionReferenceRegistry $editionReferenceRegistry
    ) {
    }

    /**
     * @return array{status: 'renderable', payload: array<string, mixed>}|array{status: 'denied', denial: string}
     */
    public function retrieve(string $salesChannelId, string $routeName, string $incomingUri, string $reference, string $requestLocale): array
    {
        if (!StorefrontRoleRegistry::isCanonicalPublicStorefront($salesChannelId)) {
            return $this->denied(self::DENIAL_ROUTE_MISMATCH);
        }

        $routeLocale = self::ROUTE_LOCALES[$routeName] ?? null;

        if ($routeLocale === null) {
            return $this->denied(self::DENIAL_ROUTE_MISMATCH);
        }

        $locale = $this->normalizeLocale($requestLocale);

        if ($locale === null) {
            return $this->denied(self::DENIAL_INVALID_LOCALE);
        }

        if ($locale !== $routeLocale) {
            return $this->denied(self::DENIAL_ROUTE_MISMATCH);
        }

        if (\preg_match(self::REFERENCE_PATTERN, $reference) !== 1) {
            return $this->denied(self::DENIAL_MALFORMED_REFERENCE);
        }

        $incomingPath = $this->normalizeIncomingPath($incomingUri);

        if ($incomingPath === null) {
            return $this->denied(self::DENIAL_ROUTE_MISMATCH);
        }

        try {
            $result = $this->editionReferenceRegistry->retrieveGuardedRenderingResult($reference, $locale);
        } catch (\Throwable) {
            return $this->denied(self::DENIAL_REGISTRY_FAILURE);
        }

        if ($result['state'] === EditionReferenceRegistry::STATE_PUBLICATION_BLOCKED) {
            return $this->denied(self::DENIAL_UNPUBLISHED_RECORD);
        }

        if ($result['state'] === EditionReferenceRegistry::STATE_UNSUPPORTED_PUBLICATION_STATE) {
            return $this->denied(self::DENIAL_UNSUPPORTED_STATE);
        }

        if ($result['state'] !== EditionReferenceRegistry::STATE_PUBLICLY_RENDERABLE
            || $result['exposureAllowed'] !== true
            || !\is_array($result['payload'])
        ) {
            return $this->denied(self::DENIAL_REGISTRY_FAILURE);
        }

        if (($result['payload']['canonicalRoute'] ?? null) !== $incomingPath) {
            return $this->denied(self::DENIAL_ROUTE_MISMATCH);
        }

        return [
            'status' => 'renderable',
            'payload' => $result['payload'],
        ];
    }

    private function normalizeLocale(string $locale): ?string
    {
        return self::CONFIGURED_LOCALE_MAPPINGS[strtolower(str_replace('_', '-', $locale))] ?? null;
    }

    private function normalizeIncomingPath(string $incomingUri): ?string
    {
        $path = parse_url($incomingUri, \PHP_URL_PATH);

        if (!\is_string($path)
            || $path === ''
            || !str_starts_with($path, '/')
            || str_contains($path, '//')
            || \preg_match('/%(?![0-9a-fA-F]{2})/', $path) === 1
        ) {
            return null;
        }

        $decodedPath = rawurldecode($path);

        if ($decodedPath !== $path
            || str_contains($decodedPath, '//')
            || \in_array('.', explode('/', $decodedPath), true)
            || \in_array('..', explode('/', $decodedPath), true)
        ) {
            return null;
        }

        return $decodedPath;
    }

    /**
     * @return array{status: 'denied', denial: string}
     */
    private function denied(string $denial): array
    {
        return [
            'status' => 'denied',
            'denial' => $denial,
        ];
    }
}
