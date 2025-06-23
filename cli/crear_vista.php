<?php
function crearVista(string $rutaVista)
{
    // Normaliza la ruta
    $rutaVista = str_replace(['\\', '//'], '/', $rutaVista);

    // Agrega .php si no lo tiene
    if (!str_ends_with($rutaVista, '.php')) {
        $rutaVista .= '.php';
    }

    $carpetaBase = __DIR__ . '/../app/views/';
    $rutaCompleta = $carpetaBase . $rutaVista;

    $carpetaDestino = dirname($rutaCompleta);

    if (!is_dir($carpetaDestino)) {
        mkdir($carpetaDestino, 0755, true);
    }

    if (file_exists($rutaCompleta)) {
        echo "ERROR: La vista $rutaCompleta ya existe.\n";
        exit(1);
    }

    $contenido = <<<HTML
<section class="introducir clase">
    <!-- Vista: $rutaVista -->
</section>

HTML;

    file_put_contents($rutaCompleta, $contenido);

    echo "Vista creada en $rutaCompleta\n";
}
