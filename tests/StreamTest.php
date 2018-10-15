<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @link      https://github.com/slimphp/Slim-Psr7
 * @copyright Copyright (c) 2011-2017 Josh Lockhart
 * @license   https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE (MIT License)
 */
namespace Slim\Tests\Psr7;

use PHPUnit\Framework\TestCase;
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
            stream_get_contents($this->pipeFh); // prevent broken pipe error message
        }
    }

    /**
     * @covers Slim\Psr7\Stream::isPipe
     */
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

    /**
     * @covers Slim\Psr7\Stream::isReadable
     */
    public function testIsPipeReadable()
    {
        $this->openPipeStream();

        $this->assertTrue($this->pipeStream->isReadable());
    }

    /**
     * @covers Slim\Psr7\Stream::isSeekable
     */
    public function testPipeIsNotSeekable()
    {
        $this->openPipeStream();

        $this->assertFalse($this->pipeStream->isSeekable());
    }

    /**
     * @covers Slim\Psr7\Stream::seek
     * @expectedException \RuntimeException
     */
    public function testCannotSeekPipe()
    {
        $this->openPipeStream();

        $this->pipeStream->seek(0);
    }

    /**
     * @covers Slim\Psr7\Stream::tell
     * @expectedException \RuntimeException
     */
    public function testCannotTellPipe()
    {
        $this->openPipeStream();

        $this->pipeStream->tell();
    }

    /**
     * @covers Slim\Psr7\Stream::rewind
     * @expectedException \RuntimeException
     */
    public function testCannotRewindPipe()
    {
        $this->openPipeStream();

        $this->pipeStream->rewind();
    }

    /**
     * @covers Slim\Psr7\Stream::getSize
     */
    public function testPipeGetSizeYieldsNull()
    {
        $this->openPipeStream();

        $this->assertNull($this->pipeStream->getSize());
    }

    /**
     * @covers Slim\Psr7\Stream::close
     */
    public function testClosePipe()
    {
        $this->openPipeStream();

        stream_get_contents($this->pipeFh); // prevent broken pipe error message
        $this->pipeStream->close();
        $this->pipeFh = null;

        $this->assertFalse($this->pipeStream->isPipe());
    }

    /**
     * @covers Slim\Psr7\Stream::__toString
     */
    public function testPipeToString()
    {
        $this->openPipeStream();

        $this->assertSame('', (string) $this->pipeStream);
    }

    /**
     * @covers Slim\Psr7\Stream::getContents
     */

    public function testPipeGetContents()
    {
        $this->openPipeStream();

        $contents = trim($this->pipeStream->getContents());
        $this->assertSame('12', $contents);
    }

    /**
     * Opens the pipe stream
     *
     * @see StreamTest::pipeStream
     */
    private function openPipeStream()
    {
        $this->pipeFh = popen('echo 12', 'r');
        $this->pipeStream = new Stream($this->pipeFh);
    }
}
