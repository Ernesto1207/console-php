<?php
function crearMigracion($nombre, $campos = [])
{
    $nombre = strtolower($nombre);
    $nombreClase = str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $nombre)));
    $fecha = date('Ymd_His');
    $archivo = __DIR__ . "/../database/migrations/{$fecha}_{$nombre}.php";

    $sqlUp = "CREATE TABLE IF NOT EXISTS {$nombre} (";
    $sqlUpFields = [];

    foreach ($campos as $campo) {
        $sqlUpFields[] = "$campo VARCHAR(255) NOT NULL";
    }

    array_unshift($sqlUpFields, 'id INT AUTO_INCREMENT PRIMARY KEY');

    $sqlUp .= implode(", ", $sqlUpFields);
    $sqlUp .= ") ENGINE=InnoDB;";

    $sqlDown = "DROP TABLE IF EXISTS {$nombre};";

    $codigo = <<<PHP
<?php
class $nombreClase {
    public function up(\$conn)
    {
        \$sql = "$sqlUp";
        \$conn->exec(\$sql);
    }

    public function down(\$conn)
    {
        \$sql = "$sqlDown";
        \$conn->exec(\$sql);
    }
}
PHP;

    if (!is_dir(__DIR__ . '/../database/migrations')) {
        mkdir(__DIR__ . '/../database/migrations', 0755, true);
    }

    file_put_contents($archivo, $codigo);
    echo "Migración creada: $archivo\n";
}
