<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim/blob/4.x/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Psr7\Factory;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Slim\Psr7\Stream;

class StreamFactory implements StreamFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createStream(string $content = ''): StreamInterface
    {
        $resource = fopen('php://temp', 'r+');
        fwrite($resource, $content);
        rewind($resource);

        return $this->createStreamFromResource($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        return $this->createStreamFromResource(fopen($filename, $mode));
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        return new Stream($resource);
    }
}
