<?php

namespace RestFlexPHP;

use RuntimeException;

require_once 'HttpMethod.php';
require_once 'HttpStatus.php';
require_once 'RestFlexRequest.php';

/**
 * RestFlexController class handles RESTful requests and provides a simple routing mechanism.
 *
 * This class is responsible for creating a controller instance based on the incoming HTTP request,
 * handling CORS headers, setting appropriate content types, and mapping routes to callback functions.
 *
 * @package RestFlexPHP
 */
class RestFlexController {

    /** @var string The HTTP method of the request (e.g., GET, POST, PUT, DELETE). */
    public $method;

    /** @var RestFlexRequest The request object containing data and parameters. */
    public $request;

    /** @var string The URL of the request. */
    public $url;

    /** @var bool Indicates whether the request has been handled. */
    private $handled = false;

    /**
     * Constructs a new RestFlexController instance.
     *
     * @param string $method  The HTTP method of the request (e.g., GET, POST, PUT, DELETE).
     * @param mixed  $data    The request data, typically an associative array.
     * @param array $params  The URL parameters, typically received from $_GET.
     * @param string $url     The URL of the request.
     */
    private function __construct(string $method, $data, array $params, string $url)
    {
        $this->method = $method;
        $this->request = new RestFlexRequest($data, $params);
        $this->url = $url;
    }

    private function startsWith($haystack, $needle): bool
    {
        $length = strlen($needle);
        return substr($haystack, 0, $length) === $needle;
    }

    private function endsWith($haystack, $needle): bool
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }
        return substr($haystack, -$length) === $needle;
    }

    /**
     * Retrieves a RestFlexController instance based on the incoming HTTP request.
     *
     * This method is responsible for handling CORS headers, setting the appropriate content type,
     * and creating a RestFlexController instance for further request processing.
     *
     * @param array|string $allowedMethods   An array of allowed HTTP methods or a string representing
     *                                       the allowed origins for CORS. Default is all methods
     *                                       (GET, POST, PUT, DELETE, OPTIONS).
     * @param string       $allowedOrigins   The allowed origins for CORS. Defaults to '*'.
     * @param array        $allowedHeaders   An array of allowed HTTP headers for CORS.
     *                                       Default is ['Content-Type'].
     *
     * @return RestFlexController|null       A RestFlexController instance if the request is valid,
     *                                       or null if the request method is not allowed.
     */
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
            $url = $_GET['__url__'] ?? '';
            unset($_GET['__url__']);
            return new RestFlexController(
                $_SERVER['REQUEST_METHOD'], json_decode($jsonInput, true), $_GET, $url);
        }
        else {
            http_response_code(HttpStatus::METHOD_NOT_ALLOWED);
            exit;
        }
    }

    private function parsePathVariables($path) {
        $segments = explode("/", $path);
        $reqSegments = explode("/", $this->url);

        if (count($segments) !== count($reqSegments)) {
            return null;
        }

        $vars = [];
        for ($i = 0; $i < count($segments); $i++) {
            if ($segments[$i] !== $reqSegments[$i]) {
                if (RestFlexController::startsWith($segments[$i], "{") && RestFlexController::endsWith($segments[$i], "}")) {
                    $vars[trim($segments[$i], "{}")] = $reqSegments[$i];
                } else {
                    return null;
                }
            }
        }
        return $vars;
    }

    private function map(string $method, string $path, $callback) {
        if ($this->method === $method) {
            $vars = $this->parsePathVariables($path);
            if ($vars !== null) {
                $this->handled = true;
                $this->request->pathVariables = $vars;
                if (is_array($callback)) {
                    call_user_func($callback, $this->request);
                } else {
                    $callback($this->request);
                }
                exit();
            }
            if ($path === $this->url) {
                $this->handled = true;
                if (is_array($callback)) {
                    call_user_func($callback, $this->request);
                } else {
                    $callback($this->request);
                }
                exit();
            }
        }
    }

    /**
     * Maps a callback function to handle HTTP GET requests with a specific path.
     *
     * This method is used to associate a callback function with a specific HTTP GET request path.
     * When a matching GET request is received, the provided callback function will be executed,
     * allowing custom handling of the request.
     *
     * @param string   $path      The path to match against the incoming request URL.
     * @param callable|array $callback The callback function or an array of object and method name to handle
     *                                 the request. If an array is provided, it should have two elements:
     *                                 the object instance and the method name (e.g., [$userController, 'getUser']).
     *                                 The method should accept a RestFlexRequest object as its only parameter.
     * @return void
     */
    public function get(string $path, $callback) {
        $this->map(HttpMethod::GET, $path, $callback);
    }

    /**
     * Maps a callback function to handle HTTP POST requests with a specific path.
     *
     * This method is used to associate a callback function with a specific HTTP POST request path.
     * When a matching POST request is received, the provided callback function will be executed,
     * allowing custom handling of the request.
     *
     * @param string   $path      The path to match against the incoming request URL.
     * @param callable|array $callback The callback function or an array of object and method name to handle
     *                                 the request. If an array is provided, it should have two elements:
     *                                 the object instance and the method name (e.g., [$userController, 'createUser']).
     *                                 The method should accept a RestFlexRequest object as its only parameter.
     * @return void
     */
    public function post(string $path, $callback) {
        $this->map(HttpMethod::POST, $path, $callback);
    }

    /**
     * Maps a callback function to handle HTTP PUT requests with a specific path.
     *
     * This method is used to associate a callback function with a specific HTTP PUT request path.
     * When a matching PUT request is received, the provided callback function will be executed,
     * allowing custom handling of the request.
     *
     * @param string   $path      The path to match against the incoming request URL.
     * @param callable|array $callback The callback function or an array of object and method name to handle
     *                                 the request. If an array is provided, it should have two elements:
     *                                 the object instance and the method name (e.g., [$userController, 'updateUser']).
     *                                 The method should accept a RestFlexRequest object as its only parameter.
     * @return void
     */
    public function put(string $path, $callback) {
        $this->map(HttpMethod::PUT, $path, $callback);
    }

    /**
     * Maps a callback function to handle HTTP DELETE requests with a specific path.
     *
     * This method is used to associate a callback function with a specific HTTP DELETE request path.
     * When a matching DELETE request is received, the provided callback function will be executed,
     * allowing custom handling of the request.
     *
     * @param string   $path      The path to match against the incoming request URL.
     * @param callable|array $callback The callback function or an array of object and method name to handle
     *                                 the request. If an array is provided, it should have two elements:
     *                                 the object instance and the method name (e.g., [$userController, 'deleteUser']).
     *                                 The method should accept a RestFlexRequest object as its only parameter.
     * @return void
     */
    public function delete(string $path, $callback) {
        $this->map(HttpMethod::DELETE, $path, $callback);
    }

    /**
     * Handle the case where no route is matched.
     *
     * This method should call at the end of the main logic to handle requests when no route is matched for the current request. It allows
     * providing a callback to customize the behavior or use a default 404 response.
     *
     * @param callable|null $callback A callback function to handle the unmatched route. If null,
     *                                a default 404 response is sent. The callback should accept
     *                                a RestFlexRequest object as its only parameter.
     * @return void
     */
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

    /**
     * Generates an .htaccess file with mod_rewrite rules for routing requests to a specified index file.
     *
     * This function creates an .htaccess file in the same directory as the script,
     * containing mod_rewrite rules to route requests to the specified index file.
     *
     * @param string $indexFile The name of the file with main logic (e.g., "index.php").
     * @return void
     * @throws RuntimeException If unable to write to the .htaccess file.
     */
    public static function generateHtaccess(string $indexFile) {
        $dir = __DIR__;
        $content = <<<EOT
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /$dir/
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ ./$indexFile?__url__=$1 [QSA,L]
</IfModule>
EOT;
        $success = file_put_contents(__DIR__ . '../.htaccess', $content);
        if ($success === false) {
            throw new RuntimeException('Unable to write to the .htaccess file.');
        }
    }
}