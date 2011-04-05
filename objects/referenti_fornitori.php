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


class referenti_fornitori extends P4A_Mask
{
	function referenti_fornitori ()
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();


		$this->SetTitle( "Relazioni tra Referenti e Fornitori" );

		
		// ------------------------------------------------ DB source principale
		$this->build( "p4a_db_source", "ds_ref" );
        $this->ds_ref->setFields( array( 
            $p4a->e3g_prefix . "referenti.*", 
            "u.descrizione AS referente",
            "f.descrizione AS fornitore" ) );
		$this->ds_ref->setTable( $p4a->e3g_prefix . "referenti" );
        $this->ds_ref->addJoin( $p4a->e3g_prefix . "anagrafiche u", "u.codice = " . $p4a->e3g_prefix . "referenti.codanag" );
        $this->ds_ref->addJoin( $p4a->e3g_prefix . "anagrafiche f", "f.codice = " . $p4a->e3g_prefix . "referenti.codfornitore" );
		$this->ds_ref->setPk( "idtable" );
        $this->ds_ref->addOrder( "codanag" );
        $this->ds_ref->addOrder( "codfornitore" );
		$this->ds_ref->load();

		$this->setSource( $this->ds_ref );
		$this->ds_ref->firstRow();

		// Fields properties
		$fields =& $this->fields;

		// Campi Obbligatori Fields
	    $this->mf = array( "codanag", "codfornitore" );
		foreach ( $this->mf as $mf )
			$fields->$mf->label->setFontWeight("bold");


		// ----------------------------------------------------- Altri DB source
        // Utenti
        $this->build( "p4a_db_source", "ds_anag_ref" );
        $this->ds_anag_ref->setTable( $p4a->e3g_prefix . "anagrafiche" );
        $this->ds_anag_ref->setWhere( "( tipocfa = 'C' AND ( tipoutente = 'AS' OR tipoutente = 'R' ) AND stato = 1 ) OR idanag = 0" );
        $this->ds_anag_ref->setPk("codice");
        $this->ds_anag_ref->addOrder("descrizione");
        $this->ds_anag_ref->load();

        // Fornitori
		$this->build( "p4a_db_source", "ds_anag_forn" );
		$this->ds_anag_forn->setTable( $p4a->e3g_prefix . "anagrafiche" );
		$this->ds_anag_forn->setWhere( "( tipocfa = 'F' AND stato = 1 )  OR idanag = 0" );
		$this->ds_anag_forn->setPk( "codice" );
		$this->ds_anag_forn->addOrder( "descrizione" );
		$this->ds_anag_forn->load();
		
		
		// ------------------------------------------------------------- Toolbar
		$this->build( "p4a_standard_toolbar", "toolbar" );
		$this->toolbar->setMask( $this );


        // ------------------------------------------------------------- Tabella
        $table =& $this->build("p4a_table", "table");
        $table->setWidth( E3G_TABLE_WIDTH );
        $table->setSource($this->ds_ref);
        $table->setVisibleCols( array( "referente", "fornitore" ) );
        
        $table->cols->referente->setOrderable( false );
        $table->cols->fornitore->setOrderable( false );
        
        
        // ------------------------------------------------------------- Message
        $message =& $this->build( "p4a_message", "message" );
        $message->setWidth( 400 );


		// --------------------------------------------------------------- Campi
		$fields->codanag->setLabel( "Referente" );
		$fields->codanag->setType( "select" );
		$fields->codanag->setSource( $this->ds_anag_ref );
		$fields->codanag->setSourceValueField( "codice" );
		$fields->codanag->setSourceDescriptionField( "descrizione" );
		$fields->codanag->setWidth( 250 );
	
		$fields->codfornitore->setLabel( "Fornitore" );
		$fields->codfornitore->setType( "select" );
		$fields->codfornitore->setSource( $this->ds_anag_forn );
		$fields->codfornitore->setSourceValueField( "codice" );
		$fields->codfornitore->setSourceDescriptionField( "descrizione" );
		$fields->codfornitore->setWidth( 250 );


        $fset=& $this->build( "p4a_fieldset", "frame" );
        $fset->setTitle( "Dettaglio" );
        $fset->setWidth( E3G_FIELDSET_DATI_WIDTH );
        $fset->anchor( $this->fields->codanag );
        $fset->anchorLeft( $this->fields->codfornitore );


        // ---------------------------------------------------- Frame principale
		$frm=& $this->build( "p4a_frame", "frm" );
		$frm->setWidth( E3G_MAIN_FRAME_WIDTH );
        $frm->anchor( $table );
        $frm->anchor( $message );
		$frm->anchor( $fset );

		e3g_scrivi_footer( $this, $frm );

  		// Display
		$this->display("main", $frm);
		$this->display("menu", $p4a->menu);
		$this->display("top", $this->toolbar);
	}
	
	
	function main()
	{
		parent::main();

		foreach ( $this->mf as $mf )
			$this->fields->$mf->unsetStyleProperty("border");
	}


	function newRow()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
		parent::newRow();
		
		$this->fields->codanag->setNewValue( "00" );
		$this->fields->codfornitore->setNewValue( "00" );
	}


	function saveRow()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
	
		$error_text = "";

		// Verifica campi obbligatori
		foreach ( $this->mf as $mf ) {
			$value = $this->fields->$mf->getNewValue();
			if ( trim($value) === "" ) {
				$this->fields->$mf->setStyleProperty( "border", "1px solid red" );
				$error_text = "Compilare i campi obbligatori.";
			}
		}
	
	
		// Verifica compilazione referente e fornitore diverso dal predefinito
		if ( $error_text == '' and
		     ( $this->fields->codanag->getNewValue() == '00' OR $this->fields->codfornitore->getNewValue() == '00' ) ) 
		{
			$error_text = "Selezionare sia un referente che un fornitore.";
		}


		// Assegnazione chiave primaria
		if ( $error_text == '' and 
		     !is_numeric($this->fields->idtable->getNewValue()) )
		{
			$idnuovo = $db->queryOne( "SELECT MAX(idtable) FROM " . $p4a->e3g_prefix . "referenti" );
			if ( is_numeric($idnuovo) )
				$idnuovo++;
			else
				$idnuovo = 1 ; 
			$this->fields->idtable->setNewValue($idnuovo);
		}
		
	
		// Verifica univocità
		if ( $error_text == '' )
		{
			$esiste_uguale = $db->queryOne( "SELECT idtable FROM " . $p4a->e3g_prefix . "referenti " .
				" WHERE codanag = '" . $this->fields->codanag->getNewValue() . "' " .
				"   AND codfornitore = '" . $this->fields->codfornitore->getNewValue() . "' " .
				"   AND idtable <> " . $this->fields->idtable->getNewValue() );
			if ( is_numeric($esiste_uguale) )
			{
				$error_text = "Esiste gia' una relazione per questa coppia referente/fornitore.";
			}
		}


		if ( $error_text == '' ) 
			parent::saveRow();
		else
			$this->message->setValue( $error_text );
	}

	
}

?>