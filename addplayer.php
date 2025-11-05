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

// Check admin (Asumimos que solo Admin puede agregar jugadores)
$admin_query = "SELECT is_admin FROM users WHERE email = '$email'";
$admin_result = mysqli_query($conn, $admin_query);
$is_admin = mysqli_fetch_assoc($admin_result)['is_admin'] == 1;

if (!$is_admin) {
    header("Location: Fantasy-League.php"); // Redirige si no es admin
    exit();
}

// --- Handler para agregar jugador ---
if (isset($_POST['add_player'])) {
    $name = trim($_POST['name']);
    $position = trim($_POST['position']);
    $price = intval($_POST['price']);

    if (empty($name) || empty($position) || $price <= 0) {
        $error_msg = "Todos los campos son obligatorios y el precio debe ser mayor a 0.";
    } else {
        // Sanitize inputs
        $name = mysqli_real_escape_string($conn, $name);
        $position = mysqli_real_escape_string($conn, $position);
        $price = mysqli_real_escape_string($conn, $price);

        // Agrega posicion a INSERT (para validar) (NUEVO CÓDIGO)
        $posicion = mysqli_real_escape_string($conn, $_POST['position']);
        
        $query = "INSERT INTO players (name, position, price, posicion) 
                  VALUES ('$name', '$position', '$price', '$posicion')"; // Duplica position para consistencia

        if (mysqli_query($conn, $query)) {
            $success_msg = "¡Jugador **$name** agregado exitosamente al Pool Global!";
        } else {
            $error_msg = "Error al agregar jugador: " . mysqli_error($conn);
        }
    }
}

// Check para asegurar que las columnas 'posicion' y 'position' existan si es necesario.
$check_posicion = mysqli_query($conn, "SHOW COLUMNS FROM players LIKE 'posicion'");
if (mysqli_num_rows($check_posicion) == 0) {
    mysqli_query($conn, "ALTER TABLE players ADD COLUMN posicion VARCHAR(50) NULL");
}
$check_position = mysqli_query($conn, "SHOW COLUMNS FROM players LIKE 'position'");
if (mysqli_num_rows($check_position) == 0) {
    mysqli_query($conn, "ALTER TABLE players ADD COLUMN position VARCHAR(50) NULL");
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Admin: Agregar Jugador - Fantasy Vinotinto N10</title>
    <link rel="stylesheet" href="Style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Admin: Agregar Nuevo Jugador</h1>
        <a href="Fantasy-League.php" class="btn btn-secondary mb-3">Volver a Fantasy League</a>

        <?php if ($error_msg): ?>
            <div class="alert alert-danger"><?php echo $error_msg; ?></div>
        <?php endif; ?>
        <?php if ($success_msg): ?>
            <div class="alert alert-success"><?php echo $success_msg; ?></div>
        <?php endif; ?>

        <div class="card p-4">
            <form method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Nombre del Jugador</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                
                <div class="mb-3">
                    <label for="position" class="form-label">Posición Principal</label>
                    <select class="form-select" id="position" name="position" required>
                        <option value="">Seleccione una posición</option>
                        <option value="Arquero">Arquero</option>
                        <option value="Defensa">Defensa</option>
                        <option value="Mediocampista">Mediocampista</option>
                        <option value="Atacante">Atacante</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="price" class="form-label">Precio Inicial (Naranjas)</label>
                    <input type="number" class="form-control" id="price" name="price" required min="1000000" step="1000000">
                    <small class="form-text text-muted">Precio en millones (e.g., 50000000).</small>
                </div>
                
                <button type="submit" name="add_player" class="btn btn-primary">Crear Jugador</button>
            </form>
        </div>
        
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>