<?php
session_start();
include 'db_connect.php';  // Tu conexión DB

// Check si logueado; si no, redirige
if (!isset($_SESSION['usrn'])) {
    header("Location: index.php");
    exit();
}

$email = $_SESSION['usrn'];
$success_msg = '';
$error_msg = '';
$warning_msg = ''; // <-- Inicialización para advertencias de plantilla

// Check admin
$admin_query = "SELECT is_admin FROM users WHERE email = '$email'";
$admin_result = mysqli_query($conn, $admin_query);
$is_admin = mysqli_fetch_assoc($admin_result)['is_admin'] == 1;

// Check suscripción
$active_query = "SELECT subscription_active FROM users WHERE email = '$email'";
$active_result = mysqli_query($conn, $active_query);
$subscription_active = mysqli_fetch_assoc($active_result)['subscription_active'];
$is_active = $subscription_active && strtotime($subscription_active) > time();

date_default_timezone_set('America/Caracas');  // VET
$current_time = date('Y-m-d H:i:s');

// Query jornada active
$jornada_query = "SELECT * FROM jornadas WHERE active = 1 AND '$current_time' BETWEEN fecha_mercado_inicio AND fecha_puntuacion_fin";
$jornada_result = mysqli_query($conn, $jornada_query);
$is_locked = mysqli_num_rows($jornada_result) > 0;  // Si en ventana puntuación, lock

if ($is_locked && ($is_active || $is_admin)) {
    $error_msg = '¡Mercado cerrado! Jornada en puntuación (Fri-Mon). Consulta scores.';
}

if (!$is_active && !$is_admin && (isset($_POST['addtosquad']) || isset($_POST['update_scores']) || isset($_POST['sell_player']))) {
    $error_msg = '¡Equipo congelado! Renueva tu suscripción en payments.php ($1/mes). Solo puedes ver.';
} elseif (!$is_active && !$is_admin) {
    $error_msg = '¡Suscripción requerida para editar! Paga $1/mes para armar plantilla. <a href="payments.php" class="text-white fw-bold">Ir a Pagos</a>';
}

// Límite de plantilla (e.g., 18 jugadores max; custom para reglamento)
$max_squad = 11; // Plantilla Titular

// Formación actual del user (default 1: 4-3-3)
$formacion_query = "SELECT f.id as formacion_id, f.nombre as formacion_nombre FROM users u LEFT JOIN formaciones f ON u.formacion_id = f.id WHERE u.email = '$email'";
$formacion_result = mysqli_query($conn, $formacion_query);
$formacion_row = mysqli_fetch_assoc($formacion_result);
$formacion_id = $formacion_row ? ($formacion_row['formacion_id'] ?? 1) : 1; // Asegura que no sea null
$formacion_nombre = $formacion_row['formacion_nombre'] ?? '4-3-3'; // Para el mensaje de advertencia

$squad_query = "SELECT COUNT(*) as squad_size FROM dream_team WHERE email = '$email'";
$squad_result_size = mysqli_query($conn, $squad_query);
$squad_size = mysqli_fetch_assoc($squad_result_size)['squad_size'];


// Query para obtener el capital actual del usuario
$capital_query = "SELECT COALESCE(capital, 60000000) as current_capital FROM users WHERE email = '$email'";
$capital_result = mysqli_query($conn, $capital_query);
$current_capital = mysqli_fetch_assoc($capital_result)['current_capital'];

// ==========================================================
// NUEVO: Lógica para calcular y formatear el Valor del Equipo
// ==========================================================
$team_value_query = "
    SELECT COALESCE(SUM(p.price), 0) as total_team_value
    FROM dream_team dt
    JOIN players p ON dt.pid = p.p_id
    WHERE dt.email = '$email'
";
$team_value_result = mysqli_query($conn, $team_value_query);
$total_team_value = mysqli_fetch_assoc($team_value_result)['total_team_value'];

// Formato para el valor del equipo (Valor completo + 🍊)
$team_value_formatted = number_format($total_team_value, 0, ',', '.') . ' 🍊';
// ==========================================================


// ==========================================================
// LÓGICA DE FILTRADO Y PERSISTENCIA
// ==========================================================
$allowed_positions = ['Todos', 'Portero', 'Defensa', 'Mediocampista', 'Atacante']; 

// Usamos $_REQUEST para tomar de POST (envío de formulario/acción) o GET (redirección)
$search = isset($_REQUEST['search']) ? mysqli_real_escape_string($conn, trim($_REQUEST['search'])) : '';
$filter_position = isset($_REQUEST['position_filter']) && in_array($_REQUEST['position_filter'], $allowed_positions) ? $_REQUEST['position_filter'] : 'Todos';

// Crear los parámetros GET para la redirección
$filter_params = http_build_query([
    'position_filter' => $filter_position,
    'search' => $search
]);
// ==========================================================


// Handler para agregar a plantilla (MODIFICADO: Redirección PRG)
if (isset($_POST['addtosquad']) && $squad_size < $max_squad && ($is_active || $is_admin) && !$is_locked) { 
    $pid = intval($_POST['pid']);
    
    // Check si ya existe en squad
    $check_query = "SELECT COUNT(*) as count FROM dream_team WHERE email = '$email' AND pid = '$pid'";
    $check_result = mysqli_query($conn, $check_query);
    $exists = mysqli_fetch_assoc($check_result)['count'] > 0;
    
    if ($exists) {
        $error_msg = '¡Este jugador ya está en tu plantilla!';
    } else {
        // Valida posición para formación
        $posicion_query = "SELECT position, price FROM players WHERE p_id = '$pid'"; 
        $posicion_result = mysqli_query($conn, $posicion_query);
        $player_data = mysqli_fetch_assoc($posicion_result);
        $posicion = $player_data['position'];
        $player_price = $player_data['price']; 
        
        // Verifica slots (usa $formacion_id actual)
        $slot_query = "SELECT cantidad FROM slots_formacion WHERE formacion_id = '$formacion_id' AND posicion = '$posicion'";
        $slot_result = mysqli_query($conn, $slot_query);
        $max_pos = mysqli_fetch_assoc($slot_result)['cantidad'] ?? 0;

        $current_pos_count_query = "SELECT COUNT(dt.pid) as current_count FROM dream_team dt JOIN players p ON dt.pid = p.p_id WHERE dt.email = '$email' AND p.position = '$posicion'";
        $current_pos_count_result = mysqli_query($conn, $current_pos_count_query);
        $current_pos_count = mysqli_fetch_assoc($current_pos_count_result)['current_count'];
        
        if ($current_pos_count >= $max_pos) {
            $error_msg = "¡Posición inválida para tu formación ($posicion)! Slots agotados para esa posición ($max_pos max).";
        } else {
            // Asigna slot (elige primer ID de slot disponible para esa posición y formación)
            $slot_assign = "SELECT id FROM slots_formacion WHERE formacion_id = '$formacion_id' AND posicion = '$posicion' LIMIT 1";
            $slot_result_assign = mysqli_query($conn, $slot_assign);
            $slot_id = mysqli_fetch_assoc($slot_result_assign)['id'];

            $query = "INSERT INTO dream_team (email, pid, slot_id) VALUES ('$email', '$pid', '$slot_id')";
            
            if (mysqli_query($conn, $query)) {
                
                // === DEDUCCIÓN DE CAPITAL === 
                $capital_deduct_query = "UPDATE users SET capital = capital - '$player_price' WHERE email = '$email'";
                mysqli_query($conn, $capital_deduct_query); 
                
                // Redirección PRG para mantener filtros y evitar doble envío
                header("Location: Fantasy-League.php?$filter_params&action_msg=added");
                exit();

            } else {
                $error_msg = 'Error al agregar: ' . mysqli_error($conn);
            }
        }
    }
} elseif (isset($_POST['addtosquad'])) {
    if (!$is_active && !$is_admin) {
        $error_msg = "¡Suscripción o admin requerida para agregar!";
    } elseif ($is_locked) {
        $error_msg = "¡Mercado cerrado! No puedes hacer traspasos ahora.";
    } else {
        $error_msg = "Plantilla llena ($max_squad max).";
    }
}

// Handler para cambio de formación (MODIFICADO: Pasa filtros)
if (isset($_POST['change_formacion']) && $is_admin) {
    $new_formacion = intval($_POST['formacion_id']);
    $update_form = "UPDATE users SET formacion_id = '$new_formacion' WHERE email = '$email'";
    if (mysqli_query($conn, $update_form)) {
        $success_msg = 'Formación cambiada con éxito.';
        // Forzar recarga completa, manteniendo filtros.
        header("Location: Fantasy-League.php?$filter_params");
        exit();
    } else {
         $error_msg = 'Error al cambiar formación: ' . mysqli_error($conn);
    }
}


// Handler para vender jugador (MODIFICADO: Redirección PRG)
if (isset($_POST['sell_player']) && !$is_locked && ($is_active || $is_admin)) {
    $sell_pid = intval($_POST['sell_pid']);
    
    // 1. Obtener precio del jugador
    $price_query = "SELECT price FROM players WHERE p_id = '$sell_pid'";
    $price_result = mysqli_query($conn, $price_query);
    
    if (mysqli_num_rows($price_result) > 0) {
        $sell_price = mysqli_fetch_assoc($price_result)['price'];

        // 2. Quitar de squad
        $delete_query = "DELETE FROM dream_team WHERE email = '$email' AND pid = '$sell_pid'";
        mysqli_query($conn, $delete_query);

        // 3. Suma 100% a capital
        $capital_update = "UPDATE users SET capital = COALESCE(capital, 60000000) + '$sell_price' WHERE email = '$email'";
        mysqli_query($conn, $capital_update);

        // Redirección PRG para mantener filtros y evitar doble envío
        header("Location: Fantasy-League.php?$filter_params&action_msg=sold");
        exit();

    } else {
        $error_msg = "Error: Jugador no encontrado para la venta.";
    }
}

// === Recuperar mensajes después de la redirección ===
if (isset($_GET['action_msg'])) {
    if ($_GET['action_msg'] === 'added') {
        $success_msg = '¡Jugador agregado a tu plantilla!';
    } elseif ($_GET['action_msg'] === 'sold') {
        // Recargar el capital y el precio vendido para mostrar el mensaje preciso (opcional)
        $capital_result_refresh = mysqli_query($conn, $capital_query);
        $current_capital = mysqli_fetch_assoc($capital_result_refresh)['current_capital'];
        $success_msg = "¡Jugador vendido! Capital actualizado.";
    }
}


// ==========================================================
// Lógica de Ordenamiento y Separación para la Plantilla 
// ==========================================================
$position_order_map = [
    'Portero' => 1, 
    'Defensa' => 2,
    'Mediocampista' => 3,
    'Atacante' => 4 
];
$position_order_cases = "";
foreach ($position_order_map as $pos => $order) {
    $position_order_cases .= " WHEN p.position = '$pos' THEN $order";
}

$squad_query_full = "
    SELECT 
        p.p_id, p.name, p.position, p.price, dt.points 
    FROM dream_team dt 
    JOIN players p ON dt.pid = p.p_id 
    WHERE dt.email = '$email'
    ORDER BY 
        CASE 
            $position_order_cases 
            ELSE 99 
        END, p.name ASC
";
$squad_result = mysqli_query($conn, $squad_query_full);

$total_points = 0;
$squad_data = [];
$gk_squad = []; 
$def_squad = []; 
$mid_squad = []; 
$att_squad = []; 

if ($squad_result) {
    // Resetear puntero de resultado si ya fue consumido por la cuenta de puntos
    mysqli_data_seek($squad_result, 0); 

    while ($row = mysqli_fetch_assoc($squad_result)) {
        // === MODIFICACIÓN PARA ASEGURAR MÍNIMO DE 0.00 PUNTOS (UX FIX) ===
        $player_points = floatval($row['points']);
        // Asegura que los puntos mostrados y sumados sean 0 si el valor DB es negativo (e.g., -1.50)
        $points_to_display = max(0.00, $player_points); 

        $total_points += $points_to_display;
        $row['points'] = $points_to_display; // Sobreescribimos el valor para la visualización en render_player_card()
        // === FIN MODIFICACIÓN ===
        
        $squad_data[] = $row; 
        
        switch ($row['position']) {
            case 'Portero': 
                $gk_squad[] = $row;
                break;
            case 'Defensa':
                $def_squad[] = $row;
                break;
            case 'Mediocampista':
                $mid_squad[] = $row;
                break;
            case 'Atacante': 
                $att_squad[] = $row;
                break;
        }
    }
} 
// ==========================================================

// ==========================================================
// VALIDACIÓN DE PLANTILLA DESPUÉS DE CARGAR EL EQUIPO: SOBRAN Y FALTAN
// ==========================================================
$positions_to_check = ['Portero', 'Defensa', 'Mediocampista', 'Atacante'];
$sobrantes = [];
$faltantes = []; 

if ($squad_size >= 0) { 
    
    // 1. Obtener los límites de la formación actual
    $form_limits_query = "SELECT posicion, cantidad FROM slots_formacion WHERE formacion_id = '$formacion_id'";
    $form_limits_result = mysqli_query($conn, $form_limits_query);
    $form_limits = [];
    while ($row = mysqli_fetch_assoc($form_limits_result)) {
        $form_limits[$row['posicion']] = $row['cantidad'];
    }

    // 2. Obtener el conteo de jugadores actuales por posición
    $current_counts_query = "
        SELECT p.position, COUNT(dt.pid) as current_count
        FROM dream_team dt 
        JOIN players p ON dt.pid = p.p_id 
        WHERE dt.email = '$email' 
        GROUP BY p.position
    ";
    $current_counts_result = mysqli_query($conn, $current_counts_query);
    $current_counts = [];
    while ($row = mysqli_fetch_assoc($current_counts_result)) {
        $current_counts[$row['position']] = $row['current_count'];
    }

    // 3. Comparar conteos con límites (Sobran y Faltan)
    foreach ($positions_to_check as $pos) {
        $limite = $form_limits[$pos] ?? 0;
        $actual = $current_counts[$pos] ?? 0;

        // Se usa plural o singular para un mensaje más amigable
        $nombre_pos = ($pos == 'Portero') ? 'Porteros' : $pos . 's';

        // LÓGICA DE SOBRANTES (Vender)
        if ($actual > $limite) {
            $sobra = $actual - $limite;
            $sobrantes[] = "vender $sobra de $nombre_pos"; 
        }

        // LÓGICA DE FALTANTES (Comprar) 
        if ($actual < $limite) {
            $falta = $limite - $actual;
            $faltantes[] = "comprar $falta de $nombre_pos"; 
        }
    }

    // 4. Generar el mensaje de advertencia combinado
    if (!empty($sobrantes) || !empty($faltantes)) {
        
        // ******************************************************
        // LÓGICA MODIFICADA: Mensaje de bienvenida si está vacío
        // ******************************************************
        if ($squad_size == 0) {
             $warning_msg = '¡Tu plantilla está vacía! Arma tu formación y elige tu once ahora para empezar a sumar puntos.';
        } 
        // ******************************************************
        
        else { // Mensaje detallado para plantillas NO vacías
            $sobra_text = '';
            $falta_text = '';
            
            if (!empty($sobrantes)) {
                $sobra_text = 'Debes ' . implode(', ', $sobrantes);
            }
            
            if (!empty($faltantes)) {
                $falta_text = 'Te falta ' . implode(', ', $faltantes);
            }
            
            $separator = (!empty($sobra_text) && !empty($falta_text)) ? ' y también ' : '';

            $warning_msg = '¡ADVERTENCIA! Tu plantilla actual no cumple con la formación **' . $formacion_nombre . '**. ' . $sobra_text . $separator . $falta_text . '.';
        }
    }
}
// ==========================================================


// ==========================================================
// LÓGICA DE FILTRADO PARA EL POOL GLOBAL (Aplica la lógica del bloque anterior)
// ==========================================================
// Las variables $allowed_positions, $search y $filter_position ya están definidas arriba.
$where_clauses = [];

if ($filter_position !== 'Todos') {
    $where_clauses[] = "position = '$filter_position'";
}

if ($search) {
    // Busca por nombre
    $where_clauses[] = "name LIKE '%$search%'";
}

// Combinar las cláusulas WHERE
$where = '';
if (!empty($where_clauses)) {
    $where = "WHERE " . implode(" AND ", $where_clauses);
}

// Query pool jugadores (con filtros aplicados)
$query_players = "SELECT * FROM players $where ORDER BY price DESC";
$players_result = mysqli_query($conn, $query_players);

// ==========================================================


// Función helper para renderizar un jugador como card (USADA SOLO EN LA PLANTILLA)
function render_player_card($player, $is_locked, $is_active, $is_admin, $filter_position, $search) {
    
    // --- Lógica funcional sin cambios ---
    $sell_disabled = ($is_locked || (!$is_active && !$is_admin)) ? 'disabled title="Mercado cerrado o suscripción requerida"' : '';
    $name_short = htmlspecialchars($player['name']);
    
    if (strlen($name_short) > 18) {
        $name_short = substr($name_short, 0, 15) . '...';
    }
    // --- FIN Lógica funcional sin cambios ---
    
    // --- CAMBIO DE DISEÑO (Gamificado/Compacto) ---
    // MODIFICACIÓN: Mostrar el valor completo del jugador con formato y emoji 🍊
    $price_formatted = number_format($player['price'], 0, ',', '.') . ' 🍊';

    return '
        <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3 px-1">
            <div class="card player-card team-card shadow-sm border-0"> 
                <div class="card-body p-2 text-dark">
                    <p class="mb-0 fw-bold small text-truncate text-vinotinto">' . $name_short . '</p>
                    <small class="text-secondary d-block">' . htmlspecialchars($player['position']) . '</small>
                    <div class="info-group">
                        <span class="badge bg-gold text-vinotinto fw-bold"><i class="fas fa-coins"></i> ' . $price_formatted . '</span> 
                        <span class="badge bg-vinotinto-light text-white fw-bold"><i class="fas fa-star"></i> ' . number_format($player['points'], 2) . '</span> 
                    </div>
                    
                    <form method="POST" onsubmit="return confirm(\'¿Vender ' . htmlspecialchars($player['name']) . '?\');" class="mt-2">
                        <input type="hidden" name="sell_pid" value="' . $player['p_id'] . '">
                        <input type="hidden" name="position_filter" value="' . htmlspecialchars($filter_position) . '">
                        <input type="hidden" name="search" value="' . htmlspecialchars($search) . '">
                        <button type="submit" name="sell_player" class="btn btn-sm btn-danger w-100 btn-sell" ' . $sell_disabled . '><i class="fas fa-hand-holding-usd"></i> Vender</button>
                    </form>
                </div>
            </div>
        </div>
    ';
    // --- FIN CAMBIO DE DISEÑO ---
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fantasy League - Fantasy Vinotinto N10</title>
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
            --field-green: #38761d;
            --field-border: #6aa84f;
        }

        .text-vinotinto { color: var(--vinotinto) !important; }
        .bg-vinotinto { background-color: var(--vinotinto) !important; }
        .bg-gold { background-color: var(--gold) !important; }
        .bg-vinotinto-light { background-color: var(--vinotinto-light) !important; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--vinotinto-dark) 0%, var(--vinotinto) 100%);
            min-height: 100vh;
            color: var(--white);
            padding: 1rem;
        }

        /* --- Header / Hero Section (Gamificado) --- */
        .hero-section {
            background: rgba(255, 255, 255, 0.1);
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            border: 2px solid var(--gold);
        }

        /* ESTILO PARA CAPITAL (GOLD) */
        .capital-badge {
            background: var(--gold);
            color: var(--vinotinto);
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: bold;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            display: inline-flex;
            align-items: center;
        }
        .capital-badge i {
            margin-right: 0.5rem;
            font-size: 1.25rem;
        }
        
        /* ESTILO PARA VALOR DEL EQUIPO (VINOTINTO) (NUEVO) */
        .team-value-badge {
            background: var(--vinotinto-light); /* Color contrastante */
            color: var(--white);
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: bold;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            display: inline-flex;
            align-items: center;
            border: 2px solid var(--gold); /* Borde para realzar */
        }
        .team-value-badge i {
            margin-right: 0.5rem;
            font-size: 1.25rem;
        }

        /* --- Alertas GAMIFICADAS --- */
        
        /* Estilo base */
        .alert-game {
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            opacity: 0;
            animation: fadeInDown 0.5s ease-out forwards;
        }
        .alert-game i {
            font-size: 1.5rem;
            margin-right: 1rem;
        }
        
        /* Success (Agregado/Vendido) */
        .alert-game-success {
            background-color: #d4edda;
            color: #155724;
            border: 3px solid var(--field-border);
        }
        .alert-game-success i { color: #155724; }
        
        /* Error (Bloqueo/Suscripción) */
        .alert-game-danger { 
            background-color: #f8d7da; 
            color: #721c24; 
            border: 3px solid var(--vinotinto); 
        }
        .alert-game-danger i { color: #721c24; }

        /* Warning (Faltan/Sobran jugadores) */
        .alert-game-warning { 
            background-color: #fff3cd; 
            color: #856404; 
            border: 3px solid var(--gold); 
        }
        .alert-game-warning i { color: #856404; }

        /* Animación de entrada */
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* --- Cancha (Plantilla) --- */
        .field {
            background-color: var(--field-green); 
            border: 5px solid var(--field-border);
            padding: 15px 5px; 
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
            margin-top: 1.5rem;
        }
        .position-row h5 {
            background-color: var(--vinotinto);
            color: var(--white);
            display: inline-block;
            padding: 0.3rem 1rem;
            border-radius: 5px;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }
        .team-card {
            background-color: var(--white);
            transition: transform 0.2s;
        }
        .team-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.2);
        }

        /* --- Player Card Styling (Gamified/Compact) --- */
        .player-card {
            border: 1px solid var(--gris-claro);
        }
        .player-card .info-group {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin: 0.25rem 0;
            flex-wrap: wrap; /* Añadido para mejor responsive en precios largos */
        }
        .player-card .badge {
            font-size: 0.65rem;
            padding: 0.4em 0.6em;
            line-height: 1;
        }
        .player-card .btn-primary, .player-card .btn-danger {
            font-size: 0.75rem;
            padding: 0.3rem 0.5rem;
            border-radius: 8px;
        }
        .player-card small {
            font-size: 0.7rem;
        }

        /* --- Pool Filters --- */
        .pool-filters {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .form-select, .form-control {
            border: 2px solid var(--vinotinto-light);
            color: var(--vinotinto);
        }
        .form-label {
            color: var(--vinotinto);
            font-weight: bold;
        }

        /* --- Pool Grid (Responsive) --- */
        .pool-grid .col-lg-3 { /* Secciones en el pool (2-3 columnas en desktop) */
            padding-right: 5px;
            padding-left: 5px;
        }

    </style>
</head>
<body>
    <div class="container mt-4">
        
        <div class="hero-section text-center">
            <a href="Home.php" class="btn btn-sm btn-outline-light mb-3 float-start"><i class="fas fa-home"></i> Inicio</a>
            <h1 class="text-white fw-bold mb-2">Fantasy League: Mercado de Fichajes</h1>
            <p class="lead text-white-50 mb-3">Arma tu once ideal y gestiona tu plantilla.</p>
            
            <div class="d-flex justify-content-center flex-wrap gap-3 mx-auto mb-4">
                <div class="capital-badge">
                    <i class="fas fa-hand-holding-usd"></i> Capital: <?php echo number_format($current_capital, 0, ',', '.'); ?> 🍊
                </div>
                
                <div class="team-value-badge">
                    <i class="fas fa-dollar-sign"></i> Valor del Equipo: <?php echo $team_value_formatted; ?>
                </div>
            </div>

            <div class="d-flex justify-content-center align-items-center flex-wrap gap-3">
                <span class="badge bg-vinotinto p-2 fs-6"><i class="fas fa-users"></i> Plantilla: <?php echo $squad_size; ?>/<?php echo $max_squad; ?></span>
                <span class="badge bg-vinotinto p-2 fs-6"><i class="fas fa-chart-line"></i> Total Pts: <?php echo number_format($total_points, 2); ?></span>
            </div>
        </div>

        <?php if ($error_msg): ?>
            <div class="alert alert-game alert-game-danger" role="alert">
                <i class="fas fa-times-circle"></i> <div><?php echo $error_msg; ?></div>
            </div>
        <?php endif; ?>
        <?php if ($success_msg): ?>
            <div class="alert alert-game alert-game-success" role="alert">
                <i class="fas fa-trophy"></i> <div><?php echo $success_msg; ?></div>
            </div>
        <?php endif; ?>
        <?php if ($warning_msg): // Mensaje de advertencia para ajustes de plantilla ?>
            <div class="alert alert-game alert-game-warning" role="alert">
                <i class="fas fa-exclamation-triangle"></i> <div><?php echo $warning_msg; ?></div>
            </div>
        <?php endif; ?>
        <div class="d-flex justify-content-center my-4">
            <form method="POST" class="d-flex align-items-center gap-3">
                <label for="formacion_id" class="form-label text-white fw-bold m-0">Formación Actual: </label>
                <select name="formacion_id" id="formacion_id" class="form-select w-auto" onchange="this.form.submit();" <?php echo !$is_admin ? 'disabled title="Solo Admin puede cambiar la formación actualmente"' : ''; ?>>
                    <?php
                    $forms_query = "SELECT id, nombre FROM formaciones";
                    $forms_result = mysqli_query($conn, $forms_query);
                    if ($forms_result && mysqli_num_rows($forms_result) > 0) {
                        while ($form = mysqli_fetch_assoc($forms_result)): ?>
                            <option value="<?php echo $form['id']; ?>" <?php echo $form['id'] == $formacion_id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($form['nombre']); ?>
                            </option>
                        <?php endwhile;
                    } else {
                        echo '<option value="1">4-3-3 (Default)</option>';
                    }
                    ?>
                </select>
                <input type="hidden" name="change_formacion" value="1">
                <input type="hidden" name="position_filter" value="<?php echo htmlspecialchars($filter_position); ?>">
                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
            </form>
        </div>


        <h3 class="mt-5 text-center fw-bold text-white">Tu Once Ideal - <?php echo htmlspecialchars($formacion_nombre); ?></h3>
        
        <?php if ($squad_size > 0): ?>
            <div class="field text-white">
                <div class="position-row text-center">
                    <h5 class="mx-auto">Portero (<?php echo count($gk_squad); ?>/<?php echo $form_limits['Portero'] ?? 1; ?>)</h5>
                    <div class="row justify-content-center">
                        <?php 
                        foreach ($gk_squad as $player) {
                            echo render_player_card($player, $is_locked, $is_active, $is_admin, $filter_position, $search);
                        }
                        if (count($gk_squad) < ($form_limits['Portero'] ?? 0)) {
                             echo '<p class="text-center text-warning small w-100">Slot Vacío (Faltan ' . (($form_limits['Portero'] ?? 0) - count($gk_squad)) . ')</p>';
                        }
                        ?>
                    </div>
                </div>

                <div class="position-row text-center">
                    <h5 class="mx-auto">Defensas (<?php echo count($def_squad); ?>/<?php echo $form_limits['Defensa'] ?? 4; ?>)</h5>
                    <div class="row justify-content-center">
                        <?php 
                        foreach ($def_squad as $player) {
                            echo render_player_card($player, $is_locked, $is_active, $is_admin, $filter_position, $search);
                        }
                        if (count($def_squad) < ($form_limits['Defensa'] ?? 0)) {
                             echo '<p class="text-center text-warning small w-100">Slot Vacío (Faltan ' . (($form_limits['Defensa'] ?? 0) - count($def_squad)) . ')</p>';
                        }
                        ?>
                    </div>
                </div>

                <div class="position-row text-center">
                    <h5 class="mx-auto">Mediocampistas (<?php echo count($mid_squad); ?>/<?php echo $form_limits['Mediocampista'] ?? 3; ?>)</h5>
                    <div class="row justify-content-center">
                        <?php 
                        foreach ($mid_squad as $player) {
                            echo render_player_card($player, $is_locked, $is_active, $is_admin, $filter_position, $search);
                        }
                        if (count($mid_squad) < ($form_limits['Mediocampista'] ?? 0)) {
                             echo '<p class="text-center text-warning small w-100">Slot Vacío (Faltan ' . (($form_limits['Mediocampista'] ?? 0) - count($mid_squad)) . ')</p>';
                        }
                        ?>
                    </div>
                </div>

                <div class="position-row text-center">
                    <h5 class="mx-auto">Atacantes (<?php echo count($att_squad); ?>/<?php echo $form_limits['Atacante'] ?? 3; ?>)</h5>
                    <div class="row justify-content-center">
                        <?php 
                        foreach ($att_squad as $player) {
                            echo render_player_card($player, $is_locked, $is_active, $is_admin, $filter_position, $search);
                        }
                        if (count($att_squad) < ($form_limits['Atacante'] ?? 0)) {
                             echo '<p class="text-center text-warning small w-100">Slot Vacío (Faltan ' . (($form_limits['Atacante'] ?? 0) - count($att_squad)) . ')</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-game alert-game-warning text-center">
                <i class="fas fa-futbol"></i> 
                <div>Tu plantilla está vacía. ¡Agrega jugadores del pool!</div>
            </div>
        <?php endif; ?>
        
        <h3 class="mt-5 mb-4 text-center fw-bold text-white">Pool Global de Jugadores</h3>
        
        <form method="POST" class="mb-4 pool-filters">
            <div class="row align-items-end">
                <div class="col-md-4 mb-3">
                    <label for="position_filter" class="form-label">Filtrar por Posición</label>
                    <select name="position_filter" id="position_filter" class="form-select">
                        <?php 
                        foreach ($allowed_positions as $pos) {
                            $selected = ($pos == $filter_position) ? 'selected' : '';
                            echo "<option value=\"$pos\" $selected>" . htmlspecialchars($pos) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="search_text" class="form-label">Buscar por Nombre</label>
                    <input type="text" name="search" id="search_text" class="form-control" placeholder="Buscar jugador por nombre..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-2 mb-3 d-grid">
                    <button type="submit" class="btn bg-vinotinto text-white fw-bold"><i class="fas fa-filter"></i> Aplicar</button>
                </div>
            </div>
        </form>
        <div class="row pool-grid justify-content-center">
            <?php 
            if ($players_result) {
                 mysqli_data_seek($players_result, 0); 
            }
            if (mysqli_num_rows($players_result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($players_result)): ?>
                    <?php
                    // Lógica funcional de Check y Disabling (Intacta)
                    $check_squad = "SELECT COUNT(*) as in_squad FROM dream_team WHERE email = '$email' AND pid = '" . $row['p_id'] . "'";
                    $squad_check = mysqli_query($conn, $check_squad);
                    $in_squad = mysqli_fetch_assoc($squad_check)['in_squad'] > 0;
                    
                    $can_afford = $current_capital >= $row['price'];
                    
                    $disable_button = (($is_locked) || (!$is_active && !$is_admin) || $in_squad || $squad_size >= $max_squad || !$can_afford);
                    $title_text = '';
                    if (!$can_afford) {
                        $title_text = 'Capital insuficiente';
                    } elseif ($is_locked) {
                         $title_text = 'Mercado cerrado';
                    } elseif (!$is_active && !$is_admin) {
                        $title_text = 'Suscripción requerida';
                    } elseif ($squad_size >= $max_squad) {
                        $title_text = 'Plantilla llena';
                    }
                    
                    // MODIFICACIÓN: Mostrar el valor completo del jugador con formato y emoji 🍊
                    $price_F = number_format($row['price'], 0, ',', '.') . ' 🍊';
                    
                    $score_F = number_format($row['score'], 2);
                    $name_short = htmlspecialchars($row['name']);
                    if (strlen($name_short) > 18) {
                        $name_short = substr($name_short, 0, 15) . '...';
                    }
                    // --- FIN Lógica funcional de Check y Disabling (Intacta) ---
                    ?>
                    
                    <div class="col-6 col-sm-4 col-md-3 col-lg-3 mb-3">
                        <div class="card player-card pool-card shadow-lg border-0">
                            <div class="card-body p-2 text-center text-dark">
                                <p class="mb-0 fw-bold small text-truncate text-vinotinto"><?php echo $name_short; ?></p>
                                <small class="text-secondary d-block"><?php echo htmlspecialchars($row['position']); ?></small>
                                
                                <div class="info-group">
                                    <span class="badge bg-gold text-vinotinto fw-bold"><i class="fas fa-coins"></i> <?php echo $price_F; ?></span> 
                                    <span class="badge bg-vinotinto-light text-white fw-bold"><i class="fas fa-star"></i> <?php echo $score_F; ?></span> 
                                </div>
                                
                                <form method="POST" class="mt-2">
                                    <input type="hidden" name="pid" value="<?php echo $row['p_id']; ?>">
                                    <input type="hidden" name="position_filter" value="<?php echo htmlspecialchars($filter_position); ?>">
                                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                                    
                                    <?php if ($in_squad): ?>
                                        <small class="text-muted fw-bold d-block mt-1"><i class="fas fa-check-circle text-success"></i> ¡En Plantilla!</small>
                                    <?php else: ?>
                                        <button type="submit" name="addtosquad" class="btn btn-sm bg-vinotinto text-white w-100 fw-bold btn-add" 
                                                <?php echo $disable_button ? 'disabled title="' . htmlspecialchars($title_text) . '"' : ''; ?>>
                                                <i class="fas fa-plus-circle"></i> Agregar
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
            <?php else: ?>
                <p class="alert alert-info text-center mt-3">No hay jugadores que coincidan con los filtros aplicados. Intenta una búsqueda diferente.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JS para aplicar una sutil animación de carga a las cards (Mejora UX)
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.player-card');
            cards.forEach((card, index) => {
                card.style.opacity = 0;
                setTimeout(() => {
                    card.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                    card.style.opacity = 1;
                    card.style.transform = 'translateY(0)';
                }, 100 + index * 50); // Pequeño delay escalonado
            });
        });
    </script>
</body>
</html>