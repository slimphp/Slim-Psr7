<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Psr7\Interfaces;

use ArrayAccess;
use Countable;
use IteratorAggregate;

interface CollectionInterface extends ArrayAccess, Countable, IteratorAggregate
{
    /**
     * Set collection item
     *
     * @param string $key   The data key
     * @param mixed  $value The data value
     *
     * @return static
     */
    public function set(string $key, $value);

    /**
     * Get collection item for key
     *
     * @param string $key     The data key
     * @param mixed  $default The default value to return if data key does not exist
     *
     * @return mixed The key's value, or the default value
     */
    public function get(string $key, $default = null);

    /**
     * Add item to collection, replacing existing items with the same data key
     *
     * @param array $items Key-value array of data to append to this collection
     *
     * @return static
     */
    public function replace(array $items);

    /**
     * Get all items in collection
     *
     * @return array The collection's source data
     */
    public function all(): array;

    /**
     * Does this collection have a given key?
     *
     * @param string $key The data key
     *
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Remove item from collection
     *
     * @param string $key The data key
     *
     * @return static
     */
    public function remove(string $key);

    /**
     * Remove all items from collection
     *
     * @return static
     */
    public function clear();
}
