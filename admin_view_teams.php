<?php
// --- INICIO DE DEPURACIÓN CRÍTICA (Desactivar en producción) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- FIN DE DEPURACIÓN CRÍTICA ---

// =======================================================
// CONFIGURACIÓN Y CONEXIÓN
// =======================================================
session_start();
require_once 'db_connect.php'; // Tu archivo de conexión

// =======================================================
// 1. VERIFICACIÓN DE ACCESO DE ADMINISTRADOR
// =======================================================
if (!isset($_SESSION['usrn'])) {
    header("Location: login.php");
    exit();
}
$email = $_SESSION['usrn'];

// Consulta para verificar si es administrador
$admin_query = "SELECT is_admin FROM users WHERE email = ?";
if ($stmt = $conn->prepare($admin_query)) {
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $admin_result = $stmt->get_result();
    $is_admin = $admin_result->fetch_assoc()['is_admin'] == 1;
    $stmt->close();
} else {
    // Manejar error de conexión/consulta
    $is_admin = false;
}

if (!$is_admin) {
    die('Acceso denegado. Solo administradores.');
}

// =======================================================
// 2. LÓGICA DE VISTAS (LISTADO O DETALLE)
// =======================================================

$view_detail = isset($_GET['user_email']);
$target_email = $view_detail ? htmlspecialchars($_GET['user_email']) : '';
$target_alias = '';
$user_plantilla = [];
$error_db = '';

if ($view_detail) {
    // --- LÓGICA DE DETALLE DE PLANTILLA ---
    
    // 1. Obtener Alias del usuario objetivo
    $alias_query = "SELECT alias FROM users WHERE email = ?";
    if ($stmt = $conn->prepare($alias_query)) {
        $stmt->bind_param("s", $target_email);
        $stmt->execute();
        $alias_result = $stmt->get_result();
        if ($alias_data = $alias_result->fetch_assoc()) {
            $target_alias = htmlspecialchars($alias_data['alias']);
        } else {
            $error_db = "Usuario con email '{$target_email}' no encontrado.";
            $view_detail = false; // Vuelve a la vista de lista si hay error.
        }
        $stmt->close();
    } else {
        $error_db = "Error al obtener alias: " . $conn->error;
    }

    if ($target_alias && !$error_db) {
        // 2. Obtener Plantilla del usuario objetivo
        // CORRECCIÓN CLAVE: Usamos sf.posicion para el nombre del slot (según la estructura DB)
        $sql_plantilla = "SELECT 
            p.name AS player_name, 
            p.posicion AS player_position, 
            dt.points AS current_points,
            sf.posicion AS slot_name
        FROM dream_team dt
        INNER JOIN players p ON dt.pid = p.p_id
        INNER JOIN slots_formacion sf ON dt.slot_id = sf.id
        WHERE dt.email = ?
        ORDER BY sf.id ASC";

        if ($stmt = $conn->prepare($sql_plantilla)) {
            $stmt->bind_param("s", $target_email);
            $stmt->execute();
            $user_plantilla = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        } else {
            // Este error ya debería haberse resuelto al usar sf.posicion
            $error_db = "Error al obtener la plantilla: " . $conn->error;
        }
    }

} else {
    // --- LÓGICA DE LISTADO DE USUARIOS (Paginación simplificada con búsqueda) ---
    
    $search_term = isset($_GET['search']) ? '%' . trim($_GET['search']) . '%' : '%';
    $search_input = isset($_GET['search']) ? htmlspecialchars(trim($_GET['search'])) : '';
    
    // Base de la consulta
    $sql_users = "SELECT id, email, alias 
                  FROM users 
                  WHERE alias LIKE ? OR email LIKE ?
                  ORDER BY alias ASC"; 
    
    $list_users = [];
    if ($stmt = $conn->prepare($sql_users)) {
        // Usamos el mismo término de búsqueda para alias y email
        $stmt->bind_param("ss", $search_term, $search_term);
        $stmt->execute();
        $list_users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        $error_db = "Error al listar usuarios: " . $conn->error;
    }
}


// =======================================================
// 3. INICIO DE HTML / DISPLAY
// =======================================================
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Admin - Ver Plantillas</title>
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
        .container { padding-top: 2rem; }
        .page-header {
            background: rgba(255, 255, 255, 0.1);
            padding: 1.5rem 1rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            border: 2px solid var(--gold);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5);
        }

        .table-custom {
            background-color: var(--white);
            border-radius: 10px;
            overflow: hidden;
        }
        .table-custom th {
            background-color: var(--vinotinto);
            color: var(--white);
            font-weight: bold;
        }
        .table-custom td {
            color: var(--vinotinto-dark);
        }
        .table-custom tbody tr:nth-child(even) {
            background-color: var(--grey-light);
        }
        .table-custom tbody tr:hover {
            background-color: #ffeeb3;
        }
        
        /* Estilos para la tabla de plantilla (reutilizados del fantasy-league.php) */
        .player-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px dashed #ccc;
        }
        .player-row:last-child {
            border-bottom: none;
        }
        .player-name-col {
            flex-grow: 1;
            font-weight: bold;
            color: var(--vinotinto);
        }
        .player-pos-col {
            width: 80px;
            text-align: center;
        }
        .player-points-col {
            width: 80px;
            text-align: right;
            font-weight: bold;
        }

        .pos-badge {
            min-width: 40px;
            display: inline-block;
            text-align: center;
            font-weight: bold;
            color: var(--white);
            padding: 0.35em 0.65em;
            border-radius: 0.25rem;
            font-size: 0.75em;
        }
        .bg-del { background-color: #0d6efd !important; } /* Azul */
        .bg-mc { background-color: #198754 !important; } /* Verde */
        .bg-dfc { background-color: #dc3545 !important; } /* Rojo */
        .bg-por { background-color: #6c757d !important; } /* Gris */
        
        .score-display {
            font-size: 1rem;
            padding: 0.3rem 0.6rem;
            border-radius: 5px;
            min-width: 60px;
            display: inline-block;
            text-align: center;
        }
        .score-good { background-color: #d4edda; color: #155724; }
        .score-neutral { background-color: #fff3cd; color: #856404; }
        .score-bad { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container">

        <header class="text-center page-header">
            <h1 class="fw-bold text-white mb-2"><i class="fas fa-users-cog text-gold"></i> Panel Admin: Plantillas</h1>
            <p class="lead text-white-50 mb-3">Acceso y soporte a los Dream Teams de los usuarios.</p>
            <a href="Home.php" class="btn bg-gold text-vinotinto fw-bold"><i class="fas fa-arrow-left"></i> Volver al Admin</a>
        </header>

        <?php if ($error_db): ?>
            <div class="alert alert-danger text-center shadow-sm">
                <i class="fas fa-exclamation-triangle me-2"></i> Error de Base de Datos: <?php echo $error_db; ?>
            </div>
        <?php endif; ?>

        <?php if ($view_detail): // --- VISTA DE DETALLE DE PLANTILLA --- ?>
            
            <div class="card shadow-lg mb-4">
                <div class="card-header bg-vinotinto text-white">
                    <h4 class="mb-0">Plantilla de **<?php echo $target_alias; ?>**</h4>
                    <p class="mb-0 small text-gold"><?php echo $target_email; ?></p>
                </div>
                <div class="card-body bg-white">
                    <a href="admin_view_teams.php" class="btn btn-sm btn-outline-secondary mb-3"><i class="fas fa-chevron-left"></i> Volver al listado</a>

                    <?php if (empty($user_plantilla)): ?>
                        <div class="alert alert-warning text-center">
                            <i class="fas fa-info-circle me-2"></i> El usuario **<?php echo $target_alias; ?>** aún no tiene jugadores en su plantilla (Dream Team vacío).
                        </div>
                    <?php else: ?>
                        
                        <div class="list-group list-group-flush">
                            <div class="player-row text-vinotinto fw-bold border-bottom">
                                <div class="player-name-col">Posición (Slot) y Jugador</div>
                                <div class="player-pos-col">Posición</div>
                                <div class="player-points-col">Puntos</div>
                            </div>
                            
                            <?php foreach ($user_plantilla as $player): 
                                $pos_text = htmlspecialchars($player['player_position'] ?? '');
                                $slot_name = htmlspecialchars($player['slot_name'] ?? '');
                                $player_name = htmlspecialchars($player['player_name'] ?? '');
                                $points = floatval($player['current_points'] ?? 0);

                                // Lógica de colores de posición (Basada en la posición del JUGADOR, no del slot)
                                $pos_class = '';
                                switch (strtolower($pos_text)) {
                                    case 'por': $pos_class = 'bg-por'; break;
                                    case 'dfc':
                                    case 'dfi':
                                    case 'dfd': $pos_class = 'bg-dfc'; break;
                                    case 'mc':
                                    case 'mdi':
                                    case 'mdd':
                                    case 'mo': $pos_class = 'bg-mc'; break;
                                    case 'del': $pos_class = 'bg-del'; break;
                                    default: $pos_class = 'bg-secondary'; break;
                                }

                                // Lógica de color de puntuación
                                if ($points > 0) {
                                    $score_class = 'score-good';
                                } elseif ($points < 0) {
                                    $score_class = 'score-bad';
                                } else {
                                    $score_class = 'score-neutral';
                                }
                            ?>
                            <div class="player-row">
                                <div class="player-name-col">
                                    <span class="badge <?php echo $pos_class; ?> me-2"><?php echo $slot_name; ?></span> 
                                    <?php echo $player_name; ?>
                                </div>
                                <div class="player-pos-col">
                                    <span class="pos-badge <?php echo $pos_class; ?>">
                                        <?php echo $pos_text; ?>
                                    </span>
                                </div>
                                <div class="player-points-col">
                                    <span class="badge score-display <?php echo $score_class; ?>">
                                        <?php echo number_format($points, 2); ?>
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                    <?php endif; ?>
                </div>
            </div>

        <?php else: // --- VISTA DE LISTADO DE USUARIOS --- ?>

            <div class="row mb-4">
                <div class="col-12">
                    <form method="GET" class="d-flex">
                        <input type="text" name="search" class="form-control me-2" placeholder="Buscar por Alias o Email" value="<?php echo $search_input; ?>">
                        <button type="submit" class="btn bg-gold text-vinotinto fw-bold"><i class="fas fa-search"></i> Buscar</button>
                        <?php if ($search_input): ?>
                            <a href="admin_view_teams.php" class="btn btn-outline-light ms-2"><i class="fas fa-times"></i> Limpiar</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-striped table-hover table-custom shadow-lg">
                    <thead>
                        <tr>
                            <th style="width: 5%;">ID</th>
                            <th style="width: 30%;">Alias</th>
                            <th style="width: 45%;">Email</th>
                            <th style="width: 20%;" class="text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($list_users)): ?>
                            <?php foreach ($list_users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td><?php echo htmlspecialchars($user['alias']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td class="text-center">
                                    <a href="admin_view_teams.php?user_email=<?php echo urlencode($user['email']); ?>" 
                                       class="btn btn-sm bg-vinotinto text-white">
                                        <i class="fas fa-eye"></i> Ver Plantilla
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">No se encontraron usuarios o managers.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="alert alert-info mt-4 shadow-sm text-center small">
                <i class="fas fa-exclamation-triangle me-1"></i> **Nota:** El listado actual muestra todos los usuarios. La búsqueda por alias o email es sensible a mayúsculas/minúsculas.
            </div>

        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>