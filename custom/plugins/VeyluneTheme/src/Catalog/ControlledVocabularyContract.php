<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class ControlledVocabularyContract
{
    private const VALUES = [
        PropertyDictionaryGovernanceContract::DICTIONARY_MATERIAL => [
            'ceramic' => ['en' => 'Ceramic', 'de' => 'Keramik'],
            'glass' => ['en' => 'Glass', 'de' => 'Glas'],
            'leather' => ['en' => 'Leather', 'de' => 'Leder'],
            'marble' => ['en' => 'Marble', 'de' => 'Marmor'],
            'metal' => ['en' => 'Metal', 'de' => 'Metall'],
            'stone' => ['en' => 'Stone', 'de' => 'Stein'],
            'travertine' => ['en' => 'Travertine', 'de' => 'Travertin'],
            'upholstery_fabric' => ['en' => 'Upholstery Fabric', 'de' => 'Polsterstoff'],
            'wood' => ['en' => 'Wood', 'de' => 'Holz'],
            'wool' => ['en' => 'Wool', 'de' => 'Wolle'],
        ],
        PropertyDictionaryGovernanceContract::DICTIONARY_FINISH => [
            'brushed' => ['en' => 'Brushed', 'de' => 'Gebuerstet'],
            'honed' => ['en' => 'Honed', 'de' => 'Geschliffen'],
            'lacquered' => ['en' => 'Lacquered', 'de' => 'Lackiert'],
            'matte' => ['en' => 'Matte', 'de' => 'Matt'],
            'natural' => ['en' => 'Natural', 'de' => 'Natur'],
            'oiled' => ['en' => 'Oiled', 'de' => 'Geoelt'],
            'polished' => ['en' => 'Polished', 'de' => 'Poliert'],
        ],
        PropertyDictionaryGovernanceContract::DICTIONARY_COLOR => [
            'beige' => ['en' => 'Beige', 'de' => 'Beige'],
            'black' => ['en' => 'Black', 'de' => 'Schwarz'],
            'blue' => ['en' => 'Blue', 'de' => 'Blau'],
            'brown' => ['en' => 'Brown', 'de' => 'Braun'],
            'green' => ['en' => 'Green', 'de' => 'Gruen'],
            'grey' => ['en' => 'Grey', 'de' => 'Grau'],
            'stone' => ['en' => 'Stone', 'de' => 'Stein'],
            'warm_neutral' => ['en' => 'Warm Neutral', 'de' => 'Warm Neutral'],
            'white' => ['en' => 'White', 'de' => 'Weiss'],
        ],
        PropertyDictionaryGovernanceContract::DICTIONARY_ROOM => [
            'bedroom' => ['en' => 'Bedroom', 'de' => 'Schlafzimmer'],
            'dining_room' => ['en' => 'Dining Room', 'de' => 'Esszimmer'],
            'hallway' => ['en' => 'Hallway', 'de' => 'Flur'],
            'home_office' => ['en' => 'Home Office', 'de' => 'Arbeitszimmer'],
            'living_room' => ['en' => 'Living Room', 'de' => 'Wohnzimmer'],
            'outdoor' => ['en' => 'Outdoor', 'de' => 'Outdoor'],
        ],
        PropertyDictionaryGovernanceContract::DICTIONARY_STYLE => [
            'contemporary' => ['en' => 'Contemporary', 'de' => 'Zeitgenoessisch'],
            'minimal' => ['en' => 'Minimal', 'de' => 'Minimal'],
            'modern_classic' => ['en' => 'Modern Classic', 'de' => 'Modern Klassisch'],
            'organic' => ['en' => 'Organic', 'de' => 'Organisch'],
            'sculptural' => ['en' => 'Sculptural', 'de' => 'Skulptural'],
        ],
    ];

    private const SUPPLIER_ALIASES = [
        PropertyDictionaryGovernanceContract::DICTIONARY_MATERIAL => [
            'fabric upholstery' => 'upholstery_fabric',
            'natural stone' => 'stone',
        ],
        PropertyDictionaryGovernanceContract::DICTIONARY_COLOR => [
            'gray' => 'grey',
            'off white' => 'warm_neutral',
        ],
        PropertyDictionaryGovernanceContract::DICTIONARY_ROOM => [
            'office' => 'home_office',
        ],
    ];

    /**
     * @return array<string, array<string, array{en: string, de: string}>>
     */
    public static function values(): array
    {
        return self::VALUES;
    }

    /**
     * @return array<string, array{en: string, de: string}>
     */
    public static function valuesForDictionary(string $dictionary): array
    {
        return self::VALUES[$dictionary] ?? [];
    }

    public static function normalizeSupplierValue(string $dictionary, string $value): ?string
    {
        $normalized = \strtolower(\trim($value));
        $normalized = \preg_replace('/[^a-z0-9]+/', '_', $normalized) ?? '';
        $normalized = \trim($normalized, '_');
        $aliasKey = \str_replace('_', ' ', $normalized);

        if (isset(self::SUPPLIER_ALIASES[$dictionary][$aliasKey])) {
            return self::SUPPLIER_ALIASES[$dictionary][$aliasKey];
        }

        return isset(self::VALUES[$dictionary][$normalized]) ? $normalized : null;
    }

    public static function hasLocaleParity(string $dictionary, string $value): bool
    {
        $labels = self::VALUES[$dictionary][$value] ?? null;

        return isset($labels['en'], $labels['de'])
            && $labels['en'] !== ''
            && $labels['de'] !== '';
    }
}
