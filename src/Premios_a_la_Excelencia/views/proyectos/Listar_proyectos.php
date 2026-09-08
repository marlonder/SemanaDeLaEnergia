<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../controller/ProyectoController.php';

requerirLogin();

$controller = new ProyectoController();

// ---------- Eliminar (borrado lógico, vía GET con confirm) ----------
if (isset($_GET['eliminar'])) {
    $resultado = $controller->eliminar((int)$_GET['eliminar']);
    header('Location: Listar_proyectos.php?ok=' . ($resultado['ok'] ? 1 : 0) . '&msg=' . urlencode($resultado['msg']));
    exit;
}

// ---------- Crear / Actualizar (vía POST) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['id'] ?? '');

    $datos = [
        'titulo' => $_POST['titulo'] ?? '',
        'descripcion' => $_POST['descripcion'] ?? '',
        'organizacion' => $_POST['organizacion'] ?? '',
        'link' => $_POST['link'] ?? '',
        'red_social' => $_POST['red_social'] ?? '',
        'anio' => $_POST['anio'] ?? '',
        'estado' => $_POST['estado'] ?? '',
        'id_pais' => $_POST['id_pais'] ?? '',
        'id_categoria' => $_POST['id_categoria'] ?? '',
        'id_Tipo_E' => $_POST['id_Tipo_E'] ?? '',
        'id_subcategoria' => $_POST['id_subcategoria'] ?? '',
    ];

    $archivoImagen = $_FILES['imagen'] ?? null;

    $resultado = $id !== ''
        ? $controller->actualizar((int)$id, $datos, $archivoImagen)
        : $controller->crear($datos, $_SESSION['usuario_id']);

    header('Location: Listar_proyectos.php?ok=' . ($resultado['ok'] ? 1 : 0) . '&msg=' . urlencode($resultado['msg']));
    exit;
}

// ---------- Datos para pintar la página ----------
$proyectoEditar = isset($_GET['editar']) ? $controller->obtener((int)$_GET['editar']) : null;

$proyectos = $controller->listar();
$paises = $controller->listarPaises();
$categorias = $controller->listarCategorias();
$subcategorias = $controller->listarSubCategorias();
$tiposEntidad = $controller->listarTiposEntidad();

$msg = $_GET['msg'] ?? null;
$ok = isset($_GET['ok']) ? (bool)$_GET['ok'] : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Proyectos</title>
<style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; }
    h1 { margin-top: 0; font-size: 20px; }
    h2 { font-size: 16px; margin: 0 0 12px; }

    /* Layout con menú lateral (igual que en Usuarios/Categorías) */
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
    .form-grid input[type=text], .form-grid input[type=number],
    .form-grid select, .form-grid textarea {
        width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;
        font-size: 14px; box-sizing: border-box; font-family: inherit;
    }
    .form-grid textarea { resize: vertical; min-height: 60px; }
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
    <?php $paginaActiva = 'proyectos'; include __DIR__ . '/../../partials/sidebar.php'; ?>

    <main class="contenido">
        <div class="cabecera">
            <h1>Proyectos</h1>
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
            <h2><?= $proyectoEditar ? 'Editar proyecto' : 'Nuevo proyecto' ?></h2>

            <form method="POST" action="Listar_proyectos.php">
                <input type="hidden" name="id" value="<?= $proyectoEditar ? (int)$proyectoEditar['id'] : '' ?>">

                <div class="form-grid">
                    <div class="full">
                        <label>Título *</label>
                        <input type="text" name="titulo" required
                               value="<?= htmlspecialchars($proyectoEditar['titulo'] ?? '') ?>">
                    </div>

                    <div class="full">
                        <label>Descripción</label>
                        <textarea name="descripcion"><?= htmlspecialchars($proyectoEditar['descripcion'] ?? '') ?></textarea>
                    </div>

                    <div>
                        <label>Organización</label>
                        <input type="text" name="organizacion"
                               value="<?= htmlspecialchars($proyectoEditar['organizacion'] ?? '') ?>">
                    </div>

                    <div>
                        <label>Link (Para el QR)</label>
                        <input type="text" name="link"
                               value="<?= htmlspecialchars($proyectoEditar['link'] ?? '') ?>">
                    </div>

                    <div>
                        <label>Red social</label>
                        <input type="text" name="red_social"
                               value="<?= htmlspecialchars($proyectoEditar['red_social'] ?? '') ?>">
                    </div>

                    <div>
                        <label>Imagen</label>
                        <input type="file" name="imagen" id="inputImagen" accept="image/png, image/jpeg, image/webp, image/gif">
                        <?php if (!empty($proyectoEditar['imagen_path'])): ?>
                            <p style="font-size:12px;margin-top:4px;">
                                Actual:
                                <img src="media/<?= htmlspecialchars($proyectoEditar['imagen_path']) ?>"
                                    alt="" style="height:40px;vertical-align:middle;">
                            </p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label>Año</label>
                        <input type="number" name="anio" min="1900" max="2100"
                               value="<?= htmlspecialchars($proyectoEditar['anio'] ?? '') ?>">
                    </div>

                    <div>
                        <label>País *</label>
                        <select name="id_pais" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($paises as $p): ?>
                                <option value="<?= (int)$p['id'] ?>"
                                    <?= (isset($proyectoEditar['id_pais']) && $proyectoEditar['id_pais'] == $p['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label>Categoría *</label>
                        <select name="id_categoria" id="selectCategoria" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($categorias as $c): ?>
                                <option value="<?= (int)$c['id'] ?>"
                                    <?= (isset($proyectoEditar['id_categoria']) && $proyectoEditar['id_categoria'] == $c['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label>Tipo de entidad *</label>
                        <select name="id_Tipo_E" id="selectTipoEntidad" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($tiposEntidad as $t): ?>
                                <option value="<?= (int)$t['id'] ?>"
                                    <?= (isset($proyectoEditar['id_Tipo_E']) && $proyectoEditar['id_Tipo_E'] == $t['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label>Subcategoría *</label>
                        <select name="id_subcategoria" id="selectSubcategoria" required>
                            <option value="">Seleccione una categoría primero...</option>
                            <?php foreach ($subcategorias as $s): ?>
                               <option value="<?= (int)$s['id'] ?>"
                                    data-categoria="<?= (int)$s['id_categoria'] ?>"
                                    data-tipo="<?= (int)$s['id_Tipo_E'] ?>"
                                    <?= (isset($proyectoEditar['id_subcategoria']) && $proyectoEditar['id_subcategoria'] == $s['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="checkbox-row">
                        <input type="checkbox" id="estado" name="estado"
                               <?= (!isset($proyectoEditar) || !empty($proyectoEditar['estado'])) ? 'checked' : '' ?>>
                        <label for="estado" style="margin:0;">Proyecto activo</label>
                    </div>
                </div>

                <div class="acciones-form">
                    <button type="submit" class="btn btn-guardar">
                        <?= $proyectoEditar ? 'Actualizar' : 'Guardar' ?>
                    </button>
                    <?php if ($proyectoEditar): ?>
                        <a href="Listar_proyectos.php" class="btn btn-cancelar">Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- ================= LISTADO ================= -->
        <div class="panel">
            <h2>Listado de proyectos</h2>
            <table>
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>País</th>
                        <th>Categoría</th>
                        <th>Subcategoría</th>
                        <th>Año</th>
                        <th>Registrado por</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($proyectos)): ?>
                        <tr><td colspan="8" class="vacio">No hay proyectos registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($proyectos as $p): ?>
                            <tr>
                                <td><?= htmlspecialchars($p['titulo']) ?></td>
                                <td><?= htmlspecialchars($p['pais_nombre']) ?></td>
                                <td><?= htmlspecialchars($p['categoria_nombre']) ?></td>
                                <td><?= htmlspecialchars($p['subcategoria_nombre']) ?></td>
                                <td><?= htmlspecialchars($p['anio'] ?? '') ?></td>
                                <td><?= htmlspecialchars($p['usuario_nombres']) ?></td>
                                <td>
                                    <?php if ($p['estado']): ?>
                                        <span class="badge badge-activo">Activo</span>
                                    <?php else: ?>
                                        <span class="badge badge-inactivo">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="acciones">
                                    <a class="btn btn-editar" href="Listar_proyectos.php?editar=<?= (int)$p['id'] ?>">Editar</a>
                                    <a class="btn btn-eliminar"
                                       href="Listar_proyectos.php?eliminar=<?= (int)$p['id'] ?>"
                                       onclick="return confirm('¿Desactivar este proyecto?');">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script>
    // Filtra las subcategorías según la categoría seleccionada
    (function () {
        var selectCategoria = document.getElementById('selectCategoria');
        var selectTipoEntidad = document.getElementById('selectTipoEntidad');
        var selectSubcategoria = document.getElementById('selectSubcategoria');
        var opciones = Array.prototype.slice.call(selectSubcategoria.options);

        function filtrar() {
            var categoriaId = selectCategoria.value;
            var tipoId = selectTipoEntidad.value;
            var haySeleccionValida = false;

            opciones.forEach(function (opt) {
                if (!opt.value) {
                    opt.hidden = false;
                    return;
                }
                var coincide = opt.dataset.categoria === categoriaId && opt.dataset.tipo === tipoId;
                opt.hidden = !coincide;
                if (coincide && opt.selected) haySeleccionValida = true;
            });

            if (!haySeleccionValida) selectSubcategoria.value = '';
        }

        selectCategoria.addEventListener('change', filtrar);
        selectTipoEntidad.addEventListener('change', filtrar);
        filtrar();
    })();

    // Validación extra de imagen en el navegador (una sola, formato correcto)
    document.getElementById('inputImagen').addEventListener('change', function () {
        var permitido = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (this.files.length > 0 && permitido.indexOf(this.files[0].type) === -1) {
            alert('Formato no permitido. Solo imágenes JPG, PNG, WEBP o GIF.');
            this.value = '';
        }
    });
</script>
</body>
</html>