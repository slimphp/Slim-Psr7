<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @link      https://github.com/slimphp/Slim-Psr7
 * @copyright Copyright (c) 2011-2018 Josh Lockhart
 * @license   https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE (MIT License)
 */
namespace Slim\Tests\Psr7\Factory;

use Interop\Http\Factory\ResponseFactoryTestCase;
use Slim\Psr7\Factory\ResponseFactory;

class ResponseFactoryTest extends ResponseFactoryTestCase
{
    /**
     * @return ResponseFactory
     */
    protected function createResponseFactory()
    {
        return new ResponseFactory();
    }
}
