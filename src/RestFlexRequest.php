<?php

class RestFlexRequest {
    public $body;
    public $params;

    /**
     * @param $body
     * @param $params
     */
    public function __construct($body, $params)
    {
        $this->body = $body;
        $this->params = $params;
    }

    public function sendResponse($data, $status = HttpStatus::OK) {
        http_response_code($status);
        echo json_encode($data);
    }
}