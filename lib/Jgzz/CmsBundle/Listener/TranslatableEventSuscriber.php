<?php
namespace Jgzz\CmsBundle\Listener;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Jgzz\CmsBundle\Entity\JzcmsContent;

/**
 * 
 */ 
class TranslatableEventSuscriber {

	private $request;

	public function setRequest(Request $request)
	{
		$this->request = $request;
	}


	/**
	 * Setea el locale del objeto si es Translatable en base al request
	 * 
	 * @param  [type] $entity [description]
	 * @return [type]         [description]
	 */
	public function postLoad(LifecycleEventArgs $args)
    {
    	$entity = $args->getEntity();
    	$em = $args->getEntityManager();

		if ( !is_a($entity, 'Jgzz\DoctrineI18n\Entity\Translatable') ){
			return;
		}
		
		if($entity->hasCurrentTranslation()){

			// $repo = $em->getRepository('Jgzz\DoctrineI18n\Entity\Translatable');

			if($entity->getCurrentTranslation()->getLocale()){
				return;
			}

			$locale = $this->request->getLocale();

			$entity->getCurrentTranslation()->setLocale($locale);
		}

    }

}
