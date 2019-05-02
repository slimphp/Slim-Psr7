<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Psr7\Factory;

use InvalidArgumentException;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Slim\Psr7\Uri;

class UriFactory implements UriFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createUri(string $uri = ''): UriInterface
    {
        $parts = parse_url($uri);

        if ($parts === false) {
            throw new InvalidArgumentException('URI cannot be parsed');
        }

        $scheme = $parts['scheme'] ?? '';
        $user = $parts['user'] ?? '';
        $pass = $parts['pass'] ?? '';
        $host = $parts['host'] ?? '';
        $port = $parts['port'] ?? null;
        $path = $parts['path'] ?? '';
        $query = $parts['query'] ?? '';
        $fragment = $parts['fragment'] ?? '';

        return new Uri($scheme, $host, $port, $path, $query, $fragment, $user, $pass);
    }

    /**
     * Create new Uri from environment.
     *
     * @internal This method is not part of PSR-17
     *
     * @param array $globals The global server variables.
     *
     * @return Uri
     */
    public function createFromGlobals(array $globals): Uri
    {
        // Scheme
        $https = isset($globals['HTTPS']) ? $globals['HTTPS'] : false;
        $scheme = !$https || $https === 'off' ? 'http' : 'https';

        // Authority: Username and password
        $username = isset($globals['PHP_AUTH_USER']) ? $globals['PHP_AUTH_USER'] : '';
        $password = isset($globals['PHP_AUTH_PW']) ? $globals['PHP_AUTH_PW'] : '';

        // Authority: Host
        if (isset($globals['HTTP_HOST'])) {
            $host = $globals['HTTP_HOST'] ?: '';
        } else {
            $host = $globals['SERVER_NAME'] ?: '';
        }

        // Authority: Port
        $port = isset($globals['SERVER_PORT']) && !empty($globals['SERVER_PORT']) ? (int) $globals['SERVER_PORT'] : 80;
        if (preg_match('/^(\[[a-fA-F0-9:.]+\])(:\d+)?\z/', $host, $matches)) {
            $host = $matches[1];

            if (isset($matches[2])) {
                $port = (int) substr($matches[2], 1);
            }
        } else {
            $pos = strpos($host, ':');
            if ($pos !== false) {
                $port = (int) substr($host, $pos + 1);
                $host = strstr($host, ':', true);
            }
        }

        // Query string
        $queryString = '';
        if (isset($globals['QUERY_STRING'])) {
            $queryString = $globals['QUERY_STRING'];
        }

        // Request URI
        $requestUri = '';
        if (isset($globals['REQUEST_URI'])) {
            $uriFragments = explode('?', $globals['REQUEST_URI']);
            $requestUri = $uriFragments[0];

            if ($queryString === '' && count($uriFragments) > 1) {
                $queryString = parse_url('http://www.example.com' . $globals['REQUEST_URI'], PHP_URL_QUERY);
            }
        }

        // Build Uri
        $uri = new Uri($scheme, $host, $port, $requestUri, $queryString, '', $username, $password);

        return $uri;
    }
}
