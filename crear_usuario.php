<?php
session_start();
require_once "MySQLAux.php";

// 🔐 SOLO ADMIN
if (!isset($_SESSION["usuario_valida"]) || $_SESSION["tipo_usuario"] !== "admin") {
    die("❌ Acceso denegado.");
}

// 🔗 CONEXIÓN
$mysql = new MySQLAux("localhost","pruebas","root","root");

$mensaje = "";

// 📥 GUARDAR USUARIO
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = $_POST["nombre"];
    $usuario = $_POST["usuario"];
    $password = md5($_POST["password"]); // igual que tu sistema
    $tipo = $_POST["tipo"];

    // 🔍 VALIDAR DUPLICADO
    $check = $mysql->conn->prepare("SELECT * FROM usuarios WHERE usuario = ?");
    $check->execute([$usuario]);

    if ($check->fetch()) {
        $mensaje = "❌ El usuario ya existe";
    } else {
        $query = "INSERT INTO usuarios (nombre, usuario, clave, tipo) VALUES (?, ?, ?, ?)";
        $stmt = $mysql->conn->prepare($query);
        $stmt->execute([$nombre, $usuario, $password, $tipo]);

        $mensaje = "✅ Usuario creado correctamente";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Crear Usuario | MigaApp</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- ICONOS 👁️ -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
body {
    background: #fff8e1;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}

.card {
    width: 100%;
    max-width: 420px;
    border-radius: 20px;
    box-shadow: 0 15px 35px rgba(0,0,0,.15);
}
</style>
</head>

<body>

<div class="card p-4">

    <h3 class="text-center mb-3">👤 Crear usuario</h3>

    <?php if($mensaje): ?>
        <div class="alert <?= str_contains($mensaje, '❌') ? 'alert-danger' : 'alert-success'; ?>">
            <?= $mensaje ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <label>Nombre</label>
        <input type="text" name="nombre" class="form-control mb-2" required>

        <label>Usuario</label>
        <input type="text" name="usuario" class="form-control mb-2" required>

        <label>Contraseña</label>
        <div class="input-group mb-2">
            <input type="password" name="password" id="password" class="form-control" required>

            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword()">
                <i class="bi bi-eye" id="icono"></i>
            </button>
        </div>

        <label>Tipo de usuario</label>
        <select name="tipo" class="form-control mb-3">
            <option value="admin">Admin</option>
            <option value="empleado">Empleado</option>
        </select>

        <button class="btn btn-success w-100">
            Crear usuario
        </button>

        <a href="soloadmin.php" class="btn btn-secondary w-100 mt-2">
            Volver
        </a>

    </form>

</div>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>

<script>
function togglePassword() {
    const input = document.getElementById("password");
    const icono = document.getElementById("icono");

    if (input.type === "password") {
        input.type = "text";
        icono.classList.remove("bi-eye");
        icono.classList.add("bi-eye-slash");
    } else {
        input.type = "password";
        icono.classList.remove("bi-eye-slash");
        icono.classList.add("bi-eye");
    }
}
</script>

</body>
</html>