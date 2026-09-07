<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../model/Usuario.php';

class UsuarioController
{
    private $db;
    private $usuario;

    public function __construct()
    {
        $conexion = new Conexion();
        $this->db = $conexion->conectar();
        $this->usuario = new Usuario($this->db);
    }

    public function listar()
    {
        return $this->usuario->listar();
    }

    public function obtener($id)
    {
        return $this->usuario->obtenerPorId($id);
    }

    public function crear($nombres, $apellidos, $password, $estado)
    {
        $nombres = trim($nombres);
        $apellidos = trim($apellidos);

        if ($nombres === '' || trim($password) === '') {
            return ['ok' => false, 'msg' => 'Los nombres y la contraseña son obligatorios.'];
        }

        $this->usuario->nombres = $nombres;
        $this->usuario->apellidos = $apellidos;
        $this->usuario->password_hash = password_hash($password, PASSWORD_DEFAULT);
        $this->usuario->estado = $estado ? 1 : 0;

        if ($this->usuario->crear()) {
            return ['ok' => true, 'msg' => 'Usuario creado correctamente.'];
        }

        return ['ok' => false, 'msg' => 'Ocurrió un error al crear el usuario.'];
    }

    public function actualizar($id, $nombres, $apellidos, $password, $estado)
    {
        if (!$this->usuario->existeId($id)) {
            return ['ok' => false, 'msg' => 'El usuario no existe.'];
        }

        $nombres = trim($nombres);
        $apellidos = trim($apellidos);

        if ($nombres === '') {
            return ['ok' => false, 'msg' => 'El nombre es obligatorio.'];
        }

        $this->usuario->id = $id;
        $this->usuario->nombres = $nombres;
        $this->usuario->apellidos = $apellidos;
        $this->usuario->estado = $estado ? 1 : 0;
        $this->usuario->password_hash = trim($password) !== ''
            ? password_hash($password, PASSWORD_DEFAULT)
            : null;

        if ($this->usuario->actualizar()) {
            return ['ok' => true, 'msg' => 'Usuario actualizado correctamente.'];
        }

        return ['ok' => false, 'msg' => 'Ocurrió un error al actualizar el usuario.'];
    }

    public function login($nombres, $password)
    {
        $nombres = trim($nombres);

        if ($nombres === '' || trim($password) === '') {
            return ['ok' => false, 'msg' => 'Debe ingresar usuario y contraseña.'];
        }

        $usuario = $this->usuario->obtenerPorNombres($nombres);

        // Mensaje genérico para no revelar si el usuario existe o no
        $mensajeError = 'Usuario o contraseña incorrectos.';

        if (!$usuario) {
            return ['ok' => false, 'msg' => $mensajeError];
        }

        if (!$usuario['estado']) {
            return ['ok' => false, 'msg' => 'El usuario está inactivo. Contacte al administrador.'];
        }

        if (!password_verify($password, $usuario['password_hash'])) {
            return ['ok' => false, 'msg' => $mensajeError];
        }

        return [
            'ok' => true,
            'msg' => 'Inicio de sesión exitoso.',
            'usuario' => [
                'id' => $usuario['id'],
                'nombres' => $usuario['nombres'],
                'apellidos' => $usuario['apellidos'],
            ],
        ];
    }

    public function eliminar($id)
    {
        if (!$this->usuario->existeId($id)) {
            return ['ok' => false, 'msg' => 'El usuario no existe.'];
        }

        if ($this->usuario->eliminar($id)) {
            return ['ok' => true, 'msg' => 'Usuario eliminado correctamente.'];
        }

        return ['ok' => false, 'msg' => 'Ocurrió un error al eliminar el usuario.'];
    }
}