<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class DraftCatalogManifest
{
    public const BATCH_ID = 'WP-CAT-04-DRAFT-50';

    /**
     * @return array<string, array{en: string, de: string}>
     */
    public static function departments(): array
    {
        return [
            'furniture' => ['en' => 'Furniture', 'de' => 'Moebel'],
            'lighting' => ['en' => 'Lighting', 'de' => 'Leuchten'],
            'decor_objects' => ['en' => 'Decor & Objects', 'de' => 'Dekor & Objekte'],
            'textiles_rugs' => ['en' => 'Textiles & Rugs', 'de' => 'Textilien & Teppiche'],
            'dining_kitchen' => ['en' => 'Dining & Kitchen', 'de' => 'Essen & Kueche'],
            'outdoor' => ['en' => 'Outdoor', 'de' => 'Outdoor'],
        ];
    }

    /**
     * @return array<string, array{department: string, en: string, de: string}>
     */
    public static function productTypes(): array
    {
        return [
            'sofas' => ['department' => 'furniture', 'en' => 'Sofas', 'de' => 'Sofas'],
            'lounge_chairs' => ['department' => 'furniture', 'en' => 'Lounge Chairs', 'de' => 'Loungesessel'],
            'dining_chairs' => ['department' => 'furniture', 'en' => 'Dining Chairs', 'de' => 'Esszimmerstuehle'],
            'office_chairs' => ['department' => 'furniture', 'en' => 'Office Chairs', 'de' => 'Buerostuehle'],
            'benches_stools' => ['department' => 'furniture', 'en' => 'Benches & Stools', 'de' => 'Baenke & Hocker'],
            'coffee_tables' => ['department' => 'furniture', 'en' => 'Coffee Tables', 'de' => 'Couchtische'],
            'side_tables' => ['department' => 'furniture', 'en' => 'Side Tables', 'de' => 'Beistelltische'],
            'consoles' => ['department' => 'furniture', 'en' => 'Consoles', 'de' => 'Konsolen'],
            'desks' => ['department' => 'furniture', 'en' => 'Desks', 'de' => 'Schreibtische'],
            'beds' => ['department' => 'furniture', 'en' => 'Beds', 'de' => 'Betten'],
            'storage' => ['department' => 'furniture', 'en' => 'Storage', 'de' => 'Aufbewahrung'],
            'floor_lamps' => ['department' => 'lighting', 'en' => 'Floor Lamps', 'de' => 'Stehleuchten'],
            'table_lamps' => ['department' => 'lighting', 'en' => 'Table Lamps', 'de' => 'Tischleuchten'],
            'pendant_lights' => ['department' => 'lighting', 'en' => 'Pendant Lights', 'de' => 'Pendelleuchten'],
            'wall_lighting' => ['department' => 'lighting', 'en' => 'Wall Lighting', 'de' => 'Wandleuchten'],
            'vessels' => ['department' => 'decor_objects', 'en' => 'Vessels', 'de' => 'Gefaesse'],
            'sculptural_objects' => ['department' => 'decor_objects', 'en' => 'Sculptural Objects', 'de' => 'Skulpturale Objekte'],
            'mirrors' => ['department' => 'decor_objects', 'en' => 'Mirrors', 'de' => 'Spiegel'],
            'trays' => ['department' => 'decor_objects', 'en' => 'Trays', 'de' => 'Tabletts'],
            'decorative_objects' => ['department' => 'decor_objects', 'en' => 'Decorative Objects', 'de' => 'Dekorative Objekte'],
            'rugs' => ['department' => 'textiles_rugs', 'en' => 'Rugs', 'de' => 'Teppiche'],
            'throws' => ['department' => 'textiles_rugs', 'en' => 'Throws', 'de' => 'Decken'],
            'cushions' => ['department' => 'textiles_rugs', 'en' => 'Cushions', 'de' => 'Kissen'],
            'dining_tables' => ['department' => 'dining_kitchen', 'en' => 'Dining Tables', 'de' => 'Esstische'],
            'tableware' => ['department' => 'dining_kitchen', 'en' => 'Tableware', 'de' => 'Geschirr'],
            'serveware' => ['department' => 'dining_kitchen', 'en' => 'Serveware', 'de' => 'Servierwaren'],
            'kitchen_objects' => ['department' => 'dining_kitchen', 'en' => 'Kitchen Objects', 'de' => 'Kuechenobjekte'],
            'outdoor_seating' => ['department' => 'outdoor', 'en' => 'Outdoor Seating', 'de' => 'Outdoor-Sitzmoebel'],
            'outdoor_tables' => ['department' => 'outdoor', 'en' => 'Outdoor Tables', 'de' => 'Outdoor-Tische'],
            'planters_objects' => ['department' => 'outdoor', 'en' => 'Planters & Objects', 'de' => 'Pflanzgefaesse & Objekte'],
        ];
    }

    /**
     * @return array<string, array{en: string, de: string}>
     */
    public static function rooms(): array
    {
        return [
            'living_room' => ['en' => 'Living Room', 'de' => 'Wohnzimmer'],
            'dining_room' => ['en' => 'Dining Room', 'de' => 'Esszimmer'],
            'bedroom' => ['en' => 'Bedroom', 'de' => 'Schlafzimmer'],
            'home_office' => ['en' => 'Home Office', 'de' => 'Arbeitszimmer'],
            'hallway' => ['en' => 'Hallway', 'de' => 'Flur'],
            'outdoor' => ['en' => 'Outdoor', 'de' => 'Outdoor'],
        ];
    }

    /**
     * @return array<string, array{en: string, de: string}>
     */
    public static function collections(): array
    {
        return [
            'new_arrivals' => ['en' => 'New Arrivals', 'de' => 'Neuheiten'],
            'founder_selection' => ['en' => 'Founder Selection', 'de' => 'Founder Selection'],
            'quiet_living' => ['en' => 'Quiet Living', 'de' => 'Quiet Living'],
            'material_forms' => ['en' => 'Material Forms', 'de' => 'Material Forms'],
            'architectural_light' => ['en' => 'Architectural Light', 'de' => 'Architectural Light'],
            'table_rituals' => ['en' => 'Table Rituals', 'de' => 'Table Rituals'],
            'open_air' => ['en' => 'Open Air', 'de' => 'Open Air'],
        ];
    }

    /**
     * @return array<string, array{en: string, de: string}>
     */
    public static function materials(): array
    {
        return [
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
        ];
    }

    /**
     * @return list<array{
     *     id: string,
     *     sku: string,
     *     nameEn: string,
     *     nameDe: string,
     *     department: string,
     *     productType: string,
     *     primaryMaterial: string,
     *     secondaryMaterials: list<string>,
     *     rooms: list<string>,
     *     collections: list<string>,
     *     price: float,
     *     consultation: string
     * }>
     */
    public static function products(): array
    {
        return [
            self::product('F01', 'VLS-FUR-000001', 'Aurelia Modular Sofa', 'Aurelia Modulares Sofa', 'furniture', 'sofas', 'upholstery_fabric', ['wood', 'metal'], ['living_room'], ['quiet_living', 'founder_selection'], 4900, 'required'),
            self::product('F02', 'VLS-FUR-000002', 'Liora Curved Sofa', 'Liora Geschwungenes Sofa', 'furniture', 'sofas', 'upholstery_fabric', ['wood'], ['living_room'], ['quiet_living'], 4200, 'recommended'),
            self::product('F03', 'VLS-FUR-000003', 'Oris Leather Lounge Chair', 'Oris Leder-Loungesessel', 'furniture', 'lounge_chairs', 'leather', ['wood'], ['living_room', 'bedroom'], ['quiet_living', 'founder_selection'], 2250, 'recommended'),
            self::product('F04', 'VLS-FUR-000004', 'Selene Oak Lounge Chair', 'Selene Lounge-Sessel aus Eiche', 'furniture', 'lounge_chairs', 'wood', ['upholstery_fabric'], ['living_room', 'bedroom'], ['quiet_living'], 1850, 'recommended'),
            self::product('F05', 'VLS-FUR-000005', 'Edda Dining Chair', 'Edda Esszimmerstuhl', 'furniture', 'dining_chairs', 'wood', ['upholstery_fabric'], ['dining_room'], ['quiet_living'], 690, 'none'),
            self::product('F06', 'VLS-FUR-000006', 'Noma Metal Dining Chair', 'Noma Metall-Esszimmerstuhl', 'furniture', 'dining_chairs', 'metal', ['leather'], ['dining_room'], ['material_forms'], 760, 'none'),
            self::product('F07', 'VLS-FUR-000007', 'Forma Desk Chair', 'Forma Schreibtischstuhl', 'furniture', 'office_chairs', 'leather', ['wood', 'metal'], ['home_office'], ['quiet_living'], 1350, 'recommended'),
            self::product('F08', 'VLS-FUR-000008', 'Talo Counter Stool', 'Talo Barhocker', 'furniture', 'benches_stools', 'wood', ['leather'], ['dining_room'], ['quiet_living'], 640, 'none'),
            self::product('F09', 'VLS-FUR-000009', 'Stillwater Oak Bench', 'Stillwater Eichenbank', 'furniture', 'benches_stools', 'wood', ['upholstery_fabric'], ['hallway', 'bedroom', 'dining_room'], ['quiet_living'], 1250, 'none'),
            self::product('F10', 'VLS-FUR-000010', 'Elara Travertine Coffee Table', 'Elara Couchtisch aus Travertin', 'furniture', 'coffee_tables', 'travertine', ['metal'], ['living_room'], ['material_forms', 'founder_selection'], 2200, 'recommended'),
            self::product('F11', 'VLS-FUR-000011', 'Varo Oak Coffee Table', 'Varo Couchtisch aus Eiche', 'furniture', 'coffee_tables', 'wood', ['metal'], ['living_room'], ['quiet_living'], 1650, 'recommended'),
            self::product('F12', 'VLS-FUR-000012', 'Neri Marble Side Table', 'Neri Beistelltisch aus Marmor', 'furniture', 'side_tables', 'marble', ['metal'], ['living_room', 'bedroom'], ['material_forms'], 890, 'recommended'),
            self::product('F13', 'VLS-FUR-000013', 'Portico Travertine Console', 'Portico Konsole aus Travertin', 'furniture', 'consoles', 'travertine', ['metal'], ['hallway', 'living_room'], ['material_forms', 'founder_selection'], 2750, 'recommended'),
            self::product('F14', 'VLS-FUR-000014', 'Linea Writing Desk', 'Linea Schreibtisch', 'furniture', 'desks', 'wood', ['leather', 'metal'], ['home_office', 'bedroom'], ['quiet_living', 'founder_selection'], 2450, 'recommended'),
            self::product('F15', 'VLS-FUR-000015', 'Serein Platform Bed', 'Serein Polsterbett', 'furniture', 'beds', 'upholstery_fabric', ['wood'], ['bedroom'], ['quiet_living', 'founder_selection'], 3600, 'recommended'),
            self::product('F16', 'VLS-FUR-000016', 'Vale Low Cabinet', 'Vale Niedriges Sideboard', 'furniture', 'storage', 'wood', ['metal'], ['living_room', 'dining_room'], ['quiet_living'], 2800, 'recommended'),
            self::product('F17', 'VLS-FUR-000017', 'Canto Tall Cabinet', 'Canto Hochschrank', 'furniture', 'storage', 'wood', ['glass'], ['living_room', 'home_office'], ['quiet_living'], 3200, 'recommended'),
            self::product('F18', 'VLS-FUR-000018', 'Mira Leather Day Chair', 'Mira Leder-Tagessessel', 'furniture', 'lounge_chairs', 'leather', ['metal'], ['living_room', 'bedroom'], ['material_forms'], 1950, 'recommended'),
            self::product('F19', 'VLS-FUR-000019', 'Plinth Glass Side Table', 'Plinth Beistelltisch aus Glas', 'furniture', 'side_tables', 'glass', ['metal'], ['living_room', 'bedroom'], ['material_forms'], 820, 'none'),
            self::product('L01', 'VLS-LGT-000001', 'Nocturne Floor Lamp', 'Nocturne Stehleuchte', 'lighting', 'floor_lamps', 'metal', ['stone'], ['living_room', 'home_office'], ['architectural_light'], 1450, 'none'),
            self::product('L02', 'VLS-LGT-000002', 'Orbis Counterweighted Floor Lamp', 'Orbis Stehleuchte mit Gegengewicht', 'lighting', 'floor_lamps', 'metal', ['travertine'], ['living_room', 'home_office'], ['architectural_light', 'founder_selection'], 1850, 'none'),
            self::product('L03', 'VLS-LGT-000003', 'Lumen Ceramic Table Lamp', 'Lumen Keramik-Tischleuchte', 'lighting', 'table_lamps', 'ceramic', ['metal', 'upholstery_fabric'], ['bedroom', 'living_room'], ['architectural_light'], 590, 'none'),
            self::product('L04', 'VLS-LGT-000004', 'Halo Ribbed Glass Pendant', 'Halo Pendelleuchte aus geripptem Glas', 'lighting', 'pendant_lights', 'glass', ['metal'], ['dining_room', 'hallway'], ['architectural_light', 'founder_selection'], 1150, 'required'),
            self::product('L05', 'VLS-LGT-000005', 'Axis Linear Floor Lamp', 'Axis Lineare Stehleuchte', 'lighting', 'floor_lamps', 'metal', ['stone'], ['living_room'], ['architectural_light'], 1650, 'none'),
            self::product('L06', 'VLS-LGT-000006', 'Alba Stone Table Lamp', 'Alba Tischleuchte aus Stein', 'lighting', 'table_lamps', 'stone', ['upholstery_fabric', 'metal'], ['bedroom', 'living_room'], ['material_forms'], 790, 'none'),
            self::product('L07', 'VLS-LGT-000007', 'Vela Ceramic Table Lamp', 'Vela Keramik-Tischleuchte', 'lighting', 'table_lamps', 'ceramic', ['metal'], ['bedroom', 'home_office'], ['architectural_light'], 520, 'none'),
            self::product('L08', 'VLS-LGT-000008', 'Meridian Pendant', 'Meridian Pendelleuchte', 'lighting', 'pendant_lights', 'metal', ['glass'], ['dining_room'], ['architectural_light'], 1250, 'required'),
            self::product('L09', 'VLS-LGT-000009', 'Lucent Glass Wall Light', 'Lucent Wandleuchte aus Glas', 'lighting', 'wall_lighting', 'glass', ['metal'], ['bedroom', 'hallway'], ['architectural_light'], 620, 'required'),
            self::product('L10', 'VLS-LGT-000010', 'Linea Wall Sconce', 'Linea Wandleuchte', 'lighting', 'wall_lighting', 'metal', ['glass'], ['hallway', 'living_room'], ['architectural_light'], 540, 'required'),
            self::product('D01', 'VLS-DEC-000001', 'Atelier Stone Vessel', 'Atelier Steingefaess', 'decor_objects', 'vessels', 'stone', [], ['living_room', 'dining_room', 'hallway'], ['material_forms'], 420, 'none'),
            self::product('D02', 'VLS-DEC-000002', 'Cairn Stone Vessel', 'Cairn Steingefaess', 'decor_objects', 'vessels', 'stone', [], ['living_room', 'hallway'], ['material_forms'], 480, 'none'),
            self::product('D03', 'VLS-DEC-000003', 'Tectona Travertine Vessel', 'Tectona Gefaess aus Travertin', 'decor_objects', 'vessels', 'travertine', [], ['living_room', 'dining_room'], ['material_forms', 'founder_selection'], 560, 'none'),
            self::product('D04', 'VLS-DEC-000004', 'Meridian Cast Sculpture', 'Meridian Gussskulptur', 'decor_objects', 'sculptural_objects', 'metal', ['stone'], ['living_room', 'home_office'], ['material_forms', 'founder_selection'], 740, 'none'),
            self::product('D05', 'VLS-DEC-000005', 'Arc Full-Length Mirror', 'Arc Ganzkoerperspiegel', 'decor_objects', 'mirrors', 'wood', ['glass'], ['bedroom', 'hallway'], ['quiet_living', 'founder_selection'], 1450, 'recommended'),
            self::product('D06', 'VLS-DEC-000006', 'Strata Valet Tray', 'Strata Ablageschale', 'decor_objects', 'trays', 'marble', ['leather'], ['hallway', 'bedroom'], ['material_forms'], 320, 'none'),
            self::product('D07', 'VLS-DEC-000007', 'Forma Ceramic Object', 'Forma Keramikobjekt', 'decor_objects', 'decorative_objects', 'ceramic', [], ['living_room', 'dining_room'], ['material_forms'], 290, 'none'),
            self::product('D08', 'VLS-DEC-000008', 'Monolith Stone Object', 'Monolith Steinobjekt', 'decor_objects', 'sculptural_objects', 'stone', [], ['living_room', 'hallway'], ['material_forms'], 680, 'none'),
            self::product('D09', 'VLS-DEC-000009', 'Orbit Metal Object', 'Orbit Metallobjekt', 'decor_objects', 'decorative_objects', 'metal', [], ['living_room', 'home_office'], ['material_forms'], 360, 'none'),
            self::product('T01', 'VLS-TEX-000001', 'Tactile Hand-Knotted Rug', 'Tactile Handgeknuepfter Wollteppich', 'textiles_rugs', 'rugs', 'wool', [], ['living_room', 'bedroom'], ['quiet_living', 'founder_selection'], 1850, 'recommended'),
            self::product('T02', 'VLS-TEX-000002', 'Loma Wool Runner', 'Loma Woll-Laeufer', 'textiles_rugs', 'rugs', 'wool', [], ['hallway', 'bedroom'], ['quiet_living'], 890, 'recommended'),
            self::product('T03', 'VLS-TEX-000003', 'Sera Woven Throw', 'Sera Gewebte Wolldecke', 'textiles_rugs', 'throws', 'wool', [], ['living_room', 'bedroom'], ['quiet_living'], 320, 'none'),
            self::product('T04', 'VLS-TEX-000004', 'Vale Textured Cushion', 'Vale Strukturkissen', 'textiles_rugs', 'cushions', 'upholstery_fabric', [], ['living_room', 'bedroom'], ['quiet_living'], 160, 'none'),
            self::product('T05', 'VLS-TEX-000005', 'Noma Boucle Cushion', 'Noma Boucle-Kissen', 'textiles_rugs', 'cushions', 'upholstery_fabric', [], ['living_room', 'bedroom'], ['quiet_living'], 180, 'none'),
            self::product('K01', 'VLS-DIN-000001', 'Calma Travertine Dining Table', 'Calma Esstisch aus Travertin', 'dining_kitchen', 'dining_tables', 'travertine', ['metal'], ['dining_room'], ['material_forms', 'founder_selection'], 1399, 'recommended'),
            self::product('K02', 'VLS-DIN-000002', 'Sera Ceramic Place Setting', 'Sera Keramik-Gedeck', 'dining_kitchen', 'tableware', 'ceramic', [], ['dining_room'], ['table_rituals'], 240, 'none'),
            self::product('K03', 'VLS-DIN-000003', 'Talo Oak Serving Board', 'Talo Servierbrett aus Eiche', 'dining_kitchen', 'serveware', 'wood', [], ['dining_room'], ['table_rituals'], 190, 'none'),
            self::product('K04', 'VLS-DIN-000004', 'Linea Countertop Object', 'Linea Kuechenobjekt', 'dining_kitchen', 'kitchen_objects', 'metal', ['wood'], ['dining_room'], ['table_rituals'], 260, 'none'),
            self::product('O01', 'VLS-OUT-000001', 'Terra Outdoor Lounge Chair', 'Terra Outdoor-Loungesessel', 'outdoor', 'outdoor_seating', 'wood', ['upholstery_fabric', 'metal'], ['outdoor'], ['open_air', 'founder_selection'], 1650, 'recommended'),
            self::product('O02', 'VLS-OUT-000002', 'Monolith Outdoor Table', 'Monolith Outdoor-Tisch', 'outdoor', 'outdoor_tables', 'stone', ['metal'], ['outdoor'], ['open_air', 'founder_selection'], 2400, 'required'),
            self::product('O03', 'VLS-OUT-000003', 'Grove Teak Planter', 'Grove Pflanzgefaess aus Teakholz', 'outdoor', 'planters_objects', 'wood', ['metal'], ['outdoor'], ['open_air'], 520, 'recommended'),
        ];
    }

    /**
     * @param list<string> $secondaryMaterials
     * @param list<string> $rooms
     * @param list<string> $collections
     *
     * @return array{
     *     id: string,
     *     sku: string,
     *     nameEn: string,
     *     nameDe: string,
     *     department: string,
     *     productType: string,
     *     primaryMaterial: string,
     *     secondaryMaterials: list<string>,
     *     rooms: list<string>,
     *     collections: list<string>,
     *     price: float,
     *     consultation: string
     * }
     */
    private static function product(
        string $id,
        string $sku,
        string $nameEn,
        string $nameDe,
        string $department,
        string $productType,
        string $primaryMaterial,
        array $secondaryMaterials,
        array $rooms,
        array $collections,
        float $price,
        string $consultation
    ): array {
        return compact(
            'id',
            'sku',
            'nameEn',
            'nameDe',
            'department',
            'productType',
            'primaryMaterial',
            'secondaryMaterials',
            'rooms',
            'collections',
            'price',
            'consultation'
        );
    }
}
