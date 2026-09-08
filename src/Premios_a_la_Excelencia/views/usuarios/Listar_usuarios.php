<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../controller/Usuariocontroller.php';

requerirLogin();

$controller = new UsuarioController();

// ---------- Eliminar (borrado lógico, vía GET con confirm) ----------
if (isset($_GET['eliminar'])) {
    $resultado = $controller->eliminar((int)$_GET['eliminar']);
    header('Location: Listar_usuarios.php?ok=' . ($resultado['ok'] ? 1 : 0) . '&msg=' . urlencode($resultado['msg']));
    exit;
}

// ---------- Crear / Actualizar (vía POST) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['id'] ?? '');
    $nombres = $_POST['nombres'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $password = $_POST['password'] ?? '';
    $estado = isset($_POST['estado']) ? 1 : 0;

    $resultado = $id !== ''
        ? $controller->actualizar((int)$id, $nombres, $apellidos, $password, $estado)
        : $controller->crear($nombres, $apellidos, $password, $estado);

    header('Location: Listar_usuarios.php?ok=' . ($resultado['ok'] ? 1 : 0) . '&msg=' . urlencode($resultado['msg']));
    exit;
}

// ---------- Datos para pintar la página ----------
$usuarioEditar = isset($_GET['editar']) ? $controller->obtener((int)$_GET['editar']) : null;
$usuarios = $controller->listar();

$msg = $_GET['msg'] ?? null;
$ok = isset($_GET['ok']) ? (bool)$_GET['ok'] : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Usuarios</title>
<style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; }
    h1 { margin-top: 0; font-size: 20px; }
    h2 { font-size: 16px; margin: 0 0 12px; }

    /* Layout con menú lateral (igual que en Categorías/Proyectos) */
    .layout { display: flex; min-height: 100vh; }
    .sidebar {
        width: 220px; flex-shrink: 0; background: #1e2a35; color: #cfd8dc;
        padding: 20px 0; min-height: 100vh; box-sizing: border-box;
    }
    .sidebar-logo {
        font-size: 15px; font-weight: bold; color: #fff;
        padding: 0 20px 20px; border-bottom: 1px solid #35424f; margin-bottom: 10px;
    }
    .sidebar-nav { display: flex; flex-direction: column; }
    .sidebar-nav a {
        color: #cfd8dc; text-decoration: none; padding: 12px 20px;
        font-size: 14px; border-left: 3px solid transparent;
    }
    .sidebar-nav a:hover { background: #2a3a48; color: #fff; }
    .sidebar-nav a.activo {
        background: #2a3a48; color: #fff; border-left: 3px solid #1565c0; font-weight: bold;
    }
    .contenido { flex: 1; padding: 30px; }

    .cabecera { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
    .cabecera a { font-size: 13px; color: #c62828; text-decoration: none; }

    .alerta { padding: 10px 15px; border-radius: 4px; margin-bottom: 15px; font-size: 14px; }
    .alerta-ok { background: #e8f5e9; color: #1b5e20; border: 1px solid #a5d6a7; }
    .alerta-error { background: #ffebee; color: #b71c1c; border: 1px solid #ef9a9a; }

    .panel {
        background: #fff; padding: 18px 20px; border-radius: 8px;
        box-shadow: 0 1px 4px rgba(0,0,0,.1); margin-bottom: 20px;
    }

    .form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 14px; }
    .form-grid .full { grid-column: 1 / -1; }
    .form-grid label { display: block; font-size: 13px; font-weight: bold; margin-bottom: 4px; }
    .form-grid input[type=text], .form-grid input[type=password] {
        width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;
        font-size: 14px; box-sizing: border-box; font-family: inherit;
    }
    .form-grid small { color: #777; display: block; margin-top: 3px; }
    .checkbox-row { display: flex; align-items: center; gap: 8px; margin-top: 22px; }

    .acciones-form { margin-top: 6px; }
    .btn { padding: 8px 14px; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; text-decoration: none; display: inline-block; }
    .btn-guardar { background: #2e7d32; color: #fff; }
    .btn-cancelar { background: #757575; color: #fff; margin-left: 6px; }
    .btn-editar { background: #1565c0; color: #fff; }
    .btn-eliminar { background: #c62828; color: #fff; }

    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 8px; border-bottom: 1px solid #eee; text-align: left; font-size: 13px; }
    th { background: #fafafa; }
    .acciones a { margin-right: 6px; }
    .vacio { text-align: center; padding: 16px; color: #888; font-size: 13px; }
    .badge { padding: 3px 8px; border-radius: 12px; font-size: 12px; color: #fff; }
    .badge-activo { background: #2e7d32; }
    .badge-inactivo { background: #757575; }

    @media (max-width: 900px) {
        .form-grid { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 600px) {
        .form-grid { grid-template-columns: 1fr; }
    }
</style>
</head>
<body>
<div class="layout">
    <?php $paginaActiva = 'usuarios'; include __DIR__ . '/../../partials/sidebar.php'; ?>

    <main class="contenido">
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

        <!-- ================= FORMULARIO ================= -->
        <div class="panel">
            <h2><?= $usuarioEditar ? 'Editar usuario' : 'Nuevo usuario' ?></h2>

            <form method="POST" action="Listar_usuarios.php">
                <input type="hidden" name="id" value="<?= $usuarioEditar ? (int)$usuarioEditar['id'] : '' ?>">

                <div class="form-grid">
                    <div>
                        <label>Nombres *</label>
                        <input type="text" name="nombres" required
                               value="<?= htmlspecialchars($usuarioEditar['nombres'] ?? '') ?>">
                    </div>

                    <div>
                        <label>Apellidos</label>
                        <input type="text" name="apellidos"
                               value="<?= htmlspecialchars($usuarioEditar['apellidos'] ?? '') ?>">
                    </div>

                    <div>
                        <label><?= $usuarioEditar ? 'Nueva contraseña' : 'Contraseña *' ?></label>
                        <input type="password" name="password" <?= $usuarioEditar ? '' : 'required' ?>>
                        <?php if ($usuarioEditar): ?>
                            <small>Dejar en blanco para mantener la contraseña actual.</small>
                        <?php endif; ?>
                    </div>

                    <div class="checkbox-row">
                        <input type="checkbox" id="estado" name="estado"
                               <?= (!isset($usuarioEditar) || !empty($usuarioEditar['estado'])) ? 'checked' : '' ?>>
                        <label for="estado" style="margin:0;">Usuario activo</label>
                    </div>
                </div>

                <div class="acciones-form">
                    <button type="submit" class="btn btn-guardar">
                        <?= $usuarioEditar ? 'Actualizar' : 'Guardar' ?>
                    </button>
                    <?php if ($usuarioEditar): ?>
                        <a href="Listar_usuarios.php" class="btn btn-cancelar">Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- ================= LISTADO ================= -->
        <div class="panel">
            <h2>Listado de usuarios</h2>
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
                        <tr><td colspan="6" class="vacio">No hay usuarios registrados.</td></tr>
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
                                    <a class="btn btn-editar" href="Listar_usuarios.php?editar=<?= (int)$u['id'] ?>">Editar</a>
                                    <a class="btn btn-eliminar"
                                       href="Listar_usuarios.php?eliminar=<?= (int)$u['id'] ?>"
                                       onclick="return confirm('¿Desactivar este usuario?');">Eliminar</a>
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