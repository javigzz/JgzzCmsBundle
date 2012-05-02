<?php
namespace Jgzz\CmsBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Jgzz\DoctrineI18n\Entity\Translatable;
use Jgzz\DoctrineI18n\Entity\Repository\TranslatableRepository;


/**
 * Extensión de la clase Admin de Sonata para integrar con bundle JgzzDoctrineI18n
 * para internacionalización de entidades
 * 
 */
abstract class I18nAdmin extends Admin {
	
	private $entity_manager;
	
	protected function getEntityManager()
	{
		if(!isset($this->entity_manager)){
			$this->entity_manager = $this->getModelManager()->getEntityManager();
		}
		
		return $this->entity_manager;
	}
	
	/**
	 * Sobreescribo método para integrar con i18n.
	 * Se recupera la entidad usando el parámetro locale
	 */
    public function getObject($id)
    {
    	$class = $this->getClass();
		
		$mm = $this->getModelManager();
    	
    	$values = array_combine($mm->getIdentifierFieldNames($class), explode('-', $id));
		
		$er = $mm->getEntityManager()->getRepository($class);
		
		if ( $er instanceof TranslatableRepository )
		{
			// recuperamos entidades traducidas

			// locale: pasado en request o por defecto
			$locale = $this->getRequest()->query->get('locale', $this->default_locale);
			
			// método find específico de entidades traducibles según jgzz i18n
			return $er->findLocale($locale, $id);
		
		} else {

			// comportamiento default Admin
			return parent::getObject($id);
		}

    }
    
	/**
	 * Estamos editando la entidad?. ... mejor manera?
	 */
	public function isEdit()
	{
		return $this->getRequest()->get('_route') == $this->baseRouteName.'_edit';
	}
	
	/**
	 * Locale del objeto actual, si no, del request.
	 * 
	 */
	public function currentLocale()
	{
		$subject = $this->getSubject();
		if ($subject)
		{
			$ob_locale = $subject->getLocale();
		}
		
		
		if(  isset($ob_locale) )
		{
			return $ob_locale;
		} 
		elseif( $req_locale = $this->reqLocale() )
		{
			return $req_locale;
			
		} else {
			return null;
		}
	}
	
	public function reqLocale()
	{
		return isset($this->request) ? $this->request->query->get('locale', null) : null;
	}
	
	/**
	 * Modificación de la generación de urls de objeto. Cuando éstos son entidades traducibles, 
	 * se añade el parámetro locale a los parámetros de la url (si no venía ya establecido).
	 * 
	 */
	public function generateObjectUrl($name, $object, array $parameters = array())
	{
	
		if( !array_key_exists('locale', $parameters) && is_a($object, 'Translatable') )
		{
			$parameters['locale'] = $object->getLocale();
		}
		
		return parent::generateObjectUrl($name, $object, $parameters);
	}
	
	/**
	 * Sobreescrito para generar las url de edición con parámetro locale
	 * siempre que nos encontremos en una página localizada (páginas de edición)
	 */	
	public function generateUrl($name, array $parameters = array())
    {
    
    	if ($name == 'edit' && !array_key_exists('locale', $parameters))
		{
			$locale = $this->currentLocale();	
			
			if ($locale)
			{
				$parameters['locale'] = $locale;
			}
		}
		
		return parent::generateUrl($name, $parameters);
	}
	
}
