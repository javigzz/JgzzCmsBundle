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
class AttachPropertyManager {

	/**
	 * Entidad poseedora de las propiedades-archivo
	 */
	protected $entity;
	
	/**
	 * Ruta del directorio público web
	 */
	private $base_web;

	public function __construct(EntityAttachPropsManagerInterface $entity) {
		$this -> entity = $entity;
		
		 //$this->base_web = __DIR__.'/../../../../../../web/';
	}
	
	public function setContainer($container){
		$this->base_web = $contaner->get('kernel')->getRootDir()."/../web/";
		// TODO: podría no ser 'web'
	}

	/**
	 * Actualiza en servidor todos los archivos enlazados desde propiedades
	 * de la entidad que está siendo gestionada
	 */
	public function saveAllAttachedProps($basepath) {
		$att_reg = $this -> entity -> getRegAttaches();
		
		foreach ($att_reg as $propiedad => $att_config) {
			$this -> upload($propiedad, $basepath);
		}
	}


	/**
	 * Carga el archivo asociado a la propiedad
	 */
	public function upload($propiedad, $basepath) {
		
		if ($prop_reg = $this->getPropiedadReg($propiedad)){
				
			// nombe de la propiedad de la entidad que almacena el archivo cargado (diferente a la propiedad que almacena la ruta del archivo)
			$filetype_prop_name = $prop_reg['file_field'];
			
			$file = $this -> entity -> $filetype_prop_name;
			
		} else {
			// no se ha registrado, puede que haya que registrar la propiedad en el constructor de la entidad
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
		
		$file -> move($this -> getUploadRootDir($this->base_web.$basepath), $file -> getClientOriginalName());
		//$file -> move($this->getAbsolutePath($propiedad), $file -> getClientOriginalName());

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
		
	    return null === $ruta ? null : $this->getUploadRootDir($this->base_web).$ruta;
	  }
	
	  public function getWebPath($prop)
	  {
	  	// valor de la propiedad (ruta relativa)
	  	$ruta = $this->getPropiedad($prop);
		
	    return null === $ruta ? null : $this -> entity->getUploadDir().'/'.$ruta;
	  }
	  
	  /**
	   * Valor de la propiedad de la entidad gestionada
	   */
	  protected function getPropiedad($prop){
	  	$prop_reg = $this->getPropiedadReg($prop);
	  	
	  	if (!$prop_reg){
	  		throw new \Exception("No se ha registrado la propiedad '$prop' en la entidad gestionada", 1);
			  
	  		return null;
	  	}
	  	
	  	$getter = $prop_reg['getter'];
	  	
		return $this->entity->$getter();
	  }
	  
	  /**
	 * Obtiene el array de registro de una propiedad de la entidad. Registro como propiedad
	 * con archivo adjunto
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
	protected function getUploadRootDir($basepath) {
		return $basepath . $this -> entity->getUploadDir();
	}

}
