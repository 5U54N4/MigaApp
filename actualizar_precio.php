<?php
session_start();
require_once "MySQLAux.php";

// 🔐 Validación correcta
if (!isset($_SESSION["usuario_valida"]) || $_SESSION["tipo_usuario"] !== "admin") {
    die("❌ Acceso denegado.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id = $_POST["id_pan"];
    $nuevo_precio = $_POST["precio"];

    $mysql = new MySQLAux("localhost", "pruebas", "root", "root");

    $query = "UPDATE panes SET precio = ? WHERE id_pan = ?";
    $stmt = $mysql->conn->prepare($query);

    $success = $stmt->execute([$nuevo_precio, $id]);

    if ($success) {
        header("Location: soloadmin.php");
        exit;
    } else {
        echo "❌ Error al actualizar el precio.";
    }
}
?>
