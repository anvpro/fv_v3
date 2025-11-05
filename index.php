<?php
session_start();
include 'db_connect.php';

// Si logueado, redirige a Home privado
if (isset($_SESSION['usrn'])) {
    header("Location: Home.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fantasy Vinotinto N10 - Bienvenido</title>
    <link rel="stylesheet" href="Style.css">
    <style>
        /* Paleta Corporativa */
        :root {
            --vinotinto: #6f2232;
            --oro: #ffc72c;
            --cian: #49faee;
            --naranja: #ff9900;
            --blanco: #ffffff;
            --gris-claro: #f8f9fa;
            --gris-oscuro: #6c757d;
        }

        /* Reset y Base */
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
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        /* Hero Section */
        .hero {
            text-align: center;
            padding: 4rem 0;
            color: var(--blanco);
        }

        .hero h1 {
            font-size: clamp(2rem, 5vw, 3.5rem);
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .hero .lead {
            font-size: clamp(1rem, 3vw, 1.25rem);
            margin-bottom: 2rem;
            opacity: 0.95;
        }

        .hero img {
            max-width: 100%;
            height: auto;
            max-height: 250px;
            border-radius: 50%;
            border: 4px solid var(--oro);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
        }

        .hero img:hover {
            transform: scale(1.05);
        }

        /* Alert */
        .alert {
            background: var(--naranja);
            color: var(--vinotinto);
            border: none;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
            position: relative;
        }

        .alert button {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
        }

        /* Cards Section */
        .cards-section {
            display: flex;
            flex-direction: column;
            gap: 2rem;
            margin: 3rem 0;
        }

        .card {
            background: var(--blanco);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-align: center;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }

        .card h5 {
            color: var(--vinotinto);
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .card p {
            color: var(--gris-oscuro);
            margin-bottom: 1.5rem;
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .btn-primary {
            background: var(--vinotinto);
            color: var(--blanco);
        }

        .btn-primary:hover {
            background: #5a1b28;
            transform: scale(1.05);
        }

        .btn-success {
            background: var(--oro);
            color: var(--vinotinto);
        }

        .btn-success:hover {
            background: #e6b323;
            transform: scale(1.05);
        }

        .btn-outline-info {
            background: transparent;
            color: var(--blanco);
            border: 2px solid var(--cian);
        }

        .btn-outline-info:hover {
            background: var(--cian);
            color: var(--vinotinto);
            transform: scale(1.05);
        }

        /* Teaser Section */
        .teaser {
            background: rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            color: var(--blanco);
        }

        .teaser h5 {
            color: var(--oro);
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }

        .teaser p {
            margin-bottom: 1.5rem;
            opacity: 0.95;
        }

        /* Responsive */
        @media (min-width: 768px) {
            .cards-section {
                flex-direction: row;
                justify-content: center;
            }

            .card {
                flex: 1;
                max-width: 400px;
            }

            .hero {
                padding: 6rem 0;
            }
        }

        /* Animaciones Suaves */
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

        .hero, .card, .teaser {
            animation: fadeInUp 0.8s ease-out;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="hero">
            <h1>¡Bienvenido a Fantasy Vinotinto N10!</h1>
            
            <?php if (isset($_GET['redirect'])): ?>
                <div class="alert">
                    Login requerido para <?php echo $_GET['redirect'] == 'liga' ? 'armar plantilla' : 'ver standings'; ?>.
                    <button type="button" onclick="this.parentElement.style.display='none'">&times;</button>
                </div>
            <?php endif; ?>

            <p class="lead">Compite con la comunidad venezolana armando plantillas de nuestros vinotintos en el exterior. Suma puntos con tu fórmula propietaria, gana premios mensuales y vive la pasión por la Vinotinto.</p>
            <img src="img/fantasy-vinotinto-logo-min.png" alt="Vinotinto Logo" loading="lazy">
        </div>

        <div class="cards-section">
            <div class="card">
                <h5>Únete Gratis (Prueba)</h5>
                <p>Regístrate y explora el pool de jugadores. Paga $1/mes para competir full.</p>
                <a href="register.php" class="btn btn-success">Registrarse</a>
            </div>
            <div class="card">
                <h5>Ya Tienes Cuenta?</h5>
                <p>Accede y arma tu plantilla.</p>
                <a href="login.php" class="btn btn-primary">Login</a>
            </div>
        </div>

        <div class="teaser">
            <h5>Teaser del Juego</h5>
            <p>Pool de ~100 vinotintos en ligas pro extranjeras. Arma squad con 100M Naranjas 🍊, suma points semanales (viernes-lunes), revalorización mensual. ¡Premios para el #1!</p>
            <a href="register.php" class="btn btn-outline-info">Ver Más y Empezar</a>
        </div>
    </div>

    <script>
        // JS mínimo para interacciones suaves (cierre alert)
        document.addEventListener('DOMContentLoaded', function() {
            // Animación de entrada staggered para cards
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.2}s`;
            });
        });
    </script>
</body>
</html>