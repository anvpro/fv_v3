<?php
// --- INICIO DE DEPURACIÓN CRÍTICA (Desactivar en producción) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- FIN DE DEPURACIÓN CRÍTICA ---

// =======================================================
// CONFIGURACIÓN Y CONEXIÓN
// =======================================================

// 1. Iniciar la sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Incluir el archivo de conexión real
require_once 'db_connect.php'; 

// 3. Verificar si el usuario está logueado (Restricción de acceso)
if (!isset($_SESSION['usrn'])) {
    header("Location: login.php");
    exit();
}

if (!isset($conn)) {
    die("Error crítico de DB: No se pudo cargar la conexión.");
}

// =======================================================
// 1. OBTENER LISTA DE JORNADAS Y JORNADA SELECCIONADA
// =======================================================

// Consulta a la tabla 'jornadas'
$sql_jornadas = "SELECT id, nombre, fecha_mercado_inicio, fecha_puntuacion_fin FROM jornadas ORDER BY id DESC";
$jornadas = [];
$selected_jornada_id = null;
$selected_jornada_nombre = 'N/A';
$selected_jornada_fechas = 'N/A';
$error_db = null;

// Obtener todas las jornadas
if ($resultado_jornadas = $conn->query($sql_jornadas)) {
    while ($fila = $resultado_jornadas->fetch_assoc()) {
        $jornadas[] = $fila;
    }
} else {
    $error_db = "Error al obtener jornadas: " . $conn->error;
}

// Determinar la jornada seleccionada (por GET o por defecto la última)
if (!empty($jornadas)) {
    $default_jornada = $jornadas[0]; // La más reciente
    
    $selected_jornada_id = $default_jornada['id'];
    
    if (isset($_GET['jornada']) && is_numeric($_GET['jornada'])) {
        $requested_id = intval($_GET['jornada']);
        $found = false;
        foreach ($jornadas as $j) {
            if ($j['id'] == $requested_id) {
                $selected_jornada_id = $j['id'];
                $found = true;
                break;
            }
        }
    } 
    
    // Actualizar nombre y fechas de la jornada seleccionada (sea por GET o por defecto)
    foreach ($jornadas as $j) {
        if ($j['id'] == $selected_jornada_id) {
            // FIX: Usar '?? ""' para prevenir el error 'Passing null' si el nombre es NULL
            $selected_jornada_nombre = htmlspecialchars($j['nombre'] ?? '');
            $selected_jornada_fechas = date('d/m', strtotime($j['fecha_mercado_inicio'])) . ' - ' . date('d/m', strtotime($j['fecha_puntuacion_fin']));
            break;
        }
    }
}


// =======================================================
// 2. LÓGICA DE CONSULTA DE PUNTOS DE JUGADORES
// =======================================================

$historial_jugadores = [];
$scores_found = false;

if ($selected_jornada_id !== null) {
    
    // --- CONSULTA FINAL: Solo Nombre de Jugador y Puntuación ---
    $sql_scores = "SELECT 
        p.name AS nombre_jugador, 
        CAST(ps.final_score AS DECIMAL(10, 2)) AS final_score, 
        CAST(ps.base_score AS DECIMAL(10, 2)) AS base_score
    FROM player_jornada_scores ps 
    INNER JOIN players p ON ps.player_id = p.p_id 
    WHERE ps.jornada_id = ? 
    ORDER BY final_score DESC, p.name ASC"; 

    // Ejecutar consulta preparada
    if ($stmt = $conn->prepare($sql_scores)) {
        $stmt->bind_param("i", $selected_jornada_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        // Almacenar resultados
        while ($fila = $resultado->fetch_assoc()) {
            $historial_jugadores[] = $fila;
        }
        
        if (!empty($historial_jugadores)) {
            $scores_found = true;
        }

        $stmt->close();
    } else {
        $error_db = "Error de preparación SQL: " . $conn->error; 
    }
}

// =======================================================
// INICIO DE HTML / DISPLAY
// =======================================================
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Puntos - Jugadores</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --vinotinto: #6f2232;
            --vinotinto-dark: #4b1621;
            --vinotinto-light: #8b3c4f;
            --gold: #ffc72c;
            --white: #ffffff;
            --grey-light: #f8f9fa;
        }

        .text-vinotinto { color: var(--vinotinto) !important; }
        .bg-vinotinto { background-color: var(--vinotinto) !important; }
        .bg-gold { background-color: var(--gold) !important; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--vinotinto-dark) 0%, var(--vinotinto) 100%);
            min-height: 100vh;
            color: var(--white);
            padding-bottom: 3rem;
        }

        .page-header {
            background: rgba(255, 255, 255, 0.1);
            padding: 1.5rem 1rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            border: 2px solid var(--gold);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5);
        }
        .jornada-selector {
            background-color: var(--vinotinto-light);
            border-radius: 8px;
            padding: 1rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        .jornada-selector select {
            border: 2px solid var(--gold);
            background-color: var(--vinotinto-dark);
            color: var(--white);
            font-weight: bold;
        }

        .table-ranking {
            background-color: var(--white);
            border-radius: 10px;
            overflow: hidden;
        }
        .table-ranking th {
            background-color: var(--vinotinto);
            color: var(--white);
            position: sticky; 
            top: 0;
            z-index: 10;
            font-weight: bold;
            font-size: 1rem;
        }
        .table-ranking td {
            color: var(--vinotinto);
        }
        .table-ranking tbody tr:nth-child(even) {
            background-color: var(--grey-light);
        }
        .table-ranking tbody tr:hover {
            background-color: #ffeeb3;
        }
        
        .score-display {
            font-size: 1.1rem;
            padding: 0.3rem 0.6rem;
            border-radius: 5px;
            min-width: 60px; /* Para mantener alineación */
            display: inline-block;
            text-align: center;
        }
        .score-good { background-color: #d4edda; color: #155724; }
        .score-neutral { background-color: #fff3cd; color: #856404; }
        .score-bad { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container mt-4">

        <header class="text-center page-header">
            <h1 class="fw-bold text-white mb-2"><i class="fas fa-history text-gold"></i> Historial de Puntos por Jugador</h1>
            <p class="lead text-white-50 mb-3">Consulta el desempeño individual de cada jugador del pool en la jornada.</p>
            <a href="Home.php" class="btn bg-gold text-vinotinto fw-bold"><i class="fas fa-arrow-left"></i> Volver al Centro</a>
        </header>
        
        <?php if ($error_db): ?>
            <div class="alert alert-danger text-center shadow-sm">
                <i class="fas fa-exclamation-triangle me-2"></i> Error de Base de Datos: <?php echo htmlspecialchars($error_db ?? ''); ?>
            </div>
        <?php endif; ?>

        <div class="jornada-selector mb-4">
            <form method="GET" class="row g-3 align-items-center">
                <div class="col-md-8">
                    <label for="jornada_select" class="form-label text-white fw-bold mb-0">Seleccionar Jornada:</label>
                    <select name="jornada" id="jornada_select" class="form-select" onchange="this.form.submit()">
                        <?php 
                        if (!empty($jornadas)) {
                            foreach ($jornadas as $jornada) {
                                // FIX: Usar '?? ""' en htmlspecialchars()
                                $jornada_nombre_safe = htmlspecialchars($jornada['nombre'] ?? '');
                                $display_text = $jornada_nombre_safe . 
                                                ' (' . date('d/m', strtotime($jornada['fecha_mercado_inicio'])) . 
                                                ' - ' . date('d/m', strtotime($jornada['fecha_puntuacion_fin'])) . ')';
                                $selected = ($jornada['id'] == $selected_jornada_id) ? 'selected' : '';
                                echo "<option value=\"{$jornada['id']}\" {$selected}>{$display_text}</option>";
                            }
                        } else {
                             echo "<option value=\"\" disabled>No hay jornadas disponibles</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4 text-end">
                    <h5 class="text-gold fw-bold mb-0"><?php echo $selected_jornada_nombre; ?></h5>
                    <p class="text-white-50 small mb-0"><?php echo $selected_jornada_fechas; ?></p>
                </div>
            </form>
        </div>

        <?php if (!$scores_found && $selected_jornada_id !== null): ?>
            <div class="alert alert-info text-center shadow-sm">
                <i class="fas fa-info-circle me-2"></i> **No se encontraron puntuaciones finales** para la jornada seleccionada.
            </div>
        <?php elseif ($scores_found): ?>
            
            <div class="table-responsive">
                <table class="table table-striped table-hover table-ranking shadow-lg">
                    <thead>
                        <tr>
                            <th style="width: 10%;">#</th>
                            <th style="width: 65%;">Jugador</th>
                            <th style="width: 25%;" class="text-center">Puntuación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $posicion_rank = 1;
                        foreach ($historial_jugadores as $jugador): 
                            
                            // FIX: Usar '?? ""' en htmlspecialchars() para prevenir errores
                            $nombre_jugador_safe = htmlspecialchars($jugador['nombre_jugador'] ?? '');

                            $score = floatval($jugador['final_score']);
                            
                            // *** MODIFICACIÓN CLAVE: Ocultar puntuaciones negativas o cero ***
                            $score_display = ($score <= 0) ? 0.00 : $score;
                            // ***************************************************************

                            if ($score_display >= 7.0) {
                                $score_class = 'score-good';
                            } elseif ($score_display > 0.0) {
                                $score_class = 'score-neutral';
                            } else {
                                $score_class = 'score-bad';
                            }
                        ?>
                        <tr>
                            <td><?php echo $posicion_rank++; ?></td>
                            <td class="fw-bold"><?php echo $nombre_jugador_safe; ?></td>
                            <td class="text-center">
                                <span class="badge score-display <?php echo $score_class; ?>">
                                    <?php echo number_format($score_display, 2); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="alert alert-info mt-4 shadow-sm text-center small">
                <i class="fas fa-asterisk me-1"></i> La tabla está ordenada por **Puntuación Real** (de mayor a menor) de la jornada seleccionada.
            </div>

        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const select = document.getElementById('jornada_select');
            const urlParams = new URLSearchParams(window.location.search);
            const selectedJornada = urlParams.get('jornada');
            
            if (selectedJornada) {
                select.value = selectedJornada;
            }
        });
    </script>
</body>
</html>