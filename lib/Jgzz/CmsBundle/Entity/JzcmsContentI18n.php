<?php

namespace Jgzz\CmsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Jgzz\DoctrineI18n\Entity\Translation as Translation;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Jgzz\CmsBundle\Entity\JzcmsContentI18n
 *
 * @ORM\Entity(repositoryClass="Jgzz\CmsBundle\Entity\JzcmsContentI18nRepository")
 */
abstract class JzcmsContentI18n extends Translation
{
  	
	/**
	 * @ORM\ManyToOne(targetEntity="JzcmsContent", inversedBy="jgzz_translations", fetch="EAGER")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $parent;
	
	
    /**
     * @var text $text
     *
     * @ORM\Column(name="text", type="text", nullable=true)
	 * 
	 * @Assert\NotBlank()
     */
    private $text;

    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @var string $subtitle
     *
     * @ORM\Column(name="subtitle", type="string", length=255, nullable=true)
     */
    private $subtitle;

    /**
     * @var string $slug
     *
     * @ORM\Column(name="slug", type="string", length=255, nullable=true)
     */
    private $slug;
	
	
	/**
	 * Slug absoluto, incluyendo las referencias a los contenidos padre
	 * 
	 * @ORM\Column(name="slug_absoluto", type="string", length=255, nullable=true)
	 */
	private $slug_absoluto;


    /**
     * Set text
     *
     * @param text $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * Get text
     *
     * @return text 
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set subtitle
     *
     * @param string $subtitle
     */
    public function setSubtitle($subtitle)
    {
        $this->subtitle = $subtitle;
    }

    /**
     * Get subtitle
     *
     * @return string 
     */
    public function getSubtitle()
    {
        return $this->subtitle;
    }

    /**
     * Set slug. Filtrado del slug proporcionado
     *
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $this->slugize($slug);
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }
	
	
	public function setSlugAbsoluto($slug)
    {
        $this->slug_absoluto = $slug;
    }

    public function getSlugAbsoluto()
    {
        return $this->slug_absoluto;
    }
	
	/**
	 * función para generar una cadena apta para url amigable
	 * http://www.gongoraescobar.com/personal/php-mysql/funcion-para-crear-slugs-url-amigables/
	 */
	public function slugize($name,$utf=true){

		$sname = trim($name); //remover espacios vacios
		
		$sname = strtolower(preg_replace('/\s+/','-',$sname)); // pasamos todo a minusculas y cambiamos todos los espacios por -
		
		if($utf){ // se el texto no viene en formato utf8 se le manda a codificar como tal.
		$sname = utf8_decode($sname);
		}
		// Lista de caracteres latinos y sus correspondientes para slug
		$table = array(
		'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'C'=>'C', 'c'=>'c', 'C'=>'C', 'c'=>'c',
		'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
		'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
		'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'S',
		'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
		'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
		'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
		'ÿ'=>'y', 'R'=>'R', 'r'=>'r', ','=>''
		);
		
		$sname = strtr($sname, $table); // remplazamos los acentos, etc, por su correspondientes
		$sname = preg_replace('/[^A-Za-z0-9-]+/', '', $sname); // eliminamos cualquier caracter que no sea de la a-z o 0 al 9 o -
		
		return $sname;
	}
	
	
}
