<?php
session_start();

// 🧹 Limpiar sesión
session_unset();
session_destroy();

// 🔥 ELIMINAR COOKIE (AQUÍ ESTÁ EL FIX)
if (isset($_COOKIE['usuario'])) {
    setcookie('usuario', '', time() - 3600, '/');
}

// 🔄 Redirigir
header("Location: login.php");
exit();
?>