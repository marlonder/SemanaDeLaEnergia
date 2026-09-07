<?php
/**
 * Menú lateral reutilizable.
 * La vista que lo incluye debe definir $paginaActiva ANTES del include:
 *   'usuarios' | 'proyectos' | 'categorias'
 *
 * Ejemplo de uso, desde views/usuarios/Listar_usuarios.php:
 *   <?php $paginaActiva = 'usuarios'; include __DIR__ . '/../partials/sidebar.php'; ?>
 */
$paginaActiva = $paginaActiva ?? '';
?>
<aside class="sidebar">
    <div class="sidebar-logo">Premios a la Excelencia</div>

    <nav class="sidebar-nav">
        <a href="#" class="<?= $paginaActiva === 'usuarios' ? 'activo' : '' ?>">
            Usuarios
        </a>
        <a href="../categoria/lista_categorias.php" class="<?= $paginaActiva === 'proyectos' ? 'activo' : '' ?>">
            Proyectos
        </a>
        <a href="#" class="<?= $paginaActiva === 'categorias' ? 'activo' : '' ?>">
            Categorías
        </a>
    </nav>
</aside>