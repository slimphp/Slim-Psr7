<?php

/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Tests\Psr7;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Header;

class HeaderTest extends TestCase
{
    /**
     * Instantiate a default header.
     *
     * @return Header
     */
    protected function headerFactory(): Header
    {
        $originalName = 'ACCEPT';
        $normalizedName = 'accept';
        $values = ['application/json'];

        return new Header($originalName, $normalizedName, $values);
    }

    public function testGetOriginalName()
    {
        $header = $this->headerFactory();
        $this->assertEquals('ACCEPT', $header->getOriginalName());
    }

    public function testGetNormalizedName()
    {
        $header = $this->headerFactory();
        $this->assertEquals('accept', $header->getNormalizedName());
    }

    public function testAddValue()
    {
        $header = $this->headerFactory();
        $header2 = $header->addValue('text/html');

        $this->assertEquals(['application/json', 'text/html'], $header->getValues());
        $this->assertSame($header2, $header);
    }

    public function testAddValuesString()
    {
        $header = $this->headerFactory();
        $header2 = $header->addValues('text/html');

        $this->assertEquals(['application/json', 'text/html'], $header->getValues());
        $this->assertSame($header2, $header);
    }

    public function testAddValuesNull()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter 1 of Header::addValues() should be a string or an array.');

        $header = $this->headerFactory();
        $header->addValues(null);
    }
}
