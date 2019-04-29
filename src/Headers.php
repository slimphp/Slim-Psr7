<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Psr7;

use Slim\Psr7\Interfaces\HeadersInterface;

class Headers extends Collection implements HeadersInterface
{
    /**
     * Special HTTP headers that do not have the "HTTP_" prefix
     *
     * @var array
     */
    protected static $special = [
        'CONTENT_TYPE' => 1,
        'CONTENT_LENGTH' => 1,
        'PHP_AUTH_USER' => 1,
        'PHP_AUTH_PW' => 1,
        'PHP_AUTH_DIGEST' => 1,
        'AUTH_TYPE' => 1,
    ];

    /**
     * Create new headers collection with data extracted from the PHP global environment
     *
     * @param array $globals Global server variables
     *
     * @return static
     */
    public static function createFromGlobals(array $globals)
    {
        $data = [];
        $globals = self::determineAuthorization($globals);

        foreach ($globals as $key => $value) {
            $key = strtoupper($key);
            if (isset(static::$special[$key]) || strpos($key, 'HTTP_') === 0) {
                if ($key !== 'HTTP_CONTENT_LENGTH') {
                    $data[self::reconstructOriginalKey($key)] = $value;
                }
            }
        }

        return new static($data);
    }

    /**
     * If HTTP_AUTHORIZATION does not exist tries to get it from getallheaders() when available.
     *
     * @param array $globals
     *
     * @return array
     */
    public static function determineAuthorization(array $globals): array
    {
        $authorization = isset($globals['HTTP_AUTHORIZATION']) ? $globals['HTTP_AUTHORIZATION'] : null;
        if (!empty($authorization)) {
            return $globals;
        }

        $headers = getallheaders();
        if (!is_array($headers)) {
            return $globals;
        }

        $headers = array_change_key_case($headers, CASE_LOWER);
        if (isset($headers['authorization'])) {
            $globals['HTTP_AUTHORIZATION'] = $headers['authorization'];
        }

        return $globals;
    }

    /**
     * Return array of HTTP header names and values.
     * This method returns the _original_ header name as specified by the end user.
     *
     * @return array
     */
    public function all(): array
    {
        $all = parent::all();
        $out = [];

        foreach ($all as $key => $props) {
            $out[$props['originalKey']] = $props['value'];
        }

        return $out;
    }

    /**
     * Set HTTP header value
     *
     * This method sets a header value. It replaces
     * any values that may already exist for the header name.
     *
     * @param string       $key   The case-insensitive header name
     * @param array|string $value The header value
     *
     * @return static
     */
    public function set($key, $value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }

        parent::set($this->normalizeKey($key), [
            'value' => $value,
            'originalKey' => $key
        ]);

        return $this;
    }

    /**
     * Get HTTP header value
     *
     * @param  string $key     The case-insensitive header name
     * @param  mixed  $default The default value if key does not exist
     *
     * @return string[]
     */
    public function get($key, $default = null)
    {
        if ($this->has($key)) {
            return parent::get($this->normalizeKey($key))['value'];
        }

        return $default;
    }

    /**
     * Get HTTP header key as originally specified
     *
     * @param  string $key     The case-insensitive header name
     * @param  string $default The default value if key does not exist
     *
     * @return string|null
     */
    public function getOriginalKey($key, string $default = null): ?string
    {
        if ($this->has($key)) {
            return parent::get($this->normalizeKey($key))['originalKey'];
        }

        return $default;
    }

    /**
     * Add HTTP header value
     *
     * This method appends a header value. Unlike the set() method,
     * this method _appends_ this new value to any values
     * that already exist for this header name.
     *
     * @param string       $key   The case-insensitive header name
     * @param array|string $value The new header value(s)
     *
     * @return static
     */
    public function add($key, $value)
    {
        $oldValues = $this->get($key, []);
        $newValues = is_array($value) ? $value : [$value];
        $this->set($key, array_merge($oldValues, array_values($newValues)));

        return $this;
    }

    /**
     * Does this collection have a given header?
     *
     * @param  string $key The case-insensitive header name
     *
     * @return bool
     */
    public function has($key): bool
    {
        return parent::has($this->normalizeKey($key));
    }

    /**
     * Remove header from collection
     *
     * @param  string $key The case-insensitive header name
     *
     * @return static
     */
    public function remove($key)
    {
        parent::remove($this->normalizeKey($key));

        return $this;
    }

    /**
     * Normalize header name
     *
     * This method transforms header names into a
     * normalized form. This is how we enable case-insensitive
     * header names in the other methods in this class.
     *
     * @param  string $key The case-insensitive header name
     *
     * @return string Normalized header name
     */
    public function normalizeKey($key): string
    {
        $key = strtr(strtolower($key), '_', '-');

        if (strpos($key, 'http-') === 0) {
            $key = substr($key, 5);
        }

        return $key;
    }

    /**
     * Reconstruct original header name
     *
     * This method takes an HTTP header name from the Environment
     * and returns it as it was probably formatted by the actual client.
     *
     * @param string $key An HTTP header key from the $_SERVER global variable
     *
     * @return string The reconstructed key
     *
     * @example CONTENT_TYPE => Content-Type
     * @example HTTP_USER_AGENT => User-Agent
     */
    private static function reconstructOriginalKey($key): string
    {
        if (strpos($key, 'HTTP_') === 0) {
            $key = substr($key, 5);
        }

        return strtr(ucwords(strtr(strtolower($key), '_', ' ')), ' ', '-');
    }
}
