<?php

require_once 'HttpMethod.php';
require_once 'RestFlexRequest.php';
require_once 'HttpStatus.php';

function initController(array $allowedMethods = [HttpMethod::GET, HttpMethod::POST, HttpMethod::PUT, HttpMethod::DELETE, HttpMethod::OPTIONS], $allowedOrigins = '*', array $allowedHeaders = ['Content-Type']) {
    header('Access-Control-Allow-Origin: ' . $allowedOrigins);
    header('Content-Type: application/json');
    header('Access-Control-Allow-Methods: ' . implode(', ', $allowedMethods));
    header('Access-Control-Allow-Headers: ' . implode(', ', $allowedHeaders));

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        exit;
    }
    else if (in_array($_SERVER['REQUEST_METHOD'], $allowedMethods)) {
        $jsonInput = file_get_contents('php://input');
        return new RestFlexRequest($_SERVER['REQUEST_METHOD'], json_decode($jsonInput, true), $_GET);
    }
    else {
        http_response_code(HttpStatus::METHOD_NOT_ALLOWED);
        exit;
    }
}