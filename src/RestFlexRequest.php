<?php
class RestFlexRequest {
    public $method;
    public $data;
    public $params;

    /**
     * @param $method
     * @param $data
     */
    public function __construct($method, $data, $params)
    {
        $this->method = $method;
        $this->data = $data;
        $this->params = $params;
    }

    public function sendResponse($data, $status = 200) {
        http_response_code($status);
        echo json_encode($data);
    }
}