<?php

require_once 'RestController.php';

$req = initController([HttpMethod::GET, HttpMethod::POST]);

$req->sendResponse(array(
    'name_given' => $req->data['name'],
    'age_given' => $req->data['age']
));