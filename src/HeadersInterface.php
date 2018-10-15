<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @link      https://github.com/slimphp/Slim-Http
 * @copyright Copyright (c) 2011-2017 Josh Lockhart
 * @license   https://github.com/slimphp/Slim-Http/blob/master/LICENSE (MIT License)
 */
namespace Slim\Http;

/**
 * Headers Interface
 *
 * @package Slim
 * @since   1.0.0
 */
interface HeadersInterface extends CollectionInterface
{
    public function add($key, $value);

    public function normalizeKey($key);
}
