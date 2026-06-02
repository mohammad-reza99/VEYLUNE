<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class ContentQualityAuditHarness
{
    /**
     * @param array<string, string> $content
     *
     * @return list<string>
     */
    public static function violations(array $content): array
    {
        $violations = [];

        foreach (['en_title', 'de_title', 'en_description', 'de_description', 'en_seo_title', 'de_seo_title', 'en_meta_description', 'de_meta_description'] as $field) {
            if (($content[$field] ?? '') === '') {
                $violations[] = 'missing_' . $field;
            }
        }

        if (($content['en_title'] ?? '') !== '' && ($content['de_title'] ?? '') === '') {
            $violations[] = 'de_title_parity_missing';
        }

        if (($content['en_description'] ?? '') !== '' && ($content['de_description'] ?? '') === '') {
            $violations[] = 'de_description_parity_missing';
        }

        if (($content['de_title'] ?? '') !== '' && ($content['en_title'] ?? '') === '') {
            $violations[] = 'en_title_parity_missing';
        }

        if (($content['de_description'] ?? '') !== '' && ($content['en_description'] ?? '') === '') {
            $violations[] = 'en_description_parity_missing';
        }

        return \array_values(\array_unique($violations));
    }
}
