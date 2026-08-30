<?php declare(strict_types=1);

namespace VeyluneTheme\Subscriber;

use Shopware\Core\Framework\Event\BeforeSendRedirectResponseEvent;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\PlatformRequest;
use Shopware\Storefront\Framework\Routing\RequestTransformer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use VeyluneTheme\Storefront\StorefrontRoleRegistry;
use VeyluneTheme\Storefront\StorefrontRouteOwnershipPolicy;

#[Package('storefront')]
final class IdentityIngressAllowlistSubscriber implements EventSubscriberInterface
{
    private const CANONICAL_PUBLIC_STOREFRONT_HOSTS = [
        'veylune-shopware.ddev.site',
        'veylune.com',
    ];

    private const CONSULTATION_CATEGORY_ID = '019e4718d96272ac9fbbe508ccc6c6a6';

    private const CONTACT_PAGE_ID = '019e3bf907a971b3a48974fb8e7f7fbe';

    private const IMPRINT_PAGE_ID = '019e3bf90c8271f2ab585548d3e747f9';

    private const PRIVACY_PAGE_ID = '019e3bf90c7f7226a8cb32e3f0ceac6b';

    private const ALLOWED_ROUTE_NAMES = [
        'frontend.home.page',
        'frontend.veylune.editions.page',
        'frontend.veylune.editions.page.de',
        'frontend.veylune.editions.detail.guard',
        'frontend.veylune.editions.detail.guard.de',
        'frontend.veylune.partnership.page',
        'frontend.veylune.consultation.page',
        'frontend.veylune.trade.page',
        'frontend.veylune.studio.page',
        'frontend.veylune.legal.page',
        'frontend.veylune.contact.page',
        'frontend.veylune.discovery.room',
        'frontend.veylune.discovery.category',
        'frontend.veylune.discovery.collection',
        'frontend.veylune.discovery.collection.permanent',
        'frontend.veylune.discovery.collection.editorial',
        'frontend.veylune.preview.catalog.home',
        'frontend.veylune.preview.catalog.category',
        'frontend.veylune.preview.catalog.room',
        'frontend.veylune.preview.catalog.collection',
        'frontend.veylune.preview.catalog.product',
        'frontend.veylune.preview.cart',
        'frontend.veylune.preview.checkout',
        'frontend.veylune.preview.account',
        'frontend.form.contact.send',
        'frontend.captcha.basic-captcha.load',
        'frontend.captcha.basic-captcha.validate',
        'frontend.cookie.offcanvas',
        'frontend.cookie.permission',
        'frontend.cookie.consent.offcanvas',
        'frontend.cookie.groups',
        'frontend.robots.txt',
        'frontend.sitemap.xml',
        'frontend.sitemap.proxy',
        'frontend.header',
        'frontend.footer',
    ];

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['enforceAllowlist', -5],
            KernelEvents::EXCEPTION => 'enforceNotFoundDenial',
            BeforeSendRedirectResponseEvent::class => 'enforceCanonicalRedirectAllowlist',
        ];
    }

    public function enforceNotFoundDenial(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        $throwable = $event->getThrowable();

        if (!$event->isMainRequest()
            || !$this->isIdentityIngressRequest($request)
            || !$throwable instanceof HttpExceptionInterface
            || $throwable->getStatusCode() !== Response::HTTP_NOT_FOUND
        ) {
            return;
        }

        $event->setResponse($this->deniedResponse($request));
    }

    public function enforceCanonicalRedirectAllowlist(BeforeSendRedirectResponseEvent $event): void
    {
        $request = $event->getRequest();

        if (!$this->isCanonicalPublicStorefrontRequest($request)) {
            return;
        }

        if ($request->attributes->get('resolved-uri') === '/navigation/' . self::CONSULTATION_CATEGORY_ID) {
            return;
        }

        $event->setResponse($this->deniedResponse($request));
    }

    public function enforceAllowlist(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $originalRequestUri = (string) $request->attributes->get(RequestTransformer::ORIGINAL_REQUEST_URI, $request->getRequestUri());
        $originalPath = parse_url($originalRequestUri, \PHP_URL_PATH) ?: $request->getPathInfo();

        if ($this->isCanonicalPublicStorefrontRequest($request) && $originalPath === '/consultation') {
            $event->setResponse(new RedirectResponse('/private-consultation', Response::HTTP_MOVED_PERMANENTLY));

            return;
        }

        if ($this->isCanonicalPublicStorefrontRequest($request)) {
            $publicAliasCanonicalPath = match ($originalPath) {
                '/journal', '/inspiration' => '/editions',
                '/about' => '/about-studio',
                default => null,
            };

            if ($publicAliasCanonicalPath !== null) {
                $event->setResponse(new RedirectResponse($publicAliasCanonicalPath, Response::HTTP_MOVED_PERMANENTLY));

                return;
            }
        }

        if ($this->isCanonicalPublicStorefrontRequest($request)) {
            $legacyCmsCanonicalPath = match ($originalPath) {
                '/page/cms/' . self::CONTACT_PAGE_ID => '/contact-studio',
                '/page/cms/' . self::IMPRINT_PAGE_ID => '/legal/imprint',
                '/page/cms/' . self::PRIVACY_PAGE_ID => '/legal/privacy',
                default => null,
            };

            if ($legacyCmsCanonicalPath !== null) {
                $event->setResponse(new RedirectResponse($legacyCmsCanonicalPath, Response::HTTP_MOVED_PERMANENTLY));

                return;
            }
        }

        if ($this->isCanonicalPublicStorefrontApiRequest($request)) {
            $event->setResponse($this->deniedResponse($request));

            return;
        }

        if (!$this->isCanonicalPublicStorefrontRequest($request)) {
            return;
        }

        if ($this->isActivationPendingRoute($request)) {
            $event->setResponse($this->deniedResponse($request));

            return;
        }

        if ($this->isAllowed($request)) {
            return;
        }

        $event->setResponse($this->deniedResponse($request));
    }

    private function isCanonicalPublicStorefrontApiRequest(Request $request): bool
    {
        if (!$this->isIdentityIngressRequest($request)) {
            return false;
        }

        $path = $request->getPathInfo();

        return $path === '/api'
            || str_starts_with($path, '/api/')
            || $path === '/store-api'
            || str_starts_with($path, '/store-api/');
    }

    private function isIdentityIngressRequest(Request $request): bool
    {
        return \in_array($request->getHost(), self::CANONICAL_PUBLIC_STOREFRONT_HOSTS, true)
            || $this->isCanonicalPublicStorefrontRequest($request);
    }

    private function isCanonicalPublicStorefrontRequest(Request $request): bool
    {
        return StorefrontRoleRegistry::isCanonicalPublicStorefront(
            (string) $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID)
        );
    }

    private function isAllowed(Request $request): bool
    {
        $route = (string) $request->attributes->get('_route');

        if (str_starts_with($route, 'frontend.account.')) {
            return true;
        }

        if (\in_array($route, self::ALLOWED_ROUTE_NAMES, true)) {
            return true;
        }

        if ($route === 'frontend.navigation.page') {
            return $request->attributes->get('navigationId') === self::CONSULTATION_CATEGORY_ID;
        }

        if ($route === 'frontend.cms.page.full' || $route === 'frontend.cms.page') {
            return \in_array($request->attributes->get('id'), $this->allowedCmsPageIds(), true);
        }

        if ($route === 'frontend.maintenance.singlepage') {
            return \in_array($request->attributes->get('id'), [self::IMPRINT_PAGE_ID, self::PRIVACY_PAGE_ID], true);
        }

        return false;
    }

    private function isActivationPendingRoute(Request $request): bool
    {
        $navigationId = $request->attributes->get('navigationId');

        return StorefrontRouteOwnershipPolicy::isActivationPendingRoute(
            (string) $request->attributes->get('_route'),
            \is_string($navigationId) ? $navigationId : null
        );
    }

    /**
     * @return list<string>
     */
    private function allowedCmsPageIds(): array
    {
        return [
            self::CONTACT_PAGE_ID,
            self::IMPRINT_PAGE_ID,
            self::PRIVACY_PAGE_ID,
        ];
    }

    private function deniedResponse(Request $request): Response
    {
        $originalRequestUri = (string) $request->attributes->get(RequestTransformer::ORIGINAL_REQUEST_URI, $request->getRequestUri());
        $originalPath = parse_url($originalRequestUri, \PHP_URL_PATH) ?: $request->getPathInfo();

        $isGerman = str_starts_with($originalPath, '/de/')
            || $originalPath === '/de'
            || str_starts_with($request->getLocale(), 'de');

        $title = $isGerman ? 'Seite nicht gefunden' : 'Page not found';
        $text = $isGerman
            ? 'Die angeforderte Seite ist nicht Teil des oeffentlichen Veylune Studios.'
            : 'The requested page is not part of the public Veylune Studio.';
        $eyebrow = $isGerman ? 'Kontrollierter Zugang' : 'Governed access';
        $action = $isGerman ? 'Zum Studio' : 'Return to the studio';

        $html = sprintf(
            <<<'HTML'
                <!DOCTYPE html>
                <html lang="%s">
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <meta name="robots" content="noindex,nofollow">
                        <title>%s | VEYLUNE STUDIO</title>
                        <style>
                            :root { color-scheme: light; }
                            * { box-sizing: border-box; }
                            body { margin: 0; color: #25221d; background: #f4f0e8; font-family: Inter, "Helvetica Neue", Arial, sans-serif; }
                            main { display: grid; min-height: 100svh; place-items: center; padding: clamp(1.25rem, 5vw, 5rem); }
                            article { width: min(100%%, 58rem); padding: clamp(2rem, 7vw, 6rem); background: #27241f; color: #f5efe4; }
                            .eyebrow { margin: 0 0 1.5rem; color: #cbbb9f; font-size: .68rem; letter-spacing: .18em; text-transform: uppercase; }
                            h1 { max-width: 10ch; margin: 0; font-family: "Iowan Old Style", Baskerville, Georgia, serif; font-size: clamp(3rem, 9vw, 7rem); font-weight: 400; line-height: .92; letter-spacing: -.04em; }
                            .copy { max-width: 38rem; margin: 1.75rem 0 2.25rem; color: rgba(245,239,228,.72); font-size: clamp(1rem, 2vw, 1.2rem); line-height: 1.65; }
                            a { display: inline-flex; align-items: center; min-height: 44px; padding-bottom: .2rem; color: #f5efe4; border-bottom: 1px solid rgba(245,239,228,.45); font-size: .72rem; letter-spacing: .12em; text-decoration: none; text-transform: uppercase; }
                            a:focus-visible { outline: 2px solid #cbbb9f; outline-offset: 6px; }
                            @media (max-width: 30rem) { article { padding-inline: 1.5rem; } }
                        </style>
                    </head>
                    <body>
                        <main data-veylune-identity-denial>
                            <article>
                                <p class="eyebrow">VEYLUNE STUDIO · %s</p>
                                <h1>%s</h1>
                                <p class="copy">%s</p>
                                <a href="/">%s</a>
                            </article>
                        </main>
                    </body>
                </html>
                HTML,
            $isGerman ? 'de' : 'en',
            $title,
            $eyebrow,
            $title,
            $text,
            $action
        );

        return new Response($html, Response::HTTP_NOT_FOUND, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Cache-Control' => 'no-store, private',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }
}
