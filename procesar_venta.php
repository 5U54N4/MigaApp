<?php 
session_start();

/* TEMA */
if(!isset($_SESSION['tema'])) {
    $_SESSION['tema'] = 'light';
}

/* SEGURIDAD */
if (!isset($_SESSION["usuario_valida"]) || $_SESSION["tipo_usuario"] !== "empleado") {
    header("Location: login.php");
    exit;
}

require_once "MySQLAux.php";

$mysql = new MySQLAux("localhost", "pruebas", "root", "root");
$pdo = $mysql->conn;

$id_usuario = $_SESSION["usuario_id"];

/* VALIDACIÓN */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: solousuario.php");
    exit;
}

$cantidades = $_POST["cantidades"] ?? [];
$metodo_pago = $_POST["metodo_pago"] ?? "No especificado";

if (empty($cantidades)) {
    die("No se enviaron productos.");
}

/* OBTENER PRODUCTOS */
$panes = $mysql->obtenTodasLasFilas("panes");

$precios = [];
$nombres = [];

foreach ($panes as $pan) {
    $precios[$pan["id_pan"]] = $pan["precio"];
    $nombres[$pan["id_pan"]] = $pan["nombre"];
}

/* CALCULAR VENTA */
$totalVenta = 0;
$detalleVenta = [];

foreach ($cantidades as $id_pan => $cantidad) {
    if ($cantidad > 0 && isset($precios[$id_pan])) {

        $subtotal = $cantidad * $precios[$id_pan];
        $totalVenta += $subtotal;

        $detalleVenta[] = [
            "id_pan" => $id_pan,
            "nombre" => $nombres[$id_pan],
            "cantidad" => $cantidad,
            "precio" => $precios[$id_pan],
            "subtotal" => $subtotal
        ];
    }
}

if ($totalVenta <= 0) {
    die("No se registró ningún pan en la venta.");
}

/* INSERT VENTA */
$stmt = $pdo->prepare("
    INSERT INTO ventas (id_usuario, total, forma_pago) 
    VALUES (?, ?, ?)
");
$stmt->execute([$id_usuario, $totalVenta, $metodo_pago]);

$id_venta = $pdo->lastInsertId();

/* INSERT DETALLE */
foreach ($detalleVenta as $item) {
    $stmt = $pdo->prepare("
        INSERT INTO detalle_venta (id_venta, id_pan, cantidad, precio_unitario) 
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([
        $id_venta,
        $item["id_pan"],
        $item["cantidad"],
        $item["precio"]
    ]);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Venta registrada - MigaApp</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
:root {
    --cafe: #6f4e37;
    --beige: #f8f1e7;
    --dorado: #d4a373;
}

body { background-color: var(--beige); }
body.dark { background-color: #212529; color:#fff; }

.card { border-radius:18px; }

.logo { width:90px; margin-bottom:15px; }

.text-cafe { color:var(--cafe); }

.btn-pan { background:var(--cafe); color:white; }
.btn-pan:hover { background:var(--dorado); }
</style>
</head>

<body class="<?= $_SESSION['tema']; ?>">

<div class="container d-flex justify-content-center align-items-center min-vh-100">
<div class="col-lg-9">

<div class="card shadow-lg p-4">

<div class="text-center">
<img src="imagenes/migaapp.png" class="logo">
</div>

<h2 class="text-center text-success mb-4">
✅ Venta registrada con éxito
</h2>

<div class="row mb-4">
<div class="col-md-6">
<p><strong>📅 Fecha:</strong> <?= date('d/m/Y'); ?></p>
<p><strong>👤 Empleado:</strong> <?= $_SESSION["usuario"]; ?></p>
</div>

<div class="col-md-6 text-end">
<p class="h4 text-cafe">
<strong>Total:</strong> $<?= number_format($totalVenta, 2); ?>
</p>

<p>
<strong>💳 Método de pago:</strong> <?= htmlspecialchars($metodo_pago); ?>
</p>
</div>
</div>

<h4 class="mb-3 text-cafe">🧾 Detalle de la venta</h4>

<table class="table table-bordered">
<thead class="table-primary">
<tr>
<th>Pan</th>
<th>Cantidad</th>
<th>Precio</th>
<th>Subtotal</th>
</tr>
</thead>

<tbody>
<?php foreach ($detalleVenta as $item): ?>
<tr>
<td><?= htmlspecialchars($item['nombre']) ?></td>
<td><?= $item['cantidad'] ?></td>
<td>$<?= number_format($item['precio'],2) ?></td>
<td>$<?= number_format($item['subtotal'],2) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<div class="d-flex justify-content-between mt-4">

<form method="post" action="generar_pdf.php" target="_blank">
<input type="hidden" name="detalle_venta" value='<?= htmlspecialchars(json_encode($detalleVenta)); ?>'>
<input type="hidden" name="totalVenta" value="<?= $totalVenta; ?>">
<input type="hidden" name="metodo_pago" value="<?= htmlspecialchars($metodo_pago); ?>">
<button class="btn btn-outline-secondary">🖨️ Imprimir</button>
</form>

<a href="solousuario.php" class="btn btn-pan">
🛒 Nueva venta
</a>

</div>

</div>
</div>
</div>

</body>
</html>