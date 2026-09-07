<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function estaLogueado(): bool
{
    return isset($_SESSION['usuario_id']);
}

/**
 * Redirige al login si no hay una sesión activa.
 * $rutaLogin es la ruta relativa desde el archivo que llama a esta función
 * hasta index.php (por defecto, para vistas dentro de views/usuarios/).
 */
function requerirLogin(string $rutaLogin = '../../index.php'): void
{
    if (!estaLogueado()) {
        header('Location: ' . $rutaLogin);
        exit;
    }
}

function cerrarSesion(): void
{
    $_SESSION = [];
    session_destroy();
}