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
use Slim\Psr7\Collection;
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
        $env = new Collection($globals);

        // Scheme
        $isSecure = $env->get('HTTPS');
        $scheme = (empty($isSecure) || $isSecure === 'off') ? 'http' : 'https';

        // Authority: Username and password
        $username = $env->get('PHP_AUTH_USER', '');
        $password = $env->get('PHP_AUTH_PW', '');

        // Authority: Host
        if ($env->has('HTTP_HOST')) {
            $host = $env->get('HTTP_HOST', '');
        } else {
            $host = $env->get('SERVER_NAME', '');
        }

        // Authority: Port
        $port = (int) $env->get('SERVER_PORT', 80);
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

        $requestUri = \current(\explode('?', $env->get('REQUEST_URI', '')));

        // Query string
        $queryString = $env->get('QUERY_STRING', '');
        if ($queryString === '') {
            $queryString = parse_url('http://example.com' . $env->get('REQUEST_URI', ''), PHP_URL_QUERY);
        }

        // Fragment
        $fragment = '';

        // Build Uri
        $uri = new Uri($scheme, $host, $port, $requestUri, $queryString, $fragment, $username, $password);

        return $uri;
    }
}
