<?php
session_start();
if (isset($_GET['tema'])) {
    $temasPermitidos = ['light', 'dark', 'primary', 'success', 'danger'];
    if (in_array($_GET['tema'], $temasPermitidos)) {
        $_SESSION['tema'] = $_GET['tema'];
        // Opcional: guardar también en cookie para persistencia
        setcookie('tema', $_GET['tema'], time() + (86400 * 30), "/"); // 30 días
    }
}
echo "OK";
?>