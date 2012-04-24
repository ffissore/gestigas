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


class login extends P4A_Mask
{

	// -------------------------------------------------------------------------
	function login()
	// -------------------------------------------------------------------------
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();

		e3g_verifica_connessione_db();

		// Determina il tipo di gestione (singola o multipla) ------------------
		$n_aziende = $db->queryOne( "SELECT COUNT( * ) FROM _aziende" );
    
	    switch ( $n_aziende ) {
	    	case 0:
		    	// Situazione anomala: mancano i dati del gas/bottega, mentre ci deve
				// sempre essere almeno un record in _aziende
				// per ora inviamo una segnalazione di errore
				exit( "Situazione anomala: mancano i dati del gas/bottega (tabella _aziende non impostata..." );
				
				break;
				
			case 1:
		    	// MONO-GESTIONE (-> abbiamo una sola riga in _aziende)
		    	$result = $db->queryRow("SELECT * FROM _aziende");
		
		      	$p4a->e3g_prefix = $result["prefix"];
		
				e3g_update_var_azienda();
                e3g_cron();
				$show_new_account = $result[ "show_new_account" ];
	
				break;
				
			default:
		    	// MULTI-GESTIONE
		    	// Il prefisso dovrebbe stare in coda all'url .../index.php?prefix=retegas_
		    	// ma se manca, si cercherà di determinarlo a posteriori
		    	
		    	$result = $db->queryRow("SELECT * FROM _aziende " .
		    		"WHERE prefix='" . (isset( $_GET["prefix"] ) ? $_GET["prefix"] : "*") . "'");
		
				if ( $result ) {
					// Prefisso trovato, tutto regolare
			      	$p4a->e3g_prefix = $result["prefix"];
	
					e3g_update_var_azienda();

                    if ( E3G_LOGIN_CRON ) e3g_cron();
                        
					$show_new_account = $result[ "show_new_account" ];
				}
				else {
					// Prefisso assente (o errato)
					// Ricercherà l'email di chi si sta connettendo in tutte le gestioni
					// e quindi determinerà a posteriori il prefisso
					// Questa situazione è riconoscibile in bu_entra_click() dal fatto
					// che ($p4a->e3g_prefix) non è settata
	
					$show_new_account = false;
				}
		
				break;
	    }

	
		$this->setTitle( e3g_title() );

	
		// Immagine logo (come box HTML anzichè p4a_image altrimenti non si centra)
		if ( E3G_TIPO_GESTIONE == 'G' )
			$src_logo = 'images/gestigas_01.jpg';
		else
			$src_logo = 'images/equogest_01.jpg';
		$this->build("p4a_box", "box_logo");
		$this->box_logo->setValue( '<div align="center"><img src="' . $src_logo .
			'" alt="Progetto e3g - Equogest/GestiGAS" /></div>' );
	
	
		// LOGIN ---------------------------------------------------------------
		$this->build("p4a_label", "lbl_intro_login");
		$this->lbl_intro_login->setValue( "Accedi a <strong>$p4a->e3g_nome_sw</strong> " .
			"utilizzando i tuoi e-mail e password <em>(i cookies devono essere abilitati nel tuo browser)</em>:" );
		$this->lbl_intro_login->setWidth("650");
		
		// Message per eventuale segnalazione di errori
		$message =& $this->build("p4a_message", "message");
		$message->setWidth("650");
		
		// Campo email
		$this->build( "p4a_field", "email" );
		$this->email->setLabel( E3G_TIPO_GESTIONE == 'G' ? "E-mail:" : "Nome utente:" );
		$this->email->setWidth( 250 );
		$this->email->addAction( "onReturnPress" );
		$this->intercept( $this->email, "onReturnPress","bu_entra_click" );
		
		// Campo Password 
		$this->build( "p4a_field", "pwd" );
		$this->pwd->setLabel( "Password:" );
		$this->pwd->setType( "password" );
		$this->pwd->setWidth( 250 );
		$this->pwd->addAction( "onReturnPress" );
		$this->intercept( $this->pwd, "onReturnPress", "bu_entra_click" );
		
		// Pulsante Login
		$this->build("p4a_button", "bu_entra");
		$this->bu_entra->setWidth("300");
		$this->bu_entra->setLabel("Entra in $p4a->e3g_nome_sw");
		$this->bu_entra->setIcon( "login" );
		$this->bu_entra->addAction("onClick");
		$this->intercept($this->bu_entra, "onClick", "bu_entra_click");
		
		//Fieldset
		$this->build("p4a_fieldset", "fs_login");
		$this->fs_login->setWidth(700);
		$this->fs_login->setTitle("Ciao, sei ritornato?");
		$this->fs_login->anchor($this->lbl_intro_login);
		$this->fs_login->anchor($this->message);
		$this->fs_login->anchor($this->email);
		$this->fs_login->anchor($this->pwd);
		$this->fs_login->anchorRight($this->bu_entra);
		
		
		// PASSWORD DIMENTICATA ------------------------------------------------
		$this->build("p4a_button", "bu_forgot_password");
		$this->bu_forgot_password->setWidth("300");
		$this->bu_forgot_password->setLabel("Inviami la password per e-mail");
		$this->bu_forgot_password->setIcon( "mail_forward" );
		$this->bu_forgot_password->addAction("onClick");
		$this->intercept($this->bu_forgot_password, "onClick", "bu_forgot_password_click");
		
		//Fieldset
		$this->build("p4a_fieldset", "fs_forgot_password");
		$this->fs_forgot_password->setWidth(700);
		$this->fs_forgot_password->setTitle("Hai dimenticato la password?");
		$this->fs_forgot_password->anchorRight($this->bu_forgot_password);
	
		// RICHIESTA NUOVO ACCOUNT (parte opzionale) ---------------------------
		$this->build("p4a_label", "lbl_signup");
		$this->lbl_signup->setValue( "Benvenuto. Per un pieno accesso a $p4a->e3g_nome_sw" .
			" hai bisogno di un minuto per richiedere un nuovo account per te." );
		$this->lbl_signup->setWidth("650");
		
		$this->build("p4a_button", "bu_signup");
		$this->bu_signup->setWidth("300");
		$this->bu_signup->setLabel("Inizia adesso a creare un nuovo account");
		$this->bu_signup->setIcon( "login_sign" );
		$this->bu_signup->addAction("onClick");
		$this->intercept($this->bu_signup, "onClick", "bu_signup_click");
		
		//Fieldset
		$this->build("p4a_fieldset", "fs_signup");
		$this->fs_signup->setWidth(700);
		$this->fs_signup->setTitle("E' la prima volta che hai accesso qui?");
		$this->fs_signup->anchor($this->lbl_signup);
		$this->fs_signup->anchorRight($this->bu_signup);
		
		
		// Frame principale ----------------------------------------------------
		$frm=& $this->build("p4a_frame", "frm");
		$frm->setWidth(730);
	
		$frm->anchorCenter($this->box_logo);
		$frm->anchorCenter($this->fs_login);

		if ( $p4a->e3g_prefix )
			// Per semplicità per ora non dà la possibilità di recuperare la pass
			// se non si è specificato il prefisso, ma in realtà è possibile
			// farlo ugualmente, con la tecnica usata per il login
			// TODO  
			$frm->anchorCenter($this->fs_forgot_password);
			
		if ( $show_new_account )
			$frm->anchorCenter($this->fs_signup);
		
		e3g_scrivi_footer( $this, $frm );
		
		// Display
		$this->display("main", $frm);
			
		if ( STATO_DEBUG )
			$this->setFocus($this->email);
	}

	
	// -------------------------------------------------------------------------
	function main()
	// -------------------------------------------------------------------------
	{
		parent::main();
	}
	
	
	// -------------------------------------------------------------------------
	function bu_entra_click()
	// -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();

		$this->email->setNewValue( strtolower($this->email->getNewValue()) );
		
		if ( $this->email->getNewValue() == '' ) 
			$this->message->setValue( "Scrivere l'indirizzo e-mail con cui ci si era registrati." );
		elseif ( $this->pwd->getNewValue() == '' ) 
			$this->message->setValue( "Scrivere anche la password." );
		else {
			
			if ( !$p4a->e3g_prefix ) { 
				// Se il prefisso non è settato, allora siamo in multigestione e non 
				// è stato indicato in coda all'url

				// Scorre la tabella _aziende e prova i vari prefissi
				$this->build("p4a_db_source", "ds_aziende");
				$this->ds_aziende->setTable( "_aziende" );
				$this->ds_aziende->load();
				$this->ds_aziende->firstRow();
				
				for ( $n_riga = 1; $n_riga <= $this->ds_aziende->getNumRows(); $n_riga++ )
				{
					$result = $db->queryRow( 
						"SELECT idanag, password, stato " . 
						"  FROM " . $this->ds_aziende->fields->prefix->getValue() . "anagrafiche " .
						" WHERE email = '" . $this->email->getNewValue() . "'" );
					
                  if ( $result )       
						break;
					else
						$this->ds_aziende->nextRow();
				}
			
				if ( $result )  
					$p4a->e3g_prefix = $this->ds_aziende->fields->prefix->getValue();
			
			}
			else { 
				// Siamo in multigestione e c'è l'indicazione del prefisso
				$result = $db->queryRow( 
					"SELECT idanag, password, stato " .
					"  FROM " . $p4a->e3g_prefix . "anagrafiche " .
					" WHERE email = '" . $this->email->getNewValue() . "'" );
            }
	

			// Indirizzo e-mail trovato ----------------------------------------
            if ( $result )    {   
			
				if ( $result[ "password" ] == $this->pwd->getNewValue() )
				{
					// password corretta
					switch ( $result[ "stato" ] )
					{
						case 0:  // In attesa
							$this->message->setValue( "Utente in attesa di abilitazione." );
							break;

						case 1:  // Abilitato -> OK, si esegue la connessione...
							e3g_update_var_utente( $result[ "idanag" ] );
							e3g_update_var_azienda();
							
							if ( $p4a->e3g_azienda_db_multi_versione >= '0010' ) {
								// Legge dati del precedente login
								$result = $db->queryRow( 
									"SELECT last_login, " .
									"       DATE_FORMAT( last_login, '%d/%m/%Y alle %H:%i' ) AS desc_last_login " .
									"  FROM " . $p4a->e3g_prefix . "anagrafiche " .
									" WHERE email = '" . $this->email->getNewValue() . "'" );
								$p4a->e3g_utente_last_login = $result[ "last_login" ];							
								$p4a->e3g_utente_desc_last_login = $result[ "desc_last_login" ];							

								// Aggiorna dati di login
								$result = $db->query( 
									"UPDATE " . $p4a->e3g_prefix . "anagrafiche " .
									"   SET n_login = n_login + 1, " .
									"       last_login = NOW() " .
									" WHERE email = '" . $this->email->getNewValue() . "'" );
							}
							
							// Avvia e mostra la finestra principale
							$e3g_main =& e3g_main::singleton();
				   			$e3g_main->inizializza();
							break;

						case 2:  // Disabilitato
							$this->message->setValue( "Utente disabilitato." );
						  break;
					}
				}
				else
					// password errata
					$this->message->setValue( "Password errata, riprova." );
			}
			else
				// email errata
				$this->message->setValue( "Non risulta alcun utente con indirizzo '" . $this->email->getNewValue() . "'." );
		}

	}


	// -------------------------------------------------------------------------
	function bu_forgot_password_click()
	// -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();

		$p4a->openMask("forgot_password");
	}


	// -------------------------------------------------------------------------
	function bu_signup_click()
	// -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();

		$p4a->openMask("signup");
	}


	// -------------------------------------------------------------------------
	function pwd_click()
	// -------------------------------------------------------------------------
	{
		// commentato perchè con il campo Type('password') mi riempie il campo di *
		//$this->setFocus($this->bu_entra);
	}
	

}


?>