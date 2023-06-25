<?php 
require_once __DIR__ . "/./Auto.php";
require_once __DIR__ . "/./IMetodos.php";


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SlimAuto implements IMetodos {

    public function agregarUno(Request $request, Response $response, array $args): Response 
    {
        try {
            $arrayDeParametros = $request->getParsedBody();
        
            $jsonAuto = $arrayDeParametros['obj_json'];
            $autoJson = json_decode($jsonAuto);
            
            $color = $autoJson->color;
            $marca = $autoJson->marca;
            $precio = $autoJson->precio;
            $modelo = $autoJson->modelo;
    
            $auto = new Auto();
            $auto->setMarca ($marca);
            $auto->setPrecio($precio);
            $auto->setColor($color);
            $auto->setModelo($modelo);
    
            $stdRespuesta = json_decode( $auto->Agregar());
    
            $newResponse = $response->withStatus($stdRespuesta->estado, "OK");
    
            $newResponse->getBody()->write(json_encode($stdRespuesta));
            return $newResponse->withHeader('Content-Type', 'application/json');
        } catch (\Exception $error) {

            $mensaje = [];
            $mensaje["error"] = $error->getMessage();
            $mensaje["mensaje"] = "se produjo un error, verifica si se estan pasando los datos correctamente";

            return self::respuesta($response, false, $mensaje, 403);
        }
    }
    public function traerTodos(Request $request, Response $response, array $args): Response 
	{
        try {
            $todosLosCds = Auto::traerTodos();
  
            $newResponse = $response->withStatus(200, "OK");
            $newResponse->getBody()->write(json_encode($todosLosCds));
    
            return $newResponse->withHeader('Content-Type', 'application/json');	
        } catch (\Exception $error) {

            $mensaje = [];
            $mensaje["error"] = $error->getMessage();
            $mensaje["mensaje"] = "se produjo un error, verifica si se estan pasando los datos correctamente";

            return self::respuesta($response, false, $mensaje, 403);
        }
    }
    /**
     * TACHO PORUQE CON EL MIDDLEWARE YA SE ESTA VERIFICANDO
     */
    public function borrarAuto(Request $request, Response $response, array $args): Response {

        try{
            //Obtener el ID del auto a ser borrado
            $idAuto = $args['id_auto'];

            $token = $request->getHeader("token")[0];
        // $obj_rta = Autentificadora::obtenerPayLoad($token); // obtengo los datos
            $mensaje = [];

        /* if ($obj_rta->payload->usuario->perfil != "propietario"){
                $mensaje["error"] = "se ha intentado borrar un auto no siendo propietario, datos: {$obj_rta->usuario}";
                return self::respuesta($response, false, $mensaje, 418);
            }*/

            $respuesta = Auto::borrarUno($idAuto);
            $mensaje["Auto"] = "Se borro el auto con el id {$idAuto}";
            return self::respuesta($response, $respuesta, $mensaje, 200);
        } catch (\Exception $error) {

            $mensaje = [];
            $mensaje["error"] = $error->getMessage();
            $mensaje["mensaje"] = "se produjo un error, verifica si se estan pasando los datos correctamente";

            return self::respuesta($response, false, $mensaje, 403);
        }
    }

    public function modificarAuto(Request $request, Response $response, array $args): Response {
        try {
            $idAuto = $args['id_auto'];

            $token = $request->getHeader("token")[0];
            $obj_rta = Autentificadora::obtenerPayLoad($token); // obtengo los datos
            $mensaje = [];

            /*if ($obj_rta->payload->usuario->perfil != "encargado"){
                $mensaje["error"] = "se ha intentado modificar un auto no siendo encargado, datos: {$obj_rta->usuario}";
                return self::respuesta($response, false, $mensaje, 418);
            }*/

            $jsonAuto = json_decode($request->getBody());

            $color = $jsonAuto->color;
            $marca = $jsonAuto->marca;
            $precio = $jsonAuto->precio;
            $modelo = $jsonAuto->modelo;

            $auto = new Auto();
            $auto->setId ($idAuto);
            $auto->setMarca ($marca);
            $auto->setPrecio($precio);
            $auto->setColor($color);
            $auto->setModelo($modelo);

            $stdRespuesta =  $auto->modificar();
            
            if ( $stdRespuesta == false){
                $mensaje["Auto"] = "no se modifico, verifique colocar el id bien o colocar nuevos datos: {$idAuto}";
            }
            else {
                $mensaje["Auto"] = "Se modifico el auto con el id: {$idAuto}";
            }
            return self::respuesta($response, $stdRespuesta, $mensaje, 200);
        } catch (\Exception $error) {
            $mensaje = [];
            $mensaje["error"] = $error->getMessage();
            $mensaje["mensaje"] = "se produjo un error, verifica si se estan pasando los datos correctamente";

            return self::respuesta($response, false, $mensaje, 403);
        }
    }


    private static function respuesta(Response $response, bool $exito, array $mensaje, int $estado): Response {
        $errorMensaje = [
            "exito" => $exito,
            "mensaje" => $mensaje
        ];
        $newResponse = $response->withStatus($estado);
        $newResponse->getBody()->write(json_encode($errorMensaje));
        return $newResponse->withHeader("Content-Type", "application/json");
    }
  
}

?>