<?php 

require_once "AccesoDatos.php";
use Poo\AccesoDatos;

class Auto {
    protected int $id;
    protected string $color;
    protected string $marca;
    protected int $precio;
    protected string $modelo;

    function __construct($color="", $marca="", $precio="", $modelo="")
    {
        $this->color = $color;
        $this->marca = $marca;
        $this->precio = intval($precio);
        $this->modelo= $modelo;
    }

    public function getId() : int {
        return $this->id;
    }
    public function getColor() : string {
        return $this->color;
    }
    public function getMarca() : string {
        return $this->marca;
    }
    public function getPrecio() : int {
        return $this->precio;
    }
    public function getMdelo() : int {
        return $this->modelo;
    }
    public function setId($id) {
        $this->id = $id;
    }
    public function setColor($color) {
        $this->color = $color;
    }
    public function setMarca($marca) {
        $this->marca = $marca;
    }
    public function setModelo($modelo) {
        $this->modelo = $modelo;
    }
    public function setPrecio($precio) {
        $this->precio = intval($precio);
    }

    public function Agregar()
	{
		$objetoAccesoDato = AccesoDatos::accesoPDO(); 
		$consulta = $objetoAccesoDato->consultaPDO("INSERT into autos (color, marca, precio, modelo)values(:color,:marca,:precio,:modelo)");
		$consulta->bindValue(':color',$this->color, PDO::PARAM_STR);
		$consulta->bindValue(':marca', $this->marca, PDO::PARAM_STR);
		$consulta->bindValue(':precio', $this->precio, PDO::PARAM_INT);
		$consulta->bindValue(':modelo', $this->modelo, PDO::PARAM_STR);
		$consulta->execute();		

        $consulta->execute();
        $exito = $consulta->rowCount();

        $mensaje = new stdClass();
        $mensaje->notificar= ($exito >= 1 ? "" : "No ") . "Se agrego el auto";
        $mensaje->exito = $exito  >= 1 ? true : false;
        $mensaje->estado = ($exito >= 1 ) ? 200 : 418;
        return json_encode($mensaje);
	}
    public static function traerTodos()
    {
        $objetoAccesoDato = AccesoDatos::accesoPDO(); 
		$consulta = $objetoAccesoDato->consultaPDO("select * from autos;");
		$consulta->execute();			
		$lista = $consulta->fetchAll(PDO::FETCH_CLASS, "stdClass");		

        $exito = $consulta->rowCount();

        $mensaje = new stdClass();
        $mensaje->notificar= ($exito >= 1 ? "" : "No ") . "Lista de autos";
        $mensaje->exito = $exito  >= 1 ? true : false;
        $mensaje->estado = ($exito >= 1 ) ? 200 : 418;
        $mensaje->tabla = ($lista);

        return json_encode($mensaje);
    }


    public static function borrarUno($id) : bool {
        $objetoAccesoDato = AccesoDatos::accesoPDO();

        $consulta = $objetoAccesoDato -> consultaPDO("delete from autos where id = :id");
        $consulta->bindValue(":id", $id, PDO::PARAM_INT );
        $consulta->execute();
        $borrado= $consulta->rowCount();
        return ($borrado)> 0; 
    }

    public function modificar()
	{	$objetoAccesoDato = AccesoDatos::accesoPDO(); 
		$consulta = $objetoAccesoDato->consultaPDO("
				update autos 
				set
                color=:color,
				marca=:marca,
				precio=:precio,
				modelo=:modelo
				WHERE id=:id");
	
		$consulta->bindValue(':id',$this->id, PDO::PARAM_INT);
		$consulta->bindValue(':color',$this->color, PDO::PARAM_STR);
		$consulta->bindValue(':marca', $this->marca, PDO::PARAM_STR);
		$consulta->bindValue(':precio', $this->precio, PDO::PARAM_INT);
		$consulta->bindValue(':modelo', $this->modelo, PDO::PARAM_STR);
		$consulta->execute();		
        return ($consulta->rowCount() > 0) ;
	}
}

?>