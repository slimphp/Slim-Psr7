<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @link      https://github.com/slimphp/Slim-Http
 * @copyright Copyright (c) 2011-2017 Josh Lockhart
 * @license   https://github.com/slimphp/Slim-Http/blob/master/LICENSE (MIT License)
 */
namespace Slim\Tests\Http;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Slim\Http\Environment;
use Slim\Http\Uri;

class UriTest extends TestCase
{
    public function uriFactory()
    {
        $scheme = 'https';
        $host = 'example.com';
        $port = 443;
        $path = '/foo/bar';
        $query = 'abc=123';
        $fragment = 'section3';
        $user = 'josh';
        $password = 'sekrit';

        return new Uri($scheme, $host, $port, $path, $query, $fragment, $user, $password);
    }

    /********************************************************************************
     * Scheme
     *******************************************************************************/

    public function testGetScheme()
    {
        $this->assertEquals('https', $this->uriFactory()->getScheme());
    }

    public function testWithScheme()
    {
        $uri = $this->uriFactory()->withScheme('http');

        $this->assertAttributeEquals('http', 'scheme', $uri);
    }

    public function testWithSchemeRemovesSuffix()
    {
        $uri = $this->uriFactory()->withScheme('http://');

        $this->assertAttributeEquals('http', 'scheme', $uri);
    }

    public function testWithSchemeEmpty()
    {
        $uri = $this->uriFactory()->withScheme('');

        $this->assertAttributeEquals('', 'scheme', $uri);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Uri scheme must be one of: "", "https", "http"
     */
    public function testWithSchemeInvalid()
    {
        $this->uriFactory()->withScheme('ftp');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Uri scheme must be a string
     */
    public function testWithSchemeInvalidType()
    {
        $this->uriFactory()->withScheme([]);
    }

    /********************************************************************************
     * Authority
     *******************************************************************************/

    public function testGetAuthorityWithUsernameAndPassword()
    {
        $this->assertEquals('josh:sekrit@example.com', $this->uriFactory()->getAuthority());
    }

    public function testGetAuthorityWithUsername()
    {
        $uri = Uri::createFromString('https://josh@example.com/foo/bar?abc=123#section3');

        $this->assertEquals('josh@example.com', $uri->getAuthority());
    }

    public function testGetAuthority()
    {
        $uri = Uri::createFromString('https://example.com/foo/bar?abc=123#section3');

        $this->assertEquals('example.com', $uri->getAuthority());
    }

    public function testGetAuthorityWithNonStandardPort()
    {
        $uri = Uri::createFromString('https://example.com:400/foo/bar?abc=123#section3');

        $this->assertEquals('example.com:400', $uri->getAuthority());
    }

    public function testGetUserInfoWithUsernameAndPassword()
    {
        $uri = Uri::createFromString('https://josh:sekrit@example.com:443/foo/bar?abc=123#section3');

        $this->assertEquals('josh:sekrit', $uri->getUserInfo());
    }

    public function testGetUserInfoWithUsernameAndPasswordEncodesCorrectly()
    {
        $uri = Uri::createFromString('https://bob%40example.com:pass%3Aword@example.com:443/foo/bar?abc=123#section3');

        $this->assertEquals('bob%40example.com:pass%3Aword', $uri->getUserInfo());
    }

    public function testGetUserInfoWithUsername()
    {
        $uri = Uri::createFromString('http://josh@example.com/foo/bar?abc=123#section3');

        $this->assertEquals('josh', $uri->getUserInfo());
    }

    public function testGetUserInfoNone()
    {
        $uri = Uri::createFromString('https://example.com/foo/bar?abc=123#section3');

        $this->assertEquals('', $uri->getUserInfo());
    }

    public function testWithUserInfo()
    {
        $uri = $this->uriFactory()->withUserInfo('bob', 'pass');

        $this->assertAttributeEquals('bob', 'user', $uri);
        $this->assertAttributeEquals('pass', 'password', $uri);
    }

    public function testWithUserInfoEncodesCorrectly()
    {
        $uri = $this->uriFactory()->withUserInfo('bob@example.com', 'pass:word');

        $this->assertAttributeEquals('bob%40example.com', 'user', $uri);
        $this->assertAttributeEquals('pass%3Aword', 'password', $uri);
    }

    public function testWithUserInfoRemovesPassword()
    {
        $uri = $this->uriFactory()->withUserInfo('bob');

        $this->assertAttributeEquals('bob', 'user', $uri);
        $this->assertAttributeEquals('', 'password', $uri);
    }

    public function testWithUserInfoRemovesInfo()
    {
        $uri = $this->uriFactory()->withUserInfo('bob', 'password');

        $uri = $uri->withUserInfo('');
        $this->assertAttributeEquals('', 'user', $uri);
        $this->assertAttributeEquals('', 'password', $uri);
    }

    public function testGetHost()
    {
        $this->assertEquals('example.com', $this->uriFactory()->getHost());
    }

    public function testWithHost()
    {
        $uri = $this->uriFactory()->withHost('slimframework.com');

        $this->assertAttributeEquals('slimframework.com', 'host', $uri);
    }

    public function testFilterHost()
    {
        $uri = new Uri('http', '2001:db8::1');

        $this->assertEquals('[2001:db8::1]', $uri->getHost());
    }

    public function testGetPortWithSchemeAndNonDefaultPort()
    {
        $uri = new Uri('https', 'www.example.com', 4000);

        $this->assertEquals(4000, $uri->getPort());
    }

    public function testGetPortWithSchemeAndDefaultPort()
    {
        $uriHttp = new Uri('http', 'www.example.com', 80);
        $uriHttps = new Uri('https', 'www.example.com', 443);

        $this->assertNull($uriHttp->getPort());
        $this->assertNull($uriHttps->getPort());
    }

    public function testGetPortWithoutSchemeAndPort()
    {
        $uri = new Uri('', 'www.example.com');

        $this->assertNull($uri->getPort());
    }

    public function testGetPortWithSchemeWithoutPort()
    {
        $uri = new Uri('http', 'www.example.com');

        $this->assertNull($uri->getPort());
    }

    public function testWithPort()
    {
        $uri = $this->uriFactory()->withPort(8000);

        $this->assertAttributeEquals(8000, 'port', $uri);
    }

    public function testWithPortNull()
    {
        $uri = $this->uriFactory()->withPort(null);

        $this->assertAttributeEquals(null, 'port', $uri);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWithPortInvalidInt()
    {
        $this->uriFactory()->withPort(70000);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWithPortInvalidString()
    {
        $this->uriFactory()->withPort('Foo');
    }

    /********************************************************************************
     * Path
     *******************************************************************************/

    public function testGetPath()
    {
        $this->assertEquals('/foo/bar', $this->uriFactory()->getPath());
    }

    public function testWithPath()
    {
        $uri = $this->uriFactory()->withPath('/new');

        $this->assertAttributeEquals('/new', 'path', $uri);
    }

    public function testWithPathWithoutPrefix()
    {
        $uri = $this->uriFactory()->withPath('new');

        $this->assertAttributeEquals('new', 'path', $uri);
    }

    public function testWithPathEmptyValue()
    {
        $uri = $this->uriFactory()->withPath('');

        $this->assertAttributeEquals('', 'path', $uri);
    }

    public function testWithPathUrlEncodesInput()
    {
        $uri = $this->uriFactory()->withPath('/includes?/new');

        $this->assertAttributeEquals('/includes%3F/new', 'path', $uri);
    }

    public function testWithPathDoesNotDoubleEncodeInput()
    {
        $uri = $this->uriFactory()->withPath('/include%25s/new');

        $this->assertAttributeEquals('/include%25s/new', 'path', $uri);
    }

    /**
     * @covers Slim\Http\Uri::withPath
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Uri path must be a string
     */
    public function testWithPathInvalidType()
    {
        $this->uriFactory()->withPath(['foo']);
    }

    /********************************************************************************
     * Query
     *******************************************************************************/

    public function testGetQuery()
    {
        $this->assertEquals('abc=123', $this->uriFactory()->getQuery());
    }

    public function testWithQuery()
    {
        $uri = $this->uriFactory()->withQuery('xyz=123');

        $this->assertAttributeEquals('xyz=123', 'query', $uri);
    }

    public function testWithQueryRemovesPrefix()
    {
        $uri = $this->uriFactory()->withQuery('?xyz=123');

        $this->assertAttributeEquals('xyz=123', 'query', $uri);
    }

    public function testWithQueryEmpty()
    {
        $uri = $this->uriFactory()->withQuery('');

        $this->assertAttributeEquals('', 'query', $uri);
    }

    public function testFilterQuery()
    {
        $uri = $this->uriFactory()->withQuery('?foobar=%match');

        $this->assertAttributeEquals('foobar=%25match', 'query', $uri);
    }

    /**
     * @covers Slim\Http\Uri::withQuery
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Uri query must be a string
     */
    public function testWithQueryInvalidType()
    {
        $this->uriFactory()->withQuery(['foo']);
    }

    /********************************************************************************
     * Fragment
     *******************************************************************************/

    public function testGetFragment()
    {
        $this->assertEquals('section3', $this->uriFactory()->getFragment());
    }

    public function testWithFragment()
    {
        $uri = $this->uriFactory()->withFragment('other-fragment');

        $this->assertAttributeEquals('other-fragment', 'fragment', $uri);
    }

    public function testWithFragmentRemovesPrefix()
    {
        $uri = $this->uriFactory()->withFragment('#other-fragment');

        $this->assertAttributeEquals('other-fragment', 'fragment', $uri);
    }

    public function testWithFragmentEmpty()
    {
        $uri = $this->uriFactory()->withFragment('');

        $this->assertAttributeEquals('', 'fragment', $uri);
    }

    /**
     * @covers Slim\Http\Uri::withFragment
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Uri fragment must be a string
     */
    public function testWithFragmentInvalidType()
    {
        $this->uriFactory()->withFragment(['foo']);
    }

    /********************************************************************************
     * Helpers
     *******************************************************************************/

    public function testToString()
    {
        $uri = $this->uriFactory();

        $this->assertEquals('https://josh:sekrit@example.com/foo/bar?abc=123#section3', (string) $uri);

        $uri = $uri->withPath('bar');
        $this->assertEquals('https://josh:sekrit@example.com/bar?abc=123#section3', (string) $uri);

        $uri = $uri->withPath('/bar');
        $this->assertEquals('https://josh:sekrit@example.com/bar?abc=123#section3', (string) $uri);
    }

    /**
     * @covers Slim\Http\Uri::createFromString
     */
    public function testCreateFromString()
    {
        $uri = Uri::createFromString('https://example.com:8080/foo/bar?abc=123');

        $this->assertEquals('https', $uri->getScheme());
        $this->assertEquals('example.com', $uri->getHost());
        $this->assertEquals('8080', $uri->getPort());
        $this->assertEquals('/foo/bar', $uri->getPath());
        $this->assertEquals('abc=123', $uri->getQuery());
    }

    /**
     * @covers Slim\Http\Uri::createFromString
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Uri must be a string
     */
    public function testCreateFromStringWithInvalidType()
    {
        Uri::createFromString(['https://example.com:8080/foo/bar?abc=123']);
    }

    public function testCreateFromGlobals()
    {
        $globals = Environment::mock([
            'SCRIPT_NAME' => '/index.php',
            'REQUEST_URI' => '/foo/bar',
            'PHP_AUTH_USER' => 'josh',
            'PHP_AUTH_PW' => 'sekrit',
            'QUERY_STRING' => 'abc=123',
            'HTTP_HOST' => 'example.com:8080',
            'SERVER_PORT' => 8080,
        ]);

        $uri = Uri::createFromGlobals($globals);

        $this->assertEquals('josh:sekrit', $uri->getUserInfo());
        $this->assertEquals('example.com', $uri->getHost());
        $this->assertEquals('8080', $uri->getPort());
        $this->assertEquals('/foo/bar', $uri->getPath());
        $this->assertEquals('abc=123', $uri->getQuery());
        $this->assertEquals('', $uri->getFragment());
    }


    public function testCreateFromGlobalWithIPv6HostNoPort()
    {
        $environment = Environment::mock([
            'SCRIPT_NAME' => '/index.php',
            'REQUEST_URI' => '/foo/bar',
            'PHP_AUTH_USER' => 'josh',
            'PHP_AUTH_PW' => 'sekrit',
            'QUERY_STRING' => 'abc=123',
            'HTTP_HOST' => '[2001:db8::1]',
            'REMOTE_ADDR' => '2001:db8::1',
            'SERVER_PORT' => 8080,
        ]);
        $uri = Uri::createFromGlobals($environment);

        $this->assertEquals('josh:sekrit', $uri->getUserInfo());
        $this->assertEquals('[2001:db8::1]', $uri->getHost());
        $this->assertEquals('8080', $uri->getPort());
        $this->assertEquals('/foo/bar', $uri->getPath());
        $this->assertEquals('abc=123', $uri->getQuery());
        $this->assertEquals('', $uri->getFragment());
    }

    public function testCreateFromGlobalsWithIPv6HostWithPort()
    {
        $globals = Environment::mock([
            'SCRIPT_NAME' => '/index.php',
            'REQUEST_URI' => '/foo/bar',
            'PHP_AUTH_USER' => 'josh',
            'PHP_AUTH_PW' => 'sekrit',
            'QUERY_STRING' => 'abc=123',
            'HTTP_HOST' => '[2001:db8::1]:8080',
            'REMOTE_ADDR' => '2001:db8::1',
            'SERVER_PORT' => 8080,
        ]);

        $uri = Uri::createFromGlobals($globals);

        $this->assertEquals('josh:sekrit', $uri->getUserInfo());
        $this->assertEquals('[2001:db8::1]', $uri->getHost());
        $this->assertEquals('8080', $uri->getPort());
        $this->assertEquals('/foo/bar', $uri->getPath());
        $this->assertEquals('abc=123', $uri->getQuery());
        $this->assertEquals('', $uri->getFragment());
    }

    public function testCreateFromGlobalsWithBasePathContainingSpace()
    {
        $globals = Environment::mock([
            'SCRIPT_NAME' => "/f'oo bar/index.php",
            'REQUEST_URI' => "/f%27oo%20bar/baz",
        ]);
        $uri = Uri::createFromGlobals($globals);

        $this->assertEquals('/f%27oo%20bar/baz', $uri->getPath());
    }

    public function testGetBaseUrl()
    {
        $globals = Environment::mock([
            'SCRIPT_NAME' => '/foo/index.php',
            'REQUEST_URI' => '/foo/bar',
            'QUERY_STRING' => 'abc=123',
            'HTTP_HOST' => 'example.com:80',
            'SERVER_PORT' => 80
        ]);
        $uri = Uri::createFromGlobals($globals);

        $this->assertEquals('http://example.com', $uri->getBaseUrl());
    }

    public function testGetBaseUrlWithNoBasePath()
    {
        $globals = Environment::mock([
            'SCRIPT_NAME' => '/index.php',
            'REQUEST_URI' => '/foo/bar',
            'QUERY_STRING' => 'abc=123',
            'HTTP_HOST' => 'example.com:80',
            'SERVER_PORT' => 80
        ]);
        $uri = Uri::createFromGlobals($globals);

        $this->assertEquals('http://example.com', $uri->getBaseUrl());
    }

    public function testGetBaseUrlWithAuthority()
    {
        $globals = Environment::mock([
            'SCRIPT_NAME' => '/foo/index.php',
            'REQUEST_URI' => '/foo/bar',
            'PHP_AUTH_USER' => 'josh',
            'PHP_AUTH_PW' => 'sekrit',
            'QUERY_STRING' => 'abc=123',
            'HTTP_HOST' => 'example.com:8080',
            'SERVER_PORT' => 8080
        ]);
        $uri = Uri::createFromGlobals($globals);

        $this->assertEquals('http://josh:sekrit@example.com:8080', $uri->getBaseUrl());
    }

    /**
     * @covers Slim\Http\Uri::createFromGlobals
     * @ticket 1380
     */
    public function testWithPathWhenBaseRootIsEmpty()
    {
        $globals = \Slim\Http\Environment::mock([
            'SCRIPT_NAME' => '/index.php',
            'REQUEST_URI' => '/bar',
        ]);
        $uri = \Slim\Http\Uri::createFromGlobals($globals);

        $this->assertEquals('http://localhost/test', (string) $uri->withPath('test'));
    }

    /**
     * When the URL is /foo/index.php/bar/baz, we need the baseURL to be
     * /foo/index.php so that routing works correctly.
     *
     * @ticket 1639 as a fix to 1590 broke this.
     */
    public function testRequestURIContainsIndexDotPhp()
    {
        $uri = Uri::createFromGlobals(
            Environment::mock(
                [
                    'SCRIPT_NAME' => '/foo/index.php',
                    'REQUEST_URI' => '/foo/index.php/bar/baz',
                ]
            )
        );
        $this->assertSame('/foo/index.php/bar/baz', $uri->getPath());
    }

    public function testRequestURICanContainParams()
    {
        $uri = Uri::createFromGlobals(
            Environment::mock(
                [
                    'REQUEST_URI' => '/foo?abc=123',
                ]
            )
        );
        $this->assertEquals('abc=123', $uri->getQuery());
    }

    public function testUriDistinguishZeroFromEmptyString()
    {
        $expected = 'https://0:0@0:1/0?0#0';
        $this->assertSame($expected, (string) Uri::createFromString($expected));
    }

    public function testGetBaseUrlDistinguishZeroFromEmptyString()
    {
        $expected = 'https://0:0@0:1/0?0#0';
        $this->assertSame('https://0:0@0:1', (string) Uri::createFromString($expected)->getBaseUrl());
    }
}
