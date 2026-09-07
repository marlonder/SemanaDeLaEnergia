<?php
require_once __DIR__ . '/../../controller/Usuariocontroller.php';


$controller = new UsuarioController();
$error = null;

$id = $_GET['id'] ?? ($_POST['id'] ?? null);

if (!$id || !ctype_digit((string)$id)) {
    header('Location: Listar_usuarios.php?ok=0&msg=' . urlencode('Usuario no válido.'));
    exit;
}
$id = (int)$id;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombres = $_POST['nombres'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $password = $_POST['password'] ?? '';
    $estado = isset($_POST['estado']) ? 1 : 0;

    $resultado = $controller->actualizar($id, $nombres, $apellidos, $password, $estado);

    if ($resultado['ok']) {
        header('Location: Listar_usuarios.php?ok=1&msg=' . urlencode($resultado['msg']));
        exit;
    }

    $error = $resultado['msg'];
    // Para repoblar el formulario si falla la validación
    $usuario = [
        'id' => $id,
        'nombres' => $nombres,
        'apellidos' => $apellidos,
        'estado' => $estado,
    ];
} else {
    $usuario = $controller->obtener($id);

    if (!$usuario) {
        header('Location: Listar_usuarios.php?ok=0&msg=' . urlencode('El usuario no existe.'));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Usuario</title>
<style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 30px; }
    .contenedor { max-width: 500px; margin: 0 auto; background: #fff; padding: 25px 30px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,.1); }
    h1 { margin-top: 0; }
    label { display: block; margin-top: 14px; font-weight: bold; font-size: 14px; }
    input[type=text], input[type=password] {
        width: 100%; padding: 8px; margin-top: 4px; box-sizing: border-box;
        border: 1px solid #ccc; border-radius: 4px;
    }
    small { color: #777; }
    .checkbox-row { margin-top: 14px; display: flex; align-items: center; gap: 8px; }
    .acciones { margin-top: 22px; }
    .btn { padding: 9px 16px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; text-decoration: none; }
    .btn-guardar { background: #1565c0; color: #fff; }
    .btn-cancelar { background: #757575; color: #fff; margin-left: 8px; }
    .alerta-error { background: #ffebee; color: #b71c1c; border: 1px solid #ef9a9a; padding: 10px 15px; border-radius: 4px; margin-bottom: 15px; font-size: 14px; }
</style>
</head>
<body>
<div class="contenedor">
    <h1>Editar Usuario #<?= (int)$usuario['id'] ?></h1>

    <?php if ($error): ?>
        <div class="alerta-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="editar_usuarios.php">
        <input type="hidden" name="id" value="<?= (int)$usuario['id'] ?>">

        <label for="nombres">Nombres *</label>
        <input type="text" id="nombres" name="nombres" value="<?= htmlspecialchars($usuario['nombres']) ?>" required>

        <label for="apellidos">Apellidos</label>
        <input type="text" id="apellidos" name="apellidos" value="<?= htmlspecialchars($usuario['apellidos'] ?? '') ?>">

        <label for="password">Nueva contraseña</label>
        <input type="password" id="password" name="password">
        <small>Dejar en blanco para mantener la contraseña actual.</small>

        <div class="checkbox-row">
            <input type="checkbox" id="estado" name="estado" <?= !empty($usuario['estado']) ? 'checked' : '' ?>>
            <label for="estado" style="margin:0;">Usuario activo</label>
        </div>

        <div class="acciones">
            <button type="submit" class="btn btn-guardar">Actualizar</button>
            <a href="Listar_usuarios.php" class="btn btn-cancelar">Cancelar</a>
        </div>
    </form>
</div>
</body>
</html>