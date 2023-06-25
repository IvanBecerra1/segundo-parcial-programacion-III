

<?php 
require_once __DIR__ . "/./Usuario.php";
require_once __DIR__ . "/./IMetodos.php";

use Poo\AccesoDatos;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SlimUsuario implements IMetodos {

    public function agregarUno(Request $request, Response $response, array $args): Response 
    {
        try{
            $arrayDeParametros = $request->getParsedBody();
            
            $jsonUsuario = $arrayDeParametros['obj_json'];
            
            $usuarioJson = json_decode($jsonUsuario);
            
            $correo = $usuarioJson->correo;
            $clave = $usuarioJson->clave;
            $nombre = $usuarioJson->nombre;
            $apellido = $usuarioJson->apellido;
            $perfil = $usuarioJson->perfil;

            $usuario = new Usuario();
            $usuario->setCorreo ($correo);
            $usuario->setClave($clave);
            $usuario->setNombre($nombre);
            $usuario->setApellido($apellido);
            $usuario->setPerfil($perfil);


            //*********************************************************************************************//
        //SUBIDA DE FOTO    
        //*********************************************************************************************//

            $archivos = $request->getUploadedFiles();
            $destino = __DIR__ . "/../fotos/";

            $nombreAnterior = $archivos['foto']->getClientFilename();
            $extension = explode(".", $nombreAnterior);

            $extension = array_reverse($extension);
            $archivos['foto']->moveTo($destino . $usuario->getNombre() .'.'. date('His') . "." . $extension[0]);

            //foto al usuario
            //obtener ultimo id;
            //spaggeti.code.com
            $usuario->setId(Usuario::devolverUltimoId()+1);
            $ubicacion = $usuario->getCorreo() .'_'. $usuario->getId() . "." . $extension[0];
            $usuario->setFoto("./fotos/$ubicacion");
            
            $stdRespuesta = json_decode( $usuario->Agregar());
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
            $todosLosCds = Usuario::traerTodos();
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