<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Psr7\Factory;

use InvalidArgumentException;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Slim\Psr7\Stream;

class StreamFactory implements StreamFactoryInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws RuntimeException
     */
    public function createStream(string $content = ''): StreamInterface
    {
        $resource = fopen('php://temp', 'rw+');

        if (!is_resource($resource)) {
            throw new RuntimeException('StreamFactory::createStream() could not open temporary file stream.');
        }

        fwrite($resource, $content);
        rewind($resource);

        return $this->createStreamFromResource($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        $resource = @fopen($filename, $mode);

        if (!is_resource($resource)) {
            throw new RuntimeException(
                "StreamFactory::createStreamFromFile() could not create resource from file `$filename`"
            );
        }

        return new Stream($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        if (!is_resource($resource)) {
            throw new InvalidArgumentException(
                'Parameter 1 of StreamFactory::createStreamFromResource() must be a resource.'
            );
        }

        return new Stream($resource);
    }
}
