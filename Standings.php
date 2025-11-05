<?php
session_start();
include 'db_connect.php'; // Asegúrate de que db_connect.php NO use mysqli_close() al final

// Check si logueado; si no, redirige
if (!isset($_SESSION['usrn'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['usrn'];

// --- FUNCIONES DE AYUDA (Para no repetir código) ---

function execute_prepared_query($conn, $query, $types, ...$params) {
    if ($stmt = mysqli_prepare($conn, $query)) {
        // Enlaza parámetros solo si existen
        if ($params) {
            // Aseguramos que los parámetros se pasan correctamente como array
            // El tipo 'i' (integer) debe ser usado para IDs
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    } else {
        // Logueo de error para depuración si la consulta falla
        error_log("SQL Prepare Error: " . mysqli_error($conn) . " | Query: " . $query);
        return false;
    }
}

// --- 0. CONFIGURACIÓN DE PAGINACIÓN ---
$limit = 50; 
$page = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
$offset = ($page - 1) * $limit;

// --- 1. LÓGICA DE SUSCRIPCIÓN Y PRE-FETCH DE DATOS ---

// 1.1 Obtener Alias y ID del Usuario Actual (¡ID es la clave!)
$user_data_query = "SELECT id, alias FROM users WHERE email = ?";
$user_data_result = execute_prepared_query($conn, $user_data_query, "s", $email);

$user_data = $user_data_result ? mysqli_fetch_assoc($user_data_result) : ['id' => null, 'alias' => null];
$current_user_id = $user_data['id']; // ID del usuario actual (CLAVE para la unión con puntuaciones)
$current_user_alias = (isset($user_data['alias']) && $user_data['alias'] !== null && $user_data['alias'] !== '') ? $user_data['alias'] : $email; 

// 1.2 Check admin (Usa el email como siempre)
$admin_query = "SELECT is_admin FROM users WHERE email = ?";
$admin_result = execute_prepared_query($conn, $admin_query, "s", $email);
$is_admin = $admin_result ? (mysqli_fetch_assoc($admin_result)['is_admin'] == 1) : false;

// 1.3 Check suscripción (Usa el email como siempre)
$active_query = "SELECT subscription_active FROM users WHERE email = ?";
$active_result = execute_prepared_query($conn, $active_query, "s", $email);
$subscription_active = $active_result ? mysqli_fetch_assoc($active_result)['subscription_active'] : null;
$is_active = $subscription_active && strtotime($subscription_active) > time();


// --- LÓGICA DE SUSCRIPCIÓN Y DATA FETCH ---
$subscription_error_msg = '';
$standings_result = null;
$my_position = 'N/A';
$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

if (!$is_active && !$is_admin) {
    $subscription_error_msg = '¡Alerta! Para acceder a la tabla de posiciones (Standings) y el ranking de mánagers, necesitas tener una suscripción activa. Puedes renovarla o activarla por solo $1/mes. <a href="payments.php" class="alert-link">Ir a la página de Suscripción</a>';
    
} else if ($current_user_id === null) {
    // Manejar caso donde el ID no se pudo obtener
    $subscription_error_msg = 'Error de usuario. No se encontró su ID en la base de datos.';
    
} else {
    // --- 2. CÁLCULO DE POSICIÓN GLOBAL DEL USUARIO (Mi Posición) ---
    // CORRECCIÓN CLAVE: Usamos CAST para asegurar que los puntos sean sumados como números
    // Simplificación de la query: ya no necesita join a 'users' si usamos el ID directamente
    $my_scores_query = "
        SELECT 
            COALESCE(SUM(CAST(p.puntos_totales AS DECIMAL(10, 2))), 0) AS my_total_points,  /* SUMAMOS y casteamos */
            COALESCE(MAX(CAST(p.puntos_totales AS DECIMAL(10, 2))), 0) AS my_max_jornada    /* Máximo puntaje y casteamos */
        FROM puntuaciones p
        WHERE p.id_usuario = ? 
    ";
    // El ID es un entero (i)
    $my_scores_result = execute_prepared_query($conn, $my_scores_query, "i", $current_user_id);
    $my_scores = $my_scores_result ? mysqli_fetch_assoc($my_scores_result) : ['my_total_points' => 0, 'my_max_jornada' => 0];

    $my_total_points = $my_scores['my_total_points'];
    $my_max_jornada = $my_scores['my_max_jornada'];

    // Consulta para contar cuántos usuarios tienen un mejor ranking
    // CORRECCIÓN CLAVE: Usamos CAST en la subconsulta para el ranking total
    $rank_query = "
        SELECT COUNT(DISTINCT sub.user_email) AS higher_rank_count
        FROM (
            SELECT 
                u.email AS user_email, 
                COALESCE(SUM(CAST(p.puntos_totales AS DECIMAL(10, 2))), 0) AS total_points, /* CORREGIDO con CAST */
                COALESCE(MAX(CAST(p.puntos_totales AS DECIMAL(10, 2))), 0) AS max_jornada /* CORREGIDO con CAST */
            FROM users u
            LEFT JOIN puntuaciones p ON u.id = p.id_usuario 
            GROUP BY u.email
        ) AS sub
        WHERE 
            sub.total_points > {$my_total_points} 
            OR (
                sub.total_points = {$my_total_points} 
                AND sub.max_jornada > {$my_max_jornada}
            )
    ";
    // Usamos mysqli_query() aquí porque las variables ya están sanitizadas al ser resultados numéricos de la DB
    $rank_result = mysqli_query($conn, $rank_query);
    $higher_rank_count = $rank_result ? mysqli_fetch_assoc($rank_result)['higher_rank_count'] : 0;
    $my_position = $higher_rank_count + 1;


    // --- 3. CONSULTA PRINCIPAL CON PAGINACIÓN ---
    // CORRECCIÓN CLAVE: Usamos CAST para asegurar la suma en el ranking principal
    $standings_query = "
        SELECT 
            u.email AS user_email, 
            COALESCE(u.alias, u.email) AS display_name, 
            COALESCE(SUM(CAST(p.puntos_totales AS DECIMAL(10, 2))), 0) AS total_points, /* CORREGIDO con CAST */
            COALESCE(MAX(CAST(p.puntos_totales AS DECIMAL(10, 2))), 0) AS max_jornada  /* CORREGIDO con CAST */
        FROM users u
        LEFT JOIN puntuaciones p ON u.id = p.id_usuario 
        GROUP BY u.email, display_name 
        ORDER BY total_points DESC, max_jornada DESC
        LIMIT ? OFFSET ?
    ";
    // Tipos: "ii" (integer, integer)
    $standings_result = execute_prepared_query($conn, $standings_query, "ii", $limit, $offset);

    // Consulta para el total de usuarios (simple)
    $count_query = "SELECT COUNT(u.email) AS total_users FROM users u";
    $count_result = mysqli_query($conn, $count_query);
    $total_users = $count_result ? mysqli_fetch_assoc($count_result)['total_users'] : 0;
    $total_pages = ceil($total_users / $limit);
}

// =======================================================
// INICIO DE HTML / DISPLAY (No modificado, es el mismo que funciona)
// =======================================================
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Standings - Fantasy Vinotinto N10</title>
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
        .bg-vinotinto-light { background-color: var(--vinotinto-light) !important; }
        .text-gold { color: var(--gold) !important; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--vinotinto-dark) 0%, var(--vinotinto) 100%);
            min-height: 100vh;
            color: var(--white);
            padding-bottom: 3rem;
        }

        /* --- Hero Section (Header) --- */
        .hero-header {
            background: rgba(255, 255, 255, 0.1);
            padding: 2rem 1rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            border: 2px solid var(--gold);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5);
        }

        /* --- Mi Posición Card (Gamificado) --- */
        .manager-rank-card {
            background: var(--vinotinto-light);
            border: 3px solid var(--gold);
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease;
        }
        .manager-rank-card:hover {
            transform: translateY(-5px);
        }
        .rank-number {
            font-size: 3rem;
            font-weight: 900;
            color: var(--gold);
            text-shadow: 2px 2px 4px var(--vinotinto);
            line-height: 1;
        }
        .rank-title {
            color: var(--white);
            font-weight: bold;
            font-size: 1.25rem;
        }

        /* --- Tabla de Ranking --- */
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
        .table-ranking tbody tr {
            transition: background-color 0.2s;
            cursor: pointer;
        }
        .table-ranking tbody tr:hover {
            background-color: var(--grey-light) !important;
        }

        /* Colores condicionales */
        .table-ranking .table-primary td { /* Mi Usuario */
            background-color: #d6eaff !important; /* Azul más claro */
            color: var(--vinotinto);
            font-weight: bold;
            border-left: 5px solid #0d6efd; 
        }
        .table-ranking .table-warning td { /* Líder #1 */
            background-color: #fff3cd !important; 
            color: #856404;
            font-weight: bold;
            border-left: 5px solid var(--gold); 
        }

        /* Badges de Posición */
        .pos-badge {
            font-size: 1em;
            padding: 0.5em 0.75em;
            border-radius: 5px;
            min-width: 50px;
            display: inline-block;
            text-align: center;
        }
        .table-ranking .table-warning .pos-badge {
             background-color: var(--gold) !important;
             color: var(--vinotinto) !important;
             box-shadow: 0 0 8px rgba(255, 199, 44, 0.8);
        }
        .table-ranking .table-primary .pos-badge {
             background-color: #0d6efd !important;
             color: var(--white) !important;
        }
        .table-ranking .bg-secondary {
             background-color: var(--vinotinto-light) !important;
        }

        /* Alerta de Suscripción (UX) */
        .alert-subscription {
            background-color: var(--vinotinto);
            color: var(--white);
            border: 3px solid var(--gold);
            border-radius: 10px;
        }
        .alert-link {
            color: var(--gold) !important;
            font-weight: bold;
            text-decoration: underline;
        }
        
        /* Paginación gamificada */
        .pagination .page-link {
            color: var(--vinotinto);
            border: 1px solid var(--vinotinto-light);
            transition: all 0.3s;
        }
        .pagination .page-link:hover {
            background-color: var(--gold);
            color: var(--vinotinto);
        }
        .pagination .page-item.active .page-link {
            background-color: var(--vinotinto);
            border-color: var(--gold);
            color: var(--white);
        }
        .pagination .page-item.disabled .page-link {
            color: #ccc;
            background-color: #eee;
        }
    </style>
</head>
<body>
    <div class="container mt-4">

        <header class="text-center hero-header">
            <h1 class="fw-bold text-white mb-2"><i class="fas fa-trophy text-gold"></i> Ranking de Mánagers</h1>
            <p class="lead text-white-50 mb-0">¡La competencia por la gloria y el mejor puntaje total!</p>
            <a href="Home.php" class="btn bg-gold text-vinotinto fw-bold mt-3"><i class="fas fa-arrow-left"></i> Volver al Centro</a>
        </header>

        <?php if ($subscription_error_msg): ?>
            <div class="alert alert-danger shadow-lg alert-subscription" role="alert">
                <i class="fas fa-exclamation-triangle me-2 text-gold"></i>
                <span class="fw-bold">ACCESO RESTRINGIDO:</span> <?php echo $subscription_error_msg; ?>
            </div>
            
        <?php else: ?>
            
            <div class="card manager-rank-card mb-4 shadow-lg text-white">
                <div class="card-body py-3 px-4">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <p class="mb-0 text-gold fw-bold small">Tu Ranking Actual</p>
                            <h5 class="rank-title mb-0"><i class="fas fa-user-circle me-1"></i> Mánager: <?php echo htmlspecialchars($current_user_alias); ?></h5>
                        </div>
                        <div class="col-6 text-end">
                            <span class="rank-number text-end">#<?php echo $my_position; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-light border border-secondary text-center small mb-4 text-dark shadow-sm">
                Mostrando managers **<?php echo $offset + 1; ?>** a **<?php echo min($offset + $limit, $total_users); ?>** de un total de **<?php echo $total_users; ?>**
            </div>
            
            <?php if ($standings_result && mysqli_num_rows($standings_result) > 0): ?>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-ranking shadow-lg">
                        <thead>
                            <tr>
                                <th style="width: 10%;">Pos.</th>
                                <th style="width: 45%;">Mánager (Alias)</th>
                                <th style="width: 25%;" class="text-center">Total Points</th>
                                <th style="width: 20%;" class="text-center">Desempate (Max Score)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $position = $offset + 1;
                            while ($row = mysqli_fetch_assoc($standings_result)): 
                                $is_current_user = ($row['user_email'] == $email);
                                $is_winner = ($position == 1);
                                
                                // Determinar clases y contenido del badge
                                if ($is_winner && $page == 1) {
                                    $row_class = 'table-warning fw-bold';
                                    $badge_icon = '🥇';
                                    $badge_class = 'bg-warning text-dark';
                                } elseif ($is_current_user) {
                                    $row_class = 'table-primary';
                                    $badge_icon = '<i class="fas fa-star"></i>';
                                    $badge_class = 'bg-primary';
                                } else {
                                    $row_class = '';
                                    $badge_icon = '';
                                    $badge_class = 'bg-secondary';
                                }
                                
                                $alias_display = htmlspecialchars($row['display_name']); 
                                $desempate = $row['max_jornada'] ?? 0;
                            ?>
                                <tr class="<?php echo $row_class; ?>">
                                    <td>
                                        <span class="badge pos-badge <?php echo $badge_class; ?>">
                                            <?php echo $badge_icon . ' ' . $position; ?>
                                        </span>
                                    </td>
                                    <td class="text-dark"><?php echo $alias_display; ?></td>
                                    <td class="text-center fw-bold text-success"><?php echo number_format($row['total_points'], 2); ?></td>
                                    <td class="text-center text-muted"><?php echo number_format($desempate, 2); ?></td>
                                </tr>
                            <?php $position++; endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Navegación del Ranking" class="mt-4">
                        <ul class="pagination justify-content-center flex-wrap">
                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?p=<?php echo max(1, $page - 1); ?>" aria-label="Previous">
                                    <span aria-hidden="true"><i class="fas fa-chevron-left"></i> Anterior</span>
                                </a>
                            </li>

                            <?php
                            $max_links = 5;
                            $start_page = max(1, $page - floor($max_links / 2));
                            $end_page = min($total_pages, $start_page + $max_links - 1);
                            
                            if ($end_page - $start_page < $max_links - 1) {
                                $start_page = max(1, $end_page - $max_links + 1);
                            }

                            for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?p=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?p=<?php echo min($total_pages, $page + 1); ?>" aria-label="Next">
                                    <span aria-hidden="true">Siguiente <i class="fas fa-chevron-right"></i></span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
                
                <?php if ($my_position == 1): ?>
                    <div class="alert alert-success mt-4 shadow-lg text-center fw-bold">
                        <h4 class="alert-heading"><i class="fas fa-crown me-2"></i> ¡Líder de la Liga! 👑</h4>
                        <p class="mb-0">Estás actualmente en la cima de la clasificación. ¡Mantén la corona!</p>
                    </div>
                <?php elseif ($my_position > $limit): // Muestra el mensaje solo si no está en la primera página ?>
                    <div class="alert alert-info mt-4 shadow-sm text-center small">
                        Tu posición es la **#<?php echo $my_position; ?>**. Usa la navegación para explorar la tabla.
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <p class="alert alert-warning shadow-sm text-center">
                    <i class="fas fa-info-circle me-2"></i> No hay managers que mostrar en esta página o aún no hay datos de puntos.
                </p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>