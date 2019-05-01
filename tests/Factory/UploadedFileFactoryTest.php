<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Tests\Psr7\Factory;

use Interop\Http\Factory\UploadedFileFactoryTestCase;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Factory\UploadedFileFactory;

class UploadedFileFactoryTest extends UploadedFileFactoryTestCase
{
    /**
     * @return UploadedFileFactory
     */
    protected function createUploadedFileFactory()
    {
        return new UploadedFileFactory();
    }

    /**
     * @return StreamInterface
     */
    protected function createStream($content)
    {
        $file = tempnam(sys_get_temp_dir(), 'Slim_Http_UploadedFileTest_');
        $resource = fopen($file, 'r+');
        fwrite($resource, $content);
        rewind($resource);

        return (new StreamFactory())->createStreamFromResource($resource);
    }

    /**
     * Prophesize a `\Psr\Http\Message\StreamInterface` with a `getMetadata` method prophecy.
     *
     * @param string $argKey Argument for the method prophecy.
     * @param mixed $returnValue Return value of the `getMetadata` method.
     *
     * @return StreamInterface
     */
    protected function prophesizeStreamInterfaceWithGetMetadataMethod(string $argKey, $returnValue): StreamInterface
    {
        $streamProphecy = $this->prophesize(StreamInterface::class);

        /** @noinspection PhpUndefinedMethodInspection */
        $streamProphecy
            ->getMetadata($argKey)
            ->willReturn($returnValue)
            ->shouldBeCalled();

        /** @var StreamInterface $stream */
        $stream = $streamProphecy->reveal();

        return $stream;
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage File is not readable.
     */
    public function testCreateUploadedFileWithInvalidUri()
    {
        $this->factory->createUploadedFile(
            $this->prophesizeStreamInterfaceWithGetMetadataMethod('uri', null)
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage File is not readable.
     */
    public function testCreateUploadedFileWithNonReadableFile()
    {
        $this->factory->createUploadedFile(
            $this->prophesizeStreamInterfaceWithGetMetadataMethod('uri', 'non-readable')
        );
    }
}
