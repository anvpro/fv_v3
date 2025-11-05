<?php
session_start();
include 'db_connect.php';

$step = 1;  // 1: Email + Pregunta, 2: Verify Answer, 3: Nuevo Pass
$error_msg = '';
$success_msg = '';
$user_email = '';
$user_question = '';

if (isset($_POST['step1'])) {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $query = "SELECT security_question FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $user_email = $email;
        $user_question = $row['security_question'];
        $step = 2;  // Avanza a verify answer
    } else {
        $error_msg = 'Email no encontrado.';
    }
}

if (isset($_POST['step2'])) {
    $email = $_POST['email'];
    $answer = $_POST['security_answer'];
    $query = "SELECT password, security_answer_hash FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    if (password_verify($answer, $row['security_answer_hash'])) {
        $step = 3;  // Avanza a nuevo pass
    } else {
        $error_msg = 'Respuesta incorrecta.';
    }
}

if (isset($_POST['step3'])) {
    $email = $_POST['email'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];
    if ($new_pass === $confirm_pass) {
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $update_query = "UPDATE users SET password = '$hash' WHERE email = '$email'";
        if (mysqli_query($conn, $update_query)) {
            $success_msg = '¡Password cambiado! Login con nuevo.';
            $step = 1;
        } else {
            $error_msg = 'Error al cambiar.';
        }
    } else {
        $error_msg = 'Passwords no coinciden.';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Olvidé Password - Fantasy Vinotinto N10</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Olvidé mi Password</h2>
        <?php if ($error_msg): ?>
            <div class="alert alert-danger"><?php echo $error_msg; ?></div>
        <?php endif; ?>
        <?php if ($success_msg): ?>
            <div class="alert alert-success"><?php echo $success_msg; ?></div>
        <?php endif; ?>

        <?php if ($step == 1): ?>
            <form method="POST">
                <input type="hidden" name="step1" value="1">
                Email: <input type="email" name="email" class="form-control mb-2" required><br>
                <button type="submit" class="btn btn-primary">Continuar</button>
            </form>
        <?php elseif ($step == 2): ?>
            <form method="POST">
                <input type="hidden" name="step2" value="1">
                <input type="hidden" name="email" value="<?php echo $user_email; ?>">
                Pregunta: <p class="form-control-plaintext"><?php echo $user_question; ?></p><br>
                Respuesta: <input type="text" name="security_answer" class="form-control mb-2" required><br>
                <button type="submit" class="btn btn-primary">Verificar</button>
            </form>
        <?php elseif ($step == 3): ?>
            <form method="POST">
                <input type="hidden" name="step3" value="1">
                <input type="hidden" name="email" value="<?php echo $user_email; ?>">
                Nuevo Password: <input type="password" name="new_password" class="form-control mb-2" required><br>
                Confirmar: <input type="password" name="confirm_password" class="form-control mb-2" required><br>
                <button type="submit" class="btn btn-primary">Cambiar</button>
            </form>
        <?php endif; ?>

        <br><a href="login.php">Volver a Login</a>
    </div>
</body>
</html>