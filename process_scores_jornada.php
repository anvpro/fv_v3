<?php
// Script de Finalización de Puntuación de Jornada (Destinado a ser un Cron Job)

// 1. Incluir el archivo de conexión
require_once 'db_connect.php'; 

// Usamos la misma zona horaria del admin_scores.php
date_default_timezone_set('America/Caracas'); // VET

if (!isset($conn) || $conn->connect_error) {
    die("Error crítico de conexión DB.");
}

// =======================================================
// 1. IDENTIFICAR LA JORNADA CERRADA
// =======================================================

// Buscamos la jornada que acaba de terminar (active=1 y ya pasó la fecha_puntuacion_fin)
$current_time = date('Y-m-d H:i:s');
$jornada_sql = "SELECT id, nombre FROM jornadas 
                WHERE active = 1 AND fecha_puntuacion_fin <= ?";

if ($stmt = $conn->prepare($jornada_sql)) {
    $stmt->bind_param("s", $current_time);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    // Si no hay jornadas para cerrar, terminamos
    if ($resultado->num_rows == 0) {
        // echo "No hay jornadas listas para finalizar en este momento.";
        $conn->close();
        exit();
    }
    
    $jornada_a_cerrar = $resultado->fetch_assoc();
    $id_jornada_cerrada = $jornada_a_cerrar['id'];
    $nombre_jornada = $jornada_a_cerrar['nombre'];
    $stmt->close();
} else {
    die("Error al preparar la consulta de jornada: " . $conn->error);
}

// =======================================================
// 2. AGREGAR PUNTOS DE DREAM_TEAM Y GUARDAR EN PUNTUACIONES
// =======================================================

// Agregamos los puntos por usuario desde dream_team (que contiene los puntos actuales)
// Nota: Usamos INSERT...ON DUPLICATE KEY UPDATE por si el script se corre más de una vez.
// Esto requiere que 'puntuaciones' tenga la clave UNIQUE (id_usuario, id_jornada), lo cual ya confirmamos.

$puntos_sql = "INSERT INTO puntuaciones (id_usuario, id_jornada, puntos_totales)
               SELECT dt.id_usuario, ?, SUM(dt.points)
               FROM dream_team dt
               GROUP BY dt.id_usuario
               ON DUPLICATE KEY UPDATE 
               puntos_totales = VALUES(puntos_totales)";

if ($stmt = $conn->prepare($puntos_sql)) {
    $stmt->bind_param("i", $id_jornada_cerrada);
    $stmt->execute();
    $stmt->close();
    
    // Si la inserción fue exitosa, procedemos a limpiar y cerrar
    
    // 2.1 Reiniciar los puntos de los equipos para la nueva jornada (en dream_team)
    $conn->query("UPDATE dream_team SET points = 0.00");
    
    // 2.2 Desactivar la jornada
    $conn->query("UPDATE jornadas SET active = 0 WHERE id = {$id_jornada_cerrada}");

    // 2.3 Activar la próxima jornada (Asumiendo que id = id_jornada_cerrada + 1)
    $conn->query("UPDATE jornadas SET active = 1 WHERE id = ({$id_jornada_cerrada} + 1)");
    
} else {
    die("Error al preparar la consulta de puntos: " . $conn->error);
}

// =======================================================
// 3. ACTUALIZAR JUGADORES (Reiniciar scores y puntos)
// =======================================================

// Reiniciar los scores de los jugadores y sus fechas de score
$conn->query("UPDATE players SET score = 0.00, final_score = 0.00, score_date = NULL");

echo "Proceso de finalización de la {$nombre_jornada} completado exitosamente.";
$conn->close();
?>