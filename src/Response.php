<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @link      https://github.com/slimphp/Slim-Psr7
 * @copyright Copyright (c) 2011-2017 Josh Lockhart
 * @license   https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE (MIT License)
 */
namespace Slim\Psr7;

use Fig\Http\Message\StatusCodeInterface;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * Response
 *
 * This class represents an HTTP response. It manages
 * the response status, headers, and body
 * according to the PSR-7 standard.
 *
 * @link https://github.com/php-fig/http-message/blob/master/src/MessageInterface.php
 * @link https://github.com/php-fig/http-message/blob/master/src/ResponseInterface.php
 */
class Response extends Message implements ResponseInterface
{
    /**
     * Status code
     *
     * @var int
     */
    protected $status = StatusCodeInterface::STATUS_OK;

    /**
     * Reason phrase
     *
     * @var string
     */
    protected $reasonPhrase = '';

    /**
     * Status codes and reason phrases
     *
     * @var array
     */
    protected static $messages = [
        //Informational 1xx
        StatusCodeInterface::STATUS_CONTINUE => 'Continue',
        StatusCodeInterface::STATUS_SWITCHING_PROTOCOLS => 'Switching Protocols',
        StatusCodeInterface::STATUS_PROCESSING => 'Processing',
        //Successful 2xx
        StatusCodeInterface::STATUS_OK => 'OK',
        StatusCodeInterface::STATUS_CREATED => 'Created',
        StatusCodeInterface::STATUS_ACCEPTED => 'Accepted',
        StatusCodeInterface::STATUS_NON_AUTHORITATIVE_INFORMATION => 'Non-Authoritative Information',
        StatusCodeInterface::STATUS_NO_CONTENT => 'No Content',
        StatusCodeInterface::STATUS_RESET_CONTENT => 'Reset Content',
        StatusCodeInterface::STATUS_PARTIAL_CONTENT => 'Partial Content',
        StatusCodeInterface::STATUS_MULTI_STATUS => 'Multi-Status',
        StatusCodeInterface::STATUS_ALREADY_REPORTED => 'Already Reported',
        StatusCodeInterface::STATUS_IM_USED => 'IM Used',
        //Redirection 3xx
        StatusCodeInterface::STATUS_MULTIPLE_CHOICES => 'Multiple Choices',
        StatusCodeInterface::STATUS_MOVED_PERMANENTLY => 'Moved Permanently',
        StatusCodeInterface::STATUS_FOUND => 'Found',
        StatusCodeInterface::STATUS_SEE_OTHER => 'See Other',
        StatusCodeInterface::STATUS_NOT_MODIFIED => 'Not Modified',
        StatusCodeInterface::STATUS_USE_PROXY => 'Use Proxy',
        StatusCodeInterface::STATUS_RESERVED => '(Unused)',
        StatusCodeInterface::STATUS_TEMPORARY_REDIRECT => 'Temporary Redirect',
        StatusCodeInterface::STATUS_PERMANENT_REDIRECT => 'Permanent Redirect',
        //Client Error 4xx
        StatusCodeInterface::STATUS_BAD_REQUEST => 'Bad Request',
        StatusCodeInterface::STATUS_UNAUTHORIZED => 'Unauthorized',
        StatusCodeInterface::STATUS_PAYMENT_REQUIRED => 'Payment Required',
        StatusCodeInterface::STATUS_FORBIDDEN => 'Forbidden',
        StatusCodeInterface::STATUS_NOT_FOUND => 'Not Found',
        StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED => 'Method Not Allowed',
        StatusCodeInterface::STATUS_NOT_ACCEPTABLE => 'Not Acceptable',
        StatusCodeInterface::STATUS_PROXY_AUTHENTICATION_REQUIRED => 'Proxy Authentication Required',
        StatusCodeInterface::STATUS_REQUEST_TIMEOUT => 'Request Timeout',
        StatusCodeInterface::STATUS_CONFLICT => 'Conflict',
        StatusCodeInterface::STATUS_GONE => 'Gone',
        StatusCodeInterface::STATUS_LENGTH_REQUIRED => 'Length Required',
        StatusCodeInterface::STATUS_PRECONDITION_FAILED => 'Precondition Failed',
        StatusCodeInterface::STATUS_PAYLOAD_TOO_LARGE => 'Request Entity Too Large',
        StatusCodeInterface::STATUS_URI_TOO_LONG => 'Request-URI Too Long',
        StatusCodeInterface::STATUS_UNSUPPORTED_MEDIA_TYPE => 'Unsupported Media Type',
        StatusCodeInterface::STATUS_RANGE_NOT_SATISFIABLE => 'Requested Range Not Satisfiable',
        StatusCodeInterface::STATUS_EXPECTATION_FAILED => 'Expectation Failed',
        StatusCodeInterface::STATUS_IM_A_TEAPOT => 'I\'m a teapot',
        StatusCodeInterface::STATUS_MISDIRECTED_REQUEST => 'Misdirected Request',
        StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY => 'Unprocessable Entity',
        StatusCodeInterface::STATUS_LOCKED => 'Locked',
        StatusCodeInterface::STATUS_FAILED_DEPENDENCY => 'Failed Dependency',
        StatusCodeInterface::STATUS_UPGRADE_REQUIRED => 'Upgrade Required',
        StatusCodeInterface::STATUS_PRECONDITION_REQUIRED => 'Precondition Required',
        StatusCodeInterface::STATUS_TOO_MANY_REQUESTS => 'Too Many Requests',
        StatusCodeInterface::STATUS_REQUEST_HEADER_FIELDS_TOO_LARGE => 'Request Header Fields Too Large',
        444 => 'Connection Closed Without Response',
        StatusCodeInterface::STATUS_UNAVAILABLE_FOR_LEGAL_REASONS => 'Unavailable For Legal Reasons',
        499 => 'Client Closed Request',
        //Server Error 5xx
        StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR => 'Internal Server Error',
        StatusCodeInterface::STATUS_NOT_IMPLEMENTED => 'Not Implemented',
        StatusCodeInterface::STATUS_BAD_GATEWAY => 'Bad Gateway',
        StatusCodeInterface::STATUS_SERVICE_UNAVAILABLE => 'Service Unavailable',
        StatusCodeInterface::STATUS_GATEWAY_TIMEOUT => 'Gateway Timeout',
        StatusCodeInterface::STATUS_VERSION_NOT_SUPPORTED => 'HTTP Version Not Supported',
        StatusCodeInterface::STATUS_VARIANT_ALSO_NEGOTIATES => 'Variant Also Negotiates',
        StatusCodeInterface::STATUS_INSUFFICIENT_STORAGE => 'Insufficient Storage',
        StatusCodeInterface::STATUS_LOOP_DETECTED => 'Loop Detected',
        StatusCodeInterface::STATUS_NOT_EXTENDED => 'Not Extended',
        StatusCodeInterface::STATUS_NETWORK_AUTHENTICATION_REQUIRED => 'Network Authentication Required',
        599 => 'Network Connect Timeout Error',
    ];

    /**
     * EOL characters used for HTTP response.
     *
     * @var string
     */
     const EOL = "\r\n";

    /**
     * Create new HTTP response.
     *
     * @param int                   $status  The response status code.
     * @param HeadersInterface|null $headers The response headers.
     * @param StreamInterface|null  $body    The response body.
     */
    public function __construct(
        $status = StatusCodeInterface::STATUS_OK,
        HeadersInterface $headers = null,
        StreamInterface $body = null
    ) {
        $this->status = $this->filterStatus($status);
        $this->headers = $headers ? $headers : new Headers();
        $this->body = $body ? $body : new Body(fopen('php://temp', 'r+'));
    }

    /**
     * This method is applied to the cloned object
     * after PHP performs an initial shallow-copy. This
     * method completes a deep-copy by creating new objects
     * for the cloned object's internal reference pointers.
     */
    public function __clone()
    {
        $this->headers = clone $this->headers;
    }

    /*******************************************************************************
     * Status
     ******************************************************************************/

    /**
     * Gets the response status code.
     *
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @return int Status code.
     */
    public function getStatusCode()
    {
        return $this->status;
    }

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     *
     * If no reason phrase is specified, implementations MAY choose to default
     * to the RFC 7231 or IANA recommended reason phrase for the response's
     * status code.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated status and reason phrase.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @param int $code The 3-digit integer result code to set.
     * @param string $reasonPhrase The reason phrase to use with the
     *     provided status code; if none is provided, implementations MAY
     *     use the defaults as suggested in the HTTP specification.
     * @return static
     * @throws \InvalidArgumentException For invalid status code arguments.
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $code = $this->filterStatus($code);

        if (!is_string($reasonPhrase) && !method_exists($reasonPhrase, '__toString')) {
            throw new InvalidArgumentException('ReasonPhrase must be a string');
        }

        $clone = clone $this;
        $clone->status = $code;
        if ($reasonPhrase === '' && isset(static::$messages[$code])) {
            $reasonPhrase = static::$messages[$code];
        }

        if ($reasonPhrase === '') {
            throw new InvalidArgumentException('ReasonPhrase must be supplied for this code');
        }

        $clone->reasonPhrase = $reasonPhrase;

        return $clone;
    }

    /**
     * Filter HTTP status code.
     *
     * @param  int $status HTTP status code.
     * @return int
     * @throws \InvalidArgumentException If an invalid HTTP status code is provided.
     */
    protected function filterStatus($status)
    {
        if (!is_integer($status) || $status < StatusCodeInterface::STATUS_CONTINUE || $status > 599) {
            throw new InvalidArgumentException('Invalid HTTP status code');
        }

        return $status;
    }

    /**
     * Gets the response reason phrase associated with the status code.
     *
     * Because a reason phrase is not a required element in a response
     * status line, the reason phrase value MAY be null. Implementations MAY
     * choose to return the default RFC 7231 recommended reason phrase (or those
     * listed in the IANA HTTP Status Code Registry) for the response's
     * status code.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @return string Reason phrase; must return an empty string if none present.
     */
    public function getReasonPhrase()
    {
        if ($this->reasonPhrase) {
            return $this->reasonPhrase;
        }
        if (isset(static::$messages[$this->status])) {
            return static::$messages[$this->status];
        }
        return '';
    }

    /*******************************************************************************
     * Body
     ******************************************************************************/

    /**
     * Write data to the response body.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * Proxies to the underlying stream and writes the provided data to it.
     *
     * @param string $data
     * @return $this
     */
    public function write($data)
    {
        $this->getBody()->write($data);

        return $this;
    }

    /*******************************************************************************
     * Response Helpers
     ******************************************************************************/

    /**
     * Redirect.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * This method prepares the response object to return an HTTP Redirect
     * response to the client.
     *
     * @param  string|UriInterface $url    The redirect destination.
     * @param  int|null            $status The redirect HTTP status code.
     * @return static
     */
    public function withRedirect($url, $status = null)
    {
        $responseWithRedirect = $this->withHeader('Location', (string)$url);

        if (is_null($status) && $this->getStatusCode() === StatusCodeInterface::STATUS_OK) {
            $status = StatusCodeInterface::STATUS_FOUND;
        }

        if (!is_null($status)) {
            return $responseWithRedirect->withStatus($status);
        }

        return $responseWithRedirect;
    }

    /**
     * Json.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * This method prepares the response object to return an HTTP Json
     * response to the client.
     *
     * @param  mixed  $data   The data
     * @param  int    $status The HTTP status code.
     * @param  int    $encodingOptions Json encoding options
     * @throws \RuntimeException
     * @return static
     */
    public function withJson($data, $status = null, $encodingOptions = 0)
    {
        $response = $this->withBody(new Body(fopen('php://temp', 'r+')));
        $response->body->write($json = json_encode($data, $encodingOptions));

        // Ensure that the json encoding passed successfully
        if ($json === false) {
            throw new \RuntimeException(json_last_error_msg(), json_last_error());
        }

        $responseWithJson = $response->withHeader('Content-Type', 'application/json;charset=utf-8');
        if (isset($status)) {
            return $responseWithJson->withStatus($status);
        }
        return $responseWithJson;
    }

    /**
     * Is this response empty?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return in_array($this->getStatusCode(), [
            StatusCodeInterface::STATUS_NO_CONTENT,
            StatusCodeInterface::STATUS_RESET_CONTENT,
            StatusCodeInterface::STATUS_NOT_MODIFIED
        ]);
    }

    /**
     * Is this response informational?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isInformational()
    {
        return $this->getStatusCode() >= StatusCodeInterface::STATUS_CONTINUE
            && $this->getStatusCode() < StatusCodeInterface::STATUS_OK;
    }

    /**
     * Is this response OK?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isOk()
    {
        return $this->getStatusCode() === StatusCodeInterface::STATUS_OK;
    }

    /**
     * Is this response successful?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->getStatusCode() >= StatusCodeInterface::STATUS_OK
            && $this->getStatusCode() < StatusCodeInterface::STATUS_MULTIPLE_CHOICES;
    }

    /**
     * Is this response a redirect?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isRedirect()
    {
        return in_array($this->getStatusCode(), [
            StatusCodeInterface::STATUS_MOVED_PERMANENTLY,
            StatusCodeInterface::STATUS_FOUND,
            StatusCodeInterface::STATUS_SEE_OTHER,
            StatusCodeInterface::STATUS_TEMPORARY_REDIRECT,
            StatusCodeInterface::STATUS_PERMANENT_REDIRECT
        ]);
    }

    /**
     * Is this response a redirection?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isRedirection()
    {
        return $this->getStatusCode() >= StatusCodeInterface::STATUS_MULTIPLE_CHOICES
            && $this->getStatusCode() < StatusCodeInterface::STATUS_BAD_REQUEST;
    }

    /**
     * Is this response forbidden?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     * @api
     */
    public function isForbidden()
    {
        return $this->getStatusCode() === StatusCodeInterface::STATUS_FORBIDDEN;
    }

    /**
     * Is this response not Found?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isNotFound()
    {
        return $this->getStatusCode() === StatusCodeInterface::STATUS_NOT_FOUND;
    }

    /**
     * Is this response a client error?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isClientError()
    {
        return $this->getStatusCode() >= StatusCodeInterface::STATUS_BAD_REQUEST
            && $this->getStatusCode() < StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR;
    }

    /**
     * Is this response a server error?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isServerError()
    {
        return $this->getStatusCode() >= StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR
            && $this->getStatusCode() < 600;
    }

    /**
     * Convert response to string.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return string
     */
    public function __toString()
    {
        $output = sprintf(
            'HTTP/%s %s %s',
            $this->getProtocolVersion(),
            $this->getStatusCode(),
            $this->getReasonPhrase()
        );
        $output .= Response::EOL;
        foreach ($this->getHeaders() as $name => $values) {
            $output .= sprintf('%s: %s', $name, $this->getHeaderLine($name)) . Response::EOL;
        }
        $output .= Response::EOL;
        $output .= (string)$this->getBody();

        return $output;
    }
}
