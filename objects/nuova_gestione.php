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

class nuova_gestione extends P4A_Mask
{
	function nuova_gestione()
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();

		
		if ( E3G_TIPO_GESTIONE == 'G' ) 
			$this->setTitle('Creazione database per nuova gestione G.A.S.');
		else 
			$this->setTitle('Creazione database per nuova gestione Bottega');


		// ------------------------------------------------------------- Toolbar
		$this->build("p4a_quit_toolbar", "toolbar");


		// ------------------------------------------------------------- Message
		$message =& $this->build("p4a_message", "message");
		$message->setWidth("700");
		$message->setIcon("warning");
			

		// -------------------------------------------------------- Dati azienda
		$fld_rag_soc=& $this->build("p4a_field", "fld_rag_soc");
		$fld_rag_soc->label->setWidth(150);
		$fld_rag_soc->setWidth("200");
		if ( E3G_TIPO_GESTIONE == 'G' ) {
			$fld_rag_soc->setLabel('Nome G.A.S.');
			$fld_rag_soc->setValue("nome del G.A.S.");
		}
		else {
			$fld_rag_soc->setLabel('Nome Bottega');
			$fld_rag_soc->setValue("nome della Bottega");
		}
			
		$fld_prefix=& $this->build("p4a_field", "fld_prefix");
		$fld_prefix->label->setWidth(150);
		$fld_prefix->setWidth("200");
		$fld_prefix->setLabel('Prefisso tabelle');
		$fld_prefix->setValue("_");
			
		$fld_anno=& $this->build("p4a_field", "fld_anno");
		$fld_anno->label->setWidth(150);
		$fld_anno->setWidth("50");
		$fld_anno->setLabel('Anno');
		$fld_anno->setValue(date("Y"));
			

		$fs_azienda =& $this->build("p4a_fieldset", "fs_azienda");
		$fs_azienda->setWidth(730);
		$fs_azienda->setTitle('Dati ' . ( E3G_TIPO_GESTIONE == 'G' ? 'G.A.S.' : 'Bottega' ) );
		$fs_azienda->anchor( $fld_rag_soc );
		$fs_azienda->anchor( $fld_prefix );
		$fs_azienda->anchor( $fld_anno );


		// ------------------------------------------------- Dati amministratore
		$fld_admin_nome=& $this->build("p4a_field", "fld_admin_nome");
		$fld_admin_nome->label->setWidth(150);
		$fld_admin_nome->setWidth("200");
		$fld_admin_nome->setLabel('Nome');
		$fld_admin_nome->setValue("Mario");
			
		$fld_admin_cognome=& $this->build("p4a_field", "fld_admin_cognome");
		$fld_admin_cognome->label->setWidth(150);
		$fld_admin_cognome->setWidth("200");
		$fld_admin_cognome->setLabel('Cognome');
		$fld_admin_cognome->setValue("Rossi");
			
		$fld_admin_email=& $this->build("p4a_field", "fld_admin_email");
		$fld_admin_email->label->setWidth(150);
		$fld_admin_email->setWidth("200");
		$fld_admin_email->setLabel('Indirizzo e-mail');
		$fld_admin_email->setValue("admin@admin.it");

			
		$fs_admin =& $this->build("p4a_fieldset", "fs_admin");
		$fs_admin->setWidth(730);
		$fs_admin->setTitle('Amministratore');
		$fs_admin->anchor( $fld_admin_nome );
		$fs_admin->anchor( $fld_admin_cognome );
		$fs_admin->anchor( $fld_admin_email );


		// Campi Obbligatori Fields
	    $this->mf = array( "fld_rag_soc", "fld_prefix", "fld_anno", "fld_admin_nome", "fld_admin_cognome", "fld_admin_email" );
		foreach ( $this->mf as $mf )
			$this->$mf->label->setFontWeight("bold");

		// ------------------------------------------------------ Bottone "Crea"
		$this->build("p4a_button", "bu_crea_db");
		$this->bu_crea_db->setLabel("Crea database...");
		$this->bu_crea_db->addAction("onClick");
		$this->bu_crea_db->setIcon( "new" );
		$this->intercept($this->bu_crea_db, "onClick", "bu_crea_db_click");
		$this->bu_crea_db->requireConfirmation( "onClick", 
			"Confermi la creazione di una nuova gestione '$p4a->e3g_nome_sw' ? (l'operazione NON e' reversibile)" );
			
			
		// ---------------------------------------------------- Frame principale
		$frm=& $this->build("p4a_frame", "frm");
		$frm->setWidth(730);
	
		$frm->anchor($message);
		$frm->anchor($this->fs_azienda);
		$frm->anchor($this->fs_admin);
		$frm->anchor($this->bu_crea_db);
			
		e3g_scrivi_footer( $this, $frm );

		// Display
		$this->display("main", $frm);
		$this->display("menu", $p4a->menu);
		$this->display("top", $this->toolbar);
	}
		

	function main()
	{
		parent::main();

		$this->fld_rag_soc->unsetStyleProperty( "border" );
		$this->fld_prefix->unsetStyleProperty( "border" );

		foreach($this->mf as $mf)
			$this->$mf->unsetStyleProperty("border");

		$this->bu_crea_db->enable();
	}

	
	function bu_crea_db_click()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
		$error_text = "";
	
		// Verifica campi obbligatori
		foreach ( $this->mf as $mf ) {
			$value = $this->$mf->getNewValue();
			if (trim($value) === "") {
				$this->$mf->setStyleProperty("border", "1px solid red");
				$error_text = "Compilare i campi obbligatori (" .$this->$mf->getLabel()  . ")";
			}
		}
		
		// Prefisso: mette in minuscolo, rimuove gli eventuali spazi iniziali e
		// finali, aggiunge (se manca) _ finale 
		$prefix = trim( strtolower( $this->fld_prefix->getNewValue() ) );
		if ( $prefix <> "" and $prefix[ strlen($prefix)-1 ] <> '_' )
			$prefix .= '_'; 
		
		// Verifica correttezza campo prefisso
		// (solo lettere, numeri e _
		if ( $error_text == '' and !ereg( "^[a-zA-Z0-9_]+$", $prefix ) )
		{
			$this->fld_prefix->setStyleProperty( "border", "1px solid red" );
			$error_text = "Nome prefisso non valido (solo lettere minuscole, numeri e _)";
		}

		// Verifica univocità del prefisso
		if ( $error_text == '' )
		{
			$result = $db->queryOne( "SELECT COUNT(*) FROM _aziende WHERE prefix = '" . $prefix . "'" );
			
			if ( $result > 0 )
			{
				$this->fld_prefix->setStyleProperty( "border", "1px solid red" );
				$error_text = "Nome prefisso non univoco: sceglierne uno diverso";
			}
		}

		// Verifica email
		$this->fld_admin_email->setNewValue( strtolower( $this->fld_admin_email->getNewValue() ) );
		if ( $error_text == '' and !e3g_email_valido($this->fld_admin_email->getNewValue()) ) 
		{
			$this->fld_admin_email->setStyleProperty( "border", "1px solid red" );
			$error_text = "L'indirizzo e-mail indicato non sembra essere valido.";
		}


		// Controllo esistenza script
		if ( $error_text == '' )
		{
			$nome_file = dirname(__FILE__) . "/../db/" .
				( E3G_TIPO_GESTIONE == 'G' ? 'gestigas' : 'equogest' ) . "_db_init_multi.sql";

			if ( !file_exists($nome_file) )
				$error_text = "Creazione impossibile causa mancanza del file '$nome_file'";
		}

		
		// --------------------------------------------------- Creazione tabelle
		if ( $error_text == '' )
		{
			$idfile = fopen( $nome_file, "r" )
				or exit( "Impossibile leggere il file ($nome_file)" );
			$dati = file( $nome_file );

			foreach ( $dati as $riga ) {
				if ( trim($riga) != "" ) {
					$strdata = str_replace( "[PREFIX]", $prefix, $riga );
					$db->query( $strdata );
				}
			}
			fclose( $idfile );
			
			$db->query( 
				"UPDATE _aziende " .
				"   SET rag_soc        = '" . $this->fld_rag_soc->getNewValue() . "', " .
				"       anno_contabile = '" . $this->fld_anno->getNewValue() . "' " .
				" WHERE prefix = '$prefix'" );

			$db->query( 
				"UPDATE " . $prefix . "anagrafiche " .
				"   SET nome        = '" . $this->fld_admin_nome->getNewValue() . "', " .
				"       cognome     = '" . $this->fld_admin_cognome->getNewValue() . "', " .
				"       descrizione = '" . $this->fld_admin_nome->getNewValue() . " " . $this->fld_admin_cognome->getNewValue() . "', " .
				"       email       = '" . $this->fld_admin_email->getNewValue() . "' " .
				" WHERE tipoutente = 'AS' AND stato = 1" ); 
				
			// Verifica che l'operazione sia andata a buon fine
			$dbver = $db->queryOne( "SELECT dbver FROM _aziende WHERE prefix = '$prefix'" );				
			if ( !is_string($dbver) ) {
				$error_text = "Creazione tabelle nuova gestione avvenuta con ERRORI";
			}
		}
		
		
		if ( $error_text == '' )
		// ---------------------------------- Aggiornamento delle tabelle create
		{
			$result = (array)e3g_aggiorna_database( $prefix );
			
			if ( $result[0] )
			{
				$this->message->setValue( "Creazione nuova gestione eseguita con successo." );  				
				$this->message->setIcon("info");
			}
			else
				$error_text = "Creazione tabelle nuova gestione avvenuta, ma...<br />" . $result[1];

			// Il bottone è comunque da disabilitare, perchè anche se l'esito 
			// dell'aggiornamento è fallito, le tabelle sono state create
			$this->bu_crea_db->disable();
		}
		else
		// ------------------------------------------------ Situazione di ERRORE
		{
			$this->fld_prefix->setNewValue( $prefix );
			$this->message->setValue( $error_text );
		}
		
	}

}
?>
