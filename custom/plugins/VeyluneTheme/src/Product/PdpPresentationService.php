<?php declare(strict_types=1);

namespace VeyluneTheme\Product;

use Shopware\Core\Content\Product\ProductEntity;

final class PdpPresentationService
{
    /**
     * @var array<string, array{family: string, label: string, focus: string, fields: list<array{label: string, value: string}>}>
     */
    private const PROFILES = [
        'seating' => [
            'family' => 'seating',
            'label' => 'Seating',
            'focus' => 'Proportion, support and upholstery direction are reviewed together for the intended room.',
            'fields' => [
                ['label' => 'Support profile', 'value' => 'Residential comfort specification pending'],
                ['label' => 'Upholstery performance', 'value' => 'Supplier evidence pending'],
                ['label' => 'Assembly', 'value' => 'Confirmed with final configuration'],
            ],
        ],
        'tables' => [
            'family' => 'tables',
            'label' => 'Tables and desks',
            'focus' => 'Surface, edge, base and footprint are confirmed as one structural specification.',
            'fields' => [
                ['label' => 'Surface finish', 'value' => 'Finish sample review required'],
                ['label' => 'Base construction', 'value' => 'Supplier evidence pending'],
                ['label' => 'Assembly', 'value' => 'Confirmed with access review'],
            ],
        ],
        'lighting' => [
            'family' => 'lighting',
            'label' => 'Lighting',
            'focus' => 'Light source, electrical rating and installation conditions are verified before commitment.',
            'fields' => [
                ['label' => 'Light source', 'value' => 'Supplier specification pending'],
                ['label' => 'Electrical rating', 'value' => 'Destination compliance review required'],
                ['label' => 'Installation', 'value' => 'Professional installation review'],
            ],
        ],
        'textiles' => [
            'family' => 'textiles',
            'label' => 'Textiles and rugs',
            'focus' => 'Composition, hand, dimensions and care instructions are reviewed against the project use.',
            'fields' => [
                ['label' => 'Composition', 'value' => 'Supplier specification pending'],
                ['label' => 'Construction', 'value' => 'Material evidence pending'],
                ['label' => 'Care', 'value' => 'Final care sheet required'],
            ],
        ],
        'storage' => [
            'family' => 'storage',
            'label' => 'Storage',
            'focus' => 'Interior layout, door clearance, anchoring and delivery access are checked together.',
            'fields' => [
                ['label' => 'Interior configuration', 'value' => 'Final layout pending'],
                ['label' => 'Wall anchoring', 'value' => 'Site review required'],
                ['label' => 'Assembly', 'value' => 'Delivery-access dependent'],
            ],
        ],
        'objects' => [
            'family' => 'objects',
            'label' => 'Objects and accessories',
            'focus' => 'Material variation, scale, placement and care are confirmed for the selected piece.',
            'fields' => [
                ['label' => 'Material variation', 'value' => 'Natural variation expected'],
                ['label' => 'Placement', 'value' => 'Interior use unless confirmed otherwise'],
                ['label' => 'Care', 'value' => 'Final care sheet required'],
            ],
        ],
        'outdoor' => [
            'family' => 'outdoor',
            'label' => 'Outdoor',
            'focus' => 'Climate suitability, drainage, finish and seasonal care require destination review.',
            'fields' => [
                ['label' => 'Climate suitability', 'value' => 'Destination review required'],
                ['label' => 'Outdoor finish', 'value' => 'Supplier evidence pending'],
                ['label' => 'Seasonal care', 'value' => 'Final care sheet required'],
            ],
        ],
        'sleep' => [
            'family' => 'sleep',
            'label' => 'Beds',
            'focus' => 'Mattress fit, support system, access and assembly are confirmed before approval.',
            'fields' => [
                ['label' => 'Mattress compatibility', 'value' => 'Final size confirmation required'],
                ['label' => 'Support system', 'value' => 'Supplier specification pending'],
                ['label' => 'Assembly', 'value' => 'Room-access review required'],
            ],
        ],
    ];

    /** @return array<string, mixed> */
    public function forDraft(array $product): array
    {
        return $this->build(
            typeKey: (string) ($product['productType'] ?? ''),
            materialLabel: (string) ($product['materialLabel'] ?? 'Material direction pending'),
            width: $this->number($product['width'] ?? null),
            height: $this->number($product['height'] ?? null),
            length: $this->number($product['length'] ?? null),
            weight: $this->number($product['weight'] ?? null),
            galleryCount: \count($product['media'] ?? []),
            mode: 'preview',
            available: false,
        );
    }

    /** @return array<string, mixed> */
    public function forProduct(ProductEntity $product): array
    {
        $translatedCustomFields = $product->getTranslated()['customFields'] ?? null;
        $customFields = \is_array($translatedCustomFields) ? $translatedCustomFields : ($product->getCustomFields() ?? []);
        $materials = [];
        foreach ($product->getProperties() ?? [] as $property) {
            $groupName = (string) ($property->getGroup()?->getTranslated()['name'] ?? $property->getGroup()?->getName() ?? '');
            if (str_contains(strtolower($groupName), 'material')) {
                $materials[] = (string) ($property->getTranslated()['name'] ?? $property->getName() ?? '');
            }
        }

        return $this->build(
            typeKey: (string) ($customFields['veylune_product_type_key'] ?? 'object'),
            materialLabel: implode(', ', array_values(array_filter($materials))) ?: 'Material specification pending',
            width: $this->number($product->getWidth()),
            height: $this->number($product->getHeight()),
            length: $this->number($product->getLength()),
            weight: $this->number($product->getWeight()),
            galleryCount: $product->getMedia()?->count() ?? 0,
            mode: 'native',
            available: (bool) $product->getAvailable(),
        );
    }

    /** @return array<string, mixed> */
    private function build(
        string $typeKey,
        string $materialLabel,
        ?float $width,
        ?float $height,
        ?float $length,
        ?float $weight,
        int $galleryCount,
        string $mode,
        bool $available,
    ): array {
        $profile = self::PROFILES[$this->familyFor($typeKey)];
        $dimensions = $this->dimensions($width, $height, $length);
        $specifications = [
            ['label' => 'Product family', 'value' => $profile['label']],
            ['label' => 'Material direction', 'value' => $materialLabel],
            ['label' => 'Dimensions', 'value' => $dimensions],
            ['label' => 'Weight', 'value' => $weight !== null && $weight > 0 ? $this->formatNumber($weight) . ' kg' : 'Supplier specification pending'],
            ...$profile['fields'],
        ];
        $isPreview = $mode === 'preview';

        return [
            'typeKey' => $typeKey,
            'typeLabel' => $this->label($typeKey),
            'family' => $profile['family'],
            'familyLabel' => $profile['label'],
            'focus' => $profile['focus'],
            'specifications' => $specifications,
            'gallery' => [
                'count' => $galleryCount,
                'state' => $galleryCount >= 5 ? 'launch-ready' : 'cover-only',
                'label' => $galleryCount >= 5 ? 'Multi-view gallery' : 'Governed cover; additional views pending approval',
            ],
            'commerce' => [
                'mode' => $mode,
                'state' => $isPreview ? 'non-binding-preview' : ($available ? 'buy' : 'inquire'),
                'primaryLabel' => $isPreview ? 'Add to preview cart' : ($available ? 'Add to cart' : 'Request availability'),
                'disclosure' => $isPreview
                    ? 'This action saves a non-binding project estimate. It does not reserve stock, collect payment or create an order.'
                    : ($available
                        ? 'Price, tax, shipping, stock and order state are calculated by Shopware.'
                        : 'Availability and commercial terms require studio confirmation.'),
            ],
            'delivery' => [
                'title' => $isPreview ? 'Project delivery review' : 'Delivery calculated at checkout',
                'copy' => $isPreview
                    ? 'Access, destination, lead time, assembly and final delivery cost remain subject to studio confirmation.'
                    : 'Available shipping methods and final cost are resolved from the active Shopware cart and destination.',
            ],
            'returns' => [
                'title' => 'Terms before commitment',
                'copy' => 'Cancellation, damage procedure, warranty and return eligibility are shown only from approved supplier and legal terms.',
            ],
        ];
    }

    private function familyFor(string $typeKey): string
    {
        return match ($typeKey) {
            'sofas', 'lounge_chairs', 'dining_chairs', 'office_chairs', 'benches_stools' => 'seating',
            'coffee_tables', 'side_tables', 'dining_tables', 'outdoor_tables', 'desks', 'consoles' => 'tables',
            'floor_lamps', 'table_lamps', 'pendant_lights', 'wall_lighting' => 'lighting',
            'rugs', 'throws', 'cushions' => 'textiles',
            'storage' => 'storage',
            'outdoor_seating', 'planters_objects' => 'outdoor',
            'beds' => 'sleep',
            default => 'objects',
        };
    }

    private function dimensions(?float $width, ?float $height, ?float $length): string
    {
        if (($width ?? 0.0) <= 0 && ($height ?? 0.0) <= 0 && ($length ?? 0.0) <= 0) {
            return 'Supplier specification pending';
        }

        return sprintf(
            'W %s x H %s x D %s mm',
            $width !== null && $width > 0 ? $this->formatNumber($width) : 'pending',
            $height !== null && $height > 0 ? $this->formatNumber($height) : 'pending',
            $length !== null && $length > 0 ? $this->formatNumber($length) : 'pending',
        );
    }

    private function label(string $value): string
    {
        return ucwords(str_replace('_', ' ', $value !== '' ? $value : 'object'));
    }

    private function number(mixed $value): ?float
    {
        return \is_numeric($value) ? (float) $value : null;
    }

    private function formatNumber(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }
}
