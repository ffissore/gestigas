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


class duplica_listino extends P4A_Mask
{
    
    // -------------------------------------------------------------------------
    function duplica_listino()
    // -------------------------------------------------------------------------
    {
        $this->p4a_mask();
        $this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
        $p4a =& p4a::singleton();
        
        $this->SetTitle( "Multigestione: duplica listino" );

        // ------------------------------------------------------------- Toolbar
        $this->build( "p4a_quit_toolbar", "toolbar" );


        // ------------------------------------------------------------- Message
        $this->build("p4a_message", "message" );
        $this->message->setWidth( 500 );


        // ----------------------------------------------------------- DB source
        // db_source listino di riferimento
        $this->build( "p4a_db_source", "ds_fornitori_rif" );
        $this->ds_fornitori_rif->setSelect( "*, CONCAT_WS( ' / ', descrizione, desc_agg ) AS desc_desc_agg" );
        $this->ds_fornitori_rif->setTable( $p4a->e3g_prefix . "anagrafiche" );
        $this->ds_fornitori_rif->setWhere( "tipocfa = 'F' OR idanag = 0" );        
        $this->ds_fornitori_rif->setPk( "codice" );        
        $this->ds_fornitori_rif->addOrder( "descrizione" );     
        $this->ds_fornitori_rif->load();     
        $this->ds_fornitori_rif->firstRow();
        
        // db_source gestioni altri GAS
        $this->build( "p4a_db_source", "ds_gestioni_gas" );
        $this->ds_gestioni_gas->setTable( "_aziende" );
        $this->ds_gestioni_gas->setWhere( "prefix <> '" . $p4a->e3g_prefix . "'" );        
        $this->ds_gestioni_gas->setPk( "id_azienda" );        
        $this->ds_gestioni_gas->addOrder( "rag_soc" );     
        $this->ds_gestioni_gas->load();     
        $this->ds_gestioni_gas->firstRow();
        
        // db_source fornitori altri GAS
        $this->build( "p4a_db_source", "ds_fornitori_gas" );
        $this->ds_fornitori_gas->setFields( array( "codice" => "codice", "CONCAT_WS( ' / ', descrizione, desc_agg )" => "desc_desc_agg" ) );
        $this->ds_fornitori_gas->setTable( $this->ds_gestioni_gas->fields->prefix->getNewValue() . "anagrafiche" );
        $this->ds_fornitori_gas->setWhere( "tipocfa = 'F' OR idanag = 0" );        
        $this->ds_fornitori_gas->setPk( "codice" );        
        $this->ds_fornitori_gas->addOrder( "desc_desc_agg" );     
        $this->ds_fornitori_gas->load();
        $this->ds_fornitori_gas->firstRow();


        // ------------------------------------------------------------- Oggetti
        // Combo fornitori di riferimento
        $this->build( "p4a_field", "fld_fornitori_rif" );
        $this->fld_fornitori_rif->setLabel( "Listino di riferimento da duplicare" );
        $this->fld_fornitori_rif->setType( "select" );
        $this->fld_fornitori_rif->setSource( $this->ds_fornitori_rif );
        $this->fld_fornitori_rif->setSourceValueField( "codice" );
        $this->fld_fornitori_rif->setSourceDescriptionField( "desc_desc_agg" );
        $this->fld_fornitori_rif->setNewValue( $this->ds_fornitori_rif->fields->codice->getNewValue() );
        $this->fld_fornitori_rif->label->setWidth( 200 );
        $this->fld_fornitori_rif->setWidth( 300 );


        // Combo gestioni GAS
        $this->build( "p4a_field", "fld_gestioni_gas" );
        $this->fld_gestioni_gas->setLabel( "Gestione GAS dove duplicare" );
        $this->fld_gestioni_gas->setType( "select" );
        $this->fld_gestioni_gas->setSource( $this->ds_gestioni_gas );
        $this->fld_gestioni_gas->setSourceValueField( "prefix" );
        $this->fld_gestioni_gas->setSourceDescriptionField( "rag_soc" );
        $this->fld_gestioni_gas->setNewValue( $this->ds_gestioni_gas->fields->prefix->getNewValue() );
        $this->fld_gestioni_gas->label->setWidth( 200 );
        $this->fld_gestioni_gas->setWidth( 300 );
        $this->fld_gestioni_gas->addAction( "OnChange" );
        $this->intercept( $this->fld_gestioni_gas, "onChange", "fld_gestioni_gas_change" );      


        // Combo fornitori altri GAS
        $this->build( "p4a_field", "fld_fornitori_gas" );
        $this->fld_fornitori_gas->setLabel( "Fornitori con listino da sostituire" );
        $this->fld_fornitori_gas->setType( "select" );
        $this->fld_fornitori_gas->setSource( $this->ds_fornitori_gas );
        $this->fld_fornitori_gas->setSourceValueField( "codice" );
        $this->fld_fornitori_gas->setSourceDescriptionField( "desc_desc_agg" );
        $this->fld_fornitori_gas->label->setWidth( 200 );
        $this->fld_fornitori_gas->setWidth( 300 );

/*
        $values = array();
        $values[] = array( "id" => "1", "desc" => "Elimina listino destinazione e inserisci listino di riferimento" );
        $values[] = array( "id" => "2", "desc" => "Sostituisci valori articoli a corrispondenza campo codice" );
        $this->build( "p4a_array_source", "array_source" ); 
        $this->array_source->load( $values ); 
        $this->array_source->setPk( "id" ); 
        $this->build( "p4a_field", "fld_tipo_azione" );
        $this->fld_tipo_azione->setLabel( "Tipo azione" );
        $this->fld_tipo_azione->label->setWidth( 200 );
        $this->fld_tipo_azione->setType( "radio" );
        $this->fld_tipo_azione->setSource( $this->array_source ); 
        $this->fld_tipo_azione->setSourceDescriptionField( "desc" );
        $this->fld_tipo_azione->setValue( 1 );
*/

        // Bottone "Esegui duplicazione..."
        $this->build( "p4a_button", "bu_duplica" );
        $this->bu_duplica->setLabel( "Duplica listino..." );
        $this->bu_duplica->setIcon( "execute" );
        $this->bu_duplica->addAction( "onClick" );
        $this->intercept( $this->bu_duplica, "onClick", "bu_duplica_click" );
        $this->bu_duplica->requireConfirmation( "onClick", "Confermi la duplicazione (il listino esistente nella destinazione verra' sovrascritto)?" );
        

        // ---------------------------------------------------------------------
        $this->build( "p4a_fieldset", "fs_main" );
        $this->fs_main->setWidth( E3G_FIELDSET_SEARCH_WIDTH );
        $this->fs_main->anchor( $this->fld_fornitori_rif );
        $this->fs_main->anchor( $this->fld_gestioni_gas );
        $this->fs_main->anchor( $this->fld_fornitori_gas );
//      $this->fs_main->anchor( $this->fld_tipo_azione );
        $this->fs_main->anchorRight( $this->bu_duplica );


        // ---------------------------------------------------- Frame principale
        $frm=& $this->build( "p4a_frame", "frm" );
        $frm->setWidth( E3G_MAIN_FRAME_WIDTH );
        
        $frm->anchor( $this->message );
        $frm->anchor( $this->fs_main );
        
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

        $p4a =& p4a::singleton();

        parent::main();
    }
    

    // -------------------------------------------------------------------------
    function fld_gestioni_gas_change()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();

        $this->ds_fornitori_gas->setTable( $this->fld_gestioni_gas->getNewValue() . "anagrafiche" );
        $this->ds_fornitori_gas->load();
        $this->ds_fornitori_gas->firstRow();

        $this->fld_fornitori_gas->setNewValue( $this->ds_fornitori_gas->fields->codice->getNewValue() );
    }


    // -------------------------------------------------------------------------
    function bu_duplica_click()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();


        // Sorgente, fornitore il cui listino è da duplicare (codice):       $this->fld_fornitori_rif->getNewValue()
        // Destinazione, GAS (prefix):                                       $this->fld_gestioni_gas->getNewValue()
        // Destinazione, fornitore il cui listino è da rimpiazzare (codice): $this->fld_fornitori_gas->getNewValue()


        // Verifica valori di partenza
        if ( $this->fld_fornitori_rif->getNewValue() == "00" ) {
            $this->message->setIcon( "error" );
            $this->message->setValue( "Selezionare un listino di riferimento da duplicare." );
            return;
        }
        elseif ( $this->fld_fornitori_gas->getNewValue() == "00" ) {
            $this->message->setIcon( "error" );
            $this->message->setValue( "Selezionare un fornitore di destinazione da sostituire." );
            return;
        }


        // 1) Eliminazione listino articoli        
        $sql_txt =
            "DELETE FROM " . $this->fld_gestioni_gas->getNewValue() . "articoli " .
            " WHERE centrale = '" . $this->fld_fornitori_gas->getNewValue() . "'";
        $db->query( $sql_txt );


        // 2) Duplicazione listino da sorgente a destinazione
        $qu_articoli_ref = $db->getAll( 
            "SELECT * " .
            "  FROM " . $p4a->e3g_prefix . "articoli " .
            " WHERE centrale = '" . $this->fld_fornitori_rif->getNewValue() . "'" );          

        if ( $qu_articoli_ref ) {

            // db_source per l'inserimento degli articoli
            $this->build( "p4a_db_source", "ds_articoli_gas" );
            $this->ds_articoli_gas->setTable( $this->fld_gestioni_gas->getNewValue() . "articoli");
            $this->ds_articoli_gas->setPk( "idarticolo" );        
            $this->ds_articoli_gas->load();

            $new_idarticolo = $db->queryOne( "SELECT MAX( idarticolo ) FROM " . $this->fld_gestioni_gas->getNewValue() . "articoli" );
            if ( !is_numeric($new_idarticolo) )
                $new_idarticolo = 0;
            $new_idarticolo++;
    
            $n_articoli = 0;
            foreach ( $qu_articoli_ref as $qu_articolo_ref ) {
                $this->ds_articoli_gas->newRow();
                
                $this->ds_articoli_gas->fields->idarticolo->setNewValue( $new_idarticolo );
                
                $this->ds_articoli_gas->fields->codice->setNewValue(                 $qu_articolo_ref["codice"] );
                $this->ds_articoli_gas->fields->descrizione->setNewValue(            $qu_articolo_ref["descrizione"] );
                $this->ds_articoli_gas->fields->prezzoven->setNewValue(              $qu_articolo_ref["prezzoven"] );
                $this->ds_articoli_gas->fields->prezzoacq->setNewValue(              $qu_articolo_ref["prezzoacq"] );
                $this->ds_articoli_gas->fields->prezzo_mag_perc_libera->setNewValue( $qu_articolo_ref["prezzo_mag_perc_libera"] );
                $this->ds_articoli_gas->fields->tipo->setNewValue(                   $qu_articolo_ref["tipo"] );
                $this->ds_articoli_gas->fields->centrale->setNewValue(               $qu_articolo_ref["centrale"] );
                $this->ds_articoli_gas->fields->catmerce->setNewValue(               $qu_articolo_ref["catmerce"] );
                $this->ds_articoli_gas->fields->um->setNewValue(                     $qu_articolo_ref["um"] );
                $this->ds_articoli_gas->fields->pzperconf->setNewValue(              $qu_articolo_ref["pzperconf"] );
                $this->ds_articoli_gas->fields->qtaminordine->setNewValue(           $qu_articolo_ref["qtaminordine"] );
                $this->ds_articoli_gas->fields->qtaminperfamiglia->setNewValue(      $qu_articolo_ref["qtaminperfamiglia"] );
                $this->ds_articoli_gas->fields->um_qta->setNewValue(                 $qu_articolo_ref["um_qta"] );
                $this->ds_articoli_gas->fields->stato->setNewValue(                  $qu_articolo_ref["stato"] );
                $this->ds_articoli_gas->fields->data_ins->setNewValue(               $qu_articolo_ref["data_ins"] );
                $this->ds_articoli_gas->fields->data_agg->setNewValue(               $qu_articolo_ref["data_agg"] );
                $this->ds_articoli_gas->fields->bio->setNewValue(                    $qu_articolo_ref["bio"] );
                $this->ds_articoli_gas->fields->ingredienti->setNewValue(            $qu_articolo_ref["ingredienti"] );
                $this->ds_articoli_gas->fields->data_agg_ing->setNewValue(           $qu_articolo_ref["data_agg_ing"] );
                $this->ds_articoli_gas->fields->desc_agg->setNewValue(               $qu_articolo_ref["desc_agg"] );
                $this->ds_articoli_gas->fields->gestione_a_peso->setNewValue(        $qu_articolo_ref["gestione_a_peso"] );
                
                $this->ds_articoli_gas->saveRow();
                $new_idarticolo++;
                $n_articoli++;
            }

            $this->message->setIcon( "info" );
            $this->message->setValue( "Operazione terminata, sono stati duplicati $n_articoli articoli." );
        }
    }

}
?>