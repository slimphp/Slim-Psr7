<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @link      https://github.com/slimphp/Slim-Psr7
 * @copyright Copyright (c) 2011-2018 Josh Lockhart
 * @license   https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE (MIT License)
 */
namespace Slim\Tests\Psr7\Factory;

use Interop\Http\Factory\StreamFactoryTestCase;
use Slim\Psr7\Factory\StreamFactory;

class StreamFactoryTest extends StreamFactoryTestCase
{
    /**
     * @return StreamFactory
     */
    protected function createStreamFactory()
    {
        return new StreamFactory();
    }
}
