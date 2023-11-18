<?php

namespace RestFlexPHP;

/**
 * Class RestFlexRequest
 *
 * Represents an HTTP request, encapsulating request data and parameters.
 *
 * @package RestFlexPHP
 */
class RestFlexRequest {

    /** @var mixed The request body data. */
    public $body;

    /** @var array The request parameters. */
    public $params;

    /** @var array The path variables extracted from the request URL. */
    public $pathVariables = [];

    /**
     * RestFlexRequest constructor.
     *
     * @param mixed $body   The request body data, typically an associative array.
     * @param array $params The request parameters, typically received from $_GET.
     */
    public function __construct($body, array $params)
    {
        $this->body = $body;
        $this->params = $params;
    }

    /**
     * Sends a JSON response and sets the HTTP status code.
     *
     * @param mixed $data   The data to be encoded and sent as the response body.
     * @param int $status The HTTP status code to be set. Defaults to HttpStatus::OK (200).
     *
     * @return void
     */
    public function sendResponse($data, int $status = HttpStatus::OK) {
        http_response_code($status);
        echo json_encode($data);
        exit();
    }
}