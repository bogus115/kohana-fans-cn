<?php defined('SYSPATH') OR die('No direct access allowed.');

class url extends url_Core {
 
	public static function friendly( $string )
	{
		if ( preg_match('/[\x{2E80}-\x{9FFF}]+/u', $string) )
			$string = google::translate($string);
			
		$string = preg_replace('`\[.*\]`U', '', $string);
		$string = preg_replace('`&(amp;)?#?[a-z0-9]+;`i', '-', $string);
		$string = htmlentities($string, ENT_COMPAT, 'utf-8');
		$string = preg_replace("`&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);`i", "\\1", $string );
		$string = preg_replace(array('`[^a-z0-9]`i', '`[-]+`'), '-', $string);
		return strtolower(trim($string, '-'));
	}
}
