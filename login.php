<?php
session_start();
include 'db_connect.php';

$error_msg = '';
$success_msg = '';

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error_msg = 'Email y password requeridos.';
    } else {
        $query = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            if (password_verify($password, $user['password'])) {
                $_SESSION['usrn'] = $email;
                $success_msg = 'Login exitoso! Redirigiendo...';
                echo "<script>setTimeout(function(){ window.location.href = 'Home.php'; }, 2000);</script>";
            } else {
                $error_msg = 'Password incorrecto.';
            }
        } else {
            $error_msg = 'Email no encontrado. Regístrate primero.';
        }
    }
}

// Si ya logueado, redirige a Home
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
    <title>Login - Fantasy Vinotinto N10</title>
    <link rel="stylesheet" href="Style.css">
    <style>
        /* Paleta Corporativa (Coherente con index.php y register.php) */
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .container {
            max-width: 400px;
            width: 100%;
            background: var(--blanco);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            animation: fadeInUp 0.8s ease-out;
        }

        h2 {
            text-align: center;
            color: var(--vinotinto);
            margin-bottom: 0.5rem;
            font-size: clamp(1.5rem, 4vw, 2rem);
        }

        p {
            text-align: center;
            color: var(--gris-oscuro);
            margin-bottom: 1.5rem;
        }

        /* Alertas (Coherente con index y register) */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: bold;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        /* Formularios (Coherente) */
        form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        label {
            font-weight: bold;
            color: var(--vinotinto);
            margin-bottom: 0.5rem;
            display: block;
        }

        input[type="email"],
        input[type="password"] {
            padding: 0.75rem;
            border: 2px solid var(--gris-claro);
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            width: 100%;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: var(--oro);
            box-shadow: 0 0 0 3px rgba(255, 199, 44, 0.1);
        }

        /* Botones (Coherente) */
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
            margin-top: 0.5rem;
            width: 100%;
        }

        .btn-primary {
            background: var(--vinotinto);
            color: var(--blanco);
        }

        .btn-primary:hover {
            background: #5a1b28;
            transform: scale(1.02);
        }

        /* Enlaces como Botones */
        .link-btn {
            display: block;
            padding: 0.75rem;
            background: var(--gris-claro);
            border: 1px solid var(--oro);
            border-radius: 8px;
            color: var(--vinotinto);
            text-align: center;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .link-btn:hover {
            background: var(--oro);
            color: var(--vinotinto);
            transform: scale(1.02);
        }

        /* Responsive */
        @media (max-width: 480px) {
            .container {
                padding: 1.5rem;
                margin: 1rem;
            }

            h2 {
                font-size: 1.5rem;
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
    </style>
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <p>Ingresa con tu email y password.</p>
        
        <?php if ($error_msg): ?>
            <div class="alert alert-danger"><?php echo $error_msg; ?></div>
        <?php endif; ?>
        <?php if ($success_msg): ?>
            <div class="alert alert-success"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required placeholder="yo@test.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required placeholder="tu_pass">
            
            <button type="submit" name="login" class="btn btn-primary">Login</button>
        </form>
        
        <a href="register.php" class="link-btn">¿No tienes cuenta? Regístrate</a>
        <a href="forgot_password.php" class="link-btn">¿Olvidaste tu password?</a>
    </div>

    <script>
        // JS mínimo para validación suave (opcional, coherente con register)
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                // Placeholder para validaciones futuras
            });
        });
    </script>
</body>
</html>