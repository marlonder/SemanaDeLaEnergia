<?php
/**
 * Conexión a la base de datos usando PDO (MySQL).
 */
class Conexion
{
    private $host = "192.168.1.205";
    private $port = "3307";
    private $db_name = "SemanaDelaEnergia";
    private $username = "root";
    private $password = "Olacde.2026";
    public $conn;

    public function conectar()
    {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }

        return $this->conn;
    }
}