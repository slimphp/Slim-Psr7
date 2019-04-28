<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Psr7\Interfaces;

use InvalidArgumentException;

interface CookiesInterface
{
    /**
     * Get request cookie
     *
     * @param  string $name    Cookie name
     * @param  mixed  $default Cookie default value
     *
     * @return mixed Cookie value if present, else default
     */
    public function get(string $name, $default = null);

    /**
     * Set response cookie
     *
     * @param string          $name  Cookie name
     * @param string|string[] $value Cookie value, or cookie properties
     */
    public function set(string $name, $value);

    /**
     * Convert to array of `Set-Cookie` headers
     *
     * @return string[]
     */
    public function toHeaders();

    /**
     * Parse HTTP request `Cookie:` header and extract into a PHP associative array.
     *
     * Returns an associative array of cookie names and values
     *
     * @param  string|string[] $header The raw HTTP request `Cookie:` header
     *
     * @return array
     *
     * @throws InvalidArgumentException if the cookie data cannot be parsed
     */
    public static function parseHeader($header);
}
