<?php
session_start();
require_once "MySQLAux.php";

/* -------------------- Seguridad -------------------- */
if (!isset($_SESSION["usuario_valida"]) || $_SESSION["tipo_usuario"] !== "admin") {
    die("❌ Acceso denegado.");
}

/* -------------------- Conexión -------------------- */
$mysql = new MySQLAux("localhost", "pruebas", "root", "root");

/* -------------------- Eliminar -------------------- */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id_pan"])) {

    $id = $_POST["id_pan"];

    $query = "DELETE FROM panes WHERE id_pan = ?";
    $stmt = $mysql->conn->prepare($query);
    $stmt->execute([$id]);
}

/* -------------------- Redirección -------------------- */
header("Location: soloadmin.php");
exit();
?>