<?php
session_start();

// -------------------- CONEXIÓN --------------------
function conectarDB() {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=pruebas;charset=utf8", "root", "root");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Error de conexión");
    }
}

// -------------------- VALIDAR USUARIO --------------------
function validarUsuario($usuario, $clave) {
    $pdo = conectarDB();

    $stmt = $pdo->prepare(
        "SELECT * FROM usuarios 
         WHERE usuario = :usuario 
         AND clave = MD5(:clave)"
    );

    $stmt->execute([
        ':usuario' => $usuario,
        ':clave'   => $clave
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// -------------------- SESIÓN Y COOKIE --------------------
function haySesionValida() {
    return isset($_SESSION['usuario_valida']) && $_SESSION['usuario_valida'] === true;
}

function hayCookie() {
    return isset($_COOKIE['usuario']);
}

function crearSesion($usuario) {
    $_SESSION['usuario'] = $usuario['usuario'];
    $_SESSION['usuario_valida'] = true;
    $_SESSION['usuario_id'] = $usuario['id_usuario'];
    $_SESSION['tipo_usuario'] = $usuario['tipo']; // admin o empleado
}

function crearCookie($usuario) {
    setcookie('usuario', $usuario['usuario'], time() + (7 * 24 * 60 * 60), "/");
}

function borrarCookie() {
    setcookie('usuario', '', time() - 3600, "/");
}

function redirigirInicio() {
    if ($_SESSION['tipo_usuario'] === 'admin') {
        header("Location: soloadmin.php");
    } else {
        header("Location: solousuario.php");
    }
    exit();
}

// -------------------- AUTO LOGIN POR COOKIE --------------------
if (!haySesionValida() && hayCookie()) {
    $usuarioCookie = $_COOKIE['usuario'];
    $pdo = conectarDB();

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = :usuario");
    $stmt->execute([':usuario' => $usuarioCookie]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        crearSesion($usuario);
        redirigirInicio();
    } else {
        borrarCookie();
    }
}

// -------------------- LOGIN MANUAL --------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuarioInput = $_POST['nombre'] ?? '';
    $claveInput   = $_POST['clave'] ?? '';
    $recordar     = isset($_POST['recordar']);

    $usuario = validarUsuario($usuarioInput, $claveInput);

    if ($usuario) {
        crearSesion($usuario);

        if ($recordar) {
            crearCookie($usuario);
        } else {
            borrarCookie();
        }

        redirigirInicio();
    } else {
        $mensaje = "❌ Usuario o contraseña incorrectos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión - MigaApp</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CSS propio -->
    <link href="wwwroot/css/site.css" rel="stylesheet">
</head>

<body>

<img src="imagenes/migaapp.png" class="img-izquierda" alt="Logo izquierda">
<img src="imagenes/migaapp.png" class="img-derecha" alt="Logo derecha">

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card">
            <div class="card-body p-4">

                <h2 class="login-title">🕊️ MigaApp</h2>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Usuario</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="clave" class="form-control" required>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="recordar" id="recordar">
                        <label class="form-check-label" for="recordar">Recordarme</label>
                    </div>

                    <button class="btn btn-panaderia w-100 py-2">
                        Iniciar sesión
                    </button>
                </form>

                <?php if (!empty($mensaje)): ?>
                    <div class="alert alert-danger text-center mt-3">
                        <?= $mensaje ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>