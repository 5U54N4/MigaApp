<?php
session_start();
require_once "MySQLAux.php";
require_once "Utils/Html2Pdf/Html2Pdf.php";

// Verificar que los datos necesarios estén presentes
if (!isset($_POST['detalle_venta']) || !isset($_POST['totalVenta'])) {
    die("Datos incompletos para generar el PDF");
}

// Decodificar los datos del POST
$detalleVenta = json_decode($_POST['detalle_venta'], true);
$totalVenta = $_POST['totalVenta'];

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Venta</title>
    <style>
        body { font-family: Arial, sans-serif; }
        h2 { color: #28a745; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total { font-weight: bold; font-size: 1.2em; }
    </style>
</head>
<body>
    <h2>Comprobante de Venta</h2>
    <p><strong>Fecha: </strong><?php echo date('d/m/Y'); ?></p>
    <p><strong>Empleado: </strong><?php echo $_SESSION["usuario_nombre"] ?? ''; ?></p>
    <p class="total"><strong>Total: $<?php echo number_format($totalVenta, 2); ?></strong></p>

    <h3>Detalle de la venta:</h3>
    <table>
        <thead>
            <tr>
                <th>Pan</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($detalleVenta as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                <td><?php echo $item['cantidad']; ?></td>
                <td>$<?php echo number_format($item['precio'], 2); ?></td>
                <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
<?php
$html = ob_get_clean();

try {
    $html2pdf = new Spipu\Html2Pdf\Html2Pdf('P', 'A4', 'es');
    $html2pdf->writeHTML($html);
    $html2pdf->output('venta_'.date('Ymd_His').'.pdf');
    exit;
} catch (Spipu\Html2Pdf\Exception\Html2PdfException $e) {
    $html2pdf->clean();
    die("Error al generar PDF: " . $e->getMessage());
}
?>