<?php

/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Psr7;

use Slim\Tests\Psr7\Assets\HeaderStack;

use function array_key_exists;

/**
 * Return the value of the global variable $GLOBALS['getallheaders_return'] if it exists. Otherwise the
 * function override calls the default php built-in function.
 *
 * @return array|false
 */
function getallheaders()
{
    if (array_key_exists('getallheaders_return', $GLOBALS)) {
        return $GLOBALS['getallheaders_return'];
    }

    return \getallheaders();
}

/**
 * Emit a header, without creating actual output artifacts
 *
 * @param string   $string
 * @param bool     $replace
 * @param int|null $statusCode
 */
function header($string, $replace = true, $statusCode = null)
{
    HeaderStack::push(
        [
            'header'      => $string,
            'replace'     => $replace,
            'status_code' => $statusCode,
        ]
    );
}

/**
 * Remove a previously emmited header from the HeaderStack.
 *
 * @param string|null $name
 */
function header_remove($name = null)
{
    HeaderStack::remove($name);
}

/**
 * @param string $filename
 *
 * @return bool
 */
function is_uploaded_file(string $filename): bool
{
    if (isset($GLOBALS['is_uploaded_file_return'])) {
        return $GLOBALS['is_uploaded_file_return'];
    }

    return \is_uploaded_file($filename);
}

/**
 * Return the level of the output buffering shifted by the value of the global
 * variable $GLOBALS['ob_get_level_shift'] if it exists. Otherwise the function
 * override calls the default php built-in function.
 *
 * @return int
 */
function ob_get_level(): int
{
    if (isset($GLOBALS['ob_get_level_shift'])) {
        return \ob_get_level() + $GLOBALS['ob_get_level_shift'];
    }

    return \ob_get_level();
}

/**
 * @param string        $source
 * @param string        $destination
 * @param resource|null $context
 *
 * @return bool
 */
function copy(string $source, string $destination, $context = null): bool
{
    if (isset($GLOBALS['copy_return'])) {
        return $GLOBALS['copy_return'];
    }

    if ($context === null) {
        return \copy($source, $destination);
    }
    return \copy($source, $destination, $context);
}

/**
 * @param string        $oldName
 * @param string        $newName
 * @param resource|null $context
 *
 * @return bool
 */
function rename(string $oldName, string $newName, $context = null): bool
{
    if (isset($GLOBALS['rename_return'])) {
        return $GLOBALS['rename_return'];
    }

    if ($context === null) {
        return \rename($oldName, $newName);
    }
    return \rename($oldName, $newName, $context = null);
}
