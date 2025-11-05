<?php
session_start();
include 'db_connect.php';

// =======================================================
// 1. VERIFICACIÓN DE ACCESO DE ADMINISTRADOR
// =======================================================
if (!isset($_SESSION['usrn'])) {
    header("Location: index.php");
    exit();
}
$email = $_SESSION['usrn'];
$admin_query = "SELECT is_admin FROM users WHERE email = '$email'";
$admin_result = mysqli_query($conn, $admin_query);
if (mysqli_fetch_assoc($admin_result)['is_admin'] != 1) {
    die('Acceso denegado. Solo admins.');
}

$success_msg = '';
$error_msg = '';

// =======================================================
// 2. LÓGICA DE GESTIÓN DEL MERCADO
// =======================================================

if (isset($_POST['set_market_status'])) {
    $new_status = intval($_POST['set_market_status']); // 1 para abierto, 0 para cerrado
    
    // Obtener la jornada activa para aplicar el cambio
    $active_jornada_query = "SELECT id, nombre FROM jornadas WHERE active = 1 LIMIT 1";
    $active_jornada_result = mysqli_query($conn, $active_jornada_query);
    
    if (mysqli_num_rows($active_jornada_result) > 0) {
        $jornada_data = mysqli_fetch_assoc($active_jornada_result);
        $jornada_id = $jornada_data['id'];
        $jornada_nombre = $jornada_data['nombre'];

        $update_query = "UPDATE jornadas SET is_market_open = $new_status WHERE id = $jornada_id";
        
        if (mysqli_query($conn, $update_query)) {
            $status_text = $new_status ? 'ABIERTO' : 'CERRADO';
            $success_msg = "✅ Mercado de **{$jornada_nombre}** actualizado a: **{$status_text}**.";
        } else {
            $error_msg = '❌ Error al actualizar la base de datos: ' . mysqli_error($conn);
        }
    } else {
        $error_msg = '❌ Error: No se encontró una jornada marcada como `active = 1`.';
    }
}

// =======================================================
// 3. OBTENER ESTADO ACTUAL PARA LA VISTA
// =======================================================
$active_jornada_query = "SELECT id, nombre, is_market_open FROM jornadas WHERE active = 1 LIMIT 1";
$active_jornada_result = mysqli_query($conn, $active_jornada_query);
$current_jornada_data = mysqli_fetch_assoc($active_jornada_result);

$is_market_currently_open = $current_jornada_data['is_market_open'] ?? 0;
$jornada_name = $current_jornada_data['nombre'] ?? 'N/A';
$jornada_found = mysqli_num_rows($active_jornada_result) > 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Admin - Control de Jornadas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --primary-vinotinto: #6f2232;
        }
        body { background-color: #f8f9fa; }
        .card-market { border-left: 5px solid var(--primary-vinotinto); }
        .btn-open { background-color: #28a745; color: white; }
        .btn-close { background-color: #dc3545; color: white; }
    </style>
</head>
<body>
    <div class="container py-5">
        <h1 class="mb-4 text-center">Panel de Control de Jornadas</h1>
        <h3 class="mb-5 text-center text-secondary">
            <i class="fas fa-calendar-alt me-2"></i> Jornada Activa: **<?php echo htmlspecialchars($jornada_name); ?>**
        </h3>

        <?php if ($success_msg): ?>
            <div class="alert alert-success shadow-sm" role="alert"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="alert alert-danger shadow-sm" role="alert"><?php echo $error_msg; ?></div>
        <?php endif; ?>
        
        <?php if ($jornada_found): ?>

            <div class="card p-4 shadow-lg card-market">
                <h4 class="card-title mb-4">Estado Actual del Mercado de Fichajes</h4>
                
                <div class="alert <?php echo $is_market_currently_open ? 'alert-success' : 'alert-danger'; ?> text-center fw-bold fs-5">
                    <?php if ($is_market_currently_open): ?>
                        <i class="fas fa-unlock me-2"></i> MERCADO ABIERTO
                    <?php else: ?>
                        <i class="fas fa-lock me-2"></i> MERCADO CERRADO
                    <?php endif; ?>
                </div>

                <form method="POST" class="d-grid gap-2 d-md-flex justify-content-md-center mt-4">
                    <?php if ($is_market_currently_open): ?>
                        <button type="submit" name="set_market_status" value="0" class="btn btn-close btn-lg">
                            <i class="fas fa-times-circle me-2"></i> CERRAR MERCADO
                        </button>
                    <?php else: ?>
                        <button type="submit" name="set_market_status" value="1" class="btn btn-open btn-lg">
                            <i class="fas fa-check-circle me-2"></i> ABRIR MERCADO
                        </button>
                    <?php endif; ?>
                </form>
            </div>
            
        <?php else: ?>
            <div class="alert alert-warning text-center">
                <i class="fas fa-exclamation-triangle me-2"></i> No hay una jornada marcada como `active = 1`. Por favor, activa una jornada en la tabla `jornadas`.
            </div>
        <?php endif; ?>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>