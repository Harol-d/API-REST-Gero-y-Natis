<?php
include "../Modelo/Conexion.php";

class Usuario
{
    private $documento;
    private $tipoDocumento;
    private $nombre;
    private $apellido;
    private $direccion;
    private $localidad;
    private $telefono;
    private $correo;
    private $estado;
    private $rol;
    private $contrasena;
    private $Conexion;

    public function __construct($documento = null, $tipoDocumento = null, $nombre = null, $apellido = null, $direccion = null, $localidad = null, $correo = null, $contrasena = null, $estado = null, $Conexion = null, $telefono = null, $rol = null)
    {
        $this->documento = $documento;
        $this->tipoDocumento = $tipoDocumento;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->direccion = $direccion;
        $this->localidad = $localidad;
        $this->telefono = $telefono;
        $this->correo = $correo;
        $this->estado = $estado;
        $this->rol = $rol;
        $this->contrasena = $contrasena;
        $this->Conexion = Conectarse();
    }

    public function obtenerUsuarios()
    {
        $sql = "SELECT `documento`, `tipoDocumento`, `nombre`, `apellido`, `direccion`, `localidad`, `telefono`, `correo`, 
                (SELECT estados.tiposestados FROM estados WHERE `id_estado` = idestado) AS idestado, 
                (SELECT rol.rol FROM rol WHERE usuario.`idrol`= rol.idrol) AS idrol 
                FROM `usuario` WHERE `id_estado`=3;";
    
        $resultado = $this->Conexion->query($sql);
        // No cierres la conexión aquí
        return $resultado;
    }
    
    public function obtenerUsuariosi()
    {
        $sqli = "SELECT `documento`, `tipoDocumento`, `nombre`, `apellido`, `direccion`, `localidad`, `telefono`, `correo`, 
                 (SELECT estados.tiposestados FROM estados WHERE `id_estado` = idestado) AS idestado, 
                 (SELECT rol.rol FROM rol WHERE usuario.`idrol`= rol.idrol) AS idrol 
                 FROM `usuario` WHERE `id_estado`=4;";
    
        $resultadoi = $this->Conexion->query($sqli);
        // No cierres la conexión aquí
        return $resultadoi;
    }
    

       public function añadirUsuario($documento, $tipoDocumento, $nombre, $apellido, $direccion, $localidad, $telefono, $correo, $estado, $rol, $contrasena)
    {
        // Inicia la transacción
        $this->Conexion->begin_transaction();
    
        try {
            // Inserta el usuario en la tabla 'usuario'
            $sqlUsuario = "INSERT INTO `usuario`(`documento`, `tipoDocumento`, `nombre`, `apellido`, `direccion`, `localidad`, `telefono`, `correo`, `id_estado`, `idrol`) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            if ($stmtUsuario = $this->Conexion->prepare($sqlUsuario)) {
                $stmtUsuario->bind_param("isssssssii", $documento, $tipoDocumento, $nombre, $apellido, $direccion, $localidad, $telefono, $correo, $estado, $rol);
    
                if ($stmtUsuario->execute()) {
                    // Inserción en la tabla 'sesion' para almacenar la contraseña
                    $sqlSesion = "INSERT INTO `sesion`(`documento`, `contrasena`) VALUES (?, AES_ENCRYPT(?, 'empanadaslomejor4'))";
                    if ($stmtSesion = $this->Conexion->prepare($sqlSesion)) {
                        // Aquí se pasa la contraseña y el documento
                        $stmtSesion->bind_param("is", $documento, $contrasena);
    
                        if ($stmtSesion->execute()) {
                            // Todo ha ido bien, confirmamos la transacción
                            $this->Conexion->commit();
                            header("Location: ../Controlador/controladorUsuario.php");
                            exit;
                        } else {
                            // Error al añadir la sesión, lanzamos excepción
                            throw new Exception("Error al añadir la sesión: " . $stmtSesion->error);
                        }
    
                        $stmtSesion->close();
                    } else {
                        throw new Exception("Error al preparar la consulta para la tabla de sesión: " . $this->Conexion->error);
                    }
                } else {
                    throw new Exception("Error al añadir el usuario: " . $stmtUsuario->error);
                }
    
                $stmtUsuario->close();
            } else {
                throw new Exception("Error al preparar la consulta para la tabla de usuario: " . $this->Conexion->error);
            }
        } catch (Exception $e) {
            // Algo ha fallado, hacemos rollback
            $this->Conexion->rollback();
            echo $e->getMessage();
        }
    }
    

public function borrarUsuario( $documento, $tipoDocumento, $nombre, $apellido, $direccion, $localidad,$telefono,$correo,$estado){
    $this->Conexion = Conectarse();

    $sql = "UPDATE `usuario` SET `id_estado`= '4'WHERE documento=?";
    $stmt = $this->Conexion->prepare($sql);
    $stmt->bind_param("i", $documento);
    $resultado = $stmt->execute();
    $stmt->close();
    $this->Conexion->close();
    if ($resultado) {
        header("Location: ../Controlador/controladorUsuario.php?success=1");
        exit();
    }

    return $resultado;
}

public function actualizarUsuario( $documento, $nombre, $apellido, $direccion, $localidad,$telefono,$correo,$estado, $rol ){
    $this->Conexion = Conectarse();
    $sql = 'UPDATE `usuario` SET `nombre`=?,`apellido`=?,`direccion`=?,`localidad`=?,`telefono`=?,`correo`=?,`id_estado`=?,`idrol`=? WHERE `documento`=?';
    $stmt = $this->Conexion->prepare($sql);
    $stmt->bind_param("ssssisiii", $nombre, $apellido, $direccion, $localidad,$telefono,$correo,$estado, $rol,$documento);
    $resultado = $stmt->execute();
    $stmt->close();
    $this->Conexion->close();
    return $resultado;
}

public function activaUsuario( $documento, $tipoDocumento, $nombre, $apellido, $direccion, $localidad,$telefono,$correo,$estado){
    $this->Conexion = Conectarse();

    $sql = "UPDATE `usuario` SET `id_estado`= '3' WHERE documento=?";
    $stmt = $this->Conexion->prepare($sql);
    $stmt->bind_param("i", $documento);
    $resulta = $stmt->execute();
    $stmt->close();
    $this->Conexion->close();
    if ($resulta) {
        header("Location: ../Controlador/controladorUsuario.php?success=1");
        exit();
    }

    return $resulta;
}}
?>
