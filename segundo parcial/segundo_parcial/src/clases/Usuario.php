<?php

require_once "AccesoDatos.php";
use Poo\AccesoDatos;
use JsonSerializable;

class Usuario implements JsonSerializable {
    protected int $id;
    protected string $correo;
    protected string $clave;
    protected string $nombre;
    protected string $apellido;
    protected string $perfil;
    protected string $foto;

    function __construct($correo="", $clave="", $nombre="", $apellido="",  $perfil="", $foto="")
    {
        $this->correo = $correo;
        $this->clave = $clave;
        $this->nombre = $nombre;
        $this->nombre = $apellido;
        $this->perfil = $perfil;
        $this->foto = $foto;
    }

    
    public function getId() : int {
        return $this->id;
    }
    public function getNombre() : string {
        return $this->nombre;
    }
    public function getApellido() : string {
        return $this->apellido;
    }

    public function getClave() : string {
        return $this->clave;
    }
    public function getFoto() : string {
        return $this->foto;
    }

    public function getPerfil() : string {
        return $this->perfil;
    }
    public function getCorreo() : string {
        return $this->correo;
    }
    public function setId($id) {
        $this->id = $id;
    }
    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }
    public function setApellido($apellido) {
        $this->apellido = $apellido;
    }
    public function setPerfil($perfil) {
        $this->perfil = $perfil;
    }

    public function setClave($clave) {
        $this->clave = $clave;
    }
    public function setCorreo($correo) {
        $this->correo = $correo;
    }

    public function setFoto($foto) {
        $this->foto = $foto;
    }

    public function jsonSerialize() {
        return json_encode([
            'id' => $this->getId(),
            'nombre' => $this->getNombre(),
            'apellido' => $this->getApellido(),
            'clave' => $this->getClave(),
            'correo' => $this->getCorreo(),
            'foto' => $this->getFoto(),
            'perfil' => $this->getPerfil()
        ]);
    }

    
    public function Agregar() {
        $objetoAccesoDato = AccesoDatos::accesoPDO();
        
        // (id,nombre, correo, clave e id_perfil),
        $consulta =$objetoAccesoDato->consultaPDO
        ("INSERT INTO usuarios (correo, clave, nombre, apellido, foto, perfil)"
        . "VALUES(:correo, :clave, :nombre, :apellido, :foto, :perfil)");
        
        $consulta->bindValue(':correo', $this->correo, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $this->clave, PDO::PARAM_STR);
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':apellido', $this->apellido, PDO::PARAM_STR);
        $consulta->bindValue(':foto', $this->foto, PDO::PARAM_STR);
        $consulta->bindValue(':perfil', $this->perfil, PDO::PARAM_STR);
   
        $consulta->execute();
        $exito = $consulta->rowCount();

        $mensaje = new stdClass();
        
        $mensaje->notificar= ($exito >= 1 ? "" : "No ") . "Se agrego al usuario";
        $mensaje->exito = $exito  >= 1 ? true : false;
        $mensaje->estado = ($exito >= 1 ) ? 200 : 418;
        return json_encode($mensaje);
    }

    public static function devolverUltimoId(){
		$objetoAccesoDato = AccesoDatos::accesoPDO(); 
		$consulta = $objetoAccesoDato->consultaPDO("SELECT MAX(id) FROM usuarios;");
        
		$consulta->execute();
		$cdBuscado= $consulta->fetchColumn();
		return $cdBuscado;		
	}
    
    public static function traerTodos()
    {
        $objetoAccesoDato = AccesoDatos::accesoPDO(); 
		$consulta = $objetoAccesoDato->consultaPDO("select * from usuarios;");
		$consulta->execute();			
		return $consulta->fetchAll(PDO::FETCH_CLASS, "stdClass");		
    }


    public static function traerUno($stdClass) 
	{
		$objetoAccesoDato = AccesoDatos::accesoPDO(); 
		$consulta = $objetoAccesoDato->consultaPDO("select id,nombre,apellido,foto,perfil,correo from usuarios where correo = :correo and clave = :clave");

        
        $consulta->bindValue(':correo', $stdClass->correo, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $stdClass->clave, PDO::PARAM_STR);

		$consulta->execute();
		$cdBuscado= $consulta->fetchObject('stdClass');
		return $cdBuscado;		
	}


    public static function verificarCorreoClaveBD($correo, $clave) :bool {
        $objetoAccesoDato = AccesoDatos::accesoPDO(); 
		$consulta = $objetoAccesoDato->consultaPDO("SELECT EXISTS(SELECT 1 FROM usuarios WHERE correo = :correo AND clave = :clave)");

        
        $consulta->bindValue(':correo', $correo, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $clave, PDO::PARAM_STR);

		$consulta->execute();
		$encontrado = boolval($consulta->fetchColumn());
		return $encontrado;	
    }

    public static function verificarCorreo($correo) :bool {
        $objetoAccesoDato = AccesoDatos::accesoPDO(); 
		$consulta = $objetoAccesoDato->consultaPDO("SELECT EXISTS(SELECT 1 FROM usuarios WHERE correo = :correo)");

        
        $consulta->bindValue(':correo', $correo, PDO::PARAM_STR);

		$consulta->execute();
		$encontrado = boolval($consulta->fetchColumn());
		return $encontrado;	
    }
}


?>