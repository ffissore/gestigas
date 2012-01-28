<?php
/**
 * Progetto e3g - Equogest/GestiGAS
 *   Software gestionali per l'economia solidale
 *   <http://www.progettoe3g.org>
 *
 * Copyright (C) 2003-2012
 *   Andrea Piazza <http://www.andreapiazza.it>
 *   Marco Munari  <http://www.marcomunari.it>
 *
 * @package Progetto e3g - Equogest/GestiGAS
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * 
 * Questo  programma e' software  libero; e' lecito redistribuirlo  o
 * modificarlo secondo i termini  della Licenza Pubblica Generica GNU
 * come  pubblicata dalla Free  Software  Foundation; o la versione 2
 * della licenza o (a propria scelta) una versione successiva.
 * 
 * Questo programma e' distribuito nella  speranza che sia  utile, ma
 * SENZA  ALCUNA GARANZIA;  senza  neppure la  garanzia implicita  di
 * NEGOZIABILITA' o di APPLICABILITA' PER  UN PARTICOLARE  SCOPO.  Si
 * veda la Licenza Pubblica Generica GNU per avere maggiori dettagli.
 * 
 * Questo  programma deve  essere  distribuito assieme  ad una  copia
 * della Licenza Pubblica Generica GNU.
*/


// -----------------------------------------------------------------------------
// File richiamato solo da servizi esterni per attivare le funzioni periodiche
// -----------------------------------------------------------------------------

require_once( dirname(__FILE__) . '/config.php' );
require_once( P4A_ROOT_DIR . '/p4a.php' );
require_once( dirname(__FILE__) . '/libraries/e3g_cron.php' );

$p4a =& p4a::singleton();
$db =& p4a_db::singleton();

e3g_verifica_connessione_db();

$qu_aziende = $db->getAll( "SELECT prefix FROM _aziende" );

if ( $qu_aziende ) {
    foreach ( $qu_aziende as $qu_azienda ) {
        $p4a->e3g_prefix = $qu_azienda["prefix"];

        e3g_update_var_azienda();
        e3g_cron();
    }
}


?>
