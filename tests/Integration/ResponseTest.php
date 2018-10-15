<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @link      https://github.com/slimphp/Slim-Http
 * @copyright Copyright (c) 2011-2017 Josh Lockhart
 * @license   https://github.com/slimphp/Slim-Http/blob/master/LICENSE (MIT License)
 */
namespace Slim\Tests\Http\Integration;

use Http\Psr7Test\ResponseIntegrationTest;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;

class ResponseTest extends ResponseIntegrationTest
{
    use BaseTestFactories;

    /**
     * @return ResponseInterface that is used in the tests
     */
    public function createSubject()
    {
        return new Response();
    }
}
