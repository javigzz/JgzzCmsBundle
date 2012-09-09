<?php
namespace Jgzz\CmsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
//use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;

/**
 * Adaptada a componente forms de syfmony 2.1
 */
class ImageFileType extends AbstractType
{
  //   public function getDefaultOptions(array $options)
  //   {
		// return array(
		// 	'current_file' => null,
		// 	'max_width' => 200,
		// 	'width'	=> null,
		// 	'height' => null,
		// );
  //   }
  //   
  	public function setDefaultOptions(OptionsResolverInterface $resolver)
{
    $resolver->setDefaults(array(
        'current_file' => null,
		'height' => null,
		'max_width' => 200,
		'width'	=> null,
    ));
}

    public function getParent()
    {
        return 'file';
    }

    public function getName()
    {
        return 'imagefile';
    }
	
	/*
	se ha sustituido interfaz de FormBuilder por FormBuilderInterface
	por compatibilidad con sf 2.1, así como añadido array options en buildView
	sin embargo esto no es suficiente para garantizar BC con sf 2.0 así que se 
	optará por 
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	$builder
            ->setAttribute('current_file', $options['current_file'])
			->setAttribute('max_width', $options['max_width'])
			;
	}
	
	public function buildView(FormView $view, FormInterface $form, array $options){
		
		//$options = $this->getDefaultOptions(array());
		
		$view
            ->set('current_file', "/".$form->getAttribute('current_file'))
			->set('max_width', $form->getAttribute('max_width'))
            
        ;
	}
}