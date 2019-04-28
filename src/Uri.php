<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Psr7;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    /**
     * Uri scheme (without "://" suffix)
     *
     * @var string
     */
    protected $scheme = '';

    /**
     * @var string
     */
    protected $user = '';

    /**
     * @var string
     */
    protected $password = '';

    /**
     * @var string
     */
    protected $host = '';

    /**
     * @var null|int
     */
    protected $port;

    /**
     * @var string
     */
    protected $path = '';

    /**
     * Uri query string (without "?" prefix)
     *
     * @var string
     */
    protected $query = '';

    /**
     * Uri fragment string (without "#" prefix)
     *
     * @var string
     */
    protected $fragment = '';

    /**
     * @param string $scheme   Uri scheme.
     * @param string $host     Uri host.
     * @param int    $port     Uri port number.
     * @param string $path     Uri path.
     * @param string $query    Uri query string.
     * @param string $fragment Uri fragment.
     * @param string $user     Uri user.
     * @param string $password Uri password.
     */
    public function __construct(
        $scheme,
        $host,
        $port = null,
        $path = '/',
        $query = '',
        $fragment = '',
        $user = '',
        $password = ''
    ) {
        $this->scheme = $this->filterScheme($scheme);
        $this->host = $this->filterHost($host);
        $this->port = $this->filterPort($port);
        $this->path = $this->filterPath($path);
        $this->query = $this->filterQuery($query);
        $this->fragment = $this->filterQuery($fragment);
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * {@inheritdoc}
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * {@inheritdoc}
     */
    public function withScheme($scheme)
    {
        $scheme = $this->filterScheme($scheme);
        $clone = clone $this;
        $clone->scheme = $scheme;

        return $clone;
    }

    /**
     * Filter Uri scheme.
     *
     * @param  string $scheme Raw Uri scheme.
     * @return string
     *
     * @throws InvalidArgumentException If the Uri scheme is not a string.
     * @throws InvalidArgumentException If Uri scheme is not "", "https", or "http".
     */
    protected function filterScheme($scheme)
    {
        static $valid = [
            '' => true,
            'https' => true,
            'http' => true,
        ];

        if (!is_string($scheme) && !method_exists($scheme, '__toString')) {
            throw new InvalidArgumentException('Uri scheme must be a string');
        }

        $scheme = str_replace('://', '', strtolower($scheme));
        if (!isset($valid[$scheme])) {
            throw new InvalidArgumentException('Uri scheme must be one of: "", "https", "http"');
        }

        return $scheme;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthority()
    {
        $userInfo = $this->getUserInfo();
        $host = $this->getHost();
        $port = $this->getPort();

        return ($userInfo !== '' ? $userInfo . '@' : '') . $host . ($port !== null ? ':' . $port : '');
    }

    /**
     * {@inheritdoc}
     */
    public function getUserInfo()
    {
        $info = $this->user;

        if (isset($this->password) && $this->password !== '') {
            $info .= ':' . $this->password;
        }

        return $info;
    }

    /**
     * {@inheritdoc}
     */
    public function withUserInfo($user, $password = null)
    {
        $clone = clone $this;
        $clone->user = $this->filterUserInfo($user);
        if ($clone->user !== '') {
            $clone->password = isset($password) ? $this->filterUserInfo($password) : '';
        } else {
            $clone->password = '';
        }

        return $clone;
    }

    /**
     * Filters the user info string.
     *
     * @param string $query The raw uri query string.
     * @return string The percent-encoded query string.
     */
    protected function filterUserInfo($query)
    {
        return preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=]+|%(?![A-Fa-f0-9]{2}))/u',
            function ($match) {
                return rawurlencode($match[0]);
            },
            $query
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * {@inheritdoc}
     */
    public function withHost($host)
    {
        $clone = clone $this;
        $clone->host = $this->filterHost($host);

        return $clone;
    }

    /**
     * Filter Uri host.
     *
     * If the supplied host is an IPv6 address, then it is converted to a reference
     * as per RFC 2373.
     *
     * @param  string $host The host to filter.
     * @return string
     * @throws InvalidArgumentException for invalid host names.
     */
    protected function filterHost($host)
    {
        if (!is_string($host) && !method_exists($host, '__toString')) {
            throw new InvalidArgumentException('Uri host must be a string');
        }

        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $host = '[' . $host . ']';
        }

        return strtolower($host);
    }

    /**
     * {@inheritdoc}
     */
    public function getPort()
    {
        return $this->port && !$this->hasStandardPort() ? $this->port : null;
    }

    /**
     * {@inheritdoc}
     */
    public function withPort($port)
    {
        $port = $this->filterPort($port);
        $clone = clone $this;
        $clone->port = $port;

        return $clone;
    }

    /**
     * Does this Uri use a standard port?
     *
     * @return bool
     */
    protected function hasStandardPort()
    {
        return ($this->scheme === 'http' && $this->port === 80) || ($this->scheme === 'https' && $this->port === 443);
    }

    /**
     * Filter Uri port.
     *
     * @param  null|int $port The Uri port number.
     * @return null|int
     *
     * @throws InvalidArgumentException If the port is invalid.
     */
    protected function filterPort($port)
    {
        if (is_null($port) || (is_integer($port) && ($port >= 1 && $port <= 65535))) {
            return $port;
        }

        throw new InvalidArgumentException('Uri port must be null or an integer between 1 and 65535 (inclusive)');
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function withPath($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException('Uri path must be a string');
        }

        $clone = clone $this;
        $clone->path = $this->filterPath($path);

        return $clone;
    }

    /**
     * Filter Uri path.
     *
     * This method percent-encodes all reserved
     * characters in the provided path string. This method
     * will NOT double-encode characters that are already
     * percent-encoded.
     *
     * @param  string $path The raw uri path.
     * @return string       The RFC 3986 percent-encoded uri path.
     * @link   http://www.faqs.org/rfcs/rfc3986.html
     */
    protected function filterPath($path)
    {
        return preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~:@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/',
            function ($match) {
                return rawurlencode($match[0]);
            },
            $path
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * {@inheritdoc}
     */
    public function withQuery($query)
    {
        if (!is_string($query) && !method_exists($query, '__toString')) {
            throw new InvalidArgumentException('Uri query must be a string');
        }
        $query = ltrim($query, '?');
        $clone = clone $this;
        $clone->query = $this->filterQuery($query);

        return $clone;
    }

    /**
     * Filters the query string or fragment of a URI.
     *
     * @param string $query The raw uri query string.
     * @return string The percent-encoded query string.
     */
    protected function filterQuery($query)
    {
        return preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/',
            function ($match) {
                return rawurlencode($match[0]);
            },
            $query
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * {@inheritdoc}
     */
    public function withFragment($fragment)
    {
        if (!is_string($fragment) && !method_exists($fragment, '__toString')) {
            throw new InvalidArgumentException('Uri fragment must be a string');
        }
        $fragment = ltrim($fragment, '#');
        $clone = clone $this;
        $clone->fragment = $this->filterQuery($fragment);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $scheme = $this->getScheme();
        $authority = $this->getAuthority();
        $path = $this->getPath();
        $query = $this->getQuery();
        $fragment = $this->getFragment();

        $path = '/' . ltrim($path, '/');

        return ($scheme !== '' ? $scheme . ':' : '')
            . ($authority !== '' ? '//' . $authority : '')
            . $path
            . ($query !== '' ? '?' . $query : '')
            . ($fragment !== '' ? '#' . $fragment : '');
    }
}
