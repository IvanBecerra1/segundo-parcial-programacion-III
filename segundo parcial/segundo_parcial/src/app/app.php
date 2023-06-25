<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory ;

require __DIR__ . '/../../vendor/autoload.php';


$app = AppFactory::create();

$app->get('/saludar', function (Request $request, Response $response, $args){
	$response->getBody()->write("Test hello asdasds");

	return $response;
});

 require_once __DIR__ . "/../clases/SlimUsuario.php";
 require_once __DIR__ . "/../clases/SlimAuto.php";
 require_once __DIR__ . "/../clases/SlimLogin.php";
 require_once __DIR__ . "/../clases/MW.php";
 use \Slim\Routing\RouteCollectorProxy;
 
/***
 * NIVEL DE APLICACION
 ***/

// LISTADO USUARIO
$app->get('[/]', \SlimUsuario::class . ':traerTodos') // listado
->add(\MW::class . ":listaSiEsEncargadoUsuario") // midleware 2
->add(\MW::class . ":listaSiEsEmpleadoUsuario") // midleware 3
->add(\MW::class . "::listaSiEsPropietarioUsuario") // midleware 4
->add(\MW::class . ":verificarJWTPorHeader"); // middleware 1;
	
// ALTA DE AUTO
$app->post('[/]', \SlimAuto::class . ':agregarUno')->add(\MW::class . ":verificarRangoPrecio"); // midleware 5

// ELIMIANR AUTO
$app->delete('/{id_auto}', \SlimAuto::class . ':borrarAuto')
->add(\MW::class . "::verificarPropietario") // middleware 2
->add(\MW::class . ":verificarJWTPorHeader"); // middleware 1;

// MODIFICAR AUTO
$app->put('/{id_auto}', \SlimAuto::class . ':modificarAuto')
->add(\MW::class . ":verificarEncargado") // middleware 3
->add(\MW::class . ":verificarJWTPorHeader"); // middleware 1;

/***
 * NIVEL RUTEO
 ***/
// ALTA - USUARIO
$app->post('/usuario', \SlimUsuario::class . ':agregarUno')
->add(\MW::class . ":verificarExistenciaCorreo") // middleware 4
->add(\MW::class . "::verificarCampos")//middleware 2
->add(\MW::class . ":verificarCorreoClave"); // midleware 1

// LISTA AUTOS
$app->get('/autos', \SlimAuto::class . ':traerTodos')
->add(\MW::class . ":listaSiEsEncargado") // midleware 2
->add(\MW::class . ":listaSiEsEmpleado") // midleware 3
->add(\MW::class . "::listaSiEsPropietario") // midleware 4
->add(\MW::class . ":verificarJWTPorHeader"); // middleware 1;

// LOGIN
$app->post('/login', \SlimLogin::class . ':loginUsuario')
->add(\MW::class . ":verificarExistenciaCorreoClave") // 3
->add(\MW::class . "::verificarCampos")//middleware 2
->add(\MW::class . ":verificarCorreoClave"); // midleware 1
															

$app->get('/login', \SlimLogin::class . ':verificarToken'); // verificar token

$app->run();
?>