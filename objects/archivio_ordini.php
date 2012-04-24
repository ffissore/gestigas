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


class archivio_ordini extends P4A_Mask
{
	
    // -------------------------------------------------------------------------
	function archivio_ordini()
    // -------------------------------------------------------------------------
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();
		
		$this->SetTitle( "Archivio Ordini" );


		// ------------------------------------------------------------- Toolbar
		$this->build("p4a_quit_toolbar", "toolbar");


		// ------------------------------------------------------------- Message
		$message =& $this->build("p4a_message", "message");
		$message->setWidth("500");

        // -------------------------------------------------------- Combo utenti
        $this->build( "p4a_db_source", "ds_utenti" );
        $this->ds_utenti->setSelect( "DISTINCT a.idanag, a.stato, a.descrizione, " .
            " a.codice AS cod_utente, " .
            " IF( a.stato = 1, " .
            "     a.descrizione, CONCAT( a.descrizione, ' (NON attivo)' ) ) AS desc_utente " );
        $this->ds_utenti->setTable( $p4a->e3g_prefix . "anagrafiche AS a" );
        $this->ds_utenti->addJoin( $p4a->e3g_prefix . "doct AS t",  "a.codice = t.codclifor" );

        $str_where = "t.codtipodoc = '" . $p4a->e3g_azienda_gg_cod_doc_ordine_fam . "' AND t.totdoc <> 0 ";
        if ( $p4a->e3g_utente_tipo == "U" )  // I normali utenti possono vedere solo i propri ordini (o di tutto il GAS)
            $str_where .= " AND a.idanag = " . $p4a->e3g_utente_idanag;
        $this->ds_utenti->setWhere( $str_where );

        $this->ds_utenti->addOrder( "stato" );
        $this->ds_utenti->addOrder( "descrizione" );
        $this->ds_utenti->setPk( "idanag" );
        $this->ds_utenti->load();
        $this->ds_utenti->firstRow();


        $this->build( "p4a_field", "fld_cod_utente" );
        $this->fld_cod_utente->setLabel( "Utente" );
        $this->fld_cod_utente->label->setWidth( 50 );
        $this->fld_cod_utente->setWidth( 250 );
        $this->fld_cod_utente->setType( "select" );
        $this->fld_cod_utente->setSource( $this->ds_utenti );
        $this->fld_cod_utente->setSourceValueField( "cod_utente" );
        $this->fld_cod_utente->setSourceDescriptionField( "desc_utente" );
        $this->fld_cod_utente->addAction( "onChange" );
        $this->intercept( $this->fld_cod_utente, "onChange", "fld_cod_utente_change" );

        // Posizionamento sul primo record (non avviene in automatico)        
        $this->fld_cod_utente->setNewValue( $this->ds_utenti->fields->cod_utente->getNewValue() );


		// --------------------------------------------- Griglia "Elenco ordini"
		$this->build( "p4a_db_source", "ds_doct" );
        $this->ds_doct->setSelect( "t.iddoc, t.numdocum, t.data, t.totdoc," .
                "COUNT( DISTINCT a.centrale ) AS n_for_diversi, " .
                "COUNT( DISTINCT r.codice ) AS n_art_diversi, " .
                "SUM( r.quantita ) AS tot_qta" );
        $this->ds_doct->setTable( $p4a->e3g_prefix . "doct AS t" );
        $this->ds_doct->addJoin( $p4a->e3g_prefix . "docr AS r", "r.iddocr = t.iddoc" );
        $this->ds_doct->addJoin( $p4a->e3g_prefix . "articoli AS a", "r.codice = a.codice" );
        $this->ds_doct->setWhere( "1 = 0" );  // Impostato da fld_cod_utente_change()
        $this->ds_doct->addOrder( "t.data", "DESC" );
        $this->ds_doct->addGroup( "t.iddoc, t.numdocum, t.data, t.totdoc" );
		$this->ds_doct->setPk( "t.iddoc" );
		$this->ds_doct->setPageLimit( $p4a->e3g_utente_db_source_page_limit );
		$this->ds_doct->load();
		$this->setSource( $this->ds_doct );  // db_source principale
		
        
        $tab_ord =& $this->build( "p4a_table", "tab_doct" );
        $fs_utente_width = E3G_FIELDSET_SEARCH_WIDTH/2 - 140;
        $this->tab_doct->setWidth( E3G_TABLE_WIDTH - $fs_utente_width - 40 );
        $this->tab_doct->setSource( $this->ds_doct );
        $this->tab_doct->setTitle( "Elenco ordini utente selezionato" );
        $this->tab_doct->setVisibleCols( array( "data", "numdocum", "totdoc", "n_for_diversi", "n_art_diversi", "tot_qta") );
        $this->tab_doct->showNavigationBar();
        $this->intercept( $this->tab_doct->rows, "afterClick", "tab_doct_afterClick" );        

        $this->tab_doct->cols->data->setLabel( "Data doc." );
        $this->tab_doct->cols->numdocum->setLabel( "Numero doc." );
        $this->tab_doct->cols->totdoc->setLabel( "Totale ordine" );
        $this->tab_doct->cols->n_for_diversi->setLabel( "N. fornitori diversi" );
        $this->tab_doct->cols->n_art_diversi->setLabel( "N. articoli diversi" );
        $this->tab_doct->cols->tot_qta->setLabel( "Quantita' totale" );
        
        
        // ------------------------------ Griglia "Dettaglio ordine selezionato"
		$this->build( "p4a_db_source", "ds_docr" );
        $this->ds_docr->setSelect( "r.iddocr, r.codice, " .
            "r.quantita, a.descrizione, " .
            "CONCAT_WS( ' ', a.um_qta, a.um ) AS um_qta_um, a.um_qta, " .  // CONCAT_WS non è vuoto se manca l'UM 
            "r.prezzo, r.totale, " .
            "f.descrizione AS fornitore " );
        $this->ds_docr->setTable( $p4a->e3g_prefix . "docr AS r" );
		$this->ds_docr->addJoin( $p4a->e3g_prefix . "articoli AS a", "r.codice = a.codice" );
		$this->ds_docr->addJoin( $p4a->e3g_prefix . "anagrafiche AS f", "a.centrale = f.codice" );
        $this->ds_docr->setWhere( "1 = 0" );  // Impostato da tab_doct_click()
        $this->ds_docr->addOrder( "f.descrizione" );
        $this->ds_docr->addOrder( "r.descrizione" );
		$this->ds_docr->setPk( "iddocr" );
		$this->ds_docr->setPageLimit( $p4a->e3g_utente_db_source_page_limit );
		$this->ds_docr->load();

		
		$this->build( "p4a_table", "tab_row" );
		$this->tab_row->setWidth( E3G_TABLE_WIDTH );
		$this->tab_row->setSource( $this->ds_docr );
		$this->tab_row->setVisibleCols( array( "fornitore", "quantita", "descrizione", "um_qta_um", "um_qta", "prezzo", "totale") );
        $this->tab_row->showNavigationBar();
        $this->intercept( $this->tab_row->rows, "beforeDisplay", "tab_row_beforeDisplay" );  
		
        $this->tab_row->cols->um_qta->setVisible( false );

        $this->tab_row->cols->fornitore->setLabel( "Fornitore" );
        $this->tab_row->cols->quantita->setLabel( "Q.ta'" );
        $this->tab_row->cols->descrizione->setLabel( "Descrizione articolo" );
        $this->tab_row->cols->um_qta_um->setLabel( "Conf." );
        $this->tab_row->cols->prezzo->setLabel( "Prezzo unitario" );
        $this->tab_row->cols->totale->setLabel( "Importo totale" );

        $this->tab_row->cols->fornitore->setWidth( 200 );
        $this->tab_row->cols->quantita->setWidth( 50 );
//      $this->tab_row->cols->descrizione->setWidth();  Per differenza
        $this->tab_row->cols->um_qta_um->setWidth( 50 );
        $this->tab_row->cols->prezzo->setWidth( 50 );
        $this->tab_row->cols->totale->setWidth( 75 );

        $this->tab_row->cols->fornitore->setOrderable( false );
        $this->tab_row->cols->um_qta_um->setOrderable( false );
        
        
        // ----------------------------------------------- Fieldset combo utente 
        $this->build ("p4a_fieldset", "fs_utente" );
        $this->fs_utente->setTitle( "Intestatario documento" );
        $this->fs_utente->setWidth( $fs_utente_width );
        $this->fs_utente->anchor( $this->fld_cod_utente );


        // ---------------------------------------------------- Frame principale
		$frm=& $this->build("p4a_frame", "frm");
		$frm->setWidth(E3G_MAIN_FRAME_WIDTH);
		
		$this->frm->anchor( $this->message );
        if ( $p4a->e3g_utente_tipo <> "U" )  // I normali utenti vedono solo i propri ordini
            $this->frm->anchor( $this->fs_utente );
        $this->frm->anchorLeft( $this->tab_doct );
        $this->frm->anchor( $this->tab_row );
		
		e3g_scrivi_footer( $this, $frm );

		// Display
		$this->display("main", $frm);
		$this->display("menu", $p4a->menu);
		$this->display("top", $this->toolbar);
		
        
        $this->fld_cod_utente_change();
	}


    // -------------------------------------------------------------------------
	function main()
    // -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();

		parent::main();
	}
	

    // -------------------------------------------------------------------------
	function fld_cod_utente_change()
    // -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();
		
		$this->ds_doct->setWhere(
            "t.codtipodoc = '" . $p4a->e3g_azienda_gg_cod_doc_ordine_fam . "' AND " .
            "t.codclifor = '" . $this->fld_cod_utente->getNewValue() . "'" );
		$this->ds_doct->firstRow();
		$this->tab_doct_afterClick();
	}


    // -------------------------------------------------------------------------
	function tab_doct_afterClick()
    // -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();

        $this->tab_row->setTitle( "Dettaglio ordine n." . $this->ds_doct->fields->numdocum->getNewValue() .
            " del " . e3g_format_mysql_data( $this->ds_doct->fields->data->getNewValue() ) );

		// Controllo inserito perchè la prima volta che entra iddocr non restituisce niente e la query andrebbe in errore
		if ( is_numeric( $this->tab_doct->data->fields->iddoc->getNewValue() ) )
      		$this->ds_docr->setWhere( "iddocr = " . $this->tab_doct->data->fields->iddoc->getNewValue() );
		else 
      		$this->ds_docr->setWhere( "1 = 0" );
	}


    // -------------------------------------------------------------------------
    function tab_row_beforeDisplay( $obj, $rows ) 
    // -------------------------------------------------------------------------
    {  
        for( $i=0; $i<count($rows); $i++ ) {  
            if ( $rows[$i]["um_qta"] == "" )
                $rows[$i]["um_qta_um"] = "";
        }  
        return $rows;  
    }  


}
?>