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
use Slim\Psr7\Uri;

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

    public function testGetAuthorityWithUsernameAndPassword()
    {
        $this->assertEquals('josh:sekrit@example.com', $this->uriFactory()->getAuthority());
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
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Uri path must be a string
     */
    public function testWithPathInvalidType()
    {
        $this->uriFactory()->withPath(['foo']);
    }

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
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Uri query must be a string
     */
    public function testWithQueryInvalidType()
    {
        $this->uriFactory()->withQuery(['foo']);
    }

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
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Uri fragment must be a string
     */
    public function testWithFragmentInvalidType()
    {
        $this->uriFactory()->withFragment(['foo']);
    }

    public function testToString()
    {
        $uri = $this->uriFactory();

        $this->assertEquals('https://josh:sekrit@example.com/foo/bar?abc=123#section3', (string) $uri);

        $uri = $uri->withPath('bar');
        $this->assertEquals('https://josh:sekrit@example.com/bar?abc=123#section3', (string) $uri);

        $uri = $uri->withPath('/bar');
        $this->assertEquals('https://josh:sekrit@example.com/bar?abc=123#section3', (string) $uri);
    }
}
