<?php
namespace Jgzz\CmsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class ImageFileType extends AbstractType
{
    public function getDefaultOptions(array $options)
    {
		return array(
			'current_file' => null,
			'max_width' => 200,
		);
    }

    public function getParent(array $options)
    {
        return 'file';
    }

    public function getName()
    {
        return 'imagefile';
    }
	
	 public function buildForm(FormBuilder $builder, array $options)
    {
    	$builder
            ->setAttribute('current_file', $options['current_file'])
			->setAttribute('max_width', $options['max_width'])
			;
	}
	
	public function buildView(FormView $view, FormInterface $form){
		
		//$options = $this->getDefaultOptions(array());
		
		$view
            ->set('current_file', "/".$form->getAttribute('current_file'))
			->set('max_width', $form->getAttribute('max_width'))
            
        ;
	}
}