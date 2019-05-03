<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Tests\Psr7\Factory;

use InvalidArgumentException;
use Interop\Http\Factory\StreamFactoryTestCase;
use RuntimeException;
use Slim\Psr7\Factory\StreamFactory;

class StreamFactoryTest extends StreamFactoryTestCase
{
    public function tearDown()
    {
        if (isset($GLOBALS['fopen_return'])) {
            unset($GLOBALS['fopen_return']);
        }
    }

    /**
     * @return StreamFactory
     */
    protected function createStreamFactory()
    {
        return new StreamFactory();
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage StreamFactory::createStream() could not open temporary file stream.
     */
    public function testCreateStreamThrowsRuntimeException()
    {
        $GLOBALS['fopen_return'] = false;

        $factory = $this->createStreamFactory();

        $factory->createStream();
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage StreamFactory::createStreamFromFile() could not create resource
     *                           from file `non-readable`
     */
    public function testCreateStreamFromFileThrowsRuntimeException()
    {
        $GLOBALS['fopen_return'] = false;

        $factory = $this->createStreamFactory();

        $factory->createStreamFromFile('non-readable');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Parameter 1 of StreamFactory::createStreamFromResource() must be a resource.
     */
    public function testCreateStreamFromResourceThrowsRuntimeException()
    {
        $factory = $this->createStreamFactory();

        $factory->createStreamFromResource('not-resource');
    }
}
