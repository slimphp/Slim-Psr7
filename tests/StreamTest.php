<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Tests\Psr7;

use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use RuntimeException;
use Slim\Psr7\Stream;

class StreamTest extends TestCase
{
    /**
     * @var resource pipe stream file handle
     */
    private $pipeFh;

    /**
     * @var Stream
     */
    private $pipeStream;

    public function tearDown()
    {
        if ($this->pipeFh != null) {
            // prevent broken pipe error message
            stream_get_contents($this->pipeFh);
        }
    }

    public function testIsPipe()
    {
        $this->openPipeStream();

        $this->assertTrue($this->pipeStream->isPipe());

        $this->pipeStream->detach();
        $this->assertFalse($this->pipeStream->isPipe());

        $fhFile = fopen(__FILE__, 'r');
        $fileStream = new Stream($fhFile);
        $this->assertFalse($fileStream->isPipe());
    }

    public function testIsPipeReadable()
    {
        $this->openPipeStream();

        $this->assertTrue($this->pipeStream->isReadable());
    }

    public function testPipeIsNotSeekable()
    {
        $this->openPipeStream();

        $this->assertFalse($this->pipeStream->isSeekable());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testCannotSeekPipe()
    {
        $this->openPipeStream();

        $this->pipeStream->seek(0);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testCannotTellPipe()
    {
        $this->openPipeStream();

        $this->pipeStream->tell();
    }

    /**
     * @expectedException RuntimeException
     */
    public function testCannotRewindPipe()
    {
        $this->openPipeStream();

        $this->pipeStream->rewind();
    }

    public function testPipeGetSizeYieldsNull()
    {
        $this->openPipeStream();

        $this->assertNull($this->pipeStream->getSize());
    }

    public function testClosePipe()
    {
        $this->openPipeStream();

        // prevent broken pipe error message
        stream_get_contents($this->pipeFh);

        $this->pipeStream->close();
        $this->pipeFh = null;

        $this->assertFalse($this->pipeStream->isPipe());
    }

    public function testPipeToString()
    {
        $this->openPipeStream();

        $this->assertSame('', (string) $this->pipeStream);
    }

    public function testPipeGetContents()
    {
        $this->openPipeStream();

        $contents = trim($this->pipeStream->getContents());
        $this->assertSame('12', $contents);
    }

    /**
     * Test that a call to the protected method `attach` would invoke `detach`.
     *
     * @throws ReflectionException
     */
    public function testAttachAgain()
    {
        $this->openPipeStream();

        $streamProphecy = $this->prophesize(Stream::class);

        /** @noinspection PhpUndefinedMethodInspection */
        $streamProphecy->detach()->shouldBeCalled();

        /** @var Stream $stream */
        $stream = $streamProphecy->reveal();

        $streamProperty = new ReflectionProperty(Stream::class, 'stream');
        $streamProperty->setAccessible(true);
        $streamProperty->setValue($stream, $this->pipeFh);

        $attachMethod = new ReflectionMethod(Stream::class, 'attach');
        $attachMethod->setAccessible(true);
        $attachMethod->invoke($stream, $this->pipeFh);
    }

    public function testGetMetaDataReturnsNullIfStreamIsDetached()
    {
        $resource = fopen('php://temp', 'rw+');
        $stream = new Stream($resource);
        $stream->detach();

        $this->assertNull($stream->getMetadata());
    }

    private function openPipeStream()
    {
        $this->pipeFh = popen('echo 12', 'r');
        $this->pipeStream = new Stream($this->pipeFh);
    }
}
