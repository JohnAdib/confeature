<?php
/**
 * Localisation
 */

class L10N {
	
	/**
	 * Associative array of localized texts
	 * @var array
	 */
	public static $translations = array();
	
	
	/**
	 * Loads a language from the cache or from the locales files
	 *
	 * @param string $languages	Language code (e.g. en_US or fr_FR)
	 */
	public static function load($language){
		if(!preg_match('#^([a-z]{2})(?:_[A-Z]{2})?$#', $language, $match))
			throw new Exception('Wrong language format');
		$language_base = $match[0];
		
		// Locale of PHP
		setlocale(LC_ALL, $language.'.UTF-8', $language_base.'.UTF-8', 'en_EN.UTF-8');
		
		// Retrieving the translations
		$last_modif = max(filemtime(CF_DIR.'locales/'.$language), filemtime(APP_DIR.'locales/'.$language));
		self::$translations = Cache::read('translations_'.$last_modif);
		if(self::$translations != false)
			return;
		
		// If the translations cache doesn't exist, we create it
		$vars = '';
		try {
			$vars .= File::read(CF_DIR.'locales/'.$language);
		}catch(Exception $e){
			throw new Exception('The L10N file "'.$language.'" for Confeature is not found');
		}
		$vars .= "\n\n";
		try {
			$vars .= File::read(APP_DIR.'locales/'.$language);
		}catch(Exception $e){
			throw new Exception('The L10N file "'.$language.'" for the App is not found');
		}
		
		// Extraction of the variables and storage in the class
		self::$translations = self::parse($vars);
		
		Cache::write('translations_'.$last_modif, self::$translations, 3600*24);
		echo 'write cache';
	}
	
	
	/**
	 * Analyze a configuration text and extract an associative array of values
	 *
	 * Example of configuration text :
	 *			# Comment here with a sharp
	 *			FIRST_VAR
	 *				Hello {username}
	 *			SECOND_VAR	# Comment after a variable name
	 *				Content of
	 *				the var
	 *				on several lines
	 *			AN_ARRAY[]
	 *				First value
	 *			AN_ARRAY[]
	 *				Second value
	 *			AN_ASSOCIATIVE_ARRAY[VAR1]
	 *				FOO
	 *			AN_ASSOCIATIVE_ARRAY[VAR2]
	 *				BAR
	 *
	 * @param string $txt	Configuration text
	 * @return array	Associative array of values
	 */
	public static function parse($txt){
		// Delete comments (everywhere but in texts)
		$txt = preg_replace('/((?:^|\n)[^ \t][^\n]*)#.*/', '$1', $txt);
		
		// Extract variables
		preg_match_all('/
			(?<=^|\n)			# Begin of line
			([a-z_][a-z0-9_]*)	# Name of variable
			[ \t]*
			(\[[ \t]*([a-z_][a-z0-9_]*)?[ \t]*\])?	# Possibly an array
			[ \t]*\n
			([ \t]+)		# First indentation
			(.*)			# First line of text
			((?:\n\4.*)*)	# Possibly other lines
		/xi', $txt, $matches, PREG_SET_ORDER);
		
		// Map variables into an array
		$vars = array();
		foreach($matches as $match){
			// Content of the variable
			$text = $match[5].str_replace("\n".$match[4], "\n", $match[6]);
			$text = trim($text);
			// If it isn't an array
			if($match[2] == ''){
				$vars[$match[1]] = $text;
			// If it's an array
			}else{
				if(!isset($vars[$match[1]]))
					$vars[$match[1]] = array();
				if($match[3] == '')
					$vars[$match[1]][] = $text;
				else
					$vars[$match[1]][$match[3]] = $text;
			}
		}
		return $vars;
	}
	
}

/**
 * Gettext function
 *
 * @param string $var	Name of the variable
 * @param array $params	Associative array of strings to be replaced in the text
 * @return string	Value of the variable
 */
function __($var, $params=null){
	if(isset(L10N::$translations[$var])){
		if(isset($params) && is_array($params)){
			$text = L10N::$translations[$var];
			foreach($params as $key => $value)
				$text = str_replace('{'.trim($key, '{}').'}', $value, $text);
			return $text;
		}else{
			return L10N::$translations[$var];
		}
	}else{
		return $var;
	}
}
