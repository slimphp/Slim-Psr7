<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Tests\Psr7;

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Headers;

class HeadersTest extends TestCase
{
    public function testCreateFromGlobals()
    {
        $GLOBALS['getallheaders_return'] = [
            'HTTP_ACCEPT' => 'application/json',
        ];

        $headers = Headers::createFromGlobals();

        unset($GLOBALS['getallheaders_return']);

        $this->assertEquals(['accept' => ['application/json']], $headers->getHeaders());
        $this->assertEquals(['ACCEPT' => ['application/json']], $headers->getHeaders(true));
    }

    public function testCreateFromGlobalsUsesEmptyArrayIfGetAllHeadersReturnsFalse()
    {
        $GLOBALS['getallheaders_return'] = false;

        $headers = Headers::createFromGlobals();

        unset($GLOBALS['getallheaders_return']);

        $this->assertEquals([], $headers->getHeaders());
    }

    public function testAddHeader()
    {
        $headers = new Headers([
            'Accept' => 'application/json',
        ]);

        $headers->addHeader('Accept', 'text/html');

        $this->assertEquals(['application/json', 'text/html'], $headers->getHeader('Accept'));
        $this->assertEquals(['accept' => ['application/json', 'text/html']], $headers->getHeaders());
        $this->assertEquals(['Accept' => ['application/json', 'text/html']], $headers->getHeaders(true));
    }

    public function testRemoveHeader()
    {
        $headers = new Headers([
            'Accept' => 'application/json',
        ]);

        $headers->removeHeader('Accept');

        $this->assertEquals([], $headers->getHeader('Accept'));
        $this->assertEquals([], $headers->getHeaders());
    }

    public function testGetHeader()
    {
        $headers = new Headers([
            'Accept' => ['application/json', 'text/html'],
        ]);

        $this->assertEquals(['application/json', 'text/html'], $headers->getHeader('accept'));
        $this->assertEquals(['application/json', 'text/html'], $headers->getHeader('Accept'));
        $this->assertEquals(['application/json', 'text/html'], $headers->getHeader('HTTP_ACCEPT'));
    }

    public function testSetHeader()
    {
        $headers = new Headers([
            'Content-Length' => 0,
        ]);

        $headers->setHeader('Content-Length', 100);

        $this->assertSame(['100'], $headers->getHeader('Content-Length'));
        $this->assertEquals(['content-length' => ['100']], $headers->getHeaders());
        $this->assertEquals(['Content-Length' => ['100']], $headers->getHeaders(true));
    }

    public function testSetHeaders()
    {
        $headers = new Headers([
            'Content-Length' => 0,
        ]);

        $headers->setHeaders([
            'Accept' => 'application/json',
        ]);

        $this->assertEquals(['accept' => ['application/json']], $headers->getHeaders());
        $this->assertEquals(['Accept' => ['application/json']], $headers->getHeaders(true));
    }

    public function testHasHeader()
    {
        $headers = new Headers([
            'Accept' => 'application/json',
        ]);

        $this->assertTrue($headers->hasHeader('accept'));
        $this->assertTrue($headers->hasHeader('Accept'));
        $this->assertTrue($headers->hasHeader('HTTP_ACCEPT'));
    }

    public function testGetHeaders()
    {
        $headers = new Headers([
            'HTTP_ACCEPT' => 'text/html',
            'HTTP_CONTENT_TYPE' => 'application/json',
        ]);

        $expectedNormalizedHeaders = [
            'accept' => ['text/html'],
            'content-type' => ['application/json'],
        ];

        $this->assertEquals($expectedNormalizedHeaders, $headers->getHeaders());
    }

    public function testGetHeadersPreservesOriginalCase()
    {
        $headers = new Headers([
            'HTTP_ACCEPT' => 'text/html',
            'HTTP_CONTENT_TYPE' => 'application/json',
        ]);

        $expectedOriginalHeaders = [
            'ACCEPT' => ['text/html'],
            'CONTENT-TYPE' => ['application/json'],
        ];

        $this->assertEquals($expectedOriginalHeaders, $headers->getHeaders(true));
    }
}
