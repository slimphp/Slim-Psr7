<?php

/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Tests\Psr7\Integration;

use Http\Psr7Test\UploadedFileIntegrationTest;
use Psr\Http\Message\UploadedFileInterface;
use Slim\Psr7\UploadedFile;

use function sys_get_temp_dir;
use function tempnam;

class UploadedFileTest extends UploadedFileIntegrationTest
{
    use BaseTestFactories;

    /**
     * @return UploadedFileInterface
     */
    public function createSubject()
    {
        $file = tempnam(sys_get_temp_dir(), 'Slim_Http_UploadedFileTest_');

        return new UploadedFile($file);
    }
}
