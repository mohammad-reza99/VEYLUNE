<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;

final class CatalogProductProjector
{
    private const EUR_CURRENCY_ID = 'b7d2554b0ce847cd82f3ac9bd1c0dfca';

    /**
     * @return array<string, mixed>
     */
    public function project(ProductEntity $product, bool $public): array
    {
        $translatedCustomFields = $product->getTranslated()['customFields'] ?? null;
        $customFields = \is_array($translatedCustomFields)
            ? \array_merge($product->getCustomFields() ?? [], $translatedCustomFields)
            : ($product->getCustomFields() ?? []);
        $recordId = $this->recordId($product, $customFields);
        $manifest = $this->manifestProduct($recordId);
        $primaryMaterial = (string) ($customFields['veylune_primary_material_key'] ?? $manifest['primaryMaterial'] ?? 'material');
        $materials = [];
        $rooms = [];
        $collections = [];

        foreach ($product->getProperties() ?? [] as $property) {
            $groupName = $property->getGroup()?->getTranslated()['name'] ?? $property->getGroup()?->getName();
            $key = $property->getCustomFieldsValue('veylune_canonical_key');

            if (!\is_string($key)) {
                continue;
            }

            if ($groupName === 'Veylune Material') {
                $materials[] = $key;
            } elseif ($groupName === 'Veylune Room') {
                $rooms[] = $key;
            } elseif ($groupName === 'Veylune Collection') {
                $collections[] = $key;
            }
        }

        if ($materials === [] && $manifest !== null) {
            $materials = \array_values(\array_unique([
                $manifest['primaryMaterial'],
                ...$manifest['secondaryMaterials'],
            ]));
        }
        if ($rooms === [] && $manifest !== null) {
            $rooms = $manifest['rooms'];
        }
        if ($collections === [] && $manifest !== null) {
            $collections = $manifest['collections'];
        }

        $coverMedia = $product->getCover()?->getMedia();
        $media = [];
        foreach ($product->getMedia() ?? [] as $productMedia) {
            $mediaEntity = $productMedia->getMedia();
            if ($mediaEntity === null || $mediaEntity->getUrl() === null) {
                continue;
            }
            $translated = $mediaEntity->getTranslated();
            $media[] = [
                'url' => $mediaEntity->getUrl(),
                'alt' => (string) ($translated['alt'] ?? $product->getTranslated()['name'] ?? ''),
                'title' => (string) ($translated['title'] ?? $product->getTranslated()['name'] ?? ''),
                'width' => $mediaEntity->getMetaData()['width'] ?? null,
                'height' => $mediaEntity->getMetaData()['height'] ?? null,
                'position' => $productMedia->getPosition(),
            ];
        }
        \usort($media, static fn (array $left, array $right): int => ($left['position'] ?? 0) <=> ($right['position'] ?? 0));

        $available = $public && $product->getAvailable();
        $price = $product instanceof SalesChannelProductEntity
            ? $product->getCalculatedPrice()->getUnitPrice()
            : $product->getPrice()?->getCurrencyPrice(self::EUR_CURRENCY_ID)?->getGross();
        $rails = $this->decodeList($customFields['veylune_rail_candidates'] ?? null);
        if ($public && $rails === []) {
            $rails[] = 'New Arrivals';
        }

        return [
            'id' => $product->getId(),
            'recordId' => $recordId,
            'productNumber' => (string) ($product->getProductNumber() ?? ''),
            'name' => (string) ($product->getTranslated()['name'] ?? $product->getName() ?? $manifest['nameEn'] ?? ''),
            'description' => (string) ($product->getTranslated()['description'] ?? ''),
            'targetPrice' => $price ?? (float) ($customFields['veylune_target_price_gross'] ?? $manifest['price'] ?? 0),
            'status' => $public
                ? ($available ? 'Available' : 'Out of stock')
                : (string) ($customFields['veylune_status_copy'] ?? 'Supplier Selection'),
            'department' => (string) ($customFields['veylune_department_key'] ?? $manifest['department'] ?? ''),
            'productType' => (string) ($customFields['veylune_product_type_key'] ?? $manifest['productType'] ?? ''),
            'material' => $primaryMaterial,
            'materialLabel' => DraftCatalogManifest::materials()[$primaryMaterial]['en'] ?? \ucwords(\str_replace('_', ' ', $primaryMaterial)),
            'materials' => $materials,
            'rooms' => $rooms,
            'collections' => $collections,
            'rails' => $rails,
            'coverUrl' => $coverMedia?->getUrl(),
            'coverAlt' => (string) ($coverMedia?->getTranslated()['alt'] ?? $product->getTranslated()['name'] ?? ''),
            'media' => $media,
            'customFields' => $customFields,
            'width' => $product->getWidth(),
            'height' => $product->getHeight(),
            'length' => $product->getLength(),
            'weight' => $product->getWeight(),
            'available' => $available,
            'stock' => $product->getStock(),
            'nativeProductId' => $public ? $product->getId() : null,
        ];
    }

    /**
     * @param array<string, mixed> $customFields
     */
    private function recordId(ProductEntity $product, array $customFields): string
    {
        $recordId = \strtoupper(\trim((string) ($customFields['veylune_catalog_record_id'] ?? '')));
        if ($recordId !== '') {
            return $recordId;
        }

        $productNumber = \strtoupper((string) $product->getProductNumber());

        return \preg_match('/^VLT-TEST-([A-Z][0-9]{2})$/', $productNumber, $matches) === 1
            ? $matches[1]
            : '';
    }

    /**
     * @return array<string, mixed>|null
     */
    private function manifestProduct(string $recordId): ?array
    {
        foreach (DraftCatalogManifest::products() as $product) {
            if ($product['id'] === $recordId) {
                return $product;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function decodeList(mixed $value): array
    {
        if (!\is_string($value) || $value === '') {
            return [];
        }

        $decoded = \json_decode($value, true);

        return \is_array($decoded) ? \array_values(\array_filter($decoded, 'is_string')) : [];
    }
}
