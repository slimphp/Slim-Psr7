<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Psr7;

use InvalidArgumentException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use Slim\Psr7\Interfaces\HeadersInterface;

abstract class Message implements MessageInterface
{
    /**
     * @var string
     */
    protected $protocolVersion = '1.1';

    /**
     * @var array
     */
    protected static $validProtocolVersions = [
        '1.0' => true,
        '1.1' => true,
        '2.0' => true,
        '2' => true,
    ];

    /**
     * @var HeadersInterface
     */
    protected $headers;

    /**
     * @var StreamInterface
     */
    protected $body;

    /**
     * Disable magic setter to ensure immutability
     *
     * @param string $name  The property name
     * @param mixed  $value The property value
     */
    public function __set($name, $value)
    {
        // Do nothing
    }

    /**
     * {@inheritdoc}
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function withProtocolVersion($version)
    {
        if (!isset(self::$validProtocolVersions[$version])) {
            throw new InvalidArgumentException(
                'Invalid HTTP version. Must be one of: '
                . implode(', ', array_keys(self::$validProtocolVersions))
            );
        }
        $clone = clone $this;
        $clone->protocolVersion = $version;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        return $this->headers->all();
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader($name)
    {
        return $this->headers->has($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($name)
    {
        return $this->headers->get($name, []);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderLine($name)
    {
        return implode(',', $this->headers->get($name, []));
    }

    /**
     * {@inheritdoc}
     */
    public function withHeader($name, $value)
    {
        $this->validateHeaderName($name);
        $this->validateHeaderValue($value);

        $clone = clone $this;
        $clone->headers->set($name, $value);

        if ($this instanceof Response && $this->body instanceof NonBufferedBody) {
            header(sprintf('%s: %s', $name, $this->getHeaderLine($name)));
        }

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedHeader($name, $value)
    {
        $this->validateHeaderName($name);
        $this->validateHeaderValue($value);

        $clone = clone $this;
        $clone->headers->add($name, $value);

        if ($this instanceof Response && $this->body instanceof NonBufferedBody) {
            header(sprintf('%s: %s', $name, $this->getHeaderLine($name)));
        }

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutHeader($name)
    {
        $clone = clone $this;
        $clone->headers->remove($name);

        if ($this instanceof Response && $this->body instanceof NonBufferedBody) {
            header_remove($name);
        }

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function withBody(StreamInterface $body)
    {
        $clone = clone $this;
        $clone->body = $body;

        return $clone;
    }

    /**
     * @param string $name
     * @throws InvalidArgumentException
     */
    protected function validateHeaderName($name)
    {
        if (!is_string($name) || empty($name)) {
            throw new InvalidArgumentException('Header names must be a non empty strings');
        }

        if (!preg_match('/^[a-zA-Z0-9\'`#$%&*+.^_|~!-]+$/', $name)) {
            throw new InvalidArgumentException("'$name' is not valid header name");
        }
    }

    /**
     * @param string|string[] $value
     * @throws InvalidArgumentException
     */
    protected function validateHeaderValue($value)
    {
        if (!is_array($value)) {
            $value = [$value];
        } elseif (empty($value)) {
            throw new InvalidArgumentException('Header values must be non empty strings');
        }

        foreach ($value as $v) {
            if (!is_string($v) && !is_numeric($v)) {
                throw new InvalidArgumentException('Header values must be strings or numeric');
            }

            $v = (string) $v;
            if (preg_match("#(?:(?:(?<!\r)\n)|(?:\r(?!\n))|(?:\r\n(?![ \t])))#", $v)) {
                throw new InvalidArgumentException("'$v' is not valid header value");
            }
            if (preg_match('/[^\x09\x0a\x0d\x20-\x7E\x80-\xFE]/', $v)) {
                throw new InvalidArgumentException("'$v' is not valid header value");
            }
        }
    }
}
