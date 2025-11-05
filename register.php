<?php
session_start();
include 'db_connect.php';  // Tu conexión DB

$error_msg = '';
$success_msg = '';

if (isset($_POST['register'])) {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    // Nuevo campo: Alias
    $alias = mysqli_real_escape_string($conn, trim($_POST['alias'])); 
    $password = $_POST['password'];
    $security_question = mysqli_real_escape_string($conn, trim($_POST['security_question']));
    $security_answer = mysqli_real_escape_string($conn, trim($_POST['security_answer']));
    $answer_hash = password_hash($security_answer, PASSWORD_DEFAULT);

    // Validación: Se añade el alias a la lista de campos requeridos
    if (empty($email) || empty($alias) || empty($password) || empty($security_question) || empty($security_answer)) {
        $error_msg = 'Email, Alias, password y pregunta de seguridad son requeridos.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Query de registro: Se añade el campo 'alias' al INSERT
        $query = "INSERT INTO users (email, alias, password, security_question, security_answer_hash) VALUES ('$email', '$alias', '$hash', '$security_question', '$answer_hash')";
        
        if (mysqli_query($conn, $query)) {
            $success_msg = 'Registro exitoso! Redirigiendo...';
            $_SESSION['usrn'] = $email;
            echo "<script>setTimeout(function(){ window.location.href = 'Home.php'; }, 2000);</script>";
        } else {
            // El error 'Entrada duplicada' ahora puede ser por email O por alias
            if (mysqli_errno($conn) == 1062) {
                 $error_msg = 'Error: El Email o el Alias ya existe. Por favor, elige otro.';
            } else {
                 $error_msg = 'Error: Fallo en DB. ' . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Fantasy Vinotinto N10</title>
    <link rel="stylesheet" href="Style.css">
    <style>
        /* Paleta Corporativa (Coherente con index.php) */
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
            max-width: 500px;
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
            margin-bottom: 1.5rem;
            font-size: clamp(1.5rem, 4vw, 2rem);
        }

        /* Alertas (Coherente con index) */
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

        /* Formularios */
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
        input[type="text"],
        input[type="password"],
        select {
            padding: 0.75rem;
            border: 2px solid var(--gris-claro);
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            width: 100%;
        }

        input[type="email"]:focus,
        input[type="text"]:focus,
        input[type="password"]:focus,
        select:focus {
            outline: none;
            border-color: var(--oro);
            box-shadow: 0 0 0 3px rgba(255, 199, 44, 0.1);
        }

        /* Botones (Coherente con index) */
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
        }

        .btn-primary {
            background: var(--vinotinto);
            color: var(--blanco);
            width: 100%;
        }

        .btn-primary:hover {
            background: #5a1b28;
            transform: scale(1.02);
        }

        .btn-secondary {
            background: var(--gris-oscuro);
            color: var(--blanco);
            text-align: center;
        }

        .btn-secondary:hover {
            background: #545b62;
            transform: scale(1.02);
            color: var(--blanco);
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
        <h2>Registro de Usuario</h2>
        
        <?php if ($error_msg): ?>
            <div class="alert alert-danger"><?php echo $error_msg; ?></div>
        <?php endif; ?>
        <?php if ($success_msg): ?>
            <div class="alert alert-success"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required placeholder="yo@test.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            
            <label for="alias">Alias (Nombre público):</label>
            <input type="text" id="alias" name="alias" required placeholder="Tu alias en el ranking" maxlength="50" value="<?php echo htmlspecialchars($_POST['alias'] ?? ''); ?>">
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required placeholder="Tu password">
            
            <label for="security_question">Seguridad (para reset pass):</label>
            <select id="security_question" name="security_question" required>
                <option value="">Elige una</option>
                <option value="Nombre de tu primer mascota?" <?php echo (($_POST['security_question'] ?? '') == 'Nombre de tu primer mascota?') ? 'selected' : ''; ?>>Nombre de tu primer mascota?</option>
                <option value="Ciudad donde naciste?" <?php echo (($_POST['security_question'] ?? '') == 'Ciudad donde naciste?') ? 'selected' : ''; ?>>Ciudad donde naciste?</option>
                <option value="Nombre del primer colegio?" <?php echo (($_POST['security_question'] ?? '') == 'Nombre del primer colegio?') ? 'selected' : ''; ?>>Nombre del primer colegio?</option>
                <option value="Plato favorito de la infancia?" <?php echo (($_POST['security_question'] ?? '') == 'Plato favorito de la infancia?') ? 'selected' : ''; ?>>Plato favorito de la infancia?</option>
            </select>
            
            <label for="security_answer">Respuesta:</label>
            <input type="text" id="security_answer" name="security_answer" required placeholder="Respuesta secreta (case-sensitive)">
            
            <button type="submit" name="register" class="btn btn-primary">Registrar</button>
        </form>
        
        <br><a href="index.php" class="btn btn-secondary">Ir a Login</a>
    </div>

    <script>
        // JS mínimo para validación suave (opcional, coherente con index)
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                // Placeholder para validaciones futuras (ej. strength password)
            });
        });
    </script>
</body>
</html>