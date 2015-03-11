<?php

require_once('config.php');

/** Singleton de débuggage utilisé pour logger des messages dans un fichier. */
class Logger {

    private static $previousTime;
    private static $instance = null;

    private function __construct() {
        self::$previousTime = microtime();
    }

    public static function add($txt) {

        if(is_array($txt) || is_object($txt)) {
            $txt = "\n" . print_r($txt, true);
        }

        $currentTime = microtime();
        $operationTime = $currentTime - self::$previousTime;
        self::$previousTime = $currentTime;

        $line = date("[j/m/y H:i:s]") . " (" . round($operationTime * 100) . " ms) - $txt \r\n";

        self::write($line);
    }

    public static function nl($line_count = 1) {

        $nl = "";

        for($i = 0; $i < $line_count; $i++) {
            $nl .= "\n";
        }

        self::write($nl);
    }

    private static function write($line) {

        if(is_null(self::$instance)) {
            self::$instance = new Logger();
        }

        if(ADD_LOG) {
            $filename = dirname(__FILE__).'/debug.log';

            if(!file_exists($filename)) {
                touch($filename);
            }

            $handle = fopen($filename, "a+");
            fwrite($handle, $line);
            fclose($handle);
        }
    }
}

/** Retourne la chaine passée en paramètre tronquée ($maxLength + 3) */
function cutString($string, $maxLength) {

	$string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');

    if(strlen($string) > $maxLength) {
        $string = substr($string, 0, $maxLength) . '...';
    }
    return $string;
}

/** Elipse une chaine de texte à afficher sur UNE SEULE ligne
Il faut appliquer text-overflow: ellipsis et overflow:hidden sur le conteneur du texte */
function ellipseLine($line) {
	$line =  str_replace(' ', '&nbsp;', $line);
	return str_replace('-', '&#8209;',$line);
}

 /**
 * Passe une date du format US (YYYY-MM-DD) au format FR (JJ/MM/AAAA)
 *
 * @param type $date Date à convertir : peut être un timestamp, le séparateur n'importe pas
 * @param type $separateur Spéarateur à utiliser dans la date convertie
 */
function date_us_to_fr($date, $separateur = "/") {

	//Fonctionne avec une $date (YYYY-MM-DD) de 10 caractères ou plus (peut donc être un timestamp)
	if(strlen($date) >= 10) {

		//Décomposition de la date
		$annee = substr($date, 0, 4);
		$mois = substr($date, 5, 2);
		$jour = substr($date, 8, 2);

		if(preg_match("#^[0-9]#",$annee) && preg_match("#^[0-9]#",$mois) && preg_match("#^[0-9]#",$jour)
			&& $jour <= 31 && $mois <= 12){
			return ($jour . $separateur . $mois . $separateur . $annee);
		}
		else {
			return false;
		}
	}
	else {
		return false;
	}
}

/**
 * Passe une date du format FR (JJ/MM/AAAA) au format US (YYYY-MM-DD)
 *
 * @param type $date Date à convertir : le séparateur n'importe pas
 * @param type $separateur Spéarateur à utiliser dans la date convertie
 */
function date_fr_to_us($date, $separateur = "-") {


	if(strlen($date) == 10) {
		$annee = substr($date, 6, 10);
		$mois = substr($date, 3, 2);
		$jour = substr($date, 0, 2);

		if(preg_match("#^[0-9]#",$annee) && preg_match("#^[0-9]#",$mois) && preg_match("#^[0-9]#",$jour)
			&& $jour <= 31 && $mois <= 12){
			return ($annee . $separateur . $mois . $separateur . $jour);
		} else {
			return false;
		}
	}
	else {
		return false;
	}
}

// appel de la fonction permettant d'ajouter un fichier de traduction au thème
add_translation_source(dirname(__FILE__) . '/languages');

/**
 * Fonction utilisée pour le débuggage.
 * Formate le résultat différemment en fonction de ce qui est passé en argument (array, object, string, int, boolean, ...)
 * @version 4 (2014-07-09)
 * @author moobee (Dorian MARCHAL)
 */
function pr($o = '', $calledByPh = false) {

    $enableTerminalColor = true;

    $isCli = isCli();
    $isAjax = isAjax();
    $addHtml = !$isCli && (!$isAjax || $calledByPh);
    $addTerminalColor = $enableTerminalColor && $isCli;

    $valueIsObject = is_object($o);
    $valueIsArray = is_array($o);

    $label = '';

    // Récupération du nom de la variable (via la backtrace : un peu crade mais c'est pour du débuggage)
    $bt = debug_backtrace();

    // Si pr est appelée via pa, on récupère le second niveau de la backtrace
    $btIndex = $calledByPh ? 1 : 0;

    $src = file($bt[$btIndex]['file']);
    $line = $src[$bt[$btIndex]['line'] - 1];

    preg_match('#p[rh]\((.*)\)#', $line, $match);
    $label = $match[1];

    $firstChar = substr($label, 0, 1);

    // Si la fonction a été appelée sans paramètre, on passe juste une ligne
    if ($firstChar === false) {
        // Un <br> en html
        if ($addHtml) {
            echo '<br>';
        }
        // Un \n ailleurs
        else {
            echo "\n";
        }
        return;
    }
    // Sinon, si on a juste passé une chaîne à la fonction, on l'affiche simplement
    else if ($firstChar === '\'' ||  $firstChar === '"') {
        // Un <br> en html
        if ($addHtml) {
            echo "$o<br>";
        }
        // Un \n ailleurs
        else {
            echo "$o\n";
        }
        return;
    }

    // Si on affiche le log dans une page web, on le wrappe dans un <pre>
    if ($addHtml) {
        echo '<pre class="debug-pr" style="color:black;font-family:monospace;" >';
        echo "<strong>$label: </strong>";
    }
    // Sinon, si on doit ajouter des couleurs pour le shell
    else if ($addTerminalColor) {
        echo "\033[1;34m$label:\033[0m ";
    }
    else {
        echo "$label: ";
    }

    // Dans le cas des tableaux ou des objets, on fait un print_r
    if ($valueIsObject || $valueIsArray) {

        // Si c'est un tableau, on affiche en plus le nombre d'éléments
        if ($valueIsArray) {
            echo '{' . count($o) . '} ';
        }

        echo print_r($o, true);
    }
    // Dans tous les autres cas, on fait un var_dump
    else {
        var_dump($o);
    }

    if ($addHtml) {
        echo '</pre>';
    }
}

/**
 * Appelle pr en forçant l'HTML
 */
function ph($o = '') {
    pr($o, true);
}

/**
 * Retourne true si le script est exécuté depuis la ligne de commande.
 */
function isCli() {
    return php_sapi_name() === 'cli';
}

/**
 * Retourne true si le script est appelé en AJAX
 * /!\ Cette fonction n'est pas sûre.
 */
function isAjax() {

    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    return $isAjax;
}
