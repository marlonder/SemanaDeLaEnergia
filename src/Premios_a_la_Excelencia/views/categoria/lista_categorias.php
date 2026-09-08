<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../controller/CategoriaController.php';

requerirLogin();

$controller = new CategoriaController();

// ---------- Eliminar (vía GET, con confirm en el enlace) ----------
if (isset($_GET['eliminar_cat'])) {
    $resultado = $controller->eliminarCategoria((int)$_GET['eliminar_cat']);
    header('Location: lista_categorias.php?ok=' . ($resultado['ok'] ? 1 : 0) . '&msg=' . urlencode($resultado['msg']));
    exit;
}
if (isset($_GET['eliminar_sub'])) {
    $resultado = $controller->eliminarSubCategoria((int)$_GET['eliminar_sub']);
    header('Location: lista_categorias.php?ok=' . ($resultado['ok'] ? 1 : 0) . '&msg=' . urlencode($resultado['msg']));
    exit;
}

// ---------- Crear / Actualizar (vía POST) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'guardar_categoria') {
        $id = trim($_POST['id'] ?? '');
        $nombre = $_POST['nombre'] ?? '';

        $resultado = $id !== ''
            ? $controller->actualizarCategoria((int)$id, $nombre)
            : $controller->crearCategoria($nombre);

        header('Location: lista_categorias.php?ok=' . ($resultado['ok'] ? 1 : 0) . '&msg=' . urlencode($resultado['msg']));
        exit;
    }

    if ($accion === 'guardar_subcategoria') {
        $id = trim($_POST['id'] ?? '');
        $nombre = $_POST['nombre'] ?? '';
        $idCategoria = $_POST['id_categoria'] ?? '';
        $idTipoEntidad = $_POST['id_Tipo_E'] ?? ''; // NUEVO

        $resultado = $id !== ''
            ? $controller->actualizarSubCategoria((int)$id, $nombre, (int)$idCategoria, (int)$idTipoEntidad) // NUEVO parámetro
            : $controller->crearSubCategoria($nombre, (int)$idCategoria, (int)$idTipoEntidad); // NUEVO parámetro

        header('Location: lista_categorias.php?ok=' . ($resultado['ok'] ? 1 : 0) . '&msg=' . urlencode($resultado['msg']));
        exit;
    }
}

// ---------- Datos para pintar la página ----------
$categoriaEditar = isset($_GET['editar_cat']) ? $controller->obtenerCategoria((int)$_GET['editar_cat']) : null;
$subCategoriaEditar = isset($_GET['editar_sub']) ? $controller->obtenerSubCategoria((int)$_GET['editar_sub']) : null;

$categorias = $controller->listarCategorias();
$subcategorias = $controller->listarSubCategorias();
$tiposEntidad = $controller->listarTiposEntidad(); // NUEVO

$msg = $_GET['msg'] ?? null;
$ok = isset($_GET['ok']) ? (bool)$_GET['ok'] : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Categorías y Subcategorías</title>
<style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; }
    h1 { margin-top: 0; font-size: 20px; }
    h2 { font-size: 16px; margin: 0 0 12px; }

    /* Layout con menú lateral (igual que en Usuarios) */
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

    /* Dos columnas: Categoría | Subcategoría */
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: start; }
    .panel {
        background: #fff; padding: 18px 20px; border-radius: 8px;
        box-shadow: 0 1px 4px rgba(0,0,0,.1);
    }

    .form-fila { display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; }
    .form-fila input[type=text], .form-fila select {
        flex: 1; min-width: 120px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px;
    }
    .btn { padding: 8px 14px; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; text-decoration: none; display: inline-block; }
    .btn-guardar { background: #2e7d32; color: #fff; }
    .btn-cancelar { background: #757575; color: #fff; }
    .btn-editar { background: #1565c0; color: #fff; }
    .btn-eliminar { background: #c62828; color: #fff; }

    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 8px; border-bottom: 1px solid #eee; text-align: left; font-size: 13px; }
    th { background: #fafafa; }
    .acciones a { margin-right: 6px; }
    .vacio { text-align: center; padding: 16px; color: #888; font-size: 13px; }

    @media (max-width: 800px) {
        .grid-2 { grid-template-columns: 1fr; }
    }
</style>
</head>
<body>
<div class="layout">
    <?php $paginaActiva = 'categorias'; include __DIR__ . '/../../partials/sidebar.php'; ?>

    <main class="contenido">
        <div class="cabecera">
            <h1>Categorías y Subcategorías</h1>
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

        <div class="grid-2">

            <!-- ================= CATEGORÍAS ================= -->
            <div class="panel">
                <h2><?= $categoriaEditar ? 'Editar categoría' : 'Nueva categoría' ?></h2>

                <form method="POST" action="lista_categorias.php" class="form-fila">
                    <input type="hidden" name="accion" value="guardar_categoria">
                    <input type="hidden" name="id" value="<?= $categoriaEditar ? (int)$categoriaEditar['id'] : '' ?>">

                    <input type="text" name="nombre" placeholder="Nombre de la categoría"
                           value="<?= htmlspecialchars($categoriaEditar['nombre'] ?? '') ?>" required>

                    <button type="submit" class="btn btn-guardar">
                        <?= $categoriaEditar ? 'Actualizar' : 'Agregar' ?>
                    </button>

                    <?php if ($categoriaEditar): ?>
                        <a href="lista_categorias.php" class="btn btn-cancelar">Cancelar</a>
                    <?php endif; ?>
                </form>

                <table>
                    <thead>
                        <tr><th>Nombre</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categorias)): ?>
                            <tr><td colspan="2" class="vacio">No hay categorías registradas.</td></tr>
                        <?php else: ?>
                            <?php foreach ($categorias as $c): ?>
                                <tr>
                                    <td><?= htmlspecialchars($c['nombre']) ?></td>
                                    <td class="acciones">
                                        <a class="btn btn-editar" href="lista_categorias.php?editar_cat=<?= (int)$c['id'] ?>">Editar</a>
                                        <a class="btn btn-eliminar"
                                           href="lista_categorias.php?eliminar_cat=<?= (int)$c['id'] ?>"
                                           onclick="return confirm('¿Eliminar esta categoría?');">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- ================= SUBCATEGORÍAS ================= -->
            <div class="panel">
                <h2><?= $subCategoriaEditar ? 'Editar subcategoría' : 'Nueva subcategoría' ?></h2>

                <form method="POST" action="lista_categorias.php" class="form-fila">
                    <input type="hidden" name="accion" value="guardar_subcategoria">
                    <input type="hidden" name="id" value="<?= $subCategoriaEditar ? (int)$subCategoriaEditar['id'] : '' ?>">

                    <select name="id_categoria" required>
                        <option value="">Categoría...</option>
                        <?php foreach ($categorias as $c): ?>
                            <option value="<?= (int)$c['id'] ?>"
                                <?= (isset($subCategoriaEditar['id_categoria']) && $subCategoriaEditar['id_categoria'] == $c['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <!-- NUEVO: select de Tipo de entidad -->
                    <select name="id_Tipo_E" required>
                        <option value="">Tipo de entidad...</option>
                        <?php foreach ($tiposEntidad as $te): ?>
                            <option value="<?= (int)$te['id'] ?>"
                                <?= (isset($subCategoriaEditar['id_Tipo_E']) && $subCategoriaEditar['id_Tipo_E'] == $te['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($te['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <input type="text" name="nombre" placeholder="Nombre de la subcategoría"
                           value="<?= htmlspecialchars($subCategoriaEditar['nombre'] ?? '') ?>" required>

                    <button type="submit" class="btn btn-guardar">
                        <?= $subCategoriaEditar ? 'Actualizar' : 'Agregar' ?>
                    </button>

                    <?php if ($subCategoriaEditar): ?>
                        <a href="lista_categorias.php" class="btn btn-cancelar">Cancelar</a>
                    <?php endif; ?>
                </form>

                <table>
                    <thead>
                        <tr><th>Nombre</th><th>Categoría</th><th>Tipo</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($subcategorias)): ?>
                            <tr><td colspan="4" class="vacio">No hay subcategorías registradas.</td></tr>
                        <?php else: ?>
                            <?php foreach ($subcategorias as $s): ?>
                                <tr>
                                    <td><?= htmlspecialchars($s['nombre']) ?></td>
                                    <td><?= htmlspecialchars($s['categoria_nombre']) ?></td>
                                    <td><?= htmlspecialchars($s['tipo_entidad_nombre']) ?></td>
                                    <td class="acciones">
                                        <a class="btn btn-editar" href="lista_categorias.php?editar_sub=<?= (int)$s['id'] ?>">Editar</a>
                                        <a class="btn btn-eliminar"
                                           href="lista_categorias.php?eliminar_sub=<?= (int)$s['id'] ?>"
                                           onclick="return confirm('¿Eliminar esta subcategoría?');">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </main>
</div>
</body>
</html>