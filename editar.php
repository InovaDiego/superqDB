<?php
// =======================================================
// Archivo: editar.php (Formulario para UPDATE)
// =======================================================

require 'conexion.php'; 

$producto = null;
$error_msg = '';

// 1. Verificar si se recibió un ID
if (isset($_GET['id'])) {
    $id_producto = $_GET['id'];
    
    // 2. Traer los datos del producto
    $sql_select = "SELECT * FROM PRODUCTOS WHERE id_producto = ?";
    
    try {
        $stmt_select = $pdo->prepare($sql_select);
        $stmt_select->execute([$id_producto]);
        $producto = $stmt_select->fetch();
        
        if (!$producto) {
            $error_msg = "❌ Producto no encontrado.";
        }
        
    } catch (PDOException $e) {
        $error_msg = "❌ Error de consulta: " . $e->getMessage();
    }
} else {
    $error_msg = "❌ ID de producto no proporcionado.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto | Super Q</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px; }
        label { display: block; margin-top: 10px; font-weight: bold; }
        input[type="text"], input[type="number"], input[type="date"] { width: 100%; padding: 8px; margin-top: 5px; box-sizing: border-box; }
        button { background-color: #ffc107; color: black; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; margin-top: 20px; }
        .error { color: red; font-weight: bold; }
    </style>
</head>
<body>

    <div class="container">
        <h1>Editar Producto (ID: <?php echo htmlspecialchars($producto['id_producto'] ?? 'N/A'); ?>)</h1>
        
        <?php if ($error_msg): ?>
            <p class="error"><?php echo $error_msg; ?></p>
        <?php elseif ($producto): ?>
            
            <form action="actualizar.php" method="POST">
                
                <input type="hidden" name="id_producto" value="<?php echo htmlspecialchars($producto['id_producto']); ?>">
                
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($producto['nombre_producto']); ?>" required>
                
                <label for="precio">Precio ($):</label>
                <input type="number" id="precio" name="precio" step="0.01" min="0" value="<?php echo htmlspecialchars($producto['precio']); ?>" required>
                
                <label for="caducidad">Fecha de Caducidad:</label>
                <input type="date" id="caducidad" name="caducidad" value="<?php echo htmlspecialchars($producto['caducidad']); ?>">
                
                <label for="cantidad">Cantidad (Stock):</label>
                <input type="number" id="cantidad" name="cantidad" min="0" value="<?php echo htmlspecialchars($producto['cantidad']); ?>" required>
                
                <label for="id_barras">ID-BARRAS (Código de Barras):</label>
                <input type="text" id="id_barras" name="id_barras" value="<?php echo htmlspecialchars($producto['id_barras']); ?>" required>
                
                <label for="proveedor">Proveedor:</label>
                <input type="text" id="proveedor" name="proveedor" value="<?php echo htmlspecialchars($producto['proveedor']); ?>">
                
                <button type="submit" name="submit">Guardar Cambios</button>
            </form>
            
        <?php endif; ?>

        <p><a href="index.php">← Volver a la Lista</a></p>
    </div>

</body>
</html>