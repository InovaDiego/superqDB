<?php
require 'conexion.php';

// Inicializar la sesión y el carrito si no existen
session_start();
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$productos = [];
try {
    // 💡 MODIFICACIÓN 1: Incluir la columna 'ruta_imagen' en la consulta SQL
    $sql = "SELECT id_producto, nombre_producto, precio, cantidad, ruta_imagen FROM PRODUCTOS ORDER BY nombre_producto ASC";
    $stmt = $pdo->query($sql);
    $productos = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Error al cargar productos: " . $e->getMessage();
}

// =======================================================
// LÓGICA AÑADIDA: AJUSTAR CANTIDAD EN EL CARRITO (+/-)
// =======================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['update_quantity']) || isset($_POST['decrement_quantity']))) {
    $id_producto = isset($_POST['update_quantity']) ? $_POST['update_quantity'] : $_POST['decrement_quantity'];
    
    if (isset($_SESSION['carrito'][$id_producto])) {
        if (isset($_POST['update_quantity'])) {
            // Acción de incrementar (+)
            $_SESSION['carrito'][$id_producto]['cantidad'] += 1;
        } elseif (isset($_POST['decrement_quantity'])) {
            // Acción de decrementar (-)
            $_SESSION['carrito'][$id_producto]['cantidad'] -= 1;

            // Si la cantidad llega a 0, eliminamos el ítem del carrito
            if ($_SESSION['carrito'][$id_producto]['cantidad'] < 1) {
                unset($_SESSION['carrito'][$id_producto]);
            }
        }
    }
    header("Location: pos.php");
    exit;
}

// =======================================================
// LÓGICA CORREGIDA: AÑADIR PRODUCTO AL CARRITO (CON CANTIDAD VARIABLE)
// =======================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $id = $_POST['id_producto'];
    $nombre = $_POST['nombre_producto'];
    $precio = $_POST['precio'];
    
    // Obtener la cantidad del campo 'cantidad_agregar' del formulario HTML
    $cantidad = isset($_POST['cantidad_agregar']) ? (int)$_POST['cantidad_agregar'] : 1; 
    if ($cantidad < 1) $cantidad = 1;

    if (isset($_SESSION['carrito'][$id])) {
        // Si el producto ya está, solo aumenta la cantidad sumando el valor del input
        $_SESSION['carrito'][$id]['cantidad'] += $cantidad;
    } else {
        // Si es nuevo, añade el producto con la cantidad deseada
        $_SESSION['carrito'][$id] = [
            'id_producto' => $id,
            'nombre_producto' => $nombre,
            'precio' => $precio,
            'cantidad' => $cantidad
        ];
    }
    // Redireccionar para evitar reenvío
    header("Location: pos.php");
    exit;
}

// =======================================================
// LÓGICA AÑADIDA: ELIMINAR PRODUCTO DEL CARRITO (Botón ❌)
// =======================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_from_cart'])) {
    $id_a_eliminar = $_POST['remove_from_cart'];
    if (isset($_SESSION['carrito'][$id_a_eliminar])) {
        // Elimina el elemento completo del array de sesión (el carrito)
        unset($_SESSION['carrito'][$id_a_eliminar]);
    }
    // Redireccionar para actualizar la vista
    header("Location: pos.php");
    exit;
}
// =======================================================


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
        /* Añadido estilo para la imagen */
        .producto-card img { max-width: 100%; height: 100px; object-fit: contain; margin-bottom: 10px; } 
        .producto-card h4 { margin: 5px 0; font-size: 1em; }
        .producto-card p { font-size: 1.2em; color: #007bff; font-weight: bold; }
        .carrito { flex: 1; padding: 20px; background-color: #333; color: white; min-width: 350px; }
        .carrito h2 { border-bottom: 2px solid white; padding-bottom: 10px; margin-top: 0; }
        /* 💡 CORRECCIÓN CSS: Alineación de ítems del carrito */
        .item-carrito { display: flex; justify-content: space-between; margin-bottom: 10px; border-bottom: 1px dotted #555; padding-bottom: 5px; } 
        .item-carrito span { font-size: 0.9em; }
        .total-box { border-top: 2px dashed white; margin-top: 20px; padding-top: 10px; }
        .total-box p { font-size: 1.5em; font-weight: bold; }
        .btn-comprar { width: 100%; padding: 15px; background-color: #28a745; color: white; border: none; font-size: 1.2em; cursor: pointer; margin-top: 15px; }
    </style>
</head>
<body>

    <div class="catalogo">
        <h2>Inventario (Use el campo Cantidad y Añadir)</h2>
        <?php foreach ($productos as $producto): ?>
            <div class="producto-card">
                
                <?php 
                // Usamos la carpeta 'imagenes/' que tienes en tu proyecto
                $ruta_img = isset($producto['ruta_imagen']) && !empty($producto['ruta_imagen']) 
                            ? 'imagenes/' . htmlspecialchars($producto['ruta_imagen']) 
                            : 'https://via.placeholder.com/100/CCCCCC/000000?text=Sin+Foto';
                ?>
                <img src="<?php echo $ruta_img; ?>" alt="<?php echo htmlspecialchars($producto['nombre_producto']); ?>">
                <h4><?php echo htmlspecialchars($producto['nombre_producto']); ?></h4>
                
                <p>$<?php echo htmlspecialchars(number_format($producto['precio'], 2)); ?></p>
                <form method="POST" action="pos.php">
                    <input type="hidden" name="id_producto" value="<?php echo $producto['id_producto']; ?>">
                    <input type="hidden" name="nombre_producto" value="<?php echo $producto['nombre_producto']; ?>">
                    <input type="hidden" name="precio" value="<?php echo $producto['precio']; ?>">
                    
                    <label for="cantidad_<?php echo $producto['id_producto']; ?>" style="font-size: 0.8em;">Cant:</label>
                    <input type="number" 
                           id="cantidad_<?php echo $producto['id_producto']; ?>" 
                           name="cantidad_agregar" 
                           value="1" 
                           min="1" 
                           style="width: 50px; text-align: center; margin-bottom: 10px; border: 1px solid #ccc;">
                           
                    <button type="submit" name="add_to_cart" class="btn-comprar" style="background-color: #007bff; padding: 8px;">
                        Añadir al Carrito
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="carrito">
        <h2>CARRITO DE VENTA</h2>
        <?php if (count($_SESSION['carrito']) > 0): ?>
            
            <?php 
            // Bucle que itera sobre los ítems del carrito con controles de cantidad +/-
            foreach ($_SESSION['carrito'] as $id => $item): 
            ?>
                <div class="item-carrito" style="align-items: center;">
                    <div style="flex-grow: 1; margin-right: 10px;">
                        <span><?php echo htmlspecialchars($item['nombre_producto']); ?></span>
                        <br>
                        <span style="font-size: 0.8em; color: #ccc;">@$<?php echo number_format($item['precio'], 2); ?></span>
                    </div>

                    <div style="display: flex; align-items: center; gap: 5px;">
                        <form method="POST" action="pos.php" style="display: inline;">
                            <input type="hidden" name="decrement_quantity" value="<?php echo $id; ?>">
                            <button type="submit" style="padding: 3px 8px; background-color: #f8d7da; border: none; cursor: pointer; color: black; font-weight: bold;">
                                -
                            </button>
                        </form>
                        
                        <span style="font-weight: bold; min-width: 20px; text-align: center;">
                            <?php echo htmlspecialchars($item['cantidad']); ?>
                        </span>

                        <form method="POST" action="pos.php" style="display: inline;">
                            <input type="hidden" name="update_quantity" value="<?php echo $id; ?>">
                            <button type="submit" style="padding: 3px 7px; background-color: #d4edda; border: none; cursor: pointer; color: black; font-weight: bold;">
                                +
                            </button>
                        </form>

                        <span style="margin-left: 10px; font-weight: bold; min-width: 60px; text-align: right;">
                            $<?php echo number_format($item['precio'] * $item['cantidad'], 2); ?>
                        </span>

                        <form method="POST" action="pos.php" style="display: inline;">
                            <input type="hidden" name="remove_from_cart" value="<?php echo $id; ?>">
                            <button type="submit" 
                                    onclick="return confirm('¿Eliminar todas las unidades de <?php echo htmlspecialchars($item['nombre_producto']); ?>?');"
                                    style="background: none; border: none; color: red; cursor: pointer; font-weight: bold; padding: 0 5px;"
                                    title="Eliminar ítem completo">
                                ❌
                            </button>
                        </form>
                    </div>
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