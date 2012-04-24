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


class statistiche_ordini extends P4A_Mask
{
    
    // -------------------------------------------------------------------------
    function statistiche_ordini()
    // -------------------------------------------------------------------------
    {
        $this->p4a_mask();
        $this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
        $p4a =& p4a::singleton();
        
        $this->SetTitle( "Statistiche Ordini" );


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


        // ------------------------------------------------------------- Toolbar
        $this->build("p4a_quit_toolbar", "toolbar");


        // ------------------------------------------------------------- Message
        $message =& $this->build("p4a_message", "message");
        $message->setWidth( 500 );


        // ------------------------------------------- Radio button del soggetto        
        $values_sog = array();
        $values_sog[] = array("id" => "1", "desc" => "Tutti gli utenti del gruppo");
        $values_sog[] = array("id" => "2", "desc" => $p4a->e3g_utente_desc);
        if ( $p4a->e3g_utente_tipo <> "U" )  // I normali utenti non possono selezionare altri utenti
            $values_sog[] = array("id" => "3", "desc" => "Utente:");
        $array_source_sog =& $this->build("p4a_array_source", "array_source"); 
        $array_source_sog->load( $values_sog ); 
        $array_source_sog->setPk( "id" ); 
        
        $fld_soggetto =& $this->build("p4a_field", "fld_soggetto");
        $fld_soggetto->setLabel( "" );
        $fld_soggetto->setWidth( 250 );
        $fld_soggetto->setType( "radio" );
        $fld_soggetto->setSource( $array_source_sog ); 
        $fld_soggetto->setValue( 1 );
        $fld_soggetto->addAction( "onChange" );
        $this->intercept( $this->fld_soggetto, "onChange", "fld_soggetto_change" );
        

        // --------------------------------------- Combo scelta utente specifico
        $this->build( "p4a_field", "fld_cod_utente" );
        $this->fld_cod_utente->setLabel( "" );
        $this->fld_cod_utente->setWidth( 250 );
        $this->fld_cod_utente->disable();
        $this->fld_cod_utente->setType( "select" );
        $this->fld_cod_utente->setSource( $this->ds_utenti );
        $this->fld_cod_utente->setSourceValueField( "cod_utente" );
        $this->fld_cod_utente->setSourceDescriptionField( "desc_utente" );
        $this->fld_cod_utente->addAction( "onChange" );
        $this->intercept( $this->fld_cod_utente, "onChange", "fld_soggetto_change" );

        // Posizionamento sul primo record (non avviene in automatico)        
        $this->fld_cod_utente->setNewValue( $this->ds_utenti->fields->cod_utente->getNewValue() );


        // ------------------------ Checkbox per selezione utente o tutto il GAS
        $this->build( "p4a_field", "ck_tutto_gas" );
        $this->ck_tutto_gas->setLabel( "Tutto il GAS" );
        $this->ck_tutto_gas->setTooltip( "Visualizza statistiche di tutto il GAS o solo dell'utente selezionato" );
        $this->ck_tutto_gas->label->setWidth( 100 );
        $this->ck_tutto_gas->setType( "checkbox" );
        $this->ck_tutto_gas->setValue( 1 );
        $this->ck_tutto_gas->addAction( "onChange" );
        $this->intercept( $this->ck_tutto_gas, "onChange", "fld_soggetto_change" );

        
        // ---------------------------------------------- Griglia "Più ordinati"
//TODO Bisogna forse limitare la query ad un certo numero di righe (per esempio primi 100), magari chiedendolo all'utente: tutto o prime N righe?        
        $this->build( "p4a_db_source", "ds_piu_ordinati" );
        $this->ds_piu_ordinati->setSelect( 
            "f.descrizione AS fornitore, " .
            "a.descrizione, " .
            "CONCAT_WS( ' ', a.um_qta, a.um ) AS um_qta_um, a.um_qta, " .  // CONCAT_WS non è vuoto se manca l'UM 
            "SUM( d.quantita ) AS quantita, " .
            "( SUM(d.prezzo*d.quantita) / SUM(d.quantita) ) AS prezzo, " .
            "SUM( d.prezzo*d.quantita ) AS totale" );
        $this->ds_piu_ordinati->setTable( $p4a->e3g_prefix . "docr AS d" );
        $this->ds_piu_ordinati->addJoin( $p4a->e3g_prefix . "articoli AS a", "d.codice = a.codice" );
        $this->ds_piu_ordinati->addJoin( $p4a->e3g_prefix . "anagrafiche AS f", "a.centrale = f.codice"  );
        $this->ds_piu_ordinati->setWhere( "1 = 0 " );  // Impostato in fld_soggetto_change()
        $this->ds_piu_ordinati->addGroup( "f.descrizione, a.descrizione" );
        $this->ds_piu_ordinati->addOrder( "quantita", "DESC" );
        $this->ds_piu_ordinati->setPk( "a.descrizione" );
        $this->ds_piu_ordinati->setPageLimit( $p4a->e3g_utente_db_source_page_limit );
        $this->ds_piu_ordinati->load();
        
        
        $this->build( "p4a_table", "tab_piu_ordinati_utente" );
        $this->tab_piu_ordinati_utente->setWidth( E3G_TABLE_WIDTH );
        $this->tab_piu_ordinati_utente->setSource( $this->ds_piu_ordinati );
        $this->tab_piu_ordinati_utente->setVisibleCols( array( "fornitore", "quantita", "descrizione", "um_qta_um", "um_qta", "prezzo", "totale" ) );
        $this->tab_piu_ordinati_utente->showNavigationBar();
        $this->intercept( $this->tab_piu_ordinati_utente->rows, "beforeDisplay", "tab_piu_ordinati_utente_beforeDisplay" );  
        
        $this->tab_piu_ordinati_utente->cols->um_qta->setVisible( false );

        $this->tab_piu_ordinati_utente->cols->fornitore->setLabel( "Fornitore" );
        $this->tab_piu_ordinati_utente->cols->quantita->setLabel( "Q.ta'" );
        $this->tab_piu_ordinati_utente->cols->descrizione->setLabel( "Descrizione articolo" );
        $this->tab_piu_ordinati_utente->cols->um_qta_um->setLabel( "Conf." );
        $this->tab_piu_ordinati_utente->cols->prezzo->setLabel( "Prezzo unitario (medio)" );
        $this->tab_piu_ordinati_utente->cols->totale->setLabel( "Importo totale" );

        $this->tab_piu_ordinati_utente->cols->fornitore->setWidth( 200 );
        $this->tab_piu_ordinati_utente->cols->quantita->setWidth( 50 );
//      $this->tab_piu_ordinati_utente->cols->descrizione->setWidth();  Per differenza
        $this->tab_piu_ordinati_utente->cols->um_qta_um->setWidth( 50 );
        $this->tab_piu_ordinati_utente->cols->prezzo->setWidth( 50 );
        $this->tab_piu_ordinati_utente->cols->totale->setWidth( 75 );

        $this->tab_piu_ordinati_utente->cols->fornitore->setOrderable( false );
        $this->tab_piu_ordinati_utente->cols->quantita->setOrderable( false );
        $this->tab_piu_ordinati_utente->cols->um_qta_um->setOrderable( false );
        $this->tab_piu_ordinati_utente->cols->prezzo->setOrderable( false );
        $this->tab_piu_ordinati_utente->cols->totale->setOrderable( false );

        
        // ----------------------------------------------- Fieldset combo utente 
        $this->build ("p4a_fieldset", "fs_utente" );
        $this->fs_utente->setTitle( "Soggetto da considerare" );
        $this->fs_utente->setWidth( E3G_FIELDSET_SEARCH_WIDTH );
        $this->fs_utente->anchor( $this->fld_soggetto );
        if ( $p4a->e3g_utente_tipo <> "U" )  // I normali utenti non possono selezionare altri utenti
            $this->fs_utente->anchor( $this->fld_cod_utente, "80px" );


        // ---------------------------------------------------- Frame principale
        $frm=& $this->build("p4a_frame", "frm");
        $frm->setWidth(E3G_MAIN_FRAME_WIDTH);
        
        $this->frm->anchor( $this->message );
        $this->frm->anchor( $this->fs_utente );
        $this->frm->anchor( $this->tab_piu_ordinati_utente );
        
        e3g_scrivi_footer( $this, $frm );

        // Display
        $this->display("main", $frm);
        $this->display("menu", $p4a->menu);
        $this->display("top", $this->toolbar);
        
        
        $this->fld_soggetto_change();
    }


    // -------------------------------------------------------------------------
    function main()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();

        parent::main();
    }
    

    // -------------------------------------------------------------------------
    function fld_soggetto_change()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();

        $query_txt = "codtipodoc = '".$p4a->e3g_azienda_gg_cod_doc_ordine_fam."' AND quantita > 0 ";
        
        switch ( $this->fld_soggetto->getNewValue() ) {
            case 1:  // Tutto il GAS
                $this->fld_cod_utente->disable();
                $this->tab_piu_ordinati_utente->setTitle( "Articoli piu' ordinati da tutti gli utenti del gruppo" );
                break;

            case 2:  // Solo utente connesso
                $query_txt .= " AND codutente = '" . $p4a->e3g_utente_codice . "'";
                $this->fld_cod_utente->disable();
                $this->tab_piu_ordinati_utente->setTitle( "Articoli piu' ordinati da " . $p4a->e3g_utente_desc );
                break;

            case 3:  // Solo utente selezionato
                $query_txt .= " AND codutente = '" . $this->fld_cod_utente->getNewValue() . "'";
                $this->fld_cod_utente->enable();
                $this->tab_piu_ordinati_utente->setTitle( "Articoli piu' ordinati dall'utente selezionato" );
                break;
        }

        $this->ds_piu_ordinati->setWhere( $query_txt );
        $this->ds_piu_ordinati->firstRow();
    }


    // -------------------------------------------------------------------------
    function tab_piu_ordinati_utente_beforeDisplay( $obj, $rows ) 
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