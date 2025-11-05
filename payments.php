<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['usrn'])) {
    header("Location: register.php");
    exit();
}

$email = $_SESSION['usrn'];

// Verifica si ya activo
$check_active = "SELECT subscription_active FROM users WHERE email = '$email'";
$active_result = mysqli_query($conn, $check_active);
$active = mysqli_fetch_assoc($active_result)['subscription_active'];

if ($active && strtotime($active) > time()) {
    // MODIFICADO: Uso de icono en el script de alerta para mejor UX
    echo "<script>alert('👑 ¡Ya estás suscrito! Válido hasta " . date('d/m/Y', strtotime($active)) . "'); window.location.href='Home.php';</script>";
    exit();
}

// Retorno de PayPal (si GET success=1)
if (isset($_GET['success']) && $_GET['success'] == '1') {
    // En prod, verifica IPN; por MVP, asume pago ok y setea +1 mes
    $new_expire = date('Y-m-d H:i:s', strtotime('+1 month'));
    $update_query = "UPDATE users SET subscription_active = '$new_expire' WHERE email = '$email'";
    if (mysqli_query($conn, $update_query)) {
        $_SESSION['active'] = true;
        // MODIFICADO: Uso de icono en el script de alerta para mejor UX
        echo "<script>alert('✅ ¡Suscripción activada! Disfruta Fantasy Vinotinto.'); window.location.href='Home.php';</script>";
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Suscripción Pro - Fantasy Vinotinto N10</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        /* --- Tarjeta Principal de Suscripción (UX Avanzado) --- */
        .subscription-card {
            background-color: var(--white);
            color: var(--vinotinto);
            border-radius: 15px;
            border: 3px solid var(--gold); /* Borde dorado destacado */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            max-width: 500px;
            width: 100%;
            padding: 2rem;
            text-align: center;
            transition: transform 0.3s ease;
        }
        .subscription-card:hover {
            transform: translateY(-5px); /* Efecto "Lift" al pasar el mouse */
        }
        
        .price-tag {
            background-color: var(--vinotinto);
            color: var(--gold);
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-size: 2rem;
            font-weight: bold;
            display: inline-block;
            margin: 1.5rem 0;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        
        .feature-list {
            list-style: none;
            padding: 0;
            text-align: left;
            margin-top: 1.5rem;
        }
        .feature-list li {
            margin-bottom: 0.75rem;
            font-size: 1.1rem;
            font-weight: 500;
        }
        .feature-list i {
            color: var(--vinotinto);
            margin-right: 0.75rem;
        }
        
        /* Estilo para el contenedor del botón de PayPal */
        #paypal-button-container-P-6XS94338W1964140AND357KQ {
            margin-top: 1.5rem;
            padding: 1rem;
            background-color: #f0f0f0; /* Fondo neutral para que el botón PayPal destaque */
            border-radius: 8px;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="subscription-card">
        
        <a href="Home.php" class="btn bg-vinotinto text-white float-start fw-bold mb-3"><i class="fas fa-arrow-left"></i> Volver</a>

        <h2 class="fw-bold mb-0 text-vinotinto"><i class="fas fa-crown text-gold me-2"></i> Suscripción PRO</h2>
        <p class="lead text-muted">Acceso completo a todas las funcionalidades del juego.</p>
        
        <div class="price-tag">
            $1 USD <span class="small fw-normal">/ mes</span>
        </div>
        
        <p class="fw-bold fs-5 text-vinotinto-dark">¡Desbloquea tu potencial de mánager!</p>
        
        <ul class="feature-list">
            <li><i class="fas fa-check-circle"></i> Edición de Plantilla y Traspasos Ilimitados.</li>
            <li><i class="fas fa-check-circle"></i> Acceso a la Tabla de Posiciones (Standings) Global.</li>
            <li><i class="fas fa-check-circle"></i> Participación activa en las Ligas Premium.</li>
            <li><i class="fas fa-check-circle"></i> Soporte Prioritario.</li>
        </ul>
        
        <div id="paypal-button-container-P-6XS94338W1964140AND357KQ"></div>
        <script src="https://sandbox.paypal.com/sdk/js?client-id=AaDH6YgVbKCNo3ICo0ibKqWRTV8rA0WzRqsNa9H6vUOHEgkUR4YXRvdeeR-h-_yJbYWJJPP1iq31IgLD&vault=true&intent=subscription" data-sdk-integration-source="button-factory"></script>
        <script>
            // Lógica de PayPal Intacta
            paypal.Buttons({
                style: {
                    shape: 'rect',
                    color: 'gold',
                    layout: 'vertical',
                    label: 'subscribe'
                },
                createSubscription: function(data, actions) {
                    return actions.subscription.create({
                        /* Creates the subscription */
                        plan_id: 'P-6XS94338W1964140AND357KQ'
                    });
                },
                onApprove: function(data, actions) {
                    // Redirecciona al backend para procesar la activación de la suscripción
                    window.location.href = 'payments.php?success=1&subscriptionID=' + data.subscriptionID;
                }
            }).render('#paypal-button-container-P-6XS94338W1964140AND357KQ'); // Renders the PayPal button
        </script>

        <p class="mt-4"><small class="text-muted fw-bold">La suscripción se cobra automáticamente cada 30 días. Tu acceso se congela si el pago falla al vencer.</small></p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>