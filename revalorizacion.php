<?php
// revalorizacion.php - Script mensual para revalorización (ejecuta martes 00:01 VET)
// Acceso: Solo admin (hardcode email o via login)

include 'db_connect.php';

// Check admin (hardcode para MVP; integra session si quieres)
$admin_emails = ['yo@test.com', 'naranjazos@gmail.com'];
$current_email = $_GET['admin'] ?? '';  // Para test: ?admin=yo@test.com
if (!in_array($current_email, $admin_emails)) {
    die('Acceso denegado. Solo admins.');
}

// Mes anterior (para avg score; para MVP, usa todos scores)
$prev_month = date('Y-m', strtotime('-1 month'));
$start_date = $prev_month . '-01';
$end_date = date('Y-m-d', strtotime($start_date . ' +1 month'));

// Log file
$log_file = 'revalor_log.txt';
$log = function($msg) use ($log_file) {
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - $msg\n", FILE_APPEND);
};

$log("Iniciando revalorización para mes $prev_month");

// Agrega columna score_date si no existe (para filtrar futuras jornadas)
$check_score_date = mysqli_query($conn, "SHOW COLUMNS FROM players LIKE 'score_date'");
if (mysqli_num_rows($check_score_date) == 0) {
    mysqli_query($conn, "ALTER TABLE players ADD COLUMN score_date DATE DEFAULT CURRENT_DATE");
    $log("Columna score_date agregada.");
}

// Agrega columna capital en users si no existe (default 60000000)
$check_capital = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'capital'");
if (mysqli_num_rows($check_capital) == 0) {
    mysqli_query($conn, "ALTER TABLE users ADD COLUMN capital DECIMAL(12,0) DEFAULT 60000000");
    $log("Columna capital agregada con default 60000000.");
}

// 1. Calcula avg score por jugador en mes (usa score_date si existe; para MVP, todos scores)
$avg_query = "SELECT p_id, AVG(score) as avg_score FROM players WHERE 1=1";  // Sin filtro fecha por MVP; agrega AND score_date BETWEEN... cuando tengas dates
$avg_result = mysqli_query($conn, $avg_query);

$updated = 0;
while ($row = mysqli_fetch_assoc($avg_result)) {
    $p_id = $row['p_id'];
    $avg = floatval($row['avg_score']);
    $current_price_query = "SELECT price FROM players WHERE p_id = '$p_id'";
    $price_result = mysqli_query($conn, $current_price_query);
    $current_price = mysqli_fetch_assoc($price_result)['price'];

    $new_price = $current_price;
    $change = 'neutral';
    if ($avg > 6) {
        $new_price = $current_price * 1.1;  // +10%
        $change = 'up';
    } elseif ($avg < 5) {
        $new_price = $current_price * 0.9;  // -10%
        $change = 'down';
    }

    $update_price = "UPDATE players SET price = '$new_price' WHERE p_id = '$p_id'";
    if (mysqli_query($conn, $update_price)) {
        $log("Jugador ID $p_id: Avg $avg -> Price $current_price to $new_price ($change)");
        $updated++;

        // 2. Ajusta capital users que tienen este jugador
        $diff = $new_price - $current_price;
        $capital_update = "UPDATE users u JOIN dream_team dt ON u.email = dt.email SET u.capital = COALESCE(u.capital, 60000000) + '$diff' WHERE dt.pid = '$p_id'";
        mysqli_query($conn, $capital_update);
        $log("Capital ajustado por $diff para owners de ID $p_id");
    }
}

$log("Revalorización completada: $updated jugadores actualizados para $prev_month");
echo "Revalorización OK: $updated jugadores. Ver log en revalor_log.txt";
?>