<?php
function crearControladorCRUD($nombreControlador, $nombreModelo, $nombreVista, $campos)
{
    $modeloVar = lcfirst($nombreModelo);
    $rutaVista = strtolower($nombreVista);
    $camposArray = array_map('trim', explode(',', $campos));

    $camposAsignacion = [];
    foreach ($camposArray as $campo) {
        $camposAsignacion[] = "'$campo' => \$_POST['$campo'] ?? null";
    }
    $asignacion = implode(",\n                ", $camposAsignacion);

    $validacion = "if (" . implode(" || ", array_map(fn($campo) => "empty(\$data['$campo'])", $camposArray)) . ") {
                \$_SESSION['error'] = \"Todos los campos son obligatorios.\";
                View::render('{$rutaVista}/" . (count($camposArray) ? "crear" : "") . "', [
                    'titulo' => 'Crear " . ucfirst($rutaVista) . "',
                    'data' => \$data,
                    'error' => \$_SESSION['error'] ?? null
                ]);
                return;
            }";

    $validacionEdit = "if (" . implode(" || ", array_map(fn($campo) => "empty(\$data['$campo'])", $camposArray)) . ") {
                \$_SESSION['error'] = \"Todos los campos son obligatorios.\";
                \$model = new $nombreModelo();
                \$item = \$model->find(\$id);
                View::render('{$rutaVista}/editar', [
                    'titulo' => 'Editar " . ucfirst($rutaVista) . "',
                    '{$modeloVar}' => array_merge(\$item, \$data),
                    'error' => \$_SESSION['error'] ?? null
                ]);
                return;
            }";

    $codigo = <<<PHP
<?php

class {$nombreControlador} extends BaseController
{
    public function __construct()
    {
        parent::__construct([]);
    }

    // Listar todos
    public function index()
    {
        \${$modeloVar}Model = new {$nombreModelo}();
        \${$rutaVista}s = \${$modeloVar}Model->all();

        View::render('{$rutaVista}/index', [
            'titulo' => 'Listado de ' . ucfirst("$rutaVista") . 's',
            '{$rutaVista}s' => \${$rutaVista}s
        ]);
    }

    // Crear (mostrar formulario y guardar)
    public function crear()
    {
        if (\$_SERVER['REQUEST_METHOD'] == 'POST') {
            \$data = [
                {$asignacion}
            ];

            {$validacion}

            \${$modeloVar}Model = new {$nombreModelo}();
            \$id = \${$modeloVar}Model->create(\$data);

            if (\$id) {
                \$_SESSION['success'] = ucfirst('{$rutaVista}') . " creado correctamente.";
                header("Location: /{$rutaVista}");
                exit;
            } else {
                \$_SESSION['error'] = "Error al crear " . ucfirst('{$rutaVista}') . ".";
                View::render('{$rutaVista}/crear', [
                    'titulo' => 'Crear ' . ucfirst("$rutaVista") . '',
                    'data' => \$data,
                    'error' => \$_SESSION['error'] ?? null
                ]);
                return;
            }
        }
        View::render('{$rutaVista}/crear', [
            'titulo' => 'Crear ' . ucfirst("$rutaVista") . ''
        ]);
    }

    // Editar (mostrar formulario de edición)
    public function editar(\$id)
    {
        \${$modeloVar}Model = new {$nombreModelo}();
        \${$modeloVar} = \${$modeloVar}Model->find(\$id);

        if (!\${$modeloVar}) {
            \$_SESSION['error'] = ucfirst('{$rutaVista}') . " no encontrado.";
            header("Location: /{$rutaVista}");
            exit;
        }

        View::render('{$rutaVista}/editar', [
            'titulo' => 'Editar ' . ucfirst("$rutaVista") . '',
            '{$modeloVar}' => \${$modeloVar}
        ]);
    }

    // Actualizar
    public function actualizar(\$id)
    {
        if (\$_SERVER['REQUEST_METHOD'] == 'POST') {
            \$data = [
                {$asignacion}
            ];

            {$validacionEdit}

            \${$modeloVar}Model = new {$nombreModelo}();
            \$resultado = \${$modeloVar}Model->update(\$id, \$data);

            if (\$resultado) {
                \$_SESSION['success'] = ucfirst('{$rutaVista}') . " actualizado.";
                header("Location: /{$rutaVista}");
                exit;
            } else {
                \$_SESSION['error'] = "Error al actualizar " . ucfirst('{$rutaVista}') . ".";
                \$item = \${$modeloVar}Model->find(\$id);
                View::render('{$rutaVista}/editar', [
                    'titulo' => 'Editar ' . ucfirst("$rutaVista") . '',
                    '{$modeloVar}' => array_merge(\$item, \$data),
                    'error' => \$_SESSION['error'] ?? null
                ]);
                return;
            }
        } else {
            header("Location: /{$rutaVista}/editar/{\$id}");
            exit;
        }
    }

    // Eliminar
    public function eliminar(\$id)
    {
        \${$modeloVar}Model = new {$nombreModelo}();
        \${$modeloVar}Model->delete(\$id);

        \$_SESSION['success'] = ucfirst('{$rutaVista}') . " eliminado correctamente.";
        header("Location: /{$rutaVista}");
        exit;
    }
}
PHP;

    $carpeta = __DIR__ . '/../app/controllers';
    if (!is_dir($carpeta)) {
        mkdir($carpeta, 0755, true);
    }
    $rutaArchivo = "$carpeta/{$nombreControlador}.php";
    if (file_exists($rutaArchivo)) {
        echo "ERROR: El controlador $nombreControlador ya existe.\n";
        exit(1);
    }
    file_put_contents($rutaArchivo, $codigo);
    echo "Controlador CRUD creado: $rutaArchivo\n";
}
