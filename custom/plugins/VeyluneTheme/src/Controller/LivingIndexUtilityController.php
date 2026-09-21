<?php declare(strict_types=1);

namespace VeyluneTheme\Controller;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Framework\Routing\StorefrontRouteScope;
use Shopware\Storefront\Page\GenericPageLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use VeyluneTheme\Discovery\EditorialMediaResolver;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StorefrontRouteScope::ID]])]
#[Package('storefront')]
final class LivingIndexUtilityController extends StorefrontController
{
    private const AXES = [
        'object' => 'Object',
        'room' => 'Room',
        'material' => 'Material',
        'style' => 'Style',
        'maker' => 'Maker',
        'project' => 'Project',
    ];

    private const QUERY_EXPANSIONS = [
        'sofa' => ['seating', 'furniture', 'living'],
        'sofas' => ['seating', 'furniture', 'living'],
        'chair' => ['seating', 'furniture'],
        'chairs' => ['seating', 'furniture'],
        'table' => ['tables', 'furniture', 'dining'],
        'desk' => ['workspace', 'furniture'],
        'lamp' => ['lighting', 'light'],
        'lamps' => ['lighting', 'light'],
        'rug' => ['rugs', 'textile'],
        'carpet' => ['rugs', 'textile'],
        'oak' => ['wood', 'material'],
        'travertine' => ['stone', 'material'],
        'marble' => ['stone', 'material'],
        'fabric' => ['textile', 'material'],
        'bronze' => ['metal', 'material'],
        'minimal' => ['quiet', 'style'],
        'japandi' => ['quiet', 'wood', 'style'],
        'designer' => ['maker', 'atelier'],
        'designers' => ['maker', 'atelier'],
        'service' => ['project', 'consultation'],
        'interior' => ['room', 'project'],
        'home' => ['room', 'living'],
    ];

    private const ENTRIES = [
        [
            'key' => 'object:furniture',
            'type' => 'Object',
            'title' => 'Furniture',
            'description' => 'Seating, tables, storage, and room-defining objects.',
            'keywords' => 'object furniture sofa sofas seating chair chairs table tables storage bench benches stool stools wood fabric living quiet',
            'asset' => 'veylune-category-furniture-v1.webp',
            'route' => 'frontend.veylune.discovery.category',
            'routeParams' => ['categoryKey' => 'furniture'],
        ],
        [
            'key' => 'object:lighting',
            'type' => 'Object',
            'title' => 'Lighting',
            'description' => 'Ambient, task, and sculptural light for rooms and surfaces.',
            'keywords' => 'object lighting light pendant pendants floor lamp lamps table lamp lamps metal fabric stone atmosphere',
            'asset' => 'veylune-category-lighting-v1.webp',
            'route' => 'frontend.veylune.discovery.category',
            'routeParams' => ['categoryKey' => 'lighting'],
        ],
        [
            'key' => 'object:decor',
            'type' => 'Object',
            'title' => 'Decor Objects',
            'description' => 'Vessels, mirrors, sculpture, and material accents.',
            'keywords' => 'object decor decoration vessel vessels mirror mirrors sculptural objects sculpture stone wood metal travertine',
            'asset' => 'veylune-category-decor-v1.webp',
            'route' => 'frontend.veylune.discovery.category',
            'routeParams' => ['categoryKey' => 'decor-objects'],
        ],
        [
            'key' => 'object:textiles-rugs',
            'type' => 'Object',
            'title' => 'Textiles & Rugs',
            'description' => 'Soft layers, rugs, throws, and cushions for room scale and tactility.',
            'keywords' => 'object textile textiles rug rugs carpet throw throws cushion cushions fabric soft layer',
            'asset' => 'veylune-category-textiles-v1.webp',
            'route' => 'frontend.veylune.discovery.category',
            'routeParams' => ['categoryKey' => 'textiles-rugs'],
        ],
        [
            'key' => 'object:dining-kitchen',
            'type' => 'Object',
            'title' => 'Dining & Kitchen',
            'description' => 'Tables, seating, tableware, and objects for gathering.',
            'keywords' => 'object dining kitchen table tables seating chair chairs tableware gathering hosting stone wood metal',
            'asset' => 'veylune-category-dining-v1.webp',
            'route' => 'frontend.veylune.discovery.category',
            'routeParams' => ['categoryKey' => 'dining-kitchen'],
        ],
        [
            'key' => 'object:outdoor',
            'type' => 'Object',
            'title' => 'Outdoor Objects',
            'description' => 'Exterior seating, tables, and planters for seasonal living.',
            'keywords' => 'object outdoor exterior seating tables planters garden terrace patio stone wood metal',
            'asset' => 'veylune-category-outdoor-v1.webp',
            'route' => 'frontend.veylune.discovery.category',
            'routeParams' => ['categoryKey' => 'outdoor'],
        ],
        [
            'key' => 'object:bedding-bath',
            'type' => 'Object',
            'title' => 'Bedding & Bath',
            'description' => 'Bedding, bath textiles, throws, and cushions for daily comfort.',
            'keywords' => 'object bedding bath bedroom textile textiles throw throws cushion cushions fabric comfort',
            'asset' => 'veylune-room-bedroom-v1.webp',
            'route' => 'frontend.veylune.discovery.category',
            'routeParams' => ['categoryKey' => 'bedding-bath'],
        ],
        [
            'key' => 'object:mattresses',
            'type' => 'Object',
            'title' => 'Mattresses',
            'description' => 'Sleep foundations considered through support and material quality.',
            'keywords' => 'object mattress mattresses bed sleep bedroom support fabric comfort',
            'asset' => 'veylune-room-bedroom-v1.webp',
            'route' => 'frontend.veylune.discovery.category',
            'routeParams' => ['categoryKey' => 'mattresses'],
        ],
        [
            'key' => 'object:rugs',
            'type' => 'Object',
            'title' => 'Rugs',
            'description' => 'Textural foundations for scale, softness, pattern, and depth.',
            'keywords' => 'object rug rugs carpet carpets textile textiles fabric floor pattern',
            'asset' => 'veylune-category-textiles-v1.webp',
            'route' => 'frontend.veylune.discovery.category',
            'routeParams' => ['categoryKey' => 'rugs'],
        ],
        [
            'key' => 'object:organization',
            'type' => 'Object',
            'title' => 'Organization',
            'description' => 'Storage and organizational forms for clarity and quiet utility.',
            'keywords' => 'object organization organisation storage cabinet cabinets shelving shelf shelves wood metal workspace',
            'asset' => 'veylune-room-workspace-v1.webp',
            'route' => 'frontend.veylune.discovery.category',
            'routeParams' => ['categoryKey' => 'organization'],
        ],
        [
            'key' => 'room:living-room',
            'type' => 'Room',
            'title' => 'Living Room',
            'description' => 'Connected compositions for seating, surfaces, light, and scale.',
            'keywords' => 'room living sofa lounge coffee table interior space quiet calm home',
            'asset' => 'veylune-room-living-v1.webp',
            'route' => 'frontend.veylune.discovery.room',
            'routeParams' => ['roomKey' => 'living-room'],
        ],
        [
            'key' => 'room:dining-room',
            'type' => 'Room',
            'title' => 'Dining Room',
            'description' => 'A room path for gathering, proportion, surface, and light.',
            'keywords' => 'room dining table chairs gathering hosting interior space',
            'asset' => 'veylune-room-dining-v1.webp',
            'route' => 'frontend.veylune.discovery.room',
            'routeParams' => ['roomKey' => 'dining-room'],
        ],
        [
            'key' => 'room:bedroom',
            'type' => 'Room',
            'title' => 'Bedroom',
            'description' => 'Rest, textiles, storage, and low-light atmosphere.',
            'keywords' => 'room bedroom bed sleep mattress bedding calm quiet interior',
            'asset' => 'veylune-room-bedroom-v1.webp',
            'route' => 'frontend.veylune.discovery.room',
            'routeParams' => ['roomKey' => 'bedroom'],
        ],
        [
            'key' => 'room:workspace',
            'type' => 'Room',
            'title' => 'Workspace',
            'description' => 'Focused compositions for work, storage, and task light.',
            'keywords' => 'room workspace office desk chair storage task light interior',
            'asset' => 'veylune-room-workspace-v1.webp',
            'route' => 'frontend.veylune.discovery.room',
            'routeParams' => ['roomKey' => 'workspace'],
        ],
        [
            'key' => 'room:hallway',
            'type' => 'Room',
            'title' => 'Hallway',
            'description' => 'Threshold objects, storage, mirrors, and guiding light.',
            'keywords' => 'room hallway entry entryway corridor console mirror storage light',
            'asset' => 'veylune-category-decor-v1.webp',
            'route' => 'frontend.veylune.discovery.room',
            'routeParams' => ['roomKey' => 'hallway'],
        ],
        [
            'key' => 'room:outdoor',
            'type' => 'Room',
            'title' => 'Outdoor Room',
            'description' => 'Exterior living shaped by weather, material, and seasonal use.',
            'keywords' => 'room outdoor terrace patio garden exterior seating table',
            'asset' => 'veylune-room-outdoor-v1.webp',
            'route' => 'frontend.veylune.discovery.room',
            'routeParams' => ['roomKey' => 'outdoor'],
        ],
        [
            'key' => 'material:index',
            'type' => 'Material',
            'title' => 'Material Index',
            'description' => 'Tone, surface, care, provenance, and spatial context.',
            'keywords' => 'material travertine stone oak wood metal bronze fabric textile care surface provenance',
            'asset' => 'products/f10-elara-travertine-coffee-table-v1.webp',
            'route' => 'frontend.veylune.editions.page',
            'routeParams' => [],
        ],
        [
            'key' => 'material:stone',
            'type' => 'Material',
            'title' => 'Stone Studies',
            'description' => 'Travertine, marble, mineral tone, edge, and care.',
            'keywords' => 'material stone travertine marble mineral limestone care surface',
            'asset' => 'products/f10-elara-travertine-coffee-table-v1.webp',
            'route' => 'frontend.veylune.editions.page',
            'routeParams' => ['focus' => 'stone'],
        ],
        [
            'key' => 'material:wood',
            'type' => 'Material',
            'title' => 'Wood Studies',
            'description' => 'Oak, grain, joinery, finish, and structural warmth.',
            'keywords' => 'material wood oak timber grain joinery finish surface',
            'asset' => 'products/f11-varo-oak-coffee-table-v1.webp',
            'route' => 'frontend.veylune.editions.page',
            'routeParams' => ['focus' => 'wood'],
        ],
        [
            'key' => 'material:textile',
            'type' => 'Material',
            'title' => 'Textile Studies',
            'description' => 'Weave, hand, drape, performance, and daily care.',
            'keywords' => 'material textile fabric weave wool linen upholstery care surface',
            'asset' => 'veylune-category-textiles-v1.webp',
            'route' => 'frontend.veylune.editions.page',
            'routeParams' => ['focus' => 'textile'],
        ],
        [
            'key' => 'material:metal',
            'type' => 'Material',
            'title' => 'Metal Studies',
            'description' => 'Bronze, steel, patina, reflection, and structural detail.',
            'keywords' => 'material metal bronze steel brass patina reflection surface',
            'asset' => 'products/f06-noma-metal-dining-chair-v1.webp',
            'route' => 'frontend.veylune.editions.page',
            'routeParams' => ['focus' => 'metal'],
        ],
        [
            'key' => 'style:founder-selection',
            'type' => 'Style',
            'title' => 'Founder Selection',
            'description' => 'A composed edit of forms, relationships, and quiet contrast.',
            'keywords' => 'style collection founder selection modern quiet sculptural minimal living',
            'asset' => 'veylune-promo-living-room-v1.webp',
            'route' => 'frontend.veylune.discovery.collection',
            'routeParams' => ['collectionKey' => 'founder-selection'],
        ],
        [
            'key' => 'style:new-arrivals',
            'type' => 'Style',
            'title' => 'New Arrivals',
            'description' => 'The latest governed additions to the Veylune edit.',
            'keywords' => 'style collection new arrivals latest current edit',
            'asset' => 'veylune-home-hero-architectural-warm-v1.webp',
            'route' => 'frontend.veylune.discovery.collection',
            'routeParams' => ['collectionKey' => 'new-arrivals'],
        ],
        [
            'key' => 'style:best-sellers',
            'type' => 'Style',
            'title' => 'Best Sellers',
            'description' => 'Frequently considered forms held inside a curated context.',
            'keywords' => 'style collection best sellers popular considered curated',
            'asset' => 'veylune-room-living-v1.webp',
            'route' => 'frontend.veylune.discovery.collection',
            'routeParams' => ['collectionKey' => 'best-sellers'],
        ],
        [
            'key' => 'style:permanent',
            'type' => 'Style',
            'title' => 'Permanent Collections',
            'description' => 'Longer-lived forms with stable material and spatial relevance.',
            'keywords' => 'style collection permanent timeless long term archive',
            'asset' => 'veylune-room-living-v1.webp',
            'route' => 'frontend.veylune.discovery.collection.permanent',
            'routeParams' => [],
        ],
        [
            'key' => 'style:editorial',
            'type' => 'Style',
            'title' => 'Editorial Collections',
            'description' => 'Room narratives shaped through atmosphere and point of view.',
            'keywords' => 'style collection editorial inspiration mood atmosphere narrative quiet',
            'asset' => 'veylune-promo-living-room-v1.webp',
            'route' => 'frontend.veylune.discovery.collection.editorial',
            'routeParams' => [],
        ],
        [
            'key' => 'maker:atelier-partnerships',
            'type' => 'Maker',
            'title' => 'Atelier Partnerships',
            'description' => 'The studios, disciplines, and making relationships behind the work.',
            'keywords' => 'maker makers atelier studio craft designer designers collaboration partnership manufacturing',
            'asset' => 'veylune-room-workspace-v1.webp',
            'route' => 'frontend.veylune.partnership.page',
            'routeParams' => [],
        ],
        [
            'key' => 'project:private-consultation',
            'type' => 'Project',
            'title' => 'Private Consultation',
            'description' => 'Material direction and sourcing for residential and professional projects.',
            'keywords' => 'project consultation architect interior designer residential service trade sourcing room home',
            'asset' => 'veylune-room-dining-v1.webp',
            'route' => 'frontend.veylune.consultation.page',
            'routeParams' => [],
        ],
    ];

    public function __construct(
        private readonly GenericPageLoader $genericPageLoader,
        private readonly EditorialMediaResolver $editorialMediaResolver
    )
    {
    }

    #[Route(path: '/discover', name: 'frontend.veylune.living_index.search', methods: [Request::METHOD_GET])]
    public function search(Request $request, SalesChannelContext $context): Response
    {
        $query = trim(mb_substr((string) $request->query->get('q', ''), 0, 120));
        $activeAxis = strtolower(trim((string) $request->query->get('axis', '')));
        $sort = strtolower(trim((string) $request->query->get('sort', 'relevance')));

        if (!isset(self::AXES[$activeAxis])) {
            $activeAxis = '';
        }

        if (!in_array($sort, ['relevance', 'title', 'type'], true)) {
            $sort = 'relevance';
        }

        $rankedResults = $this->rankedResults($query);
        $facetCounts = array_fill_keys(array_keys(self::AXES), 0);

        foreach ($rankedResults as $entry) {
            ++$facetCounts[strtolower($entry['type'])];
        }

        $results = $activeAxis === ''
            ? $rankedResults
            : array_values(array_filter(
                $rankedResults,
                static fn (array $entry): bool => strtolower($entry['type']) === $activeAxis
            ));

        if ($sort === 'title') {
            usort($results, static fn (array $left, array $right): int => strcasecmp($left['title'], $right['title']));
        } elseif ($sort === 'type') {
            usort($results, static function (array $left, array $right): int {
                $typeOrder = strcasecmp($left['type'], $right['type']);

                return $typeOrder !== 0 ? $typeOrder : strcasecmp($left['title'], $right['title']);
            });
        }

        $results = array_map(function (array $entry) use ($context): array {
            $entry['media'] = $this->editorialMediaResolver->resolve(
                $this->editorialDestinationId($entry),
                (string) $entry['asset'],
                (string) $entry['title'] . ' context',
                $context->getContext()
            );

            return $entry;
        }, $results);

        $page = $this->genericPageLoader->load($request, $context);
        $page->getMetaInformation()?->setMetaTitle($query === '' ? 'Search the Living Index' : sprintf('Search: %s', $query));
        $page->getMetaInformation()?->setMetaDescription('Search Veylune by object, room, material, style, maker, or project.');
        $page->getMetaInformation()?->setCanonical($request->getSchemeAndHttpHost() . '/discover');
        $page->getMetaInformation()?->setRobots($query === '' && $activeAxis === '' && $sort === 'relevance' ? 'index,follow' : 'noindex,follow');

        return $this->renderStorefront('@VeyluneTheme/storefront/veylune/living-index-search.html.twig', [
            'page' => $page,
            'veyluneSearchQuery' => $query,
            'veyluneSearchResults' => $results,
            'veyluneSearchTotal' => count($results),
            'veyluneSearchMatchTotal' => array_sum($facetCounts),
            'veyluneSearchActiveAxis' => $activeAxis,
            'veyluneSearchSort' => $sort,
            'veyluneSearchAxes' => self::AXES,
            'veyluneSearchFacetCounts' => $facetCounts,
        ]);
    }

    #[Route(path: '/selection', name: 'frontend.veylune.living_index.selection', methods: [Request::METHOD_GET])]
    public function selection(Request $request, SalesChannelContext $context): Response
    {
        $page = $this->genericPageLoader->load($request, $context);
        $page->getMetaInformation()?->setMetaTitle('Your Veylune Selection');
        $page->getMetaInformation()?->setMetaDescription('A private context board for Veylune objects, rooms, materials, styles, makers, and projects.');
        $page->getMetaInformation()?->setCanonical($request->getSchemeAndHttpHost() . '/selection');
        $page->getMetaInformation()?->setRobots('noindex,follow');

        return $this->renderStorefront('@VeyluneTheme/storefront/veylune/living-index-selection.html.twig', [
            'page' => $page,
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function rankedResults(string $query): array
    {
        $needle = $this->normalize($query);

        if ($needle === '') {
            return self::ENTRIES;
        }

        $requiredTokens = array_values(array_unique(array_filter(explode(' ', $needle))));
        $searchTokens = $requiredTokens;

        foreach ($requiredTokens as $token) {
            foreach (self::QUERY_EXPANSIONS[$token] ?? [] as $expandedToken) {
                $searchTokens[] = $expandedToken;
            }
        }

        $searchTokens = array_values(array_unique($searchTokens));
        $ranked = [];
        $requiredThreshold = max(1, (int) ceil(count($requiredTokens) * 0.6));

        foreach (self::ENTRIES as $entry) {
            $title = $this->normalize($entry['title']);
            $type = $this->normalize($entry['type']);
            $description = $this->normalize($entry['description']);
            $haystack = $this->normalize($entry['title'] . ' ' . $entry['type'] . ' ' . $entry['description'] . ' ' . $entry['keywords']);
            $requiredMatches = count(array_filter(
                $requiredTokens,
                static fn (string $token): bool => str_contains($haystack, $token)
            ));

            if ($requiredMatches < $requiredThreshold) {
                continue;
            }

            $score = str_contains($title, $needle) ? 60 : 0;
            $score += str_contains($haystack, $needle) ? 30 : 0;

            foreach ($searchTokens as $token) {
                $score += str_contains($title, $token) ? 12 : 0;
                $score += str_contains($type, $token) ? 8 : 0;
                $score += str_contains($description, $token) ? 4 : 0;
                $score += str_contains($haystack, $token) ? 2 : 0;
            }

            $entry['_score'] = $score;
            $ranked[] = $entry;
        }

        usort($ranked, static function (array $left, array $right): int {
            $scoreOrder = $right['_score'] <=> $left['_score'];

            return $scoreOrder !== 0 ? $scoreOrder : strcasecmp($left['title'], $right['title']);
        });

        return array_map(static function (array $entry): array {
            unset($entry['_score']);

            return $entry;
        }, $ranked);
    }

    private function normalize(string $value): string
    {
        $normalized = preg_replace('/[^\p{L}\p{N}]+/u', ' ', mb_strtolower($value));

        return trim((string) $normalized);
    }

    /** @param array<string, mixed> $entry */
    private function editorialDestinationId(array $entry): string
    {
        $parameters = $entry['routeParams'] ?? [];

        if (isset($parameters['categoryKey'])) {
            return 'category:' . $parameters['categoryKey'];
        }

        if (isset($parameters['roomKey'])) {
            return 'room:' . $parameters['roomKey'];
        }

        if (isset($parameters['collectionKey'])) {
            return 'collection:' . $parameters['collectionKey'];
        }

        return match ($entry['route'] ?? '') {
            'frontend.veylune.discovery.collection.permanent' => 'collection:permanent-collections',
            'frontend.veylune.discovery.collection.editorial' => 'collection:editorial-collections',
            default => (string) ($entry['key'] ?? 'search:unknown'),
        };
    }
}
