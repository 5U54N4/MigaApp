<?php 
session_start();

if (!isset($_SESSION["usuario_valida"]) || $_SESSION["tipo_usuario"] !== "admin") {
    header("Location: login.php");
    exit;
}

if(!isset($_SESSION['tema'])) {
    $_SESSION['tema'] = 'light';
}

require_once "MySQLAux.php";
$mysql = new MySQLAux("localhost","pruebas","root","root");

$inicio = $_GET["inicio"] ?? "";
$fin    = $_GET["fin"] ?? "";

$ventas = [];
$total_general = 0;

if($inicio && $fin){

    $query = "SELECT v.id_venta, v.total, v.fecha, 
                     u.nombre AS empleado, v.forma_pago
              FROM ventas v
              JOIN usuarios u ON v.id_usuario = u.id_usuario
              WHERE DATE(v.fecha) BETWEEN ? AND ?
              ORDER BY v.fecha ASC";

    $stmt = $mysql->conn->prepare($query);
    $stmt->execute([$inicio,$fin]);
    $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach($ventas as $v){
        $total_general += $v["total"];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de ventas</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<style>
body { background:#fff8e1; }
body.dark { background:#212529; color:#fff; }

.navbar-custom { background:#f5f0e6; }
body.dark .navbar-custom { background:#2c3034; }

.logo-admin {
    position: fixed;
    top: 90px;
    width: 110px;
    opacity: 0.8;
}
.logo-admin.izq { left:15px; }
.logo-admin.der { right:15px; }

.titulo {
    text-align:center;
    font-size:26px;
    margin-bottom:20px;
    font-weight:bold;
}

.filtro {
    display:flex;
    justify-content:center;
    gap:10px;
    margin-bottom:20px;
}

.total-periodo {
    text-align:center;
    font-weight:bold;
    margin-bottom:15px;
}
/* NAVBAR MODO OSCURO COMPLETO */
body.dark .navbar-custom {
    background-color: #2c3034 !important;
}

/* TEXTO DEL NAVBAR */
body.dark .navbar-custom .nav-link,
body.dark .navbar-custom .navbar-brand,
body.dark .navbar-custom span {
    color: #ffffff !important;
}

/* HOVER */
body.dark .navbar-custom .nav-link:hover {
    color: #ffc107 !important;
}

/* BOTONES */
body.dark .navbar-custom .btn {
    color: #ffffff !important;
    border-color: #ffffff !important;
}

/* BOTÓN SALIR (opcional ajuste) */
body.dark .btn-danger {
    border-color: #ff4d4d !important;
}

@media print {
    .navbar, .logo-admin, .btn, .filtro, canvas {
        display:none !important;
    }
}
</style>
</head>

<body class="<?= $_SESSION['tema']; ?>">

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-custom fixed-top shadow">
<div class="container-fluid">

<a class="navbar-brand fw-bold">🕊️ MigaApp</a>

<ul class="navbar-nav me-auto">
<li class="nav-item"><a class="nav-link" href="soloadmin.php">🥐 Productos</a></li>
<li class="nav-item"><a class="nav-link" href="historial_ventas.php">📋 Historial</a></li>
<li class="nav-item"><a class="nav-link" href="reporte_ventas.php">📊 Reporte</a></li>
<li class="nav-item"><a class="nav-link" href="estadisticas.php">📈 Estadísticas</a></li>
<li class="nav-item"><a class="nav-link" href="crear_usuario.php">👤Crear Usuarios</a></li>
</ul>

<div class="d-flex gap-2">
<span>👤 <?= $_SESSION['usuario']; ?></span>

<!-- BOTÓN AYUDA -->

<button class="btn btn-outline-dark btn-sm" onclick="cambiarTema()">🌙</button>
<button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#modalAyuda">❓</button>

<a href="logout.php" class="btn btn-danger btn-sm">Salir</a>
</div>

</div>
</nav>

<!-- LOGOS -->
<img src="imagenes/migaapp.png" class="logo-admin izq">
<img src="imagenes/migaapp.png" class="logo-admin der">

<div class="container mt-5 pt-4">
<div class="card p-4 shadow">

<div class="titulo">📊 Reporte de ventas</div>

<!-- MENSAJE DE AYUDA AUTOMÁTICO -->
<?php if(!$inicio || !$fin): ?>
<div class="alert alert-info text-center">
Selecciona un rango de fechas para generar el reporte 📅
</div>
<?php endif; ?>

<form method="GET" class="filtro">
<input type="date" name="inicio" value="<?= $inicio ?>" class="form-control">
<input type="date" name="fin" value="<?= $fin ?>" class="form-control">
<button class="btn btn-primary btn-sm">Generar</button>
</form>

<?php if($inicio && $fin): ?>
<div id="reporteArea">

<h5 class="total-periodo">
Total del periodo: <b>$<?= number_format($total_general,2) ?></b>
</h5>

<div class="d-flex gap-2 mb-3">
<button class="btn btn-success btn-sm" onclick="window.print()">🖨️ Imprimir</button>
<button class="btn btn-danger btn-sm" onclick="descargarPDF()">📥 PDF</button>
</div>

<table class="table table-bordered table-striped">
<thead class="table-primary">
<tr>
<th>Fecha</th>
<th>No. Venta</th>
<th>Empleado</th>
<th>Forma de pago</th>
<th>Total</th>
</tr>
</thead>

<tbody>
<?php foreach($ventas as $v): ?>
<tr>
<td><?= date("d/m/Y", strtotime($v["fecha"])) ?></td>
<td><?= $v["id_venta"] ?></td>
<td><?= $v["empleado"] ?></td>
<td><?= $v["forma_pago"] ?></td>
<td>$<?= number_format($v["total"],2) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<canvas id="grafica"></canvas>

<script>
let labels = <?= json_encode(array_column($ventas,'fecha')) ?>;
let data = <?= json_encode(array_column($ventas,'total')) ?>;

new Chart(document.getElementById('grafica'), {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Ventas',
            data: data
        }]
    }
});
</script>

</div>
<?php endif; ?>

</div>
</div>

<!-- MODAL AYUDA -->
<div class="modal fade" id="modalAyuda">
<div class="modal-dialog modal-lg">
<div class="modal-content">

<div class="modal-header">
<h5 class="modal-title">📊 Ayuda - Reporte</h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<h6>¿Qué puedes hacer?</h6>
<ul>
<li>Ver ventas por fechas</li>
<li>Consultar total</li>
<li>Ver gráfica</li>
<li>Imprimir o descargar PDF</li>
</ul>

<h6>¿Cómo usarlo?</h6>
<ol>
<li>Selecciona fechas</li>
<li>Presiona Generar</li>
<li>Revisa resultados</li>
</ol>
</div>

<div class="modal-footer">
<button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
</div>

</div>
</div>
</div>

<script>
function cambiarTema(){
    let body = document.body;
    body.classList.toggle("dark");
    fetch("guardar_tema.php?tema=" + (body.classList.contains("dark") ? "dark":"light"));
}

async function descargarPDF(){
    const { jsPDF } = window.jspdf;
    const contenido = document.getElementById("reporteArea");

    const canvas = await html2canvas(contenido);
    const imgData = canvas.toDataURL("image/png");

    const pdf = new jsPDF();
    pdf.addImage(imgData, "PNG", 10, 10, 190, 0);
    pdf.save("reporte_ventas.pdf");
}
</script>

<!-- BOOTSTRAP JS (IMPORTANTE) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>