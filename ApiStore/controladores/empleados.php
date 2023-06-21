<?php

class empleados
{
    const NOMBRE_TABLA = "empleado";
    const ID_EMPLEADO = "idEmpleado";
    const NOMBRE_EMPLEADO = "Nombre";
    const APELLIDO_EMPLEADO = "Apellido";
    const CORREO_EMPLEADO = "Correo";
    const ID_USUARIO = "idUsuario";

    const CODIGO_EXITO = 1;
    const ESTADO_EXITO = 1;
    const ESTADO_ERROR = 2;
    const ESTADO_ERROR_BD = 3;
    const ESTADO_ERROR_PARAMETROS = 4;
    const ESTADO_NO_ENCONTRADO = 5;

    /**
     *  /servicios/:id  ---> devuelve los datos del servicio con id = :idParam
     *  /servicios/  ---> devuelve los datos de todos los servicios
     * 
    */

    public static function get($peticion)
    {
        //echo ('$peticion = ' . ($peticion == null));
        //Para validar que se proporcionó una API KEY válida
        $idUsuario = usuarios::autorizar();

        $idEmpleado = $peticion[0];

        if ($idEmpleado != ''){
            if (intval($idEmpleado))
                return self::listarServicio($idEmpleado);
            else {
                throw new ExcepcionApi(self::ESTADO_ERROR_PARAMETROS, "Id no válido ...", 422);
            }
        }
        else
            return self::listarServicios();
    }

    private static function listarServicio($idEmpleado= null)
    {
        try {
            if (isset($idEmpleado)) {
                $comando = "SELECT * FROM " . self::NOMBRE_TABLA .
                    " WHERE " . self::ID_EMPLEADO . "=?";

                //echo ("Valor comando = " . $comando);

                // Preparar sentencia
                $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
                // Ligar idUsuario
                $sentencia->bindParam(1, $idEmpleado, PDO::PARAM_INT);

            }

            // Ejecutar sentencia preparada
            if ($sentencia->execute()) {
                http_response_code(200);
                return
                    [
                        "estado" => self::ESTADO_EXITO,
                        "datos" => $sentencia->fetchAll(PDO::FETCH_ASSOC)
                    ];
            } else
                throw new ExcepcionApi(self::ESTADO_ERROR, "Se ha producido un error");

        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }


    private static function listarServicios()
    {
        try {
            
            $comando = "SELECT * FROM " . self::NOMBRE_TABLA;

            //echo ("Valor comando = " . $comando);

            // Preparar sentencia
            $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);

            // Ejecutar sentencia preparada
            if ($sentencia->execute()) {
                http_response_code(200);
                return
                    [
                        "estado" => self::ESTADO_EXITO,
                        "datos" => $sentencia->fetchAll(PDO::FETCH_ASSOC)
                    ];
            } else
                throw new ExcepcionApi(self::ESTADO_ERROR, "Se ha producido un error");

        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }

    public static function post($peticion)
    {
        $idUsuario = usuarios::autorizar();

        $idEmpleado = $peticion[0];

        $body = file_get_contents('php://input');
        $empleado = json_decode($body);

        $idEmpleado = empleados::crear($empleado);

        http_response_code(201);
        return [
            "estado" => self::CODIGO_EXITO,
            "mensaje" => "Empleado creado",
            "id" => $idEmpleado
        ];

    }

    /**
     * A�ade un nuevo contacto asociado a un usuario
     * @param int $idUsuario identificador del usuario
     * @param mixed $contacto datos del contacto
     * @return string identificador del contacto
     * @throws ExcepcionApi
     */

    
     private static function crear($empleado)
     {
         if ($empleado) {
             try {
                 $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
 
                 
 
                 // Sentencia INSERT
                 $comando = "INSERT INTO " . self::NOMBRE_TABLA . " ( " .
                     self:: ID_EMPLEADO . "," .
                     self:: NOMBRE_EMPLEADO . "," .
                     self:: APELLIDO_EMPLEADO . "," .
                     self:: CORREO_EMPLEADO . "," .
                     self:: ID_USUARIO .  ")" .
                     " VALUES(?,?,?,?,?)";
 
                 //echo "comando" . $comando;
  
                 $idEmpleado= $empleado->idEmpleado;
                 $nombre = $empleado->nombre;
                 $apellido = $empleado->apellido;
                 $correo = $empleado->correo;
                 $idUsuario = $empleado->idUsuario;
 
                 // Preparar la sentencia
                 $sentencia = $pdo->prepare($comando);
 
                 $sentencia->bindParam(1, $idEmpleado);
                 $sentencia->bindParam(2, $nombre);
                 $sentencia->bindParam(3, $apellido);
                 $sentencia->bindParam(4, $correo);
                 $sentencia->bindParam(5, $idUsuario);
 
 
                 
                 $sentencia->execute();
 
                 // Retornar en el �ltimo id insertado
                 return $pdo->lastInsertId();
 
             } catch (PDOException $e) {
                 throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
             }
         } else {
             throw new ExcepcionApi(
                 self::ESTADO_ERROR_PARAMETROS,
                 utf8_encode("Error en existencia o sintaxis de par�metros"));
         }
 
     }

    public static function put($peticion)
    {
        $idUsuario = usuarios::autorizar();

        $idEmpleado = $peticion [0];
        //echo ('$peticion = ' . ($peticion == null));
        if (!empty($peticion[0])) {
            $body = file_get_contents('php://input');
            $empleado = json_decode($body);

            echo "body" . $body;
            

            if (self::actualizar( $peticion[0], $empleado) > 0) {
                http_response_code(200);
                return [
                    "estado" => self::CODIGO_EXITO,
                    "mensaje" => "Registro actualizado correctamente"
                ];
            } else {
                throw new ExcepcionApi(self::ESTADO_NO_ENCONTRADO,
                    "El contacto al que intentas acceder no existe", 404);
            }
        } else {
            throw new ExcepcionApi(self::ESTADO_ERROR_PARAMETROS, "Falta id", 422);
        }
    }

    /**
     * Actualiza el contacto especificado por idUsuario
     * @param int $idUsuario
     * @param object $contacto objeto con los valores nuevos del contacto
     * @param int $idContacto
     * @return PDOStatement
     * @throws Exception
     */
    private static function actualizar($idEmpleado, $empleado) 
    { 
        try { 
            // Creando consulta UPDATE
            $consulta = "UPDATE " . self::NOMBRE_TABLA .
                " SET " . self:: NOMBRE_EMPLEADO . "=?," .
                self:: APELLIDO_EMPLEADO . "=?," .
                self:: CORREO_EMPLEADO . "=?," .
                self:: ID_USUARIO . "=? " .
                " WHERE " . self::ID_EMPLEADO . /*"=? AND " . self::ID_USUARIO . */"=?";


                //echo "consulta " . $consulta;

            // Preparar la sentencia
            $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($consulta);


            $sentencia->bindParam(1, $nombre);
            $sentencia->bindParam(2, $apellido);
            $sentencia->bindParam(3, $correo);
            $sentencia->bindParam(4, $idUsuario);

            $sentencia->bindParam(5, $idEmpleado);

            //$sentencia->bindParam(14, $idUsuario);

            $nombre = $empleado->nombre;
            $apellido = $empleado->apellido;
            $correo = $empleado->correo;
            $idUsuario = $empleado->idUsuario;

            // Ejecutar la sentencia
            $sentencia->execute();

            return $sentencia->rowCount();

        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }

    public static function delete( $peticion)
    {
        $idUsuario = usuarios::autorizar();

        $idEmpleado = $peticion [0];

        if (!empty($peticion[0])) {
            if (self::eliminar($idEmpleado, $peticion[0]) > 0) {
                http_response_code(200);
                return [
                    "estado" => self::CODIGO_EXITO,
                    "mensaje" => "Registro eliminado correctamente",
                    //"registroEliminados" => $numRegs
                ];
            } else {
                throw new ExcepcionApi(self::ESTADO_NO_ENCONTRADO,
                    "El contacto al que intentas acceder no existe", 404);
            }
        } else {
            throw new ExcepcionApi(self::ESTADO_ERROR_PARAMETROS, "Falta id", 422);
        }
    }

    private static function eliminar($idEmpleado)
    {
        try {
            // Sentencia DELETE
            $comando = "DELETE FROM " . self::NOMBRE_TABLA .
                " WHERE " . self::ID_EMPLEADO . /*"=? AND " .
                self::ID_USUARIO .*/ "=?";

            // Preparar la sentencia
            $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);

            $sentencia->bindParam(1, $idEmpleado);
           // $sentencia->bindParam(2, $idUsuario);

            $sentencia->execute();

            return $sentencia->rowCount();

        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }
}