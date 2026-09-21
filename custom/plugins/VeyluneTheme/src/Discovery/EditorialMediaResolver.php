<?php declare(strict_types=1);

namespace VeyluneTheme\Discovery;

use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use VeyluneTheme\Preview\EditorialMediaRegistry;

final class EditorialMediaResolver
{
    public function __construct(private readonly EntityRepository $mediaRepository)
    {
    }

    /** @return array{url: string, alt: string, width: int, height: int, source: string, registryId: string, fallbackAsset: string} */
    public function resolve(string $destinationId, string $fallbackAsset, string $fallbackAlt, Context $context): array
    {
        $registryId = EditorialMediaRegistry::mediaId($destinationId);
        $media = $this->mediaRepository->search(new Criteria([$registryId]), $context)->first();

        if (!$media instanceof MediaEntity || !$media->getUrl()) {
            return [
                'url' => '', 'alt' => $fallbackAlt, 'width' => 0, 'height' => 0,
                'source' => 'theme_fallback', 'registryId' => $registryId, 'fallbackAsset' => $fallbackAsset,
            ];
        }

        $metadata = $media->getMetaData() ?? [];
        $translated = $media->getTranslated();

        return [
            'url' => $media->getUrl(),
            'alt' => trim((string) ($translated['alt'] ?? '')) ?: $fallbackAlt,
            'width' => (int) ($metadata['width'] ?? 0),
            'height' => (int) ($metadata['height'] ?? 0),
            'source' => 'shopware_admin_media',
            'registryId' => $registryId,
            'fallbackAsset' => $fallbackAsset,
        ];
    }
}
