<?php
require_once __DIR__ . '/../../controller/Usuariocontroller.php';

$controller = new UsuarioController();
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombres = $_POST['nombres'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $password = $_POST['password'] ?? '';
    $estado = isset($_POST['estado']) ? 1 : 0;

    $resultado = $controller->crear($nombres, $apellidos, $password, $estado);

    if ($resultado['ok']) {
        header('Location: Listar_usuarios.php?ok=1&msg=' . urlencode($resultado['msg']));
        exit;
    }

    $error = $resultado['msg'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Nuevo Usuario</title>
<style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 30px; }
    .contenedor { max-width: 500px; margin: 0 auto; background: #fff; padding: 25px 30px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,.1); }
    h1 { margin-top: 0; }
    label { display: block; margin-top: 14px; font-weight: bold; font-size: 14px; }
    input[type=text], input[type=password] {
        width: 100%; padding: 8px; margin-top: 4px; box-sizing: border-box;
        border: 1px solid #ccc; border-radius: 4px;
    }
    .checkbox-row { margin-top: 14px; display: flex; align-items: center; gap: 8px; }
    .acciones { margin-top: 22px; }
    .btn { padding: 9px 16px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; text-decoration: none; }
    .btn-guardar { background: #2e7d32; color: #fff; }
    .btn-cancelar { background: #757575; color: #fff; margin-left: 8px; }
    .alerta-error { background: #ffebee; color: #b71c1c; border: 1px solid #ef9a9a; padding: 10px 15px; border-radius: 4px; margin-bottom: 15px; font-size: 14px; }
</style>
</head>
<body>
<div class="contenedor">
    <h1>Nuevo Usuario</h1>

    <?php if ($error): ?>
        <div class="alerta-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="crear_usuarios.php">
        <label for="nombres">Nombres *</label>
        <input type="text" id="nombres" name="nombres" value="<?= htmlspecialchars($_POST['nombres'] ?? '') ?>" required>

        <label for="apellidos">Apellidos</label>
        <input type="text" id="apellidos" name="apellidos" value="<?= htmlspecialchars($_POST['apellidos'] ?? '') ?>">

        <label for="password">Contraseña *</label>
        <input type="password" id="password" name="password" required>

        <div class="checkbox-row">
            <input type="checkbox" id="estado" name="estado" <?= isset($_POST['estado']) || !isset($_POST['nombres']) ? 'checked' : '' ?>>
            <label for="estado" style="margin:0;">Usuario activo</label>
        </div>

        <div class="acciones">
            <button type="submit" class="btn btn-guardar">Guardar</button>
            <a href="Listar_usuarios.php" class="btn btn-cancelar">Cancelar</a>
        </div>
    </form>
</div>
</body>
</html>