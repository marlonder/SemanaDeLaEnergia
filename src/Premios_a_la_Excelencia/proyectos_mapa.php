<?php
require_once __DIR__ . '/controller/ProyectoController.php';

header('Content-Type: application/json; charset=utf-8');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// =====================================================
// URLs públicas base donde se sirven imágenes y QR de proyectos
// =====================================================
const URL_BASE_IMAGENES = 'http://192.168.1.205:8080/Premios_a_la_Excelencia/media/proyecto/';
const URL_BASE_QR       = 'http://192.168.1.205:8080/Premios_a_la_Excelencia/media/qr/';

$nombrePais = trim($_GET['pais'] ?? '');

if ($nombrePais === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Falta el parámetro "pais"']);
    exit;
}

try {
    $controller = new ProyectoController();
    $filas = $controller->listarActivosPorPais($nombrePais);

    $proyectos = array_map(function ($fila) {
        return [
            'nombre'       => $fila['titulo'],
            'descripcion'  => $fila['descripcion'],
            'categoria'    => $fila['categoria_nombre'],
            'foto'         => !empty($fila['imagen_path'])
                ? URL_BASE_IMAGENES . $fila['imagen_path']
                : null,
            'red_social'   => $fila['red_social'],
            'organizacion' => $fila['organizacion'],
            'anio'         => $fila['anio'],
            // 'link' ahora guarda el nombre del archivo QR subido (ver Proyecto model),
            // así que armamos la URL pública igual que con la imagen.
            'qr'           => !empty($fila['link'])
                ? URL_BASE_QR . $fila['link']
                : null,
        ];
    }, $filas);

    echo json_encode($proyectos, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al consultar los proyectos']);
}