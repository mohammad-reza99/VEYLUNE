<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class MediaQualityAuditHarness
{
    private const MINIMUM_IMAGE_COUNT = 5;

    /**
     * @param array{images?: list<array{type?: string, en_alt?: string, de_alt?: string, rights_owner?: string, crop?: string, quality?: string}>} $media
     *
     * @return list<string>
     */
    public static function violations(array $media): array
    {
        $violations = [];
        $images = $media['images'] ?? [];

        if (\count($images) < self::MINIMUM_IMAGE_COUNT) {
            $violations[] = 'insufficient_image_count';
        }

        $hasPrimary = false;
        $crop = null;

        foreach ($images as $image) {
            $hasPrimary = $hasPrimary || (($image['type'] ?? '') === 'primary');

            foreach (['en_alt', 'de_alt', 'rights_owner', 'crop', 'quality'] as $field) {
                if (($image[$field] ?? '') === '') {
                    $violations[] = 'missing_' . $field;
                }
            }

            $crop ??= $image['crop'] ?? null;
            if ($crop !== null && ($image['crop'] ?? null) !== $crop) {
                $violations[] = 'inconsistent_crop';
            }

            if (($image['quality'] ?? '') !== 'approved') {
                $violations[] = 'image_quality_not_approved';
            }
        }

        if (!$hasPrimary) {
            $violations[] = 'missing_primary_image';
        }

        return \array_values(\array_unique($violations));
    }
}
