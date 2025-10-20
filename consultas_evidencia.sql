-- =======================================================
-- CONSULTAS SQL DE EVIDENCIA PARA INVENTARIO SUPERQ
-- Total de productos esperado: 100
-- =======================================================

-- 1. CONTEO TOTAL Y VALOR DE INVENTARIO (Métricas de Negocio)
SELECT 
    COUNT(id_producto) AS Total_Productos, 
    SUM(precio * cantidad) AS Valor_Total_Inventario 
FROM 
    PRODUCTOS;

-- 2. PRODUCTOS CON STOCK BAJO (Control de Existencias - Menos de 20)
SELECT 
    nombre_producto, 
    cantidad 
FROM 
    PRODUCTOS 
WHERE 
    cantidad < 20 
ORDER BY 
    cantidad ASC;

-- 3. PRODUCTOS DEL PROVEEDOR ESPECÍFICO 
SELECT 
    nombre_producto, 
    precio 
FROM 
    PRODUCTOS 
WHERE 
    proveedor = 'Coca-Cola Company' 
ORDER BY 
    precio DESC;
    -- Filtra todos los productos de un proovedor en este caso COCA :p 

-- 4. PRODUCTOS PRÓXIMOS A CADUCAR (Gestión de Fecha - Próximos 4 meses)
SELECT 
    nombre_producto, 
    caducidad, 
    cantidad 
FROM 
    PRODUCTOS 
WHERE 
    caducidad IS NOT NULL 
    AND caducidad < DATE_ADD(CURDATE(), INTERVAL 4 MONTH) 
ORDER BY 
    caducidad ASC;

-- 5. Consulta de los 100 productos 
USE superq;
SELECT COUNT(id_producto) AS Total_Productos FROM PRODUCTOS; 