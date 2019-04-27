<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Tests\Psr7;

use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Slim\Psr7\Body;
use Slim\Psr7\Headers;
use Slim\Psr7\Response;

class ResponseTest extends TestCase
{
    /*******************************************************************************
     * Create
     ******************************************************************************/

    public function testConstructoWithDefaultArgs()
    {
        $response = new Response();

        $this->assertAttributeEquals(200, 'status', $response);
        $this->assertAttributeInstanceOf('\Slim\Psr7\Headers', 'headers', $response);
        $this->assertAttributeInstanceOf('\Psr\Http\Message\StreamInterface', 'body', $response);
    }

    public function testConstructorWithCustomArgs()
    {
        $headers = new Headers();
        $body = new Body(fopen('php://temp', 'r+'));
        $response = new Response(404, $headers, $body);

        $this->assertAttributeEquals(404, 'status', $response);
        $this->assertAttributeSame($headers, 'headers', $response);
        $this->assertAttributeSame($body, 'body', $response);
    }

    public function testDeepCopyClone()
    {
        $headers = new Headers();
        $body = new Body(fopen('php://temp', 'r+'));
        $response = new Response(404, $headers, $body);
        $clone = clone $response;

        $this->assertAttributeEquals('1.1', 'protocolVersion', $clone);
        $this->assertAttributeEquals(404, 'status', $clone);
        $this->assertAttributeNotSame($headers, 'headers', $clone);
        $this->assertAttributeSame($body, 'body', $clone);
    }

    public function testDisableSetter()
    {
        $response = new Response();
        $response->foo = 'bar';

        $this->assertFalse(property_exists($response, 'foo'));
    }

    /*******************************************************************************
     * Status
     ******************************************************************************/

    public function testGetStatusCode()
    {
        $response = new Response();
        $responseStatus = new ReflectionProperty($response, 'status');
        $responseStatus->setAccessible(true);
        $responseStatus->setValue($response, '404');

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testWithStatus()
    {
        $response = new Response();
        $clone = $response->withStatus(302);

        $this->assertAttributeEquals(302, 'status', $clone);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWithStatusInvalidStatusCodeThrowsException()
    {
        $response = new Response();
        $response->withStatus(800);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage ReasonPhrase must be a string
     */
    public function testWithStatusInvalidReasonPhraseThrowsException()
    {
        $response = new Response();
        $response->withStatus(200, null);
    }

    public function testWithStatusEmptyReasonPhrase()
    {
        $responseWithNoMessage = new Response(310);

        $this->assertEquals('', $responseWithNoMessage->getReasonPhrase());
    }

    public function testGetReasonPhrase()
    {
        $response = new Response(404);

        $this->assertEquals('Not Found', $response->getReasonPhrase());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage ReasonPhrase must be supplied for this code
     */
    public function testMustSetReasonPhraseForUnrecognisedCode()
    {
        $response = new Response();
        $response = $response->withStatus(199);
    }

    public function testSetReasonPhraseForUnrecognisedCode()
    {
        $response = new Response();
        $response = $response->withStatus(199, 'Random Message');

        $this->assertEquals('Random Message', $response->getReasonPhrase());
    }

    public function testGetCustomReasonPhrase()
    {
        $response = new Response();
        $clone = $response->withStatus(200, 'Custom Phrase');

        $this->assertEquals('Custom Phrase', $clone->getReasonPhrase());
    }
}
