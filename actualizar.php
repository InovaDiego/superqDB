<?php
// =======================================================
// Archivo: actualizar.php (Procesador de la operación UPDATE)
// =======================================================

require 'conexion.php'; 

if (isset($_POST['submit'])) {
    
    // 1. Recolección de datos, incluyendo el ID oculto
    $id = htmlspecialchars($_POST['id_producto']);
    $nombre = htmlspecialchars($_POST['nombre']);
    $precio = htmlspecialchars($_POST['precio']);
    $caducidad = htmlspecialchars($_POST['caducidad']);
    $cantidad = htmlspecialchars($_POST['cantidad']);
    $id_barras = htmlspecialchars($_POST['id_barras']);
    $proveedor = htmlspecialchars($_POST['proveedor']);
    
    // 2. Consulta SQL para UPDATE (cambiar)
    $sql = "UPDATE PRODUCTOS SET 
                nombre_producto = ?, 
                precio = ?, 
                caducidad = ?, 
                cantidad = ?, 
                id_barras = ?, 
                proveedor = ? 
            WHERE id_producto = ?";
    
    try {
        // 3. Preparar y ejecutar la sentencia
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            $nombre, 
            $precio, 
            $caducidad, 
            $cantidad, 
            $id_barras, 
            $proveedor, 
            $id 
        ]);
        
        // 4. Redireccionar al index con mensaje de éxito
        header('Location: index.php?mensaje=actualizado');
        exit();
        
    } catch (PDOException $e) {
        // Redireccionar al index con mensaje de error
        header('Location: index.php?error=' . urlencode("Error al actualizar: " . $e->getMessage()));
        exit();
    }
} else {
    // Si no se envió el formulario, redirigir al index
    header('Location: index.php');
    exit();
}
?>