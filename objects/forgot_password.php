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


require_once( dirname(__FILE__) . '/../libraries/e3g_utils.php' );
require_once( dirname(__FILE__) . '/../config.php' );


class forgot_password extends P4A_Mask
{

	// -------------------------------------------------------------------------
	function forgot_password()
	// -------------------------------------------------------------------------
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();

		$this->setTitle( e3g_title() );

	    // Immagine logo (come box HTML anzichè p4a_image altrimenti non si centra)
		if ( E3G_TIPO_GESTIONE == 'G' )
			$src_logo = 'images/gestigas_01.jpg';
		else
			$src_logo = 'images/equogest_01.jpg';
		$this->build("p4a_box", "box_logo");
		$this->box_logo->setValue( '<div align="center"><img src="' . $src_logo .
			'" alt="Progetto e3g - Equogest/GestiGAS" /></div>' );


		$this->build("p4a_label", "lbl_intro");
	    $this->lbl_intro->setValue( "Scrivi il tuo " . ( E3G_TIPO_GESTIONE == 'G' ? "indirizzo e-mail" : "nome utente" ) .
			" che avevi usato per registrarti e cosi' facendo:<br /><br />" .
			"1. verra' eliminata la tua attuale password<br />" .
			"2. ne verra' generata una nuova<br />" .
			"3. la password nuova ti sara' spedita via posta elettronica." );
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
		
		// Pulsante Login
		$this->build("p4a_button", "bu_invia_password");
		$this->bu_invia_password->setWidth("300");
		$this->bu_invia_password->setLabel("Inviami la password per e-mail");
		$this->bu_invia_password->setIcon( "mail_forward" );
		$this->bu_invia_password->addAction("onClick");
		$this->intercept($this->bu_invia_password, "onClick", "bu_invia_password_click");

		//Fieldset
		$this->build("p4a_fieldset", "fs_forgot_password");
		$this->fs_forgot_password->setWidth(700);
		$this->fs_forgot_password->setTitle("Invia i miei dati per e-mail");
 		$this->fs_forgot_password->anchor($this->lbl_intro);
		$this->fs_forgot_password->anchor($this->message);
 		$this->fs_forgot_password->anchor($this->email);
		$this->fs_forgot_password->anchorRight($this->bu_invia_password);


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
		$frm->anchorCenter($this->fs_forgot_password);
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
	function bu_invia_password_click()
	// -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();

		$this->email->setNewValue( strtolower(trim($this->email->getNewValue())) );
		
		if ( $this->email->getNewValue() == '' ) 
			$this->message->setValue( "Scrivere l'indirizzo e-mail con cui ci si era registrati." );
		else {
			$result = $db->queryRow( 
				"SELECT idanag, descrizione, stato " .
				"  FROM " . $p4a->e3g_prefix . "anagrafiche " .
				" WHERE LCASE( email ) = '" . addslashes( $this->email->getNewValue() ) . "'" .
                "   AND tipocfa = 'C'" );

			if ( $result ) {  // indirizzo e-mail trovato

				switch ( $result[ "stato" ] )
		      	{
					case 0:  // In attesa
						$this->message->setValue( "Utente in attesa di abilitazione." );
						break;
					case 1:  // Abilitato
						$id_anagrafica = $result[ "idanag" ];
						$nome = $result[ "descrizione" ];
					
						// Generazione di una nuova password
						$new_pass = e3g_get_pass();
                        
						// Sostituzione della vecchia password con la nuova
                        $db->query( "UPDATE " . $p4a->e3g_prefix . "anagrafiche " .
							"   SET password = '" . md5($new_pass) . "' " .
							" WHERE idanag = $id_anagrafica " );

						// Invio della nuova password per email
						$corpo  = "Salve $nome,\n\n";
						$corpo .= "qualcuno (probabilmente tu) ha richiesto una nuova password per accedere a " .
							"$p4a->e3g_nome_sw, il software gestionale di $p4a->e3g_azienda_rag_soc.\n\n";
						$corpo .= "- il tuo indirizzo e-mail e': " . $this->email->getNewValue() . "\n";
						$corpo .= "- la tua nuova password e': $new_pass\n\n";
						$corpo .= "Se non hai richiesto questa password non preoccuparti, solo tu puoi vedere questo messaggio, " .
							"ma la dovrai comunque usare per effettuare il prossimo login.\n\n";
						$corpo .= "Se hai bisogno di aiuto, puoi contattare l'amministratore del sito ";
						$corpo .= e3g_get_name_email_admin() . ".";
						
						if ( !e3g_invia_email( "$p4a->e3g_nome_sw: nuova password di accesso",
							$corpo, $this->email->getNewValue(), $nome ) )
						{
							$this->message->setIcon( "error" );
							$this->message->setValue( "Si e' verificato un errore durante la spedizione del tuo messaggio." );
							exit;
						}
						else {
							// Visualizzazione di un messaggio di conferma
							$this->message->setIcon( "info" );
							$this->message->setValue( "Riceverai tra poco per e-mail un messaggio contenente la tua nuova password. " .
								"Utilizzala per farti riconoscere ed accedere a $p4a->e3g_nome_sw." );
							$this->bu_invia_password->disable();
						}
						
						break;
					case 2:  // Disabilitato
						$this->message->setValue( "Utente presente, ma disabilitato." );
						break;
				}
					
			}
			else  // email errata
				$this->message->setValue( "Non risulta alcun utente con indirizzo '" . $this->email->getNewValue() . "'." );
		}

	}


	// -------------------------------------------------------------------------
	function email_click()
	// -------------------------------------------------------------------------
	{
		$this->setFocus( $this->pwd );
	}

}


?>
