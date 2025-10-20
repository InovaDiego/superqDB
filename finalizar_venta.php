<?php
require 'conexion.php';
session_start();

// 1. Verificar que haya algo que vender
if (empty($_SESSION['carrito']) || !isset($_POST['total_final'])) {
    header("Location: pos.php");
    exit;
}

$total_final = round($_POST['total_final'], 2);
$carrito = $_SESSION['carrito'];
$venta_exitosa = false;
$id_venta = null;

try {
    // Iniciar Transacción
    $pdo->beginTransaction();

    // 2. Insertar la Cabecera de la Venta (VENTAS)
    $sql_venta = "INSERT INTO VENTAS (total_venta, forma_pago) VALUES (?, ?)";
    $stmt_venta = $pdo->prepare($sql_venta);
    $stmt_venta->execute([$total_final, 'Efectivo']); // Asumimos efectivo para simplicidad
    
    // Obtener el ID de la venta recién creada
    $id_venta = $pdo->lastInsertId();

    // 3. Procesar cada ítem del carrito (DETALLE_VENTA y Actualizar Stock)
    $sql_detalle = "INSERT INTO DETALLE_VENTA (id_venta, id_producto, cantidad_vendida, precio_unitario_venta) VALUES (?, ?, ?, ?)";
    $sql_stock = "UPDATE PRODUCTOS SET cantidad = cantidad - ? WHERE id_producto = ?";

    foreach ($carrito as $item) {
        // Insertar Detalle
        $stmt_detalle = $pdo->prepare($sql_detalle);
        $stmt_detalle->execute([
            $id_venta, 
            $item['id_producto'], 
            $item['cantidad'], 
            $item['precio']
        ]);

        // Reducir Stock
        $stmt_stock = $pdo->prepare($sql_stock);
        $stmt_stock->execute([$item['cantidad'], $item['id_producto']]);
    }

    // Si todo salió bien, confirmar la transacción
    $pdo->commit();
    $venta_exitosa = true;

} catch (Exception $e) {
    // Si algo falla, revertir todo
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // NOTA: Para un proyecto real, aquí deberías manejar errores específicos (ej. stock insuficiente)
    echo "Error al procesar la venta: " . $e->getMessage();
    exit;
}

// 4. Limpiar el carrito después de la venta exitosa
unset($_SESSION['carrito']);

// 5. Preparar la visualización del Ticket
$subtotal = round($total_final / 1.16, 2);
$iva = round($total_final - $subtotal, 2);
$fecha_actual = date('d-m-Y H:i:s');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket de Venta #<?php echo $id_venta; ?></title>
    <style>
        /* Estilos del Ticket */
        body { font-family: monospace; font-size: 12px; margin: 0; padding: 20px; background-color: #eee; }
        .ticket { width: 300px; margin: auto; padding: 15px; border: 1px dashed #333; background-color: white; }
        .header, .footer, .line { text-align: center; margin-bottom: 10px; }
        .line { border-bottom: 1px dashed #333; }
        .item-list { margin-bottom: 10px; }
        .item { display: flex; justify-content: space-between; margin-bottom: 3px; }
        .total-row { display: flex; justify-content: space-between; font-weight: bold; margin-top: 5px; }
        .total-row.final { font-size: 1.3em; margin-top: 10px; border-top: 1px dashed #333; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="header">
            <h3>SUPER Q, S.A. DE C.V.</h3>
            <p>Venta</p>
            <p>RFC: SQX981027RY5</p>
            <p>C.P. 76069 Querétaro, Qro.</p>
        </div>
        <div class="line"></div>
        
        <div class="info">
            <p>CAJA: 01</p>
            <p>FOLIO: <?php echo str_pad($id_venta, 6, '0', STR_PAD_LEFT); ?></p>
            <p>FECHA: <?php echo $fecha_actual; ?></p>
        </div>
        <div class="line"></div>
        
        <div class="item-list">
            <div class="item" style="font-weight: bold;">
                <span style="width: 150px;">DESCRIPCION</span>
                <span>CANT.</span>
                <span>IMPORTE</span>
            </div>
            <div class="line"></div>
            
            <?php foreach ($carrito as $item): ?>
                <div class="item">
                    <span style="width: 150px;"><?php echo htmlspecialchars($item['nombre_producto']); ?></span>
                    <span><?php echo htmlspecialchars($item['cantidad']); ?></span>
                    <span>$<?php echo number_format($item['precio'] * $item['cantidad'], 2); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="line"></div>
        <div class="totals">
            <div class="total-row"><span>SUBTOTAL:</span> <span>$<?php echo number_format($subtotal, 2); ?></span></div>
            <div class="total-row"><span>I.V.A. (16%):</span> <span>$<?php echo number_format($iva, 2); ?></span></div>
            <div class="total-row final"><span>TOTAL:</span> <span>$<?php echo number_format($total_final, 2); ?></span></div>
        </div>

        <div class="footer">
            <div class="line"></div>
            <p>¡Gracias por su compra!</p>
            <p>TICKET: <?php echo time(); ?></p>
        </div>
    </div>
</body>
</html>