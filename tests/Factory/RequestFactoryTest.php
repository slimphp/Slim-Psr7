<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @link      https://github.com/slimphp/Slim-Psr7
 * @copyright Copyright (c) 2011-2018 Josh Lockhart
 * @license   https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE (MIT License)
 */
namespace Slim\Tests\Psr7\Factory;

use Interop\Http\Factory\RequestFactoryTestCase;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\UriFactory;

class RequestFactoryTest extends RequestFactoryTestCase
{
    /**
     * @return RequestFactory
     */
    protected function createRequestFactory()
    {
        return new RequestFactory();
    }

    /**
     * @param string $uri
     * @return \Psr\Http\Message\UriInterface
     */
    protected function createUri($uri)
    {
        return (new UriFactory())->createUri($uri);
    }
}
