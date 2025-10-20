<?php
require 'conexion.php';

// Inicializar el carrito en la sesión si no existe
session_start();
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$productos = [];
try {
    // NOTA: Asumiremos que tienes una columna de 'ruta_imagen' en PRODUCTOS,
    //       pero por ahora, solo cargaremos los datos.
    $sql = "SELECT id_producto, nombre_producto, precio, cantidad FROM PRODUCTOS ORDER BY nombre_producto ASC";
    $stmt = $pdo->query($sql);
    $productos = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Error al cargar productos: " . $e->getMessage();
}

// Lógica para añadir un producto al carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $id = $_POST['id_producto'];
    $nombre = $_POST['nombre_producto'];
    $precio = $_POST['precio'];
    $cantidad = 1; // Siempre se añade 1 unidad por defecto

    if (isset($_SESSION['carrito'][$id])) {
        // Si el producto ya está, solo aumenta la cantidad
        $_SESSION['carrito'][$id]['cantidad'] += $cantidad;
    } else {
        // Si es nuevo, añade el producto al carrito
        $_SESSION['carrito'][$id] = [
            'id_producto' => $id,
            'nombre_producto' => $nombre,
            'precio' => $precio,
            'cantidad' => $cantidad
        ];
    }
    // Redireccionar para evitar reenvío del formulario (buena práctica)
    header("Location: pos.php");
    exit;
}

// Lógica para calcular el total del carrito
$subtotal = 0;
foreach ($_SESSION['carrito'] as $item) {
    $subtotal += $item['precio'] * $item['cantidad'];
}
$iva_tasa = 0.16; // 16% de IVA en México
$iva = $subtotal * $iva_tasa;
$total = $subtotal + $iva;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Punto de Venta - Super Q</title>
    <style>
        /* Estilos básicos para la interfaz de punto de venta (CSS Rápido) */
        body { font-family: Arial, sans-serif; display: flex; }
        .catalogo { flex: 2; padding: 20px; background-color: #f4f4f4; display: flex; flex-wrap: wrap; gap: 20px; }
        .producto-card { border: 1px solid #ccc; padding: 10px; width: 180px; text-align: center; background-color: white; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .producto-card img { max-width: 100%; height: auto; margin-bottom: 10px; }
        .producto-card h4 { margin: 5px 0; font-size: 1em; }
        .producto-card p { font-size: 1.2em; color: #007bff; font-weight: bold; }
        .carrito { flex: 1; padding: 20px; background-color: #333; color: white; min-width: 350px; }
        .carrito h2 { border-bottom: 2px solid white; padding-bottom: 10px; margin-top: 0; }
        .item-carrito { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .item-carrito span { font-size: 0.9em; }
        .total-box { border-top: 2px dashed white; margin-top: 20px; padding-top: 10px; }
        .total-box p { font-size: 1.5em; font-weight: bold; }
        .btn-comprar { width: 100%; padding: 15px; background-color: #28a745; color: white; border: none; font-size: 1.2em; cursor: pointer; margin-top: 15px; }
    </style>
</head>
<body>

    <div class="catalogo">
        <h2>Inventario (Haga clic en 'Añadir' para simular el escaneo)</h2>
        <?php foreach ($productos as $producto): ?>
            <div class="producto-card">
                <p>📦</p> 
                <h4><?php echo htmlspecialchars($producto['nombre_producto']); ?></h4>
                <p>$<?php echo htmlspecialchars(number_format($producto['precio'], 2)); ?></p>
                <form method="POST" action="pos.php">
                    <input type="hidden" name="id_producto" value="<?php echo $producto['id_producto']; ?>">
                    <input type="hidden" name="nombre_producto" value="<?php echo $producto['nombre_producto']; ?>">
                    <input type="hidden" name="precio" value="<?php echo $producto['precio']; ?>">
                    <button type="submit" name="add_to_cart" class="btn-comprar" style="background-color: #007bff; padding: 8px;">Añadir al Carrito</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="carrito">
        <h2>CARRITO DE VENTA</h2>
        <?php if (count($_SESSION['carrito']) > 0): ?>
            <?php foreach ($_SESSION['carrito'] as $id => $item): ?>
                <div class="item-carrito">
                    <span><?php echo htmlspecialchars($item['cantidad']); ?> x <?php echo htmlspecialchars($item['nombre_producto']); ?></span>
                    <span>$<?php echo number_format($item['precio'] * $item['cantidad'], 2); ?></span>
                </div>
            <?php endforeach; ?>
            
            <div class="total-box">
                <p>SUBTOTAL: $<?php echo number_format($subtotal, 2); ?></p>
                <p>IVA (16%): $<?php echo number_format($iva, 2); ?></p>
                <p style="color: yellow;">TOTAL A PAGAR: $<?php echo number_format($total, 2); ?></p>

                <form method="POST" action="finalizar_venta.php">
                    <input type="hidden" name="total_final" value="<?php echo $total; ?>">
                    <button type="submit" class="btn-comprar">FINALIZAR VENTA (Generar Ticket)</button>
                </form>
            </div>
        <?php else: ?>
            <p>El carrito está vacío. Añada productos.</p>
        <?php endif; ?>
    </div>

</body>
</html>