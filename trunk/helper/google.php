<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Formate Google services to PHP for Kohana
 *
 * $Id$
 *
 * @package    helper
 * @author     icyleaf
 * @copyright http://www.opensource.org/licenses/bsd-license.php
 * @version 0.1
 *
 * About:
 *
 * By icyleaf (http://icyleaf.com)
 * March 27, 2009
 * Contact me: icyleaf.cn@gmail.com
 *
 *
 * Usage:
 *
 * 1. Google Translate
 *
 * 1). Coutries code ab. list:
 *		'AFRIKAANS' : 'af',
 * 		'ALBANIAN' : 'sq',
 * 		'AMHARIC' : 'am',
 * 		'ARABIC' : 'ar',
 * 		'ARMENIAN' : 'hy',
 * 		'AZERBAIJANI' : 'az',
 * 		'BASQUE' : 'eu',
 * 		'BELARUSIAN' : 'be',
 * 		'BENGALI' : 'bn',
 *		'BIHARI' : 'bh',
 *		'BULGARIAN' : 'bg',
 *		'BURMESE' : 'my',
 *		'CATALAN' : 'ca',
 * 		'CHEROKEE' : 'chr',
 * 		'CHINESE' : 'zh',
 * 		'CHINESE_SIMPLIFIED' : 'zh-CN',
 * 		'CHINESE_TRADITIONAL' : 'zh-TW',
 *		'CROATIAN' : 'hr',
 * 		'CZECH' : 'cs',
 * 		'DANISH' : 'da',
 * 		'DHIVEHI' : 'dv',
 * 		'DUTCH': 'nl',  
 * 		'ENGLISH' : 'en',
 * 		'ESPERANTO' : 'eo',
 * 		'ESTONIAN' : 'et',
 * 		'FILIPINO' : 'tl',
 * 		'FINNISH' : 'fi',
 * 		'FRENCH' : 'fr',
 * 		'GALICIAN' : 'gl',
 * 		'GEORGIAN' : 'ka',
 * 		'GERMAN' : 'de',
 * 		'GREEK' : 'el',
 * 		'GUARANI' : 'gn',
 * 		'GUJARATI' : 'gu',
 * 		'HEBREW' : 'iw',
 * 		'HINDI' : 'hi',
 * 		'HUNGARIAN' : 'hu',
 * 		'ICELANDIC' : 'is',
 * 		'INDONESIAN' : 'id',
 * 		'INUKTITUT' : 'iu',
 * 		'ITALIAN' : 'it',
 * 		'JAPANESE' : 'ja',
 * 		'KANNADA' : 'kn',
 * 		'KAZAKH' : 'kk',
 * 		'KHMER' : 'km',
 * 		'KOREAN' : 'ko',
 * 		'KURDISH': 'ku',
 * 		'KYRGYZ': 'ky',
 * 		'LAOTHIAN': 'lo',
 * 		'LATVIAN' : 'lv',
 * 		'LITHUANIAN' : 'lt',
 * 		'MACEDONIAN' : 'mk',
 * 		'MALAY' : 'ms',
 * 		'MALAYALAM' : 'ml',
 * 		'MALTESE' : 'mt',
 * 		'MARATHI' : 'mr',
 * 		'MONGOLIAN' : 'mn',
 * 		'NEPALI' : 'ne',
 * 		'NORWEGIAN' : 'no',
 * 		'ORIYA' : 'or',
 * 		'PASHTO' : 'ps',
 * 		'PERSIAN' : 'fa',
 * 		'POLISH' : 'pl',
 * 		'PORTUGUESE' : 'pt-PT',
 * 		'PUNJABI' : 'pa',
 * 		'ROMANIAN' : 'ro',
 * 		'RUSSIAN' : 'ru',
 * 		'SANSKRIT' : 'sa',
 * 		'SERBIAN' : 'sr',
 * 		'SINDHI' : 'sd',
 * 		'SINHALESE' : 'si',
 * 		'SLOVAK' : 'sk',
 * 		'SLOVENIAN' : 'sl',
 * 		'SPANISH' : 'es',
 * 		'SWAHILI' : 'sw',
 * 		'SWEDISH' : 'sv',
 * 		'TAJIK' : 'tg',
 * 		'TAMIL' : 'ta',
 * 		'TAGALOG' : 'tl',
 * 		'TELUGU' : 'te',
 * 		'THAI' : 'th',
 * 		'TIBETAN' : 'bo',
 * 		'TURKISH' : 'tr',
 * 		'UKRAINIAN' : 'uk',
 * 		'URDU' : 'ur',
 * 		'UZBEK' : 'uz',
 * 		'UIGHUR' : 'ug',
 * 		'VIETNAMESE' : 'vi',
 * 		'UNKNOWN' : ''
 *
 * 2). Sample code
 *		echo google::translate('text', 'en|de');
 *
 *
 * Full class signature:
 *
 * public static translate( $text, $lang='zh-CN|en' ) 
 * private static function getContent( $url ) 
 *
 */
class google_Core {
	
	/**
	 * Google translate
	 * @param string translate text
	 * @param string source language and target language with '|' emblem
	 * @param mix
	 */
	public static function translate( $text, $lang='zh-CN|en' ) 
	{
		if ( empty($text) || !preg_match('/[\w+|-]+\|[\w+|-]+/', $lang) )
			return NULL;
			
		$out = '';
		$google_translator_url = 'http://google.com/translate_t?langpair='.$lang.'&text='.urlencode($text).'&ie=UTF8';
		$html = self::getContent($google_translator_url);
		
		preg_match('/<div id=result_box dir="ltr">(.*?)<\/div>/', $html, $out);
	
		return utf8_encode($out[1]);
	}
 
	private static function getContent( $url ) 
	{
		$html = '';
		if( !empty($url) ) 
		{
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 15);
			$html = curl_exec($ch);
			if(curl_errno($ch)) {
				$html = '';
			}
			curl_close ($ch);
		}
		return $html;
	}
}