<?php
 /* se pued eponer /servicios/file=arch.csv podemos generar nuestra propias url
 para poder cargar un archivo 
 */
//require "usuarios.php";
class servicios
{
    const NOMBRE_TABLA = "servicio";
    const ID_SERVICIO = "id";
    const FECHA_REGISTRO = "fechaRegistro";
    const HORA_REGISTRO = "horaRegistro";
    const ID_PACIENTE = "idPaciente";
    const ID_EMPLEADO = "idEmpleado";
    const FECHA_ENTREGA = "fechaEntrega";
    const HORA_ENTREGA = "horaEntrega";
    const EDAD_PACIENTE = "edadPaciente";
    const TOTAL = "total";
    const DESCUENTO = "descuento";
    const IVA = "iva";
    const IMPORTE = "importe";
    const OBSERVACIONES = "observaciones";

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

        $idServicio = $peticion[0];

        if ($idServicio != ''){
            if (intval($idServicio))
                return self::listarServicio($idServicio);
            else {
                throw new ExcepcionApi(self::ESTADO_ERROR_PARAMETROS, "Id no válido ...", 422);
            }
        }
        else
            return self::listarServicios();
    }

    public static function post($peticion)
    {
        $idUsuario = usuarios::autorizar();

        $body = file_get_contents('php://input');
        $servicio = json_decode($body);

        $idServicio = servicios::crear($servicio);

        http_response_code(201);
        return [
            "estado" => self::CODIGO_EXITO,
            "mensaje" => "Servicio creado",
            "id" => $idServicio
        ];

    }

    public static function put($peticion)
    {
        $idUsuario = usuarios::autorizar();
        //echo ('$peticion = ' . ($peticion == null));
        if (!empty($peticion[0])) {
            $body = file_get_contents('php://input');
            $servicio = json_decode($body);

            //echo "body" . $body;
            

            if (self::actualizar($idUsuario, $servicio, $peticion[0]) > 0) {
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

    public static function delete( $peticion)
    {
        $idUsuario = usuarios::autorizar();

        if (!empty($peticion[0])) {
            if (self::eliminar($id, $peticion[0]) > 0) {
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

    /**
     * Obtiene la colecci�n de contactos o un solo contacto indicado por el identificador
     * @param int $idUsuario identificador del usuario
     * @param null $idContacto identificador del contacto (Opcional)
     * @return array registros de la tabla contacto
     * @throws Exception
     */

    private static function listarServicio($id)
    {
        try {
            if (isset($id)) {
                $comando = "SELECT * FROM " . self::NOMBRE_TABLA .
                    " WHERE " . self::ID_SERVICIO . "=?";

                //echo ("Valor comando = " . $comando);

                // Preparar sentencia
                $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
                // Ligar idUsuario
                $sentencia->bindParam(1, $id, PDO::PARAM_INT);

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

    /**
     * A�ade un nuevo contacto asociado a un usuario
     * @param int $idUsuario identificador del usuario
     * @param mixed $contacto datos del contacto
     * @return string identificador del contacto
     * @throws ExcepcionApi
     */

    
    private static function crear($servicio)
    {
        if ($servicio) {
            try {

                $pdo = ConexionBD::obtenerInstancia()->obtenerBD();

                

                // Sentencia INSERT
                $comando = "INSERT INTO " . self::NOMBRE_TABLA . " ( " .
                    self:: FECHA_REGISTRO . "," .
                    self:: HORA_REGISTRO. "," .
                    self::ID_PACIENTE . "," .
                    self::ID_EMPLEADO . "," .
                    self::FECHA_ENTREGA .  "," .
                    self:: HORA_ENTREGA . "," .
                    self:: EDAD_PACIENTE. "," .
                    self::TOTAL . "," .
                    self::DESCUENTO . "," .
                    self::IVA . "," .
                    self::IMPORTE . "," .
                    self::OBSERVACIONES . ")" .
                    " VALUES(?,?,?,?,?,?,?,?,?,?,?,?)";

                //echo "comando" . $comando;
 
                $fechaRegistro = $servicio->fechaRegistro;
                $horaRegistro = $servicio->horaRegistro;
                $idPaciente = $servicio->idPaciente;
                $idEmpleado = $servicio->idEmpleado;
                $fechaEntrega = $servicio->fechaEntrega;
                $horaEntrega = $servicio->horaEntrega;
                $edadPaciente = $servicio->edadPaciente;
                $total = $servicio->total;
                $descuento = $servicio->descuento;
                $iva = $servicio->iva;
                $importe = $servicio->importe;
                $observaciones = $servicio->observaciones;

                // Preparar la sentencia
                //$sentencia = $pdo->prepare($comando);

                $sentencia->bindParam(1, $fechaRegistro);
                $sentencia->bindParam(2, $horaRegistro);
                $sentencia->bindParam(3, $idPaciente);
                $sentencia->bindParam(4, $idEmpleado);
                $sentencia->bindParam(5, $fechaEntrega);
                $sentencia->bindParam(6, $horaEntrega);
                $sentencia->bindParam(7, $edadPaciente);
                $sentencia->bindParam(8, $total);
                $sentencia->bindParam(9, $descuento);
                $sentencia->bindParam(10, $iva);
                $sentencia->bindParam(11, $importe);
                $sentencia->bindParam(12, $observaciones);


                
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

    /**
     * Actualiza el contacto especificado por idUsuario
     * @param int $idUsuario
     * @param object $contacto objeto con los valores nuevos del contacto
     * @param int $idContacto
     * @return PDOStatement
     * @throws Exception
     */
    private static function actualizar($idUsuario, $idServicio, $servicio) 
    { 
        try { 
            // Creando consulta UPDATE
            $consulta = "UPDATE " . self::NOMBRE_TABLA .
                " SET " . self:: FECHA_REGISTRO . "=?," .
                self::HORA_REGISTRO . "=?," .
                self::ID_PACIENTE . "=?," .
                self::ID_EMPLEADO . "=?," .
                self::FECHA_ENTREGA . "=?," .
                self::HORA_ENTREGA . "=?, " .
                self::EDAD_PACIENTE . "=?, " .
                self::TOTAL . "=?, " .
                self::DESCUENTO . "=?," .
                self::IVA . "=?, " .
                self::IMPORTE . "=?, " .
                self::OBSERVACIONES . "=? " .
                " WHERE " . self::ID_SERVICIO . /*"=? AND " . self::ID_USUARIO . */"=?";

                //echo "consulta " . $consulta;

            // Preparar la sentencia
            $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($consulta);


            $sentencia->bindParam(1, $fechaRegistro);
            $sentencia->bindParam(2, $horaRegistro);
            $sentencia->bindParam(3, $idPaciente);
            $sentencia->bindParam(4, $idEmpleado);
            $sentencia->bindParam(5, $fechaEntrega);
            $sentencia->bindParam(6, $horaEntrega);
            $sentencia->bindParam(7, $edadPaciente);
            $sentencia->bindParam(8, $total);
            $sentencia->bindParam(9, $descuento);
            $sentencia->bindParam(10, $iva);
            $sentencia->bindParam(11, $importe);
            $sentencia->bindParam(12, $observaciones);

            $sentencia->bindParam(13, $idServicio);
            //$sentencia->bindParam(14, $idUsuario);

            $fechaRegistro = $servicio->fechaRegistro;
            $horaRegistro = $servicio->horaRegistro;
            $idPaciente = $servicio->idPaciente;
            $idEmpleado = $servicio->idEmpleado;
            $fechaEntrega = $servicio->fechaEntrega;
            $horaEntrega = $servicio->horaEntrega;
            $edadPaciente = $servicio->edadPaciente;
            $total = $servicio->total;
            $descuento = $servicio->descuento;
            $iva = $servicio->iva;
            $importe = $servicio->importe;
            $observaciones = $servicio->observaciones;

            // Ejecutar la sentencia
            $sentencia->execute();

            return $sentencia->rowCount();

        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }


    /**
     * Elimina un contacto asociado a un usuario
     * @param int $idUsuario identificador del usuario
     * @param int $idContacto identificador del contacto
     * @return bool true si la eliminaci�n se pudo realizar, en caso contrario false
     * @throws Exception excepcion por errores en la base de datos
     */

    
    private static function eliminar($idUsuario)
    {
        try {
            // Sentencia DELETE
            $comando = "DELETE FROM " . self::NOMBRE_TABLA .
                " WHERE " . self::ID_SERVICIO . /*"=? AND " .
                self::ID_USUARIO .*/ "=?";

            // Preparar la sentencia
            $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);

            $sentencia->bindParam(1, $idServicio);
           // $sentencia->bindParam(2, $idUsuario);

            $sentencia->execute();

            return $sentencia->rowCount();

        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }

    
}

