<?php 


require_once __DIR__ . "/./Usuario.php";
require_once __DIR__ . "/./autentificadora.php";

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SlimLogin {

    public function loginUsuario(Request $request, Response $response, array $args): Response 
    {
        try{
            $mensajeJson = new stdClass();
            $mensajeJson->exito = true;
            $mensajeJson->status = 200;


            $arrayDeParametros = $request->getParsedBody();
            
            $jsonAuto = $arrayDeParametros['obj_json'];
            $autoJson = json_decode($jsonAuto);
            
            $correo = $autoJson->correo;
            $clave = $autoJson->clave;

            $usuarioSTD = new stdClass;
            $usuarioSTD->correo = $correo;
            $usuarioSTD->clave = $clave;

            $usuarioEncontrado = Usuario::traerUno($usuarioSTD);

            if ($usuarioEncontrado == null || empty($usuarioEncontrado)){

                $mensajeJson->mensaje = "Error en datos";
                $mensajeJson->exito = false;
                $mensajeJson->status = 403;

                $newResponse = $response->withStatus( $mensajeJson->status);
                $newResponse->getBody()->write(json_encode($mensajeJson));
                return $newResponse->withHeader('Content-Type', 'application/json');
            }

            // REGISTRO EL NUEVO TOKEN
            $token = Autentificadora::crearJWT($arrayDeParametros, 60, $usuarioEncontrado);

            $mensajeJson->mensaje = "Datos correctos";
            $mensajeJson->exito = true;
            $mensajeJson->status = 200;
            $mensajeJson->jwt = $token;

            $newResponse = $response->withStatus( $mensajeJson->status);
            $newResponse->getBody()->write(json_encode($mensajeJson));
            return $newResponse->withHeader('Content-Type', 'application/json');
        } catch (\Exception $error) {
            $mensaje = [];
            $mensaje["error"] = $error->getMessage();
            $mensaje["mensaje"] = "se produjo un error, verifica si se estan pasando los datos correctamente";

            return self::respuesta($response, false, $mensaje, 403);
        }

    }

    /*
    (GET) Se envía el JWT → token (en el header) y se verifica. En caso exitoso, retorna un JSON
    con mensaje y status 200. Caso contrario, retorna un JSON con mensaje y status 403.
*/
    public function verificarToken(Request $request, Response $response, array $args) : Response {
        try {
            if (!isset($request->getHeader("token")[0])){
                throw new \Exception("Error: Token no proporcionado");
            }
            $token = $request->getHeader("token")[0];
            $obj_rta = Autentificadora::verificarJWT($token);
            $status = $obj_rta->verificado ? 200 : 403;

            $newResponse = $response->withStatus($status);
            $newResponse->getBody()->write(json_encode($obj_rta));
            return $newResponse->withHeader('Content-Type', 'application/json');
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