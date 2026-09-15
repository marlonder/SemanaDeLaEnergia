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
    <div class="sidebar-logo">
        <img 
            src="../../media/Olade_Logo.png" 
            alt="Logo de la empresa"
            class="sidebar-logo__img"
        >
        <div class="sidebar-logo__texto">
            Premios a la Excelencia
        </div>
    </div>

    <nav class="sidebar-nav">
        <a href="<?= $paginaActiva === 'usuarios' ? '#' : '../usuarios/Listar_usuarios.php' ?>"
           class="<?= $paginaActiva === 'usuarios' ? 'activo' : '' ?>">
            Usuarios
        </a>
        <a href="<?= $paginaActiva === 'categorias' ? '#' : '../categoria/lista_categorias.php' ?>"
           class="<?= $paginaActiva === 'categorias' ? 'activo' : '' ?>">
            Categorías
        </a>

        <a href="<?= $paginaActiva === 'proyectos' ? '#' : '../proyectos/Listar_proyectos.php' ?>"
           class="<?= $paginaActiva === 'proyectos' ? 'activo' : '' ?>">
            Proyectos
        </a>
    </nav>
</aside>