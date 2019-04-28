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
     * {@inheritdoc}
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
        $contentTypes = $request->getHeader('Content-Type') ?? [];

        $parsedContentType = '';
        foreach ($contentTypes as $contentType) {
            $fragments = explode(';', $contentType);
            $parsedContentType = current($fragments);
        }

        $contentTypesWithParsedBodies = ['application/x-www-form-urlencoded', 'multipart/form-data'];
        if ($method === 'POST' && in_array($parsedContentType, $contentTypesWithParsedBodies)) {
            $request = $request->withParsedBody($_POST);
        }

        return $request;
    }
}
