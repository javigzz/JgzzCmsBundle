<?php
namespace Jgzz\CmsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Consultas relacionadas con los slugs disponibles en la aplicación.
 */
class SlugCacheCommand extends ContainerAwareCommand {

	protected function configure() {

		$this -> setName('jgzzcms:slugcache') -> setDescription('Consulta del caché de slugs');
		$this->addOption('key', null, InputOption::VALUE_OPTIONAL, 'clave que se quiere consultar', false);

	}
	
	protected function execute(InputInterface $input, OutputInterface $output) {
			
		$sm = $this->getContainer()->get('jgzz.slugmanager');
		
		//$sm->use_slugs_absolutos = true;
		
		$sca = $sm->getSlugCacheArray();
		
		if ($input->getOption('key')){
			var_dump($sca[$input->getOption('key')]);
		} else {
			
			var_dump($sca);
			
		}
		

	}

	

}
