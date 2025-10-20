USE superq;

-- 1. TABLA VENTAS: Cabecera de la Compra
-- Contiene información general de la transacción.
CREATE TABLE VENTAS (
    id_venta INT(10) NOT NULL AUTO_INCREMENT,
    fecha_venta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Registra la fecha y hora de la compra
    total_venta DECIMAL(10, 2) NOT NULL,                    -- Monto total pagado por el cliente
    forma_pago VARCHAR(50) NOT NULL,                       -- Ej: 'Efectivo', 'Tarjeta'
    PRIMARY KEY (id_venta)
);

-- 2. TABLA DETALLE_VENTA: Items de la Compra
-- Contiene cada producto, su precio en el momento y la cantidad comprada.
CREATE TABLE DETALLE_VENTA (
    id_detalle INT(10) NOT NULL AUTO_INCREMENT,
    id_venta INT(10) NOT NULL,                              -- Clave foránea a la tabla VENTAS
    id_producto INT(10) NOT NULL,                           -- Clave foránea a la tabla PRODUCTOS
    cantidad_vendida INT(10) NOT NULL,                      -- Cuántas unidades se vendieron
    precio_unitario_venta DECIMAL(6, 2) NOT NULL,           -- Precio del producto en ESE momento
    
    PRIMARY KEY (id_detalle),
    
    -- Definición de Claves Foráneas
    FOREIGN KEY (id_venta) REFERENCES VENTAS(id_venta),
    FOREIGN KEY (id_producto) REFERENCES PRODUCTOS(id_producto)
);