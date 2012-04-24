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

require_once( dirname(__FILE__) . '/e3g_doc_routines.php' );
require_once( dirname(__FILE__) . '/../config.php' );
require_once( dirname(__FILE__) . '/../libraries/phpmailer/class.phpmailer.php' );


//------------------------------------------------------------------------------
// Scrive un file html in /cache con il dettaglio del prodotto selezionato
// per la visualizzazione in una finestra popup
//------------------------------------------------------------------------------
function e3g_prepara_scheda_articolo( $codice_art ) {
	
	$p4a =& p4a::singleton();
	$db =& p4a_db::singleton();
	
	// Query estrazione dati dell'articolo e del produttore
	$query = "SELECT art.codice, art.descrizione, art.prezzoven, art.ingredienti, " . 
	         "       art.pzperconf, art.qtaminordine, art.qtaminperfamiglia, art.um_qta, art.um, " .  
			 "       ana.descrizione desc_produttore, ana.localita, ana.provincia, ana.www, " . 
			 "       um.desc_singolare, um.desc_plurale, um.genere, " .
			 "       cat.descrizione desc_categoria, " .  
			 "       sottocat.descrizione desc_sottocategoria " .  
	         "  FROM " . $p4a->e3g_prefix . "articoli art, " .
	         "       " . $p4a->e3g_prefix . "anagrafiche ana, " .
	         "       " . $p4a->e3g_prefix . "um um, " .
	         "       " . $p4a->e3g_prefix . "tipiarticoli cat, " .
	         "       " . $p4a->e3g_prefix . "catmerceologica sottocat " .
			 " WHERE art.codice = '$codice_art' " .
			 "   AND art.centrale = ana.codice AND ana.tipocfa = 'F' " .
			 "   AND art.um = um.codice " .
			 "   AND art.tipo = cat.codice " .
			 "   AND art.catmerce = sottocat.codice ";
	$result = $db->queryRow( $query );
	
	if ( !($result) ) 
		die( "ERRORE: articolo con codice '$codice_art' non trovato." );
	else {
		// Recupera il modello
		$nome_modello = E3G_TEMPLATE_DIR . 'modello_scheda_articolo.html';
		$file_id = fopen( $nome_modello, "r" )
			or die( "Impossibile leggere il file ($nome_modello)" );
		$content = fread( $file_id, filesize($nome_modello) );
		fclose( $file_id );

		// Sostituisce i valori
		$content = str_replace( "#cod_articolo#",  $result["codice"], $content );
		$content = str_replace( "#desc_articolo#", $result["descrizione"], $content );
		$content = str_replace( "#prezzo#",        number_format($result["prezzoven"], $p4a->e3g_azienda_n_decimali_prezzi), $content ); 
        $content = str_replace( "#ingredienti#",   $result["ingredienti"], $content );

		$content = str_replace( "#categoria#",      $result["desc_categoria"], $content );
		$content = str_replace( "#sottocategoria#", $result["desc_sottocategoria"], $content );

		$content = str_replace( "#genere#",     $result["genere"], $content );
		$content = str_replace( "#confezione#", $result["um_qta"], $content );
		$content = str_replace( "#um_cod#",     $result["um"], $content );
		$content = str_replace( "#um_sing#",     $result["desc_singolare"], $content );
		if ( $result["um_qta"] <> 0 )
			$content = str_replace( "#prezzo_um#", 
				number_format($result["prezzoven"]/$result["um_qta"], $p4a->e3g_azienda_n_decimali_prezzi), $content );
		else
			$content = str_replace( "#prezzo_um#", "-", $content );
		$content = str_replace( "#min_pezzi_ordinabili#",  $result["qtaminperfamiglia"], $content );
		$content = str_replace( "#mult_pezzi_ordinabili#", $result["pzperconf"], $content );
		$content = str_replace( "#pezzi_per_cartone#",     $result["qtaminordine"], $content );
		
		$content = str_replace( "#desc_produttore#", $result["desc_produttore"], $content );
		$content = str_replace( "#localita#",        $result["localita"], $content );
		$content = str_replace( "#provincia#",       $result["provincia"], $content );
        
        if ( strpos( $result["www"], "http://" ) === false ) 
            $content = str_replace( "#www#",         "http://" . $result["www"], $content );
        else
            $content = str_replace( "#www#",         $result["www"], $content );
		
		// Per le immagini/icone
		$content = str_replace( "#template_path#", '../' . E3G_TEMPLATE_DIR, $content );
		
		// Creo il file html della scheda prodotto
		$nome_file_scheda = "cache/scheda_articolo_" . $p4a->e3g_prefix . md5($p4a->e3g_utente_codice) . ".html";
		$file_id = fopen( $nome_file_scheda, "w" )
			or die( "Impossibile scrivere il file ($nome_file_scheda)" );
		fwrite ( $file_id, $content );
		fclose( $file_id );
	}
		
}


//------------------------------------------------------------------------------
// Imposta il titolo dell'applicazione, quello che appare sulla barra del 
// browser, e lo restituisce anche come stringa.
// Aggiorna anche la relativa variabile globale.
//------------------------------------------------------------------------------
function e3g_title() {

	$p4a =& p4a::singleton();
	$e3g_main =& e3g_main::singleton();
	$db =& p4a_db::singleton();

	$p4a->e3g_azienda_rag_soc = $db->queryOne("SELECT rag_soc FROM _aziende WHERE prefix='" . $p4a->e3g_prefix . "'");
   	

	if ( E3G_TIPO_GESTIONE == 'G' ) 
		$e3g_main->setTitle( 'GestiGAS - Gestione ordini "' . 
			( $p4a->e3g_azienda_rag_soc ? $p4a->e3g_azienda_rag_soc : "Gruppi di Acquisto Solidale" ) . '"' );
	else 
		$e3g_main->setTitle( 'Equogest - Gestione Bottega "' . 
			( $p4a->e3g_azienda_rag_soc ? $p4a->e3g_azienda_rag_soc : "Botteghe del Mondo" ) . '"' );
			
	return $e3g_main->getTitle();
}


//------------------------------------------------------------------------------
// Scrive la riga per il footer con nome uente e copyright
//   Richiamata anche da login.php, quando le variabili globali potrebbero non
//   essere impostate
//------------------------------------------------------------------------------
function e3g_scrivi_footer( &$a_this, &$a_object ) {
	
	$p4a =& p4a::singleton();

	$tipo_azienda = ( E3G_TIPO_GESTIONE == 'G' ? 'Gruppi di Acquisto Solidale' : 'Botteghe del Mondo' );

	// Riga 1: nome ditta e dell'utente collegato
	if ( E3G_TIPO_GESTIONE == 'G' ) 
		$riga1 = '<div id="footer_user">Gestione ordini';
	else
		$riga1 = '<div id="footer_user">Gestione Bottega';
	
	
	if ( $p4a->e3g_azienda_rag_soc <> '' ) {
        if ( $p4a->e3g_azienda_web <> '' ) {
            if ( strpos( $p4a->e3g_azienda_web, "http://" ) === false ) 
                $riga1 .= ' "<a href="http://' . $p4a->e3g_azienda_web . '" target=_blank>' . $p4a->e3g_azienda_rag_soc . '</a>"';
            else
                $riga1 .= ' "<a href="' . $p4a->e3g_azienda_web . '" target=_blank>' . $p4a->e3g_azienda_rag_soc . '</a>"';
        }
        else
            $riga1 .= ' "' . $p4a->e3g_azienda_rag_soc . '"';
    }
	else
		$riga1 .= ' "' . $tipo_azienda . '"';

	if ( E3G_TIPO_GESTIONE == 'E' ) 
		$riga1 .= " - Anno Contabile: $p4a->e3g_azienda_anno_contabile";
	
	if ( $p4a->e3g_utente_desc <> '' )
		$riga1 .= " - Sei collegato come $p4a->e3g_utente_desc ($p4a->e3g_utente_tipo_desc)</div>";
	else 
		$riga1 .= ' - Non sei collegato</div>';


	// Riga 2: versione sw / versione del database cond / multi
	$riga2 = 
		'<div id="footer_e3g"><strong>' . $p4a->e3g_nome_sw . '</strong> v. ' . E3G_VERSIONE;
		 
	if ( isset($p4a->e3g_db_cond_versione) and 
	     isset($p4a->e3g_azienda_db_multi_versione) and STATO_DEBUG )
		// Se siamo in debug, allora scrive anche le versioni del database
		// (in grassetto se sono diverse da quelle attese)
		$riga2 .=
			"/$p4a->e3g_db_cond_versione/$p4a->e3g_azienda_db_multi_versione"; 
			
	$riga2 .= 
		' - (C) 2003-2009 <a href="http://www.progettoe3g.org/">Progetto e3g</a> - Software gestionali per l\'economia solidale</div>';

	$footer_copy =& $a_this->build( "p4a_label", "footer_copy" );
	$footer_copy->setWidth( "720" );
	$a_this->footer_copy->setValue( $riga1 . $riga2 );

	$a_object->anchor( $a_this->footer_copy );
}


//------------------------------------------------------------------------------
// Restituisce una riga per il footer con nome del software e copyright,
//   solo testo e senza formattazioni (usata ad esempio per le stampe)
//------------------------------------------------------------------------------
function e3g_get_text_footer( ) {
	
	$p4a =& p4a::singleton();

	return "$p4a->e3g_nome_sw v. " . E3G_VERSIONE . 
		( STATO_DEBUG ? "/$p4a->e3g_db_cond_versione/$p4a->e3g_azienda_db_multi_versione" : "" ) .
		" - (C) 2003-2009 Progetto e3g - Software gestionali per l'economia solidale (http://www.progettoe3g.org)";
}


//------------------------------------------------------------------------------
// Invio messaggio per posta elettronica
// $from_type = 1 invia usando come mittente quanto specificato nel config.php
//              2 utilizza l'utente corrente
//              3 uso mailing-list: mittente e ReplyTo sono quelli specificati di seguito 
// Se è necessario andare a capo nel corpo del messaggio, usare "\n"
//------------------------------------------------------------------------------
function e3g_invia_email( $oggetto, $corpo, $address, $address_name='', $from_type=1,
    $ml_from='', $ml_from_name='', $ml_reply_to='', $ml_reply_to_name='' ) {
        
    $p4a =& p4a::singleton();

	$mail = new PHPMailer();
	// Vedi esempi su:
	// http://phpmailer.sourceforge.net/
	// http://phpmailer.sourceforge.net/extending.html					
	// http://openskills.info/infobox.php?IDbox=1156&boxtype=scripts

    $corpo .= "\n\n\n-- \n";
    switch ( $from_type ) {
        case 1: // Mittente specificato nel config.php
            $mail->From     = MAIL_FROM;
            $mail->FromName = MAIL_FROM_NAME;
            $mail->AddReplyTo( MAIL_REPLY, MAIL_REPLY_NAME );
            $corpo .= "Non rispondere a questo messaggio in quanto generato automaticamente.\n\n";
            break;
        case 2: // Mittente utente corrente
            $mail->From     = $p4a->e3g_utente_email;
            $mail->FromName = $p4a->e3g_utente_desc;
            $mail->AddReplyTo( $p4a->e3g_utente_email, $p4a->e3g_utente_desc );
            break;
        case 3: // Uso mailing-list: mittente e ReplyTo sono quelli specificati di seguito
            $mail->From     = $ml_from;
            $mail->FromName = $ml_from_name;
            $mail->AddReplyTo( $ml_reply_to, $ml_reply_to_name );
            break;
    }
    
	$mail->AddAddress( $address, $address_name );

	$mail->Subject = $oggetto;

	if ( E3G_TIPO_GESTIONE == 'G' )
		$corpo .= "GestiGAS / Gestione ordini Gruppi di Acquisto Solidale\nhttp://www.gestigas.org";
	elseif ( E3G_TIPO_GESTIONE == 'E' )
		$corpo .= "Equogest / Gestione Botteghe Commercio Equo e Solidale\nhttp://www.equogest.org";
	else
		$corpo .= "Progetto e3g / Software gestionali per l'Economia Solidale\nhttp://www.progettoe3g.org";
	
	$mail->Body    = $corpo;
	$mail->WordWrap = 70;

    // Eventuale modalità di invio tramite SMTP
    if ( MAIL_SMTP ) {
        $mail->IsSMTP();
        $mail->SMTPSecure = MAIL_SMTP_SECURE;
        $mail->Host = MAIL_HOST;
        $mail->Port = MAIL_PORT;
        $mail->SMTPAuth = MAIL_SMTP_AUTH;
        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_PASSWORD;
    }

	return $mail->Send();
}


//------------------------------------------------------------------------------
// Codifica antispam di indirizzi e-mail
//------------------------------------------------------------------------------
function e3g_email_encode( $input, $text='', $converttype=3 ) {
	// $input: email da codificare
	// $text: testo da visualizzare (uguale all'email per default)
	// $converttype = 1 = 'simple'   : HTML Entities
	// $converttype = 2 = 'advanced' : HTML Entities, Javascript
	// $converttype = 3 = 'heavy'    : HTML Entities, Javascript, Split up
	//
	// Esempio d'uso: <?php echo e3g_email_encode('toctoc@avanti.it'); 
	//
	// Libero adattamento da http://www.net-force.nl/tools/anti-spam/#source

	function encode( $str ) {
		$str_enc = '';
			for($i = 0;$i < strlen($str);$i++) {
					$str_enc .= '&#' . ord($str[$i]) . ';';
			}
			return $str_enc;
	}

	if ( $text == '' ) $text = $input;
	
	switch ( $converttype ) {
	  case 1:  // 'simple'
			$output = '<a href="'.encode('mailto:'.$input).'">'.$text.'</a>';
			break;
	  case 2:  // 'advanced'
			$output = '<script type="text/javascript">';
			$output .= 'document.write("<a"+" "+"href=\"'.encode('mailto:'.$input).'\">'.encode($text).'</a>");';
			$output .= '</script>';
			break;
	  case 3:  // 'heavy'
			$email = explode('&#64;', encode($input));
			$output = '<script type="text/javascript">';
			$output .= 'var user = "'.$email[0].'";';
			$output .= 'var domain = "'.$email[1].'";';
			$output .= 'var mail = user + "&#64;" + domain;';
			$output .= 'var message = "'.encode($text).'";';
			$output .= 'document.write("<a"+" "+"href=\"'.encode('mailto:').'"+mail+"\">"+message+"</a>");';
			$output .= '</script>';
			break;
	}
	
	return $output;
}


//------------------------------------------------------------------------------
// Validazione indirizzo e-mail
//------------------------------------------------------------------------------
function e3g_email_valido( $indirizzo_email ) {
    return ( p4a_validate::email($indirizzo_email) );
}
   
/* 
function e3g_email_valido( $indirizzo_email ) {
	// *** Prima versione: troppo semplificata
//  return ( eregi("^[a-z0-9][_\.a-z0-9-]+@([a-z0-9][0-9a-z-]+\.)+([a-z]{2,4})", $indirizzo_email) );

	// *** Seconda versione: da http://www.devpro.it/php4_id_2.html (-> non accetta gli indirizzi con domini di terzo livello)
/*	$r1 = "([a-z0-9]+[";
	$r2 = "\-]?){1,3}([a-z0-9])*";
	return preg_match( "/(?i)^{$r1}\._{$r2}\@{$r1}{$r2}\.[a-z]{2,6}$/", $indirizzo_email ); *

	// *** Terza versione: da http://www.ilovejackdaniels.com/php/email-address-validation/
	// First, we check that there's one @ symbol, and that the lengths are right
	if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $indirizzo_email)) {
		// Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
		return false;
	}
	
	// Split it into sections to make life easier
	$email_array = explode("@", $indirizzo_email);
	$local_array = explode(".", $email_array[0]);
	
	for ($i = 0; $i < sizeof($local_array); $i++) {
		if (!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i])) {
			return false;
		}
	}
	
	if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) { // Check if domain is IP. If not, it should be valid domain name
		$domain_array = explode(".", $email_array[1]);
		if (sizeof($domain_array) < 2) {
		return false; // Not enough parts to domain
		}
		for ($i = 0; $i < sizeof($domain_array); $i++) {
			if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i])) {
				return false;
			}
		}
	}
	return true;
}
*/
		
//------------------------------------------------------------------------------
// Generazione di password
//------------------------------------------------------------------------------
function e3g_get_pass( $pass_len=8, $numeric=1, $uppercase=0, $lowercase=1 ) {
  // $pass_len  = lunghezza della password da generare
	// $numeric   = 0/1 utilizza o meno anche le cifre
	// $uppercase = 0/1 utilizza o meno i caratteri maiuscoli
	// $lowercase = 0/1 utilizza o meno anche i caratteri minuscoli

	//set alphabet arrays
	$alpha_lower = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z");
	$alpha_upper = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");

	//set option arrays
	if (($numeric == 0) && ($uppercase == 1) && ($lowercase == 0)) { $options = array("AU"); }
	if (($numeric == 0) && ($uppercase == 0) && ($lowercase == 1)) { $options = array("AL"); }
	if (($numeric == 1) && ($uppercase == 0) && ($lowercase == 0)) { $options = array("NU"); }
	if (($numeric == 1) && ($uppercase == 1) && ($lowercase == 1)) { $options = array("AU", "AL", "NU"); }
	if (($numeric == 0) && ($uppercase == 1) && ($lowercase == 1)) { $options = array("AU", "AL"); }
	if (($numeric == 1) && ($uppercase == 1) && ($lowercase == 0)) { $options = array("AU", "NU"); }
	if (($numeric == 1) && ($uppercase == 0) && ($lowercase == 1)) { $options = array("AL", "NU"); }

	//seed the random generator
	srand((double) microtime() * 1000000);

	//create password
	for ($loop = 0; $loop < $pass_len; $loop++)	{
		$pass_numeric = rand(0, 9);
		$pass_alpha_array_select = rand (0, 25);
		$item = array_rand($options, 1);

		if ($loop == 0)	{
			if ($options[$item] == "NU") { $password = $pass_numeric; }
			if ($options[$item] == "AL") { $password = $alpha_lower[$pass_alpha_array_select]; }
			if ($options[$item] == "AU") { $password = $alpha_upper[$pass_alpha_array_select]; }
		}
		else {
			if ($options[$item] == "NU") { $password .= $pass_numeric; }
			if ($options[$item] == "AL") { $password .= $alpha_lower[$pass_alpha_array_select]; }
			if ($options[$item] == "AU") { $password .= $alpha_upper[$pass_alpha_array_select]; }
		}
	}
	return $password;
}


//------------------------------------------------------------------------------
// Recupera nome ed e-mail dell'amministratore
//   Se esistono più amministratori, restituisce solo il primo
//   Esempio: "Mario Rossi (mario.rossi@gestigas.org)"
//------------------------------------------------------------------------------
function e3g_get_name_email_admin() {

	$p4a =& p4a::singleton();
	$db =& p4a_db::singleton();

	$result = $db->queryRow(
		"SELECT descrizione, email FROM " . $p4a->e3g_prefix . "anagrafiche " .
		" WHERE tipoutente = 'AS' AND stato = 1" );
	
	return $result[ "descrizione" ] . " (" . $result[ "email" ] . ")";
}


//------------------------------------------------------------------------------
// Recupera il nome dell'amministratore
//   Se esistono più amministratori, restituisce solo il primo
//------------------------------------------------------------------------------
function e3g_get_name_admin() {

	$p4a =& p4a::singleton();
	$db =& p4a_db::singleton();

	$result = $db->queryRow(
		"SELECT descrizione, email FROM " . $p4a->e3g_prefix . "anagrafiche " .
		" WHERE tipoutente = 'AS' AND stato = 1" );
	return $result[ "descrizione" ];
}


//------------------------------------------------------------------------------
// Recupera l'indirizzo e-mail dell'amministratore
//   Se esistono più amministratori, restituisce solo il primo
//------------------------------------------------------------------------------
function e3g_get_email_admin() {

	$p4a =& p4a::singleton();
	$db =& p4a_db::singleton();

	$result = $db->queryRow(
		"SELECT descrizione, email FROM " . $p4a->e3g_prefix . "anagrafiche " .
		" WHERE tipoutente = 'AS' AND stato = 1" );
	return $result[ "email" ];
}


//------------------------------------------------------------------------------
// Funzione per l'aggiornamento del database
// (richiamata dalla pagina principale, ma anche dalla creazione nuova_gestione.php)
//   $prefix == '#COND# -> aggiornamento tabelle condivise
//   altrimenti aggiornare singola gestione avente prefisso $prefix
//------------------------------------------------------------------------------
function e3g_aggiorna_database( $prefix ) {
	
	$p4a =& p4a::singleton();
	$db =& p4a_db::singleton();

	if ($prefix == "#COND#")
	{
		// aggiornamento tabelle comuni
		$dbver = $db->queryOne( "SELECT dbver FROM _config" );	
		$dbatteso = E3G_DB_COND_VERSIONE_ATTESA ;
	}
	else 
	{
		// aggiornamento tabelle normali
		$dbver = $db->queryOne( "SELECT dbver FROM _aziende WHERE prefix = '".$prefix."'" );	
		$dbatteso = E3G_DB_MULTI_VERSIONE_ATTESA ;		
	}

	if ( $dbver < $dbatteso ) 
		do {
			$i_dbver = (integer)$dbver;
			$i_dbver++;
			
			$nome_file = dirname(__FILE__) . "/../db/update_" . 
				($prefix=="#COND#" ? "cond" : "multi" ) . "_" . sprintf( "%04d", $i_dbver ) . ".sql";
			
			if ( !file_exists($nome_file) )
				return array( false, "Aggiornamento impossibile causa mancanza del file '$nome_file'" );

			$idfile = fopen( $nome_file, "r" )
				or die( "Impossibile leggere il file ($nome_file)" );
			$dati = file( $nome_file );
	
			foreach ( $dati as $riga ) {
				if ( trim($riga) != "" ) {
					$strdata = str_replace( "[PREFIX]", $prefix, $riga );
					$db->query( $strdata );
				}
			}
			fclose( $idfile );

			if ( $prefix == "#COND#" )
			{
				// aggiornamento tabelle comuni
				$dbver = $db->queryOne( "SELECT dbver FROM _config" );	
			}
			else 
			{
				// aggiornamento tabelle normali
				$dbver = $db->queryOne( "SELECT dbver FROM _aziende WHERE prefix = '".$prefix."'" );	
			}
			
			if ( (integer)$dbver <> (integer)$i_dbver )
				return array( false, "L'aggiornamento alla versione '" . sprintf( "%04d", $i_dbver ) . "' e' fallito." );
		}
		while ( $dbver < $dbatteso and $i_dbver <= 9999 );

	if ( $prefix == "#COND#" )
	{
		// aggiornamento tabelle condivise
		$p4a->e3g_db_cond_versione = $dbver;
	}
	elseif ( $prefix == $p4a->e3g_prefix )
	{
		// aggiornamento tabelle normali GESTIONE CORRENTE
		$p4a->e3g_azienda_db_multi_versione = $dbver;	
	}
	else 
	{
		// aggiornamento tabelle normali ALTRA GESTIONE (richiamo da nuova_gestione.php)
		// ...niente in più da fare
	}

	return array( true, "Aggiornamento concluso: la versione del database e' ora " . 
		"$p4a->e3g_db_cond_versione/$p4a->e3g_azienda_db_multi_versione" );
}


//------------------------------------------------------------------------------
// Aggiornamento variabili globali dell'utente'
//   (richiamata quando questi dati possono essere stati cambiati)
//------------------------------------------------------------------------------
function e3g_update_var_utente( $id_anagrafica ) {

	$p4a =& p4a::singleton();
	$db =& p4a_db::singleton();

	$result = $db->queryRow(
		"SELECT a.*, " .
		"       ltu.descrizione e3g_utente_tipo_desc " .
		"  FROM " . $p4a->e3g_prefix . "anagrafiche a, _login_tipo_utente ltu " .
		" WHERE a.idanag = $id_anagrafica " .
		"   AND a.tipoutente = ltu.codice " );
		
	$p4a->e3g_utente_codice = $result["codice"];
	$p4a->e3g_utente_desc   = $result["descrizione"];
	$p4a->e3g_utente_tipo   = $result["tipoutente"];
    $p4a->e3g_utente_idanag = $result["idanag"];
    $p4a->e3g_utente_email  = $result["email"];
    $p4a->e3g_utente_db_source_page_limit = $result["db_source_page_limit"];
	$p4a->e3g_utente_tipo_desc            = $result["e3g_utente_tipo_desc"];
    $p4a->e3g_utente_modifica_ingredienti = $result["modifica_ingredienti"];
    $p4a->e3g_utente_filtro_ingredienti   = $result["filtro_ingredienti"];
    $p4a->e3g_utente_cassiere             = $result["cassiere"];  
	
	e3g_update_where_referente();
}


//------------------------------------------------------------------------------
// Aggiornamento variabili globali del gas/bottega
//   (richiamata quando questi dati possono essere stati cambiati)
//------------------------------------------------------------------------------
function e3g_update_var_azienda() {

	$p4a =& p4a::singleton();
	$db =& p4a_db::singleton();
	
	$result = $db->queryRow( "SELECT * FROM _aziende WHERE prefix = '$p4a->e3g_prefix'" );

	$p4a->e3g_azienda_rag_soc                = $result["rag_soc"];
	$p4a->e3g_azienda_indirizzo              = $result["indirizzo"];
	$p4a->e3g_azienda_cap                    = $result["cap"];
	$p4a->e3g_azienda_localita               = $result["localita"];
	$p4a->e3g_azienda_provincia              = $result["provincia"];
	
	$p4a->e3g_azienda_telefono               = $result["telefono"];
    $p4a->e3g_azienda_web                    = $result["web"];
	$p4a->e3g_azienda_email                  = $result["email"];
	$p4a->e3g_azienda_db_multi_versione      = $result["dbver"];
	
	$p4a->e3g_azienda_piva					 = $result["piva"];
	
	$p4a->e3g_banca							 = $result["banca"];
	$p4a->e3g_agenzia						 = $result["agenzia"];
	$p4a->e3g_abi					         = $result["abi"];
	$p4a->e3g_cab							 = $result["cab"];
	$p4a->e3g_cin	    					 = $result["cin"];
	$p4a->e3g_conto_corrente			     = $result["conto_corrente"];
	$p4a->e3g_iban	    					 = $result["iban"];
	
	$p4a->e3g_azienda_gg_cod_doc_ordine      = $result["gg_cod_doc_ordine"];
	$p4a->e3g_azienda_gg_cod_doc_ordine_fam  = $result["gg_cod_doc_ordine_fam"];
	
	$p4a->e3g_azienda_n_decimali_prezzi      = $result["n_decimali_prezzi"];
	$p4a->e3g_azienda_ordine_minimo          = $result["ordine_minimo"];
	$p4a->e3g_azienda_gestione_luoghi_cons   = $result["gestione_luoghi_cons"];
    $p4a->e3g_azienda_gestione_cassa         = $result["gestione_cassa"];
    $p4a->e3g_azienda_acquista_se_credito_insufficiente = $result["acquista_se_credito_insufficiente"];
	
	$p4a->e3g_azienda_tipo_gestione_prezzi   = $result["tipo_gestione_prezzi"];
	$p4a->e3g_azienda_mostra_prezzo_sorgente = $result["mostra_prezzo_sorgente"]; 
	$p4a->e3g_azienda_prezzi_mag_fissa       = $result["prezzi_mag_fissa"];
	$p4a->e3g_azienda_prezzi_mag_perc        = $result["prezzi_mag_perc"];
	$p4a->e3g_azienda_etichette_path         = $result["etichette_path"];
	$p4a->e3g_azienda_anno_contabile         = $result["anno_contabile"];
	if ( !is_numeric($p4a->e3g_azienda_anno_contabile) )
	{
		// se non c'è l'anno contabile prendo quello corrente
		$p4a->e3g_azienda_anno_contabile     = Date("Y");
		$db->query("UPDATE _aziende SET anno_contabile = '".$p4a->e3g_azienda_anno_contabile."' WHERE prefix='$p4a->e3g_prefix'");
	}	

	// Aggiorna anche la versione delle tabelle condivise
	$p4a->e3g_db_cond_versione = $db->queryOne("SELECT dbver FROM _config");
	
	// tipo di documento in stampa (PDF, ODT...)
	$p4a->e3g_azienda_tipo_documento   = $result["tipo_documento"];
	$p4a->e3g_azienda_path_documento   = $result["path_documento"];
	$p4a->e3g_azienda_path_logo 	   = $result["path_logo"];
}


//------------------------------------------------------------------------------
// Funzione per la costruzione della SELECT per i Referenti
//------------------------------------------------------------------------------
function e3g_update_where_referente()
{
	$p4a =& p4a::singleton();
	
	$p4a->build("p4a_db_source", "ds_ref");
	$p4a->ds_ref->setTable( $p4a->e3g_prefix . "referenti" );
	$p4a->ds_ref->setPk("idtable");
	$p4a->ds_ref->setSelect("idtable, codanag, codfornitore");
	$p4a->ds_ref->setWhere("codanag = '$p4a->e3g_utente_codice'");
	$p4a->ds_ref->load();
	$p4a->ds_ref->firstRow();
		
	$p4a->e3g_where_referente = " #CAMPOCODICE# IN (";
	$riga = 1 ;

	while( $riga <= $p4a->ds_ref->getNumRows() )
	{
		if ($riga > 1)
			$p4a->e3g_where_referente .= ", ";

		$p4a->e3g_where_referente .= "'" . $p4a->ds_ref->fields->codfornitore->getNewValue() . "'";

		$p4a->ds_ref->nextRow();
		$riga++;
	}

	$p4a->e3g_where_referente .= " ) ";
}


//------------------------------------------------------------------------------
// Restituisce la condizione WHERE per stabilire di quali fornitori sono aperti 
// gli ordini; passare come parametri l'alias desiderato per la tabella
//------------------------------------------------------------------------------
function e3g_where_ordini_aperti( $alias = "" )
{
	$p4a =& p4a::singleton();
	
	if ( $alias == "" )
		$alias = $p4a->e3g_prefix . "fornitoreperiodo";

	// Così purtroppo non si può: MAKEDATE() c'è da MySQL 4.1.1 (su vandana c'è MySQL 4.0.24)	
/*	return
		" ( ( CURDATE() >= $alias.datainizio AND CURDATE() <= $alias.datafine ) OR " .
		"   ( CURDATE() >= MAKEDATE( EXTRACT(YEAR FROM CURDATE()), DAYOFYEAR($alias.datainizio) ) AND " .
		"     CURDATE() <= MAKEDATE( EXTRACT(YEAR FROM CURDATE()), DAYOFYEAR($alias.datafine) ) AND " .
		"     $alias.ricorsivo = 'S' ) ) "; 
Quindi
	MAKEDATE( EXTRACT(YEAR FROM CURDATE()), DAYOFYEAR($alias.datainizio) )
può diventare
	CAST( CONCAT( YEAR(CURDATE()), '-', MONTH($alias.datainizio), '-', DAYOFMONTH($alias.datainizio) ) AS DATE )
*/		
	return
		" ( ( CURDATE() >= $alias.datainizio AND CURDATE() <= $alias.datafine ) OR " .
		"   ( CURDATE() >= CAST( CONCAT( YEAR(CURDATE()), '-', MONTH($alias.datainizio), '-', DAYOFMONTH($alias.datainizio) ) AS DATE ) AND " .
		"     CURDATE() <= CAST( CONCAT( YEAR(CURDATE()), '-', MONTH($alias.datafine), '-', DAYOFMONTH($alias.datafine) ) AS DATE ) AND " .
		"     $alias.ricorsivo = 'S' ) ) ";
}


// -----------------------------------------------------------------------------
// Funzione per la conversione del formato date,
// da quello utilizzato da mysql "aaaa-mm-gg" a "mm/gg/aaaa"
// -----------------------------------------------------------------------------
function e3g_format_mysql_data( $miadata )
{
	if ( $miadata == '' )
		$miadata = str_replace( "/", "-", date ("d-m-y") ); 

	$pos1 = strpos( $miadata, '-' );
	$pos2 = strpos( $miadata, '-', $pos1 + 1 );

	$year  = substr( $miadata, 0, $pos1 );
	$month = substr( $miadata, $pos1 + 1, $pos2 - $pos1 - 1 );
	$day   = substr( $miadata, $pos2 + 1, strlen($miadata) - $pos2 - 1 );

	return "$day/$month/$year";
}


// -----------------------------------------------------------------------------
// Funzione per la conversione del formato date,
// da quello standard "mm-gg-aa" (o "mm-gg-aaaa") a quello utilizzato da mysql  
// -----------------------------------------------------------------------------
function e3g_format_data_to_mysql( $miadata )
{
    if ( $miadata == '')
        $miadata = str_replace( "-", "/", date ("d-m-y") );
    
    $pos1 = strpos ( $miadata, '/' );
    $pos2 = strpos ( $miadata, '/', $pos1 + 1 );
    
    $year  = substr ( $miadata, $pos2 + 1, strlen($miadata) - $pos2 - 1 );
    $month = substr ( $miadata, $pos1 + 1, $pos2 - $pos1 - 1 );
    $day   = substr ( $miadata, 0, $pos1 );

    return $year . "-" . $month . "-" . $day;
}


// -----------------------------------------------------------------------------
// Funzione per il controllo del codice fiscale persone fisiche (16 caratteri)
// -----------------------------------------------------------------------------
function CodiceFiscaleEsatto( $cf ) {
    $cf = strtoupper( $cf );

    if ( $cf == '' )  return false;
    if ( strlen($cf) != 16 ) return false;
    if( ! ereg("^[A-Z0-9]+$", $cf) ) return false;
    
    $s = 0;
    for( $i = 1; $i <= 13; $i += 2 ) {
        $c = $cf[$i];
        if ( '0' <= $c && $c <= '9' )
            $s += ord($c) - ord('0');
        else
            $s += ord($c) - ord('A');
    }
    
    for( $i = 0; $i <= 14; $i += 2 ) {
        $c = $cf[$i];
        switch( $c ){
            case '0':  $s += 1;  break;
            case '1':  $s += 0;  break;
            case '2':  $s += 5;  break;
            case '3':  $s += 7;  break;
            case '4':  $s += 9;  break;
            case '5':  $s += 13;  break;
            case '6':  $s += 15;  break;
            case '7':  $s += 17;  break;
            case '8':  $s += 19;  break;
            case '9':  $s += 21;  break;
            case 'A':  $s += 1;  break;
            case 'B':  $s += 0;  break;
            case 'C':  $s += 5;  break;
            case 'D':  $s += 7;  break;
            case 'E':  $s += 9;  break;
            case 'F':  $s += 13;  break;
            case 'G':  $s += 15;  break;
            case 'H':  $s += 17;  break;
            case 'I':  $s += 19;  break;
            case 'J':  $s += 21;  break;
            case 'K':  $s += 2;  break;
            case 'L':  $s += 4;  break;
            case 'M':  $s += 18;  break;
            case 'N':  $s += 20;  break;
            case 'O':  $s += 11;  break;
            case 'P':  $s += 3;  break;
            case 'Q':  $s += 6;  break;
            case 'R':  $s += 8;  break;
            case 'S':  $s += 12;  break;
            case 'T':  $s += 14;  break;
            case 'U':  $s += 16;  break;
            case 'V':  $s += 10;  break;
            case 'W':  $s += 22;  break;
            case 'X':  $s += 25;  break;
            case 'Y':  $s += 24;  break;
            case 'Z':  $s += 23;  break;
        }
    }

    if ( chr($s%26 + ord('A')) != $cf[15] ) return false;
    
    return true;
}


// -----------------------------------------------------------------------------
// Esportazione righe dei db_source come CSV (foglio elettronico)
// -----------------------------------------------------------------------------
function e3g_db_source_exportToCsv( $a_db_source, $a_colonne, $nome_file )
{
    // Appende la data di esportazione in coda al nome file ricevuto e sostuisce i caratteri non ammessi
    $nome_file = P4A_Get_Valid_File_Name( $nome_file . date( "_Y-m-d_H-i" ) . ".csv" );

    //$a_db_source->exportToCsv( $nome_file, ";", $a_colonne );
    send_file_to_client($nome_file, export_array_to_csv($a_db_source->getAll(), $a_colonne));
    
}


// -----------------------------------------------------------------------------
// Verifica della corretta connessione al database 
// -----------------------------------------------------------------------------
function e3g_verifica_connessione_db()
{
    $p4a =& p4a::singleton();
    $db =& p4a_db::singleton();

/*  In realtà dovrebbe essere direttamente
 *      if (PEAR::isError($db)) 
 *  ...ma purtroppo non funziona; provando una qualunque query invece si:
 */
    $result = $db->queryOne( "SELECT COUNT( * ) FROM _aziende" );
 
    if ( PEAR::isError($result) ) 
        exit( $result->getMessage() . " (problemi nella connessione al database)" );
}


// -----------------------------------------------------------------------------
// Restituisce l'elenco delle prossime aperture come lista HTML
//   (visualizza solo le prime $max_aperture)
// -----------------------------------------------------------------------------
function e3g_get_html_elenco_prossime_aperture( $max_aperture = 5 )
// -----------------------------------------------------------------------------
{
    $p4a =& p4a::singleton();
    $db =& p4a_db::singleton();

    $query = $db->getAll(
        "SELECT CONCAT_WS( ' / ', f.descrizione, f.desc_agg ) AS descrizione, " . 
        "       UNIX_TIMESTAMP( CAST( CONCAT( " . 
        "         IF( fp.ricorsivo = 'S', " .
        "           YEAR(CURRENT_DATE) + " .
        "             IF( MONTH(fp.datainizio)<=MONTH(CURRENT_DATE) AND DAY(fp.datainizio)<=DAY(CURRENT_DATE), 1, 0 ), " .
        "           YEAR(fp.datainizio) ), '-', " . 
        "         MONTH(fp.datainizio), '-', " . 
        "         DAY(fp.datainizio) ) AS DATE ) ) AS data_inizio, " .
        "       UNIX_TIMESTAMP( CAST( CONCAT( " . 
        "         IF( fp.ricorsivo = 'S', " .
        "           YEAR(CURRENT_DATE) + " .
        "             IF( MONTH(fp.datafine)<=MONTH(CURRENT_DATE) AND DAY(fp.datafine)<=DAY(CURRENT_DATE), 1, 0 ), " .
        "           YEAR(fp.datafine) ), '-', " . 
        "         MONTH(fp.datafine), '-', " . 
        "         DAY(fp.datafine) ) AS DATE ) ) AS data_fine, " .
        "       COUNT( a.codice ) AS n_articoli " .
        "  FROM " . $p4a->e3g_prefix . "fornitoreperiodo fp " . 
        "       JOIN " . $p4a->e3g_prefix . "anagrafiche f ON f.codice = fp.fornitore " .
        "       LEFT JOIN " . $p4a->e3g_prefix . "articoli a ON a.centrale = f.codice AND a.stato = 1 " . 
        " WHERE CAST( CONCAT( " . 
        "         IF( fp.ricorsivo = 'S', " .
        "           YEAR(CURRENT_DATE) + " .
        "             IF( MONTH(fp.datainizio)<=MONTH(CURRENT_DATE) AND DAY(fp.datainizio)<=DAY(CURRENT_DATE), 1, 0 ), " .
        "           YEAR(fp.datainizio) ), '-', " . 
        "         MONTH(fp.datainizio), '-', " . 
        "         DAY(fp.datainizio) ) AS DATE ) >= CURRENT_DATE " .
      "GROUP BY f.descrizione, f.desc_agg, fp.ricorsivo, fp.datainizio, fp.datafine " .          
      "ORDER BY data_inizio, f.descrizione " );
       
    // * da mercoledì 15 aprile al 15 maggio: EUGEA / Ecologia Urbana (3 articoli)

    $testo = "<ul>";
    if ( $query ) {
        $i = 0;
        foreach ( $query as $record ) {
            $testo .= "<li>da " .
                strftime( "%A %e %B" . (date("Y",$record["data_inizio"]) <> date("Y") ?" %Y" : ""),  // Anche l'anno se è diverso dal corrente
                    $record["data_inizio"] ) .  // Questo deve essere in italiano (se locale è corretto)
                " al " . strftime( "%e %B", $record["data_fine"] ) .  
                ": <strong>" . $record["descrizione"] . "</strong> " . 
                "(" . $record["n_articoli"] . " articol" . ( $record["n_articoli"]==1 ? "o" : "i" ) . ")" . 
                "</li>";
            $i++;
            if ( $i == $max_aperture ) break;  // Visualizza solo le prossime $max_aperture aperture
        }
        if ( array_key_exists($i, $query) )
            $testo .= "<li>[...]</li>";
    }
    else
        $testo .= "<li><em>nessuna apertura e' al momento programmata</em></li>";
    $testo .= "</ul>";
    
    return $testo;
}


// -----------------------------------------------------------------------------
// Restituisce l'elenco delle prossime chiusure come lista HTML
// -----------------------------------------------------------------------------
function e3g_get_html_elenco_prossime_chiusure( $max_chiusure = -1 )
// -----------------------------------------------------------------------------
{
    $p4a =& p4a::singleton();
    $db =& p4a_db::singleton();
    
    $query = $db->getAll( 
        "SELECT CONCAT_WS( ' / ', f.descrizione, f.desc_agg ) AS descrizione, " .
        "       UNIX_TIMESTAMP( CAST( CONCAT( " . 
        "         IF( fp.ricorsivo = 'S', " .
        "           YEAR(CURRENT_DATE) + " .
        "             IF( MONTH(fp.datafine)<=MONTH(CURRENT_DATE) AND DAY(fp.datafine)<=DAY(CURRENT_DATE), 1, 0 ), " .
        "           YEAR(fp.datafine) ), '-', " . 
        "         MONTH(fp.datafine), '-', " . 
        "         DAY(fp.datafine) ) AS DATE ) ) AS data_fine, " .
        "       COUNT( a.codice ) AS n_articoli " .
        "  FROM " . $p4a->e3g_prefix . "fornitoreperiodo fp " . 
        "       JOIN " . $p4a->e3g_prefix . "anagrafiche f ON f.codice = fp.fornitore " . 
        "       LEFT JOIN " . $p4a->e3g_prefix . "articoli a ON a.centrale = f.codice AND a.stato = 1 " .
        " WHERE " . e3g_where_ordini_aperti( "fp" ) .
     " GROUP BY f.descrizione, f.desc_agg, fp.ricorsivo, fp.datafine " .
     " ORDER BY CAST( CONCAT( YEAR(CURRENT_DATE), '-', MONTH(fp.datafine), '-', DAY(fp.datafine) ) AS DATE ), " . 
     "          f.descrizione" );

    // * fino a venerdì 13 marzo: EUGEA / Ecologia Urbana (3 articoli)

    $testo = "<ul>";
    $i = 0;
    foreach ( $query as $record ) {
        $testo .= "<li>fino a " .
            strftime( "%A %e %B" . (date("Y",$record["data_fine"]) <> date("Y") ?" %Y" : ""),  // Anche l'anno se è diverso dal corrente
                $record["data_fine"] ) .  // Questo deve essere in italiano (se locale è corretto)
            ": <strong>" . $record["descrizione"] . "</strong> " . 
            "(" . $record["n_articoli"] . " articol" . ( $record["n_articoli"]==1 ? "o" : "i" ) . ")" . 
            "</li>";
        $i++;
        if ( $i == $max_chiusure ) break;  // Visualizza solo le prossime $max_chiusure chiusure
    }
    if ( array_key_exists($i, $query) )
        $testo .= "<li>[...]</li>";
    $testo .= "</ul>";
    
    return $testo;
}


?>