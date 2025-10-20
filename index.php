<?php

require 'conexion.php';

// =======================================================
// LÓGICA PHP: Obtener Datos y Manejar Mensajes
// =======================================================
$productos = [];
$mensaje_error = '';
$mensaje_exito = '';

// 1. Manejar mensajes de éxito/error después de una operación (Crear, Editar, Eliminar)
if (isset($_GET['mensaje'])) {
    $tipo = htmlspecialchars($_GET['mensaje']);
    switch ($tipo) {
        case 'creado':
            $mensaje_exito = "✅ Producto agregado correctamente al inventario.";
            break;
        case 'actualizado':
            $mensaje_exito = "✏️ Producto actualizado con éxito.";
            break;
        case 'eliminado':
            $mensaje_exito = "🗑️ Producto eliminado del inventario.";
            break;
    }
} elseif (isset($_GET['error'])) {
    $mensaje_error = "❌ Ocurrió un error en la operación: " . htmlspecialchars($_GET['error']);
}

try {
    // 2. Consulta SQL para obtener todos los campos de la tabla PRODUCTOS
    // Ordenado por proveedor y luego por nombre para una mejor visualización de los 100+ productos
    $sql = "SELECT id_producto, nombre_producto, precio, caducidad, cantidad, id_barras, proveedor 
            FROM PRODUCTOS 
            ORDER BY proveedor ASC, nombre_producto ASC";
    
    // 3. Ejecutar la consulta
    // Asegúrate de que $pdo esté disponible desde 'conexion.php'
    if (!isset($pdo)) {
        throw new PDOException("La conexión a la base de datos no está disponible. Revisa 'conexion.php'.");
    }
    
    $stmt = $pdo->query($sql);
    
    // 4. Obtener todos los resultados en un array asociativo
    $productos = $stmt->fetchAll();

} catch (PDOException $e) {
    // Mostrar un error si algo falla en la conexión o la consulta
    $mensaje_error = "Error al cargar los productos: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario Super Q | Lista de Productos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f8f9fa; }
        .container { max-width: 1200px; margin: auto; padding: 20px; background-color: white; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #007bff; margin-bottom: 25px; }
        a.btn { 
            display: inline-block; 
            margin-bottom: 20px;
            padding: 10px 15px; 
            background-color: #28a745; 
            color: white; 
            text-decoration: none; 
            border-radius: 5px;
            font-weight: bold;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #dee2e6; padding: 10px; text-align: left; }
        th { background-color: #e9ecef; color: #495057; }
        .acciones { width: 180px; text-align: center; }
        .error { color: #dc3545; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .exito { color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        /* Estilo para stock bajo */
        .stock-bajo { background-color: #ffc2c2; font-weight: bold; }
        
        /* Estilos de botones de acción */
        .btn-editar { color: #007bff; text-decoration: none; margin-right: 10px; }
        .btn-eliminar { color: #dc3545; text-decoration: none; }
    </style>
</head>
<body>

    <div class="container">
        <h1>Inventario de Productos Super Q: Bebidas y Botanas (<?php echo count($productos); ?> Productos)</h1>
        
        <?php if ($mensaje_error): ?>
            <p class="error"><?php echo $mensaje_error; ?></p>
        <?php endif; ?>
        
        <?php if ($mensaje_exito): ?>
            <p class="exito"><?php echo $mensaje_exito; ?></p>
        <?php endif; ?>
        
        <a href="crear.php" class="btn">➕ Añadir Nuevo Producto</a>

        <?php if (count($productos) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Precio</th>
                    <th>Caducidad</th>
                    <th>Stock</th>
                    <th>Cód. Barras</th>
                    <th>Proveedor</th>
                    <th class="acciones">Acciones</th> 
                </tr>
            </thead>
            <tbody>
                <?php 
                // Iteramos sobre el array $productos para mostrar cada registro
                foreach ($productos as $producto): 
                    // Clase CSS para resaltar stock bajo
                    $stock_class = ($producto['cantidad'] < 10) ? 'stock-bajo' : '';
                ?>
                <tr class="<?php echo $stock_class; ?>">
                    <td><?php echo htmlspecialchars($producto['id_producto']); ?></td>
                    <td><?php echo htmlspecialchars($producto['nombre_producto']); ?></td>
                    <td>$<?php echo htmlspecialchars(number_format($producto['precio'], 2)); ?></td>
                    <td><?php 
                        // Muestra "N/A" si la fecha de caducidad es nula
                        echo $producto['caducidad'] ? htmlspecialchars($producto['caducidad']) : 'N/A'; 
                    ?></td>
                    <td><?php echo htmlspecialchars($producto['cantidad']); ?></td>
                    <td><?php echo htmlspecialchars($producto['id_barras']); ?></td>
                    <td><?php echo htmlspecialchars($producto['proveedor']); ?></td>
                    <td class="acciones">
                        <a href="editar.php?id=<?php echo $producto['id_producto']; ?>" class="btn-editar">✏️ Editar</a>
                        
                        <a href="eliminar.php?id=<?php echo $producto['id_producto']; ?>" 
                            onclick="return confirm('¿Estás seguro de que quieres eliminar este producto?');"
                            class="btn-eliminar">🗑️ Eliminar</a>
                    </td>
                </tr>
                <?php 
                    endforeach; 
                ?>
            </tbody>
        </table>
        <?php else: ?>
            <p style="text-align: center; padding: 20px; border: 1px solid #ccc; border-radius: 5px;">No hay productos registrados en el inventario.</p>
        <?php endif; ?>
    </div>

</body>
</html>