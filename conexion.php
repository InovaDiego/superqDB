<?php
// =======================================================
// Archivo: conexion.php
// Objetivo: Conexión segura a la base de datos 'superq'
// =======================================================

$host = 'localhost';
$db   = 'superq'; // ¡Cambiado a la nueva base de datos!
$user = 'root'; 
$pass = ''; 
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    // Usamos PDO (PHP Data Objects), recomendado por seguridad
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     echo "<h1>Error de Conexión:</h1>";
     echo "<p>Verifica si MySQL en XAMPP está encendido.</p>";
     echo "<p>Mensaje de error: " . $e->getMessage() . "</p>";
     exit();
}
// La variable $pdo contiene el objeto de conexión
?>