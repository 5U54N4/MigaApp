<?php
session_start();

// 🔐 Validación correcta
if (!isset($_SESSION["usuario_valida"]) || $_SESSION["tipo_usuario"] !== "admin") {
    die("❌ Acceso denegado.");
}

require_once "MySQLAux.php";

$mysql = new MySQLAux("localhost", "pruebas", "root", "root");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = $_POST["nombre"];
    $precio = $_POST["precio"];
    $descripcion = $_POST["descripcion"];

    if ($mysql->insertarPan($nombre, $precio, $descripcion)) {
        header("Location: soloadmin.php");
        exit;
    } else {
        echo "❌ Error al insertar pan.";
    }
}
?>



