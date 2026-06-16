<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;

final class DraftCatalogSeeder
{
    private const ACQUISITION_ROOT_ID = '019e9e8f000070008000000000000012';
    private const STANDARD_TAX_ID = '019e3bf8f4bd7202abf2df3cb6a0bcd9';
    private const EUR_CURRENCY_ID = 'b7d2554b0ce847cd82f3ac9bd1c0dfca';

    /**
     * @param EntityRepository<\Shopware\Core\Content\Product\ProductCollection> $productRepository
     * @param EntityRepository<\Shopware\Core\Content\Category\CategoryCollection> $categoryRepository
     * @param EntityRepository<\Shopware\Core\Content\Property\PropertyGroupCollection> $propertyGroupRepository
     * @param EntityRepository<\Shopware\Core\System\CustomField\CustomFieldSetCollection> $customFieldSetRepository
     */
    public function __construct(
        private readonly EntityRepository $productRepository,
        private readonly EntityRepository $categoryRepository,
        private readonly EntityRepository $propertyGroupRepository,
        private readonly EntityRepository $customFieldSetRepository,
        private readonly Connection $connection,
        private readonly string $projectDir
    ) {
    }

    /**
     * @return array<string, int>
     */
    public function dryRun(): array
    {
        $content = $this->contentRecords();
        $products = DraftCatalogManifest::products();
        $violations = [];

        if (\count($products) !== 50) {
            $violations[] = 'manifest.product_count';
        }

        if (\count($content) !== 50) {
            $violations[] = 'content.product_count';
        }

        $skus = \array_column($products, 'sku');
        if (\count($skus) !== \count(\array_unique($skus))) {
            $violations[] = 'manifest.duplicate_sku';
        }

        $slugs = [];
        foreach ($products as $product) {
            if (!isset($content[$product['id']])) {
                $violations[] = 'content.missing.' . $product['id'];
                continue;
            }

            $slugs[] = $this->slugify($product['nameEn']);
            $slugs[] = $this->slugify($product['nameDe']);

            if (!isset(DraftCatalogManifest::departments()[$product['department']])) {
                $violations[] = 'department.invalid.' . $product['id'];
            }

            $type = DraftCatalogManifest::productTypes()[$product['productType']] ?? null;
            if ($type === null || $type['department'] !== $product['department']) {
                $violations[] = 'product_type.invalid.' . $product['id'];
            }

            foreach ($product['rooms'] as $room) {
                if (!isset(DraftCatalogManifest::rooms()[$room])) {
                    $violations[] = 'room.invalid.' . $product['id'] . '.' . $room;
                }
            }

            foreach ($product['collections'] as $collection) {
                if (!isset(DraftCatalogManifest::collections()[$collection])) {
                    $violations[] = 'collection.invalid.' . $product['id'] . '.' . $collection;
                }
            }

            foreach ([$product['primaryMaterial'], ...$product['secondaryMaterials']] as $material) {
                if (!isset(DraftCatalogManifest::materials()[$material])) {
                    $violations[] = 'material.invalid.' . $product['id'] . '.' . $material;
                }
            }
        }

        if (\count($slugs) !== \count(\array_unique($slugs))) {
            $violations[] = 'manifest.duplicate_slug';
        }

        $this->assertInfrastructure($violations);
        $this->assertSkuAvailability($products, $violations);
        $this->assertSlugAvailability($slugs, $violations);

        if ($violations !== []) {
            throw new \RuntimeException("Draft catalog dry run failed:\n- " . \implode("\n- ", \array_unique($violations)));
        }

        return [
            'products' => 50,
            'departments' => \count(DraftCatalogManifest::departments()),
            'productTypes' => \count(DraftCatalogManifest::productTypes()),
            'rooms' => \count(DraftCatalogManifest::rooms()),
            'collections' => \count(DraftCatalogManifest::collections()),
            'materials' => \count(DraftCatalogManifest::materials()),
            'comingSoon' => \count(\array_filter($content, static fn (array $record): bool => $record['status'] === 'Coming Soon')),
            'supplierSelection' => \count(\array_filter($content, static fn (array $record): bool => $record['status'] === 'Supplier Selection')),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function seed(): array
    {
        $this->dryRun();
        $context = Context::createDefaultContext();

        $this->upsertCustomFields($context);
        $categoryIds = $this->upsertCategories($context);
        $propertyIds = $this->upsertProperties($context);
        $content = $this->contentRecords();
        $payloads = [];

        foreach (DraftCatalogManifest::products() as $product) {
            $payloads[] = $this->productPayload($product, $content[$product['id']], $categoryIds, $propertyIds);
        }

        foreach (\array_chunk($payloads, 10) as $chunk) {
            $this->productRepository->upsert($chunk, $context);
        }

        $this->purgePublicIndexResidue();
        $audit = $this->audit();

        foreach ([
            'products' => 50,
            'active' => 0,
            'visibilities' => 0,
            'positiveStock' => 0,
            'seoUrls' => 0,
            'searchKeywords' => 0,
            'categoriesAssigned' => 50,
            'roomsAssigned' => 50,
            'collectionsAssigned' => 50,
            'comingSoon' => 4,
            'supplierSelection' => 46,
        ] as $metric => $expected) {
            if (($audit[$metric] ?? -1) !== $expected) {
                throw new \RuntimeException(\sprintf('Post-seed audit failed for %s: expected %d, got %d.', $metric, $expected, $audit[$metric] ?? -1));
            }
        }

        return $audit;
    }

    /**
     * @return array<string, int>
     */
    public function audit(): array
    {
        $batch = DraftCatalogManifest::BATCH_ID;
        $languageId = Uuid::fromHexToBytes((string) $this->languageId('en-GB'));
        $productJoin = ' INNER JOIN product_translation pt ON pt.product_id = p.id AND pt.product_version_id = p.version_id AND pt.language_id = :languageId ';
        $productCondition = "JSON_UNQUOTE(JSON_EXTRACT(pt.custom_fields, '$.veylune_source_batch')) = :batch";
        $parameters = ['batch' => $batch, 'languageId' => $languageId];

        return [
            'products' => (int) $this->connection->fetchOne("SELECT COUNT(*) FROM product p {$productJoin} WHERE {$productCondition}", $parameters),
            'active' => (int) $this->connection->fetchOne("SELECT COUNT(*) FROM product p {$productJoin} WHERE {$productCondition} AND p.active = 1", $parameters),
            'visibilities' => (int) $this->connection->fetchOne("SELECT COUNT(*) FROM product_visibility pv INNER JOIN product p ON p.id = pv.product_id AND p.version_id = pv.product_version_id {$productJoin} WHERE {$productCondition}", $parameters),
            'positiveStock' => (int) $this->connection->fetchOne("SELECT COUNT(*) FROM product p {$productJoin} WHERE {$productCondition} AND (p.stock > 0 OR p.available_stock > 0)", $parameters),
            'seoUrls' => (int) $this->connection->fetchOne("SELECT COUNT(*) FROM seo_url su INNER JOIN product p ON p.id = su.foreign_key {$productJoin} WHERE {$productCondition} AND su.route_name = 'frontend.detail.page'", $parameters),
            'searchKeywords' => (int) $this->connection->fetchOne("SELECT COUNT(*) FROM product_search_keyword psk INNER JOIN product p ON p.id = psk.product_id AND p.version_id = psk.version_id {$productJoin} WHERE {$productCondition}", $parameters),
            'categoriesAssigned' => (int) $this->connection->fetchOne("SELECT COUNT(DISTINCT p.id) FROM product p INNER JOIN product_category pc ON pc.product_id = p.id AND pc.product_version_id = p.version_id {$productJoin} WHERE {$productCondition}", $parameters),
            'roomsAssigned' => $this->countProductsWithPropertyGroup('Veylune Room'),
            'collectionsAssigned' => $this->countProductsWithPropertyGroup('Veylune Collection'),
            'comingSoon' => (int) $this->connection->fetchOne("SELECT COUNT(*) FROM product p {$productJoin} WHERE {$productCondition} AND JSON_UNQUOTE(JSON_EXTRACT(pt.custom_fields, '$.veylune_status_copy')) = 'Coming Soon'", $parameters),
            'supplierSelection' => (int) $this->connection->fetchOne("SELECT COUNT(*) FROM product p {$productJoin} WHERE {$productCondition} AND JSON_UNQUOTE(JSON_EXTRACT(pt.custom_fields, '$.veylune_status_copy')) = 'Supplier Selection'", $parameters),
            'departmentCategories' => $this->countSeedCategories('department'),
            'productTypeCategories' => $this->countSeedCategories('product_type'),
            'collectionOptions' => $this->countPropertyOptions('Veylune Collection'),
            'roomOptions' => $this->countPropertyOptions('Veylune Room'),
            'materialOptions' => $this->countPropertyOptions('Veylune Material'),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function rollback(): array
    {
        $context = Context::createDefaultContext();
        $criteria = (new Criteria())->addFilter(new EqualsFilter('customFields.veylune_source_batch', DraftCatalogManifest::BATCH_ID));
        $products = $this->productRepository->searchIds($criteria, $context)->getIds();

        if ($products !== []) {
            $states = $this->connection->fetchFirstColumn(
                "SELECT DISTINCT JSON_UNQUOTE(JSON_EXTRACT(pt.custom_fields, '$.veylune_publication_state'))
                 FROM product_translation pt
                 WHERE pt.product_id IN (:ids) AND pt.language_id = :languageId",
                [
                    'ids' => Uuid::fromHexToBytesList($products),
                    'languageId' => Uuid::fromHexToBytes((string) $this->languageId('en-GB')),
                ],
                ['ids' => ArrayParameterType::BINARY]
            );

            if (\array_values(\array_filter($states, static fn (mixed $state): bool => $state !== 'draft')) !== []) {
                throw new \RuntimeException('Rollback refused because at least one seeded product is no longer draft.');
            }

            $this->productRepository->delete(\array_map(static fn (string $id): array => ['id' => $id], $products), $context);
        }

        $categoryIds = [];
        foreach (\array_keys(DraftCatalogManifest::productTypes()) as $key) {
            $categoryIds[] = ['id' => $this->id('category:product_type:' . $key)];
        }
        foreach (\array_keys(DraftCatalogManifest::departments()) as $key) {
            $categoryIds[] = ['id' => $this->id('category:department:' . $key)];
        }
        $this->categoryRepository->delete($categoryIds, $context);

        $this->propertyGroupRepository->delete([
            ['id' => $this->id('property_group:material')],
            ['id' => $this->id('property_group:room')],
            ['id' => $this->id('property_group:collection')],
        ], $context);

        $this->customFieldSetRepository->delete([['id' => $this->id('custom_field_set:catalog_governance')]], $context);

        return $this->audit();
    }

    /**
     * @param list<string> $violations
     */
    private function assertInfrastructure(array &$violations): void
    {
        foreach ([
            'acquisition_root' => ['category', self::ACQUISITION_ROOT_ID],
            'standard_tax' => ['tax', self::STANDARD_TAX_ID],
            'eur_currency' => ['currency', self::EUR_CURRENCY_ID],
        ] as $key => [$table, $id]) {
            $exists = (int) $this->connection->fetchOne("SELECT COUNT(*) FROM `{$table}` WHERE id = :id", ['id' => Uuid::fromHexToBytes($id)]);
            if ($exists !== 1) {
                $violations[] = 'infrastructure.' . $key;
            }
        }

        foreach (['en-GB', 'de-DE'] as $locale) {
            if ($this->languageId($locale) === null) {
                $violations[] = 'language.' . $locale;
            }
        }
    }

    /**
     * @param list<array<string, mixed>> $products
     * @param list<string> $violations
     */
    private function assertSkuAvailability(array $products, array &$violations): void
    {
        $skus = \array_column($products, 'sku');
        $rows = $this->connection->fetchAllAssociative(
            'SELECT HEX(p.id) AS id, p.product_number, pt.custom_fields
             FROM product p
             LEFT JOIN product_translation pt
                ON pt.product_id = p.id AND pt.product_version_id = p.version_id AND pt.language_id = :languageId
             WHERE p.product_number IN (:skus)',
            [
                'skus' => $skus,
                'languageId' => Uuid::fromHexToBytes((string) $this->languageId('en-GB')),
            ],
            ['skus' => ArrayParameterType::STRING]
        );

        foreach ($rows as $row) {
            $fields = \json_decode((string) ($row['custom_fields'] ?? '{}'), true);
            $expectedId = $this->id('product:' . $row['product_number']);
            if (\strtolower((string) $row['id']) !== $expectedId || ($fields['veylune_source_batch'] ?? null) !== DraftCatalogManifest::BATCH_ID) {
                $violations[] = 'sku.conflict.' . $row['product_number'];
            }
        }
    }

    /**
     * @param list<string> $slugs
     * @param list<string> $violations
     */
    private function assertSlugAvailability(array $slugs, array &$violations): void
    {
        $rows = $this->connection->fetchAllAssociative(
            <<<'SQL'
                SELECT p.product_number,
                       JSON_UNQUOTE(JSON_EXTRACT(pt.custom_fields, '$.veylune_reserved_slug_en')) AS slug_en,
                       JSON_UNQUOTE(JSON_EXTRACT(pt.custom_fields, '$.veylune_reserved_slug_de')) AS slug_de,
                       JSON_UNQUOTE(JSON_EXTRACT(pt.custom_fields, '$.veylune_source_batch')) AS source_batch
                FROM product p
                INNER JOIN product_translation pt
                    ON pt.product_id = p.id AND pt.product_version_id = p.version_id AND pt.language_id = :languageId
                WHERE JSON_UNQUOTE(JSON_EXTRACT(pt.custom_fields, '$.veylune_reserved_slug_en')) IN (:slugs)
                   OR JSON_UNQUOTE(JSON_EXTRACT(pt.custom_fields, '$.veylune_reserved_slug_de')) IN (:slugs)
            SQL,
            [
                'slugs' => $slugs,
                'languageId' => Uuid::fromHexToBytes((string) $this->languageId('en-GB')),
            ],
            ['slugs' => ArrayParameterType::STRING]
        );

        foreach ($rows as $row) {
            if ($row['source_batch'] !== DraftCatalogManifest::BATCH_ID) {
                $violations[] = 'slug.conflict.' . ($row['slug_en'] ?: $row['slug_de']);
            }
        }
    }

    private function upsertCustomFields(Context $context): void
    {
        $fields = [];
        $position = 1;
        foreach ($this->customFieldDefinitions() as $name => $type) {
            $fields[] = [
                'id' => $this->id('custom_field:' . $name),
                'name' => $name,
                'type' => $type,
                'active' => true,
                'allowCustomerWrite' => false,
                'allowCartExpose' => false,
                'storeApiAware' => false,
                'includeInSearch' => false,
                'config' => [
                    'label' => ['en-GB' => $this->humanize($name), 'de-DE' => $this->humanize($name)],
                    'componentName' => $type === 'bool' ? 'sw-switch-field' : 'sw-text-field',
                    'customFieldType' => $type === 'bool' ? 'checkbox' : 'text',
                    'customFieldPosition' => $position++,
                ],
            ];
        }

        $this->customFieldSetRepository->upsert([[
            'id' => $this->id('custom_field_set:catalog_governance'),
            'name' => 'veylune_catalog_governance',
            'active' => true,
            'global' => false,
            'position' => 3,
            'config' => [
                'label' => ['en-GB' => 'Veylune Catalog Governance', 'de-DE' => 'Veylune Katalog-Governance'],
                'translated' => false,
            ],
            'relations' => [[
                'id' => $this->id('custom_field_set_relation:catalog_governance:product'),
                'entityName' => 'product',
            ]],
            'customFields' => $fields,
        ]], $context);
    }

    /**
     * @return array<string, string>
     */
    private function upsertCategories(Context $context): array
    {
        $ids = [];
        $payloads = [];
        foreach (DraftCatalogManifest::departments() as $key => $labels) {
            $id = $this->id('category:department:' . $key);
            $ids['department:' . $key] = $id;
            $payloads[] = $this->categoryPayload($id, self::ACQUISITION_ROOT_ID, $labels, $key, 'department');
        }
        $this->categoryRepository->upsert($payloads, $context);

        $payloads = [];
        foreach (DraftCatalogManifest::productTypes() as $key => $definition) {
            $id = $this->id('category:product_type:' . $key);
            $ids['product_type:' . $key] = $id;
            $payloads[] = $this->categoryPayload(
                $id,
                $ids['department:' . $definition['department']],
                ['en' => $definition['en'], 'de' => $definition['de']],
                $key,
                'product_type'
            );
        }
        foreach (\array_chunk($payloads, 10) as $chunk) {
            $this->categoryRepository->upsert($chunk, $context);
        }

        return $ids;
    }

    /**
     * @return array<string, string>
     */
    private function upsertProperties(Context $context): array
    {
        $ids = [];
        $groups = [
            'material' => ['name' => ['en' => 'Veylune Material', 'de' => 'Veylune Material'], 'options' => DraftCatalogManifest::materials()],
            'room' => ['name' => ['en' => 'Veylune Room', 'de' => 'Veylune Raum'], 'options' => DraftCatalogManifest::rooms()],
            'collection' => ['name' => ['en' => 'Veylune Collection', 'de' => 'Veylune Kollektion'], 'options' => DraftCatalogManifest::collections()],
        ];

        $payloads = [];
        foreach ($groups as $groupKey => $group) {
            $groupId = $this->id('property_group:' . $groupKey);
            $options = [];
            foreach ($group['options'] as $key => $labels) {
                $optionId = $this->id('property_option:' . $groupKey . ':' . $key);
                $ids[$groupKey . ':' . $key] = $optionId;
                $options[] = [
                    'id' => $optionId,
                    'translations' => $this->translations($labels['en'], $labels['de']),
                    'customFields' => [
                        'veylune_canonical_key' => $key,
                        'veylune_source_batch' => DraftCatalogManifest::BATCH_ID,
                    ],
                ];
            }

            $payloads[] = [
                'id' => $groupId,
                'displayType' => 'text',
                'sortingType' => 'position',
                'filterable' => false,
                'visibleOnProductDetailPage' => false,
                'translations' => $this->translations($group['name']['en'], $group['name']['de']),
                'options' => $options,
            ];
        }

        $this->propertyGroupRepository->upsert($payloads, $context);

        return $ids;
    }

    /**
     * @param array<string, mixed> $product
     * @param array<string, mixed> $content
     * @param array<string, string> $categoryIds
     * @param array<string, string> $propertyIds
     *
     * @return array<string, mixed>
     */
    private function productPayload(array $product, array $content, array $categoryIds, array $propertyIds): array
    {
        $properties = [];
        foreach ([$product['primaryMaterial'], ...$product['secondaryMaterials']] as $material) {
            $properties[] = ['id' => $propertyIds['material:' . $material]];
        }
        foreach ($product['rooms'] as $room) {
            $properties[] = ['id' => $propertyIds['room:' . $room]];
        }
        foreach ($product['collections'] as $collection) {
            $properties[] = ['id' => $propertyIds['collection:' . $collection]];
        }

        $gross = (float) $product['price'];
        $net = \round($gross / 1.19, 2);

        return [
            'id' => $this->id('product:' . $product['sku']),
            'productNumber' => $product['sku'],
            'active' => false,
            'stock' => 0,
            'isCloseout' => true,
            'purchaseSteps' => 1,
            'minPurchase' => 1,
            'shippingFree' => false,
            'markAsTopseller' => false,
            'taxId' => self::STANDARD_TAX_ID,
            'price' => [[
                'currencyId' => self::EUR_CURRENCY_ID,
                'gross' => $gross,
                'net' => $net,
                'linked' => true,
            ]],
            'translations' => $this->productTranslations($product, $content),
            'categories' => [
                ['id' => $categoryIds['department:' . $product['department']]],
                ['id' => $categoryIds['product_type:' . $product['productType']]],
            ],
            'properties' => \array_values(\array_unique($properties, \SORT_REGULAR)),
            'visibilities' => [],
            'customFields' => [
                'veylune_catalog_record_id' => $product['id'],
                'veylune_publication_state' => 'draft',
                'veylune_readiness_level' => 'L0',
                'veylune_status_copy' => $content['status'],
                'veylune_source_batch' => DraftCatalogManifest::BATCH_ID,
                'veylune_record_owner' => 'product_governance',
                'veylune_rollback_target' => 'delete_wp_cat_04_draft_batch',
                'veylune_reserved_slug_en' => $this->slugify($product['nameEn']),
                'veylune_reserved_slug_de' => $this->slugify($product['nameDe']),
                'veylune_department_key' => $product['department'],
                'veylune_product_type_key' => $product['productType'],
                'veylune_primary_material_key' => $product['primaryMaterial'],
                'veylune_secondary_material_keys' => \json_encode($product['secondaryMaterials'], \JSON_THROW_ON_ERROR),
                'veylune_room_relationships' => $this->relationships($product['rooms'], 'planning_inferred'),
                'veylune_collection_relationships' => $this->relationships($product['collections'], 'candidate'),
                'veylune_consultation_mode' => $product['consultation'],
                'veylune_rail_candidates' => \json_encode($content['rails'], \JSON_THROW_ON_ERROR),
                'veylune_founder_potential' => $content['founder'],
                'veylune_target_price_gross' => $gross,
                'veylune_price_status' => 'target_unapproved',
                'veylune_sellability_status' => 'not_sellable',
                'veylune_availability_status' => 'pending_acquisition',
                'veylune_exposure_status' => 'not_approved',
                'veylune_search_index_state' => 'excluded',
                'veylune_storefront_activation_state' => 'blocked',
                'veylune_commerce_activation_state' => 'blocked',
                'veylune_material_story_draft' => $content['materialStory'],
                'veylune_feature_drafts' => \json_encode($content['features'], \JSON_THROW_ON_ERROR),
                'veylune_primary_image_direction' => $content['primaryImage'],
                'veylune_detail_image_direction' => $content['detailImage'],
                'veylune_context_image_direction' => $content['contextImage'],
                'veylune_content_source' => 'WP-CAT-03',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $product
     * @param array<string, mixed> $content
     *
     * @return array<string, array<string, mixed>>
     */
    private function productTranslations(array $product, array $content): array
    {
        $en = $this->languageId('en-GB');
        $de = $this->languageId('de-DE');
        \assert($en !== null && $de !== null);

        return [
            $en => [
                'name' => $product['nameEn'],
                'description' => $content['description'],
                'metaTitle' => null,
                'metaDescription' => null,
                'keywords' => null,
                'customSearchKeywords' => [],
            ],
            $de => [
                'name' => $product['nameDe'],
                'description' => null,
                'metaTitle' => null,
                'metaDescription' => null,
                'keywords' => null,
                'customSearchKeywords' => [],
            ],
        ];
    }

    /**
     * @param array{en: string, de: string} $labels
     *
     * @return array<string, mixed>
     */
    private function categoryPayload(string $id, string $parentId, array $labels, string $key, string $layer): array
    {
        return [
            'id' => $id,
            'parentId' => $parentId,
            'active' => false,
            'visible' => false,
            'type' => 'page',
            'translations' => $this->translations($labels['en'], $labels['de']),
            'customFields' => [
                'veylune_canonical_key' => $key,
                'veylune_taxonomy_layer' => $layer,
                'veylune_publication_state' => 'draft',
                'veylune_source_batch' => DraftCatalogManifest::BATCH_ID,
            ],
        ];
    }

    /**
     * @return array<string, array{name: string}>
     */
    private function translations(string $en, string $de): array
    {
        $enId = $this->languageId('en-GB');
        $deId = $this->languageId('de-DE');
        \assert($enId !== null && $deId !== null);

        return [$enId => ['name' => $en], $deId => ['name' => $de]];
    }

    /**
     * @return array<string, array{
     *     description: string,
     *     materialStory: string,
     *     features: list<string>,
     *     primaryImage: string,
     *     detailImage: string,
     *     contextImage: string,
     *     status: string,
     *     rails: list<string>,
     *     founder: string
     * }>
     */
    private function contentRecords(): array
    {
        $path = $this->projectDir . '/docs/veylune-wp-cat-03-product-content-visual-asset-direction.md';
        $markdown = \file_get_contents($path);
        if ($markdown === false) {
            throw new \RuntimeException('Unable to read WP-CAT-03 content source.');
        }

        \preg_match_all('/^## ([FLDTKO]\d{2}) — .*?(?=^## [FLDTKO]\d{2} —|^---$|^# G\. Production Control Summary)/msu', $markdown, $matches, \PREG_SET_ORDER);
        $records = [];

        foreach ($matches as $match) {
            $block = $match[0];
            $records[$match[1]] = [
                'description' => $this->capture($block, '/^\*\*Commerce description:\*\* (.+?)  $/mu'),
                'materialStory' => $this->capture($block, '/^\*\*Material story:\*\* (.+?)  $/mu'),
                'features' => $this->captureList($block, '/^\*\*Key features:\*\*\R\R(.+?)\R\R\*\*Image direction:/msu'),
                'primaryImage' => $this->capture($block, '/^- \*\*Primary:\*\* (.+)$/mu'),
                'detailImage' => $this->capture($block, '/^- \*\*Detail:\*\* (.+)$/mu'),
                'contextImage' => $this->capture($block, '/^- \*\*Scale\/context:\*\* (.+)$/mu'),
                'status' => $this->capture($block, '/^\*\*Status copy:\*\* (.+?)  $/mu'),
                'rails' => \array_map('trim', \explode(';', $this->capture($block, '/^\*\*Suggested rail placement:\*\* (.+?)  $/mu'))),
                'founder' => \strtok($this->capture($block, '/^\*\*Founder Selection potential:\*\* (.+)$/mu'), ' —') ?: 'Low',
            ];
        }

        return $records;
    }

    private function capture(string $block, string $pattern): string
    {
        if (\preg_match($pattern, $block, $match) !== 1) {
            throw new \RuntimeException('WP-CAT-03 content block is missing a required field.');
        }

        return \trim($match[1]);
    }

    /**
     * @return list<string>
     */
    private function captureList(string $block, string $pattern): array
    {
        $value = $this->capture($block, $pattern);
        $items = [];
        foreach (\preg_split('/\R/u', $value) ?: [] as $line) {
            if (\str_starts_with($line, '- ')) {
                $items[] = \substr($line, 2);
            }
        }

        if (\count($items) !== 3) {
            throw new \RuntimeException('WP-CAT-03 product must define exactly three key features.');
        }

        return $items;
    }

    private function purgePublicIndexResidue(): void
    {
        $ids = $this->batchProductBinaryIds();
        if ($ids === []) {
            return;
        }

        $this->connection->executeStatement(
            'DELETE FROM product_search_keyword WHERE product_id IN (:ids)',
            ['ids' => $ids],
            ['ids' => ArrayParameterType::BINARY]
        );
        $this->connection->executeStatement(
            "DELETE FROM seo_url WHERE route_name = 'frontend.detail.page' AND foreign_key IN (:ids)",
            ['ids' => $ids],
            ['ids' => ArrayParameterType::BINARY]
        );
    }

    /**
     * @return list<string>
     */
    private function batchProductBinaryIds(): array
    {
        return $this->connection->fetchFirstColumn(
            "SELECT p.id
             FROM product p
             INNER JOIN product_translation pt
                ON pt.product_id = p.id AND pt.product_version_id = p.version_id AND pt.language_id = :languageId
             WHERE JSON_UNQUOTE(JSON_EXTRACT(pt.custom_fields, '$.veylune_source_batch')) = :batch",
            [
                'batch' => DraftCatalogManifest::BATCH_ID,
                'languageId' => Uuid::fromHexToBytes((string) $this->languageId('en-GB')),
            ]
        );
    }

    private function countProductsWithPropertyGroup(string $groupName): int
    {
        return (int) $this->connection->fetchOne(
            <<<'SQL'
                SELECT COUNT(DISTINCT p.id)
                FROM product p
                INNER JOIN product_property pp ON pp.product_id = p.id AND pp.product_version_id = p.version_id
                INNER JOIN property_group_option pgo ON pgo.id = pp.property_group_option_id
                INNER JOIN property_group pg ON pg.id = pgo.property_group_id
                INNER JOIN property_group_translation pgt ON pgt.property_group_id = pg.id
                INNER JOIN language l ON l.id = pgt.language_id
                INNER JOIN locale lo ON lo.id = l.locale_id AND lo.code = 'en-GB'
                INNER JOIN product_translation pt
                    ON pt.product_id = p.id AND pt.product_version_id = p.version_id
                INNER JOIN language pl ON pl.id = pt.language_id
                INNER JOIN locale plo ON plo.id = pl.locale_id AND plo.code = 'en-GB'
                WHERE JSON_UNQUOTE(JSON_EXTRACT(pt.custom_fields, '$.veylune_source_batch')) = :batch
                  AND pgt.name = :groupName
            SQL,
            ['batch' => DraftCatalogManifest::BATCH_ID, 'groupName' => $groupName]
        );
    }

    private function countSeedCategories(string $layer): int
    {
        return (int) $this->connection->fetchOne(
            "SELECT COUNT(*)
             FROM category c
             INNER JOIN category_translation ct
                ON ct.category_id = c.id AND ct.category_version_id = c.version_id AND ct.language_id = :languageId
             WHERE JSON_UNQUOTE(JSON_EXTRACT(ct.custom_fields, '$.veylune_source_batch')) = :batch
               AND JSON_UNQUOTE(JSON_EXTRACT(ct.custom_fields, '$.veylune_taxonomy_layer')) = :layer",
            [
                'batch' => DraftCatalogManifest::BATCH_ID,
                'layer' => $layer,
                'languageId' => Uuid::fromHexToBytes((string) $this->languageId('en-GB')),
            ]
        );
    }

    private function countPropertyOptions(string $groupName): int
    {
        return (int) $this->connection->fetchOne(
            <<<'SQL'
                SELECT COUNT(*)
                FROM property_group_option pgo
                INNER JOIN property_group pg ON pg.id = pgo.property_group_id
                INNER JOIN property_group_translation pgt ON pgt.property_group_id = pg.id
                INNER JOIN language l ON l.id = pgt.language_id
                INNER JOIN locale lo ON lo.id = l.locale_id AND lo.code = 'en-GB'
                WHERE pgt.name = :groupName
            SQL,
            ['groupName' => $groupName]
        );
    }

    private function languageId(string $locale): ?string
    {
        $id = $this->connection->fetchOne(
            'SELECT HEX(l.id) FROM language l INNER JOIN locale lo ON lo.id = l.locale_id WHERE lo.code = :locale',
            ['locale' => $locale]
        );

        return \is_string($id) && $id !== '' ? \strtolower($id) : null;
    }

    /**
     * @param list<string> $keys
     */
    private function relationships(array $keys, string $confidence): string
    {
        return \json_encode(\array_map(static fn (string $key): array => [
            'key' => $key,
            'status' => 'candidate',
            'confidence' => $confidence,
            'source' => 'WP-CAT-02',
            'exposureApproved' => false,
        ], $keys), \JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, string>
     */
    private function customFieldDefinitions(): array
    {
        return [
            'veylune_catalog_record_id' => 'text',
            'veylune_publication_state' => 'text',
            'veylune_readiness_level' => 'text',
            'veylune_status_copy' => 'text',
            'veylune_source_batch' => 'text',
            'veylune_record_owner' => 'text',
            'veylune_rollback_target' => 'text',
            'veylune_reserved_slug_en' => 'text',
            'veylune_reserved_slug_de' => 'text',
            'veylune_department_key' => 'text',
            'veylune_product_type_key' => 'text',
            'veylune_primary_material_key' => 'text',
            'veylune_secondary_material_keys' => 'text',
            'veylune_room_relationships' => 'text',
            'veylune_collection_relationships' => 'text',
            'veylune_consultation_mode' => 'text',
            'veylune_rail_candidates' => 'text',
            'veylune_founder_potential' => 'text',
            'veylune_target_price_gross' => 'float',
            'veylune_price_status' => 'text',
            'veylune_sellability_status' => 'text',
            'veylune_availability_status' => 'text',
            'veylune_exposure_status' => 'text',
            'veylune_search_index_state' => 'text',
            'veylune_storefront_activation_state' => 'text',
            'veylune_commerce_activation_state' => 'text',
            'veylune_material_story_draft' => 'text',
            'veylune_feature_drafts' => 'text',
            'veylune_primary_image_direction' => 'text',
            'veylune_detail_image_direction' => 'text',
            'veylune_context_image_direction' => 'text',
            'veylune_content_source' => 'text',
        ];
    }

    private function slugify(string $value): string
    {
        $ascii = \iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $ascii = $ascii === false ? $value : $ascii;
        $slug = \strtolower((string) \preg_replace('/[^a-zA-Z0-9]+/', '-', $ascii));

        return \trim($slug, '-');
    }

    private function humanize(string $value): string
    {
        return \ucwords(\str_replace('_', ' ', \preg_replace('/^veylune_/', '', $value) ?? $value));
    }

    private function id(string $key): string
    {
        return Uuid::fromStringToHex('veylune:' . DraftCatalogManifest::BATCH_ID . ':' . $key);
    }
}
