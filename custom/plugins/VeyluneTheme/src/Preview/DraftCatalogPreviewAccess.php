<?php declare(strict_types=1);

namespace VeyluneTheme\Preview;

use Symfony\Component\HttpFoundation\Request;

final class DraftCatalogPreviewAccess
{
    public function __construct(
        private readonly string $environment,
        private readonly bool $enabled,
        private readonly string $token
    ) {
    }

    public function isAllowed(Request $request): bool
    {
        if ($this->environment !== 'dev' || !$this->enabled || $this->token === '') {
            return false;
        }

        $providedToken = (string) $request->query->get('token', '');

        return $providedToken !== '' && \hash_equals($this->token, $providedToken);
    }

    public function token(): string
    {
        return $this->token;
    }
}
