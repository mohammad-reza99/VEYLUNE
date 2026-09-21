<?php declare(strict_types=1);

namespace VeyluneTheme\Preview;

final class EditorialMediaRegistry
{
    public const SOURCE_DIRECTORY = 'custom/plugins/VeyluneTheme/src/Resources/app/storefront/src/assets';
    public const MINIMUM_LONG_EDGE = 1200;

    /**
     * @return array<string, array{source: string, titleEn: string, titleDe: string, altEn: string, altDe: string}>
     */
    public static function destinations(): array
    {
        return [
            'category:furniture' => ['source' => 'veylune-category-furniture-v1.webp', 'titleEn' => 'Furniture department hero', 'titleDe' => 'Hero der Moebelabteilung', 'altEn' => 'Sculptural furniture arranged in a warm neutral interior', 'altDe' => 'Skulpturale Moebel in einem warmen neutralen Interieur'],
            'category:lighting' => ['source' => 'veylune-category-lighting-v1.webp', 'titleEn' => 'Lighting department hero', 'titleDe' => 'Hero der Beleuchtungsabteilung', 'altEn' => 'Layered table and pendant lighting in a quiet living room', 'altDe' => 'Gestaffelte Tisch- und Pendelleuchten in einem ruhigen Wohnzimmer'],
            'category:decor-objects' => ['source' => 'veylune-category-decor-v1.webp', 'titleEn' => 'Decor and objects hero', 'titleDe' => 'Hero fuer Dekor und Objekte', 'altEn' => 'Ceramic vessels and sculptural objects on a pale stone console', 'altDe' => 'Keramikgefaesse und skulpturale Objekte auf einer hellen Steinkonsole'],
            'category:textiles-rugs' => ['source' => 'veylune-category-textiles-v1.webp', 'titleEn' => 'Textiles and rugs hero', 'titleDe' => 'Hero fuer Textilien und Teppiche', 'altEn' => 'Neutral rug, cushions and folded textiles in a bright room', 'altDe' => 'Neutraler Teppich, Kissen und gefaltete Textilien in einem hellen Raum'],
            'category:dining-kitchen' => ['source' => 'veylune-category-dining-v1.webp', 'titleEn' => 'Dining and kitchen hero', 'titleDe' => 'Hero fuer Essbereich und Kueche', 'altEn' => 'Oak dining table set for a relaxed gathering', 'altDe' => 'Eichentisch fuer ein entspanntes Zusammensein'],
            'category:outdoor' => ['source' => 'veylune-category-outdoor-v1.webp', 'titleEn' => 'Outdoor department hero', 'titleDe' => 'Hero der Outdoor-Abteilung', 'altEn' => 'Outdoor lounge chair and table on a planted terrace', 'altDe' => 'Outdoor-Loungesessel und Tisch auf einer bepflanzten Terrasse'],
            'room:living-room' => ['source' => 'veylune-room-living-v1.webp', 'titleEn' => 'Living room edit hero', 'titleDe' => 'Hero der Wohnzimmerauswahl', 'altEn' => 'Warm living room with curved sofa and sculptural coffee table', 'altDe' => 'Warmes Wohnzimmer mit geschwungenem Sofa und skulpturalem Couchtisch'],
            'room:dining-room' => ['source' => 'veylune-room-dining-v1.webp', 'titleEn' => 'Dining room edit hero', 'titleDe' => 'Hero der Esszimmerauswahl', 'altEn' => 'Dining room with oak table, upholstered chairs and pendant light', 'altDe' => 'Esszimmer mit Eichentisch, gepolsterten Stuehlen und Pendelleuchte'],
            'room:bedroom' => ['source' => 'veylune-room-bedroom-v1.webp', 'titleEn' => 'Bedroom edit hero', 'titleDe' => 'Hero der Schlafzimmerauswahl', 'altEn' => 'Quiet bedroom with upholstered bed and warm bedside lighting', 'altDe' => 'Ruhiges Schlafzimmer mit Polsterbett und warmer Nachttischbeleuchtung'],
            'room:workspace' => ['source' => 'veylune-room-workspace-v1.webp', 'titleEn' => 'Workspace edit hero', 'titleDe' => 'Hero der Arbeitszimmerauswahl', 'altEn' => 'Home workspace with oak desk, storage and directional lamp', 'altDe' => 'Arbeitsplatz zu Hause mit Eichenschreibtisch, Stauraum und gerichteter Leuchte'],
            'room:hallway' => ['source' => 'veylune-category-decor-v1.webp', 'titleEn' => 'Hallway edit hero', 'titleDe' => 'Hero der Flurauswahl', 'altEn' => 'Narrow console with mirror and sculptural objects for an entry hall', 'altDe' => 'Schmale Konsole mit Spiegel und skulpturalen Objekten fuer einen Eingangsbereich'],
            'collection:founder-selection' => ['source' => 'veylune-promo-living-room-v1.webp', 'titleEn' => 'Founder selection hero', 'titleDe' => 'Hero der Gruenderauswahl', 'altEn' => 'Founder-selected furniture in a warm architectural living room', 'altDe' => 'Vom Gruender ausgewaehlte Moebel in einem warmen architektonischen Wohnzimmer'],
            'collection:new-arrivals' => ['source' => 'veylune-home-hero-architectural-warm-v1.webp', 'titleEn' => 'New arrivals hero', 'titleDe' => 'Hero der Neuheiten', 'altEn' => 'New Veylune objects presented in a warm architectural interior', 'altDe' => 'Neue Veylune-Objekte in einem warmen architektonischen Interieur'],
        ];
    }

    public static function mediaId(string $destinationId): string
    {
        return md5('veylune-phase4|editorial-media|' . $destinationId);
    }

    public static function targetFileName(string $destinationId): string
    {
        return 'veylune-editorial-' . str_replace([':', '_'], '-', $destinationId) . '-v1';
    }
}
