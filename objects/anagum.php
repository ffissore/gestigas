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


class anagum extends P4A_Mask
{

    // -------------------------------------------------------------------------
	function anagum ()
    // -------------------------------------------------------------------------
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();


		$this->SetTitle( "Unita' di Misura" );


        //--------------------------------------------- Sorgente dati principale
		$this->build( "p4a_db_source", "ds_um" );
		$this->ds_um->setTable( $p4a->e3g_prefix . "um" );
		$this->ds_um->setPk( "codice" );
		$this->ds_um->addOrder( "ordine" );
		$this->ds_um->load();

		$this->setSource( $this->ds_um );
		$this->ds_um->firstRow();

		// Fields properties
		$fields =& $this->fields;

		// Campi Obbligatori Fields
	    $this->mf = array( "codice", "desc_singolare", "desc_plurale", "genere" );
		foreach( $this->mf as $mf ){
			$fields->$mf->label->setFontWeight( "bold" );
		}


		// ------------------------------------------------------------- Toolbar
		if ( E3G_TIPO_GESTIONE == 'G' )
		{
           	switch ( $p4a->e3g_utente_tipo ) {
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
    	            break;
            }
		}
		else 
        	$this->build( "p4a_standard_toolbar", "toolbar" );  // Equogest 

		$this->toolbar->setMask($this);


        //---------------------------------------------------- Eventuale warning
		$message =& $this->build( "p4a_message", "message" );
		$message->setWidth( 700 );


        //----------------------------------------------------- Griglia centrale 
        $this->build( "p4a_table", "tab_um" );
        $this->tab_um->showNavigationBar();
        $this->tab_um->setWidth( E3G_TABLE_WIDTH );
        $this->tab_um->setTitle( "" );
        $this->tab_um->setSource( $this->ds_um );
        $this->tab_um->setVisibleCols( array( "codice", "desc_singolare", "desc_plurale", "genere", "ordine" ) );
        
        $this->tab_um->cols->codice->setLabel( "Codice" );
        $this->tab_um->cols->desc_singolare->setLabel( "Descrizione al singolare" );
        $this->tab_um->cols->desc_plurale->setLabel( "Descrizione al plurale" );
        $this->tab_um->cols->genere->setLabel( "Genere" );
        $this->tab_um->cols->ordine->setLabel( "Ordine" );

        $this->tab_um->cols->codice->setWidth( 150 );
        //$this->tab_um->cols->desc_singolare->setWidth();  Per differenza
        //$this->tab_um->cols->desc_plurale->setWidth();  Per differenza
        $this->tab_um->cols->genere->setWidth( 150 );
        $this->tab_um->cols->ordine->setWidth( 50 );
        
        
        //------------------------------------------------------ Vista dettaglio
		$this->fields->codice->setLabel( "Codice" );
        $this->fields->codice->setTooltip( "Impostabile solo in fase di inserimento e poi non piu' modificabile" );
		$this->fields->codice->disable();  // Abilitato solo durante l'inserimento
        $this->fields->codice->label->setWidth( 180 );
	
		$this->fields->desc_singolare->setLabel( "Descrizione al singolare" );
		$this->fields->desc_singolare->setWidth( 250 );
        $this->fields->desc_singolare->label->setWidth( 180 );

		$this->fields->desc_plurale->setLabel( "Descrizione al plurale" );
		$this->fields->desc_plurale->setWidth( 250 );
        $this->fields->desc_plurale->label->setWidth( 180 );

		$this->fields->genere->setLabel( "Genere" );
        $this->fields->genere->setTooltip( "Tipologia di unita' di misura" );
        $this->fields->genere->label->setWidth( 180 );
        
        $this->fields->ordine->setLabel( "Ordine" );
        $this->fields->ordine->setTooltip( "Valore numerico per determinare l'ordinamento tra le righe" );
		

        // ------------------------------------ Fieldset principale di dettaglio
		$this->build( "p4a_fieldset", "fs_um" );
        $this->fs_um->setTitle( "Dettaglio Unita' di Misura" );
        $this->fs_um->setWidth( E3G_FIELDSET_DATI_WIDTH );

 		$this->fs_um->anchor( $this->fields->codice );
 		$this->fs_um->anchor( $this->fields->desc_singolare );
		$this->fs_um->anchor( $this->fields->desc_plurale );
		$this->fs_um->anchor( $this->fields->genere );
		$this->fs_um->anchorLeft( $this->fields->ordine );



        // ---------------------------------------------------- Frame principale
		$frm=& $this->build( "p4a_frame", "frm" );
		$frm->setWidth( E3G_MAIN_FRAME_WIDTH );
		$frm->anchor( $message );
        $frm->anchor( $this->tab_um );
        $frm->anchor( $this->fs_um );

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
		$n = $db->queryOne( "SELECT COUNT(*) FROM " . $p4a->e3g_prefix . "um" );
		if ( $n == 0 )
			$this->newRow();
			
		parent::main();

		foreach( $this->mf as $mf ){
			$this->fields->$mf->unsetStyleProperty( "border" );
		}
	}
	

    // -------------------------------------------------------------------------
	function newRow()
    // -------------------------------------------------------------------------
	{	
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();

		parent::newRow();	

        $new_ordine = $db->queryOne(
            "SELECT MAX( ordine ) FROM " . $p4a->e3g_prefix . "um" );
        if ( is_numeric($new_ordine) )
            $new_ordine += 10;
        else 
            $new_ordine = 10; 
        $this->fields->ordine->setNewValue( $new_ordine );

		$this->fields->codice->enable();
	}


    // -------------------------------------------------------------------------
	function saveRow()
    // -------------------------------------------------------------------------
	{	
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();

        $this->fields->codice->setNewValue( trim( $this->fields->codice->getNewValue() ) );
        $this->fields->desc_singolare->setNewValue( ucfirst(strtolower(trim( $this->fields->desc_singolare->getNewValue() ))) ); 
        $this->fields->desc_plurale->setNewValue( ucfirst(strtolower(trim( $this->fields->desc_plurale->getNewValue() ))) ); 
        $this->fields->genere->setNewValue( ucfirst(strtolower(trim( $this->fields->genere->getNewValue() ))) ); 

        $error_text = "";

        // Verifica campi obbligatori
        foreach ( $this->mf as $mf ) {
            $value = $this->fields->$mf->getNewValue();
            if (trim($value) === "") {
                $this->fields->$mf->setStyleProperty( "border", "1px solid red" );
                $error_text = "Compilare i campi obbligatori.";
            }
        }

        
        if ( $error_text == "" ) {
            // Verifica campo codice non duplicato 
            if ( $this->fields->codice->getNewValue() != "" && $this->ds_um->isNew() ) {
                $n = $db->queryOne("SELECT COUNT(*) FROM " . $p4a->e3g_prefix . "um WHERE codice = '" . addslashes( $this->fields->codice->getNewValue() ) . "'" );
                if ( $n > 0 ) {
                    $error_text = "Codice '" . $this->fields->codice->getNewValue() . "' gia' presente.";
                    $this->fields->codice->enable();
                }
            }
        }

        if ( $error_text == "" ) {
            parent::saveRow();

            $this->tab_um->syncPageWithSource();
            $this->fields->codice->disable();
        }
        else
            $this->message->setValue( $error_text );
	}

}

?>