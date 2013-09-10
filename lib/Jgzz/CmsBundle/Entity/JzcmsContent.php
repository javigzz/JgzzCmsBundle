<?php

namespace Jgzz\CmsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Jgzz\DoctrineI18n\Entity\Translatable as Translatable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Jgzz\CmsBundle\Entity\JzcmsContent
 *
 * @ORM\Entity(repositoryClass="Jgzz\CmsBundle\Entity\JzcmsContentRepository")
 * @ORM\HasLifecycleCallbacks
 */
abstract class JzcmsContent extends Translatable
{
 
     /**
     * @var string $jgzz_translations
	 * 
	 * Propiedad obligatoria para integrar con las traducciones
	 * 
	 * @ORM\OneToMany(targetEntity="JzcmsContentI18n", mappedBy="parent", cascade={"persist"})
	 */
    protected $jgzz_translations;
	
	
    /**
     * @var string $type
     *
     * @ORM\Column(name="type", type="string", length=15)
	 * 
	 * Validaciones: 
	 * @Assert\NotBlank() 
     */
    protected $type;
	
	
	/**
	 * Identificador / descriptor único del contenido.
	 * 
	 * @ORM\Column(name="keyword", type="string", length=30, unique=true, nullable=true)
	 * 
	 * @Assert\NotBlank() 
	 */ 
	protected $keyword;
	
	
	/**
	 * Apunta al registro de contenido padre. Útil para anidar contenidos: subsecciones 
	 * dentro de una sección o categoría.
	 * 
	 * @ORM\ManyToOne(targetEntity="JzcmsContent", inversedBy="hijas")
     * @ORM\JoinColumn(name="parent_content_id", referencedColumnName="id")
	 * 
	 */
	protected $parent;
	
	/**
	 * @ORM\OneToMany(targetEntity="JzcmsContent", mappedBy="parent")
	 */
	protected $hijas;
	

    /**
     * @var datetime $created_at
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
	 * 
	 * Validaciones: 
	 * @Assert\DateTime() 
     */
    protected $created_at;

    /**
     * @var datetime $updated_at
     *
     * @ORM\Column(name="updated_at", type="datetime")
	 * 
	 * Validaciones: 
	 * @Assert\DateTime() 
     */
    protected $updated_at;
	
	
	/**
     * @var boolean $visible
     *
     * @ORM\Column(name="visible", type="boolean")
     */
	private $visible = false;


    public function __construct()
    {
    	parent::__construct();
		
		$this -> hijas = new \Doctrine\Common\Collections\ArrayCollection();
		
        // constructor is never called by Doctrine
        $this->created_at = $this->updated_at = new \DateTime("now");
    }


	public function getTranslations()
	{
		return $this->jgzz_translations;
	}

 
    /**
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

	public function getVisible()
	{
		return $this->visible;
	}
	public function setVisible($visible)
	{
		$this->visible = $visible;
	}
	
	public function getKeyword()
	{
		return $this->keyword;
	}
	public function setKeyword($k)
	{
		$this->keyword = $k;
	}
		    
    /**
     * Set created_at
     *
     * @param datetime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
    }

    /**
     * Get created_at
     *
     * @return datetime 
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set updated_at
     *
     * @param datetime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;
    }

    /**
     * Get updated_at
     *
     * @return datetime 
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }
	

	public function getParent()
	{
		
		return $this->parent;
	}
	
	public function setParent(JzcmsContent $parent = null)
	{
		$this->parent = $parent;
		
	}
	
	public function __toString()
	{
		$st =  $this->keyword;
		
		 if ( $this->hasCurrentTranslation() && $locale = $this->getLocale())
		 {
			$st .= " / ".$locale;
		 }
		
		return $st;
	}
	
	 /**
	  * Actualiza la fecha de modificación mediante hook preupdate
	  * 
     * @ORM\PreUpdate
     */
    public function updated()
    {
        $this->updated_at = new \DateTime("now");
    }
    
	
	/*
	 * propiedades 'traducidas'
	 * necesario para especificar criterios de validación
	 */
	 
 
	 
    /*
	 * métodos delegados en i18n
	 * necesario para integración en formularios (sonata)
	 * TODO: buscar la manera de relacionar el formulario directamente con
	 * la entidad traducida, sin duplicar aquí
	 * 
	 * 
	 */
	public function getTitle()
	{
		return $this->getCurrentTranslation()->getTitle();
	}
	

	public function setTitle($title)
	{
		$this->getCurrentTranslation()->setTitle($title);
	}
	
	public function getSubtitle()
	{
		return $this->getCurrentTranslation()->getSubtitle();
	}
	
	public function setSubtitle($stitle)
	{
		$this->getCurrentTranslation()->setSubtitle($stitle);
	}
	public function getText()
	{
		return $this->getCurrentTranslation()->getText();
	}
	public function setText($text)
	{
		$this->getCurrentTranslation()->setText($text);
	}
	
	public function getSlug()
	{
		return $this->getCurrentTranslation()->getSlug();
	}
	public function setSlug($slug)
	{
		$this->getCurrentTranslation()->setSlug($slug);
	}
	
	public function getSlugAbsoluto()
	{
		return $this->getCurrentTranslation()->getSlugAbsoluto();
	}
	public function setSlugAbsoluto($slug)
	{
		$this->getCurrentTranslation()->setSlugAbsoluto($slug);
	}
	
	
}