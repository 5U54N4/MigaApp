<?php 
session_start();

// 🔐 PROTECCIÓN ADMIN
if (!isset($_SESSION["usuario_valida"]) || $_SESSION["tipo_usuario"] !== "admin") {
    header("Location: login.php");
    exit;
}

// 🌙 Tema
if(!isset($_SESSION['tema'])) {
    $_SESSION['tema'] = 'light';
}

// 🔎 CONEXIÓN Y DATOS
require_once "MySQLAux.php";
$mysql = new MySQLAux("localhost","pruebas","root","root");
$datos = $mysql->obtenTodasLasFilas("panes");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel Administrador</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/2.3.0/css/dataTables.bootstrap5.min.css" rel="stylesheet">

<style>
body.dark { background-color: #212529; color:#fff; }

.navbar-custom { background:#f5f0e6; }
body.dark .navbar-custom { background:#2c3034; }
body.dark .navbar-custom * { color:#fff !important; }

body.dark h2 { color:#000000ff !important; }

/* DATATABLES DARK */
body.dark .dataTables_wrapper,
body.dark .dataTables_length,
body.dark .dataTables_filter,
body.dark .dataTables_info,
body.dark .paginate_button {
    color: #000000ff !important;
}

body.dark .dataTables_filter input {
    background-color: #2c3034 !important;
    color: #fff !important;
    border: 1px solid #555 !important;
}

body.dark .dataTables_length select {
    background-color: #ffffffff !important;
    color: #fff !important;
}

/* CARD */
body.dark .card { background:#ffffffff; }

/* TABLA */
body.dark .table { color:#fff !important; }

/* LOGOS */
.logo-admin {
    position: fixed;
    top: 90px;
    width: 110px;
    opacity: 0.8;
}
.logo-admin.izq { left:15px; }
.logo-admin.der { right:15px; }
</style>
</head>

<body class="<?= $_SESSION['tema']; ?>">

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-custom fixed-top shadow">
<div class="container-fluid">

<a class="navbar-brand fw-bold">🕊️ MigaApp</a>

<div class="collapse navbar-collapse">

<ul class="navbar-nav me-auto">
<li class="nav-item"><a class="nav-link" href="soloadmin.php">🥐 Productos</a></li>
<li class="nav-item"><a class="nav-link" href="historial_ventas.php">📋 Historial</a></li>
<li class="nav-item"><a class="nav-link" href="reporte_ventas.php">📊 Reporte</a></li>
<li class="nav-item"><a class="nav-link" href="estadisticas.php">📈 Estadísticas</a></li>
<li class="nav-item"><a class="nav-link" href="crear_usuario.php">👤 Crear Usuarios</a></li>
</ul>

<!-- 👇 USUARIO + AYUDA + TEMA -->
<div class="d-flex gap-2 align-items-center">
<span>👤 <?= $_SESSION['usuario']; ?></span>



<button class="btn btn-outline-dark btn-sm" onclick="cambiarTema()">🌙</button>
<!-- BOTÓN AYUDA -->
<button class="btn btn-info btn-sm"
data-bs-toggle="modal"
data-bs-target="#ayudaModal">
❓ Ayuda
</button>
<a href="logout.php" class="btn btn-danger btn-sm">Salir</a>
</div>

</div>
</div>
</nav>

<!-- LOGOS -->
<img src="imagenes/migaapp.png" class="logo-admin izq">
<img src="imagenes/migaapp.png" class="logo-admin der">

<!-- CONTENIDO -->
<div class="container mt-5 pt-4">
<div class="card p-4 shadow">

<h2>🧁 Productos</h2>

<!-- FORM -->
<form method="POST" action="agregar_pan.php" class="row g-2 mb-4">

<input type="text" name="nombre" class="form-control col" placeholder="Nombre" required>
<input type="number" step="0.01" name="precio" class="form-control col" placeholder="Precio" required>
<input type="text" name="descripcion" class="form-control col" placeholder="Descripción">

<input 
    type="search" 
    id="buscadorTabla"
    class="form-control form-control-sm col"
    placeholder="🔎 Buscar productos..."
>

<div class="col-auto">
<button class="btn btn-success btn-sm px-3">Agregar</button>
</div>

</form>

<!-- TABLA -->
<table id="tabla" class="table table-striped table-bordered">

<thead class="table-primary">
<tr>
<th>ID</th>
<th>Nombre</th>
<th>Precio</th>
<th>Descripción</th>
<th>Acciones</th>
</tr>
</thead>

<tbody>
<?php foreach($datos as $fila): ?>
<tr>
<td><?= $fila["id_pan"]; ?></td>
<td><?= htmlspecialchars($fila["nombre"]); ?></td>
<td>$<?= number_format($fila["precio"],2); ?></td>
<td><?= htmlspecialchars($fila["descripcion"]); ?></td>
<td>

<button class="btn btn-danger btn-sm"
onclick="abrirModal('<?= $fila['id_pan']; ?>','<?= htmlspecialchars($fila['nombre']); ?>')">
Eliminar
</button>

<form method="GET" action="editar_precio.php" style="display:inline;">
<input type="hidden" name="id_pan" value="<?= $fila["id_pan"]; ?>">
<button class="btn btn-warning btn-sm">Editar</button>
</form>

</td>
</tr>
<?php endforeach; ?>
</tbody>

</table>

</div>
</div>

<!-- 📚 MODAL AYUDA (CORRECTO) -->
<div class="modal fade" id="ayudaModal" tabindex="-1">
<div class="modal-dialog modal-lg modal-dialog-centered">
<div class="modal-content">

<div class="modal-header bg-primary text-white">
<h5 class="modal-title">📚 Sistema de ayuda</h5>
<button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<h5>🧁 Productos</h5>
<p>En esta pantalla puedes administrar los productos de la panadería.</p>

<hr>

<h5>➕ Agregar producto</h5>
<p>Llena los campos y presiona “Agregar”.</p>

<hr>

<h5>🔎 Buscar</h5>
<p>Usa el buscador para filtrar productos en la tabla.</p>

<hr>

<h5>✏️ Editar / 🗑️ Eliminar</h5>
<p>Usa los botones dentro de cada fila.</p>

<hr>

<h5>🌙 Tema</h5>
<p>Cambia entre modo claro y oscuro con el botón 🌙</p>

</div>

<div class="modal-footer">
<button class="btn btn-primary" data-bs-dismiss="modal">Entendido</button>
</div>

</div>
</div>
</div>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.datatables.net/2.3.0/js/dataTables.min.js"></script>

<script>
let tabla = new DataTable('#tabla', {
    dom: 'lrtip'
});

document.getElementById("buscadorTabla").addEventListener("keyup", function () {
    tabla.search(this.value).draw();
});

function abrirModal(id,nombre){
document.getElementById("idPan").value=id;
document.getElementById("nombrePan").innerText=nombre;
new bootstrap.Modal(document.getElementById('modalEliminar')).show();
}

function cambiarTema(){
let body = document.body;
body.classList.toggle("dark");
fetch("guardar_tema.php?tema=" + (body.classList.contains("dark") ? "dark" : "light"));
}
</script>

</body>
</html>