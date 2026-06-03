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
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StorefrontRouteScope::ID]])]
#[Package('storefront')]
class DiscoveryDestinationController extends StorefrontController
{
    private const ROOMS = [
        'living-room' => ['title' => 'veylune.destination.rooms.livingRoom.title', 'text' => 'veylune.destination.rooms.livingRoom.text'],
        'dining-room' => ['title' => 'veylune.destination.rooms.diningRoom.title', 'text' => 'veylune.destination.rooms.diningRoom.text'],
        'bedroom' => ['title' => 'veylune.destination.rooms.bedroom.title', 'text' => 'veylune.destination.rooms.bedroom.text'],
        'workspace' => ['title' => 'veylune.destination.rooms.workspace.title', 'text' => 'veylune.destination.rooms.workspace.text'],
        'hallway' => ['title' => 'veylune.destination.rooms.hallway.title', 'text' => 'veylune.destination.rooms.hallway.text'],
        'outdoor' => ['title' => 'veylune.destination.rooms.outdoor.title', 'text' => 'veylune.destination.rooms.outdoor.text'],
    ];

    private const COLLECTIONS = [
        'founder-selection' => ['title' => 'veylune.destination.collections.founderSelection.title', 'text' => 'veylune.destination.collections.founderSelection.text'],
        'new-arrivals' => ['title' => 'veylune.destination.collections.newArrivals.title', 'text' => 'veylune.destination.collections.newArrivals.text'],
        'permanent-collections' => ['title' => 'veylune.destination.collections.permanentCollections.title', 'text' => 'veylune.destination.collections.permanentCollections.text'],
        'editorial-collections' => ['title' => 'veylune.destination.collections.editorialCollections.title', 'text' => 'veylune.destination.collections.editorialCollections.text'],
    ];

    private const CATEGORY_SHORTCUTS = [
        ['key' => 'furniture', 'label' => 'veylune.home.discovery.categories.furniture'],
        ['key' => 'lighting', 'label' => 'veylune.home.discovery.categories.lighting'],
        ['key' => 'decor-objects', 'label' => 'veylune.home.discovery.categories.decorObjects'],
        ['key' => 'textiles-rugs', 'label' => 'veylune.home.discovery.categories.textilesRugs'],
        ['key' => 'dining-kitchen', 'label' => 'veylune.home.discovery.categories.diningKitchen'],
        ['key' => 'outdoor', 'label' => 'veylune.home.discovery.categories.outdoor'],
    ];

    public function __construct(
        private readonly GenericPageLoader $genericPageLoader,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route(path: '/rooms/{roomKey}', name: 'frontend.veylune.discovery.room', requirements: ['roomKey' => 'living-room|dining-room|bedroom|workspace|hallway|outdoor'], methods: [Request::METHOD_GET])]
    public function room(string $roomKey, Request $request, SalesChannelContext $context): Response
    {
        $destination = self::ROOMS[$roomKey] ?? null;

        if ($destination === null) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $page = $this->genericPageLoader->load($request, $context);
        $page->getMetaInformation()?->setMetaTitle($this->translator->trans($destination['title']));
        $page->getMetaInformation()?->setMetaDescription($this->translator->trans('veylune.destination.meta.description'));

        return $this->renderStorefront('@Storefront/storefront/veylune/discovery-destination.html.twig', [
            'page' => $page,
            'veyluneDestinationType' => 'room',
            'veyluneDestinationKey' => $roomKey,
            'veyluneDestination' => $destination,
            'veyluneCategoryShortcuts' => self::CATEGORY_SHORTCUTS,
        ]);
    }

    #[Route(path: '/collections/permanent', name: 'frontend.veylune.discovery.collection.permanent', defaults: ['collectionKey' => 'permanent-collections'], methods: [Request::METHOD_GET])]
    #[Route(path: '/collections/editorial', name: 'frontend.veylune.discovery.collection.editorial', defaults: ['collectionKey' => 'editorial-collections'], methods: [Request::METHOD_GET])]
    #[Route(path: '/collections/{collectionKey}', name: 'frontend.veylune.discovery.collection', requirements: ['collectionKey' => 'founder-selection|new-arrivals|permanent-collections|editorial-collections'], methods: [Request::METHOD_GET])]
    public function collection(string $collectionKey, Request $request, SalesChannelContext $context): Response
    {
        $destination = self::COLLECTIONS[$collectionKey] ?? null;

        if ($destination === null) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $page = $this->genericPageLoader->load($request, $context);
        $page->getMetaInformation()?->setMetaTitle($this->translator->trans($destination['title']));
        $page->getMetaInformation()?->setMetaDescription($this->translator->trans('veylune.destination.meta.description'));

        return $this->renderStorefront('@Storefront/storefront/veylune/discovery-destination.html.twig', [
            'page' => $page,
            'veyluneDestinationType' => 'collection',
            'veyluneDestinationKey' => $collectionKey,
            'veyluneDestination' => $destination,
            'veyluneCategoryShortcuts' => [],
        ]);
    }
}
