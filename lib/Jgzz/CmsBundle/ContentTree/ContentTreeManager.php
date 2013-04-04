<?php
namespace Jgzz\CmsBundle\ContentTree;

/**
 * Gestiona el acceso a arrays de referencia a contenids anidados. Útil para formar
 * menús jerárquicos de seccione y subsecciones.
 */
class ContentTreeManager {
	
	private $container;
	
	public function __construct($container){
		$this->container = $container;
	}
	
		
	/**
	 * Recupera registros de contenidos dependientes del $root_parent que se pase (id del contenido base)
	 * Organiza en un arbol de nodos los contenidos y sus dependencias
	 * 
	 * TODO: implementar en caché
	 */
	public function getArbolContenido($tipos, $root_parent, $include_root, $n_niveles_max = null) {
		$locale = $this -> container -> get('request') -> getLocale();

		$in_str = "'" . join("','", $tipos) . "'";
		
		$include_root = $include_root ? $include_root : false; 
		
		$tabla_content = "Content";
		$tabla_content_i18n ="ContentI18n";

		$stmt = $this -> container -> get('doctrine') -> getEntityManager() -> getConnection() -> prepare(sprintf("
      SELECT
      $tabla_content.id,
      $tabla_content.keyword,
      $tabla_content.parent_content_id,
      $tabla_content_i18n.title
     
	  FROM
	  $tabla_content INNER JOIN $tabla_content_i18n ON $tabla_content_i18n.parent_id = $tabla_content.id
	  
	  WHERE
	  $tabla_content.type IN (%s)
	  AND $tabla_content.visible = 1
	  AND $tabla_content_i18n.locale = '%s'
	  
	  ORDER BY $tabla_content.id ASC
	  ", $in_str, $locale));
		$stmt -> execute();

		$regs = $stmt -> fetchAll(\PDO::FETCH_ASSOC);
		
		//print_r($regs);
		

		if (empty($regs)) {
			return array();
		}
		
		$arbol = array();
		
		$found_root = false;
		
		$ids_nivel_superior = array($root_parent);
		
		$nivel = 0;
		
		// mientras queden registros por ubicar y no nos salgamos del máximo de niveles
		while(count($regs) > 0 && (!isset($n_niveles_max) xor (isset($n_niveles_max) && $nivel < $n_niveles_max))){
			
			$nivel += 1;
			
			/*
			 * búsqueda del root
			 */
			if($include_root && !$found_root){
				foreach($regs as $reg){
					if ($reg['id'] == $root_parent){
						$reg['nivel'] = $nivel;
						array_push($arbol, $reg);
						
						$found_root = true;
						$ids_nivel_superior = array($reg['id']);
						break;
					}
				}
				if (!$found_root){
					throw new \Exception("No se ha encontrado el elemento raiz", 1);
				}
				continue;
			}
			
			

			$ids_nivel_actual_match = array();
		
			foreach ($regs as $key => $reg){

				if(in_array($reg['parent_content_id'], $ids_nivel_superior) or !isset($reg['parent_content_id'])){
					// el nodo padre está entre los nodos descubiertos en la anterior iteración
					
					$ubicado = false; 
					
					// nivel en que se ubica el registro
					$reg['nivel'] = $nivel;

					if(!$include_root && $reg['parent_content_id'] == $root_parent){
						// añadimos a la raíz del arbol
						array_push($arbol, $reg);
						$ubicado = true;
					} else {
						// buscamos y asignamos a rama padre dentro de las ramas existentes del arbol
						// TODO: optimizar pasando un indicador del nivel en el que se espera encontrar el nodo padre
						$padre = $this->asignaRecursivo('id', $reg['parent_content_id'], $arbol, $reg);
						$ubicado = $padre != false;
					}
					
					if (!$ubicado){
						//throw new \Exception("No se ha encontrado el registro padre para contenido ".$reg['id']." entre los nodos 
						//incluidos en la lista. Puede que este nodo de contenido tenga mal configurado el nodo de contenido al que pertenece", 1);
						/*
						 * No se debe lanzar excepción ya que podemos estar buscando una subrama de un tipo de contenido
						 */
					}
					
					// en la siguiente vuelta del while habrá que buscar estos ids
					//  ya que estaremos en el siguiente nivel de ramificación
					array_push($ids_nivel_actual_match, $reg['id']);
					
					
					// eliminamos el registro que ya ha sido ubicado
					unset($regs[$key]);

				}
			}
			// actualiza la lista de ids a comprobar para la siguiente vuelta
			$ids_nivel_superior = $ids_nivel_actual_match;
		}

		return $arbol;
		

	}
	
	/**
	 * Recorre recursivamente el array asociativo que se pasa, buscando una coincidencia en la clave
	 * $key_to_match. Si el array recorrido tiene un subarray en la clave $key_subarray, se transmite la búsqueda
	 * a este subarray (y así recursívamente).
	 * Una vez encontrada la coincidencia, se añade $rama_nueva al mismo
	 */
	private function asignaRecursivo($key_to_match, $value_to_match, &$array, $rama_nueva, $key_subarray = 'ramas'){
		foreach($array as &$a){
			
			if($a[$key_to_match] == $value_to_match){
				// nodo padre encontrado
				if (!array_key_exists($key_subarray, $a)){
					// creación de subarray de nodos hijos
					$a[$key_subarray] = array();
				}
				// asignación de rama a padre
				array_push($a[$key_subarray], $rama_nueva);
				
				return $a;
				
			} elseif(array_key_exists($key_subarray, $a) && is_array($a[$key_subarray])) {
				// no es el nodo padre de $rama_nueva, buscamos en hijas de $a 
				if ($match_elem = $this->asignaRecursivo($key_to_match, $value_to_match, $a[$key_subarray], $rama_nueva, $key_subarray)){
					// el padre de $rama_nueva es una de las ramas de $a 
					return $match_elem;
				}
			}
		}
		return false;
	}
	
	
	
	
//primera aproximción al problema de recuperar arbol de contenidos. optado por getArbolContenido
	// public function getGruposContenido($tipopadre, $tipohijo) {
		// $locale = $this -> container -> get('request') -> getLocale();
// 
		// $stmt = $this -> container -> get('doctrine') -> getEntityManager() -> getConnection() -> prepare(sprintf("
      // SELECT
      // content.id AS nivel1_id,
      // content.keyword AS nivel1_keyword,
      // contenti18n.title AS nivel1_title,
//       
	  // content_2.id AS nivel2_id,
      // content_2.keyword AS nivel2_keyword,
      // contenti18n_2.title AS nivel2_title
// 
	  // FROM
	  // (content INNER JOIN contenti18n 
	  // ON contenti18n.parent_id = content.id)
	  // LEFT JOIN
	  // (content AS content_2 INNER JOIN contenti18n AS contenti18n_2 
	  // ON contenti18n_2.parent_id = content_2.id)
// 	   
	  // ON content_2.parent_content_id = content.id
// 	   
// 	  
	  // WHERE
	  // content.type = '%s' AND content_2.type = '%s'
	  // AND content.visible = 1 AND content_2.visible = 1
	  // AND contenti18n.locale = '%s' AND contenti18n_2.locale = '%s'
// 	  
	  // ORDER BY content_2.id, content.id ASC 
// 	  
	  // ", $tipopadre, $tipohijo, $locale, $locale));
		// $stmt -> execute();
// 
		// $regs = $stmt -> fetchAll(\PDO::FETCH_ASSOC);
// 
		// if (empty($regs)) {
			// return array();
		// }
// 
		// $arr_grupos = array();
		// //$ini_cat = $regs['categoria_id'];
		// $ultimo_grupo = '';
// 
		// foreach ($regs as $reg) {
			// $cat_id = $reg['categoria_id'];
// 
			// // nueva categoría. se esperan resultados ordenados por categoría
			// if ($cat_id != $ultimo_grupo) {
				// $arr_grupos[$cat_id]['keyword'] = $reg['categoria_keyword'];
				// $arr_grupos[$cat_id]['title'] = $reg['categoria_title'];
				// $arr_grupos[$cat_id]['productos'] = array();
			// }
// 
			// // datos del producto
			// array_push($arr_grupos[$cat_id]['productos'], array('producto_id' => $reg['producto_id'], 'producto_keyword' => $reg['producto_keyword'], 'producto_title' => $reg['producto_title'], ));
// 
			// $ultimo_grupo = $cat_id;
		// }
// 
		// return $arr_grupos;
// 
	// }
	
	
}
