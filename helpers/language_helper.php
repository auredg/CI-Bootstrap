<?php

/**
 * Cela va permettre d'afficher le libelle de traduction si elle n'existe pas.
 * Plus facile pour voir quels traductions sont manquantes
 * @param string $line Libelle de chaine a traduire
 * @return string Chaine traduite 
 */
function lang($line){
        $CI =& get_instance();
        $trad = $CI->lang->line($line);
        
        return ($trad != '') ? $trad : $line;
}
