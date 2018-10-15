<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @link      https://github.com/slimphp/Slim-Http
 * @copyright Copyright (c) 2011-2017 Josh Lockhart
 * @license   https://github.com/slimphp/Slim-Http/blob/master/LICENSE (MIT License)
 */
namespace Slim\Tests\Http\Integration;

use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Headers;
use Slim\Http\Request;
use Http\Psr7Test\ServerRequestIntegrationTest;

class ServerRequestTest extends ServerRequestIntegrationTest
{
    use BaseTestFactories;

    /**
     * @return ServerRequestInterface that is used in the tests
     */
    public function createSubject()
    {
        return new Request('GET', $this->buildUri('/'), new Headers(), [], $_SERVER, $this->buildStream(''));
    }
}
