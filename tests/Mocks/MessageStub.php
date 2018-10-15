<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @link      https://github.com/slimphp/Slim-Psr7
 * @copyright Copyright (c) 2011-2017 Josh Lockhart
 * @license   https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE (MIT License)
 */
namespace Slim\Tests\Psr7\Mocks;

use Slim\Psr7\Message;

/**
 * Mock object for Slim\Psr7\MessageTest
 */
class MessageStub extends Message
{
    /**
     * Protocol version
     *
     * @var string
     */
    public $protocolVersion;

    /**
     * Headers
     *
     * @var \Slim\Psr7\HeadersInterface
     */
    public $headers;

    /**
     * Body object
     *
     * @var \Psr\Http\Message\StreamInterface
     */
    public $body;
}
