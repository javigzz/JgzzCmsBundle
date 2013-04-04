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
	 * Transmisión del slug hacia abajo
	 */
	 
	public function postUpdate($object) {
		parent::postUpdate($object);

		$em = $this->getModelManager()->getEntityManager($this->getClass());

		$er = $em->getRepository($this->getClass());
		
		// comprobar si ha cambiado el padre --> actualizar los slug de todas las versiones 
		//  traducidas
		$padre_cambia = false;
		
		$er->transmiteSlugAHijas($object, array());
		
		$em->flush();
		
		//$n = $this -> transmiteSlugAHijas($object);
	}
	

	protected function getSeparadorSlugs() {
		if (!isset($this -> separador_slugs)) {
			$this -> separador_slugs = $this -> configurationPool -> getContainer() -> get('jgzz.slugmanager') -> getSlugSeparator();
		}

		return $this -> separador_slugs;
	}

}
