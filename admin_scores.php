<?php
// --- INICIO DE DEPURACIÓN CRÍTICA (Desactivar en producción) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- FIN DE DEPURACIÓN CRÍTICA ---

session_start();
include 'db_connect.php';

// =======================================================
// 1. VERIFICACIÓN DE ACCESO
// =======================================================
if (!isset($_SESSION['usrn'])) {
    header("Location: index.php");
    exit();
}
$email = $_SESSION['usrn'];
$admin_query = "SELECT is_admin FROM users WHERE email = '$email'";
$admin_result = mysqli_query($conn, $admin_query);
if (mysqli_fetch_assoc($admin_result)['is_admin'] != 1) {
    die('Acceso denegado. Solo admins.');
}

$success_msg = '';
$error_msg = '';
$jornadas = [];
$selected_jornada_id = null;
$selected_jornada_nombre = 'N/A';
// Se elimina $is_locked por estar obsoleta/no utilizada

date_default_timezone_set('America/Caracas');  // VET

// =======================================================
// 2. OBTENER LISTA DE JORNADAS Y LA SELECCIONADA
// (SE MANTIENE TAL CUAL: Es la referencia a las jornadas con fechas para modificación histórica)
// =======================================================

// Obtener todas las jornadas disponibles
$sql_jornadas = "SELECT id, nombre, fecha_mercado_inicio, fecha_puntuacion_fin, active FROM jornadas ORDER BY id DESC";

if ($resultado_j = mysqli_query($conn, $sql_jornadas)) {
    while ($fila = mysqli_fetch_assoc($resultado_j)) {
        $jornadas[] = $fila;
    }
} else {
    $error_msg = "Error al obtener jornadas: " . mysqli_error($conn);
}

// Determinar la jornada a mostrar (prioridad: GET, luego: la más reciente)
if (isset($_GET['jornada_id']) && is_numeric($_GET['jornada_id'])) {
    $selected_jornada_id = intval($_GET['jornada_id']);
} elseif (!empty($jornadas)) {
    $selected_jornada_id = $jornadas[0]['id'];
}

// Obtener el nombre y el estado (active) de la jornada seleccionada para la vista
$is_active_jornada = false;
foreach ($jornadas as $j) {
    if ($j['id'] == $selected_jornada_id) {
        $inicio = date('d/M', strtotime($j['fecha_mercado_inicio']));
        $fin = date('d/M', strtotime($j['fecha_puntuacion_fin']));
        $selected_jornada_nombre = htmlspecialchars($j['nombre']) . " (" . $inicio . " - " . $fin . ")";
        $is_active_jornada = $j['active'] == 1; 
        break;
    }
}


// =======================================================
// 3. LÓGICA DE ACTUALIZACIÓN DE SCORES (POST)
// =======================================================

if (isset($_POST['update_scores'])) {
    
    $jornada_id_post = isset($_POST['jornada_id']) ? intval($_POST['jornada_id']) : null;
    $jornada_nombre_post = '';

    foreach ($jornadas as $j) {
        if ($j['id'] == $jornada_id_post) {
            $jornada_nombre_post = $j['nombre'];
            $is_active_post = $j['active'] == 1; // Bandera para saber si es la jornada activa
            break;
        }
    }
    
    if (!$jornada_id_post) {
        $error_msg = "Error: ID de jornada no especificado en el formulario.";
    } else {
        $trans_error = false;
        mysqli_begin_transaction($conn);
        
        try {
            // === PREPARAR SENTENCIAS ===
            // 1. Guardar/Actualizar en la tabla histórica (player_jornada_scores)
            $hist_update = "INSERT INTO player_jornada_scores 
                            (player_id, jornada_id, base_score, gol, asistencia, final_score) 
                            VALUES (?, ?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE 
                            base_score = VALUES(base_score), 
                            gol = VALUES(gol), 
                            asistencia = VALUES(asistencia),
                            final_score = VALUES(final_score)";

            // 2. Actualizar el Live Score en la tabla players (Solo si es la jornada activa)
            $live_update = "UPDATE players SET score = ?, final_score = ?, score_date = ? WHERE p_id = ?";
            
            // 3. Sumar puntos al Dream Team (Tabla dream_team)
            $points_update = "UPDATE dream_team dt JOIN players p ON dt.pid = p.p_id SET dt.points = dt.points + ? WHERE p.p_id = ?";


            // === EJECUTAR EL BUCLE DE ACTUALIZACIÓN ===
            foreach ($_POST['scores'] as $pid => $data) {
                $pid = intval($pid);
                $base_score = floatval($data['base']);
                $gol = isset($data['gol']) && $data['gol'] == 1 ? 1 : 0;
                $asistencia = isset($data['asistencia']) && $data['asistencia'] == 1 ? 1 : 0;
        
                // Lógica de penalti (Formula propietaria)
                $penalty = 1.5;  
                if ($gol == 1) {
                    $penalty = 0.5;  
                } elseif ($asistencia == 1) {
                    $penalty = 1.0; 
                }
                $final_score = $base_score - $penalty;
                $score_date = date('Y-m-d'); // Fecha actual para el Live Score
                
                
                // 1. Escribir en la tabla histórica
                if ($stmt_hist = mysqli_prepare($conn, $hist_update)) {
                    // 'iiddid' -> int, int, double, int, int, double (6 placeholders)
                    mysqli_stmt_bind_param($stmt_hist, "iiddid", $pid, $jornada_id_post, $base_score, $gol, $asistencia, $final_score);
                    if (!mysqli_stmt_execute($stmt_hist)) {
                        throw new Exception("Error al actualizar tabla histórica (PID: $pid): " . mysqli_error($conn));
                    }
                    mysqli_stmt_close($stmt_hist);
                } else {
                    throw new Exception("Error al preparar INSERT/UPDATE histórico: " . mysqli_error($conn));
                }

                
                // 2. Opcional: Actualizar el LIVE score en players SÓLO si es la jornada ACTIVA
                // NOTA: EL LIVE RANKING SOLO DEBE REFLEJAR LA JORNADA ACTIVA
                if ($is_active_post) {
                    if ($stmt_live = mysqli_prepare($conn, $live_update)) {
                        // CORRECCIÓN DE ERROR: El tipo debe ser "ddsi" (4 placeholders: score, final_score, score_date, p_id)
                        mysqli_stmt_bind_param($stmt_live, "ddsi", $base_score, $final_score, $score_date, $pid);
                        if (!mysqli_stmt_execute($stmt_live)) {
                            throw new Exception("Error al actualizar Live Score (PID: $pid): " . mysqli_error($conn));
                        }
                        mysqli_stmt_close($stmt_live);
                    } else {
                        throw new Exception("Error al preparar UPDATE players (Live): " . mysqli_error($conn));
                    }

                    // 3. Sumar puntos al Dream Team SÓLO si es la jornada ACTIVA
                    // Este es el punto más sensible; solo debe correr en el momento de la puntuación
                    if ($stmt_points = mysqli_prepare($conn, $points_update)) {
                        // 'di' -> double, int (2 placeholders: final_score, p_id)
                        mysqli_stmt_bind_param($stmt_points, "di", $final_score, $pid);
                        if (!mysqli_stmt_execute($stmt_points)) {
                            throw new Exception("Error al actualizar dream_team (PID: $pid): " . mysqli_error($conn));
                        }
                        mysqli_stmt_close($stmt_points);
                    } else {
                        throw new Exception("Error al preparar UPDATE dream_team: " . mysqli_error($conn));
                    }
                }
            }
            
            mysqli_commit($conn);
            $success_msg = "¡Scores de la **" . $jornada_nombre_post . "** actualizados y guardados históricamente! " . 
                                ($is_active_post ? "El Live Ranking fue actualizado y los puntos sumados." : "Solo se guardó el historial, el Live Ranking NO fue modificado.");

        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error_msg = "Error en la transacción de actualización: " . $e->getMessage();
        }
    }
}

// =======================================================
// 4. CONSULTA DE JUGADORES Y SCORES HISTÓRICOS (Para llenar la tabla de input)
// =======================================================

$historical_scores = [];
if ($selected_jornada_id) {
    // Consulta la nueva tabla para obtener los scores específicos de la jornada seleccionada
    $hist_query = "SELECT player_id, base_score, gol, asistencia, final_score FROM player_jornada_scores WHERE jornada_id = ?";
    if ($stmt_hist = mysqli_prepare($conn, $hist_query)) {
        mysqli_stmt_bind_param($stmt_hist, "i", $selected_jornada_id);
        mysqli_stmt_execute($stmt_hist);
        $result_hist = mysqli_stmt_get_result($stmt_hist);
        while ($row = mysqli_fetch_assoc($result_hist)) {
            // Indexar el array por player_id para una búsqueda rápida
            $historical_scores[$row['player_id']] = $row;
        }
        mysqli_stmt_close($stmt_hist);
    } else {
        $error_msg .= " Error al preparar la consulta histórica: " . mysqli_error($conn);
    }
}

// Consulta de jugadores (ahora solo se usa para listar)
$query_players = "SELECT * FROM players ORDER BY name";
$players_result = mysqli_query($conn, $query_players);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Admin Scores Jornada - Fantasy Vinotinto N10</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        /* --- Paleta de Colores Vinotinto N10 --- */
        :root {
            --vinotinto: #6f2232;
            --vinotinto-dark: #4b1621;
            --vinotinto-light: #8b3c4f;
            --gold: #ffc72c;
            --white: #ffffff;
            --grey-light: #f8f9fa;
            --danger-light: #f8d7da;
        }

        .text-vinotinto { color: var(--vinotinto) !important; }
        .bg-vinotinto { background-color: var(--vinotinto) !important; }
        .bg-gold { background-color: var(--gold) !important; }
        .text-gold { color: var(--gold) !important; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--vinotinto-dark) 0%, var(--vinotinto) 100%);
            min-height: 100vh;
            color: var(--white);
            padding-bottom: 3rem;
        }

        /* --- Hero Section (Header Admin) --- */
        .admin-header {
            background: rgba(255, 255, 255, 0.1);
            padding: 2rem 1rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            border: 2px solid var(--gold);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5);
        }

        /* --- Alertas de Estado (Mejor UX) --- */
        .alert-custom { 
            border: 2px solid; 
            font-weight: bold; 
            border-radius: 10px; 
            padding: 1.5rem; 
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3); 
        }

        .alert-danger { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }
        .alert-success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
        
        .alert-warning-critical {
            background-color: #8b3c4f; /* Vinotinto Light */
            color: var(--gold);
            border-color: var(--gold);
            border: 3px solid var(--gold);
        }

        /* --- Tabla de Inputs (Admin) --- */
        .table-admin { 
            background-color: var(--white); 
            color: #333; 
            border-radius: 10px; 
            overflow: hidden; 
        }
        
        .table-admin th { 
            background-color: var(--vinotinto); 
            color: var(--gold); 
            font-weight: bold;
        }

        .table-admin input[type="number"], .table-admin input[type="checkbox"] {
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        /* --- Estilos para el Selector de Jornada --- */
        .selector-jornada {
            background: rgba(255, 255, 255, 0.95);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: var(--vinotinto-dark);
            border: 1px solid var(--gold);
        }

        .selector-jornada label {
            font-weight: bold;
            margin-right: 10px;
        }

        .selector-jornada select {
            padding: 8px 12px;
            border: 1px solid var(--vinotinto);
            border-radius: 4px;
            font-size: 1rem;
            max-width: 100%;
        }

        .btn-main-action {
            background-color: var(--gold);
            color: var(--vinotinto-dark);
            font-weight: bold;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.2s;
        }
        
        .btn-main-action:hover {
            background-color: #e0ac00;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    
    <header class="admin-header text-center">
        <h1 class="text-gold"><i class="fas fa-edit me-3"></i> Administración de Puntuaciones</h1>
        <p class="lead">Asigna y corrige los puntajes base de los jugadores para la jornada seleccionada.</p>
        <a href="Home.php" class="btn btn-sm btn-outline-light mt-2"><i class="fas fa-home me-2"></i> Volver al Panel</a>
    </header>

    <?php if ($success_msg): ?>
        <div class="alert alert-success alert-custom text-center" role="alert">
            <h4 class="alert-heading"><i class="fas fa-check-circle me-2"></i> ¡Éxito!</h4>
            <p class="mb-0"><?php echo htmlspecialchars($success_msg); ?></p>
        </div>
    <?php endif; ?>

    <?php if ($error_msg): ?>
        <div class="alert alert-danger alert-custom text-center" role="alert">
            <h4 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i> Error!</h4>
            <p class="mb-0"><?php echo htmlspecialchars($error_msg); ?></p>
        </div>
    <?php endif; ?>
    
    <div class="alert alert-custom alert-warning-critical text-center" role="alert">
        <h4 class="alert-heading"><i class="fas fa-skull-crossbones me-2"></i> ¡ATENCIÓN CRÍTICA!</h4>
        <p class="mb-1">
            Esta acción **GUARDARÁ** el puntaje en el historial de la jornada seleccionada.
            Solo si es la **jornada activa** (`active = 1` en la DB), el Live Ranking (`players` y `dream_team`) será actualizado.
        </p>
        <p class="mb-0">
            **Asegúrate de que la jornada activa sea la correcta antes de guardar.**
        </p>
    </div>
    
    <?php if (!empty($jornadas)): ?>
        <div class="selector-jornada text-center">
            <form method="GET" id="jornada_select_form">
                <label for="jornada_select">Jornada a PUNTUAR/CORREGIR:</label>
                <select name="jornada_id" id="jornada_select" onchange="document.getElementById('jornada_select_form').submit()">
                    <?php foreach ($jornadas as $j): ?>
                        <?php
                            // Se mantiene la limpieza de fechas, como lo solicitó el usuario, para la funcionalidad del selector de corrección
                            $inicio = date('d/M', strtotime($j['fecha_mercado_inicio']));
                            $fin = date('d/M', strtotime($j['fecha_puntuacion_fin']));
                            $opcion_texto = htmlspecialchars($j['nombre']) . " (" . $inicio . " - " . $fin . ")" . 
                                            ($j['active'] == 1 ? ' [ACTIVA]' : '');
                        ?>
                        <option value="<?php echo $j['id']; ?>" 
                            <?php echo ($j['id'] == $selected_jornada_id) ? 'selected' : ''; ?>>
                            <?php echo $opcion_texto; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
            <p class="mt-2 mb-0" style="color: red;">
                Jornada Seleccionada: <strong><?php echo $selected_jornada_nombre; ?></strong>
            </p>
        </div>
        <?php else: ?>
        
        <div class="alert alert-danger alert-custom text-center" role="alert">
            <h4 class="alert-heading"><i class="fas fa-calendar-times me-2"></i> No hay Jornadas</h4>
            <p class="mb-0">No se encontraron jornadas registradas en la base de datos.</p>
        </div>
        
    <?php endif; ?>


    <?php if ($players_result && mysqli_num_rows($players_result) > 0 && $selected_jornada_id): ?>
        
        <form method="POST">
            <input type="hidden" name="jornada_id" value="<?php echo $selected_jornada_id; ?>">

            <div class="table-responsive">
                <table class="table table-striped table-hover table-admin align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Jugador</th>
                            <th>Posición</th>
                            <th>Puntuación Base</th>
                            <th>Gol</th>
                            <th>Asistencia</th>
                            <th>Score Histórico</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($players_result)): 
                            $player_id = $row['p_id'];
                            $score_data = $historical_scores[$player_id] ?? null;
                            
                            // Valores a mostrar en el formulario
                            $base_value = $score_data['base_score'] ?? 0.0;
                            $gol_checked = $score_data['gol'] ?? 0;
                            $asistencia_checked = $score_data['asistencia'] ?? 0;
                            $final_score_display = $score_data['final_score'] ?? 0.00;
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($player_id); ?></td>
                                <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['position']); ?></td>
                                
                                <td>
                                    <input type="number" step="0.5" name="scores[<?php echo $player_id; ?>][base]" 
                                        value="<?php echo number_format($base_value, 1, '.', ''); ?>" 
                                        class="form-control form-control-sm" style="max-width: 80px;" required>
                                </td>
                                
                                <td class="text-center">
                                    <input type="checkbox" name="scores[<?php echo $player_id; ?>][gol]" value="1" 
                                        <?php echo ($gol_checked == 1) ? 'checked' : ''; ?> 
                                        style="transform: scale(1.5);">
                                </td>
                                
                                <td class="text-center">
                                    <input type="checkbox" name="scores[<?php echo $player_id; ?>][asistencia]" value="1" 
                                        <?php echo ($asistencia_checked == 1) ? 'checked' : ''; ?> 
                                        style="transform: scale(1.5);">
                                </td>
                                
                                <td>
                                    <span class="badge bg-vinotinto text-gold p-2">
                                        <?php echo number_format($final_score_display, 2); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="text-center mt-4">
                <button type="submit" name="update_scores" class="btn btn-main-action">
                    <i class="fas fa-bolt me-2"></i> GUARDAR SCORE en JORNADA SELECCIONADA
                </button>
            </div>
        </form>
        
    <?php else: ?>
        
        <div class="alert alert-info alert-custom text-center alert-info-locked" role="alert">
            <h4 class="alert-heading"><i class="fas fa-users me-2 text-gold"></i> Lista de Jugadores Vacía</h4>
            <p class="mb-0">Asegúrate de que la tabla `players` contenga jugadores y que hayas seleccionado una jornada.</p>
        </div>
        
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>