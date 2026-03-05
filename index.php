<?php
session_start();
// index.php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/app/core/Controller.php';

$controller = $_GET['c'] ?? 'catalog';
$action     = $_GET['a'] ?? 'index';

$controllerClass = ucfirst($controller) . 'Controller';
$controllerFile  = __DIR__ . "/app/controllers/{$controllerClass}.php";

if (!file_exists($controllerFile)) {
    http_response_code(404);
    echo "Controlador no encontrado";
    exit;
}

require_once $controllerFile;

if (!class_exists($controllerClass)) {
    echo "Clase controlador no existe";
    exit;
}

$ctrl = new $controllerClass();

if (!method_exists($ctrl, $action)) {
    http_response_code(404);
    echo "Acción no encontrada";
    exit;
}

$ctrl->$action();