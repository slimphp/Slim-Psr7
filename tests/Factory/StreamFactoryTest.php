<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE.md (MIT License)
 */

declare(strict_types=1);

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
