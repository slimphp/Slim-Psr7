<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Tests\Psr7\Factory;

use Interop\Http\Factory\UploadedFileFactoryTestCase;
use Prophecy\Argument\Token\ExactValueToken;
use Prophecy\Prophecy\MethodProphecy;
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
     * @param string $argKey      Argument for the method prophecy.
     * @param mixed  $returnValue Return value of the `getMetadata` method.
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    protected function prophesizeStreamInterfaceWithGetMetadataMethod(string $argKey, $returnValue): StreamInterface
    {
        $prophecy = $this->prophesize(StreamInterface::class);
        $mp       = new MethodProphecy($prophecy, 'getMetadata', [new ExactValueToken($argKey)]);
        $mp->shouldBeCalled();
        $mp->willReturn($returnValue);

        /** @var StreamInterface $upload */
        $upload = $prophecy->reveal();
        return $upload;
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage File is not readable.
     */
    public function testCreateUploadedFileWithInvalidUri()
    {
        $this->factory->createUploadedFile($this->prophesizeStreamInterfaceWithGetMetadataMethod('uri', null));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage File is not readable.
     */
    public function testCreateUploadedFileWithNonReadableFile()
    {
        $this->factory->createUploadedFile($this->prophesizeStreamInterfaceWithGetMetadataMethod('uri', 'non-readable'));
    }
}
