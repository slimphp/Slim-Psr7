<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @link      https://github.com/slimphp/Slim-Http
 * @copyright Copyright (c) 2011-2017 Josh Lockhart
 * @license   https://github.com/slimphp/Slim-Http/blob/master/LICENSE (MIT License)
 */
namespace Slim\Tests\Http\Integration;

use Http\Psr7Test\UriIntegrationTest;
use Psr\Http\Message\UriInterface;
use Slim\Http\Uri;

class UriTest extends UriIntegrationTest
{
    use BaseTestFactories;

    /**
     * @param string $uri
     *
     * @return UriInterface
     */
    public function createUri($uri)
    {
        return Uri::createFromString($uri);
    }
}
