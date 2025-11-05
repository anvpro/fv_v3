<?php
// === LÍNEAS CRÍTICAS PARA FORZAR LA DEPURACIÓN Y EVITAR ERRORES 500 ===
// Esto convierte los "HTTP ERROR 500" genéricos en errores específicos de PHP.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// === FIN DEPURACIÓN CRÍTICA ===

$host = 'localhost';
$dbname = 'u281742200_fantasi';  // Tu DB name
$username = 'u281742200_naranjazos';   // Tu DB user
$password = 'Chupalo69**';     // Tu pass

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    // Si la conexión falla, ahora mostrará un mensaje claro en lugar de 500.
    die("❌ Error Crítico: Fallo en la conexión a la base de datos. Detalles: " . $conn->connect_error);
}

// Establecer el juego de caracteres (utf8mb4 para compatibilidad total)
$conn->set_charset("utf8mb4"); 
?>