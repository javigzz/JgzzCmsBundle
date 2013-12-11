<?php
namespace Jgzz\CmsBundle\Listener;
 
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Jgzz\CmsBundle\Entity\JzcmsContent;

/**
 * Clase suscriber de eventos para manejar la actualización de los slugs
 * de entidades que utilizan el JzcmsContentRepository
 */ 
class SlugUpdaterEventSubscriber {

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
		
		/*
		 * comprobamos que existe el método actualizaSlugAbsoluto en el repositorio, 
		 * que no es específico de Translatable sindo de algunas 'subclases'...
		 * 
		 */
		if(!(method_exists($er, 'actualizaSlugAbsoluto')))
		{
			return;
		}
		
		/*
		 * ojo, parent tiene diferente significado para un objeto JzcmsContent
		 * que para un objeto Translation:
		 */
		if ( is_a($er, 'Jgzz\CmsBundle\Entity\JzcmsContentRepository')) {
			// comprobamos si el padre ha cambiado
			$padre_cambia = $eventArgs->hasChangedField('parent');
			
		} else {
			// XXX ojo, esto  no es necesario al haber homogeneizado object más arriba
			
			// en caso de que se esté tratando un objeto Translation,
			// se entiende que el 'padre' no cambia a los efectos del 
			// método actualizaSlug...
			// TODO: esto puede provocar que se ejecute dos veces actualizaSlugA
			// una por motivo del Translation y otra del Translatable
			// SOLUCIONAR
			$padre_cambia = false;
			
		}
		//var_dump($padre_cambia);
		
		
		$er	-> actualizaSlugAbsoluto($object, $padre_cambia);

    }
	
	/**
	 * En caso de entidad nueva. Hay que manejar el caso en que 
	 * se reciba la entidad Translatable como en el que se reciba 
	 * la Tranlation (como en método preUpdate)
	 */
	public function prePersist(LifecycleEventArgs $args) {

		$entity = $args->getEntity();
		
		//$er = $args->getEntityManager()->getRepository(get_class($entity));
		
		/*
		 * el objeto sobre el que se realizan las acciones debe ser 
		 * el objeto principal, no la trauducción
		 */
		$object = is_a($entity, 'Jgzz\DoctrineI18n\Entity\Translation') ?
			$entity -> getParent() :
			$entity;
		
		$er = $args->getEntityManager()->getRepository(get_class($object));
		
		/*
		 * comprobamos que existe el método actualizaSlugAbsoluto en el repositorio, 
		 * que no es específico de Translatable sindo de algunas 'subclases'...
		 */
		if(!(method_exists($er, 'actualizaSlugAbsoluto')))
		{
			return;
		}
		
		
		/*
		 * Si el repositorio tiene el método actualizaSlugAbs...
		 */
		if ( is_a($er, 'Jgzz\CmsBundle\Entity\JzcmsContentRepository')) {
			
			$slug = $object->getSlug();
		
			if(empty($slug)){

				throw new \Exception("Se esperaba que la entidad tuviera un slug");
				
				// $slug_sintetico = $object->getKeyword()."_".strtoupper($object->getLocale());
				
				// $slug_sintetico .= "_".substr(md5($slug_sintetico), 0, 6);
				
				// $object -> setSlug($slug_sintetico);
			}
			
			
        	$er	-> actualizaSlugAbsoluto($object, false);
			
        }
	}
	
	/*
	 * TODO: traer el evento de transmisión de slugs hacia abajo
	 * de SlugableI18nAdmin
	 * 
	public function postUpdate(LifecycleEventArgs $args) {
		
		$entity = $args->getEntity();
		
		$er = $eventArgs->getEntityManager()->getRepository(get_class($entity));
		
		if ( is_a($er, 'Jgzz\CmsBundle\Entity\JzcmsContentRepository')) {
			
			// comprobamos si el padre ha cambiado
			//$padre_cambia = $eventArgs->hasChangedField('parent');
			
        	$er->transmiteSlugAHijas($entity, array());
			
			$er->flush();
			
        }
		
		
		
	}
	*/
}
    	