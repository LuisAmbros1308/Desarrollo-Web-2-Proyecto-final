<?php
require 'datos/ConexionBD.php';
require 'controladores/usuarios.php';
require 'controladores/servicios.php';
require 'controladores/empleados.php';
require 'controladores/productos.php';
/* require 'controladores/tiposEvento.php';
require 'controladores/documentos.php';  */
require 'vistas/VistaXML.php';
require 'vistas/VistaJson.php';
require 'utilidades/ExcepcionApi.php';

// Constantes de estado
const ESTADO_URL_INCORRECTA = 2;
const ESTADO_EXISTENCIA_RECURSO = 3;
const ESTADO_METODO_NO_PERMITIDO = 4;

//Validar si se llama para devolver datos en formato xml o json
$formato = isset($_GET['formato']) ? $_GET['formato'] : 'json';

switch ($formato) {
    case 'xml':
        $vista = new VistaXML();
        break;
    case 'json':
    default:
        $vista = new VistaJson();
}

// Preparar manejo de excepciones
set_exception_handler(function ($exception) use ($vista) {
    $cuerpo = array(
        "estado" => $exception->estado,
        "mensaje" => $exception->getMessage()
    );
    if ($exception->getCode()) {
        $vista->estado = $exception->getCode();
    } else {
        $vista->estado = 500;
    }

    $vista->imprimir($cuerpo);
}
);

// Extraer segmento de la url
if (isset($_GET['PATH_INFO']))
    $peticion = explode('/', $_GET['PATH_INFO']);
else
    throw new ExcepcionApi(ESTADO_URL_INCORRECTA, utf8_encode("No se reconoce la petición"));

// Obtener recurso
$recurso = array_shift($peticion);
$recursos_existentes = array('usuarios', 'tiposEvento', 'documentos', 'servicios','empleados','productos');

// Comprobar si existe el recurso
if (!in_array($recurso, $recursos_existentes)) {
    throw new ExcepcionApi(ESTADO_EXISTENCIA_RECURSO,
        "No se reconoce el recurso al que intentas acceder");
}

$metodo = strtolower($_SERVER['REQUEST_METHOD']);

// Filtrar método
switch ($metodo) {

    case 'get':
       //$vista->imprimir(usuarios::obtenerIdUsuario($peticion));
       //$vista->imprimir(usuarios::get($peticion));
        //break;
    case 'post':
        //$vista->imprimir(usuarios::insertar($peticion));
        //$vista->imprimir(usuarios::post($peticion));
        //break;
    case 'put':
        //$vista->imprimir(usuarios::modificar($peticion));
        //$vista->imprimir(usuarios::put($peticion));
        //break;
    case 'delete':
         //$vista->imprimir(usuarios::borrar($peticion));
        //$vista->imprimir(usuarios::delete($peticion));
        //break;
        
        if (method_exists($recurso, $metodo)) {
            $respuesta = call_user_func(array($recurso, $metodo), $peticion);
            $vista->imprimir($respuesta);

            break;
        }

    default:
        // Método no aceptado
        $vista->estado = 405;
        $cuerpo = [
            "estado" => ESTADO_METODO_NO_PERMITIDO,
            "mensaje" => utf8_encode("Método no permitido")
        ];
        $vista->imprimir($cuerpo);
}
?>

