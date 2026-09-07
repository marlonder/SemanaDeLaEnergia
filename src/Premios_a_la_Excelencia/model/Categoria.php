<?php
/**
 * Modelo Categoria
 * Tabla `categoria`: id, nombre
 */
class Categoria
{
    private $conn;
    private $tabla = "categoria";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function listar()
    {
        $sql = "SELECT id, nombre FROM {$this->tabla} ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT id, nombre FROM {$this->tabla} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function existeNombre($nombre, $idExcluir = null)
    {
        $sql = "SELECT id FROM {$this->tabla} WHERE nombre = :nombre";
        if ($idExcluir) {
            $sql .= " AND id != :id";
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        if ($idExcluir) {
            $stmt->bindParam(':id', $idExcluir, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function crear($nombre)
    {
        $sql = "INSERT INTO {$this->tabla} (nombre) VALUES (:nombre)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        return $stmt->execute();
    }

    public function actualizar($id, $nombre)
    {
        $sql = "UPDATE {$this->tabla} SET nombre = :nombre WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Puede lanzar PDOException si tiene subcategorías o proyectos asociados (ON DELETE RESTRICT).
    // CategoriaController::eliminarCategoria() captura esa excepción y da un mensaje claro.
    public function eliminar($id)
    {
        $sql = "DELETE FROM {$this->tabla} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}