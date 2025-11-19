# Entorno Docker para PHP + MariaDB

## 📋 Requisitos
- Docker
- Docker Compose

## 🚀 Uso

### Iniciar el entorno
```bash
docker compose -f docker-compose-alumnos.yml up -d
```

### Detener el entorno
```bash
docker compose -f docker-compose-alumnos.yml down
```

### Detener y eliminar datos (reinicio completo)
```bash
docker compose -f docker-compose-alumnos.yml down -v
```

## 🌐 Accesos

- **Aplicación PHP**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081

## 🔑 Credenciales de Base de Datos

### Acceso Root
- **Host**: `localhost` (o `db` desde PHP)
- **Puerto**: `3306`
- **Usuario**: `root`
- **Contraseña**: `root`

### Acceso Usuario Normal
- **Usuario**: `alumno`
- **Contraseña**: `alumno`
- **Base de datos**: `testdb`

## 📁 Estructura

Crea una carpeta `proyecto/` donde colocarás tus archivos PHP:

```
.
├── docker-compose-alumnos.yml
└── proyecto/
    ├── index.php
    └── ... (tus archivos PHP)
```

## 📝 Ejemplo de Conexión PHP

```php
<?php
// Conexión a la base de datos
$host = 'db';  // Nombre del servicio en docker-compose
$dbname = 'testdb';
$username = 'alumno';
$password = 'alumno';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Conexión exitosa a la base de datos";
} catch(PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage();
}
?>
```

## 🛠️ Comandos Útiles

### Ver logs
```bash
docker compose -f docker-compose-alumnos.yml logs -f
```

### Acceder al contenedor de MariaDB
```bash
docker exec -it mariadb mysql -u root -proot
```

### Reiniciar servicios
```bash
docker compose -f docker-compose-alumnos.yml restart
```

## ✨ Características

- ✅ PHP 8.3 con Apache
- ✅ MariaDB 10.11
- ✅ phpMyAdmin incluido
- ✅ Datos persistentes
- ✅ Usuario root y usuario normal pre-configurados
- ✅ Base de datos `testdb` creada automáticamente
