<?php
session_start();
require_once "MySQLAux.php";

/* -------------------- Seguridad y tema -------------------- */
if (!isset($_SESSION['tema'])) {
    $_SESSION['tema'] = 'light';
}

// 🔐 VALIDACIÓN CORRECTA
if (!isset($_SESSION["usuario_valida"]) || $_SESSION["tipo_usuario"] !== "admin") {
    die("❌ Acceso denegado.");
}

/* -------------------- Conexión -------------------- */
$mysql = new MySQLAux("localhost", "pruebas", "root", "root");

if (!isset($_GET["id_pan"])) {
    die("ID de pan no proporcionado.");
}

$id = $_GET["id_pan"];

// 🔥 CORREGIDO: precio en lugar de precio_unitario
$query = "SELECT nombre, precio FROM panes WHERE id_pan = ?";
$stmt = $mysql->conn->prepare($query);
$stmt->execute([$id]);
$pan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pan) {
    die("Pan no encontrado.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Precio | MigaApp</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
body {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #fff8e1;
}

.card {
    width: 100%;
    max-width: 460px;
    border-radius: 20px;
    box-shadow: 0 15px 35px rgba(0,0,0,.15);
}

.logo {
    width: 110px;
    margin: 0 auto 10px;
    display: block;
}

body.dark {
    background-color: #212529;
    color: #fff;
}
</style>
</head>

<body class="<?= $_SESSION['tema']; ?>">

<div class="card">
    <div class="card-body p-4">

        <img src="imagenes/migaapp.png" class="logo">

        <h4 class="text-center mb-4">
            Editar precio<br>
            <b><?= htmlspecialchars($pan["nombre"]); ?></b>
        </h4>

        <form method="POST" action="actualizar_precio.php">

            <input type="hidden" name="id_pan" value="<?= $id; ?>">

            <label>Nuevo precio</label>
            <div class="input-group mb-3">
                <span class="input-group-text">$</span>

                <!-- 🔥 CORREGIDO -->
                <input type="number"
                       step="0.01"
                       name="precio"
                       class="form-control"
                       value="<?= $pan["precio"]; ?>"
                       required>
            </div>

            <div class="d-flex justify-content-between">
                <button class="btn btn-success">
                    Guardar
                </button>

                <a href="soloadmin.php" class="btn btn-secondary">
                    Cancelar
                </a>
            </div>

        </form>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>