<?php
function crearRuta($urlPattern, $controlador, $metodo, $conId = false)
{
    $archivoRutas = __DIR__ . '/../index.php';

    // 1. Arma el regex correctamente
    $patternRegex = preg_replace('/\{id\}/', '(\\\\d+)', $urlPattern); // {id} => (\d+)
    $regex = "#^$patternRegex\$#";

    // 2. Arma el bloque de ruta
    if (strpos($urlPattern, '{id}') !== false && $conId) {
        $codigo = <<<PHP

    case preg_match('$regex', \$url, \$matches):
        \$id = \$matches[1];
        (new $controlador())->$metodo(\$id);
        break;

PHP;
    } else {
        $codigo = <<<PHP

    case preg_match('$regex', \$url):
        (new $controlador())->$metodo();
        break;

PHP;
    }

    // 3. Busca el bloque 404 y agrega ANTES de él
    $contenido = file_get_contents($archivoRutas);
    $lineas = explode("\n", $contenido);

    $insertIndex = null;
    foreach ($lineas as $i => $linea) {
        if (strpos($linea, '// 4. Ruta por defecto (404)') !== false) {
            $insertIndex = $i;
            break;
        }
    }

    if ($insertIndex === null) {
        echo "ERROR: No se encontró el bloque '// 4. Ruta por defecto (404)' en el archivo de rutas.\n";
        exit(1);
    }

    array_splice($lineas, $insertIndex, 0, rtrim($codigo));
    $nuevoContenido = implode("\n", $lineas);
    file_put_contents($archivoRutas, $nuevoContenido);

    echo "Ruta agregada:\n$codigo\n";
}
