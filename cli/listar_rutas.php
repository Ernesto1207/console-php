<?php
function listarRutas($archivo)
{
    if (!file_exists($archivo)) {
        echo "No existe el archivo de rutas: $archivo\n";
        return;
    }

    $contenido = file_get_contents($archivo);

    // Obtener bloques case
    preg_match_all('/case\s+(.*?)\s*:\s*(.*?)break;/s', $contenido, $casos, PREG_SET_ORDER);

    echo "\nMétodo\t\tRuta\t\t\t\t\t\t\tControlador@método\n";
    echo "------\t\t----\t\t\t\t\t\t\t-----------------\n";

    foreach ($casos as $caso) {
        $condicion = trim($caso[1]);
        $bloque = $caso[2];

        // Ruta estática: $url === 'login'
        if (preg_match("/\\\$url\s*===\s*['\"]([^'\"]+)['\"]/", $condicion, $rutaMatch)) {
            $ruta = $rutaMatch[1];
        }
        // Ruta dinámica: preg_match('#^admin/empresas$#', $url)
        elseif (preg_match("/preg_match\(['\"](.*?)['\"],/", $condicion, $rutaMatch)) {
            $ruta = $rutaMatch[1];
        } else {
            continue; // No es una ruta reconocida
        }

        // Buscar controlador y método en el cuerpo del case
        if (preg_match("/new\s+([A-Za-z0-9_]+)\s*\(\)\s*->\s*([a-zA-Z0-9_]+)\s*\(/", $bloque, $controladorMatch)) {
            $controlador = $controladorMatch[1];
            $metodo = $controladorMatch[2];
            echo "GET\t\t$ruta\t\t$controlador@$metodo\n";
        }
    }
}
