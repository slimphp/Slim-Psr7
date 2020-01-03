<?php

/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Tests\Psr7\Factory;

use Interop\Http\Factory\RequestFactoryTestCase;
use InvalidArgumentException;
use Psr\Http\Message\UriInterface;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\UriFactory;
use stdClass;

class RequestFactoryTest extends RequestFactoryTestCase
{
    /**
     * @return RequestFactory
     */
    protected function createRequestFactory()
    {
        return new RequestFactory();
    }

    /**
     * @param string $uri
     * @return UriInterface
     */
    protected function createUri($uri)
    {
        return (new UriFactory())->createUri($uri);
    }

    /**
     */
    public function testCreateRequestThrowsExceptionWithInvalidUri()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter 2 of RequestFactory::createRequest() must be a string' .
                                      ' or a compatible UriInterface.');

        $factory = $this->createRequestFactory();

        $factory->createRequest('GET', new stdClass());
    }
}
