<?php
namespace Jgzz\CmsBundle\Interfaces;

/**
 * Se definene los métodos que debe implementar una entidad para poder utilizar
 * las funciones del AttachPropoertyManager: gestionar la carga de archivos asociados
 * a una o varias de las propiedades de la entidad.
 * 
 */
interface EntityAttachPropsManagerInterface {
	
	/**
	 * Obtiene todos los registros de adjuntos de la entidad. 
	 * AttachPropertyManager espera un array asociativo de arrays (éstos con determinados índices descriptivos)
	 * 
	 * @return array
	 */
	function getRegAttaches();
	
	/**
	 * Ruta relativa base de todos los adjuntos relacionados con la entidad
	 */
	function getUploadDir();
}
