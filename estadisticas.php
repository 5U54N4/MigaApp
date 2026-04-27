<?php
session_start();

if (!isset($_SESSION["usuario_valida"])) {
    header("Location: login.php");
    exit;
}

if(!isset($_SESSION['tema'])){
    $_SESSION['tema'] = 'light';
}

require_once "MySQLAux.php";
$mysql = new MySQLAux("localhost","pruebas","root","root");

/* FILTROS */
$inicio = $_GET["inicio"] ?? "";
$fin    = $_GET["fin"] ?? "";
$empleado = $_GET["empleado"] ?? "";
$pago     = $_GET["pago"] ?? "";
$tipo     = $_GET["tipo"] ?? "panes";

/* EMPLEADOS */
$listaEmpleados = $mysql->conn->query("SELECT nombre FROM usuarios")->fetchAll(PDO::FETCH_ASSOC);

/* WHERE */
$where = [];
$params = [];

if($inicio && $fin){
    $where[] = "DATE(v.fecha) BETWEEN ? AND ?";
    $params[] = $inicio;
    $params[] = $fin;
}
if($empleado){
    $where[] = "u.nombre = ?";
    $params[] = $empleado;
}
if($pago){
    $where[] = "v.forma_pago = ?";
    $params[] = $pago;
}

$whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";

/* PANES */
$stmt = $mysql->conn->prepare("
SELECT p.nombre, SUM(d.cantidad) as total
FROM detalle_venta d
JOIN panes p ON d.id_pan = p.id_pan
JOIN ventas v ON d.id_venta = v.id_venta
JOIN usuarios u ON v.id_usuario = u.id_usuario
$whereSQL
GROUP BY p.id_pan, p.nombre
ORDER BY total DESC
");
$stmt->execute($params);
$panes = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* EMPLEADOS */
$stmt = $mysql->conn->prepare("
SELECT u.nombre, COUNT(v.id_venta) as total
FROM ventas v
JOIN usuarios u ON v.id_usuario = u.id_usuario
$whereSQL
GROUP BY u.nombre
");
$stmt->execute($params);
$empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* PAGOS */
$stmt = $mysql->conn->prepare("
SELECT v.forma_pago, COUNT(*) as total
FROM ventas v
JOIN usuarios u ON v.id_usuario = u.id_usuario
$whereSQL
GROUP BY v.forma_pago
");
$stmt->execute($params);
$pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* TOTAL */
$stmt = $mysql->conn->prepare("
SELECT SUM(v.total) as total
FROM ventas v
JOIN usuarios u ON v.id_usuario = u.id_usuario
$whereSQL
");
$stmt->execute($params);
$total = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Estadísticas</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<style>
body { background:#fff8e1; }
body.dark { background:#212529; color:#fff; }

/* NAVBAR */
.navbar-custom { background:#f5f0e6; }
body.dark .navbar-custom { background:#2c3034; }
body.dark .navbar-custom * { color:#fff !important; }

/* LOGOS LATERALES */
.logo-admin{
    position: fixed;
    top: 100px;
    width: 90px;
    opacity: 0.85;
    z-index: 1;
}

.logo-admin.izq{ left:20px; }
.logo-admin.der{ right:20px; }

/* CONTENIDO ENCIMA */
.container, .card{
    position: relative;
    z-index: 2;
}

/* TITULOS */
.titulo { text-align:center; font-size:26px; margin:20px; }
.real { text-align:center; color:green; font-weight:bold; }

/* MODO OSCURO LOGOS */
body.dark .logo-admin{
    opacity: 0.6;
}

/* OCULTAR EN IMPRESIÓN */
@media print {
    .logo-admin{ display:none; }
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
<li class="nav-item"><a class="nav-link" href="crear_usuario.php">👤 Crear Usuarios</a></li>
</ul>

<div class="d-flex gap-2">
<span><?= $_SESSION['usuario']; ?></span>
<button class="btn btn-outline-dark btn-sm" onclick="tema()">🌙</button>
<button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#ayuda">❓</button>
<a href="logout.php" class="btn btn-danger btn-sm">Salir</a>
</div>
</div>
</nav>

<!-- LOGOS -->
<img src="imagenes/migaapp.png" class="logo-admin izq">
<img src="imagenes/migaapp.png" class="logo-admin der">

<div class="container mt-5 pt-4">
<div class="card p-3">

<div class="titulo">📊 Estadísticas</div>
<div class="real">🟢 Actualizado en tiempo real</div>
<br>

<form method="GET" class="row g-2 mb-3">

<input type="date" name="inicio" class="form-control col" value="<?= $inicio ?>">
<input type="date" name="fin" class="form-control col" value="<?= $fin ?>">

<select name="empleado" class="form-control col">
<option value="">Todos</option>
<?php foreach($listaEmpleados as $e): ?>
<option value="<?= $e['nombre'] ?>" <?= $empleado==$e['nombre']?'selected':'' ?>>
<?= $e['nombre'] ?>
</option>
<?php endforeach; ?>
</select>

<input type="text" name="pago" placeholder="Método de pago" class="form-control col" value="<?= $pago ?>">

<select name="tipo" class="form-control col">
<option value="panes" <?= $tipo=="panes"?"selected":"" ?>>Panes</option>
<option value="empleados" <?= $tipo=="empleados"?"selected":"" ?>>Empleados</option>
<option value="pagos" <?= $tipo=="pagos"?"selected":"" ?>>Pagos</option>
</select>

<button class="btn btn-primary col-2">Consultar</button>
</form>

<!-- BOTONES -->
<div class="d-flex justify-content-end gap-2 mb-3">
<button class="btn btn-success btn-sm" onclick="imprimirBonito()">🖨️ Imprimir</button>
<button class="btn btn-danger btn-sm" onclick="pdf()">📥 PDF</button>
</div>

<div id="area">
<h4 class="text-center">💰 Total: $<?= number_format($total["total"] ?? 0,2) ?></h4>
<canvas id="grafica"></canvas>
</div>

</div>
</div>

<script>
let tipo = "<?= $tipo ?>";

let config = {};

if(tipo==="panes"){
config={type:'bar',data:{labels:<?= json_encode(array_column($panes,'nombre')) ?>,datasets:[{data:<?= json_encode(array_column($panes,'total')) ?>}]}}
}
if(tipo==="empleados"){
config={type:'bar',data:{labels:<?= json_encode(array_column($empleados,'nombre')) ?>,datasets:[{data:<?= json_encode(array_column($empleados,'total')) ?>}]}}
}
if(tipo==="pagos"){
config={type:'pie',data:{labels:<?= json_encode(array_column($pagos,'forma_pago')) ?>,datasets:[{data:<?= json_encode(array_column($pagos,'total')) ?>}]}}
}

new Chart(document.getElementById('grafica'), config);

/* TEMA */
function tema(){
document.body.classList.toggle("dark");
fetch("guardar_tema.php?tema="+(document.body.classList.contains("dark")?"dark":"light"));
}

/* PDF */
async function pdf(){
const { jsPDF } = window.jspdf;
const canvas = await html2canvas(document.getElementById("area"));
const pdf = new jsPDF();
pdf.addImage(canvas.toDataURL("image/png"),"PNG",10,10,190,0);
pdf.save("estadisticas.pdf");
}

/* AUTO REFRESH */
setInterval(()=>location.reload(),30000);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>