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


require_once( dirname(__FILE__) . '/../libraries/e3g_doc_routines.php' );


class consegna_utente extends P4A_Mask
{
    var $codtipodoc ='';
    var $numdoc = 0;
    var $date = '';
    var $codclifor = '';
    var $iddoc = 0;
    var $nuovariga = 0;
    var $consegna_aperta = 0;
    var $where_cod_fornitore = ''; 

    
    // -------------------------------------------------------------------------
    function consegna_utente()
    // -------------------------------------------------------------------------
    {
        $this->p4a_mask();
        $this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
        $p4a =& p4a::singleton();
        
        $this->SetTitle( "Consegna articoli ad Utente" );


        // Toolbar
        $this->build("p4a_quit_toolbar", "toolbar");


        // Message
        $message =& $this->build("p4a_message", "message");
        $message->setWidth("300");

        
        // Campi invisibili per stampa
        $this->build("p4a_field", "fldcodtipo");
        $this->fldcodtipo->setNewValue("");

        $this->build("p4a_field", "fldfornitore");
        $this->fldfornitore->setNewValue("");
        
        $this->build("p4a_field", "fldiddoc");
        $this->fldiddoc->setNewValue("");

        $this->build("p4a_field", "fldnumdocum");
        $this->fldnumdocum->setNewValue("");


        // -------------------------------------------------------- Combo utenti
        $this->build( "p4a_db_source", "ds_utenti" );
        $this->ds_utenti->setSelect( "anag.idanag, anag.codice, " .
            " CONCAT( anag.descrizione, ' (', SUM( d.quantita ), ' pezzi; ', " .
            "         FORMAT( SUM( d.totale ), $p4a->e3g_azienda_n_decimali_prezzi ), ' euro)' ) AS descrizione " );
        $this->ds_utenti->setTable( $p4a->e3g_prefix . "anagrafiche AS anag" );
        $this->ds_utenti->addJoin( $p4a->e3g_prefix . "docr d", 
            "d.codutente = anag.codice AND " .
            "d.visibile = 'N' AND d.codtipodoc = '" . $p4a->e3g_azienda_gg_cod_doc_ordine . "' AND " .
            "( d.estratto<>'S' OR ISNULL(d.estratto) ) " );
        $this->ds_utenti->addJoin( $p4a->e3g_prefix . "articoli AS art", "d.codice = art.codice" );
        $this->ds_utenti->setWhere( "anag.tipocfa = 'C' AND anag.tipoutente <> 'A' AND anag.stato = 1 " .
            ( $p4a->e3g_utente_tipo == 'R' ? "AND " . str_replace("#CAMPOCODICE#", "art.centrale", $p4a->e3g_where_referente) : "" ) );
        $this->ds_utenti->addGroup( "anag.idanag, anag.codice, anag.descrizione" );
        $this->ds_utenti->addOrder( "anag.descrizione" );
        $this->ds_utenti->setPk( "anag.idanag" );
        $this->ds_utenti->load();
        $this->ds_utenti->firstRow();

        $this->codclifor = $this->ds_utenti->fields->codice->getNewValue();

        $this->build( "p4a_field", "fld_utente" );
        $this->fld_utente->setLabel( "Utente" );
        $this->fld_utente->setType( "select" );
        $this->fld_utente->setSource( $this->ds_utenti );
        $this->fld_utente->setSourceValueField( "codice" );
        $this->fld_utente->setSourceDescriptionField( "descrizione");
        $this->fld_utente->setWidth( 350 );
        $this->fld_utente->label->setWidth( 60 );
        $this->fld_utente->addAction( "onChange" );
        $this->intercept( $this->fld_utente, "onChange","fld_utente_change" );

        // mi posiziono sul primo record (non avviene in automatico)        
        $this->fld_utente->setNewValue( $this->ds_utenti->fields->codice->getNewValue() );


        // ---------------------------------------------- Filtro combo Fornitori
        $this->build( "p4a_db_source", "ds_fornitori" );
        $this->ds_fornitori->setTable( $p4a->e3g_prefix."anagrafiche" );
        $this->ds_fornitori->setWhere( "( tipocfa = 'F' AND stato = 1 " . 
            ( $p4a->e3g_utente_tipo == 'R' ? "AND " . str_replace("#CAMPOCODICE#", "codice", $p4a->e3g_where_referente) : "" ) . " ) OR ( idanag = 0 )" );
        $this->ds_fornitori->addOrder( "descrizione" );
        $this->ds_fornitori->setPk( "codice" );
        $this->ds_fornitori->load();
        $this->ds_fornitori->firstRow();

        $this->build( "p4a_field", "fld_fornitore" );
        $this->fld_fornitore->setLabel( "Fornitore" );
        $this->fld_fornitore->setType( "select" );
        $this->fld_fornitore->setSource( $this->ds_fornitori );
        $this->fld_fornitore->setSourceValueField( "codice" );
        $this->fld_fornitore->setSourceDescriptionField( "descrizione" );
        $this->fld_fornitore->setWidth( 250 );
        $this->fld_fornitore->label->setWidth( 60 );
        $this->fld_fornitore->addAction( "onChange" );
        $this->intercept( $this->fld_fornitore, "onChange","fld_fornitore_change" );

        $this->fld_fornitore->setNewValue( "00" );


        // Bottone "Apri/Chiudi consegna"
        $this->build("p4a_button", "cmd_apri_chiudi_consegna");
        $this->cmd_apri_chiudi_consegna->setLabel("Apri consegna...");
        $this->cmd_apri_chiudi_consegna->setIcon("new");
        $this->cmd_apri_chiudi_consegna->addAction("onClick");
        $this->intercept($this->cmd_apri_chiudi_consegna, "onClick", "cmd_apri_chiudi_consegna_click");
        $this->cmd_apri_chiudi_consegna->requireConfirmation( "onClick", "Confermi la creazione di un documento per la consegna ?" );
        $this->cmd_apri_chiudi_consegna->setWidth(230);

        
        // Bottone "Esporta Griglia Utenti / Fornitori" 
        $this->build("p4a_button", "cmd_exp_griglia_csv");
        $this->cmd_exp_griglia_csv->setLabel("Esporta griglia Utenti/Fornitori");
        $this->cmd_exp_griglia_csv->setIcon("spreadsheet");  
        $this->cmd_exp_griglia_csv->addAction("onClick");
        $this->intercept($this->cmd_exp_griglia_csv, "onClick", "cmd_exp_griglia_csv_click");
        $this->cmd_exp_griglia_csv->setWidth(230);


        // Bottone notifica consegna (inizialmente invisibile) (FM 09/01/2008)
        $this->build("p4a_button", "cmd_notifica_consegna");
        $this->cmd_notifica_consegna->setLabel("Notifica disponibilita' articoli...");
        $this->cmd_notifica_consegna->setIcon("mail_send");
        $this->cmd_notifica_consegna->addAction("onClick");
        $this->intercept($this->cmd_notifica_consegna, "onClick", "cmd_notifica_consegna_click");
        $this->cmd_notifica_consegna->requireConfirmation( "onClick", "Confermi l'invio di una mail di notifica della consegna all'utente selezionato?" );
        $this->cmd_notifica_consegna->setWidth(230);
        $this->cmd_notifica_consegna->setInvisible();


        // Bottone "Consegna tutti gli articoli" (inizialmente invisibile)
        $this->build("p4a_button", "cmd_consegna_art_tutti");
        $this->cmd_consegna_art_tutti->setLabel("Consegna tutti gli articoli...");
        $this->cmd_consegna_art_tutti->setIcon("execute");
        $this->cmd_consegna_art_tutti->addAction("onClick");
        $this->intercept($this->cmd_consegna_art_tutti, "onClick", "cmd_consegna_art_tutti_click");
        $this->cmd_consegna_art_tutti->requireConfirmation( "onClick", "Confermi la consegna di tutti gli articoli in elenco senza modifiche delle quantita'?" );
        $this->cmd_consegna_art_tutti->setWidth(230);
        $this->cmd_consegna_art_tutti->setInvisible();


        //------------------------- Data source principale: articoli in consegna
        $this->build("p4a_db_source", "ds_articoli");
        $this->ds_articoli->setSelect( "idriga, " . 
            $p4a->e3g_prefix . "articoli.centrale AS centrale," . 
            $p4a->e3g_prefix . "docr.codice, " .
            $p4a->e3g_prefix . "docr.descrizione, " .
            "CONCAT_WS( ' ', " . $p4a->e3g_prefix . "articoli.um_qta, " . $p4a->e3g_prefix . "articoli.um ) AS um_qta_um, " .  // CONCAT_WS non è vuoto se manca l'UM 
            $p4a->e3g_prefix . "articoli.um_qta," .
            "FORMAT( " . $p4a->e3g_prefix . "docr.prezzo + ".$p4a->e3g_prefix."docr.delta_prezzo, " . $p4a->e3g_azienda_n_decimali_prezzi . " ) AS prezzo, " .
            "quantita, quantita2, (quantita-quantita2) AS qta_originale, " .
            "FORMAT( (" . $p4a->e3g_prefix . "docr.prezzo + ".$p4a->e3g_prefix."docr.delta_prezzo)*quantita, " . $p4a->e3g_azienda_n_decimali_prezzi . " ) AS importo, " .
            $p4a->e3g_prefix . "docr.codiva AS codiva, " . 
            $p4a->e3g_prefix . "docr.sconto AS sconto, data, iddocr, codutente, dataordine, FORMAT( " . $p4a->e3g_prefix . "docr.delta_prezzo, " . $p4a->e3g_azienda_n_decimali_prezzi . " ) AS delta_prezzo, FORMAT( " . $p4a->e3g_prefix . "docr.prezzo, " . $p4a->e3g_azienda_n_decimali_prezzi . " ) AS prezzo_originale" );
            
        $this->ds_articoli->setTable( $p4a->e3g_prefix."docr" );
        $this->ds_articoli->addJoin( $p4a->e3g_prefix . "articoli", $p4a->e3g_prefix . "docr.codice = " . $p4a->e3g_prefix . "articoli.codice " .
            ( $p4a->e3g_utente_tipo == 'R' ? "AND " . str_replace("#CAMPOCODICE#", $p4a->e3g_prefix . "articoli.centrale", $p4a->e3g_where_referente) : "" ) );
        $this->ds_articoli->setWhere(
            "visibile = 'N' AND codtipodoc = '" . $p4a->e3g_azienda_gg_cod_doc_ordine . "' AND " .
            "( estratto<>'S' OR ISNULL(estratto) ) AND " .
            "codutente = '" . $this->fld_utente->getNewValue() . "'" . $this->where_cod_fornitore );
        $this->ds_articoli->addOrder( "data" );

        $this->ds_articoli->setPk( "idriga" );
        $this->ds_articoli->setPageLimit( $p4a->e3g_utente_db_source_page_limit );
        $this->ds_articoli->load();
        $this->ds_articoli->firstRow();
        
        $this->setSource( $this->ds_articoli );
        

        //-------------------------------- Griglia centrale articoli in consegna        
        $this->build("p4a_table", "tab_articoli");
        $this->tab_articoli->setWidth( E3G_TABLE_WIDTH );
        $this->tab_articoli->setTitle( "Elenco articoli in ordine" );
        $this->tab_articoli->setSource( $this->ds_articoli );
        $this->tab_articoli->setVisibleCols( array( "centrale", "codice", "descrizione", "um_qta_um", "um_qta", "qta_originale", "quantita", "prezzo", "importo") );
        $this->tab_articoli->showNavigationBar();
        $this->intercept( $this->tab_articoli->rows, "afterClick", "tab_articoli_afterClick" );       
        $this->intercept( $this->tab_articoli->rows, "beforeDisplay", "tab_articoli_beforeDisplay" );  

        $this->tab_articoli->cols->um_qta->setVisible( false );

        $this->tab_articoli->cols->centrale->setLabel( "Fornitore" );
        $this->tab_articoli->cols->codice->setLabel( "Codice" );
        $this->tab_articoli->cols->descrizione->setLabel( "Articolo" );
        $this->tab_articoli->cols->um_qta_um->setLabel( "Conf." );
        $this->tab_articoli->cols->qta_originale->setLabel( "Q.ta' (orig.)" );
        $this->tab_articoli->cols->quantita->setLabel( "Q.ta'" );
        $this->tab_articoli->cols->prezzo->setLabel( "Prezzo" );
        $this->tab_articoli->cols->importo->setLabel( "Importo" );

        $this->tab_articoli->cols->centrale->setWidth( 160 );
        $this->tab_articoli->cols->codice->setWidth( 75 );
//      $this->tab_articoli->cols->descrizione->setWidth();  Per differenza
        $this->tab_articoli->cols->um_qta_um->setWidth( 50 );
        $this->tab_articoli->cols->qta_originale->setWidth( 50 );
        $this->tab_articoli->cols->quantita->setWidth( 50 );
        $this->tab_articoli->cols->prezzo->setWidth( 50 );
        $this->tab_articoli->cols->importo->setWidth( 60 );

        $this->tab_articoli->data->fields->prezzo->setType("float");  // Per l'allineamento a destra
        $this->tab_articoli->data->fields->importo->setType("float");

        $this->tab_articoli->cols->centrale->setSourceValueField( "codice");
        $this->tab_articoli->cols->centrale->setSourceDescriptionField( "descrizione" );
        $this->tab_articoli->cols->centrale->setSource( $this->ds_fornitori );
                
        $this->tab_articoli->cols->um_qta_um->setOrderable( false );
        $this->tab_articoli->cols->qta_originale->setOrderable( false );
        $this->tab_articoli->cols->prezzo->setOrderable( false );
        $this->tab_articoli->cols->importo->setOrderable( false );


        //******************************************************************
        // Sotto la griglia
        //******************************************************************
            
        $this->fields->quantita->setLabel( "Quantita' ordinata" );
        $this->fields->quantita->setWidth( 40 );
        $this->fields->quantita->label->setWidth( 150 );

        $this->build("p4a_field", "fld_qta_cons");
        $this->fld_qta_cons->setLabel( "Quantita' in consegna" );
        $this->fld_qta_cons->setValue( $this->fields->quantita->getValue() );
        $this->fld_qta_cons->setWidth( 40 );
        $this->fld_qta_cons->label->setWidth( 150 );

        
        $this->build("p4a_field", "fld_chiudi_riga");
        $this->fld_chiudi_riga->setLabel( "Chiudi riga" );
        $this->fld_chiudi_riga->setTooltip( "Considera l'articolo tutto consegnato (e quindi chiude l'ordine) anche se la quantità in consegna è inferiore a quella inizialmente ordinata" );
        $this->fld_chiudi_riga->setType( "checkbox" );
        $this->fld_chiudi_riga->setValue( true );
        $this->fld_chiudi_riga->label->setWidth( 150 );
    

        $this->build("p4a_button", "cmd_consegna_art");
        $this->cmd_consegna_art->setLabel( "Consegna articolo selezionato" );
        $this->cmd_consegna_art->setIcon("execute");
        $this->cmd_consegna_art->setSize( 16 );
        $this->cmd_consegna_art->addAction("onClick");
        $this->intercept($this->cmd_consegna_art, "onClick", "cmd_consegna_art_click");
        $this->cmd_consegna_art->setWidth( 230 );


        // Altri campi non visualizzati
        $fldimponibile =& $this->build("p4a_field", "fldimponibile");
        $fldimposta    =& $this->build("p4a_field", "fldimposta");
        $fldtotale     =& $this->build("p4a_field", "fldtotale");

    
        // ----------------------------------------------------- Fieldset utenti
        $this->build( "p4a_fieldset","fs_utenti" );
        $this->fs_utenti->setTitle( "Utenti con articoli da consegnare" );
        $this->fs_utenti->setWidth( E3G_FIELDSET_SEARCH_WIDTH/2 );
        $this->fs_utenti->anchor( $this->fld_utente );

        // ------------------------------------------- Fieldset filtro fornitore
        $this->build( "p4a_fieldset","fs_fornitori" );
        $this->fs_fornitori->setTitle( "Filtro fornitori" );
        $this->fs_fornitori->setWidth( E3G_FIELDSET_SEARCH_WIDTH/2 - 50 );
        $this->fs_fornitori->anchor( $this->fld_fornitore );
        
        
        // Fascia sotto la griglia
        $this->build( "p4a_sheet", "sh_det" );
        $this->sh_det->defineGrid( 3, 2 );
        $this->sh_det->setInvisible();

        $this->sh_det->anchor( $this->fields->quantita, 1, 1 ); // Qta ordinata 
        $this->sh_det->anchor( $this->fld_qta_cons, 2, 1 );     // Qta in consegna  
        $this->sh_det->anchor( $this->fld_chiudi_riga, 3, 1 );  // Chiudi riga  
        $this->sh_det->anchor( $this->cmd_consegna_art, 3, 2 ); // Bottone "Consegna articolo"


        // ---------------------------------------------------- Frame principale
        $frm=& $this->build("p4a_frame", "frm");
        $frm->setWidth( E3G_MAIN_FRAME_WIDTH );
        
        
        $this->frm->anchor( $this->message );

        $this->frm->anchor( $this->fs_utenti );
        $this->frm->anchorRight( $this->fs_fornitori );

        $this->frm->anchor( $this->cmd_apri_chiudi_consegna );   // Bottone "Apri/Chiudi consegna"
        $this->frm->anchorLeft( $this->cmd_exp_griglia_csv );    // Bottone "Esporta griglia CSV" 
        $this->frm->anchorLeft( $this->cmd_consegna_art_tutti ); // Bottone "Consegni tutti"
        $this->frm->anchorLeft( $this->cmd_notifica_consegna );  // Bottone "Notifica consegna"

        $this->frm->anchor( $this->tab_articoli );
        $this->frm->anchor( $this->sh_det );
        
        e3g_scrivi_footer( $this, $frm );

        // Display
        $this->display("main", $frm);
        $this->display("menu", $p4a->menu);
        $this->display("top", $this->toolbar);

        
        $this->fld_utente_change();
        $this->update_message();
        $this->setFocus( $this->fld_qta_cons );
    }


    // -------------------------------------------------------------------------
    function main()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();

        parent::main();

        $this->update_message();
    }
    


    // -------------------------------------------------------------------------
    function fld_utente_change()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        
        $this->ds_articoli->setWhere(
            "visibile='N' AND codtipodoc ='" . $p4a->e3g_azienda_gg_cod_doc_ordine . "' AND " .
            "( estratto<>'S' OR ISNULL(estratto) ) AND " .
            "codutente = '" . $this->fld_utente->getNewValue() . "'" . $this->where_cod_fornitore );

        if ( $this->ds_articoli->getNumRows() == 0 )
           $this->cmd_apri_chiudi_consegna->disable();
        else 
        {
            $this->cmd_apri_chiudi_consegna->enable();
            $this->ds_articoli->firstRow();
        }
    }


    // -------------------------------------------------------------------------
    function fld_fornitore_change()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();

        if ($this->fld_fornitore->getnewValue() == "00")
            $this->where_cod_fornitore = "";
        else
            $this->where_cod_fornitore = " AND ".$p4a->e3g_prefix."articoli.centrale='".$this->fld_fornitore->getnewValue()."' ";
        
        
        $this->ds_articoli->setWhere(
            "visibile='N' AND codtipodoc ='".$p4a->e3g_azienda_gg_cod_doc_ordine."' AND " .
            "(estratto<>'S' OR ISNULL(estratto)) AND " .
            "codutente='".$this->fld_utente->getNewValue()."' ".$this->where_cod_fornitore);
        $this->ds_articoli->firstRow();
            
        $this->fld_qta_cons->setValue($this->fields->quantita->getValue());     
    }

    
    // -------------------------------------------------------------------------
    function tab_articoli_afterClick()
    // -------------------------------------------------------------------------
    {
        $this->fld_qta_cons->setValue( $this->fields->quantita->getValue() );
    }
    
    
    // -------------------------------------------------------------------------
    function tab_articoli_beforeDisplay( $obj, $rows ) 
    // -------------------------------------------------------------------------
    {  
        for( $i=0; $i<count($rows); $i++ ) {  
            if ( $rows[$i]["um_qta"] == "" )
                $rows[$i]["um_qta_um"] = "";
        }  
        return $rows;  
    }  


    // -------------------------------------------------------------------------
    function cmd_consegna_art_tutti_click()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();
        
        // routine per consegna totale di tutte le righe del documento 
        while( $this->tab_articoli->data->getNumRows() > 0 ) {
            $this->tab_articoli->data->firstRow();
            $this->fld_qta_cons->setValue($this->fields->quantita->getValue());
            $this->cmd_consegna_art_click();
        }
        
        $this->cmd_apri_chiudi_consegna_click();  // Chiusura automatica del documento
        
        $this->ds_utenti->load();
        $this->ds_utenti->firstRow();
        $this->ds_articoli->setWhere(
            "visibile='N' AND codtipodoc ='" . $p4a->e3g_azienda_gg_cod_doc_ordine . "' AND " .
            "( estratto<>'S' OR ISNULL(estratto) ) AND " .
            "codutente = '" . $this->ds_utenti->fields->codice->getNewValue() . "'" . $this->where_cod_fornitore );

        if ( $this->ds_articoli->getNumRows() == 0 ) 
           $this->cmd_apri_chiudi_consegna->disable();
        else {
            $this->cmd_apri_chiudi_consegna->enable();
            $this->ds_articoli->firstRow();
        }
        
        $this->update_message();
    }
    
    
    // -------------------------------------------------------------------------
    function cmd_consegna_art_click()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();
    
        // AP 28.12.09 commentato per consentire la consegna di Qta > della Qta in ordine
        // (caso dei prodotti a peso, ad es. formaggio)
        //if ($this->fld_qta_cons->getUnformattedNewValue() <= $this->fields->quantita->getValue())
        //{
            $idriga = $db->queryOne("SELECT idriga FROM ".$p4a->e3g_prefix."docr ORDER BY idriga DESC");
            if ( is_numeric($idriga) )
                $idriga++;
            else
                $idriga = 1; 
            
            $numriga = $db->queryOne("SELECT nriga FROM ".$p4a->e3g_prefix."docr WHERE iddocr= ".$this->iddoc." ORDER BY idriga DESC");
            if ( is_numeric($numriga) )
                $numriga++;
            else
                $numriga = 1 ; 
                    
        
            $this->calcola_tot_riga();
            
            // AP 28.12.09 rimpiazzo la virgola con il punto per consentire INSERT INTO  
            $qta_cons = str_replace(",", ".", $this->fld_qta_cons->getUnformattedNewValue());
            
            $db->query(
                "INSERT INTO " . $p4a->e3g_prefix . "docr " .
                "       ( idriga, " .
                "         codice, descrizione, " .
                "         iddocr, anno, codtipodoc, " .
                "         data, numdocum, " .
                "         quantita, " .
                "         prezzo, delta_prezzo, " .
                "         codiva, nriga, rifidriga, rifiddoc, " .
                "         codutente, dataordine, " .
                "         imponibile, imposta, totale ) " .
                "VALUES ( $idriga, " .
                "         '" . $this->ds_articoli->fields->codice->getNewValue() . "', '" . str_replace( "'", "''", $this->ds_articoli->fields->descrizione->getNewValue()) . "', " .
                          $this->iddoc . ", '" . $p4a->e3g_azienda_anno_contabile . "', '" . $this->codtipodoc . "', " .
                "         '" . $this->date . "', '" . $this->numdoc . "', " .
                "         $qta_cons, " .
                          $this->ds_articoli->fields->prezzo_originale->getNewValue() . ", " . $this->ds_articoli->fields->delta_prezzo->getNewValue() . ", " .
                "         '" . $this->ds_articoli->fields->codiva->getNewValue() . "', $numriga, " . $this->fields->idriga->getNewValue() . ", " . $this->fields->iddocr->getNewValue() . ", " .
                "         '" . $this->fields->codutente->getNewValue() . "', '" . $this->fields->dataordine->getNewValue()."', " . 
                          $this->fldimponibile->getValue() . ", " . $this->fldimposta->getValue() . ", " . $this->fldtotale->getValue() . " )"
            );
            
            if ($this->fld_qta_cons->getUnformattedNewValue() < $this->fields->quantita->getValue())
            {   
                // estraz. Parziale 
                // da decidere cosa fare della quantita in ordine tenere invariata o aggiornare 07.12.06 AP
                //$db->query("UPDATE ".$p4a->e3g_prefix."docr SET quantita=quantita-".$this->fld_qta_cons->getUnformattedNewValue()." WHERE idriga = ".$this->fields->idriga->getNewValue());
                $db->query( "UPDATE " . $p4a->e3g_prefix . "docr SET quantita2 = " . $qta_cons . " WHERE idriga = " . $this->fields->idriga->getNewValue() );
                if ( $this->fld_chiudi_riga->getNewValue() == true )
                {
                    $db->query( "UPDATE " . $p4a->e3g_prefix . "docr SET estratto = 'S' WHERE idriga = " . $this->fields->idriga->getValue() );                   
                }       
            }
            else 
            {   
                // estratta tutta la qta
                $db->query("UPDATE ".$p4a->e3g_prefix."docr SET estratto='S' WHERE idriga = ".$this->fields->idriga->getValue());
            }
            
            $this->ds_articoli->setWhere("visibile='N' AND codtipodoc ='".$p4a->e3g_azienda_gg_cod_doc_ordine."' AND (estratto<>'S' OR ISNULL(estratto)) AND codutente='".$this->fld_utente->getNewValue()."'".$this->where_cod_fornitore);
            
            
            //$this->ds_articoli->load();
            $this->ds_articoli->firstRow();
            
            //$this->fld_qta_cons->setNewValue($this->fields->quantita->getNewValue());
            $this->fld_qta_cons->setValue($this->fields->quantita->getValue());
                    
            // Aggiorno i totali sulla testata
            $totimponibile = $db->queryOne("SELECT SUM(imponibile) FROM ".$p4a->e3g_prefix."docr WHERE iddocr=".$this->iddoc);
            $totimposta = $db->queryOne("SELECT SUM(imposta) FROM ".$p4a->e3g_prefix."docr WHERE iddocr=".$this->iddoc);
            $totdoc = $db->queryOne("SELECT SUM(totale) FROM ".$p4a->e3g_prefix."docr WHERE iddocr=".$this->iddoc);
    
            $db->query("UPDATE ".$p4a->e3g_prefix."doct SET imponibile=".str_replace(",", ".",$totimponibile).", imposta=".str_replace(",", ".",$totimposta).", totdoc=".str_replace(",", ".",$totdoc)." WHERE iddoc=".$this->iddoc);
            
            
            
            // svuoto il campo note dell'anagrafica che servira' per il prossimo ordine             
            $db->query("UPDATE " . $p4a->e3g_prefix . "anagrafiche SET note_ordine = '' WHERE codice = '" . $this->fld_utente->getNewValue()."'");
        
            
        //}
        //else
        //{ 
        //  $this->message->setValue("Stai cercando di consegnare una quantita' maggiore di quella ordinata.");
        //}
    }


    // -------------------------------------------------------------------------
    function carica_doct()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();

        $this->iddoc      = $p4a->masks->consegna_utente->iddoc;    
        $this->codtipodoc = $p4a->masks->consegna_utente->codtipodoc;
        $this->numdoc     = $p4a->masks->consegna_utente->numdoc;
        $this->codclifor  = $p4a->masks->consegna_utente->codclifor;
        $this->date       = $p4a->masks->consegna_utente->date;
        
        return 0;
    }

    
    // -------------------------------------------------------------------------
    function cmd_apri_chiudi_consegna_click()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();
        
        if ( $this->consegna_aperta == 1 ) {

        	// *********** CHIUSURA: si prepara lo schermo per un'altra apertura

            $this->fld_utente->enable();

            $this->cmd_apri_chiudi_consegna->setLabel("Apri consegna...");
            $this->cmd_apri_chiudi_consegna->setIcon("new");
            $this->cmd_apri_chiudi_consegna->requireConfirmation( "onClick", "Confermi la creazione di un documento per la consegna ?" );

            //$this->cmd_exp_griglia->setVisible();
            $this->cmd_exp_griglia_csv->setVisible();
            $this->cmd_notifica_consegna->setInvisible();  //FM
            $this->cmd_consegna_art_tutti->setInvisible();
            $this->sh_det->setInvisible();

            $this->consegna_aperta = 0;

            // Per aggiornare le righe d'ordine dell'utente appena elaborato        
            $this->fld_utente_change();  

            // quindi NON procedo a generare il documento 
            return 0;
        }
        else {  

            // **************** APERTURA: si prepara lo schermo per una chiusura

            $this->fld_utente->disable();

            $this->cmd_apri_chiudi_consegna->setLabel("Chiudi Consegna...");
            $this->cmd_apri_chiudi_consegna->setIcon("save");
            $this->cmd_apri_chiudi_consegna->requireConfirmation( "onClick", "Confermi la chiusura della presente consegna ?" );

            //$this->cmd_exp_griglia->setInvisible();
            $this->cmd_exp_griglia_csv->setInvisible();
            $this->cmd_notifica_consegna->setVisible();  //FM
            $this->cmd_consegna_art_tutti->setVisible();
            $this->sh_det->setVisible();

            $this->consegna_aperta = 1;
        
            // quindi procedo a generare il documento 
        }
        
        // estraggo le righe con il fornitore indicato
        // genero l'ordine a fornitore 
        // setto le righe Stato = A --> Stato = L
        $cod_utente = $this->fld_utente->getNewValue();
        $note_famiglia = $db->queryOne("SELECT note_ordine FROM " . $p4a->e3g_prefix . "anagrafiche WHERE codice = '" . $cod_utente ."'");
        $numrighe = $db->queryOne("SELECT COUNT(*) FROM ".$p4a->e3g_prefix."docr WHERE visibile='N' AND codtipodoc ='".$p4a->e3g_azienda_gg_cod_doc_ordine."' AND (estratto<>'S' OR ISNULL(estratto)) AND codutente='".$cod_utente."'");
        
        if ( !is_numeric($numrighe) ) {   
            $this->message->setValue( "Non ci sono ordini per questo utente" );
            return 0;
        }
        else {  
            if ($numrighe == 0) {
                $this->message->setValue("Non ci sono ordini per questo utente" );
                return 0;
            }
        }
        

        $codtipodoc = $p4a->e3g_azienda_gg_cod_doc_ordine_fam; 
            
        $datadoc = e3g_format_data_to_mysql( date("d/m/Y") );
        
        
        // DocT
        // iddoc codclifor numdocum codtipodoc anno
        // DocR
        // idriga iddocr anno codtipodoc numdocum

        $registro = $db->queryOne("SELECT codregdoc FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$codtipodoc."'");
        $numdocum = $db->queryOne("SELECT MAX(seriale) FROM ".$p4a->e3g_prefix."docregistri WHERE codice='".$registro."'");
        if ( is_numeric($numdocum) ) 
            $numdocum++;
        else 
            $numdocum = 1;

        $db->query( "UPDATE ".$p4a->e3g_prefix."docregistri SET seriale=".$numdocum." WHERE codice='".$registro."'" );
        
        $iddoc = $db->queryOne( "SELECT MAX( iddoc ) FROM ".$p4a->e3g_prefix."doct" );
        $iddoc++;   

        $this->codtipodoc = $codtipodoc;
        $this->numdoc = $numdocum;
        $this->codclifor = $cod_utente;
        $this->date = $datadoc;
        $this->iddoc = $iddoc;
        
        // Gestione maggiorazione fissa         
        $spesevarie = ( $p4a->e3g_azienda_tipo_gestione_prezzi == 1 ? $p4a->e3g_azienda_prezzi_mag_fissa : 0 );
        
        // Inserisco la testa del documento              
        $db->query( 
            "INSERT INTO ".$p4a->e3g_prefix."doct " .
            "         ( iddoc, codclifor, numdocum, codtipodoc, " .
            "           anno, data, spesevarie, data_ins, idanag, note) " .
            "  VALUES ( " . $iddoc . ", '" . $cod_utente . "', " . $numdocum . ", '" . $codtipodoc . "', " .
            "           '" . $p4a->e3g_azienda_anno_contabile . "', '" . $datadoc. "', ". $spesevarie . ", '".date ("Y-m-d H:i:s")."', " . $p4a->e3g_utente_idanag . ", '" . $note_famiglia . "')");

        // Sposto le note che erano state inserite nella anagrafica utente e le metto nella testa del documento
        $db->query("UPDATE " . $p4a->e3g_prefix . "anagrafiche SET note_ordine = '' WHERE codice ='" . $cod_utente."'");
    }

    
    // -------------------------------------------------------------------------
    function cmd_exp_griglia_click()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();

        tabella_pagamenti_pdf();
    }

    // -------------------------------------------------------------------------
    function cmd_exp_griglia_csv_click()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();

        tabella_pagamenti_csv();
    }
    
    // -------------------------------------------------------------------------
    function calcola_tot_riga()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();

        $iva = str_replace(",", ".",$db->queryOne("SELECT iva FROM ".$p4a->e3g_prefix."aliquoteiva WHERE codice='".$this->fields->codiva->getNewValue()."'"));

        //$quantita = $this->fld_qta_cons->getUnformattedNewValue();
        $quantita = str_replace(",", ".", $this->fld_qta_cons->getUnformattedNewValue());
        
        if ( !is_numeric($quantita) ) $quantita = 0;
        
        $prezzo = $this->fields->prezzo->getValue(); // questo prezzo è generato nella query come somma di prezzo + delta_prezzo
        if ( !is_numeric($prezzo) ) $prezzo = 0;
        
        $sconto = $this->fields->sconto->getValue();
        if ( !is_numeric($sconto) ) $sconto = 0;
        
        $imponibile = (($quantita * $prezzo) * (1 - $sconto / 100) / (100 + $iva)) * 100;
        $imposta    = (($quantita * $prezzo) * (1 - $sconto / 100)) - $imponibile;
        $totriga    = (($quantita * $prezzo) * (1 - $sconto / 100));
        
        $this->fldimponibile->setValue(round($imponibile, 2));
        $this->fldimposta->setValue(round($imposta,2));
        $this->fldtotale->setValue(round($totriga,2));
        
        return 0;
    }


    // -------------------------------------------------------------------------
    function update_message()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();

        if ( $this->ds_utenti->getNumRows() <> 0 ) {
            $this->cmd_apri_chiudi_consegna->enable();
            $this->message->setValue( "" );
        }
        else {
            $this->cmd_apri_chiudi_consegna->disable();
            $this->message->setIcon( "warning" );
            $this->message->setValue( "Nessun articolo da consegnare" );
        }
    }


    // FM 09/01/2008 AGGIUNTA FUNZ
    // -------------------------------------------------------------------------
    function cmd_notifica_consegna_click()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();

        // Dati mittente 
        $query = "SELECT * FROM ".$p4a->e3g_prefix."anagrafiche WHERE idanag='".$p4a->e3g_utente_idanag."'";

        $result = $db->queryRow($query);

        $indmail_mitt = $result[ "email" ];
        $cognome_mitt = $result[ "cognome" ];
        $nome_mitt = $result[ "nome" ];
        $descrizione_mitt = $result[ "descrizione" ];
        $tel1_mitt = $result[ "telefono" ];
        $tel2_mitt = $result[ "telefono2" ];
        $fax_mitt = $result[ "fax" ];

        $this->fld_qta_cons->setValue($indmail_mitt." ".$cognome_mitt." ".$nome_mitt);

        $query = "SELECT * FROM ".$p4a->e3g_prefix."anagrafiche WHERE codice='".$this->fld_utente->getNewValue()."'";

        $result = $db->queryRow($query);

        if ( is_array($result) ) { // Famiglia trovata ---------
            $indmail = $result[ "email" ];
            $cognome = $result[ "cognome" ];
            $nome = $result[ "nome" ];

            // Invio della notifica per email
            $corpo  = "Salve $nome $cognome,\n\n";
            $corpo .= "Degli articoli ordinati al GAS sono disponibili.\n\n";
            $corpo .= "Questa mail ti e' stata inviata dall'applicazione $p4a->e3g_nome_sw per conto ";
            $corpo .= "dell'utente $cognome_mitt $nome_mitt\n";
            $corpo .= "($descrizione_mitt\ntel1: $tel1_mitt\ntel2: $tel2_mitt\nfax: $fax_mitt\nmail: $indmail_mitt).\n\n";

            if ( !e3g_invia_email( "$p4a->e3g_nome_sw: notifica consegna",
                $corpo, $indmail, $nome." ".$cognome ) ) {
                $this->message->setIcon( "error" );
                $this->message->setValue( "Si è verificato un errore durante la spedizione del tuo messaggio." );
                exit;
            } 
            else {
                // Visualizzazione di un messaggio di conferma
                $this->message->setIcon( "info" );
                $this->message->setValue( "Il messaggio e-mail e' inviato con successo." );
            }
        } 
        else {
            $this->message->setIcon( "error" );
            $this->message->setValue("Famiglia non trovata");
        }
    }
    

}


?>