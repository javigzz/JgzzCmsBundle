<?php
namespace Jgzz\CmsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Prueba la salida de una búsqueda de arbol de contenidos
 */
class ArbolTestCommand extends ContainerAwareCommand {


	protected function configure() {

		$this -> setName('jgzzcms:arboltest') -> setDescription('Test de generación de árboles de contenido');
		
		$this->addOption('root', null, InputOption::VALUE_OPTIONAL, 'Id del root de contenidos', 1);
		$this->addOption('include', null, InputOption::VALUE_OPTIONAL, 'Incluye root en arbol', 0);	
		$this->addOption('nivel', null, InputOption::VALUE_OPTIONAL, 'Niveles', 1);
		
	}
	
	protected function execute(InputInterface $input, OutputInterface $output) {
		
		$arbol = $this->getContainer()->get('jgzz.contenttreemanager')->
		getArbolContenido(
			array("page", "categoria", "producto"), 
			$input->getOption('root'), 
			$input->getOption('include'), 
			$input->getOption('nivel')
			);
		
		var_dump($arbol);
		
	}
	
}