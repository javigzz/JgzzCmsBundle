<?php
namespace Jgzz\CmsBundle\Attach;

use Jgzz\CmsBundle\Interfaces\EntityAttachPropsManagerInterface;

/**
 * Util para gestionar las propiedades de entidades relacionadas con archivos.
 * Las entidades que requieran hacer uso de esto deben implementar
 * EntityAttachPropsManagerInterface
 * 
 * Desarrollado sobre:
 * ver: http://blog.code4hire.com/2011/08/symfony2-sonata-admin-bundle-and-file-uploads/
 * o: http://symfony.com/doc/2.0/cookbook/doctrine/file_uploads.html
 */
class PropertyManager {

	/**
	 * Entidad poseedora de las propiedades-archivo
	 */
	protected $entity;
	
	protected $attach_property_service;
	
	/**
	 * Server path to public web folder.
	 */
	private $web_dir_path;
	
	/*
	public function __construct(EntityAttachPropsManagerInterface $entity) {
		$this -> entity = $entity;
	}
	
	public function setService(AttachPropertyService $service){
		$this->attach_property_service = $service;
	}
	
	 */
	
	public function setWebDirPath($path){
		$this->web_dir_path = $path;
	}
	
	public function getWebDirPath(){
		return $this->web_dir_path;
	}
	
	
	public function setEntity(EntityAttachPropsManagerInterface $entity){
		$this->entity = $entity;
		return $this;
	}

	/**
	 * Actualiza en servidor todos los archivos enlazados desde propiedades
	 * de la entidad que está siendo gestionada
	 */
	public function saveAllAttachedProps() {
		
		$att_reg = $this -> entity -> getRegAttaches();
		
		foreach ($att_reg as $propiedad => $att_config) {
			
			$this -> upload($propiedad);
			
		}
	}


	/**
	 * Carga el archivo asociado a la propiedad
	 */
	public function upload($propiedad) {
		
		if ($prop_reg = $this->getPropiedadReg($propiedad)){
				
			// nombe de la propiedad de la entidad que almacena 
			// el archivo cargado (diferente a la propiedad 
			// que almacena la ruta del archivo)
			$filetype_prop_name = $prop_reg['file_field'];
			
			// FileType
			$file = $this -> entity -> $filetype_prop_name;
			
		} else {
			// no se ha registrado, puede que haya que registrar 
			// la propiedad en el constructor de la entidad
			throw new \Exception("No se ha registrado la propiedad $propiedad en arrar de adjuntos de la entidad", 1);
		}
		
		
		// método setter para la propiedad (para almacenar su nombre de archivo)
		// TODO: proponer por defecto setPropiedad
		$prop_setter_method = $prop_reg['setter'];

		// the file property can be empty if the field is not required
		if (null === $file) {
			return;
		}

		// move takes the target directory and then the target filename to move to
		$file -> move($this -> getUploadRootDir(), $file -> getClientOriginalName());
		
		// set the path property to the filename where you'ved saved the file
		$this -> entity -> $prop_setter_method($file -> getClientOriginalName());

		// clean up the file property as you won't need it anymore
		$file = null;
	}

	/**
	 * Ruta absoluta en sistema de archivos, al archivo relacionado a una propiedad de la entidad
	 * gestionada
	 */
	 public function getAbsolutePath($prop)
	  {
	  	// valor de la propiedad (ruta relativa)
	  	$ruta = $this->getPropiedad($prop);
		
	    return null === $ruta ? null : $this->getUploadRootDir().$ruta;
	  }
	
	/**
	 * web path to a property file
	 */	
	  public function getWebPath($prop)
	  {
	  	// valor de la propiedad (ruta relativa)
	  	$web_path = $this->getPropiedadWebPath($prop);
		
	    return null === $web_path ? null : sprintf("%s/%s", $this -> entity->getUploadDir(), $web_path);
	  }
	  
	  /**
	   * Valor de la propiedad (nombre archivo) de la entidad gestionada
	   */
	  protected function getPropiedadWebPath($prop){
	  	
	  	$prop_reg = $this->getPropiedadReg($prop);
	  	
	  	if (!$prop_reg){
	  		throw new \Exception("No se ha registrado la propiedad '$prop' en la entidad gestionada", 1);
	  	}
	  	
	  	$getter = $prop_reg['getter'];
	  	
		// gets file name
		return $this->entity->$getter();
	  }
	  
	  /**
	 * Obtiene el array de registro de una propiedad de la entidad. 
	 * Registro como propiedad con archivo adjunto
	 */
	protected function getPropiedadReg($propiedad){
		$att_reg = $this -> entity -> getRegAttaches();
		
		if (array_key_exists($propiedad, $att_reg)) {
			
			return $att_reg[$propiedad];

		} else {

			return null;
		}
		
	}
	  
	/**
	 * Directorio absoluto donde se debe guardar el fichero asociado
	 */
	protected function getUploadRootDir() {
		
		if(empty($this->web_dir_path)){
			throw new \Exception("web_dir_path cannot be empty for PropertyManager service. Make sure you set it with setWebDirPath before trying to safe files", 1);
		}
		
		return sprintf("%s/%s", $this->web_dir_path, $this->entity->getUploadDir());
	}

}
