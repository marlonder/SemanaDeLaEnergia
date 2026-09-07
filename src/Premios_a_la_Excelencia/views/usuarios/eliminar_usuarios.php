<?php
require_once __DIR__ . '/../../controller/Usuariocontroller.php';

$controller = new UsuarioController();

$id = $_GET['id'] ?? null;

if (!$id || !ctype_digit((string)$id)) {
    header('Location: Listar_usuarios.php?ok=0&msg=' . urlencode('Usuario no válido.'));
    exit;
}

$resultado = $controller->eliminar((int)$id);

header('Location: Listar_usuarios.php?ok=' . ($resultado['ok'] ? 1 : 0) . '&msg=' . urlencode($resultado['msg']));
exit;