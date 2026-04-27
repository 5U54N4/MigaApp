<?php
require_once "MySQLAux.php";
$mysql = new MySQLAux("localhost","pruebas","root","root");

$id = $_GET["id"];

// DETALLE
$query = "SELECT p.nombre, d.cantidad, d.precio_unitario,
                 (d.cantidad * d.precio_unitario) as subtotal
          FROM detalle_venta d
          JOIN panes p ON d.id_pan = p.id_pan
          WHERE d.id_venta = ?";
$stmt = $mysql->conn->prepare($query);
$stmt->execute([$id]);
$detalle = $stmt->fetchAll(PDO::FETCH_ASSOC);

// TOTAL
$query2 = "SELECT total, forma_pago, fecha 
           FROM ventas WHERE id_venta = ?";
$stmt2 = $mysql->conn->prepare($query2);
$stmt2->execute([$id]);
$venta = $stmt2->fetch(PDO::FETCH_ASSOC);
?>

<h3>Detalle de venta #<?= $id ?></h3>
<p>Fecha: <?= $venta["fecha"] ?></p>
<p>Pago: <?= $venta["forma_pago"] ?></p>

<table border="1" width="100%">
<tr>
<th>Producto</th>
<th>Cantidad</th>
<th>Precio</th>
<th>Subtotal</th>
</tr>

<?php foreach($detalle as $d): ?>
<tr>
<td><?= $d["nombre"] ?></td>
<td><?= $d["cantidad"] ?></td>
<td>$<?= number_format($d["precio_unitario"],2) ?></td>
<td>$<?= number_format($d["subtotal"],2) ?></td>
</tr>
<?php endforeach; ?>

<tr>
<th colspan="3">TOTAL</th>
<th>$<?= number_format($venta["total"],2) ?></th>
</tr>

</table>