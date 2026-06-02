<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class PropertyDictionaryGovernanceContract
{
    public const DICTIONARY_MATERIAL = 'material';
    public const DICTIONARY_FINISH = 'finish';
    public const DICTIONARY_COLOR = 'color';
    public const DICTIONARY_ROOM = 'room';
    public const DICTIONARY_STYLE = 'style';
    public const DICTIONARY_COLLECTION = 'collection';

    public const OWNER_PROPERTY_DICTIONARY_GOVERNANCE = 'property_dictionary_governance';
    public const OWNER_COLLECTION_GOVERNANCE = 'collection_governance';

    private const DICTIONARIES = [
        self::DICTIONARY_MATERIAL => [
            'owner' => self::OWNER_PROPERTY_DICTIONARY_GOVERNANCE,
            'value_source' => 'controlled_vocabulary',
            'cardinality' => 'multiple',
            'facet_candidate' => true,
        ],
        self::DICTIONARY_FINISH => [
            'owner' => self::OWNER_PROPERTY_DICTIONARY_GOVERNANCE,
            'value_source' => 'controlled_vocabulary',
            'cardinality' => 'multiple',
            'facet_candidate' => true,
        ],
        self::DICTIONARY_COLOR => [
            'owner' => self::OWNER_PROPERTY_DICTIONARY_GOVERNANCE,
            'value_source' => 'controlled_vocabulary',
            'cardinality' => 'multiple',
            'facet_candidate' => true,
        ],
        self::DICTIONARY_ROOM => [
            'owner' => self::OWNER_PROPERTY_DICTIONARY_GOVERNANCE,
            'value_source' => 'controlled_vocabulary',
            'cardinality' => 'multiple',
            'facet_candidate' => true,
        ],
        self::DICTIONARY_STYLE => [
            'owner' => self::OWNER_PROPERTY_DICTIONARY_GOVERNANCE,
            'value_source' => 'controlled_vocabulary',
            'cardinality' => 'multiple',
            'facet_candidate' => true,
        ],
        self::DICTIONARY_COLLECTION => [
            'owner' => self::OWNER_COLLECTION_GOVERNANCE,
            'value_source' => 'governed_collection_registry',
            'cardinality' => 'multiple',
            'facet_candidate' => true,
        ],
    ];

    /**
     * @return array<string, array{owner: string, value_source: string, cardinality: string, facet_candidate: bool}>
     */
    public static function dictionaries(): array
    {
        return self::DICTIONARIES;
    }

    /**
     * @return array{owner: string, value_source: string, cardinality: string, facet_candidate: bool}|null
     */
    public static function governanceForDictionary(string $dictionary): ?array
    {
        return self::DICTIONARIES[$dictionary] ?? null;
    }
}
