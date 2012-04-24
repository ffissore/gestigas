<?php
/**
 * Progetto e3g - Equogest/GestiGAS
 *   Software gestionali per l'economia solidale
 *   <http://www.progettoe3g.org>
 *
 * Copyright (C) 2003-2009
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


//-------------------------------------------------------------------
// Compilare con i dati specifici dell'installazione
// e rinominare questo file in "config.php"
// Quando è richiesta una directory, non dimenticare lo / finale
//-------------------------------------------------------------------

// Parametri connessione al database
define('DB_HOSTNAME', 'localhost');
define('DB_NAME',     'nome_database');
define('DB_USERNAME', 'username');
define('DB_PASSWORD', 'password');

// Per i messaggi automatici di posta elettronica
define('MAIL_FROM',       'gestigas@dominio.org');           // Indirizzo del mittente
define('MAIL_FROM_NAME',  'e3g/GestiGAS');                   // Nome del mittente
define('MAIL_REPLY',      'non-rispondere@dominio.org');     // Indirizzo per le risposte
define('MAIL_REPLY_NAME', 'Non rispondere a questa e-mail'); // Nome indirizzo risposte

define('MAIL_SMTP', false);              // Invio tramite SMTP
define('MAIL_SMTP_SECURE', 'ssl');       // Prefisso per il server
define('MAIL_HOST', "smtp.dominio.org"); // Server SMTP (anche più di uno separati da ;)
define('MAIL_PORT', 25);                 // Porta del server SMTP (anche in coda all'host separata da :)
define('MAIL_SMTP_AUTH', false);         // Autenticazione SMTP 
define('MAIL_USERNAME', 'username');     // SMTP username  
define('MAIL_PASSWORD', 'password');     // SMTP password

// Template grafico
define('E3G_TEMPLATE_DIR', 'templates/default/');

// Manuali on-line (commentare per forzare l'uso del manuale PDF in /docs)
define('GG_URL_MANUALE', 'http://wiki.progettoe3g.org/index.php?title=Manuale_GestiGAS&printable=yes');
define('EG_URL_MANUALE', 'http://wiki.progettoe3g.org/index.php?title=Manuale_Equogest&printable=yes');

// Tipo di gestione: (E)quogest oppure (G)estiGAS
define('E3G_TIPO_GESTIONE', 'G');

// Attivazione o meno dello stato di debug (con maggiore dettaglio degli errori)
define('STATO_DEBUG', false);

// Posizione dell'installazione di p4a rispetto ad e3g	
define('P4A_ROOT_DIR', dirname(__FILE__) . '/../p4a/');

// Definizione di nazione e lingua (locale di P4A)
define('P4A_LOCALE', 'it_IT');

// Eventuale attivazione delle funzioni periodiche tramite login
define('E3G_LOGIN_CRON', false);


// Costante necessaria su server con open_basedir
//define('P4A_COMPILE_DIR',dirname(__FILE__).'/p4a_tmp');


// Da NON modificare
require_once( dirname(__FILE__) . '/config_const.php' );

?>
