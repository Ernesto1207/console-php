<?php
// Mostrar errores para debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cargar el autoloader de clases (controladores, modelos, etc.)
require_once __DIR__ . '/autoload.php';

// Cargar los paquetes de Composer (dependencias como PDO, etc.)
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/helpers/auth.php';
require_once __DIR__ . '/config/database.php';

// Ruteo basado en la URL
$url = $_GET['url'] ?? 'login';
$url = trim($url, '/');
$segmentos = explode('/', $url);
$seccion = $segmentos[0];

// ------------------------------------------------------------
// ORDEN RECOMENDADO PARA LAS RUTAS EN ESTE SWITCH:
// ------------------------------------------------------------
// 1. Rutas específicas y estáticas (igualdad exacta con ===).
// 2. Rutas dinámicas específicas con parámetros (preg_match).
// 3. Rutas de utilidad o pruebas.
// 4) Ruta por defecto (404).
// ------------------------------------------------------------

switch (true) {

    // 1. Rutas específicas y estáticas


    // 2. Rutas dinámicas

    // Rutas de dashboard de empresa
    
    // 3. Rutas de utilidad o pruebas
    


    case preg_match('#^gente$#', $url):
        (new GenteController())->index();
        break;

    case preg_match('#^gente/crear$#', $url):
        (new GenteController())->crear();
        break;

    case preg_match('#^gente/editar/(\d+)$#', $url, $matches):
        $id = $matches[1];
        (new GenteController())->editar($id);
        break;

    case preg_match('#^gente/actualizar/(\d+)$#', $url, $matches):
        $id = $matches[1];
        (new GenteController())->actualizar($id);
        break;

    case preg_match('#^gente/eliminar/(\d+)$#', $url, $matches):
        $id = $matches[1];
        (new GenteController())->eliminar($id);
        break;
    // 4. Ruta por defecto (404)
    default:
        http_response_code(404);
        require __DIR__ . '/app/views/404.html';
        break;
}
// ------------------------------------------------------------
// Fin del switch