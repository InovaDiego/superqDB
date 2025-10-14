<?php
// Incluimos el archivo de conexión (debe estar en la misma carpeta)
require 'conexion.php'; //

$mensaje = ""; // Inicializamos una variable para mensajes de éxito/error

// =======================================================
// LÓGICA PHP: Procesamiento del Formulario
// =======================================================

// 1. Verificamos si el formulario fue enviado (botón 'submit' presionado)
if (isset($_POST['submit'])) {
    
    // 2. Recolección de datos y sanitización básica
    $nombre = htmlspecialchars($_POST['nombre']);
    $precio = htmlspecialchars($_POST['precio']);
    $caducidad = htmlspecialchars($_POST['caducidad']);
    $cantidad = htmlspecialchars($_POST['cantidad']);
    $id_barras = htmlspecialchars($_POST['id_barras']);
    $proveedor = htmlspecialchars($_POST['proveedor']);
    
    // 3. Consulta SQL para INSERCIÓN segura (usando marcadores ?)
    $sql = "INSERT INTO PRODUCTOS (nombre_producto, precio, caducidad, cantidad, id_barras, proveedor) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    try {
        // 4. Preparar y ejecutar la sentencia
        $stmt = $pdo->prepare($sql);
        
        // Ejecución en el mismo orden de los ?
        $stmt->execute([
            $nombre, 
            $precio, 
            $caducidad, 
            $cantidad, 
            $id_barras, 
            $proveedor
        ]);
        
        $mensaje = "<p style='color: green;'>✅ Producto '$nombre' guardado exitosamente.</p>";
        
    } catch (PDOException $e) {
        $mensaje = "<p style='color: red;'>❌ Error al guardar el producto: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Producto | Super Q Inventario</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px; }
        label { display: block; margin-top: 10px; font-weight: bold; }
        input[type="text"], input[type="number"], input[type="date"] { width: 100%; padding: 8px; margin-top: 5px; box-sizing: border-box; }
        button { background-color: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; margin-top: 20px; }
    </style>
</head>
<body>

    <div class="container">
        <h1>Añadir Nuevo Producto al Super Q</h1>
        <?php echo $mensaje; // Muestra el mensaje de resultado ?>

        <form action="crear.php" method="POST">
            
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required>
            
            <label for="precio">Precio ($):</label>
            <input type="number" id="precio" name="precio" step="0.01" min="0" required>
            
            <label for="caducidad">Fecha de Caducidad:</label>
            <input type="date" id="caducidad" name="caducidad">
            
            <label for="cantidad">Cantidad (Stock):</label>
            <input type="number" id="cantidad" name="cantidad" min="0" required>
            
            <label for="id_barras">ID-BARRAS (Código de Barras):</label>
            <input type="text" id="id_barras" name="id_barras" required>
            
            <label for="proveedor">Proveedor:</label>
            <input type="text" id="proveedor" name="proveedor">
            
            <button type="submit" name="submit">Guardar Producto</button>
        </form>

        <p><a href="index.php">← Volver a la Lista</a></p>
    </div>

</body>
</html>