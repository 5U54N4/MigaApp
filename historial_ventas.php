<?php 
session_start();

if (!isset($_SESSION["usuario_valida"])) {
    header("Location: login.php");
    exit;
}

if(!isset($_SESSION['tema'])) {
    $_SESSION['tema'] = 'light';
}

require_once "MySQLAux.php";
$mysql = new MySQLAux("localhost","pruebas","root","root");

$fecha = $_GET["fecha"] ?? "";

$id_usuario = $_SESSION["usuario_id"];
$esAdmin = $_SESSION["tipo_usuario"] === "admin";

/* ================= CONSULTA SEGURA ================= */

if($fecha != ""){

    if($esAdmin){
        $query = "SELECT v.id_venta, v.total, v.fecha, 
                         u.nombre AS empleado, v.forma_pago
                  FROM ventas v
                  JOIN usuarios u ON v.id_usuario = u.id_usuario
                  WHERE DATE(v.fecha) = ?
                  ORDER BY v.fecha DESC";
        $stmt = $mysql->conn->prepare($query);
        $stmt->execute([$fecha]);

    } else {
        $query = "SELECT v.id_venta, v.total, v.fecha, 
                         u.nombre AS empleado, v.forma_pago
                  FROM ventas v
                  JOIN usuarios u ON v.id_usuario = u.id_usuario
                  WHERE DATE(v.fecha) = ? AND v.id_usuario = ?
                  ORDER BY v.fecha DESC";
        $stmt = $mysql->conn->prepare($query);
        $stmt->execute([$fecha, $id_usuario]);
    }

} else {

    if($esAdmin){
        $query = "SELECT v.id_venta, v.total, v.fecha, 
                         u.nombre AS empleado, v.forma_pago
                  FROM ventas v
                  JOIN usuarios u ON v.id_usuario = u.id_usuario
                  ORDER BY v.fecha DESC";
        $stmt = $mysql->conn->prepare($query);
        $stmt->execute();

    } else {
        $query = "SELECT v.id_venta, v.total, v.fecha, 
                         u.nombre AS empleado, v.forma_pago
                  FROM ventas v
                  JOIN usuarios u ON v.id_usuario = u.id_usuario
                  WHERE v.id_usuario = ?
                  ORDER BY v.fecha DESC";
        $stmt = $mysql->conn->prepare($query);
        $stmt->execute([$id_usuario]);
    }
}

$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Historial de ventas</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body { background-color:#fff8e1; }
body.dark { background:#212529; color:#fff; }

.navbar-custom { background:#f5f0e6; }
body.dark .navbar-custom { background:#2c3034; }
body.dark .navbar-custom * { color:#fff !important; }

.logo-admin {
    position: fixed;
    top: 90px;
    width: 90px;
    opacity: 0.8;
}
.logo-admin.izq { left:15px; }
.logo-admin.der { right:15px; }

.container, .card { position:relative; z-index:2; }

body.dark .card {
    background:#ffffff;
    color:#000;
}

.titulo {
    text-align:center;
    font-size:26px;
    margin-bottom:20px;
    color:#000;
}

.filtro {
    display:flex;
    justify-content:center;
    gap:10px;
    margin-bottom:20px;
}

.table {
    background:#fff;
    color:#000;
}
</style>
</head>

<body class="<?= $_SESSION['tema']; ?>">

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-custom fixed-top shadow">
<div class="container-fluid">

<a class="navbar-brand fw-bold">🕊️ MigaApp</a>

<ul class="navbar-nav me-auto">

<?php if($_SESSION["tipo_usuario"] == "admin"): ?>
<li class="nav-item"><a class="nav-link" href="soloadmin.php">🥐 Productos</a></li>
<li class="nav-item"><a class="nav-link active" href="historial_ventas.php">📋 Historial</a></li>
<li class="nav-item"><a class="nav-link" href="reporte_ventas.php">📊 Reporte</a></li>
<li class="nav-item"><a class="nav-link" href="estadisticas.php">📈 Estadísticas</a></li>
<li class="nav-item"><a class="nav-link" href="crear_usuario.php">👤 Crear Usuarios</a></li>
<?php else: ?>
<li class="nav-item"><a class="nav-link" href="solousuario.php">💰 Registrar venta</a></li>
<li class="nav-item"><a class="nav-link active" href="historial_ventas.php">📋 Historial</a></li>
<li class="nav-item"><a class="nav-link" href="estadisticase.php">📈 Mis Estadísticas</a></li>
<?php endif; ?>

</ul>

<div class="d-flex gap-2">
<span>👤 <?= $_SESSION['usuario']; ?></span>
<button class="btn btn-outline-dark btn-sm" onclick="cambiarTema()">🌙</button>
<button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#ayuda">❓</button>
<a href="logout.php" class="btn btn-danger btn-sm">Salir</a>
</div>

</div>
</nav>

<!-- LOGOS -->
<img src="imagenes/migaapp.png" class="logo-admin izq">
<img src="imagenes/migaapp.png" class="logo-admin der">

<div class="container mt-5 pt-4">
<div class="card p-4 shadow">

<div class="titulo">📋 Historial de ventas</div>

<form method="GET" class="filtro">
<input type="date" name="fecha" value="<?= $fecha ?>" class="form-control">
<button class="btn btn-primary">Consultar</button>
<a href="historial_ventas.php" class="btn btn-secondary">Limpiar</a>
</form>

<table class="table table-bordered table-striped">
<thead class="table-primary">
<tr>
<th>Fecha</th>
<th>No. Venta</th>

<?php if($esAdmin): ?>
<th>Empleado</th>
<?php endif; ?>

<th>Forma de pago</th>
<th>Total</th>
</tr>
</thead>

<tbody>
<?php if(!empty($ventas)): ?>
<?php foreach($ventas as $v): ?>
<tr>
<td><?= date("d/m/Y", strtotime($v["fecha"])) ?></td>
<td><?= $v["id_venta"] ?></td>

<?php if($esAdmin): ?>
<td><?= htmlspecialchars($v["empleado"]) ?></td>
<?php endif; ?>

<td><?= $v["forma_pago"] ?></td>
<td>$<?= number_format($v["total"],2) ?></td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr><td colspan="5">No hay ventas</td></tr>
<?php endif; ?>
</tbody>
</table>

</div>
</div>

<!-- AYUDA -->
<div class="modal fade" id="ayuda">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header"><h5>Ayuda</h5></div>
<div class="modal-body">
Filtra por fecha.  
Empleado solo ve sus ventas.  
Administrador ve todo.
</div>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>

<script>
function cambiarTema(){
let body=document.body;
body.classList.toggle("dark");
fetch("guardar_tema.php?tema="+(body.classList.contains("dark")?"dark":"light"));
}
</script>

</body>
</html>