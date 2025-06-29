<?php

// Crear las carpetas necesarias para las vistas y layouts
$folders = [
    'app/controllers',
    'app/models',
    'app/views',
    'app/views/layouts'
];

foreach ($folders as $folder) {
    if (!is_dir($folder)) {
        mkdir($folder, 0755, true);
        echo "Carpeta creada: $folder\n";
    } else {
        echo "La carpeta $folder ya existe.\n";
    }
}

// Crear el archivo AuthController.php
$authControllerContent = <<<EOT
<?php
class AuthController
{
    public function login()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (\$_SERVER['REQUEST_METHOD'] == 'POST') {
            \$usuario = htmlspecialchars(trim(\$_POST['usuario'] ?? ''));
            \$password = \$_POST['password'] ?? '';

            // Verificar si los campos están completos
            if (empty(\$usuario) || empty(\$password)) {
                \$_SESSION['error'] = "Por favor ingrese usuario y contraseña.";
                header("Location: /login");
                exit;
            }

            \$userModel = new Usuario();
            \$user = \$userModel->obtenerPorUsuario(\$usuario);

            if (\$user && password_verify(\$password, \$user['password'])) {
                session_regenerate_id(true);

                \$_SESSION['id'] = \$user['id'];
                \$_SESSION['usuario'] = \$user['username'];

                header("Location: /dashboard");
                exit;
            } else {
                \$_SESSION['error'] = "Usuario o contraseña incorrectos.";
                header("Location: /login");
                exit;
            }
        } else {
            require __DIR__ . '/../views/login.php';
        }
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        \$_SESSION = [];
        session_destroy();

        header('Location: /login');
        exit;
    }
}
EOT;

file_put_contents(__DIR__ . '/../app/controllers/AuthController.php', $authControllerContent);
echo "Archivo AuthController.php creado.\n";

// Crear el archivo Usuario.php (modelo)
$usuarioModelContent = <<<EOT
<?php

class Usuario
{
    private \$conn;
    private \$table = 'usuarios';

    public function __construct()
    {
        \$database = new Database();
        \$this->conn = \$database->conectar();
    }

    public function obtenerPorUsuario(\$usuario)
    {
        \$query = "
        SELECT u.*, e.nombre AS nombre_empresa
        FROM \$this->table u
        LEFT JOIN empresas e ON u.empresa_id = e.id
        WHERE u.username = :usuario
        LIMIT 1
        ";

        \$stmt = \$this->conn->prepare(\$query);
        \$stmt->bindParam(':usuario', \$usuario);
        \$stmt->execute();

        \$usuario = \$stmt->fetch(PDO::FETCH_ASSOC);

        if (!\$usuario) {
            return false;
        }

        return \$usuario;
    }
}
EOT;

file_put_contents(__DIR__ . '/../app/models/Usuario.php', $usuarioModelContent);
echo "Archivo Usuario.php (modelo) creado.\n";

// Crear la vista de login
$loginViewContent = <<<EOT
<form method="POST" action="/login">
    <label for="usuario">Usuario:</label>
    <input type="text" id="usuario" name="usuario" required>
    <label for="password">Contraseña:</label>
    <input type="password" id="password" name="password" required>
    <button type="submit">Iniciar sesión</button>
</form>

<?php if (isset(\$_SESSION['error'])): ?>
    <div class="error"><?= \$_SESSION['error'] ?></div>
    <?php unset(\$_SESSION['error']); ?>
<?php endif; ?>
EOT;

file_put_contents(__DIR__ . '/../app/views/login.php', $loginViewContent);
echo "Archivo login.php (vista) creado.\n";

// Crear el archivo layout.php para las vistas
$layoutContent = <<<EOT
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?= \$titulo ?? 'Mi App' ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>
<?= \$contenido ?>
</body>

</html>
EOT;

file_put_contents(__DIR__ . '/../app/views/layouts/layout.php', $layoutContent);
echo "Archivo layout.php (layout) creado.\n";

