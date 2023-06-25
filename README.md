# segundo-parcial-programacion-III
Segundo parcial programacion



1) crear el archivo public7index.php
2) colocar el comando

composer require slim/psr7

composer require slim/slim:"4.*"

3) crear carpeta : src/app y achivo app.php 
4) colocar el codigo

<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../../vendor/autoload.php';

$app = AppFactory::create();

$app->get('/', function (Request $request, Response $response, $args) {

    $response->getBody()->write("Hola index php");
    return $response;
});

$app->run();

?>

_-------------------------------------------------------------------

CREAR SERVIDOR

1) ir a HTTPD.CONF (DESDE EL PANEL DE XAMPP (CONFIG) APACHE )
   cambiar AllowOverride none a =>>> AllowOverride all

   quedando:

   <Directory />
	AllowOverride all
	Require all denied
   </Directory>

2. MODIFICAR HTTPD-VHOSTS.CONF, UBICADO EN : xampp\apache\conf\extra y configurar el virtual host:

<VirtualHost *:80>
    ServerAdmin becerraivan79@gmail.com
    DocumentRoot "C:\xampp\htdocs\api_slim_segundo_parcial\backend\public"
    ServerName api_slim_segundo_parcial
    ErrorLog "logs/api_slim_segundo_parcial-error.log"
    CustomLog "logs/api_slim_segundo_parcial-access.log" common
</VirtualHost>

3. IR A ARCHIVO DE "hosts" UBICADO
	C:\Windows\System32\drivers\etc

y agregar:

# localhost name resolution is handled within DNS itself.
#	127.0.0.1       localhost
#	::1             localhost
	127.0.0.1	api_slim_segundo_parcial /// esto...
