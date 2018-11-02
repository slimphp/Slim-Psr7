<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @link      https://github.com/slimphp/Slim-Psr7
 * @copyright Copyright (c) 2011-2018 Josh Lockhart
 * @license   https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE (MIT License)
 */

declare(strict_types=1);

namespace Slim\Psr7\Factory;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Slim\Psr7\UploadedFile;

class UploadedFileFactory implements UploadedFileFactoryInterface
{
    /**
     * Create a new uploaded file.
     *
     * If a size is not provided it will be determined by checking the size of
     * the file.
     *
     * @see http://php.net/manual/features.file-upload.post-method.php
     * @see http://php.net/manual/features.file-upload.errors.php
     *
     * @param StreamInterface $stream Underlying stream representing the
     *     uploaded file content.
     * @param int $size in bytes
     * @param int $error PHP file upload error
     * @param string $clientFilename Filename as provided by the client, if any.
     * @param string $clientMediaType Media type as provided by the client, if any.
     *
     * @return UploadedFileInterface
     *
     * @throws \InvalidArgumentException If the file resource is not readable.
     */
    public function createUploadedFile(
        StreamInterface $stream,
        int $size = null,
        int $error = \UPLOAD_ERR_OK,
        string $clientFilename = null,
        string $clientMediaType = null
    ): UploadedFileInterface {
        $file = $stream->getMetadata('uri');

        if (!is_readable($file)) {
            throw new \InvalidArgumentException('File is not readable');
        }
        if (!isset($size)) {
            $size = $stream->getSize();
        }

        return new UploadedFile($file, $clientFilename, $clientMediaType, $size, $error);
    }
}
