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


//------------------------------------------------------------------------------
// Compilare con i dati del GAS/Bottega e dell'installazione e3g
//   quando è richiesta una directory, mettere lo / finale
//------------------------------------------------------------------------------

// Tipo di gestione: (E)quogest oppure (G)estiGAS
define('E3G_TIPO_GESTIONE', 'G');

define('E3G_PREFIX', 'prefix_');
define('E3G_NOME_BREVE', 'Nome-GAS');
define('E3G_NOME_LUNGO', 'Nome-GAS :: Gruppo di Acquisto Solidale (Provincia)');
define('E3G_EMAIL', 'nome-gas@dominio.org');  // Si può anche lasciare vuoto

// Sito (url) in cui è presente l'installazione condivisa di e3g
define('E3G_ROOT_URL', 'http://www.gestigas.org/e3g/');  // Oppure 'http://www.equogest.org/e3g/'

// Idem come precedente, ma espresso come directory anziché url
define('E3G_ROOT_DIR', dirname(__FILE__) . '/../e3g/');

// Visualizza o meno la scritta relativa all'hosting by lillinet
define('LILLINET_HOSTING', true);

?>
