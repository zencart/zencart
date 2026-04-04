<?php

header('X-Fixture: storefront');
echo json_encode([
    'get' => $_GET,
    'post' => $_POST,
    'request' => $_REQUEST,
    'cookies' => $_COOKIE,
    'server' => [
        'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'] ?? null,
        'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? null,
        'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? null,
        'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? null,
        'SCRIPT_FILENAME' => $_SERVER['SCRIPT_FILENAME'] ?? null,
        'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? null,
    ],
]);
