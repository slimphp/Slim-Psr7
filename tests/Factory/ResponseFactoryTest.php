<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE.md (MIT License)
 */

declare(strict_types=1);

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
