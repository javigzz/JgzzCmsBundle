<?php
namespace Jgzz\CmsBundle\Twig;

use Symfony\Component\Translation\TranslatorInterface;

use Jgzz\CmsBundle\SlugManager\SlugManager;

class jgzzcmsExtension extends \Twig_Extension {

	private $slugmanager;

	private $translator;

	private $container;

	public function __construct(TranslatorInterface $translator, SlugManager $slugmanager, $container) {
		$this -> slugmanager = $slugmanager;

		$this -> translator = $translator;

		$this -> container = $container;
	}

	public function getName() {
		return 'jgzzcms';
	}

	public function getFunctions() {
		return array(
		'slugi18n' => new \Twig_Function_Method($this, 'slugi18n'), 
		'current_slug_i18n' => new \Twig_Function_Method($this, 'getCurrentSlugI18n'), 
		'css_active_class' => new \Twig_Function_Method($this, 'getCssActiveClass'), 
		'nivel_active_key' => new \Twig_Function_Method($this, 'getIsKeywordActive'), 
		'content_tree_by_keyword' => new \Twig_Function_Method($this, 'getContentTreeByKeyword'), );
	}

	public function getFilters() {
		return array('f_urls_i18n' => new \Twig_Filter_Method($this, 'filterUrlsI18nWeb'), );
	}

	/**
	 * Devuelve el slug para la clave y locale que se pasan. Si no se encuentra slug
	 * se busca en el servicio de traducción
	 *
	 */
	public function slugi18n($key, $locale = null) {

		if ($slug = $this -> slugmanager -> getSlugAbsolutoByKeyLocale($key, $locale)) {
			return $slug;
		}

		// probamos con el servicio de traducción
		return $this -> translator -> trans($key, array(), 'slugs');
	}

	public function getCurrentSlugI18n($locale) {
		$slug = $this -> slugmanager -> getSlugAbsolutoCurrentEntityByLocale($locale);

		if (isset($slug)) {
			return $slug;
		}

		// si no se encuentra slug, se asume dirección a raiz del locale
		// TODO: integrar con reglas enrutamiento con locale
		return $locale;
	}

	/**
	 * Comprueba si el keyword pasado coincide con alguno de los keywords de las entidades activas.
	 * (entidades aludidas por el slug completo)
	 *
	 * Devuelve declaración de clase css con o sin 'class=' en base a $add_class
	 */
	public function getCssActiveClass($check_keyword, $active_class, $add_class = true) {

		$nivel = $this -> getIsKeywordActive($check_keyword);

		return $nivel > 0 ? $add_class ? 'class=' . $active_class : $active_class : '';
	}

	/**
	 * Comprueba si un keyword está activo (aludido por slug actual).
	 * Si está activo devuelve el nivel de slug en el que se encuentra:
	 * ej: $check_keyword = keyprodA
	 * slug completo = slugcategoria/slugprodA
	 * devolvería 2 (slugprodA es el slug propio de keyprodA)
	 *
	 * @var $check_keyword
	 *
	 * @return mixed: int/false
	 */
	public function getIsKeywordActive($check_keyword) {
		// keywords activos según el slug completo
		$active_entities_sa = $this -> slugmanager -> getActiveEntitiesSummaryArray();

		$nivel = 1;
		foreach ($active_entities_sa as $slug_level_zerobased => $sumary) {
			//echo "k: ".$keyword." ch:".$check_keyword." n:".$nivel;
			if ($sumary['keyword'] === $check_keyword) {

				return $nivel;
			}
			$nivel += 1;
		}

		return false;
	}

	/**
	 * Arbol de nodos de contenido de los tipos $tipos, partiendo del contenido
	 * $keyword. No devuelve objetos, sino arrays anidados con información básica
	 * sobre los nodos de contenido.
	 *
	 * Ej: getContentTreeByKeyword('productos', array('categoria', 'producto')
	 * Arbol de los contenidos dependientes del nodo 'productos' y que son de los tipos
	 * 'producto' o 'categoria'
	 */
	public function getContentTreeByKeyword($keyword, $include_root = false, $tipos = array('page'), $niveles = 2) {

		$curr_entity_sumary = $this -> slugmanager -> findEntitySummaryArrayByKeyword($keyword);

		if (!isset($curr_entity_sumary)) {
			return null;
		}

		// servicio que se ocupa de formar estructuras array de nodos de contenido
		$content_tree_mananger = $this -> container -> get('jgzz.contenttreemanager');

		return $content_tree_mananger -> getArbolContenido($tipos, $curr_entity_sumary['id'], $include_root, $niveles);

	}

	/**
	 * Filtro que transforma los 'shortcodes' de enlaces a contenidos de la web en urls
	 * internacionalizadas en base al locale en sesión.
	 * Shortcode de la forma [ruta keyword]
	 */
	public function filterUrlsI18nWeb($str) {
		// TODO: permitir especificar el locale en plantilla con sintaxis [ruta KEYWORD LOCALE]
		//return preg_replace_callback("/\[ruta\s([^\]\s]+)\s?([^\]]*)\]/iu", array($this, 'doFilterUrlsI18nWeb'), $str);
		return preg_replace_callback("/\[ruta\s([^\]]+)\]/iu", array($this, 'doFilterUrlsI18nWeb'), $str);
	}

	private function doFilterUrlsI18nWeb($match) {
//print_r($match);
		$locale = $this -> container -> get('session') -> getLocale();

		$slug = $this -> slugmanager -> getSlugAbsolutoByKeyLocale($match[1], $locale);

		if (empty($slug)) {
			return $match[1];
		}

		// servicio router para generar
		return $this -> container -> get('router') -> generate('', array('slug' => $slug), true);

	}

}
