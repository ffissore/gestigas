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


class modifica_quantita_articoli extends P4A_Mask
{
    
    var $tot_originale_1;

    // -------------------------------------------------------------------------
    function modifica_quantita_articoli()
    // -------------------------------------------------------------------------
    {
        $this->p4a_mask();
        $this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
        $p4a =& p4a::singleton();
        
        $this->SetTitle( "Modifica quantita' articoli in consegna" );


        // ------------------------------------------- DB source filtro iniziale
        // Fornitori (solo quelli interessati)
        $this->build( "p4a_db_source", "ds_filtro_for" );
        $this->ds_filtro_for->setQuery( 
            "SELECT f.codice, f.descrizione " .
            "  FROM " . $p4a->e3g_prefix . "anagrafiche AS f " .
            "       JOIN " . $p4a->e3g_prefix . "articoli AS a ON a.centrale = f.codice " .
            "       JOIN " . $p4a->e3g_prefix . "docr AS d ON d.codice = a.codice " .
            " WHERE d.visibile = 'N' " .
            "   AND d.codtipodoc = '" . $p4a->e3g_azienda_gg_cod_doc_ordine . "' " .
            "   AND ( d.estratto <> 'S' OR ISNULL(d.estratto) ) " .
        ( $p4a->e3g_utente_tipo == "R" ?
            "   AND " . str_replace("#CAMPOCODICE#", "f.codice", $p4a->e3g_where_referente) : "" ) .
          "GROUP BY f.codice, f.descrizione " .
          "ORDER BY f.descrizione" );
        // non è possibile inserire il record NON INDICATO perchè c'è il JOIN 
        // provando con il LEFT JOIN si blocca durante la query
        // AP 22.06.10        
        $this->ds_filtro_for->setPk( "f.codice" );        
        $this->ds_filtro_for->load();     
        $this->ds_filtro_for->firstRow();

        $this->build( "p4a_db_source", "ds_utente" );
        $this->ds_utente->setTable( $p4a->e3g_prefix . "anagrafiche");
        $this->ds_utente->setSelect( "codice,descrizione");
        $this->ds_utente->setPk( "codice" );        
        $this->ds_utente->load();     
        
        $this->build( "p4a_db_source", "ds_fornit" );
        $this->ds_fornit->setTable( $p4a->e3g_prefix . "anagrafiche");
        $this->ds_fornit->setSelect( "codice,descrizione");
        $this->ds_fornit->setPk( "codice" );     
        $this->ds_fornit->load();     
        
        // Categorie (tipi articoli)
        $this->build( "p4a_db_source", "ds_filtro_cat" );
        $this->ds_filtro_cat->setTable( $p4a->e3g_prefix . "tipiarticoli" );
        $this->ds_filtro_cat->setPk( "codice" );        
        $this->ds_filtro_cat->addOrder( "codice" );     
        $this->ds_filtro_cat->load();

        // Sottocategorie (categoria merceologica)      
        $this->build( "p4a_db_source", "ds_filtro_sottocat" );
        $this->ds_filtro_sottocat->setTable( $p4a->e3g_prefix . "catmerceologica" );
        $this->ds_filtro_sottocat->setWhere( "tipo = '" . $this->ds_filtro_cat->fields->codice->getNewValue() . "' OR codice='000'" );        
        $this->ds_filtro_sottocat->setPk( "codice" );     
        $this->ds_filtro_sottocat->addOrder( "codice" );      
        $this->ds_filtro_sottocat->load();


        // ------------------------------------------------------------- Toolbar
        $this->build( "p4a_navigation_toolbar", "toolbar" );
        $this->toolbar->setMask( $this );


        // -------------------------------------------------------------- Filtro
        // Filtro fornitore
        $this->build( "p4a_field", "fld_filtro_for" );
        $this->fld_filtro_for->setLabel( "Fornitore" );
        $this->fld_filtro_for->setType( "select" );
        $this->fld_filtro_for->setSource( $this->ds_filtro_for );
        $this->fld_filtro_for->setSourceValueField( "codice" );
        $this->fld_filtro_for->setSourceDescriptionField( "descrizione" );
        $this->fld_filtro_for->setNewValue( $this->ds_filtro_for->fields->codice->getNewValue() );
        $this->fld_filtro_for->label->setWidth( 60 );
        $this->fld_filtro_for->setWidth( 250 );
        $this->fld_filtro_for->addAction( "OnChange" );
        $this->intercept( $this->fld_filtro_for, "onChange", "fld_filtro_for_change" );      

        // Filtro categoria     
        $this->build( "p4a_field", "fld_filtro_cat" );
        $this->fld_filtro_cat->setLabel( "Categoria" );
        $this->fld_filtro_cat->setType( "select" );
        $this->fld_filtro_cat->setSource( $this->ds_filtro_cat );
        $this->fld_filtro_cat->setSourceValueField( "codice" );
        $this->fld_filtro_cat->setSourceDescriptionField( "descrizione" );
        $this->fld_filtro_cat->setNewValue( "00" );
        $this->fld_filtro_cat->label->setWidth( 60 );
        $this->fld_filtro_cat->setWidth( 250 );
        $this->fld_filtro_cat->addAction( "OnChange" );
        $this->intercept( $this->fld_filtro_cat, "onChange", "fld_filtro_cat_change" );      

        // Filtro sottocategoria        
        $this->build( "p4a_field", "fld_filtro_sottocat" );
        $this->fld_filtro_sottocat->setLabel( "Sottocategoria" );
        $this->fld_filtro_sottocat->setType( "select" );
        $this->fld_filtro_sottocat->setSource( $this->ds_filtro_sottocat );
        $this->fld_filtro_sottocat->setSourceValueField( "codice" );
        $this->fld_filtro_sottocat->setSourceDescriptionField( "descrizione" );
        $this->fld_filtro_sottocat->setNewValue( "000" );
        $this->fld_filtro_sottocat->label->setWidth( 80 );
        $this->fld_filtro_sottocat->setWidth( 250 );


        // Bottone "Filtra" 
        $this->build( "p4a_button", "bu_filtra" );
        $this->bu_filtra->setLabel( "Filtra" );
        $this->bu_filtra->setIcon( "find" );
        $this->bu_filtra->setSize( 16 );
        $this->bu_filtra->setWidth( 80 );
        $this->bu_filtra->addAction( "onClick" );
        $this->intercept( $this->bu_filtra, "onClick", "bu_filtra_click" );

        // Bottone "Mostra tutto"
        $this->build( "p4a_button", "bu_mostra_tutto" );
        $this->bu_mostra_tutto->setLabel( "Mostra tutto" );
        $this->bu_mostra_tutto->setIcon( "cancel" );
        $this->bu_mostra_tutto->setSize( 16 );
        $this->bu_mostra_tutto->setWidth( 110 );
        $this->bu_mostra_tutto->addAction( "onClick" );
        $this->intercept( $this->bu_mostra_tutto, "onClick", "bu_mostra_tutto_click" );


        // ------------------------------------------------ DB source principale
        $this->build( "p4a_db_source", "ds_doc_righe" );
        $this->ds_doc_righe->setSelect(
            "d.idriga, t.iddoc, t.totdoc, t.data, t.codclifor, " .  // ID testata documento (docr.iddocr = doct.iddoc)
            "a.codice, " .
            "d.quantita, " .
            "a.descrizione, " .
            "CONCAT_WS( ' ', um_qta, um ) AS um_qta_um, " .  // CONCAT_WS non è vuoto se manca l'UM 
        	"d.codutente" ); 
        $this->ds_doc_righe->setTable( $p4a->e3g_prefix . "docr AS d" );
        $this->ds_doc_righe->addJoin( $p4a->e3g_prefix . "articoli AS a", "d.codice = a.codice" );
        $this->ds_doc_righe->addJoin( $p4a->e3g_prefix . "doct AS t", "d.iddocr = t.iddoc" );
        $this->ds_doc_righe->setWhere( "( d.estratto <> 'S' OR ISNULL(d.estratto) ) AND d.visibile = 'N'" );  // Impostato in bu_filtra_click()
        //$this->ds_doc_righe->addGroup( "a.codice, a.descrizione" );
        $this->ds_doc_righe->addOrder( "d.codutente" );
        $this->ds_doc_righe->addOrder( "t.codclifor" );
        $this->ds_doc_righe->addOrder( "a.descrizione" );
        $this->ds_doc_righe->setPk( "d.idriga" );
        //$this->ds_doc_righe->setPageLimit( $p4a->e3g_utente_db_source_page_limit );
        $this->ds_doc_righe->load();

        $this->setSource( $this->ds_doc_righe );

        
        // -------------------------------------------------- Griglia principale
        $this->build( "p4a_table", "tab_doc_righe" );
        $this->tab_doc_righe->setWidth( E3G_TABLE_WIDTH );
        $this->tab_doc_righe->setTitle( "Articoli in consegna" );
        $this->tab_doc_righe->setSource( $this->ds_doc_righe );
        $this->tab_doc_righe->setVisibleCols( array( "codutente", "codclifor" ,"data","descrizione", "quantita") );
        $this->intercept( $this->tab_doc_righe->rows, "afterClick", "tab_doc_righe_AfterClick" );  
        //$this->intercept( $this->tab_doc_righe->rows, "beforeDisplay", "tab_doc_righe_BeforeDisplay" );  
        
        $this->tab_doc_righe->cols->quantita->setLabel( "Q.ta'" );
        $this->tab_doc_righe->cols->descrizione->setLabel( "Articolo" );
        $this->tab_doc_righe->cols->descrizione->setWidth( 300 );
        $this->tab_doc_righe->cols->quantita->setWidth( 30 );
        $this->tab_doc_righe->cols->quantita->setOrderable( false );
        $this->tab_doc_righe->cols->codutente->setLabel( "Utente" );
        $this->tab_doc_righe->cols->codutente->setWidth( 300 );
        $this->tab_doc_righe->cols->codutente->setSource( $this->ds_utente );
        $this->tab_doc_righe->cols->codutente->setSourceValueField( "codice" );
        $this->tab_doc_righe->cols->codutente->setSourceDescriptionField( "descrizione" );
        $this->tab_doc_righe->cols->data->setLabel( "Data Ordine" );
        $this->tab_doc_righe->cols->codclifor->setLabel( "Fornitore" );
        $this->tab_doc_righe->cols->codclifor->setSource( $this->ds_fornit );
        $this->tab_doc_righe->cols->codclifor->setSourceValueField( "codice" );
        $this->tab_doc_righe->cols->codclifor->setSourceDescriptionField( "descrizione" );
        
        
        $this->fields->quantita->data_field->setType("float");
        
        // ------------------------------------------------------------- Message
        $this->build("p4a_message", "message" );
        $this->message->setWidth( 500 );

/* 
 
        // ------------------------- Frame 1: Modifica prezzo tutti gli articoli
        $this->build( "p4a_field", "fld1_n_art_diversi" );
        $this->build( "p4a_field", "fld1_tot_originale" );
        $this->build( "p4a_field", "fld1_variazione" );
//      $this->fields->totdoc
        $this->build( "p4a_field", "fld1_valore" );

        $this->fld1_n_art_diversi->setLabel( "N. articoli diversi" );
        $this->fld1_tot_originale->setLabel( "Importo totale originale" );
        $this->fld1_variazione->setLabel( "Variazione totale" );
        $this->fields->totdoc->setLabel( "Nuovo totale" );
//      $this->fld1_valore->setLabel( "" );  // Compilato nel fld_tipo_modifica_prezzo_Change()

        $this->fld1_n_art_diversi->disable();
        $this->fld1_tot_originale->disable();
        $this->fld1_variazione->disable();
        $this->fields->totdoc->disable();

        $this->fld1_n_art_diversi->label->setWidth( 150 );
        $this->fld1_tot_originale->label->setWidth( 150 );
        $this->fld1_variazione->label->setWidth( 100 );
        $this->fields->totdoc->label->setWidth( 100 );
        $this->fld1_valore->label->setWidth( 150 );

        $this->fld1_n_art_diversi->setWidth( 75 );
        $this->fld1_tot_originale->setWidth( 75 );
        $this->fld1_variazione->setWidth( 250 );
        $this->fields->totdoc->setWidth( 100+75 );
        $this->fld1_valore->setWidth( 75 );


        $this->fld1_valore->setStyleProperty( "border", "1px solid black" );  // Viene impostato anche in bu1_esegui_modifica_tutti_click()
        $this->fld1_valore->setNewValue( 0 );

        
        $values = array();
        $values[] = array( "id" => "1", "desc" => "a) Suddividi l'importo sul prezzo di tutti gli articoli (considera come sconto se negativo o spese di consegna se positivo)" );
        $values[] = array( "id" => "2", "desc" => "b) Applica una variazione percentuale al prezzo di ogni articolo" );
        $values[] = array( "id" => "3", "desc" => "c) Applica una variazione assoluta al prezzo di ogni articolo (aggiunge l'importo da considerare)" );
        $values[] = array( "id" => "4", "desc" => "d) Ripristina prezzi originali" );
        $this->build( "p4a_array_source", "array_source" ); 
        $this->array_source->load( $values ); 
        $this->array_source->setPk( "id" ); 
        $this->build( "p4a_field", "fld1_tipo_modifica_prezzo" );
        $this->fld1_tipo_modifica_prezzo->setLabel( "Scelta" );
        $this->fld1_tipo_modifica_prezzo->label->setWidth( 150 );
        $this->fld1_tipo_modifica_prezzo->setType( "radio" );
        $this->fld1_tipo_modifica_prezzo->setSource( $this->array_source ); 
        $this->fld1_tipo_modifica_prezzo->setSourceDescriptionField( "desc" );
        $this->fld1_tipo_modifica_prezzo->setValue( 1 );
        $this->fld1_tipo_modifica_prezzo->addAction( "onChange" );
        $this->intercept( $this->fld1_tipo_modifica_prezzo, "onChange", "fld1_tipo_modifica_prezzo_Change" );

        // Bottone "Esegui modifica"
        $this->build( "p4a_button", "bu1_esegui_modifica_tutti" );
        $this->bu1_esegui_modifica_tutti->setLabel( "Esegui modifica..." );
        $this->bu1_esegui_modifica_tutti->setIcon( "execute" );
        $this->bu1_esegui_modifica_tutti->addAction( "onClick" );
        $this->intercept( $this->bu1_esegui_modifica_tutti, "onClick", "bu1_esegui_modifica_tutti_click" );
        $this->bu1_esegui_modifica_tutti->requireConfirmation( "onClick", "Confermi la modifica alla quantita' degli articoli in elenco?" );
 */
        

        // --------------------------- Frame 2: Modifica quantita singolo articolo
        $this->build( "p4a_field", "fld2_nuova_qta" );

        $this->fields->quantita->setLabel( "Quantita' in ordine" );
        $this->fields->codutente->setLabel( "Utente" );
        $this->fields->codutente->setSource( $this->ds_utente );
        $this->fields->codutente->setSourceValueField( "codice" );
        $this->fields->codutente->setSourceDescriptionField( "descrizione" );

        $this->fields->descrizione->setLabel( "Articolo" );
        $this->fld2_nuova_qta->setLabel( "Nuova Quantita'" );

        $this->fields->quantita->label->setWidth( 150 );
        $this->fields->codutente->label->setWidth( 150 );
        $this->fields->descrizione->label->setWidth( 150 );
        $this->fld2_nuova_qta->label->setWidth( 150 );

        $this->fields->quantita->setWidth( 250 );
        $this->fields->descrizione->setWidth( 800 );
        $this->fld2_nuova_qta->setWidth( 100 );
        

        $this->fields->descrizione->setFontWeight( "bold" );
        $this->fields->descrizione->setFontColor( "black" );
        $this->fld2_nuova_qta->setStyleProperty( "border", "1px solid black" );  // Viene impostato anche in bu2_esegui_modifica_singolo_click()

        
//TODO: Non dev'essere possibile spuntare entrambi i check, quindi aggiungere l'evento click che toglie l'eventuale spunta all'altro        
        // Check: articolo momentaneamente non disponibile (consegna rinviata)
        $this->build( "p4a_field", "ck2_art_ora_non_dispo" );
        $this->ck2_art_ora_non_dispo->setType( "checkbox" );
        $this->ck2_art_ora_non_dispo->setLabel( "Consegna rinviata" );
        $this->ck2_art_ora_non_dispo->setTooltip( "Articolo MOMENTANEAMENTE non disponibile (consegna RINVIATA)" );
        $this->ck2_art_ora_non_dispo->label->setWidth( 150 );
//TODO: Da gestire...        
$this->ck2_art_ora_non_dispo->setInvisible();        

        // Check: articolo non disponibile (consegna annullata)
        $this->build( "p4a_field", "ck2_art_non_dispo" );
        $this->ck2_art_non_dispo->setType( "checkbox" );
        $this->ck2_art_non_dispo->setLabel( "Consegna annullata" );
        $this->ck2_art_non_dispo->setTooltip( "Articolo DEFINITIVAMENTE non disponibile (consegna ANNULLATA)" );
        $this->ck2_art_non_dispo->label->setWidth( 150 );
//TODO: Da gestire...        
$this->ck2_art_non_dispo->setInvisible();        

        // Bottone "Esegui modifica"
        $this->build( "p4a_button", "bu2_esegui_modifica_singolo" );
        $this->bu2_esegui_modifica_singolo->setLabel( "Esegui modifica" );
        $this->bu2_esegui_modifica_singolo->setIcon( "execute" );
        $this->bu2_esegui_modifica_singolo->addAction( "onClick" );
        $this->intercept( $this->bu2_esegui_modifica_singolo, "onClick", "bu2_esegui_modifica_singolo_click" );
        $this->bu2_esegui_modifica_singolo->requireConfirmation( "onClick", "Confermi la modifica alla quantita' dell'articolo selezionato?" );


        // -------------------------------------------- Fieldset filtro iniziale
        $this->build( "p4a_fieldset", "fs_filtro" );
        $this->fs_filtro->setTitle( "Filtro" );
        $this->fs_filtro->setWidth( E3G_FIELDSET_SEARCH_WIDTH );
        $this->fs_filtro->anchor( $this->fld_filtro_for );
        $this->fs_filtro->anchor( $this->fld_filtro_cat );
        $this->fs_filtro->anchorLeft( $this->fld_filtro_sottocat );
        $this->fs_filtro->anchorLeft( $this->bu_filtra );
        $this->fs_filtro->anchorLeft( $this->bu_mostra_tutto );


        // ------------------------------------------------- Pannello principale
        $this->build( "p4a_tab_pane", "tab_pane" );
        $this->tab_pane->setWidth( E3G_TAB_PANE_WIDTH );        
        $this->tab_pane->pages->build( "p4a_frame", "tab_frame_1" );

            
        $this->tab_pane->pages->tab_frame_1->setLabel( "Modifica Q.ta articolo selezionato" );
        $this->tab_pane->pages->tab_frame_1->anchor( $this->fields->codutente );        
        $this->tab_pane->pages->tab_frame_1->anchor( $this->fields->quantita );        
        $this->tab_pane->pages->tab_frame_1->anchor( $this->fields->descrizione );        
        $this->tab_pane->pages->tab_frame_1->anchorLeft( $this->fld2_nuova_qta );        

        $this->tab_pane->pages->tab_frame_1->anchor( $this->ck2_art_ora_non_dispo );        
        $this->tab_pane->pages->tab_frame_1->anchor( $this->ck2_art_non_dispo );
        
        $this->tab_pane->pages->tab_frame_1->anchorRight( $this->bu2_esegui_modifica_singolo );


        // ---------------------------------------------------- Frame principale
        $frm=& $this->build( "p4a_frame", "frm" );
        $frm->setWidth( E3G_MAIN_FRAME_WIDTH );
        
        $frm->anchor( $this->fs_filtro );
        $frm->anchor( $this->tab_doc_righe );
        $frm->anchor( $this->message );
        $frm->anchor( $this->tab_pane );
        
        e3g_scrivi_footer( $this, $frm );

        // Display
        $this->display( "main", $frm );
        $this->display( "menu", $p4a->menu );
        $this->display( "top", $this->toolbar );
        
        $this->bu_filtra_click();
        $this->tab_doc_righe_AfterClick();
        //$this->fld1_tipo_modifica_prezzo_Change();
    }


    // -------------------------------------------------------------------------
    function main()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();

        parent::main();
    }
    

    // -------------------------------------------------------------------------
    function fld_filtro_for_change()
    // -------------------------------------------------------------------------
    {
        //$this->bu_filtra_click();
    }


    // -------------------------------------------------------------------------
    function fld_filtro_cat_change()
    // -------------------------------------------------------------------------
    {
        $this->ds_filtro_sottocat->setWhere( "tipo = '" . $this->fld_filtro_cat->getNewValue() . "' OR codice = '000'" );      
        $this->ds_filtro_sottocat->load();
    }


    // -------------------------------------------------------------------------
    function bu_filtra_click()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();

        $strwhere =
            "d.visibile = 'N' AND " .
            "d.codtipodoc = '" . $p4a->e3g_azienda_gg_cod_doc_ordine . "' AND " .
            "( d.estratto <> 'S' OR ISNULL(d.estratto) ) AND " .
            "a.centrale = '" . $this->fld_filtro_for->getNewValue() . "' ";
        if ( $this->fld_filtro_cat->getNewValue() != "00" ) {
            $strwhere .= " AND a.tipo = '" . $this->fld_filtro_cat->getNewValue() . "'";
            if ( $this->fld_filtro_sottocat->getNewValue() != "000" )
                $strwhere .= " AND a.catmerce = '" . $this->fld_filtro_sottocat->getNewValue() . "'";
        }

        $this->ds_doc_righe->setWhere( $strwhere ); 
        $this->ds_doc_righe->load();
        $this->ds_doc_righe->firstRow();
        
        $this->tab_doc_righe->syncPageWithSource();
        
        //$this->update_valori_1();
        $this->tab_doc_righe_AfterClick();
    }

    
    // -------------------------------------------------------------------------
    function bu_mostra_tutto_click()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();

        $this->fld_filtro_for->setNewValue( $this->ds_filtro_for->fields->codice->getNewValue() );
        $this->fld_filtro_cat->setNewValue( "00" );
        $this->fld_filtro_sottocat->setNewValue( "000" );

        $this->bu_filtra_click();
    }



    // -------------------------------------------------------------------------
    function tab_doc_righe_AfterClick()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();

        // Aggiorna il valore visualizzato nel campo modificabile del nuovo pezzo
        /*
        $this->fld2_nuovo_prezzo_unitario->setNewValue( $this->fields->nuovo_prezzo_unitario->getNewValue() );

        // Evidenzia le righe con variazione applicata
        if ( $this->fields->prezzo_unitario_originale->getNewValue() <> $this->fields->nuovo_prezzo_unitario->getNewValue() ) {            
            $this->fields->prezzo_unitario_originale->setFontColor( "blue" );
            $this->fld2_nuovo_prezzo_unitario->setFontColor( "purple" );
        }
        else {
            $this->fields->prezzo_unitario_originale->setFontColor( "default" );
            $this->fld2_nuovo_prezzo_unitario->setFontColor( "default" );
        }
        */
        
    }


    /*
     
    // -------------------------------------------------------------------------
    function tab_doc_righe_BeforeDisplay( $obj, $rows ) 
    // -------------------------------------------------------------------------
    {  			
        for( $i=0; $i<count($rows); $i++ ) {
            // Evidenzia la riga selezionata
            if ( $rows[$i]["descrizione"] == $this->ds_doc_righe->fields->descrizione->getNewValue() and 
                 $rows[$i]["um_qta_um"] == $this->ds_doc_righe->fields->um_qta_um->getNewValue() ) 
                $rows[$i]["descrizione"] = "<span style='color:black;font-weight:bold;'>" . $rows[$i]["descrizione"] . "</span>";

            // Evidenzia le righe con variazione applicata
            if ( $rows[$i]["prezzo_unitario_originale"] <> $rows[$i]["nuovo_prezzo_unitario"] ) {
                $rows[$i]["prezzo_unitario_originale"] = "<span style='color:blue;'>" . $rows[$i]["prezzo_unitario_originale"] . "</span>";
                $rows[$i]["nuovo_prezzo_unitario"] = "<span style='color:purple;'>" . $rows[$i]["nuovo_prezzo_unitario"] . "</span>";
            }
        }  
        return $rows;  
    }  
*/
 

    /*
    // -------------------------------------------------------------------------
    function update_valori_1()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();

        if ( $this->ds_doc_righe->getNumRows() == 0 ) {
            $this->message->setIcon( "error" );
            $this->message->setValue( "NON risulta alcun articolo pronto per la consegna." );
            return;
        }

        $this->tot_originale_1 = 0;
        $variazione_1 = 0;

        $this->ds_doc_righe->firstRow();
        for( $r=1; $r<=$this->ds_doc_righe->getNumRows(); $r++ ) {       
            $this->tot_originale_1 += $this->fields->quantita->data_field->getNewValue() ; //* $this->fields->prezzo_unitario_originale->data_field->getNewValue();
            $variazione_1 += $this->fields->quantita->data_field->getNewValue() ; //* $this->fields->delta_prezzo->data_field->getNewValue();

            $this->ds_doc_righe->nextRow();
        }
        $this->ds_doc_righe->firstRow();

        $this->tab_doc_righe->syncPageWithSource();

        //$this->fld1_n_art_diversi->setNewValue( $this->ds_doc_righe->getNumRows() );
        $this->fld1_tot_originale->setNewValue( $this->tot_originale_1 );
        if ( $this->tot_originale_1 <> 0 )
            $this->fld1_variazione->setNewValue( $variazione_1 . " euro = " . 
                number_format( $variazione_1/$this->tot_originale_1*100, $p4a->e3g_azienda_n_decimali_prezzi ) . " %" );
        else
            $this->fld1_variazione->setNewValue( "0 euro" ); 
    }
	*/
    
    // 2) Modifica quantita dell'articolo selezionato
    // -------------------------------------------------------------------------
    function bu2_esegui_modifica_singolo_click()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();
        //TODO: Da gestire...        
        //        $this->ck2_art_ora_non_dispo         
        //        $this->ck2_art_non_dispo
 
        if ( !is_numeric( $this->fld2_nuova_qta->getNewValue() ) ) {
            $this->fld2_nuova_qta->setStyleProperty( "border", "1px solid red" );
            $this->message->setIcon( "error" );
            $this->message->setValue( "Il valore specificato deve essere un numero." );
            return;
        }
        else
            $this->fld2_nuova_qta->setStyleProperty( "border", "1px solid black" );

        //$this->fields->quantita->getNewValue() // QTA ORIGINALE 
        
        // Aggiorna la riga dettaglio selezionata
        // rimpiazzo la virgola con il punto per consentire INSERT INTO  
		$qta_cons = str_replace(",", ".", $this->fld2_nuova_qta->getUnformattedNewValue());

		if (is_numeric($qta_cons))
		{
			$sql_txt =
	            "UPDATE " . $p4a->e3g_prefix . "docr " . 
	            "   SET quantita = ".$qta_cons .
	            " WHERE idriga = ".$this->fields->idriga->getUnformattedNewValue();                        
        							// usare getUnformattedNewValue() perchè se ci sono migliaia di righe
        							// l'IDRIGA viene visualizzato con il separatore delle migliaia 
        							// che manda in errore la query AP 02.07.10
        							
			$this->fld2_nuova_qta->setNewValue("");
        
    	    // FARE UPDATE DELLE RIGHE VISIBILI 
        	// --> OVVERO ORDINE A FORNITORE ???
	        // oppure tenerlo così per avere una traccia dell'ordine originale? 
                    
	        $db->query( $sql_txt );
		
	        // Aggiorna anche i totali di testata (doct.imponibile e doct.totdoc)
	        $sql_txt =
	            "UPDATE " . $p4a->e3g_prefix . "doct AS t " . 
	            "   SET t.imponibile = ( SELECT SUM( (r.prezzo+r.delta_prezzo) * (r.quantita+r.quantita2) ) " .
	            "                          FROM " . $p4a->e3g_prefix . "docr AS r " .
	            "                         WHERE r.iddocr = t.iddoc " .
	            "                           AND r.visibile = 'N' AND r.codtipodoc = '" . $p4a->e3g_azienda_gg_cod_doc_ordine . "' " .
	            "                           AND ( r.estratto <> 'S' OR ISNULL(r.estratto) ) ), " .
	            "       t.totdoc     = t.imponibile " .
	            " WHERE t.iddoc = " . $this->fields->iddoc->getUnformattedNewValue();
	        $db->query( $sql_txt );
	        
	        //$this->update_valori_1();
	
	        $this->message->setIcon( "info" );
	        $this->message->setValue( "Elaborazione eseguita: la quantita' dell'articolo e' stata aggiornata." );
		}
		else
		{
	        $this->message->setIcon( "info" );
	        $this->message->setValue( "Elaborazione fallita: inserire un valore numerico per la nuova quantita'" );
		}
        
	}


}
?>