<?php
// Conexión PDO reutilizable
// Configuración para servidor DonWeb
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 30
        ]
    );
} catch (Exception $e) {
    // En producción loggear el error en archivo y mostrar mensaje genérico
    error_log("Error de conexión BD: " . $e->getMessage());
    die('Error de conexión a la base de datos. Por favor, contacte al administrador.');
}
