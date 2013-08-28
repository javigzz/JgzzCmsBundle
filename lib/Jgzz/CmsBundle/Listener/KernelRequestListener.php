<?php
namespace Jgzz\CmsBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Listner de soporte para permitir la inyección de servicio request en TranslatableSuscriber
 * ver https://github.com/symfony/symfony/issues/5486
 */ 
class KernelRequestListener {

	private $translatableSuscriber;

	public function __construct(TranslatableEventSuscriber $translatableSuscriber)
	{
		$this->translatableSuscriber = $translatableSuscriber;
	}

	public function onKernelRequest(GetResponseEvent $event)
    {
        $this->translatableSuscriber->setRequest($event->getRequest());
    }

}
