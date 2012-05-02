<?php
namespace Jgzz\CmsBundle\Util;

class Util
{
	static public function getSlug($cadena, $separador = '-')
	{
		// Código copiado de http://cubiq.org/the-perfect-php-clean-url-generator
		$slug = iconv('UTF-8', 'ASCII//TRANSLIT', $cadena);
		$slug = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $slug);
		$slug = strtolower(trim($slug, $separador));
		$slug = preg_replace("/[\/_|+ -]+/", $separador, $slug);
		return $slug;
	}
	
		/**
	 * función para generar una cadena apta para url amigable
	 * http://www.gongoraescobar.com/personal/php-mysql/funcion-para-crear-slugs-url-amigables/
	 */
	// public function slugize($name,$utf=true){
// 
		// $sname = trim($name); //remover espacios vacios
// 		
		// $sname = strtolower(preg_replace('/\s+/','-',$sname)); // pasamos todo a minusculas y cambiamos todos los espacios por -
// 		
		// if($utf){ // se el texto no viene en formato utf8 se le manda a codificar como tal.
		// $sname = utf8_decode($sname);
		// }
		// // Lista de caracteres latinos y sus correspondientes para slug
		// $table = array(
		// 'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'C'=>'C', 'c'=>'c', 'C'=>'C', 'c'=>'c',
		// 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
		// 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
		// 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'S',
		// 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
		// 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
		// 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
		// 'ÿ'=>'y', 'R'=>'R', 'r'=>'r', ','=>''
		// );
// 		
		// $sname = strtr($sname, $table); // remplazamos los acentos, etc, por su correspondientes
		// $sname = preg_replace('/[^A-Za-z0-9-]+/', '', $sname); // eliminamos cualquier caracter que no sea de la a-z o 0 al 9 o -
// 		
		// return $sname;
	// }
}
