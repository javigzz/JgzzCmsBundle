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
	 * TODO: comprobar si ha cambiado el campo slug propio o si ha cambiado el padre 
	 * en este último caso se deberán actualizar todos los idiomas de la entidad
	 */
	private function actualizaSlugAbsoluto($object){
		// TODO: implementar evento para comprobar si se ha modificado el padre, y así 
// ordenar actualizar todos los idiomas
		$er = $this->getModelManager()->getEntityManager()->getRepository($this->getClass());
		
		// comprobar si ha cambiado el padre --> actualizar los slug de las versiones 
		//  traducidas
		$padre_cambia = false;
		
		
		$er->actualizaSlugAbsoluto($object, $padre_cambia);
	}
	
	private function transmiteSlugAHijas($object){
		$er = $this->getModelManager()->getEntityManager()->getRepository($this->getClass());
		
		// comprobar si ha cambiado el padre --> actualizar los slug de todas las versiones 
		//  traducidas
		$padre_cambia = false;
		
		
		$er->transmiteSlugAHijas($object, array(), $padre_cambia);
	}


	public function preUpdate($object) {

		parent::preUpdate($object);

		$this->actualizaSlugAbsoluto($object);
		
	}

	/**
	 * Transmisión del slug hacia abajo
	 */
	public function postUpdate($object) {
		parent::postUpdate($object);

		$this->transmiteSlugAHijas($object);
		
		$this->getModelManager()->getEntityManager()->flush();
		
		//$n = $this -> transmiteSlugAHijas($object);
	}

	public function prePersist($object) {
		parent::prePersist($object);

		$this->actualizaSlugAbsoluto($object);
	}

	public function postPersist($object) {
		parent::postPersist($object);
		
		$this->transmiteSlugAHijas($object);
		
		$this->getModelManager()->getEntityManager()->flush();
		//$n = $this -> transmiteSlugAHijas($object);
	}

	protected function getSeparadorSlugs() {
		if (!isset($this -> separador_slugs)) {
			$this -> separador_slugs = $this -> configurationPool -> getContainer() -> get('jgzz.slugmanager') -> getSlugSeparator();
		}

		return $this -> separador_slugs;
	}

}
