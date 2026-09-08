<?php
/**
 * Modelo Proyecto
 * Tabla `proyecto`: id, titulo, descripcion, organizacion, link, red_social,
 * imagen_path, anio, estado, fecha_registro, fecha_actualizacion,
 * id_pais, id_categoria, id_subcategoria, id_usuario
 */
class Proyecto
{
    private $conn;
    private $tabla = "proyecto";

    public $id;
    public $titulo;
    public $descripcion;
    public $organizacion;
    public $link;
    public $red_social;
    public $imagen_path;
    public $anio;
    public $estado;
    public $id_pais;
    public $id_categoria;
    public $id_Tipo_E;
    public $id_subcategoria;
    public $id_usuario;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Listado con nombres legibles (para la tabla)
    public function listar()
    {
        $sql = "SELECT p.id, p.titulo, p.organizacion, p.anio, p.estado,
               pa.nombre AS pais_nombre,
               c.nombre AS categoria_nombre,
               te.nombre AS tipo_entidad_nombre,
               sc.nombre AS subcategoria_nombre,
               u.nombres AS usuario_nombres
        FROM {$this->tabla} p
        INNER JOIN pais pa ON pa.id = p.id_pais
        INNER JOIN categoria c ON c.id = p.id_categoria
        INNER JOIN Tipo_entidad te ON te.id = p.id_Tipo_E
        INNER JOIN sub_categoria sc ON sc.id = p.id_subcategoria
        INNER JOIN usuario u ON u.id = p.id_usuario
        ORDER BY p.id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Detalle completo (para precargar el formulario de edición)
    public function obtenerPorId($id)
    {
        $sql = "SELECT p.*, u.nombres AS usuario_nombres
                FROM {$this->tabla} p
                INNER JOIN usuario u ON u.id = p.id_usuario
                WHERE p.id = :id
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function existeId($id)
    {
        $sql = "SELECT id FROM {$this->tabla} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function crear()
    {
        $sql = "INSERT INTO {$this->tabla}
            (titulo, descripcion, organizacion, link, red_social, imagen_path,
             anio, estado, id_pais, id_categoria, id_Tipo_E, id_subcategoria, id_usuario)
        VALUES
            (:titulo, :descripcion, :organizacion, :link, :red_social, :imagen_path,
             :anio, :estado, :id_pais, :id_categoria, :id_Tipo_E, :id_subcategoria, :id_usuario)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':titulo', $this->titulo);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':organizacion', $this->organizacion);
        $stmt->bindParam(':link', $this->link);
        $stmt->bindParam(':red_social', $this->red_social);
        $stmt->bindParam(':imagen_path', $this->imagen_path);
        $stmt->bindParam(':anio', $this->anio, PDO::PARAM_INT);
        $stmt->bindParam(':estado', $this->estado, PDO::PARAM_BOOL);
        $stmt->bindParam(':id_pais', $this->id_pais, PDO::PARAM_INT);
        $stmt->bindParam(':id_categoria', $this->id_categoria, PDO::PARAM_INT);
        $stmt->bindParam(':id_subcategoria', $this->id_subcategoria, PDO::PARAM_INT);
        $stmt->bindParam(':id_usuario', $this->id_usuario, PDO::PARAM_INT);
        $stmt->bindParam(':id_Tipo_E', $this->id_Tipo_E, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // No permite cambiar el dueño (id_usuario) del proyecto, solo sus datos
    public function actualizar()
    {
        $sql = "UPDATE {$this->tabla}
                SET titulo = :titulo,
                    descripcion = :descripcion,
                    organizacion = :organizacion,
                    link = :link,
                    red_social = :red_social,
                    imagen_path = :imagen_path,
                    anio = :anio,
                    estado = :estado,
                    id_pais = :id_pais,
                    id_categoria = :id_categoria,
                    id_Tipo_E = :id_Tipo_E,
                    id_subcategoria = :id_subcategoria
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':titulo', $this->titulo);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':organizacion', $this->organizacion);
        $stmt->bindParam(':link', $this->link);
        $stmt->bindParam(':red_social', $this->red_social);
        $stmt->bindParam(':imagen_path', $this->imagen_path);
        $stmt->bindParam(':anio', $this->anio, PDO::PARAM_INT);
        $stmt->bindParam(':estado', $this->estado, PDO::PARAM_BOOL);
        $stmt->bindParam(':id_pais', $this->id_pais, PDO::PARAM_INT);
        $stmt->bindParam(':id_categoria', $this->id_categoria, PDO::PARAM_INT);
        $stmt->bindParam(':id_Tipo_E', $this->id_Tipo_E, PDO::PARAM_INT);
        $stmt->bindParam(':id_subcategoria', $this->id_subcategoria, PDO::PARAM_INT);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Borrado lógico: solo desactiva el proyecto (igual que usuario)
    public function eliminar($id)
    {
        $sql = "UPDATE {$this->tabla} SET estado = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}