<?php declare(strict_types=1);

namespace VeyluneTheme\Sitemap;

use Shopware\Core\Content\Sitemap\Provider\AbstractUrlProvider;
use Shopware\Core\Content\Sitemap\Struct\UrlResult;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

final class IdentityScopedCustomUrlProvider extends AbstractUrlProvider
{
    public function __construct(
        private readonly AbstractUrlProvider $decorated
    ) {
    }

    public function getDecorated(): AbstractUrlProvider
    {
        return $this->decorated;
    }

    public function getName(): string
    {
        return $this->decorated->getName();
    }

    public function getUrls(SalesChannelContext $context, int $limit, ?int $offset = null): UrlResult
    {
        if ($context->getSalesChannelId() === IdentityUrlProvider::IDENTITY_SALES_CHANNEL_ID) {
            return new UrlResult([], null);
        }

        return $this->decorated->getUrls($context, $limit, $offset);
    }
}
