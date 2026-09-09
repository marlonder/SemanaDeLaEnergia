<?php
/**
 * proyectos_mapa.php
 * -------------------------------------------------------------
 * Endpoint AJAX público (solo lectura) para el mapa interactivo.
 * Reutiliza el MVC existente (ProyectoController -> Proyecto model).
 *
 * 👉 Ajusta esta ruta según dónde coloques este archivo respecto
 *    a tu carpeta "controller". Ejemplos según tu estructura real:
 *    - Si este archivo va en /src/api/proyectos_mapa.php:
 *        require_once __DIR__ . '/../controller/ProyectoController.php';
 *    - Si va directo en /src/proyectos_mapa.php:
 *        require_once __DIR__ . '/controller/ProyectoController.php';
 */
require_once __DIR__ . '/controller/ProyectoController.php';

header('Content-Type: application/json; charset=utf-8');

// =====================================================
// CORS — el frontend (nginx:8090) y este backend (webserver:8080)
// son orígenes distintos para el navegador.
// 👉 En producción, cambia '*' por el dominio real del frontend.
// =====================================================
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// =====================================================
// URL pública base donde se sirven las imágenes de proyectos
// =====================================================
const URL_BASE_IMAGENES = 'http://192.168.1.205:8080/premios/.../media/';

// =====================================================
// Validación del parámetro
// =====================================================
$nombrePais = trim($_GET['pais'] ?? '');

if ($nombrePais === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Falta el parámetro "pais"']);
    exit;
}

try {
    $controller = new ProyectoController();
    $filas = $controller->listarActivosPorPais($nombrePais);

    // Mapeamos las columnas reales de la BD a los nombres que
    // espera el front (mostrarListaProyectos / mostrarDetalleProyecto
    // en modal.js): nombre, descripcion, categoria, foto, qr
    $proyectos = array_map(function ($fila) {
        return [
            'nombre'       => $fila['titulo'],
            'descripcion'  => $fila['descripcion'],
            'categoria'    => $fila['categoria_nombre'],
            'foto'         => !empty($fila['imagen_path'])
                ? URL_BASE_IMAGENES . $fila['imagen_path']
                : null,
            'link'         => $fila['link'],
            'red_social'   => $fila['red_social'],
            'organizacion' => $fila['organizacion'],
            'anio'         => $fila['anio'],
            // 👉 Todavía no existe columna de QR en la BD.
            // Cuando la agregues, mapea aquí igual que las demás.
            'qr'           => null,
        ];
    }, $filas);

    echo json_encode($proyectos, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al consultar los proyectos']);
}