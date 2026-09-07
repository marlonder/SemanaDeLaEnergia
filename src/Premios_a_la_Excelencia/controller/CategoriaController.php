<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../model/Categoria.php';
require_once __DIR__ . '/../model/subcategoria.php';

class CategoriaController
{
    private $db;
    private $categoria;
    private $subCategoria;

    public function __construct()
    {
        $conexion = new Conexion();
        $this->db = $conexion->conectar();
        $this->categoria = new Categoria($this->db);
        $this->subCategoria = new SubCategoria($this->db);
    }

    // ---------- CATEGORÍA ----------

    public function listarCategorias()
    {
        return $this->categoria->listar();
    }

    public function obtenerCategoria($id)
    {
        return $this->categoria->obtenerPorId($id);
    }

    public function crearCategoria($nombre)
    {
        $nombre = trim($nombre);

        if ($nombre === '') {
            return ['ok' => false, 'msg' => 'El nombre de la categoría es obligatorio.'];
        }
        if ($this->categoria->existeNombre($nombre)) {
            return ['ok' => false, 'msg' => 'Ya existe una categoría con ese nombre.'];
        }

        if ($this->categoria->crear($nombre)) {
            return ['ok' => true, 'msg' => 'Categoría creada correctamente.'];
        }
        return ['ok' => false, 'msg' => 'Ocurrió un error al crear la categoría.'];
    }

    public function actualizarCategoria($id, $nombre)
    {
        $nombre = trim($nombre);

        if (!$this->categoria->obtenerPorId($id)) {
            return ['ok' => false, 'msg' => 'La categoría no existe.'];
        }
        if ($nombre === '') {
            return ['ok' => false, 'msg' => 'El nombre de la categoría es obligatorio.'];
        }
        if ($this->categoria->existeNombre($nombre, $id)) {
            return ['ok' => false, 'msg' => 'Ya existe otra categoría con ese nombre.'];
        }

        if ($this->categoria->actualizar($id, $nombre)) {
            return ['ok' => true, 'msg' => 'Categoría actualizada correctamente.'];
        }
        return ['ok' => false, 'msg' => 'Ocurrió un error al actualizar la categoría.'];
    }

    public function eliminarCategoria($id)
    {
        if (!$this->categoria->obtenerPorId($id)) {
            return ['ok' => false, 'msg' => 'La categoría no existe.'];
        }

        try {
            if ($this->categoria->eliminar($id)) {
                return ['ok' => true, 'msg' => 'Categoría eliminada correctamente.'];
            }
            return ['ok' => false, 'msg' => 'Ocurrió un error al eliminar la categoría.'];
        } catch (PDOException $e) {
            return ['ok' => false, 'msg' => 'No se puede eliminar: la categoría tiene subcategorías o proyectos asociados.'];
        }
    }

    // ---------- SUBCATEGORÍA ----------

    public function listarSubCategorias()
    {
        return $this->subCategoria->listar();
    }

    public function obtenerSubCategoria($id)
    {
        return $this->subCategoria->obtenerPorId($id);
    }

    public function crearSubCategoria($nombre, $idCategoria)
    {
        $nombre = trim($nombre);

        if ($nombre === '') {
            return ['ok' => false, 'msg' => 'El nombre de la subcategoría es obligatorio.'];
        }
        if (!$idCategoria || !$this->categoria->obtenerPorId($idCategoria)) {
            return ['ok' => false, 'msg' => 'Debe seleccionar una categoría válida.'];
        }

        if ($this->subCategoria->crear($nombre, $idCategoria)) {
            return ['ok' => true, 'msg' => 'Subcategoría creada correctamente.'];
        }
        return ['ok' => false, 'msg' => 'Ocurrió un error al crear la subcategoría.'];
    }

    public function actualizarSubCategoria($id, $nombre, $idCategoria)
    {
        $nombre = trim($nombre);

        if (!$this->subCategoria->obtenerPorId($id)) {
            return ['ok' => false, 'msg' => 'La subcategoría no existe.'];
        }
        if ($nombre === '') {
            return ['ok' => false, 'msg' => 'El nombre de la subcategoría es obligatorio.'];
        }
        if (!$idCategoria || !$this->categoria->obtenerPorId($idCategoria)) {
            return ['ok' => false, 'msg' => 'Debe seleccionar una categoría válida.'];
        }

        if ($this->subCategoria->actualizar($id, $nombre, $idCategoria)) {
            return ['ok' => true, 'msg' => 'Subcategoría actualizada correctamente.'];
        }
        return ['ok' => false, 'msg' => 'Ocurrió un error al actualizar la subcategoría.'];
    }

    public function eliminarSubCategoria($id)
    {
        if (!$this->subCategoria->obtenerPorId($id)) {
            return ['ok' => false, 'msg' => 'La subcategoría no existe.'];
        }

        try {
            if ($this->subCategoria->eliminar($id)) {
                return ['ok' => true, 'msg' => 'Subcategoría eliminada correctamente.'];
            }
            return ['ok' => false, 'msg' => 'Ocurrió un error al eliminar la subcategoría.'];
        } catch (PDOException $e) {
            return ['ok' => false, 'msg' => 'No se puede eliminar: la subcategoría tiene proyectos asociados.'];
        }
    }
}