<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @link      https://github.com/slimphp/Slim-Psr7
 * @copyright Copyright (c) 2011-2017 Josh Lockhart
 * @license   https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE (MIT License)
 */
namespace Slim\Tests\Psr7\Integration;

use Psr\Http\Message\UriInterface;
use Slim\Psr7\Factory\UriFactory;
use Slim\Psr7\Stream;
use Slim\Psr7\UploadedFile;

trait BaseTestFactories
{

    /**
     * @param $uri
     * @return UriInterface
     */
    protected function buildUri($uri)
    {
        return (new UriFactory())->createUri($uri);
    }

    /**
     * @param $data
     * @return Stream
     */
    protected function buildStream($data)
    {
        if (!is_resource($data)) {
            $h = fopen('php://temp', 'w+');
            fwrite($h, $data);

            $data = $h;
        }

        return new Stream($data);
    }

    /**
     * @param $data
     * @return UploadedFile
     */
    protected function buildUploadableFile($data)
    {
        return new UploadedFile($data);
    }
}
