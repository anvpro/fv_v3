<?php
session_start();
include 'db_connect.php';  // Tu conexión DB

// Check si logueado; si no, redirige a register
if (!isset($_SESSION['usrn'])) {
    header("Location: index.php");
    exit();
}

$email = $_SESSION['usrn'];

// === INICIO: Obtener Alias del Usuario ===
$alias_query = "SELECT alias FROM users WHERE email = '$email'";
$alias_result = mysqli_query($conn, $alias_query);
$user_data = mysqli_fetch_assoc($alias_result);
// Usamos el alias para la bienvenida, o el email si por alguna razón falla (fallback)
$alias = $user_data['alias'] ?? $email; 
// === FIN: Obtener Alias del Usuario ===


// === INICIO: Verificación de Admin ===
$admin_query = "SELECT is_admin FROM users WHERE email = '$email'";
$admin_result = mysqli_query($conn, $admin_query);
// Definir $is_admin a false si la consulta falla o no hay filas, o al valor de la columna.
$is_admin = ($admin_result && mysqli_num_rows($admin_result) > 0) ? mysqli_fetch_assoc($admin_result)['is_admin'] == 1 : false; 
// === FIN: Verificación de Admin ===


// Logout handler (opcional, si POST logout)
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Query simple para mostrar total players (opcional, para demo)
$total_players_query = "SELECT COUNT(*) as total FROM players";
$total_result = mysqli_query($conn, $total_players_query);
$total_players = mysqli_fetch_assoc($total_result)['total'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Fantasy Vinotinto N10</title>
    <link rel="stylesheet" href="Style.css">
    <style>
        /* Paleta Corporativa (Coherente con index.php, register.php y login.php) */
        :root {
            --vinotinto: #6f2232;
            --oro: #ffc72c;
            --cian: #49faee;
            --naranja: #ff9900;
            --blanco: #ffffff;
            --gris-claro: #f8f9fa;
            --gris-oscuro: #6c757d;
        }

        /* Reset y Base (Coherente) */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--vinotinto);
            background: linear-gradient(135deg, var(--vinotinto) 0%, #8b2e3b 100%);
            min-height: 100vh;
            padding: 1rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            animation: fadeInUp 0.8s ease-out;
        }

        /* Hero/Welcome Section */
        .hero {
            text-align: center;
            color: var(--blanco);
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        .hero h1 {
            font-size: clamp(1.5rem, 4vw, 2.5rem);
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .hero .lead {
            font-size: clamp(1rem, 2.5vw, 1.25rem);
            opacity: 0.95;
        }

        /* Cards Grid */
        .cards-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        @media (min-width: 768px) {
            /* Mantener el diseño de 3 columnas para tabletas/escritorio */
            .cards-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        .card {
            background: var(--blanco);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-align: center;
            height: 100%;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }

        .card h5 {
            color: var(--vinotinto);
            font-size: 1.3rem;
            margin-bottom: 0.75rem;
        }

        .card p {
            color: var(--gris-oscuro);
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }

        /* Admin Section */
        .admin-section {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 2px solid var(--oro);
        }

        .admin-section h3 {
            color: var(--oro);
            text-align: center;
            margin-bottom: 1.5rem;
            font-size: clamp(1.2rem, 3vw, 1.8rem);
        }

        .admin-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        @media (min-width: 768px) {
            .admin-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        /* Botones (Coherente) */
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            width: 100%;
            margin-top: 0.5rem;
        }

        .btn-primary {
            background: var(--vinotinto);
            color: var(--blanco);
        }

        .btn-primary:hover {
            background: #5a1b28;
            transform: scale(1.02);
        }

        .btn-success {
            background: var(--oro);
            color: var(--vinotinto);
        }

        .btn-success:hover {
            background: #e6b323;
            transform: scale(1.02);
        }

        .btn-info {
            background: var(--cian);
            color: var(--vinotinto);
        }

        .btn-info:hover {
            background: #3ad2d5;
            transform: scale(1.02);
        }

        .btn-danger {
            background: #dc3545;
            color: var(--blanco);
        }

        .btn-danger:hover {
            background: #c82333;
            transform: scale(1.02);
        }

        .btn-warning {
            background: var(--naranja);
            color: var(--vinotinto);
        }

        .btn-warning:hover {
            background: #e68900;
            transform: scale(1.02);
        }

        .btn-outline-secondary {
            background: transparent;
            color: var(--blanco);
            border: 2px solid var(--blanco);
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }

        .btn-outline-secondary:hover {
            background: var(--blanco);
            color: var(--vinotinto);
            transform: scale(1.02);
        }

        /* Footer Section */
        .footer {
            text-align: center;
            color: var(--blanco);
            padding: 2rem 0;
            border-top: 1px solid rgba(255,255,255,0.1);
            opacity: 0.9;
        }

        .footer p {
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }

        /* Responsive */
        @media (max-width: 767px) {
            body {
                padding: 0.5rem;
            }

            .hero {
                padding: 1rem 0;
            }
        }

        /* Animaciones (Coherente) */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card {
            animation: fadeInUp 0.6s ease-out;
        }

        .card:nth-child(2) { animation-delay: 0.1s; }
        .card:nth-child(3) { animation-delay: 0.2s; }
        .card:nth-child(4) { animation-delay: 0.3s; } /* Nuevo delay */
    </style>
</head>
<body>
    <div class="container">
        <div class="hero">
            <h1>Bienvenido a Fantasy Vinotinto N10, <?php echo htmlspecialchars($alias); ?>!</h1> 
            <p class="lead">Dashboard principal. Elige una opción:</p>
        </div>
        
        <div class="cards-grid">
            <div class="card">
                <h5>⚽ Mi Plantilla</h5>
                <p>Arma, modifica y vende jugadores para tu once ideal.</p>
                <a href="Fantasy-League.php" class="btn btn-primary">Ir a la Liga</a>
            </div>
            
            <div class="card">
                <h5>🏆 Standings</h5>
                <p>Ve el ranking mensual de todos los mánagers (Total Acumulado).</p>
                <a href="Standings.php" class="btn btn-success">Ver Ranking</a>
            </div>
            
            <div class="card">
                <h5>📅 Historial de Jornadas</h5>
                <p>Consulta el ranking de puntos por cada jornada ya finalizada.</p>
                <a href="historial_puntos.php" class="btn btn-warning">Ver Historial</a>
            </div>
            <div class="card">
                <h5>💳 Suscripción/Pagos</h5>
                <p>Gestiona tu pago mensual y revisa tu estado.</p>
                <a href="payments.php" class="btn btn-info">Ir a Pagos</a>
            </div>
        </div>

        <?php if ($is_admin): ?>
            <div class="admin-section">
                <h3>Panel de Administración</h3>
                <div class="admin-grid">
                    <div class="card">
                        <h5>📈 Admin Scores Jornada</h5>
                        <p>Ingresa desempeño y aplica fórmula propietaria.</p>
                        <a href="admin_scores.php" class="btn btn-danger">Input Scores</a>
                    </div>

                    <div class="card">
                        <h5>➕ Agregar Jugador</h5>
                        <p>Agrega nuevos venezolanos al pool de la liga.</p>
                        <a href="addplayer.php" class="btn btn-warning">Agregar</a>
                    </div>
                    
                    <div class="card">
                        <h5>👥 Plantillas de Usuarios</h5>
                        <p>Visualiza el Dream Team actual de cualquier usuario.</p>
                        <a href="admin_view_teams.php" class="btn btn-success">Ver Equipos</a>
                    </div>
                </div>
            </div>

        <?php endif; ?>
        
        <div class="footer">
            <p>Pool actual: <?php echo $total_players; ?> jugadores disponibles.</p>
            <form method="POST" style="display:inline;">
                <button type="submit" name="logout" class="btn btn-outline-secondary">Cerrar Sesión</button>
            </form>
        </div>
    </div>

    <script>
        // JS mínimo para interacciones suaves (coherente)
        document.addEventListener('DOMContentLoaded', function() {
            // Staggered animations para cards
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
        });
    </script>
</body>
</html>