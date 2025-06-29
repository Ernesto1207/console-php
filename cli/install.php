<?php

// Preguntar por el entorno de desarrollo o producción
echo "Selecciona el entorno de la aplicación (1: Desarrollo, 2: Producción): ";
$entorno = trim(fgets(STDIN));

$entornoSeleccionado = $entorno == 1 ? 'development' : 'production';

// Detectar si se está utilizando Laragon
$defaultHosts = ['localhost', '127.0.0.1', ''];
echo "¿Está usando Laragon (si/no)? ";
$usarLaragon = trim(fgets(STDIN));

if (strtolower($usarLaragon) == 'si') {
    echo "Ingrese el nombre de dominio de Laragon (por ejemplo, 'url.test'): ";
    $host = trim(fgets(STDIN));
} else {
    echo "Ingrese el host para $entornoSeleccionado (por defecto, se recomienda 'localhost'): ";
    $host = trim(fgets(STDIN)) ?: 'localhost';
}

// Solicitar base de datos, usuario y contraseña
echo "Ingrese el nombre de la base de datos para $entornoSeleccionado: ";
$dbName = trim(fgets(STDIN));

echo "Ingrese el nombre de usuario para la base de datos: ";
$username = trim(fgets(STDIN));

echo "Ingrese la contraseña para la base de datos (deje en blanco si no hay): ";
$password = trim(fgets(STDIN));  // Esto ahora tomará Enter como valor vacío

// Crear el archivo config.php
$configContent = <<<EOT
<?php

\$host = \$_SERVER['HTTP_HOST'] ?? '$host';

// Determinamos el entorno
if (in_array(\$host, ['localhost', '127.0.0.1', '$host'])) {
    define('APP_ENV', 'development');
} else {
    define('APP_ENV', 'production');
}

\$config = [
    'development' => [
        'host' => 'localhost',
        'db_name' => '$dbName',
        'username' => '$username',
        'password' => '$password',
        'display_errors' => true
    ],
    'production' => [
        'host' => 'localhost',
        'db_name' => '',
        'username' => '',
        'password' => '',
        'display_errors' => false
    ]
];

\$current = \$config[APP_ENV];

if (!\$current['display_errors']) {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
} else {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
EOT;

if (!is_dir(__DIR__ . '/../config')) {
    mkdir(__DIR__ . '/../config', 0755, true);
}

file_put_contents(__DIR__ . '/../config/config.php', $configContent);
echo "Archivo config.php creado.\n";

// Crear el archivo database.php
$databaseContent = <<<EOT
<?php
require_once 'config.php';

class Database
{
    private \$host;
    private \$db_name;
    private \$username;
    private \$password;
    public \$conn;

    public function __construct()
    {
        global \$current;
        \$this->host = \$current['host'];
        \$this->db_name = \$current['db_name'];
        \$this->username = \$current['username'];
        \$this->password = \$current['password'];
    }

    public function conectar()
    {
        \$this->conn = null;
        try {
            \$this->conn = new PDO(
                "mysql:host={\$this->host};dbname={\$this->db_name}",
                \$this->username,
                \$this->password
            );
            \$this->conn->exec("set names utf8");

            // \$this->conn->exec("SET time_zone = 'America/Lima'");
        } catch (PDOException \$exception) {
            echo "Error de conexión: " . \$exception->getMessage();
        }
        return \$this->conn;
    }
}
EOT;

file_put_contents(__DIR__ . '/../config/database.php', $databaseContent);
echo "Archivo database.php creado.\n";

// Crear las carpetas necesarias
$folders = [
    'app/controllers',
    'app/core',
    'app/helpers',
    'app/models',
    'app/views',
    'config'
];

foreach ($folders as $folder) {
    if (!is_dir($folder)) {
        mkdir($folder, 0755, true);
        echo "Carpeta creada: $folder\n";
    } else {
        echo "La carpeta $folder ya existe.\n";
    }
}

// Crear el archivo .htaccess
$htaccessContent = <<<EOT
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
EOT;

file_put_contents(__DIR__ . '/../.htaccess', $htaccessContent);
echo "Archivo .htaccess creado.\n";

// Crear el archivo autoload.php
$autoloadContent = <<<EOT
<?php
// public_html/autoload.php

// -- Mostrar todos los errores --
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/Lima');

// -- Manejador de errores custom --
set_error_handler(function (int \$severity, string \$message, string \$file, int \$line) {
    http_response_code(500);
    echo "<h1>Error en la Aplicación</h1>";
    echo "<p><strong>Tipo:</strong> {\$severity}</p>";
    echo "<p><strong>Mensaje:</strong> {\$message}</p>";
    echo "<p><strong>Archivo:</strong> {\$file} en línea {\$line}</p>";
    exit();
});

// -- Manejador de excepciones custom --
set_exception_handler(function (Throwable \$ex) {
    http_response_code(500);
    echo "<h1>Excepción No Capturada</h1>";
    echo "<p><strong>Clase:</strong> " . get_class(\$ex) . "</p>";
    echo "<p><strong>Mensaje:</strong> " . \$ex->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . \$ex->getFile() . " en línea " . \$ex->getLine() . "</p>";
    exit();
});

function realizarSolicitud(\$url)
{
    \$ch = curl_init(\$url);
    curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt(\$ch, CURLOPT_HEADER, true);
    \$response = curl_exec(\$ch);

    \$http_code = curl_getinfo(\$ch, CURLINFO_HTTP_CODE);
    curl_close(\$ch);

    return \$http_code;
}

spl_autoload_register(function (string \$class): bool {
    \$base = __DIR__ . '/app/';
    \$folders = ['controllers', 'models', 'helpers', 'core'];

    foreach (\$folders as \$dir) {
        \$file = "\$base{\$dir}/{\$class}.php";
        if (file_exists(\$file)) {
            require_once \$file;
            return true;
        }
    }
    return false;
});
EOT;

file_put_contents(__DIR__ . '/../autoload.php', $autoloadContent);
echo "Archivo autoload.php creado.\n";

// Crear el archivo index.php
$indexContent = <<<EOT
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
\$url = \$_GET['url'] ?? 'login';
\$url = trim(\$url, '/');
\$segmentos = explode('/', \$url);
\$seccion = \$segmentos[0];

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
    
    // 4. Ruta por defecto (404)
    default:
        http_response_code(404);
        require __DIR__ . '/app/views/404.html';
        break;
}
// ------------------------------------------------------------
// Fin del switch
EOT;

file_put_contents(__DIR__ . '/../index.php', $indexContent);
echo "Archivo index.php creado.\n";
