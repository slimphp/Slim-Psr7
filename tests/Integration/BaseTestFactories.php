<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @link      https://github.com/slimphp/Slim-Psr7
 * @copyright Copyright (c) 2011-2017 Josh Lockhart
 * @license   https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE (MIT License)
 */
namespace Slim\Tests\Psr7\Integration;

use Slim\Psr7\Stream;
use Slim\Psr7\UploadedFile;
use Slim\Psr7\Uri;

trait BaseTestFactories
{

    /**
     * @param $uri
     * @return Uri
     */
    protected function buildUri($uri)
    {
        return Uri::createFromString($uri);
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
