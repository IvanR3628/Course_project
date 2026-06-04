<?php
    require_once 'routes.php';
    $method = $_SERVER['REQUEST_METHOD'];
    $request = $_SERVER['REQUEST_URI'];
    handleRequest($method, $request);
?>