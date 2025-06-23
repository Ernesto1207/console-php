<?php
function crearModelo($nombreModelo, $nombreTabla)
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
    if (file_exists($rutaArchivo)) {
        echo "ERROR: El modelo $nombreModelo ya existe.\n";
        exit(1);
    }
    file_put_contents($rutaArchivo, $codigo);
    echo "Modelo creado: $rutaArchivo\n";
}
