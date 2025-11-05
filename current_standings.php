<?php
// --- INICIO DE DEPURACIÓN CRÍTICA ---
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
// LÓGICA DE CONSULTA (RANKING EN VIVO)
// =======================================================

/* * USAMOS LEFT JOIN y COALESCE para INCLUIR TODOS los usuarios (tabla users)
 * y mostrar 0.00 puntos si no tienen registros en dream_team.
 */
$sql_ranking = "SELECT 
                    u.alias, 
                    COALESCE(SUM(dt.points), 0.00) AS total_puntos_actual
                FROM 
                    users u 
                LEFT JOIN 
                    dream_team dt ON u.email = dt.email
                GROUP BY
                    u.email, u.alias
                ORDER BY
                    total_puntos_actual DESC"; 

$ranking_actual = [];
$error_db = null;

if ($resultado = $conn->query($sql_ranking)) {
    
    while ($fila = $resultado->fetch_assoc()) {
        $ranking_actual[] = $fila;
    }
    
    $resultado->free();
} else {
    $error_db = "Error al ejecutar la consulta SQL: " . $conn->error;
}

$conn->close();

// =======================================================
// PRESENTACIÓN (HTML + PHP) - DISEÑO UX/GAMIFICADO APLICADO
// =======================================================
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Clasificación EN VIVO | FVN10</title>
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

        /* --- Contenedor Principal (Centrado y con Sombra) --- */
        .contenedor-ranking {
            margin-top: 2rem;
            margin-bottom: 2rem;
            max-width: 800px; 
            margin-left: auto; 
            margin-right: auto;
        }
        
        /* --- Header (Hero Section) --- */
        .hero-header-live {
            background: rgba(255, 255, 255, 0.1);
            padding: 2rem 1rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            border: 2px solid var(--gold);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5);
            text-align: center;
        }

        /* --- Tabla de Ranking EN VIVO --- */
        .table-live-ranking {
            background-color: var(--white);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
        }
        .table-live-ranking th {
            background-color: var(--vinotinto);
            color: var(--gold);
            position: sticky; 
            top: 0;
            z-index: 10;
            font-weight: bold;
            font-size: 1.0rem;
        }
        .table-live-ranking tbody tr {
            transition: background-color 0.2s;
        }
        .table-live-ranking tbody tr:hover {
            background-color: #f0f0f0 !important;
        }

        /* Destacar Puntos */
        .puntos-col strong {
            font-size: 1.1em;
            color: var(--vinotinto-dark);
        }

        /* Badges de Posición */
        .pos-badge-live {
            font-size: 0.9em;
            padding: 0.5em 0.75em;
            border-radius: 5px;
            min-width: 50px;
            display: inline-block;
            text-align: center;
            font-weight: bold;
        }
        
        /* Estilo para el líder #1 */
        .leader-row {
            background-color: #fff3cd !important; /* Amarillo suave */
            color: #856404;
            font-weight: bold;
            border-left: 5px solid var(--gold); 
        }
        .leader-row .pos-badge-live {
             background-color: var(--gold) !important;
             color: var(--vinotinto) !important;
             box-shadow: 0 0 8px rgba(255, 199, 44, 0.8);
        }
        
        .default-row .pos-badge-live {
            background-color: var(--vinotinto-light);
            color: var(--white);
        }
    </style>
</head>
<body>

    <div class="container contenedor-ranking">

        <header class="hero-header-live">
            <h1 class="fw-bold text-white mb-2"><i class="fas fa-chart-line text-gold me-2"></i> Clasificación EN VIVO</h1>
            <p class="lead text-white-50">Visualización de puntos acumulados por cada equipo **durante la jornada**.</p>
            <a href="Home.php" class="btn bg-gold text-vinotinto fw-bold mt-3"><i class="fas fa-arrow-left"></i> Volver a Home</a>
        </header>

        <?php if ($error_db): ?>
            <div class="alert alert-danger shadow-sm text-center">
                <i class="fas fa-exclamation-triangle me-2"></i> Error en la base de datos: <?php echo htmlspecialchars($error_db); ?>
            </div>
        <?php elseif (empty($ranking_actual)): ?>
            <div class="alert alert-warning shadow-sm text-center">
                <i class="fas fa-info-circle me-2"></i> Aún no se han registrado usuarios o puntos. ¡Error crítico!
            </div>
        <?php else: ?>
            
            <div class="table-responsive">
                <table class="table table-striped table-hover table-live-ranking">
                    <thead>
                        <tr>
                            <th style="width: 10%;" class="text-center">#</th>
                            <th style="width: 60%;">Equipo/Usuario</th>
                            <th style="width: 30%;" class="text-end">Puntos Actuales</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $posicion = 1;
                        foreach ($ranking_actual as $equipo): 
                            $row_class = ($posicion === 1) ? 'leader-row' : 'default-row';
                            $badge_icon = ($posicion === 1) ? '🏆' : '#';
                        ?>
                        <tr class="<?php echo $row_class; ?>">
                            <td class="text-center">
                                <span class="pos-badge-live <?php echo ($posicion === 1) ? '' : 'bg-secondary'; ?>">
                                    <?php echo $badge_icon . ' ' . $posicion++; ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($equipo['alias']); ?></td>
                            <td class="text-end puntos-col"><strong><?php echo number_format($equipo['total_puntos_actual'], 2); ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="text-center mt-4">
                <p class="text-white-50"><i class="fas fa-bolt me-1 text-gold"></i> La clasificación se actualiza automáticamente con cada cambio de puntuación en el sistema.</p>
            </div>
            
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>