<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @link      https://github.com/slimphp/Slim-Psr7
 * @copyright Copyright (c) 2011-2018 Josh Lockhart
 * @license   https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE (MIT License)
 */

declare(strict_types=1);

namespace Slim\Psr7\Factory;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Slim\Psr7\Cookies;
use Slim\Psr7\Headers;
use Slim\Psr7\Request;
use Slim\Psr7\RequestBody;
use Slim\Psr7\UploadedFile;

class ServerRequestFactory implements ServerRequestFactoryInterface
{
    /**
     * @var StreamFactoryInterface|StreamFactory
     */
    protected $streamFactory;

    /**
     * @var UriFactoryInterface|UriFactory
     */
    protected $uriFactory;

    /**
     * @param StreamFactoryInterface|null $streamFactory
     * @param UriFactoryInterface|null    $uriFactory
     */
    public function __construct(StreamFactoryInterface $streamFactory = null, UriFactoryInterface $uriFactory = null)
    {
        if (!isset($streamFactory)) {
            $streamFactory = new StreamFactory();
        }

        if (!isset($uriFactory)) {
            $uriFactory = new UriFactory();
        }

        $this->streamFactory = $streamFactory;
        $this->uriFactory = $uriFactory;
    }

    /**
     * Create a new server request.
     *
     * Note that server-params are taken precisely as given - no parsing/processing
     * of the given values is performed, and, in particular, no attempt is made to
     * determine the HTTP method or URI, which must be provided explicitly.
     *
     * @param string $method The HTTP method associated with the request.
     * @param UriInterface|string $uri The URI associated with the request.
     *     If the value is a string, the factory MUST create a UriInterface instance based on it.
     * @param array $serverParams Array of SAPI parameters with which to seed the generated request instance.
     *
     * @return ServerRequestInterface
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        if (is_string($uri)) {
            $uri = $this->uriFactory->createUri($uri);
        } elseif (!$uri instanceof UriInterface) {
            throw new InvalidArgumentException('URI must either be string or instance of ' . UriInterface::class);
        }

        $body = $this->streamFactory->createStream();
        $headers = new Headers();
        $cookies = [];

        if (!empty($serverParams)) {
            $headers = Headers::createFromGlobals($serverParams);
            $cookies = Cookies::parseHeader($headers->get('Cookie', []));
        }

        return new Request($method, $uri, $headers, $cookies, $serverParams, $body);
    }

    /**
     * Create new ServerRequest from environment.
     *
     * Note: This method is not part of PSR-17
     */
    public static function createFromGlobals(): Request
    {
        $server = $_SERVER;

        $method = isset($server['REQUEST_METHOD']) ? $server['REQUEST_METHOD'] : 'GET';
        $uri = (new UriFactory())->createFromGlobals($server);
        $headers = Headers::createFromGlobals($server);
        $cookies = Cookies::parseHeader($headers->get('Cookie', []));
        $body = new RequestBody();
        $uploadedFiles = UploadedFile::createFromGlobals($server);

        $request = new Request($method, $uri, $headers, $cookies, $server, $body, $uploadedFiles);
        $type = $request->getHeader('Content-Type')[0] ?? null;

        if ($method === 'POST' && in_array($type, ['application/x-www-form-urlencoded', 'multipart/form-data'])) {
            // parsed body must be $_POST
            $request = $request->withParsedBody($_POST);
        }

        return $request;
    }
}
