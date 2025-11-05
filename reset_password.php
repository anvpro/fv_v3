<?php
session_start();
include 'db_connect.php';

$error_msg = '';
$success_msg = '';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    $error_msg = 'Token inválido.';
} else {
    // Verify token válido y no expirado
    $query = "SELECT email FROM users WHERE reset_token = '$token'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        $user_email = mysqli_fetch_assoc($result)['email'];
    } else {
        $error_msg = 'Token inválido o expirado.';
    }
}

if (isset($_POST['reset'])) {
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    if (empty($new_pass) || empty($confirm_pass) || $new_pass !== $confirm_pass) {
        $error_msg = 'Passwords no coinciden o vacíos.';
    } elseif (!empty($token) && !empty($user_email)) {
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $update_pass = "UPDATE users SET password = '$hash', reset_token = NULL WHERE email = '$user_email'";
        if (mysqli_query($conn, $update_pass)) {
            $success_msg = '¡Password cambiado! Login con nuevo.';
            $token = '';  // Limpia para no reuse
        } else {
            $error_msg = 'Error al cambiar.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reset Password - Fantasy Vinotinto N10</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Cambiar Password</h2>
        <?php if ($error_msg): ?>
            <div class="alert alert-danger"><?php echo $error_msg; ?></div>
        <?php endif; ?>
        <?php if ($success_msg): ?>
            <div class="alert alert-success"><?php echo $success_msg; ?></div>
            <a href="login.php" class="btn btn-primary">Login</a>
        <?php else: ?>
            <form method="POST">
                Nuevo Password: <input type="password" name="new_password" class="form-control mb-2" required><br>
                Confirmar: <input type="password" name="confirm_password" class="form-control mb-2" required><br>
                <button type="submit" name="reset" class="btn btn-primary">Cambiar</button>
            </form>
        <?php endif; ?>
        <br><a href="login.php">Volver a Login</a>
    </div>
</body>
</html>