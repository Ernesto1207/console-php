# 🛠 Artisan CLI para Proyectos PHP

Este es un script PHP CLI personalizado que simula la funcionalidad del comando artisan de Laravel, permitiendo generar controladores, modelos, vistas, rutas, migraciones y operaciones CRUD básicas en un proyecto PHP estructurado.

---

## 📦 Requisitos

- **PHP 7.4 o superior**  
- **Acceso a línea de comandos**  
- **Configuración previa del proyecto con estructura esperada**:  

```plaintext
/cli
/config
/database/migrations
index.php
```

---

## 🚀 Uso

Ejecutar comandos:  

```bash
php artisan <comando> [argumentos]
```

---

## 📚 Comandos Disponibles

### 🔧 `make:controller`  
Crea un controlador tipo CRUD.  

**Uso:**  
```bash
php artisan make:controller NombreControlador NombreModelo nombreVista campo1,campo2
```

**Ejemplo:**  
```bash
php artisan make:controller UsuarioController Usuario usuario nombre,email
```

---

### 🧩 `make:model`  
Crea un modelo PHP para la base de datos.  

**Uso:**  
```bash
php artisan make:model NombreModelo nombre_tabla
```

**Ejemplo:**  
```bash
php artisan make:model Usuario usuarios
```

---

### 🖼 `make:view`  
Crea una vista `.php` en la carpeta `views`.  

**Uso:**  
```bash
php artisan make:view carpeta/nombre_vista
```

**Ejemplo:**  
```bash
php artisan make:view usuarios/index
```

---

### 🌐 `make:route`  
Agrega una ruta al archivo `index.php`.  

**Uso:**  
```bash
php artisan make:route url/patron Controlador metodo [true|false]
```

**Ejemplo:**  
```bash
php artisan make:route usuario/editar/{id} UsuarioController editar true
```

---

### 🏗 `make:migration`  
Genera un archivo de migración en `database/migrations`.  

**Uso:**  
```bash
php artisan make:migration nombre_de_la_migracion
```

**Ejemplo:**  
```bash
php artisan make:migration crear_usuarios_table
```

---

### 📥 `migrate`  
Ejecuta todas las migraciones pendientes y las registra en la base de datos.  

**Uso:**  
```bash
php artisan migrate
```

---

### 📤 `migrate:rollback`  
Revierte la última migración ejecutada.  

**Uso:**  
```bash
php artisan migrate:rollback
```

---

### 📃 `route:list`  
Lista todas las rutas definidas en `index.php`.  

**Uso:**  
```bash
php artisan route:list
```

---

### ⚙ `make:crud`  
Genera automáticamente modelo, controlador, vistas, rutas y migración para una entidad.  

**Uso:**  
```bash
php artisan make:crud entidad tabla [campo1,campo2,...]
```

**Ejemplo:**  
```bash
php artisan make:crud usuario usuarios nombre,email
```

Si no se especifican los campos, el comando intentará obtenerlos automáticamente de la base de datos.

---

## 🗂 Estructura esperada

```plaintext
/cli
├── crear_controlador.php
├── crear_modelo.php
├── crear_vista.php
├── crear_ruta.php
├── crear_crud.php
├── ...
/config
└── database.php
/database/migrations
├── 2025_01_01_crear_usuarios_table.php
└── ...
index.php
artisan (este archivo)
```

---

## 📝 Notas

- Las migraciones deben definir las clases y métodos `up(PDO $conn)` y `down(PDO $conn)`.  
- Se requiere una base de datos MySQL/MariaDB correctamente configurada en `config/database.php`.  
- Las rutas se gestionan dentro de `index.php`.

---

## 📌 Créditos

Inspirado en Laravel Artisan, pero adaptado para proyectos PHP estructurados sin framework completo.  
