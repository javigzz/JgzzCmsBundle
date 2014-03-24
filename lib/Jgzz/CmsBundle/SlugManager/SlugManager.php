<?php
namespace Jgzz\CmsBundle\SlugManager;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Clase que gestiona la recuperación contenidos de la base de datos en base a un
 * slug o cadena de slugs
 */
class SlugManager {

	//protected $managed_entity = 'JgzzCmsBundle:JzcmsContent';
	protected $managed_entity;
	// = 'Jgzz\CmsBundle\Entity\JzcmsContent';

	//protected $managed_entity_i18n = 'JgzzCmsBundle:JzcmsContentI18n';
	protected $managed_entity_i18n;
	// = 'Jgzz\CmsBundle\Entity\JzcmsContentI18n';

	protected $slug_separator = '/';

	protected $container;

	protected $repository_i18n;

	/**
	 * Slug completo pasado inicialmente para recuperar entidad/es
	 */
	protected $slug_absoluto;

	/**
	 * Determina si se recuperar´na las entidades
	 * atendiendo a su slug absoluto o a su slug propio.
	 * De utilidad cuando los slug están internacionalizados.
	 */
	public $use_slugs_absolutos;

	protected $slugchain;
	
	protected $slugchain_abs;

	/**
	 * Entities associatted with slugchain
	 */
	private $entitychain;
	
	/**
	 * Entity associated with last slug (or whole absolute slug)
	 */
	private $current_entity;
	
	/**
	 * Array de información resumida sobre la entidad aludida por el slug
	 */
	private $current_entity_summary_array;
	
	/**
	 * Array de summary arrays de las entidades 'activas', todas las entidades aludidas por el slug
	 */
	private $current_active_entities_summary_array;

	/**
	 * Weather the slugchain must be supervised for correct entities parenthood
	 * eg: foo/bar --> if $check_parenthood == true, checks foo is bar's parent
	 *
	 * @var bool $check_parenthood
	 */
	protected $check_parenthood = true;

	/**
	 * Weather the slugchain must be supervised for each slug pointing to an entity
	 *
	 * @var bool $check_slugentitymatch
	 */
	protected $check_slugentitymatch = true;

	/**
	 * Weather the slugchain must be supervised for each slug pointing to an entity
	 * with the same locale
	 *
	 * @var bool $check_samelocale
	 */
	protected $check_samelocale = true;

	/**
	 * Array que almacena el mapeo entre slugs internacionalizados y las claves
	 * de contenido e idioma a las que pertenecen.
	 * Almacena slugs absolutos. entidadpadre/entidadhija
	 */
	private $slug_cache;

	public function __construct(ContainerInterface $container) {
		$this -> container = $container;
		
		$this->use_slugs_absolutos = true;
		
		$this->slugchain = array();
		
		$this->slugchain_abs = array();
	}

	public function setManagedEntities($base_ent, $trad_ent) {
		// TODO: imponer interfaz a los argumentos

		$this -> managed_entity = $base_ent;
		$this -> managed_entity_i18n = $trad_ent;

		$this -> repository_i18n = $this -> container -> get('doctrine') -> getRepository($this -> managed_entity_i18n);
	}
	
	public function setUseSlugAbsoluto($use){
		$this->use_slugs_absolutos = $use;
	}

	public function configCheks($match, $parents, $locale) {
		$this -> check_slugentitymatch = $match;
		$this -> check_parenthood = $parents;
		$this -> check_samelocale = $locale;
	}

	/**
	 * Computa y establece el slugchain y en caso de 
	 * estar usando slugs absolutos, establece 
	 * el array de slugs absolutos
	 */
	public function setSlug($wholeslug) {

		$this -> slug_absoluto = $wholeslug;

		$this -> slugchain = $this -> getSlugArray($wholeslug);

		if($this->use_slugs_absolutos){

			// recuperamos todas los slugs absolutos parciales implicados por el slug completo
			$this->slugchain_abs = array();

			$slug_pre = '';

			foreach ($this->slugchain as $slug_part) {
				$slug_pre .= $slug_pre == '' ? $slug_part : $this -> slug_separator . $slug_part;
				array_push($this->slugchain_abs, $slug_pre);
			}
			
		} 
	}

	/**
	 * Array de slugs
	 */
	public function getSlugs() {
		return $this -> slugchain;
	}

	/**
	 * Get array of entities (related to current slugs) from object.
	 * Queries bd if not already loaded.
	 */
	public function getEntities() {
		if (!isset($this -> entitychain)) {

			// si se usa el slug absoluto obtenemos primero toda la cadena de slugs
			// absolutos para consultar a la bd.
			if ($this -> use_slugs_absolutos) {

				$query_array = array('slug_absoluto' => $this->slugchain_abs);

			} else {

				// usa slugs simples
				$query_array = array('slug' => $this -> slugchain);
			}


			// TODO: optimizar en consultas con i18n para traer el objeto completo
			$trads = $this -> repository_i18n -> findBy($query_array);

			$this -> entitychain = array();

			// almacenamos en entitychain las entidades
			foreach ($trads as $trad) {
				$trad -> getParent() -> setCurrentTranslation($trad -> getLocale());
				$this -> entitychain[$trad -> getSlug()] = $trad -> getParent();
			}
			
			/* 
			 * Comprobaciones sobre a las relaciones entre las entidades recuperadas
			 * en base a los slug
			*/
			// all slug - entity match
			if ($this -> check_slugentitymatch) {
	
				$this -> checkSlugEntityMatch();
			}
	
			// parenthood check among slugs in chain
			if ($this -> check_parenthood) {
	
				$this -> checkParenthood();
			}
	
			// all slugs in same locale
			if ($this -> check_samelocale) {
	
				$this -> checkSamelocaleSlugs();
			}
		}

		return $this -> entitychain;
	}


	/**
	 * Recupera la última entidad aludida por el slug (ya se esté usando slug absoluto o simple)
	 */
	public function getEntity() {
		
		if ($this -> use_slugs_absolutos) {
			// usa slug absoluto: ej: seccion/categoria/pagina
			$query_array = array('slug_absoluto' => end($this->slugchain_abs));
		} else {
			// usa último slug simple: ej: pagina
			$query_array = array('slug' => end($this -> slugchain));
		}

		// TODO: optimizar en consultas con i18n para traer el objeto completo de una vez
		$trad = $this -> repository_i18n -> findOneBy($query_array);
		
		$trad -> getParent() -> setCurrentTranslation($trad -> getLocale());
		$this->current_entity = $trad -> getParent();
		
		//\Doctrine\Common\Util\Debug::dump($ent);


		if (!is_a($this->current_entity, $this -> managed_entity)) {
			$info = !is_object($this->current_entity) ? "No es objeto" : ". Clase: " . get_class($this->current_entity);
			throw new \Exception("Objeto recuperado por slug ".$this->slug_absoluto."' no es instancia de " . $this -> managed_entity_i18n . " $info", 1);
		}

		// actualizamos locale de sesión en base a locale de entidad
		$this -> container -> get('request') -> setLocale($this->current_entity -> getLocale());

		return $this->current_entity;

	}

	
	public function getEntityByPos($pos) {
		return $this -> entitychain[$pos];
	}

	public function getNumEntities() {
		return count($this -> entitychain);
	}
	
	/**
	 * Recupera el resumen de datos de la entidad cuyo slug se pasa 
	 * o en su defecto de la entidad en foco.
	 */
	public function getEntitySummaryArray($slugabsolto = null){
		return isset($slugabsolto) ? $this->findEntitySummaryArrayBySlugAbs($slugabsolto) : $this->getCurrentEntitySummaryArray();
	}
	
	public function getCurrentKeyword(){
		$es = $this->getCurrentEntitySummaryArray();
		return $es['keyword'];
	}
	
	
	/**
	 * Recupera los resúmenes de todas las entidades aludidas por el slug
	 */
	public function getActiveEntitiesSummaryArray()
	{
		if (!isset($this->current_active_entities_summary_array)){

			if(!$this->use_slugs_absolutos){
				//throw new \Exception("No se puede usar si no se usan slugs absolutos", 1);
			}
			
			$sa = array();
			
			foreach ($this->slugchain_abs as $slugabs){
				$sum = $this->findEntitySummaryArrayBySlugAbs($slugabs);
				if(!empty($sum)){
					array_push($sa, $sum);
				} else {
					// throw new \Exception("No se encuentra el contendio asociado slug '$slugabs'", 1);
				}
			}
			
			$this->current_active_entities_summary_array = $sa;

		}
		
		return $this->current_active_entities_summary_array;
	}
	
	
	/**
	 * Obtiene y establece en objeto el resumen de datos de la entidad aludida por el slug absoluto
	 */
	protected function getCurrentEntitySummaryArray(){
		if (!isset($this->current_entity_summary_array)){
			$this->current_entity_summary_array = $this->findEntitySummaryArrayBySlugAbs($this->slug_absoluto);			
		}
		
		return $this->current_entity_summary_array;
	}
	
	
	


	/**
	 * Retrieves an array of slugs present in slugchain and
	 * separated by slug_separator
	 */
	private function getSlugArray($wholeslug, $separator = null) {
		$separator = isset($separator) ? $separator : $this -> slug_separator;

		return explode($separator, $wholeslug);
	}

	/**
	 * Recupera array de caché de slugs relacionados con sus claves y locales
	 * TODO: usar memcache u otro sistema de caché para evitar consultas repetidas.
	 */
	public function getSlugCacheArray() {
		if (!isset($this -> slug_cache)) {

			$bd_arr = $this -> container -> get('doctrine') -> getRepository($this -> managed_entity) -> getSlugKeyArray();

			//var_dump($bd_arr);
			foreach ($bd_arr as $reg) {
				
				// depende de qué tipo de slug se use
				$slug = $this->use_slugs_absolutos === true ? $reg['slug_absoluto'] : $reg['slug'];
				
				$this -> slug_cache[$reg['keyword']]['locales'][$reg['locale']] = $slug;
				//$this -> slug_cache[$reg['keyword']]['locales'][$reg['locale']] = $reg['slug_absoluto'];
				$this -> slug_cache[$reg['keyword']]['id'] = $reg['id'];
				$this -> slug_cache[$reg['keyword']]['type'] = $reg['type'];
				
				// TODO: almacenar el array inverso slug -> keyword+locale?
			}
			//var_dump($this->slug_cache);
		}

		return $this -> slug_cache;
	}

	/**
	 * Recupera el slug absoluto asociado a una clave de contenido y un locale.
	 * Por defecto locale de sesión.
	 */
	public function getSlugAbsolutoByKeyLocale($key, $locale = null) {
		
		$key = trim($key);
		
		$locale = isset($locale) ? $locale : $this -> container -> get('request') -> getLocale();

		$key_slug_cache_array = $this -> getSlugCacheArray();

		if (!is_array($key_slug_cache_array)) {
			return null;
		}
		
		if (array_key_exists($key, $key_slug_cache_array) && array_key_exists($locale, $key_slug_cache_array[$key]['locales'])) {
			return $key_slug_cache_array[$key]['locales'][$locale];
		} else {
			//trigger_error("Key $key doesn't exists");
			return false;
		}
	}
	
	/**
	 * Slug absoluto del contenido actual. Busca en caché de slugs por medio 
	 * del current entity summary
	 */
	public function getSlugAbsolutoCurrentEntityByLocale($locale = null) {
		
		$entidad_summary = $this->getCurrentEntitySummaryArray();// aquí debería utilizar el array almacenado en objeto al iniciar
		
		if (!isset($entidad_summary)) {
			return null;
		}
		
		return $this -> getSlugAbsolutoByKeyLocale($entidad_summary['keyword'], $locale);
	}
	
	
	
	public function findEntitySummaryArrayByKeyword($keyword){
		$key_slug_cache_array = $this -> getSlugCacheArray();
		
		if(!array_key_exists($keyword, $key_slug_cache_array)){
			return null;
		}
		
		return $key_slug_cache_array[$keyword];
	}

	/**
	 * Array con todos los campos de contenido y contenido i18n almacenados 
	 * en la chaché de slugs absolutos para un slug absoluto determinado.
	 */
	public function findEntitySummaryArrayBySlugAbs($slugabsolto){
		
		$key_slug_cache_array = $this -> getSlugCacheArray();

		$slugabsolto = trim($slugabsolto);
				
		foreach ($key_slug_cache_array as $keyword => $keyword_data) {
			foreach ($keyword_data['locales'] as $locale => $key_locale_slug){
				
				$key_locale_slug = utf8_encode($key_locale_slug);
				if (strcmp($key_locale_slug, $slugabsolto) == 0){
					return array(
						'id'		=>$keyword_data['id'],
						'keyword'	=>$keyword, 
						'type'		=>$keyword_data['type'],
						'locale' 	=>$locale,
						 );
				}
			}
		}
		
		//trigger_error("no se ha encontrado $slugabsolto");
		
	}

	/**
	 * Check weather each slug gets en entity. Throws exception if not.
	 */
	private function checkSlugEntityMatch() {
		$entities = $this -> getEntities();

		if (count($entities) != count($this -> slugchain)) {

			// $slugs_found = array();
			// foreach ($entities as $ent) {
			// array_push($slugs_found, $ent -> getSlug());
			// }
			//
			$slugs_found = array_keys($entities);

			$unknow_slugs = "";
			foreach ($this->slugchain as $slug) {
				if (!in_array($slug, $slugs_found)) {
					$unknow_slugs .= " '$slug'";
				}
			}
			throw new \Exception('One or more unknown slugs: ' . $unknow_slugs . '. ' . count($entities) . ' entities found', 1);
		}

		return true;
	}

	private function checkParenthood() {
		//$entities = $this -> getEntities();
		//var_dump($this->entitychain);
		// se recorre array al revés
		for ($i = 0; $i < count($this -> slugchain) - 1; $i++) {
			//for ($i=count($this->slugchain)-1; $i >= 1 ; $i--) {
			// checks entity i is parent of entity i+1 (foo/bar --> foo is bar's parent)

			//$maybe_parent = $this->entitychain[$i-1];
			//$maybe_child = $this->entitychain[$i];

			$maybe_parent = $this -> entitychain[$this -> slugchain[$i]];
			$maybe_child = $this -> entitychain[$this -> slugchain[$i + 1]];

			if ($maybe_child -> getParent() != $maybe_parent) {

				$slug_no_parent = $maybe_parent -> getSlug();
				$slug_no_child = $maybe_child -> getSlug();
				throw new \Exception($slug_no_parent . ' is not parent of ' . $slug_no_child, 1);
			}
		}

		return true;
	}

	private function checkSamelocaleSlugs() {
		$entities = $this -> getEntities();

		$first_locale = $this -> entitychain[$this -> slugchain[0]] -> getLocale();

		for ($i = 1; $i < count($this -> entitychain); $i++) {

			if ($this -> entitychain[$this -> slugchain[$i]] -> getLocale() != $first_locale) {
				$slug_no_ok = $this -> entitychain[$i] -> getSlug();
				throw new \Exception("Slug locale mismatch: slug '" . $slug_no_ok . "' not in locale of its parent: '" . $first_locale . "'", 1);
			}
		}

		return true;
	}

	public function getSlugSeparator() {
		return $this -> slug_separator;
	}

}
