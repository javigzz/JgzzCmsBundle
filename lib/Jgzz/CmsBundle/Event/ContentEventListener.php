<?php
namespace Jgzz\CmsBundle\Event;

use Doctrine\ORM\OnFlushEventArgs;
use Jgzz\DoctrineI18n\Entity\Translatable;


class ContentEventListener {
	
	// public function onFlush(OnFlushEventArgs $eventArgs){
// 		
		// $em = $eventArgs->getEntityManager();
        // $uow = $em->getUnitOfWork();
// 
        // foreach ($uow->getScheduledEntityUpdates() AS $entity) {
//         	
        // }
	// }
	
	
	public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        if ($eventArgs->getEntity() instanceof Translatable) {
            if ($eventArgs->hasChangedField('parent')) {
            	echo "ha cambiado parent";
                //$eventArgs->setNewValue('name', 'Bob');
            } else {
            	echo "no ha cambiado parent";
            }
        }
    }
	
}
