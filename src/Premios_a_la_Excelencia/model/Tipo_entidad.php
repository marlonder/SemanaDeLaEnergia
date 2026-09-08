<?php
class TipoEntidad
{
    private $conn;
    private $tabla = "Tipo_entidad";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function listar()
    {
        $sql = "SELECT id, nombre FROM {$this->tabla} ORDER BY id ASC";
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
}