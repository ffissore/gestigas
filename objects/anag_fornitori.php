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


class anag_fornitori extends P4A_Mask
{
    // -------------------------------------------------------------------------
    function anag_fornitori()
    // -------------------------------------------------------------------------
    {
        $this->p4a_mask();
        $this->addCss( E3G_TEMPLATE_DIR . 'css/style.css' );
        $p4a =& p4a::singleton();
        
        $this->setTitle( "Anagrafica Fornitori" );
        $this->setIcon( "users" );  
        
        
        //--------------------------------------------- Sorgente dati principale
        $this->build( "p4a_db_source", "ds_fornitori" );
        $this->ds_fornitori->setFields( array( $p4a->e3g_prefix . "anagrafiche.*" => "*", "rf.codanag" => "cod_ref" ) );

        $this->ds_fornitori->setTable( $p4a->e3g_prefix . "anagrafiche" );
        $this->ds_fornitori->addJoin( $p4a->e3g_prefix . "referenti AS rf", "rf.codfornitore = codice", "LEFT" );
        $this->ds_fornitori->setWhere( "tipocfa = 'F'" );
        $this->ds_fornitori->addOrder( "descrizione" );
        $this->ds_fornitori->setPk( "idanag" );
        $this->ds_fornitori->setPageLimit( $p4a->e3g_utente_db_source_page_limit );
        $this->ds_fornitori->load();
        $this->ds_fornitori->firstRow();

        $this->setSource( $this->ds_fornitori );


        // Fields properties
        $fields =& $this->fields;

        $this->fields->idanag->setType( "decimal" );
        
        // Campi Obbligatori Fields --------------------------------------------
        $this->mf = array("codice", "descrizione", "desc_agg");
        foreach( $this->mf as $mf )
            $fields->$mf->label->setFontWeight("bold");


        //------------------------------------------------------ Altri db source
        if ( E3G_TIPO_GESTIONE == 'G' ) {
            $this->build( "p4a_db_source", "ds_referenti" );
            $this->ds_referenti->setTable( $p4a->e3g_prefix . "anagrafiche" );
            $this->ds_referenti->setPk( "codice" );        
            $this->ds_referenti->load();    
        }
        else {
            $this->build( "p4a_db_source", "ds_mastri" );
            $this->ds_mastri->setTable( $p4a->e3g_prefix . "contmastri" );
            $this->ds_mastri->setPk( "codice" );
            $this->ds_mastri->load();
        
            $this->build( "p4a_db_source", "ds_conti" );
            $this->ds_conti->setTable($p4a->e3g_prefix . "contconti" );
            $this->ds_conti->setPk( "codice" );
            $this->ds_conti->load();
        
            $this->build( "p4a_db_source", "ds_segno" );
            $this->ds_segno->setTable( $p4a->e3g_prefix . "contsegno" );
            $this->ds_segno->setPk( "codice" );
            $this->ds_segno->load();
        }
        

        // ------------------------------------------------------------- Toolbar
        if ( E3G_TIPO_GESTIONE == 'G' )
        {
            switch ($p4a->e3g_utente_tipo) {
                case "A":
                case "AS":
                    $this->build( "p4a_standard_toolbar", "toolbar" );
                    break;
                case "R" :
                    $this->build( "p4a_standard_toolbar", "toolbar" );
                    $this->toolbar->buttons->new->setInvisible();
                    $this->toolbar->buttons->delete->setInvisible();
                    break;
                case "U":
                case "G":
                    $this->build( "p4a_navigation_toolbar", "toolbar" );
                    break;
            }
        }
        else {
            $this->build("p4a_standard_toolbar", "toolbar");  // Equogest 
        }
        $this->toolbar->setMask( $this );


        if ( ( E3G_TIPO_GESTIONE == 'G' and 
               ( $p4a->e3g_utente_tipo == "AS" or $p4a->e3g_utente_tipo == "A" or $p4a->e3g_utente_tipo == "R" ) ) or
             ( E3G_TIPO_GESTIONE == 'E' ) ) {
            // --------------------- Bottone toolbar per esportare righe tabella
            $this->toolbar->addSeparator();
            $this->toolbar->addButton( "bu_esporta_csv", "spreadsheet" );
            // Con il false finale non si dovrebbe vedere la label ma solo il tooltip, ma ciò non accade in p4a 2.2.3 (bug?)
            $this->toolbar->buttons->bu_esporta_csv->setLabel( "Esporta righe come CSV (foglio elettronico)", false );
            $this->toolbar->buttons->bu_esporta_csv->addAction( "onClick" );
            $this->intercept( $this->toolbar->buttons->bu_esporta_csv, "onClick", "bu_esporta_csvClick" );
        }
        
        
        // ------------------------------------------------------- Campi Ricerca
        $txt_search =& $this->build( "p4a_field", "txt_search" );
        $txt_search->setLabel( "Ragione sociale" );
        $txt_search->setWidth( 200 );
        $txt_search->addAction( "onReturnPress" );
        $this->intercept( $txt_search, "onReturnPress", "bu_cercaClick" );

        // Bottone "Cerca"
        $bu_cerca =& $this->build( "p4a_button", "bu_cerca" );
        $bu_cerca->setLabel( "Cerca" );
        $bu_cerca->setIcon( "find" );
        $bu_cerca->setSize( 16 );
        $bu_cerca->setWidth( 150 );
        $bu_cerca->addAction( "onClick" );
        $this->intercept( $bu_cerca, "onClick","bu_cercaClick" );

        // Bottone "Annulla Ricerca"
        $bu_annulla_cerca =& $this->build( "p4a_button", "bu_annulla_cerca" );
        $bu_annulla_cerca->setLabel( "Mostra tutti" );
        $bu_annulla_cerca->setIcon( "cancel" );
        $bu_annulla_cerca->setSize( 16 );
        $bu_annulla_cerca->setWidth( 150 );
        $bu_annulla_cerca->addAction( "onClick" );
        $this->intercept( $this->bu_annulla_cerca, "onClick", "bu_annulla_cerca_click" );

        // Frame
        $fs_search =& $this->build( "p4a_fieldset", "fs_search" );
        $fs_search->setTitle( "Cerca" );
        $fs_search->setWidth( E3G_FIELDSET_SEARCH_WIDTH );
        $fs_search->anchor( $txt_search );
        $fs_search->anchorLeft( $bu_cerca );
        $fs_search->anchorLeft( $bu_annulla_cerca );


        // ---------------------------------------------------- Tabella centrale
        $this->build( "p4a_table", "tab_fornitori" );
        $this->tab_fornitori->setWidth( E3G_TABLE_WIDTH );
        $this->tab_fornitori->setSource( $this->ds_fornitori );
        if ( E3G_TIPO_GESTIONE == 'G' )
            $this->tab_fornitori->setVisibleCols( array("codice", "descrizione", "desc_agg", "localita", "cod_ref") );
        else
            $this->tab_fornitori->setVisibleCols( array("codice", "descrizione", "desc_agg", "localita") );
        $this->tab_fornitori->showNavigationBar();
        $this->intercept( $this->tab_fornitori->rows, "afterClick", "tab_fornitori_click" );

        $this->tab_fornitori->cols->descrizione->setLabel( "Ragione sociale" );
        $this->tab_fornitori->cols->desc_agg->setLabel( "Desc. agg." );

        if ( E3G_TIPO_GESTIONE == 'G' ) {  // Colonna col referente
            $this->tab_fornitori->cols->cod_ref->setLabel("Referente");
            $this->tab_fornitori->cols->cod_ref->setType("select");
            $this->tab_fornitori->cols->cod_ref->setSource($this->ds_referenti);
            $this->tab_fornitori->cols->cod_ref->setSourceValueField("codice");
            $this->tab_fornitori->cols->cod_ref->setSourceDescriptionField("descrizione");
        }


        // ------------------------------------------------------------- Message
        $message =& $this->build("p4a_message", "message");
        $message->setWidth(400);


        //------------------------------------------------------ Dati anagrafica
        $this->fields->codice->disable();

        $this->fields->descrizione->setLabel( "Ragione soc." );
        $this->fields->desc_agg->setLabel( "Desc. agg." );
        $this->fields->cap->setLabel ("CAP" );
        $this->fields->email->setLabel( "Indirizzo e-mail" );
        $this->fields->www->setLabel( "Sito web" );
        
        $this->fields->desc_agg->setTooltip( "Descrizione aggiuntiva breve per la tipologia di produzione, per esempio: \"Farine e cereali\" o \"Detergenti e cosmetici\"" );

        $this->fields->cf->setLabel( "Codice Fiscale" );
        $this->fields->piva->setLabel( "Partita IVA" );
        
        $this->fields->descrizione->setWidth( 250 );
        $this->fields->indirizzo->setWidth( 250 );
        $this->fields->email->setWidth( 250 );
        $this->fields->www->setWidth( 250 );
        
        //Fieldset con l'elenco dei campi
        $fs_anagrafica =& $this->build("p4a_fieldset", "fs_anagrafica");
        $fs_anagrafica->setTitle("Anagrafica");
        $fs_anagrafica->setWidth( E3G_FIELDSET_DATI_WIDTH );
        $fs_anagrafica->anchor($this->fields->codice);
        $fs_anagrafica->anchor($this->fields->descrizione);
        $fs_anagrafica->anchorLeft($this->fields->desc_agg);
        $fs_anagrafica->anchor($this->fields->indirizzo);
        $fs_anagrafica->anchor($this->fields->cap);
        $fs_anagrafica->anchorLeft($this->fields->localita);
        $fs_anagrafica->anchorLeft($this->fields->provincia);
        $fs_anagrafica->anchor($this->fields->telefono);
        $fs_anagrafica->anchorLeft($this->fields->fax);
        $fs_anagrafica->anchor($this->fields->email);
        $fs_anagrafica->anchor($this->fields->www);
        $fs_anagrafica->anchor($this->fields->cf);
        $fs_anagrafica->anchorLeft($this->fields->piva);
    

        // ------------------------------------------- Dati contabili (Equogest)
        if ( E3G_TIPO_GESTIONE == 'E' ) {
            $this->fields->conto->setLabel("conto");
            $this->fields->conto->setType("select");
            $this->fields->conto->setSource($this->ds_conti);
            $this->fields->conto->setSourceValueField("codice");
            $this->fields->conto->setSourceDescriptionField("descrizione");
            
            $this->fields->mastro->setLabel("mastro");
            $this->fields->mastro->setType("select");
            $this->fields->mastro->setSource($this->ds_mastri);
            $this->fields->mastro->setSourceValueField("codice");
            $this->fields->mastro->setSourceDescriptionField("descrizione");
            $this->fields->mastro->addAction("OnChange");
            $this->intercept($this->fields->mastro, "onChange","mastro_click");
    
            $this->fields->segnocontabile->setType("select");
            $this->fields->segnocontabile->setSource($this->ds_segno);
            $this->fields->segnocontabile->setSourceValueField("codice");
            $this->fields->segnocontabile->setSourceDescriptionField("descrizione");
    
            //Fieldset con l'elenco dei campi
            $fs_contabili=& $this->build("p4a_fieldset", "fs_contabili");
            $fs_contabili->setTitle("Dati Contabili");
            $fs_contabili->setWidth( E3G_FIELDSET_DATI_WIDTH );
            $fs_contabili->anchor($this->fields->mastro);
            $fs_contabili->anchor($this->fields->conto);
            $fs_contabili->anchor($this->fields->segnocontabile);
        }


        // ---------------------------------------------------------- Altri dati
        $this->fields->note->setType( "textarea" );
        $this->fields->note->setWidth( 435 );
        $this->fields->note->setHeight( 150 );

        $fs_altri_dati =& $this->build( "p4a_fieldset", "fs_altri_dati" );
        $fs_altri_dati->setWidth( E3G_FIELDSET_DATI_WIDTH );
        $fs_altri_dati->setTitle( "Altri dati" );
        $fs_altri_dati->anchor( $this->fields->note, "127px" );
    

        // ---------------------------------------------------------------- Date
        $this->fields->data_ins->setLabel( "Inserimento" );
        $this->fields->data_agg->setLabel( "Ultima modifica" );

        //Fieldset con le date ins e agg
        $this->build( "p4a_fieldset", "fs_date" );
        $this->fs_date->setTitle( "Date" );
        $this->fs_date->setWidth( E3G_FIELDSET_DATI_WIDTH );
        $this->fs_date->anchor( $this->fields->data_ins );
        $this->fs_date->anchorLeft( $this->fields->data_agg );


        // Abilitazione campi in base al tipo utente (GestiGAS)
        $this->abilitazione_campi();
        
        
        // ---------------------------------------------------- Frame principale
        $frm=& $this->build( "p4a_frame", "frm" );
        $frm->setWidth( E3G_MAIN_FRAME_WIDTH );

        $frm->anchor( $fs_search );
        $frm->anchor( $this->tab_fornitori );
        $frm->anchor( $message );
        $frm->anchor( $fs_anagrafica );
        $frm->anchor( $fs_altri_dati );
        if ( E3G_TIPO_GESTIONE == 'E' ) 
            $frm->anchor( $fs_contabili );
        $frm->anchor( $this->fs_date );
        
        e3g_scrivi_footer( $this, $frm );

        // Display
        $this->display( "main", $frm );
        $this->display( "menu", $p4a->menu );
        $this->display( "top", $this->toolbar );
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
            "SELECT COUNT(*) FROM " . $p4a->e3g_prefix . "anagrafiche " .
            " WHERE tipocfa = 'F' and stato = 1" );
        if ( $n == 0 ) {
            $this->newRow();
            $this->fields->codice->enable();
        }
            
        parent::main();

        foreach ( $this->mf as $mf )
            $this->fields->$mf->unsetStyleProperty("border");
    }
    
    // -------------------------------------------------------------------------
    function newRow()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();
        
        parent::newRow();
        
        // Campi obbligatori
        $this->fields->tipocfa->setNewValue( "F" );
        $this->fields->stato->setNewValue( 1 ); 
        $this->fields->data_ins->setNewValue( date ("Y-m-d H:i:s") );
        $this->fields->n_login->setNewValue( 0 );
        $this->fields->mailing_list->setNewValue( 0 );
        $this->fields->db_source_page_limit->setNewValue( 10 );
        $this->fields->modifica_ingredienti->setNewValue( 0 );
        $this->fields->filtro_ingredienti->setNewValue( 0 );
        $this->fields->cassiere->setNewValue( 0 );

        // Propone un codice del tipo F0000 (il controllo di unicità è nel saveRow)
        //$max_cod = $db->queryOne(
        //  "SELECT COUNT( * ) FROM " . $p4a->e3g_prefix . "anagrafiche WHERE tipocfa = 'F'" );
        $max_cod = $db->queryOne("SELECT MAX(idanag) FROM " . $p4a->e3g_prefix . "anagrafiche" );
        $this->fields->codice->setNewValue( 'F' . sprintf( "%04d", ++$max_cod ) );
        $this->fields->codice->enable();
                
        if ( E3G_TIPO_GESTIONE == 'E' )
            $this->fields->segnocontabile->setNewValue( "D" );
    }


    // -------------------------------------------------------------------------
    function deleteRow()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();

        $articoliassociati = $db->queryOne(
            "SELECT COUNT( * ) FROM " . $p4a->e3g_prefix . "articoli " .
            " WHERE centrale = '" . $this->fields->codice->getNewValue() . "'" );
        
        if ( is_numeric($articoliassociati) and ( $articoliassociati > 0 ) )
        {
            $this->message->setValue("Impossibile cancellare questo fornitore perche' ha degli articoli associati.");
        }
        else {
            // Elimina i record di associazione referenti/fornitori
            $db->query(
                "DELETE FROM " . $p4a->e3g_prefix . "referenti " .
                " WHERE codfornitore = '" . $this->fields->codice->getNewValue() . "'" );
            
            parent::deleteRow();    
        }
    }

    
    // -------------------------------------------------------------------------
    function saveRow()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();
        
        $this->fields->email->setNewValue( trim( strtolower( $this->fields->email->getNewValue() ) ) );
        $this->fields->www->setNewValue( trim( strtolower( $this->fields->www->getNewValue() ) ) );

        $error_text = "";

        // Controlla se il referente sta salvando un suo fornitore o no
        if ( E3G_TIPO_GESTIONE == 'G' && $p4a->e3g_utente_tipo == "R" ) {
            $OKref = $db->queryOne( "SELECT COUNT( * ) FROM " . $p4a->e3g_prefix . "referenti " .
                " WHERE codanag = '$p4a->e3g_utente_codice' AND codfornitore = '" . $this->fields->codice->getNewValue() . "'" );
            
            if ( $OKref == 0 )
                $error_text = "Modifiche non accettate: non risulti essere referente del fornitore '" .
                //TODO Controllare perchè la seguente riga non contiene la desc. del fornitore, ma un oggetto vuoto
                    $this->fields->descrizione->getNewValue() . "'.";
        }

        // Controllo indirizzo e-mail non valido
        if ( $error_text == "" ) {
            if ( $this->fields->email->getNewValue() <> "" and !e3g_email_valido( $this->fields->email->getNewValue() ) ) {
                $error_text = "Scrivere un indirizzo e-mail valido.";
                $this->fields->email->setStyleProperty("border", "1px solid red");
            }
        }
        
        // Assegna la chiave primaria           
        if ( $error_text == "" ) 
        {
            if ( !is_numeric($this->fields->idanag->getNewValue()) )
            {
                $maxid = $db->queryOne( "SELECT MAX( idanag ) FROM " . $p4a->e3g_prefix . "anagrafiche" );  
                if ( is_numeric($maxid) )
                    $maxid++;
                else 
                    $maxid = 1;
                $this->fields->idanag->setNewValue( $maxid );   
            }
        }

        
        // Verifica campo codice non duplicato 
        if ( $error_text == "" ) {
            if ( $this->fields->codice->getNewValue() != "" ) {
                $n = $db->queryOne(
                    "SELECT COUNT(*) FROM " . $p4a->e3g_prefix . "anagrafiche " .
                    " WHERE codice = '" . $this->fields->codice->getNewValue() . "' " .
                    "   AND idanag <> " . $this->fields->idanag->getNewValue() );
                if ( $n > 0 )
                    $error_text = "Codice '" . $this->fields->codice->getNewValue() . "' gia' presente.";
            }
        }
            
        // Verifica campi obbligatori
        if ( $error_text == "" ) {
            foreach ( $this->mf as $mf ) {
                $value = $this->fields->$mf->getNewValue();
                if ( trim($value) === "" ) {
                    $this->fields->$mf->setStyleProperty("border", "1px solid red");
                    $error_text = "Compilare i campi obbligatori";
                }
            }
        }
        
    
        if ( $error_text == "" ) {
            parent::saveRow();

            $this->tab_fornitori->syncPageWithSource();
        }
        else
            $this->message->setValue( $error_text );
    }


    // -------------------------------------------------------------------------
    function mastro_click()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();

        $this->ds_conti->setTable( $p4a->e3g_prefix . "contconti" );
        $this->ds_conti->setWhere( "mastro = '" . $this->fields->mastro->getNewValue() . "'" );     
        $this->ds_conti->load();
        $this->ds_conti->firstRow();
    }


    // -------------------------------------------------------------------------
    function bu_cercaClick()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();

        $this->ds_fornitori->setWhere( "UCASE(descrizione) LIKE '%" . addslashes( strtoupper(trim($this->txt_search->getNewValue())) ) . "%' AND tipocfa = 'F'" );
        $this->ds_fornitori->load();
        $this->ds_fornitori->firstRow();

        if ( !$this->data->getNumRows() ) {
            $this->message->setValue( "Nessun fornitore trovato." );
            $this->ds_fornitori->setWhere( "tipocfa = 'F'" );
            $this->ds_fornitori->load();
            $this->ds_fornitori->firstRow();
        }
    }


    // -------------------------------------------------------------------------
    function bu_annulla_cerca_click()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        
        $this->txt_search->setNewValue( "" );

        $this->ds_fornitori->setWhere( "tipocfa = 'F'" );
        $this->ds_fornitori->load();
        $this->ds_fornitori->firstRow();
    }

    
    // -------------------------------------------------------------------------
    function nextRow()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();

        parent::nextRow();
    
        $this->abilitazione_campi();
    }

    
    // -------------------------------------------------------------------------
    function prevRow()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();

        parent::prevRow();
        
        $this->abilitazione_campi();
    }


    // -------------------------------------------------------------------------
    function firstRow()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        
        parent::firstRow();
        
        $this->abilitazione_campi();
    }


    // -------------------------------------------------------------------------
    function lastRow()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        
        parent::lastRow();
        
        $this->abilitazione_campi();
    }
    
    
    // -------------------------------------------------------------------------
    function tab_fornitori_click()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();

        $this->abilitazione_campi();
    }

    
    // -------------------------------------------------------------------------
    function abilitazione_campi()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();

        if ( E3G_TIPO_GESTIONE == 'G' )
            switch ($p4a->e3g_utente_tipo) {
                case "A":
                case "AS":
                    $this->abilita_campi();                    
                    break;

                case "R":
                    $pos = strpos( $p4a->e3g_where_referente, "'" . $this->fields->codice->getNewValue() . "'" );
                    if ($pos === false) 
                        $this->disabilita_campi();                    
                    else 
                        $this->abilita_campi();                    
                    break;
                    
                default:
                    $this->disabilita_campi();                    
                    break;
            }

        $this->fields->data_ins->disable();
        $this->fields->data_agg->disable();
    }


    // -------------------------------------------------------------------------
    function abilita_campi()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();

        // L'utente può solo leggere
        while ( $field =& $this->fields->nextItem() ) {
            $field->enable();
        }   
        $this->fields->codice->disable();
    } 

                        
    // -------------------------------------------------------------------------
    function disabilita_campi()
    // -------------------------------------------------------------------------
    {
      $p4a =& p4a::singleton();

        // L'utente può solo leggere
        while ( $field =& $this->fields->nextItem() ) {
            $field->disable();
        }
    } 


    // Esportazione righe tabella come CSV
    // -------------------------------------------------------------------------
    function bu_esporta_csvClick() 
    // -------------------------------------------------------------------------
    {  
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();

        if ( E3G_TIPO_GESTIONE == 'G' ) {
            // MM_2009-01-26 Attenzione: causa probabile bug di p4a 2.2.3, non è possibile 
            // esportare le colonne in un ordine diverso da come sono presenti in tabella
            $colonne = array (
                "codice"        => "Codice",
                "descrizione"   => "Ragione sociale",
                "indirizzo"     => "Indirizzo",
                "localita"      => "Localita'",
                "cap"           => "CAP",
                "provincia"     => "Provincia",
                "telefono"      => "Telefono",
                "email"         => "e-mail",
                "www"           => "Sito web",
                "cf"            => "Codice fiscale",
                "piva"          => "Partita IVA",
                "data_ins"      => "Data inserimento",
                "data_agg"      => "Data ultima modifica"
            );
        }
        else {
            $colonne = array (
                "codice"        => "Codice",
                "descrizione"   => "Ragione sociale",
                "indirizzo"     => "Indirizzo",
                "localita"      => "Localita'",
                "cap"           => "CAP",
                "provincia"     => "Provincia",
                "telefono"      => "Telefono",
                "email"         => "e-mail",
                "www"           => "Sito web",
                "cf"            => "Codice fiscale",
                "piva"          => "Partita IVA",
                "data_ins"      => "Data inserimento",
                "data_agg"      => "Data ultima modifica"
            );
        }

        e3g_db_source_exportToCsv( $this->ds_fornitori, $colonne, "Fornitori " . $p4a->e3g_azienda_rag_soc );
    }
}

?>