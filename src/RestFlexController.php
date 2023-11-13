<?php

namespace RestFlexPHP;

require_once 'HttpMethod.php';
require_once 'HttpStatus.php';
require_once 'RestFlexRequest.php';

class RestFlexController {
    public $method;
    public $request;
    public $url;
    private $handled = false;

    public function __construct($method, $data, $params, $url)
    {
        $this->method = $method;
        $this->request = new RestFlexRequest($data, $params);
        $this->url = $url;
    }

    public static function getController(array $allowedMethods = [HttpMethod::GET, HttpMethod::POST, HttpMethod::PUT, HttpMethod::DELETE, HttpMethod::OPTIONS], $allowedOrigins = '*', array $allowedHeaders = ['Content-Type']) {
        header('Access-Control-Allow-Origin: ' . $allowedOrigins);
        header('Content-Type: application/json');
        header('Access-Control-Allow-Methods: ' . implode(', ', $allowedMethods));
        header('Access-Control-Allow-Headers: ' . implode(', ', $allowedHeaders));

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit;
        }
        else if (in_array($_SERVER['REQUEST_METHOD'], $allowedMethods)) {
            $jsonInput = file_get_contents('php://input');
            $url = $_GET['url'] ?? '';
            unset($_GET['url']);
            return new RestFlexController(
                $_SERVER['REQUEST_METHOD'], json_decode($jsonInput, true), $_GET, $url);
        }
        else {
            http_response_code(HttpStatus::METHOD_NOT_ALLOWED);
            exit;
        }
    }

    public function get(string $path, callable $callback) {
        if ($this->method === HttpMethod::GET && $path === $this->url) {
            $this->handled = true;
            $callback($this->request);
            exit;
        }
    }

    public function post(string $path, callable $callback) {
        if ($this->method === HttpMethod::POST && $path === $this->url) {
            $this->handled = true;
            $callback($this->request);
            exit;
        }
    }

    public function noMapping(callable $callback = null) {
        if (!$this->handled) {
            $this->handled = true;
            if ($callback === null) {
                $this->request->sendResponse("404 Not Found", HttpStatus::NOT_FOUND);
            } else {
                $callback($this->request);
            }
            exit;
        }
    }

    public static function generateHtaccess() {
        echo "Running generateHtaccess...\n";
        $content = <<<EOT
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /rest-flex-php/src/
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ ./index.php?url=$1 [QSA,L]
</IfModule>
EOT;
        file_put_contents(__DIR__ . '../.htaccess', $content);
        echo "Finish Running generateHtaccess...\n" . __DIR__;
    }
}