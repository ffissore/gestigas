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


class cassa_comune extends P4A_Mask
{
     var $ds_mov_cassa_where_periodo;
     var $ds_mov_cassa_where_altro;

    // -------------------------------------------------------------------------
    function cassa_comune()
    // -------------------------------------------------------------------------
    {
        $this->p4a_mask();
        $this->addCss( E3G_TEMPLATE_DIR . 'css/style.css' );
        $p4a =& p4a::singleton();
        
        $this->setTitle( "Cassa comune" );
        $this->setIcon( "kcalc" );  
        
        
        //--------------------------------------------- Sorgente dati principale
        $this->build( "p4a_db_source", "ds_mov_cassa" );

        $this->ds_mov_cassa->setFields( array( 
            "_cassa.*", 
            "_causali_mov_cassa.causale_mov_cassa", 
            "_causali_mov_cassa.segno", 
            "_causali_mov_cassa.tipo_rif", 
            "ur.tipocfa",
            "ur.descrizione AS utente_rif",
            "uc.descrizione AS utente_crea" ) ); 
        $this->ds_mov_cassa->setTable( "_cassa" );
        $this->ds_mov_cassa->addJoin( "_causali_mov_cassa", "_causali_mov_cassa.id_causale_mov_cassa = _cassa.id_causale_mov_cassa" );
        $this->ds_mov_cassa->addJoin( $p4a->e3g_prefix . "anagrafiche AS ur", "ur.idanag = _cassa.id_utente_rif" );
        $this->ds_mov_cassa->addJoin( $p4a->e3g_prefix . "anagrafiche AS uc", "uc.idanag = _cassa.id_utente_crea" );
        $this->ds_mov_cassa->setWhere( "1 = 0" );  // Impostato in bu_filtroClick()
        $this->ds_mov_cassa->addOrder( "_cassa.data_mov" );

        $this->ds_mov_cassa->setPk( "id_cassa" );
        $this->ds_mov_cassa->setPageLimit( $p4a->e3g_utente_db_source_page_limit );
        $this->ds_mov_cassa->load();
//      $this->ds_mov_cassa->firstRow(); eseguito nel bu_filtroClick()
        
        $this->setSource( $this->ds_mov_cassa );

        // Fields properties 
        $fields =& $this->fields;
        
        // Campi Obbligatori 
        $this->mf = array( "data_mov", "importo", "id_causale_mov_cassa" );
        foreach( $this->mf as $mf ) {
            $fields->$mf->label->setFontWeight( "bold" );
        }


        //------------------------------------------------------ Altri db source
        // Causali
        $this->build( "p4a_db_source", "ds_causali_mov_cassa" );
        $this->ds_causali_mov_cassa->setFields( array( 
            "*",
            "CASE segno WHEN  1 THEN CONCAT( '[Entrata] ', causale_mov_cassa ) " .
            "           WHEN -1 THEN CONCAT( '[Uscita] ', causale_mov_cassa ) " .
            "           ELSE causale_mov_cassa END AS descrizione" ) );
        $this->ds_causali_mov_cassa->setTable( "_causali_mov_cassa" );
        $this->ds_causali_mov_cassa->addOrder( "segno" );
        $this->ds_causali_mov_cassa->setPk( "id_causale_mov_cassa" );
        $this->ds_causali_mov_cassa->load();

        // Utenti e Fornitori
        $this->build( "p4a_db_source", "ds_ute_for" );
        $this->ds_ute_for->setFields( array( 
            "idanag", 
            "stato", 
            "tipocfa",
            "CASE tipocfa WHEN 'C' THEN CONCAT( '[Utente] ',    descrizione, IF( stato <> 1, ' (NON attivo)', '' ) ) " .
            "             WHEN 'F' THEN CONCAT( '[Fornitore] ', descrizione, IF( stato <> 1, ' (NON attivo)', '' ) ) " .
            "             ELSE descrizione END AS descrizione" ) );
        $this->ds_ute_for->setTable( $p4a->e3g_prefix . "anagrafiche" );
        $this->ds_ute_for->setWhere( "( tipocfa = 'C' AND tipoutente <> 'A' ) OR ( tipocfa = 'F' ) OR ( idanag = 0 )" );
        $this->ds_ute_for->addOrder( "tipocfa", "DESC" );
        $this->ds_ute_for->addOrder( "stato" );
        $this->ds_ute_for->addOrder( "descrizione" );
        $this->ds_ute_for->setPk( "idanag" );
        $this->ds_ute_for->load();


        //-------------------------------------------------------------- Toolbar
        $this->build( "p4a_standard_toolbar", "toolbar" );
        $this->toolbar->setMask( $this );


        //------------------------------------------------------- Filtro ricerca
        // Filtro dalla data ... alla data ...
        $this->build( "p4a_field", "fld_dalla_data" );
        $this->fld_dalla_data->setLabel( "Dalla data" );
        $this->fld_dalla_data->setType( "date" );
        $this->fld_dalla_data->setWidth( 100 );

        $this->build( "p4a_field", "fld_alla_data" );
        $this->fld_alla_data->setLabel( "Alla data" );
        $this->fld_alla_data->setType( "date" );
        $this->fld_alla_data->setWidth( 100 );

        // Filtro solo da validare
        $this->build( "p4a_field", "fld_ck_solo_da_validare" );
        $this->fld_ck_solo_da_validare->setType( "checkbox" );
        $this->fld_ck_solo_da_validare->setLabel( "Da validare" );
        $this->fld_ck_solo_da_validare->setTooltip( "Visualizza solo i movimenti da validare" );
        
        // Causale      
        $this->build( "p4a_field", "fld_causale_mov_cassa" );
        $this->fld_causale_mov_cassa->setLabel( "Causale" );
        $this->fld_causale_mov_cassa->setWidth( 250 );
        $this->fld_causale_mov_cassa->setType( "select" );
        $this->fld_causale_mov_cassa->setSource( $this->ds_causali_mov_cassa );
        $this->fld_causale_mov_cassa->setSourceValueField( "id_causale_mov_cassa" );
        $this->fld_causale_mov_cassa->setSourceDescriptionField( "descrizione" );
        $this->fld_causale_mov_cassa->setNewValue( 0 );

        // Utente di riferimento      
        $this->build( "p4a_field", "fld_utente_rif" );
        $this->fld_utente_rif->setLabel( "Utente/Fornitore rif." );
        $this->fld_utente_rif->label->setWidth( 150 );
        $this->fld_utente_rif->setTooltip( "Visualizza solo movimenti riferiti a questo utente (o fornitore)" );
        $this->fld_utente_rif->setWidth( 250 );
        $this->fld_utente_rif->setType( "select" );
        $this->fld_utente_rif->setSource( $this->ds_ute_for );
        $this->fld_utente_rif->setSourceValueField( "idanag" );
        $this->fld_utente_rif->setSourceDescriptionField( "descrizione" );
        if ( $this->utente_abilitato() ) {
            $this->fld_utente_rif->setNewValue( 0 );
        }
        else {
            $this->fld_utente_rif->setNewValue( $p4a->e3g_utente_idanag );
            $this->fld_utente_rif->disable();
        }

        // Bottone "Filtro"      
        $this->build( "p4a_button", "bu_filtro" );
        $this->bu_filtro->setWidth( 150 );
        $this->bu_filtro->setLabel( "Filtra" );
        $this->bu_filtro->setIcon( "find" );
        $this->bu_filtro->setSize( 16 );
        $this->bu_filtro->addAction( "onClick" );
        $this->intercept( $this->bu_filtro, "onClick", "bu_filtroClick" );
        
        // Bottone "Annulla Filtro"
        $this->build("p4a_button", "bu_annulla_filtro");
        $this->bu_annulla_filtro->setWidth( 150 );
        $this->bu_annulla_filtro->setLabel( "Annulla" );
        $this->bu_annulla_filtro->setIcon( "cancel" );
        $this->bu_annulla_filtro->setSize( 16 );
        $this->bu_annulla_filtro->addAction( "onClick" );
        $this->intercept( $this->bu_annulla_filtro, "onClick", "bu_annulla_filtroClick" );

        $this->build( "p4a_fieldset","fs_search" );
        $this->fs_search->setTitle( "Filtro" );
        $this->fs_search->setWidth( E3G_TABLE_IN_TAB_PANE_WIDTH -15 );

        $this->fs_search->anchor( $this->fld_dalla_data );
        $this->fs_search->anchorLeft( $this->fld_alla_data );
        $this->fs_search->anchorRight( $this->bu_annulla_filtro );
        $this->fs_search->anchorRight( $this->bu_filtro );
        $this->fs_search->anchor( $this->fld_ck_solo_da_validare );
        $this->fs_search->anchor( $this->fld_causale_mov_cassa );
        $this->fs_search->anchorRight( $this->fld_utente_rif );


        //---------------------------------------------------- Eventuale warning
        $this->build( "p4a_message", "msg_info" );
        $this->msg_info->setWidth( 700 );


        //----------------------------------------------------- Griglia centrale 
        $this->build( "p4a_table", "tab_mov_cassa" );
        $this->tab_mov_cassa->showNavigationBar();
        $this->tab_mov_cassa->setWidth( E3G_TABLE_IN_TAB_PANE_WIDTH );
        $this->tab_mov_cassa->setTitle( "Movimenti di cassa" );
        $this->tab_mov_cassa->setSource( $this->ds_mov_cassa );
        $this->tab_mov_cassa->setVisibleCols( array( "data_mov", "importo", "causale_mov_cassa", "tipocfa", "utente_rif", "validato" ) );
        $this->intercept( $this->tab_mov_cassa->rows, "beforeDisplay", "tab_mov_cassa_BeforeDisplay" );  
        
        $this->tab_mov_cassa->cols->data_mov->setLabel( "Data mov." );
        $this->tab_mov_cassa->cols->importo->setLabel( "Importo" );
        $this->tab_mov_cassa->cols->causale_mov_cassa->setLabel( "Causale" );
        $this->tab_mov_cassa->cols->tipocfa->setLabel( "Tipo rif." );
        $this->tab_mov_cassa->cols->utente_rif->setLabel( "Utente/Fornitore rif." );
        $this->tab_mov_cassa->cols->validato->setLabel( "Validato" );

        $this->tab_mov_cassa->cols->data_mov->setWidth( 80 );
        $this->tab_mov_cassa->cols->importo->setWidth( 80 );
        //$this->tab_mov_cassa->cols->causale_mov_cassa->setWidth();  Per differenza
        $this->tab_mov_cassa->cols->tipocfa->setWidth( 80 );
        $this->tab_mov_cassa->cols->utente_rif->setWidth( 250 );
        $this->tab_mov_cassa->cols->validato->setWidth( 50 );
        
        
        //------------------------------------------------------ Vista dettaglio
        $fields->data_mov->setLabel( "Data mov." );
        $fields->data_mov->setWidth( 100 );
        $fields->data_mov->setType( "date" );

        $fields->validato->setLabel( "Validato" );
        $fields->validato->setType( "checkbox" );
        if ( !$this->utente_abilitato() ) 
            $fields->validato->disable();

        $fields->id_causale_mov_cassa->setLabel( "Causale" );
        $fields->id_causale_mov_cassa->setWidth( 250 );
        $fields->id_causale_mov_cassa->setType( "select" );
        $fields->id_causale_mov_cassa->setSource( $this->ds_causali_mov_cassa );
        $fields->id_causale_mov_cassa->setSourceValueField( "id_causale_mov_cassa" );
        $fields->id_causale_mov_cassa->setSourceDescriptionField( "descrizione" );

        // Utente di riferimento
        $fields->id_utente_rif->setLabel( "Utente/Fornitore rif." );
        $fields->id_utente_rif->label->setWidth( 150 );
        $fields->id_utente_rif->setTooltip( "Utente (o fornitore) a cui si riferisce il movimento" );
        $fields->id_utente_rif->setWidth( 250 );
        $fields->id_utente_rif->setType( "select" );
        $fields->id_utente_rif->setSource( $this->ds_ute_for );
        $fields->id_utente_rif->setSourceValueField( "idanag" );
        $fields->id_utente_rif->setSourceDescriptionField( "descrizione" );
        if ( !$this->utente_abilitato() ) 
            $fields->id_utente_rif->disable();

        $fields->importo->setLabel( "Importo [euro]" );
        $fields->importo->setWidth( 200 );
        $fields->importo->data_field->setType( "float" );

        $fields->note->setLabel( "Note" );
        $fields->note->setType( "textarea" );
        $fields->note->setWidth( 750 );
        $fields->note->setHeight( 45 );

        $fields->data_ins->setLabel( "Data inserimento" );
        $fields->data_ins->setWidth( 130 );
        $fields->data_ins->disable();

        $fields->utente_crea->setLabel( "Utente" );
        $fields->utente_crea->setWidth( 250 );
        $fields->utente_crea->disable();

        $fields->data_agg->setLabel( "Ultima modifica" );
        $fields->data_agg->setWidth( 130 );
        $fields->data_agg->disable();

        // ------------------------------------ Fieldset principale di dettaglio
        $this->build( "p4a_fieldset", "fs_mov_cassa" );
        $this->fs_mov_cassa->setTitle( "Dettaglio movimento" );
        $this->fs_mov_cassa->setWidth( E3G_TABLE_IN_TAB_PANE_WIDTH -15 );

        $this->fs_mov_cassa->anchor( $fields->data_mov );
        $this->fs_mov_cassa->anchor( $fields->validato );
        $this->fs_mov_cassa->anchor( $fields->id_causale_mov_cassa );
        $this->fs_mov_cassa->anchorRight( $fields->id_utente_rif );
        $this->fs_mov_cassa->anchor( $fields->importo );
        $this->fs_mov_cassa->anchor( $fields->note, "130px" );
        

        // --------------------------------------- Fieldset creazione e modifica
        $this->build( "p4a_fieldset", "fs_crea_mod" );
        $this->fs_crea_mod->setTitle( "Inserimento e modifica" );
        $this->fs_crea_mod->setWidth( E3G_TABLE_IN_TAB_PANE_WIDTH -15 );

        $this->fs_crea_mod->anchor( $fields->data_ins );
        $this->fs_crea_mod->anchorLeft( $fields->utente_crea );
        $this->fs_crea_mod->anchorLeft( $fields->data_agg );


        // -------------------------------- Sorgente dati "Riassunto per utente"
        $this->build( "p4a_db_source", "ds_riassunto_ute" );

        $this->ds_riassunto_ute->setSelect(
            "id_utente_rif, " .
            "ur.stato, " .
            "IF( ur.stato = 1, ur.descrizione, CONCAT( ur.descrizione, ' (NON attivo)' ) ) AS desc_utente, " .
            "ur.email, " .
            "COUNT( * ) AS n_movimenti, " .
            "SUM( importo ) AS saldo" ); 
        $this->ds_riassunto_ute->setTable( "_cassa" );
        $this->ds_riassunto_ute->addJoin( $p4a->e3g_prefix . "anagrafiche AS ur", "ur.idanag = _cassa.id_utente_rif AND ur.tipocfa = 'C'" );
        $this->ds_riassunto_ute->setWhere( "_cassa.prefix = '" . $p4a->e3g_prefix . "' AND validato = 1" );
        $this->ds_riassunto_ute->addGroup( "id_utente_rif" );
        $this->ds_riassunto_ute->addOrder( "ur.stato" );  // Per ultimi gli utenti NON attivi
        $this->ds_riassunto_ute->addOrder( "desc_utente" );

        $this->ds_riassunto_ute->setPk( "ur.email" );
        $this->ds_riassunto_ute->setPageLimit( $p4a->e3g_utente_db_source_page_limit );
        $this->ds_riassunto_ute->load();
        $this->ds_riassunto_ute->firstRow(); 


        // -------------------------------------- Tabella "riassunto per utente"
        $this->build( "p4a_table", "tab_riassunto_ute" );
        $this->tab_riassunto_ute->setWidth( E3G_TABLE_IN_TAB_PANE_WIDTH );
        $this->tab_riassunto_ute->setSource( $this->ds_riassunto_ute );
        $this->tab_riassunto_ute->setVisibleCols( array("id_utente_rif", "desc_utente", "email", "n_movimenti", "saldo") );
        $this->tab_riassunto_ute->showNavigationBar();
        $this->intercept( $this->tab_riassunto_ute->rows, "beforeDisplay", "tab_riassunto_ute_beforeDisplay" );  
        
        $this->tab_riassunto_ute->cols->id_utente_rif->setVisible( false );
        
        $this->tab_riassunto_ute->cols->desc_utente->setLabel( "Utente" );
        $this->tab_riassunto_ute->cols->email->setLabel( "e-mail" );
        $this->tab_riassunto_ute->cols->n_movimenti->setLabel( "N. movimenti" );
        $this->tab_riassunto_ute->cols->saldo->setLabel( "Saldo" );

//      $this->tab_riassunto_ute->cols->desc_utente->setWidth();  Per differenza
        $this->tab_riassunto_ute->cols->email->setWidth( 200 );
        $this->tab_riassunto_ute->cols->n_movimenti->setWidth( 100 );  
        $this->tab_riassunto_ute->cols->saldo->setWidth( 100 );

        $this->tab_riassunto_ute->data->fields->saldo->setType("float");


        // ------------------------------ Sorgente dati "Riassunto per ornitore"
        $this->build( "p4a_db_source", "ds_riassunto_for" );

        $this->ds_riassunto_for->setSelect(
            "id_utente_rif, " .
            "ur.stato, " .
            "IF( ur.stato = 1, ur.descrizione, CONCAT( ur.descrizione, ' (NON attivo)' ) ) AS desc_fornitore, " .
            "ur.email, " .
            "COUNT( * ) AS n_movimenti, " .
            "SUM( importo ) AS saldo" ); 
        $this->ds_riassunto_for->setTable( "_cassa" );
        $this->ds_riassunto_for->addJoin( $p4a->e3g_prefix . "anagrafiche AS ur", "ur.idanag = _cassa.id_utente_rif AND ur.tipocfa = 'F'" );
        $this->ds_riassunto_for->setWhere( "_cassa.prefix = '" . $p4a->e3g_prefix . "' AND validato = 1" );
        $this->ds_riassunto_for->addGroup( "id_utente_rif" );
        $this->ds_riassunto_for->addOrder( "ur.stato" );  // Per ultimi i fornitori NON attivi
        $this->ds_riassunto_for->addOrder( "desc_fornitore" );

        $this->ds_riassunto_for->setPk( "ur.email" );
        $this->ds_riassunto_for->setPageLimit( $p4a->e3g_utente_db_source_page_limit );
        $this->ds_riassunto_for->load();
        $this->ds_riassunto_for->firstRow(); 


        // ----------------------------------- Tabella "riassunto per fornitore"
        $this->build( "p4a_table", "tab_riassunto_for" );
        $this->tab_riassunto_for->setWidth( E3G_TABLE_IN_TAB_PANE_WIDTH );
        $this->tab_riassunto_for->setSource( $this->ds_riassunto_for );
        $this->tab_riassunto_for->setVisibleCols( array("id_utente_rif", "desc_fornitore", "email", "n_movimenti", "saldo") );
        $this->tab_riassunto_for->showNavigationBar();
        $this->intercept( $this->tab_riassunto_for->rows, "beforeDisplay", "tab_riassunto_for_beforeDisplay" );  
        
        $this->tab_riassunto_for->cols->id_utente_rif->setVisible( false );
        
        $this->tab_riassunto_for->cols->desc_fornitore->setLabel( "Fornitore" );
        $this->tab_riassunto_for->cols->email->setLabel( "e-mail" );
        $this->tab_riassunto_for->cols->n_movimenti->setLabel( "N. movimenti" );
        $this->tab_riassunto_for->cols->saldo->setLabel( "Saldo" );

//      $this->tab_riassunto_for->cols->desc_fornitore->setWidth();  Per differenza
        $this->tab_riassunto_for->cols->email->setWidth( 200 );
        $this->tab_riassunto_for->cols->n_movimenti->setWidth( 100 );  
        $this->tab_riassunto_for->cols->saldo->setWidth( 100 );

        $this->tab_riassunto_for->data->fields->saldo->setType("float");


        // ------------------------------------------------- Pannello principale
        $this->build( "p4a_tab_pane", "tab_pane" );      
        $this->tab_pane->pages->build( "p4a_frame", "tpf_movimenti" );
        if ( $this->utente_abilitato() ) {
            $this->tab_pane->pages->build( "p4a_frame", "tbf_riassunto_ute" );
            $this->tab_pane->pages->build( "p4a_frame", "tbf_riassunto_for" );
        }

        $this->tab_pane->pages->tpf_movimenti->setLabel( "Elenco movimenti" ); 
        $this->tab_pane->pages->tpf_movimenti->anchor( $this->fs_search );        
        $this->tab_pane->pages->tpf_movimenti->anchor( $this->msg_info );        
        $this->tab_pane->pages->tpf_movimenti->anchor( $this->tab_mov_cassa );        
        $this->tab_pane->pages->tpf_movimenti->anchor( $this->fs_mov_cassa );        
        $this->tab_pane->pages->tpf_movimenti->anchor( $this->fs_crea_mod );        

        if ( $this->utente_abilitato() ) {
            $this->tab_pane->pages->tbf_riassunto_ute->setLabel( "Riassunto per utente" );
            $this->tab_pane->pages->tbf_riassunto_ute->anchor( $this->tab_riassunto_ute );        
            $this->tab_pane->pages->tbf_riassunto_for->setLabel( "Riassunto per fornitore" );
            $this->tab_pane->pages->tbf_riassunto_for->anchor( $this->tab_riassunto_for );        
        }


        // ---------------------------------------------------- Frame principale
        $frm=& $this->build( "p4a_frame", "frm" );
        $frm->setWidth( E3G_MAIN_FRAME_WIDTH );

        $frm->anchor( $this->tab_pane );

        e3g_scrivi_footer( $this, $frm );

        // Display
        $this->display( "main", $frm );
        $this->display( "menu", $p4a->menu );
        $this->display( "top", $this->toolbar );

        $this->bu_filtroClick();
    }
    

    // -------------------------------------------------------------------------
    function main()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();

        // Se non ci sono record, la finestra si predispone in inserimento, ma bisogna generare l'evento newRow()
        if ( $this->data->getNumRows() == 0 )
            $this->newRow();

        parent::main();

        foreach( $this->mf as $mf )
            $this->fields->$mf->unsetStyleProperty( "border" );
    }
    

    // -------------------------------------------------------------------------
    function utente_abilitato()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        
        // Sono abilitati all'inserimento/modifica dei movimenti di cassa
        // gli amministratori (locali e globali) ed i cassieri
        return ( $p4a->e3g_utente_tipo == "A" or $p4a->e3g_utente_tipo == "AS" or $p4a->e3g_utente_cassiere ); 
    }


    // Calcolo e visualizzazione saldi
    // -------------------------------------------------------------------------
    function mostra_saldo()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();
       
        /* Nessun periodo -> 
         * 1)     23 movimenti - Saldo finale: 123 euro
         * Periodo impostato -> 
         * 2)     45 movimenti dal 01/01/2009 - Saldo del periodo: 111 euro - Saldo finale: 123 euro
         * 3)     56 movimenti fino al 30/04/2009 - Saldo del periodo: 111 euro - Saldo finale: 123 euro
         * 4)     67 movimenti dal 01/01/2009 al 30/04/2009 - Saldo del periodo: 111 euro - Saldo finale: 123 euro
         */

        $saldo_finale = (double) $db->queryOne(
            "SELECT SUM( importo ) FROM _cassa " .
            " WHERE _cassa.prefix = " . $p4a->e3g_prefix . " AND _cassa.validato = 1" );

        $str_title = $this->data->getNumRows() . " moviment" . ( $this->data->getNumRows()==1 ? "o" : "i" );

        if ( $this->fld_dalla_data->getNewValue() == "" and $this->fld_alla_data->getNewValue() == "" ) {       
            // 1) Nessun periodo
            $str_title .= " - Saldo finale: $saldo_finale euro";
        }
        else {
            $saldo_del_periodo = $db->queryOne(
                "SELECT SUM( importo ) AS saldo FROM _cassa " .
                " WHERE _cassa.prefix = " . $p4a->e3g_prefix . " AND _cassa.validato = 1 AND " . $this->ds_mov_cassa_where_periodo );
        
            if ( $this->fld_dalla_data->getNewValue() != "" and $this->fld_alla_data->getNewValue() == "" ) {       
                // 2) Periodo dalla data ...
                $str_title .= " dal " . $this->fld_dalla_data->getNewValue();
            }
            elseif ( $this->fld_dalla_data->getNewValue() == "" and $this->fld_alla_data->getNewValue() != "" ) {       
                // 3) Periodo alla data ...
                $str_title .= " fino al " . $this->fld_alla_data->getNewValue();
            }
            elseif ( $this->fld_dalla_data->getNewValue() != "" and $this->fld_alla_data->getNewValue() != "" ) {       
                // 4) Periodo dalla data ... alla data ...
                $str_title .= " dal " . $this->fld_dalla_data->getNewValue() . " al " . $this->fld_alla_data->getNewValue();
            }

            $str_title .= " - Saldo del periodo: $saldo_del_periodo euro - Saldo finale: $saldo_finale euro";
        }

        $this->tab_mov_cassa->setTitle( $str_title );
    }


    // -------------------------------------------------------------------------
    function newRow()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();
        
        parent::newRow();

        $this->fields->prefix->setNewValue( $p4a->e3g_prefix );                   
        $this->fields->data_mov->setNewValue( date("d-m-Y") );
        $this->fields->importo->setNewValue( 0 ); 
        $this->fields->id_utente_crea->setNewValue( $p4a->e3g_utente_idanag ); 
        $this->fields->data_ins->setNewValue( date("Y-m-d H:i:s") ); 

        // Se il caricamento è da parte del cassiere, allora propone il movimento già validato 
        $this->fields->validato->setNewValue( $p4a->e3g_utente_cassiere );

        // Solo per il cassiere viene lasciato vuoto il campo "utente di riferimento"
        // mentre per gli altri viene precompilato con sé stessi
        if ( $this->utente_abilitato() )
            $this->fields->id_utente_rif->setNewValue( 0 );
        else
            $this->fields->id_utente_rif->setNewValue( $p4a->e3g_utente_idanag );

        $this->fields->id_causale_mov_cassa->setNewValue( 0 );
    }

    
    // -------------------------------------------------------------------------
    function saveRow()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();
        
        if ( !is_numeric($this->fields->id_cassa->getNewValue()) ) {
            $maxid = $db->queryOne( "SELECT MAX( id_cassa ) FROM _cassa" );
            if ( is_numeric($maxid) )
                $maxid++;
            else 
                $maxid = 1;
            $this->fields->id_cassa->setNewValue( $maxid );
        }

        $error_text = "";

        // Per i normali utenti non è possibile modificare un movimento già validato
        if ( !$this->utente_abilitato() and $this->fields->validato->getNewValue() ) {
            $error_text = "Non e' possibile modificare un movimento gia' validato.";
        }
        elseif ( $this->fields->importo->getNewValue() == 0 ) {
            $this->fields->importo->setStyleProperty( "border", "1px solid red" );
            $error_text = "Compilare un importo diverso da zero.";
        }
        // Verifica assegnazione causale_mov (obbligatorio)
        elseif ( $this->fields->id_causale_mov_cassa->getNewValue() == 0 ) {
            $this->fields->id_causale_mov_cassa->setStyleProperty( "border", "1px solid red" );
            $error_text = "Compilare la causale del movimento.";
        }
/* TODO Questi controlli sembrano non funzionare correttamente...        
        // Verifica corrispondenza segno importo/causale (entrata)
        elseif ( $this->fields->segno->getNewValue() == 1 and $this->fields->importo->getNewValue() < 0 ) {
            $this->fields->importo->setStyleProperty( "border", "1px solid red" );
            $error_text = "La causale scelta prevede un movimento di entrata (positivo).";
        }
        // Verifica corrispondenza segno importo/causale (uscita)
        elseif ( $this->fields->segno->getNewValue() == -1 and $this->fields->importo->getNewValue() > 0 ) {
            $this->fields->importo->setStyleProperty( "border", "1px solid red" );
            $error_text = "La causale scelta prevede un movimento di uscita (negativo).";
        }
        // Verifica corrispondenza tipo riferimento: FORNITORE
        elseif ( $this->fields->tipo_rif->getNewValue() == "F" and $this->fields->tipocfa->getNewValue() <> "F" ) {
            $this->fields->id_utente_rif->setStyleProperty( "border", "1px solid red" );
            $error_text = "La causale scelta prevede un fornitore come riferimento.";
        }
        // Verifica corrispondenza tipo riferimento: UTENTE
        elseif ( $this->fields->tipo_rif->getNewValue() == "C" and $this->fields->tipocfa->getNewValue() <> "C" ) {
            $this->fields->id_utente_rif->setStyleProperty( "border", "1px solid red" );
            $error_text = "La causale scelta prevede un utente come riferimento.";
        }
*/        
        else {
            // Verifica campi obbligatori
            foreach ( $this->mf as $mf ) {
                $value = $this->fields->$mf->getNewValue();
                if ( trim($value) === "" ) {
                    $this->fields->$mf->setStyleProperty( "border", "1px solid red" );
                    $error_text = "Compilare i campi obbligatori";
                }
            }
        }

        if ( $error_text == "" ) {
            $this->fields->data_agg->setNewValue( date ("Y-m-d H:i:s") );  // Questo dovrebbe essere automatico a cura del db, invece non funziona...
  
            parent::saveRow();      

            $this->tab_mov_cassa->syncPageWithSource();
            $this->mostra_saldo();
        }
        else {
            $this->msg_info->setIcon( "warning" );
            $this->msg_info->setValue( $error_text );
        }
    }


    // -------------------------------------------------------------------------
    function deleteRow()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();

        $error_text = "";

        // Per i normali utenti non è possibile eliminare un movimento già validato
        if ( !$this->utente_abilitato() and $this->fields->validato->getNewValue() ) {
            $error_text = "Non e' possibile eliminare un movimento gia' validato.";
        }

        if ( $error_text == "" ) {
            parent::deleteRow();    
    
            $this->mostra_saldo();
        }
        else {
            $this->msg_info->setIcon( "warning" );
            $this->msg_info->setValue( $error_text );
        }

    }


    // -------------------------------------------------------------------------
    function bu_filtroClick()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();

        $this->ds_mov_cassa_where_periodo = "1 = 1";

        // Intervallo date
        if ( $this->fld_dalla_data->getNewValue() != "" )
            $this->ds_mov_cassa_where_periodo .= " AND _cassa.data_mov >= '" . e3g_format_data_to_mysql($this->fld_dalla_data->getNewValue()) . "'";
        if ( $this->fld_alla_data->getNewValue() != "" )
            $this->ds_mov_cassa_where_periodo .= " AND _cassa.data_mov <= '" . e3g_format_data_to_mysql($this->fld_alla_data->getNewValue()) . "'";
             
        $this->ds_mov_cassa_where_altro = "_cassa.prefix = '" . $p4a->e3g_prefix . "'";

        // Tutti i movimenti / Solo quelli da validare
        if ( $this->fld_ck_solo_da_validare->getNewValue() != 0 )
            $this->ds_mov_cassa_where_altro .= " AND _cassa.validato = 0";

        // Causale movimenti
        if ( $this->fld_causale_mov_cassa->getNewValue() != 0 )
            $this->ds_mov_cassa_where_altro .= " AND _cassa.id_causale_mov_cassa = " . $this->fld_causale_mov_cassa->getNewValue();

        // Utente (o fornitore) di riferimento
        if ( $this->fld_utente_rif->getNewValue() != 0 )
            $this->ds_mov_cassa_where_altro .= " AND _cassa.id_utente_rif = " . $this->fld_utente_rif->getNewValue();


        $this->ds_mov_cassa->setWhere( $this->ds_mov_cassa_where_periodo . " AND " . $this->ds_mov_cassa_where_altro );

        if ( $this->data->getNumRows() == 0 ) 
            $this->msg_info->setValue( "Nessun movimento trovato." );
        $this->data->firstRow();
        $this->tab_mov_cassa->syncPageWithSource();
        
        $this->mostra_saldo();
    }


    // -------------------------------------------------------------------------
    function bu_annulla_filtroClick()
    // -------------------------------------------------------------------------
    {
        $this->fld_dalla_data->setNewValue( "" );
        $this->fld_alla_data->setNewValue( "" );
        $this->fld_ck_solo_da_validare->setNewValue( 0 );
        if ( $this->fld_utente_rif->isEnabled() )
            $this->fld_utente_rif->setNewValue( 0 );
        $this->fld_causale_mov_cassa->setNewValue( 0 );

        $this->bu_filtroClick();
    }


    // ($obj è l'oggetto che ha scatenato l'evento)
    // -------------------------------------------------------------------------
    function tab_mov_cassa_BeforeDisplay( $obj, $rows ) 
    // -------------------------------------------------------------------------
    {  
        // Campi visualizzati: array( "data_mov", "importo", "causale_mov_cassa", "tipocfa", "utente_rif", "validato" ) 
        for( $i=0; $i<count($rows); $i++ ) {
            $rows[$i]["validato"] = ( $rows[$i]["validato"] == 1 ? "Si" : "NO" );
            if ( $rows[$i]["tipocfa"] == "C" )
                $rows[$i]["tipocfa"] = "[Utente]";
            elseif ( $rows[$i]["tipocfa"] == "F" )
                $rows[$i]["tipocfa"] = "[Fornitore]";
            else
                $rows[$i]["tipocfa"] = "-";
        }  
        return $rows;  
    }  


    // -------------------------------------------------------------------------
    function tab_riassunto_ute_BeforeDisplay( $obj, $rows ) 
    // -------------------------------------------------------------------------
    {  
        // Campi visualizzati: array( "id_utente_rif", "desc_utente", "email", "n_movimenti", "saldo" ) 
        for( $i=0; $i<count($rows); $i++ ) {
            // Cambia la descrizione dei movimenti non riferiti ad un singolo utente
            if ( $rows[$i]["id_utente_rif"] == 0 )
                $rows[$i]["desc_utente"] = "Altri movimenti";
            // Evidenzia gli utenti in debito (credito negativo)
            if ( $rows[$i]["saldo"] < 0 )
                $rows[$i]["desc_utente"] = "<span style='color:red;'>" . $rows[$i]["desc_utente"] . "</span>";
        }  
        return $rows;  
    }  


    // -------------------------------------------------------------------------
    function tab_riassunto_for_BeforeDisplay( $obj, $rows ) 
    // -------------------------------------------------------------------------
    {  
        // Campi visualizzati: array( "id_utente_rif", "desc_fornitore", "email", "n_movimenti", "saldo" ) 
        for( $i=0; $i<count($rows); $i++ ) {
            // Cambia la descrizione dei movimenti non riferiti ad un singolo utente
            if ( $rows[$i]["id_utente_rif"] == 0 )
                $rows[$i]["desc_fornitore"] = "Altri movimenti";
            // Evidenzia gli utenti in debito (credito negativo)
            if ( $rows[$i]["saldo"] < 0 )
                $rows[$i]["desc_fornitore"] = "<span style='color:red;'>" . $rows[$i]["desc_fornitore"] . "</span>";
        }  
        return $rows;  
    }  


}

?>