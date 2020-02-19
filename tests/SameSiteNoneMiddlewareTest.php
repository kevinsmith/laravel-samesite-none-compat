<?php

declare(strict_types=1);

namespace KevinSmith\SameSiteNoneCompat\Laravel\Tests;

use Illuminate\Http\Request;
use KevinSmith\SameSiteNoneCompat\Laravel\SameSiteNoneMiddleware;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use function mb_strtolower;

class SameSiteNoneMiddlewareTest extends TestCase
{
    /** @var string */
    private $fallbackSuffix = '__ssn-fallback';

    /** @var SameSiteNoneMiddleware */
    protected $middleware;

    /** @var Request */
    protected $request;

    /** @var Response */
    protected $response;

    protected function setUp() : void
    {
        parent::setUp();

        $this->middleware = new SameSiteNoneMiddleware();
        $this->request    = Request::create('/');
        $this->response   = Response::create();
    }

    /**
     * @test
     */
    public function promoteIncomingFallbackCookies() : void
    {
        $this->addCookieToRequest('cookie_1', '3QZX2D0iBatn9PIXcP7W');
        $this->addCookieToRequest('cookie_2', 'kpahhaiIrQF9ywdcuQtD');
        $this->addCookieToRequest('cookie_2', 'kpahhaiIrQF9ywdcuQtD', true);
        $this->addCookieToRequest('cookie_3', 'NzYYWZ1XXvaqJh4Fnn1H', true);

        $processedRequest = null;

        $this->middleware->handle($this->request, function (Request $request) use (&$processedRequest) : Response {
            $processedRequest = $request;

            return $this->response;
        });

        /** @var Request $processedRequest */
        self::assertInstanceOf(Request::class, $processedRequest);

        self::assertCount(3, $processedRequest->cookies->all());
        self::assertSame('3QZX2D0iBatn9PIXcP7W', $processedRequest->cookies->get('cookie_1'));
        self::assertSame('kpahhaiIrQF9ywdcuQtD', $processedRequest->cookies->get('cookie_2'));
        self::assertSame('NzYYWZ1XXvaqJh4Fnn1H', $processedRequest->cookies->get('cookie_3'));
        self::assertFalse($processedRequest->cookies->has('cookie_2' . $this->fallbackSuffix));
        self::assertFalse($processedRequest->cookies->has('cookie_3' . $this->fallbackSuffix));
    }

    /**
     * @test
     */
    public function createOutgoingFallbackCookies() : void
    {
        $this->addCookieToResponse('cookie_1', 'dUuEjdsIYk86iIgiKFro');
        $this->addCookieToResponse('cookie_2', 'wprRSHWj5V2CY2v1oNyv', true, true);
        $this->addCookieToResponse('cookie_3', 'i3rXl5HLRg3V0yJySvN9', true, true);
        $this->addCookieToResponse('cookie_4', '0PitdbGuHYrX5Yz7WhF0');
        $this->addCookieToResponse('cookie_5', 'DO1FKvXp2juWJFmkVvKz', false, true);

        $response = $this->middleware->handle($this->request, function () : Response {
            return $this->response;
        });

        /** @var Cookie[] $responseCookies */
        $responseCookies = $response->headers->getCookies(ResponseHeaderBag::COOKIES_ARRAY)['example.com']['/'];

        self::assertCount(7, $responseCookies);
        self::assertSame('dUuEjdsIYk86iIgiKFro', $responseCookies['cookie_1']->getValue());

        self::assertSame('wprRSHWj5V2CY2v1oNyv', $responseCookies['cookie_2']->getValue());
        self::assertSame('none', mb_strtolower($responseCookies['cookie_2']->getSameSite()));
        self::assertSame('wprRSHWj5V2CY2v1oNyv', $responseCookies['cookie_2' . $this->fallbackSuffix]->getValue());
        self::assertNull($responseCookies['cookie_2' . $this->fallbackSuffix]->getSameSite());

        self::assertSame('i3rXl5HLRg3V0yJySvN9', $responseCookies['cookie_3']->getValue());
        self::assertSame('none', mb_strtolower($responseCookies['cookie_3']->getSameSite()));
        self::assertSame('i3rXl5HLRg3V0yJySvN9', $responseCookies['cookie_3' . $this->fallbackSuffix]->getValue());
        self::assertNull($responseCookies['cookie_3' . $this->fallbackSuffix]->getSameSite());

        self::assertSame('0PitdbGuHYrX5Yz7WhF0', $responseCookies['cookie_4']->getValue());

        self::assertSame('DO1FKvXp2juWJFmkVvKz', $responseCookies['cookie_5']->getValue());
        self::assertSame('none', mb_strtolower($responseCookies['cookie_5']->getSameSite()));
        self::assertArrayNotHasKey('cookie_5' . $this->fallbackSuffix, $responseCookies);
    }

    private function addCookieToRequest(string $name, string $value, bool $isFallback = false) : void
    {
        if ($isFallback) {
            $name .= $this->fallbackSuffix;
        }

        $this->request->cookies->add([$name => $value]);
    }

    private function addCookieToResponse(string $name, string $value, bool $isSecure = false, bool $isSameSiteNone = false) : void
    {
        $this->response->headers->setCookie(
            new Cookie(
                $name,
                $value,
                0,
                '/',
                'example.com',
                $isSecure,
                false,
                false,
                $isSameSiteNone ? 'None' : null
            )
        );
    }
}
