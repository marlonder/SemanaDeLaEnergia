<?php
/**
 * Modelo Usuario
 * Corresponde a la tabla `usuario`:
 *   id, nombres, apellidos, password_hash, estado, fecha_registro
 */
class Usuario
{
    private $conn;
    private $tabla = "usuario";

    public $id;
    public $nombres;
    public $apellidos;
    public $password_hash;
    public $estado;
    public $fecha_registro;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Listar todos los usuarios
    public function listar()
    {
        $sql = "SELECT id, nombres, apellidos, estado, fecha_registro
                FROM {$this->tabla}
                ORDER BY id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener un usuario por id
    public function obtenerPorId($id)
    {
        $sql = "SELECT id, nombres, apellidos, estado, fecha_registro
                FROM {$this->tabla}
                WHERE id = :id
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Buscar usuario por nombres, incluyendo password_hash y estado (usado para login)
    public function obtenerPorNombres($nombres)
    {
        $sql = "SELECT id, nombres, apellidos, password_hash, estado
                FROM {$this->tabla}
                WHERE nombres = :nombres
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nombres', $nombres);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Verificar si existe un usuario con ese id
    public function existeId($id)
    {
        $sql = "SELECT id FROM {$this->tabla} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // Crear usuario
    public function crear()
    {
        $sql = "INSERT INTO {$this->tabla} (nombres, apellidos, password_hash, estado)
                VALUES (:nombres, :apellidos, :password_hash, :estado)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':nombres', $this->nombres);
        $stmt->bindParam(':apellidos', $this->apellidos);
        $stmt->bindParam(':password_hash', $this->password_hash);
        $stmt->bindParam(':estado', $this->estado, PDO::PARAM_BOOL);

        return $stmt->execute();
    }

    // Actualizar usuario (la contraseña solo se actualiza si se envía una nueva)
    public function actualizar()
    {
        if (!empty($this->password_hash)) {
            $sql = "UPDATE {$this->tabla}
                    SET nombres = :nombres,
                        apellidos = :apellidos,
                        password_hash = :password_hash,
                        estado = :estado
                    WHERE id = :id";
        } else {
            $sql = "UPDATE {$this->tabla}
                    SET nombres = :nombres,
                        apellidos = :apellidos,
                        estado = :estado
                    WHERE id = :id";
        }

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':nombres', $this->nombres);
        $stmt->bindParam(':apellidos', $this->apellidos);
        $stmt->bindParam(':estado', $this->estado, PDO::PARAM_BOOL);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);

        if (!empty($this->password_hash)) {
            $stmt->bindParam(':password_hash', $this->password_hash);
        }

        return $stmt->execute();
    }

    // Eliminar usuario (borrado logico)
    public function eliminar($id)
    {
        $sql = "UPDATE {$this->tabla} SET estado = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
 
        return $stmt->execute();
    }
}