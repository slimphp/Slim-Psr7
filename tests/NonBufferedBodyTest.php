<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Tests\Psr7;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Slim\Psr7\NonBufferedBody;
use Slim\Psr7\Response;
use Slim\Tests\Psr7\Assets\HeaderStack;

class NonBufferedBodyTest extends TestCase
{
    protected function setUp()
    {
        HeaderStack::reset();
    }

    protected function tearDown()
    {
        HeaderStack::reset();
    }

    public function testTheStreamContract()
    {
        $body = new NonBufferedBody();
        self::assertSame('', (string) $body, 'Casting to string returns no data, since the class does not store any');
        self::assertNull($body->detach(), 'Returns null since there is no such underlying stream');
        self::assertNull($body->getSize(), 'Current size is undefined');
        self::assertSame(0, $body->tell(), 'Pointer is considered to be at position 0 to conform');
        self::assertTrue($body->eof(), 'Always considered to be at EOF');
        self::assertFalse($body->isSeekable(), 'Cannot seek');
        self::assertTrue($body->isWritable(), 'Body is writable');
        self::assertFalse($body->isReadable(), 'Body is not readable');
        self::assertSame('', $body->getContents(), 'Data cannot be retrieved once written');
        self::assertNull($body->getMetadata(), 'Metadata mechanism is not implemented');
    }

    public function testWithHeader()
    {
        (new Response())
            ->withBody(new NonBufferedBody())
            ->withHeader('Foo', 'Bar');

        self::assertSame([
            [
                'header' => 'Foo: Bar',
                'replace' => true,
                'status_code' => null
            ]
        ], HeaderStack::stack());
    }

    public function testWithAddedHeader()
    {
        (new Response())
            ->withBody(new NonBufferedBody())
            ->withHeader('Foo', 'Bar')
            ->withAddedHeader('Foo', 'Baz');

        self::assertSame([
            [
                'header' => 'Foo: Bar',
                'replace' => true,
                'status_code' => null
            ],
            [
                'header' => 'Foo: Bar,Baz',
                'replace' => true,
                'status_code' => null
            ]
        ], HeaderStack::stack());
    }

    public function testWithoutHeader()
    {
        (new Response())
            ->withBody(new NonBufferedBody())
            ->withHeader('Foo', 'Bar')
            ->withoutHeader('Foo');

        self::assertSame([], HeaderStack::stack());
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage A NonBufferedBody is not closable.
     */
    public function testCloseThrowsRuntimeException()
    {
        (new NonBufferedBody())->close();
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage A NonBufferedBody is not seekable.
     */
    public function testSeekThrowsRuntimeException()
    {
        (new NonBufferedBody())->seek(10);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage A NonBufferedBody is not rewindable.
     */
    public function testRewindThrowsRuntimeException()
    {
        (new NonBufferedBody())->rewind();
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage A NonBufferedBody is not readable.
     */
    public function testReadThrowsRuntimeException()
    {
        (new NonBufferedBody())->read(10);
    }
}
