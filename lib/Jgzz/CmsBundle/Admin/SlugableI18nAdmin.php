<?php
namespace Jgzz\CmsBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Jgzz\DoctrineI18n\Entity\Translatable;
use Jgzz\DoctrineI18n\Entity\Repository\TranslatableRepository;

/**
 * Extensión de la clase I18nAdmin para aquellas entidades
 * que requieran el seguimiento y actualización de slugs absolutos
 * TODO: abstraer esto de otra manera->decorator?
 *
 */
abstract class SlugableI18nAdmin extends I18nAdmin {

	protected $usa_slugs_absolutos = true;

	private $separador_slugs;
	
	
	/**
	 * Actualización del slug absoluto
	 * comentado xq ahora se ocupa el event subscriber slugupdatereventsubscriber
	 */
	/*private function actualizaSlugAbsoluto($object){
		$er = $this->getModelManager()->getEntityManager()->getRepository($this->getClass());
		
		// comprobar si ha cambiado el padre --> actualizar los slug de las versiones 
		//  traducidas
		$padre_cambia = false;
		
		
		$er->actualizaSlugAbsoluto($object, $padre_cambia);
	}*/
	
	/*
	private function transmiteSlugAHijas($object){
		
	}
	 */
	

	/*
	 * comentado xq ahora se ocupa el event subscriber slugupdatereventsubscriber
	public function preUpdate($object) {

		parent::preUpdate($object);
		
		// se implementa listern por via subscriber
		//XXX $this->actualizaSlugAbsoluto($object);
		
	}
	*/

	/**
	 * Transmisión del slug hacia abajo
	 */
	 
	public function postUpdate($object) {
		parent::postUpdate($object);

		$er = $this->getModelManager()->getEntityManager()->getRepository($this->getClass());
		
		// comprobar si ha cambiado el padre --> actualizar los slug de todas las versiones 
		//  traducidas
		$padre_cambia = false;
		
		$er->transmiteSlugAHijas($object, array());
		
		$this->getModelManager()->getEntityManager()->flush();
		
		//$n = $this -> transmiteSlugAHijas($object);
	}
	
	  

	/*
	 * comentado xq ahora se ocupa el event subscriber slugupdatereventsubscriber
	public function prePersist($object) {
		parent::prePersist($object);

		//XXX $this->actualizaSlugAbsoluto($object);
	}
	 
	 */

	/*
	 * COMENTADO: NO TIENE SENTIDO, UNA ENTIDAD NUEVA NO PUEDE TENER DESCENDIENTES
	 * EN EL MISMO MOMENTO DE CREARLA ... SALVO QUE SE CREEN EN EL MISMO ACTO?
	public function postPersist($object) {
		parent::postPersist($object);
		
		$this->transmiteSlugAHijas($object);
		
		$this->getModelManager()->getEntityManager()->flush();
		//$n = $this -> transmiteSlugAHijas($object);
	}
	 *
	 */

	protected function getSeparadorSlugs() {
		if (!isset($this -> separador_slugs)) {
			$this -> separador_slugs = $this -> configurationPool -> getContainer() -> get('jgzz.slugmanager') -> getSlugSeparator();
		}

		return $this -> separador_slugs;
	}

}
