<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';

function obtenerCamposDeTabla($tabla)
{
    $database = new Database();
    $conn = $database->conectar();

    $sql = "DESCRIBE {$tabla}";
    $stmt = $conn->query($sql);

    $columnas = $stmt->fetchAll();

    $campos = [];
    foreach ($columnas as $columna) {
        if ($columna['Field'] != 'id') { 
            $campos[] = $columna['Field'];
        }
    }

    return $campos;
}

function crearCRUD($entidad, $tabla, $campos)
{
    $Modelo      = ucfirst($entidad);
    $Controlador = ucfirst($entidad) . 'Controller';
    $vistaDir    = strtolower($entidad);
    $camposArray = obtenerCamposDeTabla($tabla);
    // --------- 1. Crear Modelo ---------
    $modeloPath = __DIR__ . "/../app/models/{$Modelo}.php";
    if (!file_exists($modeloPath)) {
        crearModelo($Modelo, $tabla, $camposArray); // Usamos función abajo
        echo "Modelo creado: $modeloPath\n";
    } else {
        echo "Modelo ya existe: $modeloPath\n";
    }

    // --------- 2. Crear Controlador ---------
    $controladorPath = __DIR__ . "/../app/controllers/{$Controlador}.php";
    if (!file_exists($controladorPath)) {
        // Aquí se usa la función del archivo externo
        require_once __DIR__ . '/crear_controlador.php';
        crearControladorCRUD($Controlador, $Modelo, $vistaDir, $campos); // Cambié de $camposArray a $campos
        echo "Controlador creado: $controladorPath\n";
    } else {
        echo "Controlador ya existe: $controladorPath\n";
    }

    // --------- 3. Crear Vistas ---------
    $viewsDir = __DIR__ . "/../app/views/{$vistaDir}";
    if (!is_dir($viewsDir)) {
        mkdir($viewsDir, 0755, true);
    }

    // index.php
    $index = "<h1 class=\"text-center\"><?= \$titulo ?></h1>
    <a href=\"/{$vistaDir}/crear\" class=\"btn btn-primary\">Crear nuevo</a>
    <?php if (!empty(\${$vistaDir}s)): ?>
    <table class=\"table table-bordered table-striped mt-3\">
    <thead>
    <tr>";
    foreach ($camposArray as $campo) {
        $index .= "<th>$campo</th>";
    }
    $index .= "<th>Acciones</th></tr>
    </thead>
    <tbody>
    <?php foreach (\${$vistaDir}s as \$item): ?>
    <tr>";
    foreach ($camposArray as $campo) {
        $index .= "<td><?= htmlspecialchars(\$item['$campo']) ?></td>";
    }
    $index .= "<td>
    <a href=\"/{$vistaDir}/editar/<?= \$item['id'] ?>\" class=\"btn btn-warning btn-sm\">Editar</a> |
    <a href=\"/{$vistaDir}/eliminar/<?= \$item['id'] ?>\" class=\"btn btn-danger btn-sm\" onclick=\"return confirm('¿Seguro de eliminar?');\">Eliminar</a>
    </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    <?php else: ?>
    <p>No hay registros.</p>
    <?php endif; ?>";
    file_put_contents("$viewsDir/index.php", $index);


    // crear.php
    $crear = "<h1 class=\"text-center\"><?= \$titulo ?></h1>
    <form method=\"POST\" class=\"mt-4\">
    ";
    foreach ($camposArray as $campo) {
        $crear .= "<div class=\"form-group\">
    <label for=\"$campo\">$campo</label>
    <input type=\"text\" name=\"$campo\" id=\"$campo\" class=\"form-control\" value=\"<?= isset(\$data['$campo']) ? htmlspecialchars(\$data['$campo']) : '' ?>\">
    </div>\n";
    }
    $crear .= "<button type=\"submit\" class=\"btn btn-success\">Guardar</button>
    </form>
    <a href=\"/{$vistaDir}\" class=\"btn btn-secondary mt-3\">Volver</a>";
    file_put_contents("$viewsDir/crear.php", $crear);


    // editar.php
    $editar = "<h1 class=\"text-center\"><?= \$titulo ?></h1>
    <form method=\"POST\" action=\"/{$vistaDir}/actualizar/<?= \${$vistaDir}['id']; ?>\" class=\"mt-4\">
    ";
        foreach ($camposArray as $campo) {
            $editar .= "<div class=\"form-group\">
    <label for=\"$campo\">$campo</label>
    <input type=\"text\" name=\"$campo\" id=\"$campo\" class=\"form-control\" value=\"<?= isset(\${$vistaDir}['$campo']) ? htmlspecialchars(\${$vistaDir}['$campo']) : '' ?>\">
    </div>\n";
        }
        $editar .= "<button type=\"submit\" class=\"btn btn-primary\">Actualizar</button>
    </form>
    <a href=\"/{$vistaDir}\" class=\"btn btn-secondary mt-3\">Volver</a>";
    file_put_contents("$viewsDir/editar.php", $editar);


    echo "Vistas creadas en: $viewsDir\n";

    // --------- 4. Crear Rutas ---------
    crearRuta("{$vistaDir}", $Controlador, 'index');
    crearRuta("{$vistaDir}/crear", $Controlador, 'crear');
    crearRuta("{$vistaDir}/editar/{id}", $Controlador, 'editar', true);
    crearRuta("{$vistaDir}/actualizar/{id}", $Controlador, 'actualizar', true);
    crearRuta("{$vistaDir}/eliminar/{id}", $Controlador, 'eliminar', true);
}

// ---- Función crearModelo con PHPDoc bilingüe ----
function crearModelo($nombreModelo, $nombreTabla, $camposArray)
{
    $codigo = <<<PHP
<?php

class $nombreModelo
{
    private \$conn;
    private \$table = '$nombreTabla';

    public function __construct()
    {
        \$database = new Database();
        \$this->conn = \$database->conectar();
    }

    /**
     * Obtener todos los registros de la tabla.
     * Get all records from the table.
     *
     * @return array
     */
    public function all()
    {
        \$stmt = \$this->conn->query("SELECT * FROM {\$this->table}");
        return \$stmt->fetchAll();
    }

    /**
     * Buscar un registro por su ID.
     * Find a record by its ID.
     *
     * @param int \$id
     * @return array|null
     */
    public function find(\$id)
    {
        \$stmt = \$this->conn->prepare("SELECT * FROM {\$this->table} WHERE id = ?");
        \$stmt->execute([\$id]);
        return \$stmt->fetch();
    }

    /**
     * Crear un nuevo registro en la tabla.
     * Create a new record in the table.
     *
     * @param array \$data
     * @return int El ID del nuevo registro creado. | The ID of the newly created record.
     */
    public function create(\$data)
    {
        \$columns = implode(',', array_keys(\$data));
        \$placeholders = implode(',', array_fill(0, count(\$data), '?'));
        \$stmt = \$this->conn->prepare("INSERT INTO {\$this->table} (\$columns) VALUES (\$placeholders)");
        \$stmt->execute(array_values(\$data));
        return \$this->conn->lastInsertId();
    }

    /**
     * Actualizar un registro por su ID.
     * Update a record by its ID.
     *
     * @param int \$id
     * @param array \$data
     * @return int El número de filas afectadas. | The number of affected rows.
     */
    public function update(\$id, \$data)
    {
        \$set = implode(', ', array_map(fn(\$col) => "\$col = ?", array_keys(\$data)));
        \$stmt = \$this->conn->prepare("UPDATE {\$this->table} SET \$set WHERE id = ?");
        \$stmt->execute([...array_values(\$data), \$id]);
        return \$stmt->rowCount();
    }

    /**
     * Eliminar un registro por su ID.
     * Delete a record by its ID.
     *
     * @param int \$id
     * @return int El número de filas afectadas. | The number of affected rows.
     */
    public function delete(\$id)
    {
        \$stmt = \$this->conn->prepare("DELETE FROM {\$this->table} WHERE id = ?");
        \$stmt->execute([\$id]);
        return \$stmt->rowCount();
    }
}

PHP;

    $carpeta = __DIR__ . '/../app/models';
    if (!is_dir($carpeta)) {
        mkdir($carpeta, 0755, true);
    }
    $rutaArchivo = "$carpeta/$nombreModelo.php";
    file_put_contents($rutaArchivo, $codigo);
}

// ---- Función para crear rutas ----
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
