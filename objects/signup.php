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


require_once( dirname(__FILE__) . '/../libraries/e3g_utils.php' );
require_once( dirname(__FILE__) . '/../config.php' );


class signup extends P4A_Mask
{

	// -------------------------------------------------------------------------
	function signup()
	// -------------------------------------------------------------------------
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();

		$this->setTitle( e3g_title() );

	    // Immagine logo (come box HTML anzichà p4a_image altrimenti non si centra)
		if ( E3G_TIPO_GESTIONE == 'G' )
			$src_logo = 'images/gestigas_01.jpg';
		else
			$src_logo = 'images/equogest_01.jpg';
		$this->build("p4a_box", "box_logo");
		$this->box_logo->setValue( '<div align="center"><img src="' . $src_logo .
			'" alt="Progetto e3g - Equogest/GestiGAS" /></div>' );


		$this->build("p4a_label", "lbl_intro");
		$this->lbl_intro->setValue( "Per un pieno accesso a $p4a->e3g_nome_sw" .
			" devi richiedere un nuovo account per te.<br />" .
			"Questi sono i passi necessari:<br /><br />" .
			"1. Compila il modulo seguente<br />" .
			"2. Un messaggio e-mail di conferma contenente la password di accesso verrà spedito al tuo indirizzo, ma ancora non lo potrai utilizzare perchè inattivo<br />" .
			"3. L'amministratore verrà informato della tua richiesta e provvederà ad abilitarti<br />" .
			"4. Un messaggio e-mail di avviso verrà spedito al tuo indirizzo di posta elettronica per informarti dell'avvenuta abilitazione<br />" .
			"5. Da questo momento potrai utilizzare i tuoi indirizzo e-mail e password per farti riconoscere ed accedere a $p4a->e3g_nome_sw<br /><br />" .
			"<strong>Compilare il modulo con le proprie informazioni: " .
			"tutti i campi sono obbligatori</strong>" );
		$this->lbl_intro->setWidth("650");

		// Message per eventuale segnalazione di errori
		$message =& $this->build("p4a_message", "message");
		$message->setWidth("650");

		// Campo email
		$this->build("p4a_field", "email");
		$this->email->setLabel( E3G_TIPO_GESTIONE == 'G' ? "E-mail:" : "Nome utente:" );
		$this->email->setWidth("250");
		//$email->addAction("onReturnPress");
		//$this->intercept($email, "onReturnPress","email_click");

		// Campo email2
		$this->build("p4a_field", "email2");
		$this->email2->setLabel( E3G_TIPO_GESTIONE == 'G' ? "E-mail (ripetere):" : "Nome utente (ripetere):" );
		$this->email2->setWidth("250");

		// Nome
		$this->build("p4a_field", "nome");
		$this->nome->setLabel( "Nome:" );
		$this->nome->setWidth("250");

		// Cognome
		$this->build("p4a_field", "cognome");
		$this->cognome->setLabel( "Cognome:" );
		$this->cognome->setWidth("250");

		$this->build("p4a_field", "indirizzo");
		$this->indirizzo->setLabel( "Indirizzo:" );
		$this->indirizzo->setWidth("250");

		$this->build("p4a_field", "cap");
		$this->cap->setLabel( "CAP:" );
		$this->cap->setWidth("250");

		$this->build("p4a_field", "localita");
		$this->localita->setLabel( "Localita':" );
		$this->localita->setWidth("250");

		$this->build("p4a_field", "provincia");
		$this->provincia->setLabel( "Provincia:" );
		$this->provincia->setWidth("250");

		$this->build("p4a_field", "telefono");
		$this->telefono->setLabel( "Telefono fisso:" );
		$this->telefono->setWidth("250");

		$this->build("p4a_field", "fax");
		$this->fax->setLabel( "Telefono cellulare:" );
		$this->fax->setWidth("250");

		$this->build("p4a_field", "data_nascita");
		$this->data_nascita->setLabel( "Data di nascita:" );
		$this->data_nascita->setWidth("250");

		$this->build("p4a_field", "luogo_nascita");
		$this->luogo_nascita->setLabel( "Luogo di nascita:" );
		$this->luogo_nascita->setWidth("250");

		$this->build("p4a_field", "cf");
		$this->cf->setLabel( "Codice fiscale:" );
		$this->cf->setWidth("250");

		// Messaggio (facoltativo))
		$this->build("p4a_field", "messaggio");
		$this->messaggio->setLabel( "Messaggio:" );
		$this->messaggio->setWidth("250");
		$this->messaggio->setHeight(100);

		// Pulsante Login
		$this->build("p4a_button", "bu_signup");
		$this->bu_signup->setWidth("300");
		$this->bu_signup->setLabel("Richiedi nuovo account");
		$this->bu_signup->setIcon( "login_sign" );
		$this->bu_signup->addAction("onClick");
		$this->intercept($this->bu_signup, "onClick", "bu_signup_click");

		//Fieldset con di login
		$this->build("p4a_fieldset", "fs_signup");
		$this->fs_signup->setWidth(700);
		$this->fs_signup->setTitle("E' la prima volta che hai accesso qui?");
 		$this->fs_signup->anchorLeft($this->lbl_intro);
		$this->fs_signup->anchor($this->message);
 		$this->fs_signup->anchor($this->email);
 		$this->fs_signup->anchor($this->email2);
 		$this->fs_signup->anchor($this->nome);
 		$this->fs_signup->anchor($this->cognome);
                $this->fs_signup->anchor($this->indirizzo);
                $this->fs_signup->anchor($this->cap);
                $this->fs_signup->anchor($this->localita);
                $this->fs_signup->anchor($this->provincia);
                $this->fs_signup->anchor($this->telefono);
                $this->fs_signup->anchor($this->fax);
                $this->fs_signup->anchor($this->data_nascita);
                $this->fs_signup->anchor($this->luogo_nascita);
                $this->fs_signup->anchor($this->cf);
 		$this->fs_signup->anchor($this->messaggio);
		$this->fs_signup->anchorRight($this->bu_signup);


		// Link per tornare alla pagina iniziale -------------------------------
		$this->build("p4a_button", "bu_home_page");
		$this->bu_home_page->setWidth("300");
		$this->bu_home_page->setLabel("Torna alla pagina iniziale");
		$this->bu_home_page->setIcon( "undo" );
		$this->bu_home_page->addAction("onClick");
		$this->intercept($this->bu_home_page, "onClick", "showPrevMask");


		// Frame principale ----------------------------------------------------
		$frm=& $this->build("p4a_frame", "frm");
		$frm->setWidth(730);

 		$frm->anchorCenter($this->box_logo);
		$frm->anchorCenter($this->fs_signup);
		$frm->anchorCenter($this->bu_home_page);

		e3g_scrivi_footer( $this, $frm );

  		// Display
		$this->display("main", $frm);
		
		$this->setFocus($this->email);
	}

	
	// -------------------------------------------------------------------------
	function main()
	// -------------------------------------------------------------------------
	{
		parent::main();
	}
	
	
	// -------------------------------------------------------------------------
	function bu_signup_click()
	// -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();

		$email = strtolower( $this->email->getNewValue() );
		$email2 = strtolower( $this->email2->getNewValue() );

		if ( $email == '' )
			$this->message->setValue( "Scrivere un indirizzo e-mail valido." );
		elseif ( $email2 == '' )
			$this->message->setValue( "Scrivere due volte l'indirizzo e-mail." );
		elseif ( $this->nome->getNewValue() == '' )
			$this->message->setValue( "Scrivere il nome." );
		elseif ( $this->cognome->getNewValue() == '' )
			$this->message->setValue( "Scrivere il cognome." );
		elseif ( $this->indirizzo->getNewValue() == '' )
			$this->message->setValue( "Scrivere l'indirizzo." );
		elseif ( $this->cap->getNewValue() == '' )
			$this->message->setValue( "Scrivere il CAP." );
		elseif ( $this->localita->getNewValue() == '' )
			$this->message->setValue( "Scrivere la localita." );
		elseif ( $this->provincia->getNewValue() == '' )
			$this->message->setValue( "Scrivere la provincia." );
		elseif ( $this->telefono->getNewValue() == '' && $this->fax->getNewValue() == '' )
			$this->message->setValue( "Scrivere un numero di telefono (fisso o cellulare)." );
		elseif ( $this->data_nascita->getNewValue() == '' )
			$this->message->setValue( "Scrivere la data di nascita." );
		elseif ( $this->luogo_nascita->getNewValue() == '' )
			$this->message->setValue( "Scrivere il luogo di nascita." );
		elseif ( $this->cf->getNewValue() == '' )
			$this->message->setValue( "Scrivere il codice fiscale." );
                elseif ( $this->cf->getNewValue() <> '' and !CodiceFiscaleEsatto( $this->cf->getNewValue() ) )
                        $this->message->setValue( "Il codice fiscale non e' corretto." );
		elseif ( !e3g_email_valido($email) )  // email non valida
			$this->message->setValue( "L'indirizzo e-mail indicato non sembra essere valido." );
		elseif ( $email <> $email2 )
			$this->message->setValue( "I due indirizzi e-mail non coincidono, controlla." );
		else {
    		// Verifica che l'email non sia già presente
			$result = $db->queryRow(
				"SELECT idanag, descrizione, stato FROM " . $p4a->e3g_prefix . "anagrafiche " .
				" WHERE email = '" . $this->email->getNewValue() . "'" );

			if ( $result )
	  			$this->message->setValue( "Un utente con l'indirizzo che hai indicato risulta gia' registrato." );
	  		else {
	    		// ucwords capitalizza la stringa
	    		$nome = str_replace( "'", "\'", ucwords( strtolower( $this->nome->getNewValue() ) ) );
	    		$cognome = str_replace( "'", "\'", ucwords( strtolower( $this->cognome->getNewValue() ) ) );
	    		$note = str_replace( "'", "\'", $this->messaggio->getNewValue() );
	    		$indirizzo = str_replace( "'", "\'", $this->indirizzo->getNewValue() );
	    		$localita = str_replace( "'", "\'", $this->localita->getNewValue() );
	    		$cap = str_replace( "'", "\'", $this->cap->getNewValue() );
	    		$provincia = str_replace( "'", "\'", $this->provincia->getNewValue() );
	    		$telefono = str_replace( "'", "\'", $this->telefono->getNewValue() );
	    		$fax = str_replace( "'", "\'", $this->fax->getNewValue() );
	    		$cf = str_replace( "'", "\'", $this->cf->getNewValue() );
	    		$data_nascita = str_replace( "'", "\'", $this->data_nascita->getNewValue() );
	    		$luogo_nascita = str_replace( "'", "\'", $this->luogo_nascita->getNewValue() );
	
	  			// Si prepara i campi necessari
	  			$result = $db->queryOne( "SELECT MAX( idanag ) FROM $p4a->e3g_prefix" . "anagrafiche" );
	  			$idanag = 1 + $result;
	
	  			// Cerca il primo codice libero
	  			$i_codice = $idanag;
	  			do {
	  				$codice = 'C' . sprintf( "%04d", $i_codice++ );
	  				$result = $db->queryOne( "SELECT idanag FROM $p4a->e3g_prefix" . "anagrafiche WHERE codice = '$codice' " );
	  			} while ( $result );
	
	  			$new_pass = e3g_get_pass();
	
	  			// Inserisce nuovo utente con stato = 0 = 'In attesa'
	  			$db->query( "INSERT $p4a->e3g_prefix" . "anagrafiche " .
	  				"       ( idanag, codice, email, nome, cognome, descrizione, indirizzo, localita, cap, provincia, telefono, fax, cf, data_nascita, luogo_nascita, note, tipoutente, tipocfa, password, admin, stato, data_ins ) " .
	  				"VALUES ( '$idanag', '$codice', '$email', '$nome', '$cognome', '$nome $cognome', '$indirizzo', '$localita', '$cap', '$provincia', '$telefono', '$fax', '$cf', '$data_nascita', '$luogo_nascita', '$note', 'U', 'C', '" . md5($new_pass) . "', 'N', 0, NOW() )" );
	
	  			// Invia email al richiedente quale conferma della richiesta
	  			$corpo = "Salve $nome $cognome,\n\n" .
	  		    	"qualcuno (probabilmente tu) ha richiesto un nuovo account per accedere a $p4a->e3g_nome_sw" .
	            		", il software gestionale di $p4a->e3g_azienda_rag_soc.\n\n" .
		  			"L'amministratore ne e' stato informato e provvedera' ad abilitarti entro qualche giorno; quando l'avra' fatto ti arrivera' un messaggio di conferma.\n\n" .
		  			"Questi sono i dati che dovrai utilizzare per l'accesso:\n\n" .
		  			"- indirizzo e-mail: $email\n" .
		  			"- password: $new_pass\n\n" .
		  			"Se non hai richiesto alcun account non preoccuparti: cestina pure questo messaggio.";
				if ( !e3g_invia_email( "$p4a->e3g_nome_sw: conferma richiesta nuovo account",
	  					 $corpo, $email, "$nome $cognome" ) )
	        	{
	          		$this->message->setIcon( "error" );
	  				$this->message->setValue( "Si e' verificato un errore durante la spedizione del messaggio di conferma." );
	  			}
	  			else {
	    			// Invia email all'admin per avvisare della presenza di una richiesta pendente
	    			$nome_admin = e3g_get_name_admin();
	    			$corpo = "Salve $nome_admin,\n\n" .
						"la seguente persona ha richiesto un nuovo account per accedere a " .
		    				"$p4a->e3g_nome_sw, il software gestionale di $p4a->e3g_azienda_rag_soc.\n\n" .
		      			"- nome: $nome\n" .
		      			"- cognome: $cognome\n" .
		      			"- e-mail: $email\n" .
		      			"- messaggio: $note\n\n" .
		      			"Il suo nome e' gia' stato inserito nell'anagrafica degli utenti ed e' in attesa di tua abilitazione.";
		
		    		if ( !e3g_invia_email( "$p4a->e3g_nome_sw: notifica richiesta nuovo account",
		    				$corpo, e3g_get_email_admin(), $nome_admin ) )
					{
		            	$this->message->setIcon( "error" );
		    			$this->message->setValue( "Si e' verificato un errore durante la spedizione del messaggio all'amministratore." );
		    		}
		    		else {
		            	$this->message->setIcon( "info" );
		      			$this->message->setValue( "Conferma richiesta nuovo account: l'amministratore e' stato informato della tua richiesta.<br />" .
							"Appena ti avra' abilitato, riceverai un messaggio e-mail di conferma." );
		            	$this->bu_signup->disable();
		      		}
	      		
	    		}
	
	  		}

		}

	}


	// -------------------------------------------------------------------------
  function email_click()
	// -------------------------------------------------------------------------
	{
		$this->setFocus($this->email2);
	}
	
}


?>
