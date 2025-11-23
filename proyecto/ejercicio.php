<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda de Frutas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        h1 {
            color: #333;
        }
        .success {
            color: #28a745;
            font-weight: bold;
        }
        .error {
            color: #dc3545;
            font-weight: bold;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        .info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>🚀 Tienda de Frutas</h1>

        <?php
        $host = 'db';  // Nombre del servicio en docker-compose
        $dbname = 'testdb';
        $username = 'alumno';
        $password = 'alumno';

        $nl = (php_sapi_name() === 'cli') ? PHP_EOL : "<br>\n";

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            echo "<p class='success'>✅ Conexión exitosa a la base de datos</p>";

            //Ejercicio 1: Creamos las tablas
            //Ponemos UNSIGNED en aquellos valores que no puedan ser negativos
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS categorias (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nombre VARCHAR(100) NOT NULL,
                    descripcion VARCHAR(100),
                    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE(nombre)
                );

                CREATE TABLE IF NOT EXISTS productos (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nombre VARCHAR(100) NOT NULL,
                    categoria_id INT NOT NULL,
                    precio FLOAT UNSIGNED NOT NULL,
                    stock INT UNSIGNED NOT NULL DEFAULT 0,
                    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE(nombre),
                    
                    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
                );

                CREATE TABLE IF NOT EXISTS usuarios (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nombre VARCHAR(100) NOT NULL,
                    email VARCHAR(100) NOT NULL,
                    contrasenia VARCHAR(100) NOT NULL DEFAULT ('contraseña'),
                    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS pedidos (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    usuario_id INT NOT NULL,
                    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    total FLOAT NOT NULL,
                    
                    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
                );
            ");

            //Ejercicio 2: insertar valores iniciales en las tablas productos y categorías
            echo "<h2 style='color:blue'>Ejercicio 2: insertar valores iniciales en las tablas productos y categorías</h2>";
            //Definimos el array de categorías a insertar
            $categorias = ['Cítricos', 'Frutas Rojas', 'Tropicales'];

            //Vamos a insertarlo con un bloque try/catch
            try {
                $pdo->beginTransaction();

                //Definimos un proceso abstracto al que podremos llamar para insertar valores en la tabla
                $stmt = $pdo->prepare(
                        'INSERT INTO categorias (nombre) VALUES(?)' );

                //Definimos un contador para saber cuántas inserciones hacemos
                $insertados = 0;

                //Recorremos todos los elementos del array anterior y vamos insertando
                foreach ($categorias as $c) {
                    $stmt->execute([ $c ]);
                    $insertados++;
                }

                //Confirmamos las inserciones y mostramos el resultado
                $pdo->commit();
                echo $insertados . ' entradas han sido insertadas en la tabla \'categorias\'' . $nl . $nl;
            } catch (Exception $e) { //Si hay algún error, deshacemos los cambios y mostramos un mensaje
                $pdo->rollBack();
                echo 'Error (no se completó la inserción): ' . $e->getMessage() . $nl . $nl;
            }

            //Definimos el array de productos para insertar
            $productos = [
                    ['nombre' => 'Naranja', 'categoria_id' => 1, 'precio' => 0.3, 'stock' => 25],
                    ['nombre' => 'Limón', 'categoria_id' => 1, 'precio' => 0.4, 'stock' => 50],
                    ['nombre' => 'Pomelo', 'categoria_id' => 1, 'precio' => 1, 'stock' => 10],
                    ['nombre' => 'Lima', 'categoria_id' => 1, 'precio' => 0.55, 'stock' => 20],
                    ['nombre' => 'Frambuesa', 'categoria_id' => 2, 'precio' => 1.6, 'stock' => 50],
                    ['nombre' => 'Arándano', 'categoria_id' => 2, 'precio' => 3, 'stock' => 7],
                    ['nombre' => 'Ciruela', 'categoria_id' => 2, 'precio' => 0.3, 'stock' => 100],
                    ['nombre' => 'Piña', 'categoria_id' => 3, 'precio' => 5, 'stock' => 4],
                    ['nombre' => 'Aguacate', 'categoria_id' => 3, 'precio' => 0.57, 'stock' => 25],
                    ['nombre' => 'Tamarindo', 'categoria_id' => 3, 'precio' => 4, 'stock' => 5]
            ];

            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare('
                    INSERT INTO productos (nombre, categoria_id, precio, stock) VALUES(?,?,?,?);
                ');
                $insertados = 0;
                foreach ($productos as $p) {
                    if ($p['precio'] > 0) {
                        $stmt->execute([ $p['nombre'], $p['categoria_id'], $p['precio'], $p['stock'] ]);
                        $insertados++;
                    }
                }
                $pdo->commit();
                echo $insertados . ' entradas han sido insertadas en la tabla \'productos\'' . $nl . $nl;
            } catch (Exception $e) {
                $pdo->rollBack();
                echo 'Error (no se completó la inserción): ' . $e->getMessage() . $nl . $nl;
            }

            //Función que imprime un array asociativo
            function imprimirArrayAsociativo($array){
                //Variable que imprime un salto de línea adecuado según el entorno
                $nl = (php_sapi_name() === 'cli') ? PHP_EOL : "<br>\n";

                //Comprobamos si el input es un array. En caso de queno lo sea, salimos de la función mostrando un mensaje de error
                if (!is_array($array)) {
                    echo "El argumento no es un array." . $nl;
                    return;
                }

                //Empezamos a imprimir el array
                echo "[" . $nl;

                $items = [];
                //Recorremos el array usando foreach para obtener clave y valor
                foreach ($array as $key => $value) {
                    // 2. Formato: [clave] => valor
                    // Usamos var_export para manejar correctamente strings y números
                    $items[] = var_export($key, true) . " => " . var_export($value, true);
                }

                // Unimos todos los elementos con una coma y un salto de línea
                echo implode("," . $nl, $items) . $nl;

                // Terminamos la impresión del array
                echo "]" . $nl . $nl;
            }

            //Ejercicio 3: Consultas SELECT básicas
            echo "<h2 style='color:blue'>Ejercicio 3: Consultas Select Básicas</h2>";
            //3.A Obtener todos los productos ordenados por precio (menor a mayor)
            echo "<h3 style='color:darkblue'>Ejercicio 3.A: Obtener todos los productos ordenados por precio (menor a mayor)</h3>";

            try{
                $stmt = $pdo->prepare('
                    SELECT * FROM productos ORDER BY precio ASC;
                ');
                $stmt->execute();
                $array = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo "Los productos ordenados de menor a mayor precio son: " . $nl . $nl;
                $contador = 1;
                foreach ($array as $a) {
                    echo "Producto " . $contador++ . ": ";
                    imprimirArrayAsociativo($a);
                }
            } catch (Exception $e) {
                $pdo->rollBack();
                echo 'Error: ' . $e->getMessage() . $nl . $nl;
            }

            //3.B Obtener todos los productos de una categoría
            echo "<h3 style='color:darkblue'>Ejercicio 3.B: Obtener todos los productos de una categoría</h3>";
            $nombre_categoria = "Frutas Rojas";
            try{
                //Primero tenemos que buscar el id asociado al nombre de la categoría
                $stmt_id = $pdo->prepare('
                    SELECT id FROM categorias WHERE nombre = ?
                ');

                $stmt_id->execute([$nombre_categoria]);

                $categoria_id = $stmt_id->fetchColumn();

                //Si no encontramos un id, cortamos la ejecución del bloque
                if(!$categoria_id){
                    echo "Error: La categoría $nombre_categoria no existe" . $nl;
                    return;
                }

                $stmt = $pdo->prepare('
                    SELECT * FROM productos WHERE categoria_id = ?;
                ');
                $stmt->execute([$categoria_id]);
                $array = $stmt->fetchAll(PDO::FETCH_ASSOC);

                //Listamos los resultados
                echo "Los productos de la categoría $nombre_categoria son: " . $nl . $nl;

                $contador = 1;
                foreach ($array as $a) {
                    echo "Producto " . $contador++ . ": ";
                    imprimirArrayAsociativo($a);
                }
            } catch (Exception $e) {
                $pdo->rollBack();
                echo 'Error: ' . $e->getMessage() . $nl . $nl;
            }

            //3.C Listar los productos con stock menor a 20
            echo "<h3 style='color:darkblue'>Ejercicio 3.C: Listar los productos con stock menor a 20</h3>";
            try{
                $maximo = 30;
                $stmt = $pdo->prepare('
                    SELECT * FROM productos WHERE stock < ?;
                ');
                $stmt->execute([$maximo]);
                $array = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo "Los productos con menos de $maximo unidades en stock son: " . $nl . $nl;
                $contador = 1;
                foreach ($array as $a) {
                    echo "Producto " . $contador++ . ": ";
                    imprimirArrayAsociativo($a);
                }
            } catch (Exception $e) {
                $pdo->rollBack();
                echo 'Error: ' . $e->getMessage() . $nl . $nl;
            }

            //3.D Contar cuántos productos hay en total
            echo "<h3 style='color:darkblue'>Ejercicio 3.D: Contar cuántos productos hay en total</h3>";
            try{
                $stmt = $pdo->prepare('
                    SELECT COUNT(id) FROM productos;
                ');
                $stmt->execute();
                $numero = $stmt->fetchColumn();

                //Mostramos los resultados
                echo "Entradas en la tabla de productos: " . $numero;
            } catch (Exception $e) {
                $pdo->rollBack();
                echo 'Error: ' . $e->getMessage() . $nl . $nl;
            }

            //Ejercicio 4: JOIN - Productos con categoría
            echo "<h2 style='color:blue'>Ejercicio 4: JOIN - Productos con categoría</h2>";

            try{
                $stmt = $pdo->prepare('
                    SELECT productos.nombre AS nombre_producto, productos.precio, categorias.nombre AS nombre_categoria
                    FROM productos 
                    LEFT JOIN categorias ON productos.categoria_id = categorias.id
                    ORDER BY productos.categoria_id, productos.precio;
                ');
                $stmt->execute();
                $array = $stmt->fetchAll(PDO::FETCH_ASSOC);

                //Mostramos los resultados
                echo "El nombre, precio y nombre de categoría de los productos son: " . $nl . $nl;
                $contador = 1;
                foreach ($array as $a) {
                    echo "Producto " . $contador++ . ": ";
                    imprimirArrayAsociativo($a);
                }
            } catch (Exception $e) {
                $pdo->rollBack();
                echo 'Error: ' . $e->getMessage() . $nl . $nl;
            }

            //Ejercicio 5: UPDATE - Cambiar precios
            echo "<h2 style='color:blue'>Ejercicio 5: UPDATE - Cambiar precios</h2>";

            //5.A Aumentar el precio de todos los productos de una categoría en un 10%
            echo "<h3 style='color:darkblue'>Ejercicio 5.A: Aumentar el precio de todos los productos de una categoría en un 10%</h3>";

            $multiplicador = 10;
            $categoria = "Frutas Rojas";
            try {
                //Comenzamos la transacción
                $pdo->beginTransaction();

                //Definimos la sentencia SQL a efectuar: multiplicar el precio de los productos cuyo categoria_id coincida con el asocidado a la categoría introducida
                $stmt = $pdo->prepare('
                    UPDATE productos SET precio = precio * ? WHERE categoria_id = (SELECT id FROM categorias WHERE nombre = ?);
                ');

                //Ejecutamos la sentencia pasando los valores necesarios
                $stmt->execute([$multiplicador, $categoria]);

                //Contamos y mostramos el número de cambios efectuados
                $filas = $stmt->rowCount();
                echo $filas . " entradas de $categoria han sido actualizadas en la tabla 'productos'" . $nl . $nl;

                //Finalizamos la transacción
                $pdo->commit();
            } catch (Exception $e) { //Si algo falla, cortamos la ejecución
                $pdo->rollBack();
                echo 'Error (no se completó la actualización de la tabla): ' . $e->getMessage() . $nl . $nl;
            }

            //5.B: Reduce el stock de un producto específico cuando se realiza una compra
            echo "<h3 style='color:darkblue'>Ejercicio 5.B: Reduce el stock de un producto específico cuando se realiza una compra</h3>";
            /*
            //La voy a hacer una función para que podamos acceder a ella sin repetir código
            function reducirStock(int $cantidad, string $producto): bool{
                try {
                    //Pasamos las variables $pdo y $nl como globales
                    global $pdo;
                    global $nl;

                    //Comenzamos la transacción
                    $pdo->beginTransaction();

                    //Definimos la sentencia SQL a efectuar: disminuir el stock de un producto en función de la cantidad vendida
                    $stmt = $pdo->prepare('
                        UPDATE productos SET stock = stock - ? WHERE nombre = ?;
                    ');

                    //Ejecutamos la sentencia pasando los valores necesarios
                    $stmt->execute([$cantidad, $producto]);

                    //Mostramos un mensaje de éxito
                    echo "Se ha actualizado el stock de $producto" . $nl . $nl;

                    //Finalizamos la transacción
                    $pdo->commit();

                    //Devolvemos true como signo de que se ha realizado bien el proceso
                    return true;
                } catch (Exception $e) { //Si algo falla, cortamos la ejecución y mostramos un mensaje
                    $pdo->rollBack();
                    echo 'Error (no se completó la actualización de la tabla): ' . $e->getMessage() . $nl . $nl;
                    return false;
                }
            }*/

            $cantidad = 4;
            $producto = "Piña";
            try {
                //Comenzamos la transacción
                $pdo->beginTransaction();

                //Definimos la sentencia SQL a efectuar: disminuir el stock de un producto en función de la cantidad vendida
                $stmt = $pdo->prepare('
                        UPDATE productos SET stock = stock - ? WHERE nombre = ?;
                    ');

                //Ejecutamos la sentencia pasando los valores necesarios
                $stmt->execute([$cantidad, $producto]);

                //Mostramos un mensaje de éxito
                echo "Se ha actualizado el stock de $producto" . $nl . $nl;

                //Finalizamos la transacción
                $pdo->commit();
            } catch (Exception $e) { //Si algo falla, cortamos la ejecución y mostramos un mensaje
                $pdo->rollBack();
                echo 'Error (no se completó la actualización de la tabla): ' . $e->getMessage() . $nl . $nl;
            }


            //5.C: Validar que el stock no sea negativo antes de actualizar
            echo "<h3 style='color:darkblue'>Ejercicio 5.C: Validar que el stock no sea negativo antes de actualizar</h3>";

            echo "<p>Esto ya lo he solucionado haciendo que el stock no tenga signo a nivel de tabla, es decir, que siempre será mayor o igual que 0</p>";

            //Ejercicio 6: DELETE - Eliminar productos
            echo "<h2 style='color:blue'>Ejercicio 6: DELETE - Eliminar productos</h2>";

            //Primero debemos alterar la abla de productos para añadir la columna "eliminado"
            try{
                $stmt = $pdo->prepare('
                    ALTER TABLE productos ADD COLUMN eliminado BOOLEAN NOT NULL DEFAULT FALSE;
                ');
                $stmt->execute();

                //Mostramos un mensaje de éxito
                echo "Se ha añadido la columna 'eliminado' a la tabla 'productos'" . $nl . $nl;
            } catch (Exception $e) {
                $pdo->rollBack();
                echo 'Error: ' . $e->getMessage() . $nl . $nl;
            }

            //Ahora podemos pasar a "eliminar" los productos con stock a 0
            try {
                //Comenzamos la transacción
                $pdo->beginTransaction();

                //Definimos la sentencia SQL a efectuar: "Eliminar" los productos cuyo stock sea igual a 0
                $stmt = $pdo->prepare('
                    UPDATE productos SET eliminado = true WHERE stock = 0;
                ');

                //Ejecutamos la sentencia pasando los valores necesarios
                $stmt->execute();

                //Contamos y mostramos el número de cambios efectuados
                $filas = $stmt->rowCount();
                echo "Número de productos de la tabla 'productos' que han sido eliminados: " . $filas . $nl . $nl;

                //Finalizamos la transacción
                $pdo->commit();
            } catch (Exception $e) { //Si algo falla, cortamos la ejecución y mostramos un mensaje
                $pdo->rollBack();
                echo 'Error (no se pudo eliminar algún producto): ' . $e->getMessage() . $nl . $nl;
            }

            //Ejercicio 7: Simulación de compra
            echo "<h2 style='color:blue'>Ejercicio 7: Simulación de compra</h2>";

            // Insertar datos de ejemplo si la tabla está vacía
            $count = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
            if ($count == 0) {
                $pdo->exec("
                    INSERT INTO usuarios (nombre, email) VALUES 
                    ('Juan Pérez', 'juan@ejemplo.com'),
                    ('María García', 'maria@ejemplo.com'),
                    ('Carlos López', 'carlos@ejemplo.com')
                ");
                echo "<p class='success'>✅ Datos de ejemplo insertados</p>";
            }

            // Mostrar usuarios
            echo "<h2>👥 Usuarios en la base de datos</h2>";
            $stmt = $pdo->query("SELECT * FROM usuarios ORDER BY id");
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($usuarios) > 0) {
                echo "<table style='width: 100%; border-collapse: collapse;'>";
                echo "<tr style='background: #f4f4f4;'>";
                echo "<th style='padding: 10px; border: 1px solid #ddd;'>ID</th>";
                echo "<th style='padding: 10px; border: 1px solid #ddd;'>Nombre</th>";
                echo "<th style='padding: 10px; border: 1px solid #ddd;'>Email</th>";
                echo "<th style='padding: 10px; border: 1px solid #ddd;'>Fecha Registro</th>";
                echo "</tr>";

                foreach ($usuarios as $usuario) {
                    echo "<tr>";
                    echo "<td style='padding: 10px; border: 1px solid #ddd;'>{$usuario['id']}</td>";
                    echo "<td style='padding: 10px; border: 1px solid #ddd;'>{$usuario['nombre']}</td>";
                    echo "<td style='padding: 10px; border: 1px solid #ddd;'>{$usuario['email']}</td>";
                    echo "<td style='padding: 10px; border: 1px solid #ddd;'>{$usuario['fecha_registro']}</td>";
                    echo "</tr>";
                }

                echo "</table>";
            }

            function hacerCompra(int $usuario, int $producto, int $cantidad): void{
                try {
                    //Recuperamos las variables pdo y nl como globales
                    global $pdo;
                    global $nl;

                    //Comenzamos la transacción
                    $pdo->beginTransaction();

                    //Primero debemos comprobar que el producto está en la tabla
                    $stmt_producto = $pdo->prepare('
                        SELECT * FROM productos WHERE id = ?;
                    ');

                    //Ejecutamos la sentencia pasando los valores necesarios
                    //Como la sentencia nos devuelve true/false, podemos usarla de condición en un booleano
                    if(!$stmt_producto->execute([$producto])){
                        throw new Exception("No se ha encontrado un producto con ese ID" . $nl . $nl);
                    }

                    //También debemos comprobar que el usuario está en la tabla
                    $stmt_usuario = $pdo->prepare('
                        SELECT COUNT(id) FROM usuarios WHERE id = ?;
                    ');

                    if(!$stmt_usuario->execute([$usuario])){
                        throw new Exception("No se ha encontrado un usuario con ese ID" . $nl . $nl);
                    }


                    //Ahora debemos reducir el stock del producto en función del número que se hayan comprado
                    //Si falla, será porque el stock es insuficiente
                    //Definimos la sentencia SQL a efectuar: disminuir el stock de un producto en función de la cantidad vendida
                    $stmt_actualizar = $pdo->prepare('
                        UPDATE productos SET stock = stock - ? WHERE id = ?;
                    ');

                    //Ejecutamos la sentencia pasando los valores necesarios
                    if(!$stmt_actualizar->execute([$cantidad, $producto])){
                        throw new Exception("El stock del producto es insuficiente" . $nl . $nl);
                    }

                    //Mostramos un mensaje de éxito
                    echo "Se ha actualizado el stock del producto con ID $producto" . $nl . $nl;

                    //Ahora pasamos a calcular el total de la compra
                    //Primero tomamos el valor del precio del producto en base a su ID
                    $stmt_precio = $pdo->prepare('
                        SELECT precio FROM productos WHERE id = ?;
                    ');

                    //Ejecutamos la sentencia pasando los valores necesarios y la asignamos a una variable
                    $stmt_precio->execute([$producto]);
                    $precio = $stmt_precio->fetchColumn();

                    //Calculamos el total
                    $total = $precio * $cantidad;

                    //Definimos la sentencia SQL a efectuar: Insertar un nuevo pedido en la tabla pedidos
                    $stmt_pedido = $pdo->prepare('
                        INSERT INTO pedidos (usuario_id, total) VALUES (?, ?);
                    ');

                    //Ejecutamos la sentencia pasando los valores necesarios
                    $stmt_pedido->execute([$usuario, $total]);

                    //Mostramos un mensaje de éxito de la inserción
                    echo "Se ha insertado una entrada en la tabla 'pedidos'" . $nl . $nl;

                    //Finalizamos la transacción
                    $pdo->commit();
                } catch (Exception $e) { //Si algo falla, cortamos la ejecución y mostramos un mensaje
                    $pdo->rollBack();
                    echo 'Error: ' . $e->getMessage() . $nl . $nl;
                }
            }

            hacerCompra(2, 2, 2);





        } catch(PDOException $e) {
            echo "<p class='error'>❌ Error de conexión: " . $e->getMessage() . "</p>";
            echo "<div class='info'>";
            echo "<strong>Verifica que:</strong><br>";
            echo "- Los contenedores estén corriendo: <code>docker compose -f docker-compose-alumnos.yml ps</code><br>";
            echo "- El servicio de base de datos esté disponible<br>";
            echo "- Las credenciales sean correctas";
            echo "</div>";
        }
        ?>

        <h2>🔗 Enlaces Útiles</h2>
        <div class="info">
            <p><strong>phpMyAdmin:</strong> <a href="http://localhost:8081" target="_blank">http://localhost:8081</a></p>
            <p><strong>Credenciales BD:</strong></p>
            <ul>
                <li>Usuario: <code>alumno</code></li>
                <li>Contraseña: <code>alumno</code></li>
                <li>Base de datos: <code>testdb</code></li>
            </ul>
        </div>
    </div>
</body>
</html>
