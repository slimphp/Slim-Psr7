<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Tests\Psr7\Factory;

use Interop\Http\Factory\ServerRequestFactoryTestCase;
use InvalidArgumentException;
use Psr\Http\Message\UriInterface;
use Slim\Psr7\Environment;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\UriFactory;
use Slim\Psr7\UploadedFile;
use stdClass;

class ServerRequestFactoryTest extends ServerRequestFactoryTestCase
{
    /**
     * @return ServerRequestFactory
     */
    protected function createServerRequestFactory()
    {
        return new ServerRequestFactory();
    }

    /**
     * @param string $uri
     * @return UriInterface
     */
    protected function createUri($uri)
    {
        return (new UriFactory())->createUri($uri);
    }

    public function testGetProtocolVersion()
    {
        $env = Environment::mock(['SERVER_PROTOCOL' => 'HTTP/1.0']);
        $request = $this->createServerRequestFactory()->createServerRequest('GET', '', $env);

        $this->assertEquals('1.0', $request->getProtocolVersion());
    }

    public function testCreateFromGlobals()
    {
        $_SERVER = Environment::mock([
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'HTTP_ACCEPT_CHARSET' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.3',
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.8',
            'HTTP_CONTENT_TYPE' => 'multipart/form-data',
            'HTTP_HOST' => 'example.com:8080',
            'HTTP_USER_AGENT' => 'Slim Framework',
            'PHP_AUTH_PW' => 'sekrit',
            'PHP_AUTH_USER' => 'josh',
            'QUERY_STRING' => 'abc=123',
            'REMOTE_ADDR' => '127.0.0.1',
            'REQUEST_METHOD' => 'POST',
            'REQUEST_TIME' => time(),
            'REQUEST_TIME_FLOAT' => microtime(true),
            'REQUEST_URI' => '/foo/bar',
            'SCRIPT_NAME' => '/index.php',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => 8080,
            'SERVER_PROTOCOL' => 'HTTP/1.1',
        ]);

        $_POST = [
            'def' => '456',
        ];

        $_FILES = [
            'uploaded_file' => [
                'name' => [
                    0 => 'foo.jpg',
                    1 => 'bar.jpg',
                ],

                'type' => [
                    0 => 'image/jpeg',
                    1 => 'image/jpeg',
                ],

                'tmp_name' => [
                    0 => '/tmp/phpUA3XUw',
                    1 => '/tmp/phpXUFS0x',
                ],

                'error' => [
                    0 => 0,
                    1 => 0,
                ],

                'size' => [
                    0 => 358708,
                    1 => 236162,
                ],
            ]
        ];

        $request = ServerRequestFactory::createFromGlobals();

        // Ensure method and protocol version are correct
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('1.1', $request->getProtocolVersion());

        // Uri should be set up correctly
        $uri = $request->getUri();
        $this->assertEquals('josh:sekrit', $uri->getUserInfo());
        $this->assertEquals('example.com', $uri->getHost());
        $this->assertEquals('8080', $uri->getPort());
        $this->assertEquals('/foo/bar', $uri->getPath());
        $this->assertEquals('abc=123', $uri->getQuery());
        $this->assertEquals('', $uri->getFragment());

        // $_POST should be placed into the parsed body
        $this->assertEquals($_POST, $request->getParsedBody());

        // $_FILES should be mapped to an array of UploadedFile objects
        $uploadedFiles = $request->getUploadedFiles();
        $this->assertCount(1, $uploadedFiles);
        $this->assertArrayHasKey('uploaded_file', $uploadedFiles);
        $this->assertInstanceOf(UploadedFile::class, $uploadedFiles['uploaded_file'][0]);
        $this->assertInstanceOf(UploadedFile::class, $uploadedFiles['uploaded_file'][1]);
    }

    public function testCreateFromGlobalsParsesBodyWithFragmentedContentType()
    {
        $_SERVER = Environment::mock([
            'HTTP_CONTENT_TYPE' => 'application/x-www-form-urlencoded;charset=utf-8',
            'REQUEST_METHOD' => 'POST',
        ]);

        $_POST = [
            'def' => '456',
        ];

        $request = ServerRequestFactory::createFromGlobals();

        $this->assertEquals($_POST, $request->getParsedBody());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCreateServerRequestWithNullAsUri()
    {
        $env = Environment::mock();
        $this->createServerRequestFactory()->createServerRequest('GET', null, $env);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCreateServerRequestWithInvalidUriObject()
    {
        $env = Environment::mock();
        $this->createServerRequestFactory()->createServerRequest('GET', new stdClass(), $env);
    }
}
