<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../controller/Usuariocontroller.php';

requerirLogin();

$controller = new UsuarioController();
$usuarios = $controller->listar();

// Mensaje flash pasado por querystring (?msg=...&ok=1)
$msg = $_GET['msg'] ?? null;
$ok = isset($_GET['ok']) ? (bool)$_GET['ok'] : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Listado de Usuarios</title>
<style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 30px; }
    .contenedor { max-width: 900px; margin: 0 auto; background: #fff; padding: 20px 30px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,.1); }
    h1 { margin-top: 0; }
    .btn { display: inline-block; padding: 8px 14px; border-radius: 4px; text-decoration: none; font-size: 14px; }
    .btn-nuevo { background: #2e7d32; color: #fff; margin-bottom: 15px; }
    .btn-editar { background: #1565c0; color: #fff; }
    .btn-eliminar { background: #c62828; color: #fff; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; font-size: 14px; }
    th { background: #fafafa; }
    .badge { padding: 3px 8px; border-radius: 12px; font-size: 12px; color: #fff; }
    .badge-activo { background: #2e7d32; }
    .badge-inactivo { background: #757575; }
    .acciones a { margin-right: 8px; }
    .alerta { padding: 10px 15px; border-radius: 4px; margin-bottom: 15px; font-size: 14px; }
    .alerta-ok { background: #e8f5e9; color: #1b5e20; border: 1px solid #a5d6a7; }
    .alerta-error { background: #ffebee; color: #b71c1c; border: 1px solid #ef9a9a; }
    .cabecera { display: flex; justify-content: space-between; align-items: center; }
    .cabecera a { font-size: 13px; color: #c62828; text-decoration: none; }

    /* --- Layout con menú lateral --- */
    .layout { display: flex; min-height: 100vh; }
    .sidebar {
        width: 220px;
        flex-shrink: 0;
        background: #1e2a35;
        color: #cfd8dc;
        padding: 20px 0;
        min-height: 100vh;
        box-sizing: border-box;
    }
    .sidebar-logo {
        font-size: 15px;
        font-weight: bold;
        color: #fff;
        padding: 0 20px 20px;
        border-bottom: 1px solid #35424f;
        margin-bottom: 10px;
    }
    .sidebar-nav { display: flex; flex-direction: column; }
    .sidebar-nav a {
        color: #cfd8dc;
        text-decoration: none;
        padding: 12px 20px;
        font-size: 14px;
        border-left: 3px solid transparent;
    }
    .sidebar-nav a:hover { background: #2a3a48; color: #fff; }
    .sidebar-nav a.activo {
        background: #2a3a48;
        color: #fff;
        border-left: 3px solid #1565c0;
        font-weight: bold;
    }
    .contenido { flex: 1; padding: 30px; }
    .contenido .contenedor { max-width: 900px; margin: 0; }
</style>
</head>
<body>
<div class="layout">
    <?php $paginaActiva = 'usuarios'; include __DIR__ . '/../../partials/sidebar.php'; ?>

    <main class="contenido">
    <div class="contenedor">
    <div class="cabecera">
        <h1>Usuarios</h1>
        <span>
            Hola, <strong><?= htmlspecialchars($_SESSION['usuario_nombres'] ?? '') ?></strong>
            &nbsp;|&nbsp; <a href="../../../logout.php">Cerrar sesión</a>
        </span>
    </div>

    <?php if ($msg): ?>
        <div class="alerta <?= $ok ? 'alerta-ok' : 'alerta-error' ?>">
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <a class="btn btn-nuevo" href="crear_usuarios.php">+ Nuevo usuario</a>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombres</th>
                <th>Apellidos</th>
                <th>Estado</th>
                <th>Fecha registro</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($usuarios)): ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding:20px;">No hay usuarios registrados.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td><?= (int)$u['id'] ?></td>
                        <td><?= htmlspecialchars($u['nombres']) ?></td>
                        <td><?= htmlspecialchars($u['apellidos'] ?? '') ?></td>
                        <td>
                            <?php if ($u['estado']): ?>
                                <span class="badge badge-activo">Activo</span>
                            <?php else: ?>
                                <span class="badge badge-inactivo">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($u['fecha_registro']) ?></td>
                        <td class="acciones">
                            <a class="btn btn-editar" href="editar_usuarios.php?id=<?= (int)$u['id'] ?>">Editar</a>
                            <a class="btn btn-eliminar"
                               href="eliminar_usuarios.php?id=<?= (int)$u['id'] ?>"
                               onclick="return confirm('¿Está seguro de eliminar este usuario?');">
                               Eliminar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
    </main>
</div>
</body>
</html>