<?php

use App\Translate;

/**
 * Alias de confort pour la fonction Translate::translate()
 *
 * @see Translate::translate
 */
function tr($word, $return = false, $params = array(), $domain = null) {
    return Translate::translate($word, $return, $params, $domain);
}
