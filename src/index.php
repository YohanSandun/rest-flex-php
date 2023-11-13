<?php

require_once 'RestFlexController.php';

$controller = RestFlexController::getController();

$controller->post('home', function (RestFlexRequest $req) {
    $req->sendResponse('This is home mapping with POST request 2');
});

$controller->get('home', function (RestFlexRequest $req) {
    $req->sendResponse('This is home mapping with GET request 2');
});

$controller->noMapping();