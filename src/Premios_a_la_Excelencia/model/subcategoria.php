<?php
class SubCategoria
{
    private $conn;
    private $tabla = "sub_categoria";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Trae todas las subcategorías con el nombre de su categoría (para listar)
    public function listar()
    {
        $sql = "SELECT sc.id, sc.nombre, sc.id_categoria, sc.id_Tipo_E,
                    c.nombre AS categoria_nombre,
                    te.nombre AS tipo_entidad_nombre
                FROM {$this->tabla} sc
                INNER JOIN categoria c ON c.id = sc.id_categoria
                INNER JOIN Tipo_entidad te ON te.id = sc.id_Tipo_E
                ORDER BY c.nombre ASC, te.nombre ASC, sc.nombre ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT id, nombre, id_categoria, id_Tipo_E FROM {$this->tabla} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($nombre, $idCategoria, $id_Tipo_E)
    {
        $sql = "INSERT INTO {$this->tabla} (nombre, id_categoria,id_Tipo_E) VALUES (:nombre, :id_categoria , :id_Tipo_E)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':id_categoria', $idCategoria, PDO::PARAM_INT);
        $stmt->bindParam(':id_Tipo_E', $id_Tipo_E, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function actualizar($id, $nombre, $idCategoria ,$id_Tipo_E)
    {
        $sql = "UPDATE {$this->tabla}
                SET nombre = :nombre, id_categoria = :id_categoria , id_Tipo_E =:id_Tipo_E
                WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':id_categoria', $idCategoria, PDO::PARAM_INT);
        $stmt->bindParam(':id_Tipo_E', $id_Tipo_E, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Puede lanzar PDOException si tiene proyectos asociados (ON DELETE RESTRICT)
    public function eliminar($id)
    {
        $sql = "DELETE FROM {$this->tabla} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}