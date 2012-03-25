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


//----------------------------------------------------------------------
// Definizioni di costanti che non dovrebbe essere necessario modificare
//----------------------------------------------------------------------

// Numero di versione del software e3g (unico sia per Equogest che per GestiGAS)
define('E3G_VERSIONE', '0.21.1');

// Numero di versione atteso per il database
define('E3G_DB_COND_VERSIONE_ATTESA', '0012');
define('E3G_DB_MULTI_VERSIONE_ATTESA', '0021');

// Configurazione icone
define('P4A_ICONS_NAME', '' );
define('P4A_ICONS_PATH', E3G_TEMPLATE_DIR . 'icons' );
define('P4A_ICONS_DIR', dirname(__FILE__) . P4A_ICONS_PATH);
define('P4A_ICONS_URL', 'http://' . $_SERVER['SERVER_NAME'] . "/" . P4A_ICONS_PATH);

// Estensione degli errori di p4a
define('P4A_EXTENDED_ERRORS', STATO_DEBUG);

//define('P4A_DSN', 'mysql://USERNAME:PASSWORD@HOSTNAME/DBNAME');  <-- Modello
define('P4A_DSN', 'mysql://' . DB_USERNAME . ':' . DB_PASSWORD . '@' . DB_HOSTNAME . '/' . DB_NAME);

// Dimensione elementi grafici
define('E3G_MAIN_FRAME_WIDTH',      965 );  // Larghezza frame principale (è la massima per non sforare su schermi larghi 1024)
define('E3G_FIELDSET_SEARCH_WIDTH', E3G_MAIN_FRAME_WIDTH - 10 );  // Pannello filtro iniziale
define('E3G_TABLE_WIDTH',           E3G_MAIN_FRAME_WIDTH );       // Tabella datasource principale
define('E3G_FIELDSET_DATI_WIDTH',   E3G_FIELDSET_SEARCH_WIDTH );  // Fieldset dati vari sotto la tabella
define('E3G_TAB_PANE_WIDTH',        E3G_TABLE_WIDTH );        

// Dimensionamenti usati soprattutto in cassa_gg_globale e singolo
define('E3G_TABLE_IN_TAB_PANE_WIDTH', E3G_MAIN_FRAME_WIDTH - 40 );  // Tabella in un p4a_tab_pane a tutta larghezza
define('E3G_LABEL_IN_TAB_PANE_WIDTH', 100 );  // Label in spalla in un p4a_tab_pane con tabella
define('E3G_FIELD_IN_TAB_PANE_WIDTH',  30 );  // Field in spalla in un p4a_tab_pane con tabella

// Tabella in un p4a_tab_pane (con spalla) 
define('E3G_NARROW_TABLE_IN_TAB_PANE_WIDTH', E3G_TABLE_IN_TAB_PANE_WIDTH - E3G_LABEL_IN_TAB_PANE_WIDTH - E3G_FIELD_IN_TAB_PANE_WIDTH - 40 );  

// Disabilta la creazione di tabelle _seq
define('P4A_AUTO_DB_SEQUENCES', false);

// Inclusione file di lingua
// temporaneamente commentato perchè in fase di implementazione 
//require_once( dirname(__FILE__) . '/language/' . P4A_LOCALE . '.php' );


?>