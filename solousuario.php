<?php 
session_start();

if(!isset($_SESSION['tema'])) {
    $_SESSION['tema'] = 'light';
}

if (!isset($_SESSION["usuario_valida"]) || $_SESSION["tipo_usuario"] !== "empleado") {
    header("Location: login.php");
    exit;
}

require_once "MySQLAux.php";

$mysql = new MySQLAux("localhost", "pruebas", "root", "root");
$panes = $mysql->obtenTodasLasFilas("panes");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registrar venta - MigaApp</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/2.3.0/css/dataTables.bootstrap5.min.css" rel="stylesheet">

<style>
body { padding-top: 90px; background:#fff8e1; }
body.dark { background:#121212; color: #efe7e7ff; }

.navbar-custom { background:#f5f0e6; }
body.dark .navbar-custom { background:#1f1f1f; }
body.dark .navbar-custom * { color:#fff !important; }

.card { border-radius:15px; }
body.dark .card {
    background: #ffffff !important;
    color: #000 !important;
}

.btn-pan { background:#6f4e37; color:white; }
.btn-pan:hover { background:#d4a373; }

.logo-lateral{
    position:fixed;
    top:120px;
    width:80px;
    opacity:0.8;
}
.logo-izq{ left:20px; }
.logo-der{ right:20px; }

@media(max-width:768px){
    .logo-lateral{ display:none; }
}
</style>
</head>

<body class="<?= $_SESSION['tema']; ?>">

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-custom fixed-top shadow">
<div class="container-fluid">

<a class="navbar-brand fw-bold">🕊️ MigaApp</a>

<ul class="navbar-nav me-auto">
<li class="nav-item"><a class="nav-link" href="solousuario.php">💰 Registrar venta</a></li>
<li class="nav-item"><a class="nav-link" href="historial_ventas.php">📋 Historial</a></li>
<li class="nav-item"><a class="nav-link" href="estadisticase.php">📈 Mis Estadísticas</a></li>
</ul>

<div class="d-flex gap-2">
<span>👤 <?= $_SESSION['usuario']; ?></span>
<button class="btn btn-outline-secondary btn-sm" onclick="cambiarTema()">🌙</button>
<button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#ayudaModal">❓</button>
<a href="logout.php" class="btn btn-danger btn-sm">Salir</a>
</div>

</div>
</nav>

<!-- LOGOS -->
<img src="imagenes/migaapp.png" class="logo-lateral logo-izq">
<img src="imagenes/migaapp.png" class="logo-lateral logo-der">

<div class="container">
<div class="card shadow p-4">

<h2 class="mb-4">🧁 Registrar venta</h2>

<form method="post" action="procesar_venta.php">

<div class="table-responsive">
<table id="tabla" class="table table-striped table-hover">

<thead class="table-primary">
    <!-- 🔥 MÉTODO DE PAGO -->
<div class="mt-3">
<label class="form-label"><b>Método de pago:</b></label>

<select name="metodo_pago" class="form-control" required>
<option value="">Seleccione</option>
<option value="Efectivo">💵 Efectivo</option>
<option value="Tarjeta">💳 Tarjeta</option>
<option value="Transferencia">🏦 Transferencia</option>
</select>
</div>
<br>
<tr>
<th>Pan</th>
<th>Precio</th>
<th>Cantidad</th>
</tr>
</thead>

<tbody>
<?php foreach($panes as $pan): ?>
<tr>
<td><?= htmlspecialchars($pan["nombre"]) ?></td>
<td>$<?= number_format($pan["precio"],2) ?></td>
<td>
<input type="number" class="form-control"
name="cantidades[<?= $pan["id_pan"] ?>]" min="0" value="0">
</td>
</tr>
<?php endforeach; ?>
</tbody>

</table>
</div>



<div class="text-end mt-3">
<button class="btn btn-pan">Registrar venta</button>
</div>

</form>

</div>
</div>

<!-- AYUDA -->
<div class="modal fade" id="ayudaModal">
<div class="modal-dialog modal-lg">
<div class="modal-content">

<div class="modal-header bg-primary text-white">
<h5 class="modal-title">📚 Guía</h5>
<button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<ul>
<li>Selecciona cantidades de productos</li>
<li>Elige método de pago</li>
<li>Presiona registrar venta</li>
</ul>
</div>

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.datatables.net/2.3.0/js/dataTables.min.js"></script>

<script>
new DataTable('#tabla');

function cambiarTema(){
let body = document.body;
body.classList.toggle("dark");
fetch("guardar_tema.php?tema="+(body.classList.contains("dark")?"dark":"light"));
}
</script>

</body>
</html>