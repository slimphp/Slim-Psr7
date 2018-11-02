<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @link      https://github.com/slimphp/Slim-Psr7
 * @copyright Copyright (c) 2011-2018 Josh Lockhart
 * @license   https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE (MIT License)
 */
namespace Slim\Tests\Psr7\Factory;

use Interop\Http\Factory\ServerRequestFactoryTestCase;
use Slim\Psr7\Environment;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\UriFactory;

class ServerRequestFactoryTest extends ServerRequestFactoryTestCase
{
    /**
     * @return ServerRequestFactory
     */
    protected function createServerRequestFactory()
    {
        return new ServerRequestFactory();
    }

    /**
     * @param string $uri
     * @return \Psr\Http\Message\UriInterface
     */
    protected function createUri($uri)
    {
        return (new UriFactory())->createUri($uri);
    }

    /*******************************************************************************
     * Protocol
     ******************************************************************************/

    public function testGetProtocolVersion()
    {
        $env = Environment::mock(['SERVER_PROTOCOL' => 'HTTP/1.0']);
        $request = $this->createServerRequestFactory()->createServerRequest('GET', '', $env);

        $this->assertEquals('1.0', $request->getProtocolVersion());
    }
}
