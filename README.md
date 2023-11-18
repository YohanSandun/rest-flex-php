# RestFlexPHP

`RestFlexPHP` is a lightweight PHP package designed to simplify handling RESTful requests and responses. It includes a flexible controller class (`RestFlexController`) for routing requests and a request class (`RestFlexRequest`) for encapsulating request-related data.

## Installation

You can install `RestFlexPHP` using [Composer](https://getcomposer.org/):

```bash
composer require yohan/rest-flex-php
```

## Setting up URL Rewriting

To enable URL rewriting for your RESTful API, you need to create an `.htaccess` file in the same directory as your main logic file (e.g., `index.php`). The file should contain the following mod_rewrite rules:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /path-from-the-root/
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ ./index.php?url=$1 [QSA,L]
</IfModule>
```

## Usage
### RestFlexController

The `RestFlexController` class provides a simple routing mechanism for handling HTTP requests. You can define routes using the get, post, put, and delete methods.

Example:
```php
<?php
use RestFlexPHP\RestFlexController;
use RestFlexPHP\RestFlexRequest;

// Retrieving a controller instance
$controller = RestFlexController::getController();

// Define a route for GET requests
$controller->get('/users/{id}', function (RestFlexRequest $request) {
    // Handle the GET request for the "/users/{id}" path
    $userId = $request->pathVariables['id'];
    
    // Your custom logic here...
    
    // Finally send the response to the client
    $request->sendResponse($data, HttpStatus::OK);
});

// Other route definitions...

// Handle unmatched routes
$controller->noMapping(function (RestFlexRequest $request) {
    // Handle unmatched routes or provide a default 404 response
    $request->sendResponse('Route not found', HttpStatus::NOT_FOUND);
});
?>
```

Example: Separating different routes with separate classes.

Here's a simple example demonstrating how to use `RestFlexPHP` to create a basic UserController with user-related routes.

#### UserController.php
```php
<?php
use RestFlexPHP\RestFlexController as RFC;
use RestFlexPHP\RestFlexRequest as RFR;

class UserController
{
    public function __construct(RFC $controller)
    {
        $controller->get('users/getAllUsers', [$this, 'getAllUsers']);
        $controller->get('users/getUser/{id}', [$this, 'getUser']);
    }

    public function getAllUsers(RFR $req)
    {
        $req->sendResponse(/* all your users */);
    }

    public function getUser(RFR $req)
    {
        $userId = $req->pathVariables['id'];
        // $user = your logic to find user by user id from the database.
        $req->sendResponse($user);
    }
}
?>
```

#### index.php
```php
<?php
use RestFlexPHP\RestFlexController;

$controller = RestFlexController::getController();

// Instantiate UserController, which will define routes
new UserController($controller);

// Handle unmatched routes
$controller->noMapping();
?>
```

### RestFlexRequest

The `RestFlexRequest` class encapsulates request-related data and provides a convenient method (sendResponse) for sending JSON responses.

Example:
```php
$controller->put('/users/{id}', function (RestFlexRequest $request) {
    // Access path variables using pathVariables array
    $userId = $request->pathVariables['id'];
    
    // Access request body using body property
    $data = $request->body;
    
    // Access URL query parameters using params property
    $params = $request->params;
    
    // Finally send the response back to the client
    $request->sendResponse($data, HttpStatus::OK);
});
```

## Contributing
If you find a bug, have a feature request, or want to contribute, please open an issue or submit a pull request. Contributions are welcome!

## License
This package is licensed under the [Apache-2.0 License](https://www.apache.org/licenses/LICENSE-2.0.txt)