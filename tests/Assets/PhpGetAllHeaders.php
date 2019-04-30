<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE.md (MIT License)
 */

declare(strict_types=1);

if(!function_exists('\getallheaders')) {
    function getallheaders() {
        return [];
    }
}
