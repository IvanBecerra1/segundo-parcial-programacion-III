<?php 
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseMW;

//require_once "./IMiddleware.php";
require_once __DIR__ . "/./Usuario.php";
require_once __DIR__ . "/./Auto.php";
class MW{

    /**
     * Verifico que existan esos campos
     * MIDDLEWARE 1
     */
    public function verificarCorreoClave(Request $request, RequestHandler $handler): ResponseMW
    {
        $response = new ResponseMW();

        try{
            $arrayDeParametros = $request->getParsedBody();
            
            if (!isset($arrayDeParametros) || empty($arrayDeParametros)){
                throw new \Exception("Error: Objeto no proporcionado");
            }
            $obj_json = json_decode($arrayDeParametros["obj_json"]);
        
            
            //GENERO UNA NUEVA RESPUESTA
            $error = [];

            if (!isset($obj_json->correo) || !isset($obj_json->clave)) {
                $error["clave-correo"] = "Correo y/o clave no proporcionados";
            }
            if (!empty($error)){
                return self::respuesta($response, false, $error, 403);
            }
            // Invocar al siguiente Middleware
            $response = $handler->handle($request);

            return $response;
        } catch (\Exception $error) {
            $mensaje = [];
            $mensaje["error"] = $error->getMessage();
            $mensaje["mensaje"] = "se produjo un error, verifica si se estan pasando los datos correctamente";

            return self::respuesta($response, false, $mensaje, 403);
        }
    }   
    /**
     * Verifico que no esten vacios
     * MIDDLEWARE 2
     */
    public static function verificarCampos(Request $request, RequestHandler $handler): ResponseMW
    {
        $response = new ResponseMW();
        try {
           
            $arrayDeParametros = $request->getParsedBody();
            if (!isset($arrayDeParametros) || empty($arrayDeParametros)){
                throw new \Exception("Error: Objeto no proporcionado");
            }

            $json = json_decode($arrayDeParametros["obj_json"]);

            //GENERO UNA NUEVA RESPUESTA
            $error = [];

            if (empty($json->correo) || empty($json->clave)) {
                $error["correo-clave"] = "Correo y/o clave vacios";
            }

            if (!empty($error)){
                return self::respuesta($response,false, $error, 403);
            }

            // Invocar al siguiente Middleware
            $response = $handler->handle($request);

            return $response;
        } catch (\Exception $error) {
            $mensaje = [];
            $mensaje["error"] = $error->getMessage();
            $mensaje["mensaje"] = "se produjo un error, verifica si se estan pasando los datos correctamente";

            return self::respuesta($response, false, $mensaje, 403);
        }
    }    
    /**
    * Verifico si existen correo y clave en la base de datos.
    * MIDDLEWARE 3
    */

    public function verificarExistenciaCorreoClave(Request $request, RequestHandler $handler): ResponseMW
    {
        $response = new ResponseMW();
        try{

            $arrayDeParametros = $request->getParsedBody();
            if (!isset($arrayDeParametros) || empty($arrayDeParametros)){
                throw new \Exception("Error: Objeto no proporcionado");
            }
            $obj_json = json_decode($arrayDeParametros["obj_json"]);

            //GENERO UNA NUEVA RESPUESTA
            $error = [];

            if (Usuario::verificarCorreoClaveBD($obj_json->correo, $obj_json->clave) == false) {
                $error["clave-correo"] = "el usuario no existe con esas credenciales";
            }

            if (!empty($error)) {
                    return self::respuesta($response,false, $error, 403);
            }

            // Invocar al siguiente Middleware
            $response = $handler->handle($request);

            return $response;
       
        } catch (\Exception $error) {
            $mensaje = [];
            $mensaje["error"] = $error->getMessage();
            $mensaje["mensaje"] = "se produjo un error, verifica si se estan pasando los datos correctamente";

            return self::respuesta($response, false, $mensaje, 403);
        }
    } 

    /**
     * VERIFICAR EXISTENCIA CORREO
     * MIDDLEWARE 4
     */
    public static function verificarExistenciaCorreo(Request $request, RequestHandler $handler): ResponseMW
    {
        $response = new ResponseMW();

        try{
            $arrayDeParametros = $request->getParsedBody();
            if (!isset($arrayDeParametros) || empty($arrayDeParametros)){
                throw new \Exception("Error: Objeto no proporcionado");
            }
            $obj_json = json_decode($arrayDeParametros["obj_json"]);

            //GENERO UNA NUEVA RESPUESTA
            $error = [];

            if (Usuario::verificarCorreo($obj_json->correo) == true) {
                $error["correo"] = "el correo ya esta en uso";
            }

            if (!empty($error)) {
                    return self::respuesta($response, false, $error, 403);
            }

            // Invocar al siguiente Middleware
            $response = $handler->handle($request);

            return $response;

        } catch (\Exception $error) {
                $mensaje = [];
                $mensaje["error"] = $error->getMessage();
                $mensaje["mensaje"] = "se produjo un error, verifica si se estan pasando los datos correctamente";

                return self::respuesta($response, false, $mensaje, 403);
        }
    } 

    /**
     * Verificar el precio no pase de 50mil a 600mil
     * y que no sea color azul
     * middleware 5
     *
     */

    public static function verificarRangoPrecio(Request $request, RequestHandler $handler): ResponseMW
    {
        $response = new ResponseMW();

        try{
            $arrayDeParametros = $request->getParsedBody();
            if (!isset($arrayDeParametros) || empty($arrayDeParametros)){
                throw new \Exception("Error: Objeto no proporcionado");
            }
            $obj_json = json_decode($arrayDeParametros["obj_json"]);
        
            $errors = [];
        
            if ($obj_json->precio < 50000 || $obj_json->precio > 600000) {
                $errors["precio"] = "El precio está fuera del rango: 60.000 - 600.000";
            }
        
            if ($obj_json->color == "azul") {
                $errors["color"] = "Por alguna razón, el color azul está prohibido";
            }
        
            if (!empty($errors)) {
                return self::respuesta($response, false, $errors, 409);
            }
        
            $response = $handler->handle($request);
            return $response;
        } catch (\Exception $error) {
            $mensaje = [];
            $mensaje["error"] = $error->getMessage();
            $mensaje["mensaje"] = "se produjo un error, verifica si se estan pasando los datos correctamente";

            return self::respuesta($response, false, $mensaje, 403);
        }
    }

    /******************************************************************** */
    /************************MIDDLEWARE PUT Y DELETE**************************************** */
    /******************************************************************** */

    /**
     * VERIFICA EL JWT
     * MIDDLEWARE 1
     */
    public function verificarJWTPorHeader(Request $request, RequestHandler $handler): ResponseMW {
        
        $response = new ResponseMW();
        try{
            
            if (!isset($request->getHeader("token")[0])){
                throw new \Exception("Error: Token no proporcionado");
            }

            $token = $request->getHeader("token")[0];
            $obj_rta = Autentificadora::verificarJWT($token);
            $mensaje = [];


            $status = $obj_rta->verificado ? 200 : 403;

            if ($status == 403){
                $mensaje["token"] = "el token no es valido";
                return self::respuesta($response, false, $mensaje, $status);
            }

            $response = $handler->handle($request);
            return $response;
        } catch (\Exception $error) {
            $mensaje = [];
            $mensaje["error"] = $error->getMessage();
            $mensaje["mensaje"] = "se produjo un error, verifica si se estan pasando los datos correctamente";

            return self::respuesta($response, false, $mensaje, 403);
        }
    }

    /**
     * VERIFICAR SI ES PROPIETARIO
     * MIDDLEWARE 2
     */
    public static function verificarPropietario(Request $request, RequestHandler $handler): ResponseMW {
        $token = $request->getHeader("token")[0];
        $obj_rta = Autentificadora::obtenerPayLoad($token);
        $mensaje = [];

        $response = new ResponseMW();

        if ($obj_rta->payload->usuario->perfil != "propietario"){
            $mensaje["privilegios"] = "no tienes privilegios de propietario, para realizar esa accion";
            $mensaje["usuario"] = $obj_rta->payload->usuario;
            $mensaje["estado"] = 409;

            return self::respuesta($response, false, $mensaje, 409);
        }

        $mensaje["privilegios"] = "aceptados";
        $response = $handler->handle($request);
        return self::respuesta($response, true, $mensaje, 200);
    }

    /**
     * 
     * VERIFICAR SI ES ENCARGADO
     * MIDDLEWARE 3
     * 3.- (método de instancia) verifique si es un ‘encargado’ o no.
Recibe el JWT → token (en el header) a ser verificado.
Retorna un JSON con encargado: true/false; mensaje: string (mensaje correspondiente);
status: 200/409.


     */
    public function verificarEncargado(Request $request, RequestHandler $handler): ResponseMW {
        $token = $request->getHeader("token")[0];
        $obj_rta = Autentificadora::obtenerPayLoad($token);
        $mensaje = [];

        $response = new ResponseMW();

        if ($obj_rta->payload->usuario->perfil != "encargado"){
            $mensaje["privilegios"] = "no tienes privilegios de encargado, para realizar esa accion";
            $mensaje["usuario"] = $obj_rta->payload->usuario;
            $mensaje["estado"] = 409;

            return self::respuesta($response, false, $mensaje, 409);
        }

        $mensaje["privilegios"] = "aceptados";
        $response = $handler->handle($request);
        return self::respuesta($response, true, $mensaje, 200);
    }
  
    /**
     * ULTIMOS MIDDLEWARE PARTE A!
     *  */

     /**
      * VERIFICAR SI ES ENCARGADO
      * MIDDLEWARE 1
      * 1.- Si el que accede al listado de autos es un ‘encargado’, retorne todos los datos, menos el ID.
        (clase MW - método de instancia)
      */

      public function listaSiEsEncargado(Request $request, RequestHandler $handler): ResponseMW {
        $token = $request->getHeader("token")[0];
        $obj_rta = Autentificadora::obtenerPayLoad($token);
         
        if ($obj_rta->payload->usuario->perfil != "encargado"){
            return $handler->handle($request); // paso al siguiente colleable
        }    

        //INVOCO AL VERBO
        $response = $handler->handle($request);
        $response = new ResponseMW();
 
        $autosObj = json_decode(Auto::traerTodos()); 
        $lista = $autosObj->tabla;

        foreach ($lista as $auto) {
            unset($auto->id); // elimino el id ;
        }
       
		$newResponse = $response->withStatus(200, "OK");
		$newResponse->getBody()->write(json_encode($lista));

		return $newResponse->withHeader('Content-Type', 'application/json');
    }

    /**
     * MIDDLEWARE 2
     */
    public function listaSiEsEmpleado(Request $request, RequestHandler $handler): ResponseMW {
        $token = $request->getHeader("token")[0];
        $obj_rta = Autentificadora::obtenerPayLoad($token);
         
        if ($obj_rta->payload->usuario->perfil != "empleado"){
            return $handler->handle($request);
        }    

       // $objeto = json_decode($response->getBody());
      //  $lista = $objeto->tabla;
        $autosObj = json_decode(Auto::traerTodos()); 
        $lista = $autosObj->tabla;
        
        //INVOCO AL VERBO
        $response = new ResponseMW();
    
        // array_unique = elimino colores duplicados
        // array column = obtengo los valores de el atributo color
        $coloresDistintos = array_unique(array_column($lista, 'color'));
        $cantidadColores = count($coloresDistintos);
    
        // array asociativo
        $mensaje = [
            'cantidad_colores' => $cantidadColores,
            'lista_autos' => $lista
        ];
        
        return self::respuesta($response, true , $mensaje, 200);
    }
       /**
     * MIDDLEWARE 3
     * 
     *  Si es un ‘propietario’, muestre todos los datos de los autos (si el ID está vacío o indefinido) o
el auto (cuyo ID fue pasado como parámetro). (clase MW - método de clase).

     */
    public static function listaSiEsPropietario(Request $request, RequestHandler $handler): ResponseMW {
        $token = $request->getHeader("token")[0];
        $obj_rta = Autentificadora::obtenerPayLoad($token);
         
        if ($obj_rta->payload->usuario->perfil != "propietario"){
            return $handler->handle($request);
        }    

      //  $objeto = json_decode($response->getBody());
       // $autosObj = json_decode( $objeto); // porque necesite decodificar 2 veces O.O para acceder al objeto
      //  $lista = $objeto->tabla;
    
        $autosObj = json_decode(Auto::traerTodos()); 
        $lista = $autosObj->tabla;
        
        //INVOCO AL VERBO
        $response = $handler->handle($request);
        $response = new ResponseMW();
        
        $mensaje = [];
        $mensaje["listado_completo"] = $lista;
        if (isset($request->getQueryParams()["id"])){

            $idAuto = intval($request->getQueryParams()["id"]);
            $auto = self::buscarAutoPorId($lista, $idAuto);
            $mensaje["id_auto"] = empty($auto) ? "no se encontro ningun auto con el id especificado" : $auto;
        }

        return self::respuesta($response, true , $mensaje, 200);
    }

    /**
     * PARTE B
     * 
     * MIDDLEWARE 1
     * 
     * 1.- Si el que accede al listado de USUARIO es un ‘encargado’, retorne todos los datos, menos la clave
y el ID. (clase MW - método de instancia).

     */

     public function listaSiEsEncargadoUsuario(Request $request, RequestHandler $handler): ResponseMW {
        $token = $request->getHeader("token")[0];
        $obj_rta = Autentificadora::obtenerPayLoad($token);
         
        if ($obj_rta->payload->usuario->perfil != "encargado"){
            return $handler->handle($request); // paso al siguiente colleable
        }    

        //INVOCO AL VERBO
        $response = $handler->handle($request);
        $response = new ResponseMW();
 
        $listaUsuario = (Usuario::traerTodos()); 

        foreach ($listaUsuario as $usuario) {
            unset($usuario->id); // elimino el id ;
            unset($usuario->clave); // elimino el clave ;
        }
       
		$newResponse = $response->withStatus(200, "OK");
		$newResponse->getBody()->write(json_encode($listaUsuario));

		return $newResponse->withHeader('Content-Type', 'application/json');
    }

    /**
     * MIDDLEWARE 2
     * 
     * 2.- Si es un ‘empleado’, muestre solo el nombre, apellido y foto de los usuarios. (clase MW -
método de instancia).

     */

     public function listaSiEsEmpleadoUsuario(Request $request, RequestHandler $handler): ResponseMW {
        $token = $request->getHeader("token")[0];
        $obj_rta = Autentificadora::obtenerPayLoad($token);
         
        if ($obj_rta->payload->usuario->perfil != "empleado"){
            return $handler->handle($request);
        }    

         //INVOCO AL VERBO
         $response = $handler->handle($request);
         $response = new ResponseMW();

        $listaUsuario = (Usuario::traerTodos()); 


        foreach ($listaUsuario as $usuario) {
            unset($usuario->id); // elimino el id ;
            unset($usuario->clave); // elimino el clave ;
            unset($usuario->correo); // elimino el clave ;
            unset($usuario->perfil); // elimino el clave ;
        }

        $newResponse = $response->withStatus(200, "OK");
		$newResponse->getBody()->write(json_encode($listaUsuario));

		return $newResponse->withHeader('Content-Type', 'application/json');
    }

    /**
     * 3.- Si es un ‘propietario’, muestre la cantidad de usuarios cuyo apellido coincida con el pasado
por parámetro o los apellidos (y sus cantidades) si es que el parámetro pasado está vacío o
indefinido. (clase MW - método de clase)

     */
    public static function listaSiEsPropietarioUsuario(Request $request, RequestHandler $handler): ResponseMW {
        $token = $request->getHeader("token")[0];
        $obj_rta = Autentificadora::obtenerPayLoad($token);
         
        if ($obj_rta->payload->usuario->perfil != "propietario"){
            return $handler->handle($request);
        }   
    
        $lista = (Usuario::traerTodos()); 
        
        //INVOCO AL VERBO
        $response = $handler->handle($request);
        $response = new ResponseMW();
        
        $mensaje = [];

         // array_unique = elimino colores duplicados
        // array column = obtengo los valores de el atributo color
        $apellidoDistintos = array_unique(array_column($lista, 'apellido'));
        $cantidadApellidos = count($apellidoDistintos);
        // array asociativo
        $mensaje["apellidos_sin_repetir"] = $cantidadApellidos;

        if (isset($request->getQueryParams()["apellido"])){

            $apellido = $request->getQueryParams()["apellido"];
            $usuarios = self::buscarAutoPorApellido($lista, $apellido);

            $mensaje["cantidades"] = count($usuarios);
            $mensaje["lista"] = empty($usuarios) ? "no se encontro ningun usuario con ese apellido" : $usuarios;
        }

        return self::respuesta($response, true , $mensaje, 200);
    }

    private static function respuesta(ResponseMW $response, bool $exito, array $mensaje, int $estado): ResponseMW {
        $errorMensaje = [
            "exito" => $exito,
            (($exito == true) ? "mensaje" : "errores:") => $mensaje
        ];
    
        $newResponse = $response->withStatus($estado);
        $newResponse->getBody()->write(json_encode($errorMensaje));
        return $newResponse->withHeader("Content-Type", "application/json");
    }
    
    private static function buscarAutoPorId($autos, $idAuto) {
        foreach ($autos as $auto) {
            if ($auto->id == $idAuto) {
                return $auto;
            }
        }
        return null;
    }
    private static function buscarAutoPorApellido($lista, $apellido) {
        $encontrados = array();
        foreach ($lista as $usuario) {
            if ($usuario->apellido == $apellido) {
                array_push($encontrados, $usuario);
            }
        }
        return $encontrados;
    }
}

?>