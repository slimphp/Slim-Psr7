<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Psr7\Factory;

/**
 * Return `false` if the parameter is "non-readable", otherwise the function would return the value from
 * the php built-in function.
 *
 * @param string $filename
 *
 * @return bool
 */
function is_readable(string $filename): bool
{
    if ($filename === 'non-readable') {
        return false;
    }

    return \is_readable($filename);
}

/**
 * Return global set value if set or value from php built-in function.
 *
 * @param $filename
 * @param $mode
 *
 * @return bool|resource
 */
function fopen($filename, $mode)
{
    if (isset($GLOBALS['fopen_return'])) {
        return isset($GLOBALS['fopen_return']);
    }

    return \fopen($filename, $mode);
}
