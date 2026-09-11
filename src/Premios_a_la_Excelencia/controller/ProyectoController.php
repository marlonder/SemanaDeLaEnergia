<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../model/Proyecto.php';
require_once __DIR__ . '/../model/Pais.php';
require_once __DIR__ . '/../model/Categoria.php';
require_once __DIR__ . '/../model/subcategoria.php';
require_once __DIR__ . '/../model/Tipo_entidad.php';

class ProyectoController
{
    private $db;
    private $proyecto;
    private $pais;
    private $categoria;
    private $subCategoria;
    private $tipoEntidad;

    public function __construct()
    {
        $conexion = new Conexion();
        $this->db = $conexion->conectar();
        $this->proyecto = new Proyecto($this->db);
        $this->pais = new Pais($this->db);
        $this->categoria = new Categoria($this->db);
        $this->subCategoria = new SubCategoria($this->db);
        $this->tipoEntidad = new TipoEntidad($this->db);
    }

    // ---------- Datos para los <select> del formulario ----------

    public function listarPaises()
    {
        return $this->pais->listar();
    }

    public function listarCategorias()
    {
        return $this->categoria->listar();
    }

    public function listarSubCategorias()
    {
        return $this->subCategoria->listar();
    }

    public function listarTiposEntidad()
    {
        return $this->tipoEntidad->listar();
    }

    // ---------- Proyecto ----------

    public function listar()
    {
        return $this->proyecto->listar();
    }

    public function obtener($id)
    {
        return $this->proyecto->obtenerPorId($id);
    }

    private function validar($datos)
    {
        if (trim($datos['titulo'] ?? '') === '') {
            return 'El título es obligatorio.';
        }
        if (empty($datos['id_pais']) || !$this->pais->existeId($datos['id_pais'])) {
            return 'Debe seleccionar un país válido.';
        }
        if (empty($datos['id_categoria'])) {
            return 'Debe seleccionar una categoría.';
        }
        if (empty($datos['id_Tipo_E']) || !$this->tipoEntidad->listar($datos['id_Tipo_E'])) {
            return 'Debe seleccionar un tipo de entidad válido.';
        }
        if (empty($datos['id_subcategoria'])) {
            return 'Debe seleccionar una subcategoría.';
        }
        if (!empty($datos['anio']) && !ctype_digit((string)$datos['anio'])) {
            return 'El año debe ser un número válido.';
        }
        return null;
    }

    public function crear($datos, $idUsuario, $archivoImagen = null, $archivoQr = null)
    {
        $error = $this->validar($datos);
        if ($error) {
            return ['ok' => false, 'msg' => $error];
        }

        $resImg = $this->subirArchivo($archivoImagen, 'media/proyecto', 'proy_');
        if (!$resImg['ok']) return ['ok' => false, 'msg' => $resImg['msg']];

        $resQr = $this->subirArchivo($archivoQr, 'media/qr', 'qr_');
        if (!$resQr['ok']) return ['ok' => false, 'msg' => $resQr['msg']];

        $this->proyecto->titulo = trim($datos['titulo']);
        $this->proyecto->descripcion = trim($datos['descripcion'] ?? '');
        $this->proyecto->organizacion = trim($datos['organizacion'] ?? '');
        $this->proyecto->link = $resQr['path']; // ahora guarda el nombre del archivo QR
        $this->proyecto->red_social = trim($datos['red_social'] ?? '');
        $this->proyecto->imagen_path = $resImg['path'];
        $this->proyecto->anio = !empty($datos['anio']) ? (int)$datos['anio'] : null;
        $this->proyecto->estado = !empty($datos['estado']) ? 1 : 0;
        $this->proyecto->id_pais = (int)$datos['id_pais'];
        $this->proyecto->id_categoria = (int)$datos['id_categoria'];
        $this->proyecto->id_Tipo_E   = (int)$datos['id_Tipo_E'];
        $this->proyecto->id_subcategoria = (int)$datos['id_subcategoria'];
        $this->proyecto->id_usuario = (int)$idUsuario;

        if ($this->proyecto->crear()) {
            return ['ok' => true, 'msg' => 'Proyecto creado correctamente.'];
        }
        return ['ok' => false, 'msg' => 'Ocurrió un error al crear el proyecto.'];
    }

   public function actualizar($id, $datos, $archivoImagen = null, $archivoQr = null)
    {
        $actual = $this->proyecto->obtenerPorId($id);
        if (!$actual) {
            return ['ok' => false, 'msg' => 'El proyecto no existe.'];
        }

        $error = $this->validar($datos);
        if ($error) {
            return ['ok' => false, 'msg' => $error];
        }

        $resImg = $this->subirArchivo($archivoImagen, 'media/proyecto', 'proy_', $actual['imagen_path']);
        if (!$resImg['ok']) {
            return ['ok' => false, 'msg' => $resImg['msg']];
        }

        $resQr = $this->subirArchivo($archivoQr, 'media/qr', 'qr_', $actual['link']);
        if (!$resQr['ok']) {
            return ['ok' => false, 'msg' => $resQr['msg']];
        }

        $this->proyecto->id = $id;
        $this->proyecto->titulo = trim($datos['titulo']);
        $this->proyecto->descripcion = trim($datos['descripcion'] ?? '');
        $this->proyecto->organizacion = trim($datos['organizacion'] ?? '');
        $this->proyecto->link = $resQr['path'];
        $this->proyecto->red_social = trim($datos['red_social'] ?? '');
        $this->proyecto->imagen_path = $resImg['path'];
        $this->proyecto->anio = !empty($datos['anio']) ? (int)$datos['anio'] : null;
        $this->proyecto->estado = !empty($datos['estado']) ? 1 : 0;
        $this->proyecto->id_pais = (int)$datos['id_pais'];
        $this->proyecto->id_categoria = (int)$datos['id_categoria'];
        $this->proyecto->id_subcategoria = (int)$datos['id_subcategoria'];
        $this->proyecto->id_Tipo_E = (int)$datos['id_Tipo_E'];

        if ($this->proyecto->actualizar()) {
            return ['ok' => true, 'msg' => 'Proyecto actualizado correctamente.'];
        }
        return ['ok' => false, 'msg' => 'Ocurrió un error al actualizar el proyecto.'];
    }

    public function eliminar($id)
    {
        if (!$this->proyecto->existeId($id)) {
            return ['ok' => false, 'msg' => 'El proyecto no existe.'];
        }
        if ($this->proyecto->eliminar($id)) {
            return ['ok' => true, 'msg' => 'Proyecto desactivado correctamente.'];
        }
        return ['ok' => false, 'msg' => 'Ocurrió un error al desactivar el proyecto.'];
    }


// Método genérico de subida 
    private function subirArchivo($archivo, $subcarpeta, $prefijo, $actual = null)
    {
        if (empty($archivo) || $archivo['error'] === UPLOAD_ERR_NO_FILE) {
            return ['ok' => true, 'path' => $actual];
        }

        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'msg' => 'Ocurrió un error al subir el archivo (código ' . $archivo['error'] . ').'];
        }

        $permitidos = [
            'image/jpeg' => 'jpg', 'image/png' => 'png',
            'image/webp' => 'webp', 'image/gif' => 'gif',
        ];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $archivo['tmp_name']);
        finfo_close($finfo);

        if (!isset($permitidos[$mime])) {
            return ['ok' => false, 'msg' => 'Formato no permitido. Solo JPG, PNG, WEBP o GIF.'];
        }

        $directorioDestino = __DIR__ . '/../' . $subcarpeta;

        if (!is_dir($directorioDestino)) {
            if (!mkdir($directorioDestino, 0755, true) && !is_dir($directorioDestino)) {
                return ['ok' => false, 'msg' => 'No se pudo crear el directorio de destino.'];
            }
        }

        $nombreArchivo = uniqid($prefijo, true) . '.' . $permitidos[$mime];
        $rutaDestino   = $directorioDestino . '/' . $nombreArchivo;

        if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            return ['ok' => false, 'msg' => 'No se pudo guardar el archivo en el servidor.'];
        }

        if (!empty($actual)) {
            $rutaAnterior = $directorioDestino . '/' . basename($actual);
            if (is_file($rutaAnterior)) @unlink($rutaAnterior);
        }

        return ['ok' => true, 'path' => $nombreArchivo];
    }


    public function listarActivosPorPais($nombrePais)
    {
        return $this->proyecto->listarActivosPorPais($nombrePais);
    }


    public function listarPaisesConProyectos()
    {
        return $this->proyecto->listarPaisesConProyectos();
    }
}