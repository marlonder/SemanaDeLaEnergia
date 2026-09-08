<?php
/**
 * Modelo Pais
 * Tabla `pais`: id, nombre
 * Solo lectura por ahora: se usa para el <select> de país en Proyectos.
 * (Si luego quieres administrarlo desde una vista, se le puede agregar
 * crear/actualizar/eliminar igual que a Categoria).
 */
class Pais
{
    private $conn;
    private $tabla = "pais";

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

    public function existeId($id)
    {
        $sql = "SELECT id FROM {$this->tabla} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}