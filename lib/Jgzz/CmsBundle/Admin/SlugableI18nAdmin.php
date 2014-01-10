<?php
namespace Jgzz\CmsBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Jgzz\DoctrineI18n\Entity\Translatable;
use Jgzz\DoctrineI18n\Entity\Repository\TranslatableRepository;
use Jgzz\CmsBundle\Exception\SlugException;

/**
 * Extensión de la clase I18nAdmin para aquellas entidades
 * que requieran el seguimiento y actualización de slugs absolutos
 * TODO: abstraer esto de otra manera->decorator?
 *
 */
abstract class SlugableI18nAdmin extends I18nAdmin {

	protected $usa_slugs_absolutos = true;

	private $separador_slugs;
	
	public function update($object)
	{
		// controlamos aquí posibles errores al proecesar los slugs que dependen de slugs de padres
		try {
			parent::update($object);
		} catch (SlugException $e) {
			$this->getConfigurationPool()->getContainer()->get('session')->getFlashBag()->set('sonata_flash_error', 'Error al actualizar el 
				contenido. Mensaje original : '.$e->getMessage());

			// hack
			// he tenido que poner esto para 'simular' que la entidad ha sido 'manejada'. de otro modo no se genera
			// la url para volver a la ficha por error en ModelManager::getNormalizedIdentifier. No se conoce el id
			$this->getModelManager()->getEntityManager($object)->getUnitOfWork()->registerManaged($object, array($object->getId()), array());
		}
	}

	/**
	 * Chequeos lógicos sobre el parent
	 * 
	 * @param  [type] $object [description]
	 * @return [type]         [description]
	 */
    public function preUpdate($object)
    {
    	// hay que comprobar que no se de un fallo de slugs en la actualización, previamente. 
    }


	/**
	 * Transmisión del slug hacia abajo
	 */
	public function postUpdate($object) {
		parent::postUpdate($object);

		$em = $this->getModelManager()->getEntityManager($this->getClass());

		$er = $em->getRepository($this->getClass());
		
		$er->transmiteSlugAHijas($object, array());
		
		$em->flush();
	}
	

	protected function getSeparadorSlugs() {
		if (!isset($this -> separador_slugs)) {
			$this -> separador_slugs = $this -> configurationPool -> getContainer() -> get('jgzz.slugmanager') -> getSlugSeparator();
		}

		return $this -> separador_slugs;
	}

}
