<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @link      https://github.com/slimphp/Slim-Psr7
 * @copyright Copyright (c) 2011-2017 Josh Lockhart
 * @license   https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE (MIT License)
 */
namespace Slim\Tests\Psr7\Integration;

use Http\Psr7Test\UriIntegrationTest;
use Psr\Http\Message\UriInterface;
use Slim\Psr7\Factory\UriFactory;

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
        return (new UriFactory())->createUri($uri);
    }
}
