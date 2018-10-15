<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @link      https://github.com/slimphp/Slim-Http
 * @copyright Copyright (c) 2011-2017 Josh Lockhart
 * @license   https://github.com/slimphp/Slim-Http/blob/master/LICENSE (MIT License)
 */
namespace Slim\Tests\Http;

use PHPUnit\Framework\TestCase;
use Slim\Http\Environment;

class EnvironmentTest extends TestCase
{
    /**
     * Test environment from mock data
     */
    public function testMock()
    {
        $env = Environment::mock([
            'SCRIPT_NAME' => '/foo/bar/index.php',
            'REQUEST_URI' => '/foo/bar?abc=123',
        ]);

        $this->assertEquals('/foo/bar/index.php', $env['SCRIPT_NAME']);
        $this->assertEquals('/foo/bar?abc=123', $env['REQUEST_URI']);
        $this->assertEquals('localhost', $env['HTTP_HOST']);
    }

    /**
     * Test environment from mock data with HTTPS
     */
    public function testMockHttps()
    {
        $env = Environment::mock([
            'HTTPS' => 'on'
        ]);

        $this->assertInternalType('array', $env);
        $this->assertEquals('on', $env['HTTPS']);
        $this->assertEquals(443, $env['SERVER_PORT']);
    }

    /**
     * Test environment from mock data with REQUEST_SCHEME
     */
    public function testMockRequestScheme()
    {
        $env = Environment::mock([
            'REQUEST_SCHEME' => 'https'
        ]);

        $this->assertInternalType('array', $env);
        $this->assertEquals('https', $env['REQUEST_SCHEME']);
        $this->assertEquals(443, $env['SERVER_PORT']);
    }
}
