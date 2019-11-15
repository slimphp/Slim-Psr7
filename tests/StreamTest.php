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
            \stream_get_contents($this->pipeFh);
        }
    }

    public function testIsPipe()
    {
        $this->openPipeStream();

        $this->assertTrue($this->pipeStream->isPipe());

        $this->pipeStream->detach();
        $this->assertFalse($this->pipeStream->isPipe());

        $fhFile = \fopen(__FILE__, 'r');
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
        \stream_get_contents($this->pipeFh);

        $this->pipeStream->close();
        $this->pipeFh = null;

        $this->assertFalse($this->pipeStream->isPipe());
    }

    public function testPipeToString()
    {
        $this->openPipeStream();
        $content = \trim((string) $this->pipeStream);

        $this->assertSame('12', $content);
    }

    public function testConvertsToStringPartiallyReadNonSeekableStream()
    {
        $this->openPipeStream();
        $head = $this->pipeStream->read(1);
        $tail = \trim((string) $this->pipeStream);

        $this->assertSame('1', $head);
        $this->assertSame('2', $tail);
    }

    public function testPipeGetContents()
    {
        $this->openPipeStream();

        $contents = \trim($this->pipeStream->getContents());
        $this->assertSame('12', $contents);
    }

    public function testIsWriteable()
    {
        $resource = \fopen('php://temp', 'w');
        $stream = new Stream($resource);

        $this->assertEquals(13, $stream->write('Hello, world!'));

        $this->assertTrue($stream->isWritable());
    }

    public function testIsReadable()
    {
        $resource = \fopen('php://temp', 'r');
        $stream = new Stream($resource);

        $this->assertTrue($stream->isReadable());
        $this->assertFalse($stream->isWritable());
    }

    public function testIsWritableAndReadable()
    {
        $resource = \fopen('php://temp', 'w+');
        $stream = new Stream($resource);

        $stream->write('Hello, world!');

        $this->assertEquals('Hello, world!', $stream);

        $this->assertTrue($stream->isWritable());
        $this->assertTrue($stream->isReadable());
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
        $resource = \fopen('php://temp', 'rw+');
        $stream = new Stream($resource);
        $stream->detach();

        $this->assertNull($stream->getMetadata());
    }

    private function openPipeStream()
    {
        $this->pipeFh = \popen('echo 12', 'r');
        $this->pipeStream = new Stream($this->pipeFh);
    }

    public function testReadOnlyCachedStreamsAreDisallowed()
    {
        $resource = \fopen('php://temp', 'w+');
        $cache =  new Stream(\fopen('php://temp', 'r'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cache stream must be seekable and writable');
        new Stream($resource, $cache);
    }

    public function testNonSeekableCachedStreamsAreDisallowed()
    {
        $resource = \fopen('php://temp', 'w+');
        $cache =  new Stream(\fopen('php://output', 'w'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cache stream must be seekable and writable');

        new Stream($resource, $cache);
    }

    public function testCachedStreamsGetsContentFromTheCache()
    {
        $resource = \popen('echo HelloWorld', 'r');
        $stream = new Stream($resource, new Stream(\fopen('php://temp', 'w+')));

        $this->assertEquals("HelloWorld\n", $stream->getContents());
        $this->assertEquals("HelloWorld\n", $stream->getContents());
    }

    public function testCachedStreamsFillsCacheOnRead()
    {
        $resource = \fopen('data://,0', 'r');
        $stream = new Stream($resource, new Stream(\fopen('php://temp', 'w+')));

        $this->assertEquals("0", $stream->read(100));
        $this->assertEquals("0", $stream->__toString());
    }

    public function testDetachingStreamDropsCache()
    {
        $cache = new Stream(\fopen('php://temp', 'w+'));
        $resource = \fopen('data://,foo', 'r');
        $stream = new Stream($resource, $cache);

        $stream->detach();

        $cacheProperty = new ReflectionProperty(Stream::class, 'cache');
        $cacheProperty->setAccessible(true);
        $finishedProperty = new ReflectionProperty(Stream::class, 'finished');
        $finishedProperty->setAccessible(true);

        $this->assertNull($cacheProperty->getValue($stream));
        $this->assertFalse($finishedProperty->getValue($stream));
    }

    public function testCachedStreamsRewindIfFinishedOnToString()
    {
        $resource = \fopen('data://,foo', 'r');

        $stream = new Stream($resource, new Stream(\fopen('php://temp', 'w+')));

        $this->assertEquals('foo', (string)$stream);
        $this->assertEquals('foo', (string)$stream);
    }
}
