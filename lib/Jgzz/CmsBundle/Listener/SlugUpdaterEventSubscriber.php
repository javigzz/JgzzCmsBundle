<?php
namespace Jgzz\CmsBundle\Listener;
 
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Jgzz\CmsBundle\Entity\JzcmsContent;
use Jgzz\CmsBundle\Entity\JzcmsContentRepository;

/**
 * Clase suscriber de eventos para manejar la actualización de los slugs
 * de entidades que utilizan el JzcmsContentRepository
 */ 
class SlugUpdaterEventSubscriber 
{
	/**
	 * If true, slug_absoluto will be updated on update and persist
	 * @var boolean
	 */
	public $autoUpdateSlugAbsoluto = true;
	
	/**
	 * Maneja actualización del slug absoluto en base al nuevo slug y al 
	 * slug del padre, si es que este ha cambiado en la edición del objeto.
	 * 
	 * El método actualizaSlugAbsoluto se encarga de modificar el slug absoluto de 
	 * todas las versiones de idioma en este último caso (ha cambiado el padre), para 
	 * ello se debe comprobar si el campo 'padre' se ha modificado.   
	 */
	public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
    	if(!$this->autoUpdateSlugAbsoluto) {
    		return;
    	}

		$entity = $eventArgs->getEntity();
		
		if ( !is_a($entity, 'Jgzz\DoctrineI18n\Entity\Translatable') 
			&& 
			!is_a($entity, 'Jgzz\DoctrineI18n\Entity\Translation')){
				return;
		}
		
		/*
		 * el objeto sobre el que se realizan las acciones debe ser 
		 * el objeto principal, no la trauducción
		 * TODO: esto sobra! si no es Translatable, return!
		 */
		$object = is_a($entity, 'Jgzz\DoctrineI18n\Entity\Translation') ?
			$entity -> getParent() :
			$entity;
		
		$er = $eventArgs->getEntityManager()->getRepository(get_class($object));
		
		if (!($er instanceof JzcmsContentRepository)) {
			return;
		}

		$padre_cambia = $eventArgs->hasChangedField('parent');
		$er->actualizaSlugAbsoluto($object, $padre_cambia);
    }
	
	/**
	 * En caso de entidad nueva. Hay que manejar el caso en que 
	 * se reciba la entidad Translatable como en el que se reciba 
	 * la Tranlation (como en método preUpdate)
	 */
	public function prePersist(LifecycleEventArgs $args) 
	{
		if(!$this->autoUpdateSlugAbsoluto) {
    		return;
    	}

		$entity = $args->getEntity();

		/*
		 * el objeto sobre el que se realizan las acciones debe ser 
		 * el objeto principal, no la trauducción
		 */
		$object = is_a($entity, 'Jgzz\DoctrineI18n\Entity\Translation') ?
			$entity->getParent() :
			$entity;
		
		$er = $args->getEntityManager()->getRepository(get_class($object));
		
		if (!($er instanceof JzcmsContentRepository)) {
			return;
		}
		
		$slug = $object->getSlug();
	
		if(empty($slug)){
			throw new \Exception("Se esperaba que la entidad tuviera un slug");
		}
			
       	$er	-> actualizaSlugAbsoluto($object, false);
	}
}
    	