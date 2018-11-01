<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @link      https://github.com/slimphp/Slim-Psr7
 * @copyright Copyright (c) 2011-2017 Josh Lockhart
 * @license   https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE (MIT License)
 */
namespace Slim\Tests\Psr7\Integration;

use Http\Psr7Test\ResponseIntegrationTest;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Response;

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
