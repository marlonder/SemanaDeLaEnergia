<?php
require_once __DIR__ . '/Premios_a_la_Excelencia/config/auth.php';

require_once __DIR__ . '/Premios_a_la_Excelencia/controller/Usuariocontroller.php';

// Si ya hay sesión activa, ir directo al listado
if (estaLogueado()) {
    header('Location: Premios_a_la_Excelencia/views/usuarios/Listar_usuarios.php');
    exit;
}

$controller = new UsuarioController();
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombres = $_POST['nombres'] ?? '';
    $password = $_POST['password'] ?? '';

    $resultado = $controller->login($nombres, $password);

    if ($resultado['ok']) {
        $_SESSION['usuario_id'] = $resultado['usuario']['id'];
        $_SESSION['usuario_nombres'] = $resultado['usuario']['nombres'];

        header('Location: Premios_a_la_Excelencia/views/usuarios/Listar_usuarios.php');
        exit;
    }

    $error = $resultado['msg'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Iniciar sesión - Premios a la Excelencia</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #eef1f5;
        margin: 0;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .contenedor {
        max-width: 380px;
        width: 100%;
        background: #fff;
        padding: 30px 32px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,.12);
    }
    h1 { margin-top: 0; font-size: 22px; text-align: center; color: #222; }
    p.subtitulo { text-align: center; color: #777; font-size: 13px; margin-top: -6px; margin-bottom: 20px; }
    label { display: block; margin-top: 14px; font-weight: bold; font-size: 14px; color: #333; }
    input[type=text], input[type=password] {
        width: 100%; padding: 10px; margin-top: 4px; box-sizing: border-box;
        border: 1px solid #ccc; border-radius: 5px; font-size: 14px;
    }
    button {
        width: 100%; margin-top: 22px; padding: 11px;
        background: #1565c0; color: #fff; border: none; border-radius: 5px;
        font-size: 15px; cursor: pointer;
    }
    button:hover { background: #0d47a1; }
    .alerta-error {
        background: #ffebee; color: #b71c1c; border: 1px solid #ef9a9a;
        padding: 10px 14px; border-radius: 5px; margin-bottom: 10px; font-size: 13px;
    }
</style>
</head>
<body>
<div class="contenedor">
    <h1>Premios a la Excelencia</h1>
    <p class="subtitulo">Ingresa tus credenciales para continuar</p>

    <?php if ($error): ?>
        <div class="alerta-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php">
        <label for="nombres">Usuario</label>
        <input type="text" id="nombres" name="nombres" value="<?= htmlspecialchars($_POST['nombres'] ?? '') ?>" required autofocus>

        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Ingresar</button>
    </form>
</div>
</body>
</html>