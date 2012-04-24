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


class periodo_ordini extends P4A_Mask
{
	var $newrecord = false;

	
    // -------------------------------------------------------------------------
	function periodo_ordini()
    // -------------------------------------------------------------------------
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();

		$this->setTitle( "Periodi degli ordini" );
		$this->setIcon( "date" );
		
		// -------------------------------------------- Sorgente dati principale
		$this->build("p4a_db_source", "ds_per");

        $this->ds_per->setFields( array( 
            $p4a->e3g_prefix . "fornitoreperiodo.*", 
            "f.descrizione AS desc_fornitore",
            "a.descrizione AS desc_referente",
            "a.email AS email_referente" ) );
        $this->ds_per->setTable( $p4a->e3g_prefix . "fornitoreperiodo" );
        // Join per visualizzare il fornitore
        $this->ds_per->addJoin( $p4a->e3g_prefix . "anagrafiche AS f", "fornitore = f.codice" );
        // Join per visualizzare il referente
		$this->ds_per->addJoin( $p4a->e3g_prefix . "referenti AS r", "fornitore = r.codfornitore", "LEFT OUTER" );
		$this->ds_per->addJoin( $p4a->e3g_prefix . "anagrafiche AS a", "r.codanag = a.codice", "LEFT OUTER" );
		$this->ds_per->addOrder( "datainizio" );

        $this->ds_per->setPageLimit( $p4a->e3g_utente_db_source_page_limit );
        $this->ds_per->setPk( "idtable" );
		$this->ds_per->load();
		$this->ds_per->firstRow();

		$this->setSource( $this->ds_per );
		
		
		// Fields properties
		$fields =& $this->fields;

		// Campi Obbligatori Fields
	    $this->mf = array( "fornitore", "datainizio", "datafine" );
		foreach ( $this->mf as $mf )
			$fields->$mf->label->setFontWeight("bold");

		// ------------------------------------------------- Altre sorgenti dati
		// Campo codice fornitore
		$this->build( "p4a_db_source", "ds_anagr" );
        $this->ds_anagr->setSelect( "codice, CONCAT_WS( ' / ', descrizione, desc_agg ) AS descrizione" );
		$this->ds_anagr->setTable( $p4a->e3g_prefix . "anagrafiche" );
		$this->ds_anagr->setWhere( "tipocfa = 'F'" );				
		$this->ds_anagr->setPk( "codice" );
		$this->ds_anagr->addOrder( "descrizione" );
		$this->ds_anagr->load();

		// Array per il SI/NO
		$values = array(); 
		$values[] = array("id" => "S", "desc" => "Si");
		$values[] = array("id" => "N", "desc" => "No");
		$array_source =& $this->build("p4a_array_source", "array_source"); 
		$array_source->load($values); 
		$array_source->setPk("id"); 

		// ------------------------------------------------------------- Toolbar
		if ( E3G_TIPO_GESTIONE == 'G' )
		{
           	switch ($p4a->e3g_utente_tipo) {
                case "A":
                case "AS":
					$this->build("p4a_standard_toolbar", "toolbar");
                    break;
				case "R" :
					$this->build("p4a_standard_toolbar", "toolbar");
            		$this->toolbar->buttons->new->setInvisible();
    				$this->toolbar->buttons->delete->setInvisible();
    	            break;
                case "U":
                case "G":
					$this->build("p4a_navigation_toolbar", "toolbar");
    	            break;
            }
		}
		else {
        	$this->build("p4a_standard_toolbar", "toolbar");  // Equogest 
		}
		$this->toolbar->setMask( $this );


		// ---------------------------------------------------- Pannello ricerca
		$this->build( "p4a_db_source", "ds_forn" );
        $this->ds_forn->setSelect( "codice, CONCAT_WS( ' / ', descrizione, desc_agg ) AS descrizione" );
		$this->ds_forn->setTable( $p4a->e3g_prefix."anagrafiche" );
		$this->ds_forn->setWhere( "tipocfa = 'F' OR codice = '00'" );				
		$this->ds_forn->setPk( "codice" );		
		$this->ds_forn->addOrder( "descrizione" );		
		$this->ds_forn->load();
		$this->ds_forn->firstRow();

		$fld_forn=& $this->build( "p4a_field", "fld_forn" );
		$fld_forn->setLabel( 'Fornitore' );
		$fld_forn->label->setWidth( 100 );
		$fld_forn->setType( "select" );
		$fld_forn->setSource( $this->ds_forn );
		$fld_forn->setSourceValueField( "codice" );
		$fld_forn->setSourceDescriptionField( "descrizione" );
		$fld_forn->setWidth( 250 );

		// Tasto attiva filtro
		$this->build("p4a_button", "bu_filtra");
		$this->bu_filtra->setLabel("Filtra");
		$this->bu_filtra->setIcon("find");
        $this->bu_filtra->setSize(16);
        $this->bu_filtra->setWidth(150);
		$this->bu_filtra->addAction("onClick");
		$this->intercept($this->bu_filtra, "onClick", "bu_filtraClick");

		// Tasto annulla filtro
		$this->build("p4a_button", "bu_annulla_filtro");
		$this->bu_annulla_filtro->setLabel("Annulla filtro");
		$this->bu_annulla_filtro->setIcon("cancel");
        $this->bu_annulla_filtro->setSize(16);
        $this->bu_annulla_filtro->setWidth(150);
		$this->bu_annulla_filtro->addAction("onClick");
		$this->intercept($this->bu_annulla_filtro, "onClick", "bu_annulla_filtroClick");

		// Frame
		$fs_ricerca=& $this->build("p4a_fieldset", "fs_ricerca");
		$fs_ricerca->anchor($this->fld_forn);
 		$fs_ricerca->anchorLeft($this->bu_filtra);
		$fs_ricerca->anchorLeft($this->bu_annulla_filtro);		
		$fs_ricerca->setWidth( E3G_FIELDSET_SEARCH_WIDTH );


		// ------------------------------------------------------- Vista tabella	
		$this->build( "p4a_table", "table" );
 		$this->table->setWidth( E3G_TABLE_WIDTH );
		$this->table->setSource( $this->ds_per );
		$this->table->setVisibleCols( array("desc_fornitore", "datainizio", "datafine", "ricorsivo", "desc_referente", "email_referente") );
		$this->intercept( $this->table->rows, "afterClick", "table_click" );
        $this->intercept( $this->table->rows, "beforeDisplay", "table_beforeDisplay" );  

		$this->table->cols->desc_fornitore->setLabel( "Fornitore" );
        $this->table->cols->datainizio->setLabel( "Dalla data" );
        $this->table->cols->datafine->setLabel( "Alla data" );
        $this->table->cols->ricorsivo->setLabel( "Annuale" );
        $this->table->cols->desc_referente->setLabel( "Referente" );
        $this->table->cols->email_referente->setLabel( "e-mail referente" );

//      $this->table->cols->fornitore->setWidth();  per differenza
        $this->table->cols->datainizio->setWidth( 80 );
        $this->table->cols->datafine->setWidth( 80 );
        $this->table->cols->ricorsivo->setWidth( 60 );
        $this->table->cols->desc_referente->setWidth( 200 );
        $this->table->cols->email_referente->setWidth( 200 );
/*
		$this->table->cols->fornitore->setSource( $this->ds_anagr );
		$this->table->cols->fornitore->setSourceValueField( "codice" );
		$this->table->cols->fornitore->setSourceDescriptionField( "descrizione" );
*/
        $this->table->cols->desc_referente->setOrderable( false ); 
        $this->table->cols->email_referente->setOrderable( false ); 


		// ------------------------------------------------------------- Message
		$message =& $this->build( "p4a_message", "message" );
		$message->setWidth( 600 );


		// ----------------------------------------------------- Vista dettaglio
		$fields->fornitore->setLabel( 'Fornitore' );
		$fields->fornitore->setType( 'select' );
		$fields->fornitore->setSource( $this->ds_anagr );
		$fields->fornitore->setSourceValueField( "codice" );
		$fields->fornitore->setSourceDescriptionField( "descrizione" );
		$fields->fornitore->setWidth( 250 );
		
		$fields->datainizio->setLabel('Dalla data');
		$fields->datainizio->setType('date');

		$fields->datafine->setLabel('Alla data');
		$fields->datafine->setType('date');
		
		$fields->ricorsivo->setLabel( "Ricorrenza annuale" );
        $fields->ricorsivo->label->setWidth( 120 );
		$fields->ricorsivo->setType( 'select' );
		$fields->ricorsivo->setSourceValueField( 'id' );
		$fields->ricorsivo->setSourceDescriptionField( 'desc' );
		$fields->ricorsivo->setSource( $array_source );
		
		//Fieldset con l'elenco dei campi
		$fs_dettaglio=& $this->build("p4a_fieldset", "fs_dettaglio");
		$fs_dettaglio->setTitle( "Dettaglio" );
		$fs_dettaglio->setWidth( E3G_FIELDSET_DATI_WIDTH );
		$fs_dettaglio->anchor($this->fields->fornitore);
 		$fs_dettaglio->anchor($this->fields->datainizio);
		$fs_dettaglio->anchor($this->fields->datafine);
		$fs_dettaglio->anchorLeft($this->fields->ricorsivo);

		
		// Abilitazione campi in base al tipo utente (GestiGAS)
		$this->abilitazione_campi();


		// ---------------------------------------------------- Frame principale
		$frm=& $this->build("p4a_frame", "frm");
		$frm->setWidth( E3G_MAIN_FRAME_WIDTH );

		$frm->anchor($fs_ricerca);
		$frm->anchor($this->table);
		$frm->anchor($message);
		$frm->anchor($fs_dettaglio);

		e3g_scrivi_footer( $this, $frm );

  		// Display
		$this->display("main", $frm);
		$this->display("menu", $p4a->menu);
		$this->display("top", $this->toolbar);
	}

	
    // -------------------------------------------------------------------------
	function main()
    // -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();

		// Se non ci sono record, allora la finestra si predispone
		// in inserimento, ma bisogna generare l'evento newRow()
		$n = $db->queryOne(
			"SELECT COUNT(*) FROM " . $p4a->e3g_prefix . "fornitoreperiodo" );
		if ( $n == 0 )
			$this->newRow();

		parent::main();

		foreach ( $this->mf as $mf )
			$this->fields->$mf->unsetStyleProperty("border");
	}


    // -------------------------------------------------------------------------
	function newRow()
    // -------------------------------------------------------------------------
	{
		$db =& p4a_db::singleton();
	
		$this->newrecord = true;

		parent::newRow();		
	}

	
    // -------------------------------------------------------------------------
	function saveRow()
    // -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
	
		if ( $this->newrecord ) {
			$maxid = $db->queryOne( "SELECT MAX(idtable) FROM " . $p4a->e3g_prefix . "fornitoreperiodo" );
			if ( is_numeric($maxid) )
				$maxid++;
			else
				$maxid = 1 ; 
			$this->fields->idtable->setNewValue( $maxid );
		}
				
		$error_text = "";

		foreach ( $this->mf as $mf ) {
			$value = $this->fields->$mf->getNewValue();
			if ( trim($value) === "" ) {
				$this->fields->$mf->setStyleProperty("border", "1px solid red");
				$error_text = "Compilare i campi obbligatori.";
			}
		}
		
		if ( $error_text == "" ) {
			if ( $this->fields->datainizio->getUnformattedNewValue() > $this->fields->datafine->getUnformattedNewValue() )
				$error_text = "La data iniziale non puo' essere successiva a quella finale.";	
		}

		if ( $error_text == "" ) {
			parent::saveRow();

			$this->newrecord = false;
            $this->table->syncPageWithSource();
		}
		else
			$this->message->setValue( $error_text );
	}
	

    // -------------------------------------------------------------------------
	function nextRow()
    // -------------------------------------------------------------------------
	{
		parent::nextRow();
		
		$p4a =& p4a::singleton();
		
		$this->abilitazione_campi();
	}

	
    // -------------------------------------------------------------------------
	function prevRow()
    // -------------------------------------------------------------------------
	{
		parent::prevRow();
		
		$p4a =& p4a::singleton();
		
		$this->abilitazione_campi();
	}


    // -------------------------------------------------------------------------
	function firstRow()
    // -------------------------------------------------------------------------
	{
		parent::firstRow();
		
		$p4a =& p4a::singleton();
		
		$this->abilitazione_campi();
	}


    // -------------------------------------------------------------------------
	function lastRow()
    // -------------------------------------------------------------------------
	{
		parent::lastRow();
		
		$p4a =& p4a::singleton();
		
		$this->abilitazione_campi();
	}


    // -------------------------------------------------------------------------
	function table_click()
    // -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();
		
		$this->abilitazione_campi();
	}
	
	
    // -------------------------------------------------------------------------
	function bu_filtraClick()
    // -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();
		
		$this->ds_per->setWhere( "fornitore = '" . $this->fld_forn->getNewValue() . "'" );
		$this->ds_per->firstRow();

		$this->abilitazione_campi();
	}
	
	
    // -------------------------------------------------------------------------
	function bu_annulla_filtroClick()
    // -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();
		
		$this->ds_per->setWhere( "1 = 1" );
		$this->ds_per->firstRow();

		$this->ds_forn->firstRow();

		$this->abilitazione_campi();
	}
	
	
    // -------------------------------------------------------------------------
	function abilita_campi()
    // -------------------------------------------------------------------------
	{
		// L'utente può solo leggere
		while ( $field =& $this->fields->nextItem() ) 
			$field->enable();
	} 

    					
    // -------------------------------------------------------------------------
	function disabilita_campi()
    // -------------------------------------------------------------------------
	{
		// L'utente può solo leggere
		while ( $field =& $this->fields->nextItem() ) 
			$field->disable();
	} 


    // -------------------------------------------------------------------------
	function abilitazione_campi()
    // -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();

		if ( E3G_TIPO_GESTIONE == 'G' ) {
           	switch ($p4a->e3g_utente_tipo) {
                case "A":
                case "AS":
					$this->abilita_campi();                    
					break;

                case "R":
					$pos = strpos( $p4a->e3g_where_referente, "'" . $this->fields->fornitore->getNewValue() . "'" );
					if ($pos === false) 
			    		$this->disabilita_campi();                    
					else 
			    		$this->abilita_campi();                    
					break;
					
				default:
					$this->disabilita_campi();                    
					break;
            }
        }
	}


    // ($obj è l'oggetto che ha scatenato l'evento)
    // -------------------------------------------------------------------------
    function table_BeforeDisplay( $obj, $rows ) 
    // -------------------------------------------------------------------------
    {
        // Campi visualizzati: array("desc_fornitore", "datainizio", "datafine", "ricorsivo", "desc_referente", "email_referente") 
        // Esempio: [datainizio] => 2010-02-10
        for( $i=0; $i<count($rows); $i++ ) {
            // Evidenzia i fornitori per i quali l'ordine è aperto
            // MOMENTANEAMENTE (in attesa del rifacimento di questa finestra)
            //   considera solo quelli senza periodicità
            if ( $rows[$i]["datainizio"] <= date("Y-m-d") and date("Y-m-d") <= $rows[$i]["datafine"] )
                $rows[$i]["desc_fornitore"] = "<span style='color:black;font-weight:bold;'>" . $rows[$i]["desc_fornitore"] . "</span>";
        }  
        return $rows;  
    }  


}

?>