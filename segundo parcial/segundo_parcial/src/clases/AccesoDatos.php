<?php 
namespace Poo;
use PDO;
use PDOException;

class AccesoDatos {
    private static AccesoDatos $objetoDatos;

    private PDO $objetoPDO;

    private function __construct()
    {
        try {
            $usuario = "root";
            $clave = "";

            $this->objetoPDO = new PDO("mysql:host=localhost;dbname=concesionaria_bd;charset=utf8", $usuario, $clave);
        }
        catch (PDOException $e) {
            print("Error!!!! ->". $e->getMessage());
            die();
        }
    }

    public function consultaPDO(string $sql) {
        return $this->objetoPDO->prepare($sql);
    }
    public function ultimoID()
    { 
        return $this->objetoPDO->lastInsertId(); 
    }

    public static function accesoPDO() : AccesoDatos {
        if (!isset(self::$objetoDatos)){
            self::$objetoDatos = new AccesoDatos();
        }

        return self::$objetoDatos;
    }
    public function __clone(){
        trigger_error('La clonaci&oacute;n de este objeto no est&aacute; permitida!!!', E_USER_ERROR);
    }
}


?>

