<?php 
session_start();

if (!isset($_SESSION["usuario_valida"]) || $_SESSION["tipo_usuario"] !== "empleado") {
    header("Location: login.php");
    exit;
}

if(!isset($_SESSION['tema'])){
    $_SESSION['tema'] = 'light';
}

require_once "MySQLAux.php";
$mysql = new MySQLAux("localhost","pruebas","root","root");

$id_usuario = $_SESSION["usuario_id"];

/* FILTROS */
$inicio = $_GET["inicio"] ?? "";
$fin    = $_GET["fin"] ?? "";
$pago   = $_GET["pago"] ?? "";

/* WHERE DINÁMICO */
$where = ["v.id_usuario = ?"];
$params = [$id_usuario];

if($inicio && $fin){
    $where[] = "DATE(v.fecha) BETWEEN ? AND ?";
    $params[] = $inicio;
    $params[] = $fin;
}

if($pago){
    $where[] = "v.forma_pago = ?";
    $params[] = $pago;
}

$whereSQL = "WHERE " . implode(" AND ", $where);

/* PANES MÁS VENDIDOS */
$stmt = $mysql->conn->prepare("
SELECT p.nombre, SUM(d.cantidad) as total
FROM detalle_venta d
JOIN panes p ON d.id_pan = p.id_pan
JOIN ventas v ON d.id_venta = v.id_venta
$whereSQL
GROUP BY p.nombre
ORDER BY total DESC
");
$stmt->execute($params);
$panes = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* MÉTODOS DE PAGO */
$stmt = $mysql->conn->prepare("
SELECT v.forma_pago, COUNT(*) as total
FROM ventas v
$whereSQL
GROUP BY v.forma_pago
");
$stmt->execute($params);
$pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* INGRESO TOTAL */
$stmt = $mysql->conn->prepare("
SELECT SUM(v.total) as total
FROM ventas v
$whereSQL
");
$stmt->execute($params);
$total = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mis estadísticas</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body { background: #fff8e1;; }
body.dark { background: #212529; color: #fff;; }

.navbar-custom { background:#f5f0e6; }
body.dark .navbar-custom { background: #ffffffff; }
body.dark .navbar-custom * { color:#fff !important; }

.card { background:#fff; }
.titulo { text-align:center; font-size:24px; margin:15px; }
.logo { position:fixed; top:100px; width:80px; opacity:0.7; }
.izq { left:15px; } .der { right:15px; }
</style>
</head>

<body class="<?= $_SESSION['tema']; ?>">

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-custom fixed-top shadow">
<div class="container-fluid">

<a class="navbar-brand fw-bold">🕊️ MigaApp</a>

<ul class="navbar-nav me-auto">
<li class="nav-item"><a class="nav-link" href="solousuario.php">💰Registrar Venta</a></li>
<li class="nav-item"><a class="nav-link" href="historial_ventas.php">📋 Historial</a></li>
<li class="nav-item"><a class="nav-link active" href="#">📊 Mis Estadísticas</a></li>
</ul>

<div class="d-flex gap-2">
<span>👤 <?= $_SESSION['usuario']; ?></span>
<button class="btn btn-outline-dark btn-sm" onclick="tema()">🌙</button>
<a href="logout.php" class="btn btn-danger btn-sm">Salir</a>
</div>

</div>
</nav>

<img src="imagenes/migaapp.png" class="logo izq">
<img src="imagenes/migaapp.png" class="logo der">

<div class="container mt-5 pt-4">
<div class="card p-4 shadow">

<div class="titulo">📊 Mis estadísticas</div>

<!-- FILTROS -->
<form method="GET" class="row g-2 mb-3">
<input type="date" name="inicio" value="<?= $inicio ?>" class="form-control col">
<input type="date" name="fin" value="<?= $fin ?>" class="form-control col">

<select name="pago" class="form-control col">
<option value="">Todos los métodos</option>
<option <?= $pago=="Efectivo"?"selected":"" ?>>Efectivo</option>
<option <?= $pago=="Tarjeta"?"selected":"" ?>>Tarjeta</option>
<option <?= $pago=="Transferencia"?"selected":"" ?>>Transferencia</option>
</select>

<button class="btn btn-primary col-2">Filtrar</button>
</form>

<h4 class="text-center">💰 Total generado: $<?= number_format($total["total"] ?? 0,2) ?></h4>

<canvas id="graficaPanes"></canvas>
<canvas id="graficaPagos" class="mt-4"></canvas>

</div>
</div>

<script>
/* PANES */
new Chart(document.getElementById("graficaPanes"), {
type:'bar',
data:{
labels: <?= json_encode(array_column($panes,'nombre')) ?>,
datasets:[{label:'Panes vendidos',data:<?= json_encode(array_column($panes,'total')) ?>}]
}
});

/* PAGOS */
new Chart(document.getElementById("graficaPagos"), {
type:'pie',
data:{
labels: <?= json_encode(array_column($pagos,'forma_pago')) ?>,
datasets:[{data:<?= json_encode(array_column($pagos,'total')) ?>}]
}
});

/* TEMA */
function tema(){
document.body.classList.toggle("dark");
fetch("guardar_tema.php?tema="+(document.body.classList.contains("dark")?"dark":"light"));
}
</script>

</body>
</html>