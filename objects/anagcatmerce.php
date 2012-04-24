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
require_once( dirname(__FILE__) . '/../config.php' );


class anagcatmerce extends P4A_Mask
{
	function anagcatmerce ()
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();


		if ( E3G_TIPO_GESTIONE == 'G' )
			$this->SetTitle('Sottocategorie Articoli');
		else 
			$this->SetTitle('Categorie Merceologiche Articoli');


		//--------------------------------------------- Sorgente dati principale
		$this->build("p4a_db_source", "ds_catmer");
		$this->ds_catmer->setTable($p4a->e3g_prefix."catmerceologica");
		$this->ds_catmer->setPk("codice");
		$this->ds_catmer->setWhere("codice <> '000'");
        $this->ds_catmer->addOrder("tipo");
        $this->ds_catmer->addOrder("descrizione");
		$this->ds_catmer->load();
        $this->ds_catmer->firstRow();

		$this->setSource($this->ds_catmer);

        //----------------------------------------- Sorgente per campo categoria
        $this->build("p4a_db_source", "ds_tipo");
        $this->ds_tipo->setTable($p4a->e3g_prefix."tipiarticoli");
        $this->ds_tipo->setPk("codice");
        $this->ds_tipo->addOrder("descrizione");
        $this->ds_tipo->load();
        

		// Fields properties
		$fields =& $this->fields;

		//---------------------------------------------------- Campi Obbligatori 
	    $this->mf = array("codice");
		foreach($this->mf as $mf){
			$fields->$mf->label->setFontWeight("bold");
		}

		//-------------------------------------------------------------- Toolbar
		$this->build("p4a_standard_toolbar", "toolbar");
		$this->toolbar->setMask($this);

        // ------------------------------------------------------- Vista tabella
        $table =& $this->build("p4a_table", "table");
        $table->setWidth(E3G_TABLE_WIDTH);
        $table->setSource($this->ds_catmer);
        $table->setVisibleCols( array("tipo","codice", "descrizione") );
        $table->showNavigationBar();

        $table->cols->tipo->setType("select");
        $table->cols->tipo->setSource($this->ds_tipo);
        $table->cols->tipo->setSourceValueField("codice");
        $table->cols->tipo->setSourceDescriptionField("descrizione");
        if ( E3G_TIPO_GESTIONE == 'G' ) {
            $table->cols->tipo->setLabel("Categoria");
        }
        else {
            $table->cols->tipo->setLabel("Tipo articolo");
        }

        $table->cols->codice->setLabel("Codice");
        $table->cols->descrizione->setLabel("Descrizione sottocategoria");           

        //-------------------------------------------------------------- Message
        $message =& $this->build("p4a_message", "message");
        $message->setWidth("300");

		//------------------------------------------------- Campi della finestra
        // Campo categoria
        $this->fields->tipo->setType('select');
        $this->fields->tipo->setSourceValueField('codice');
        $this->fields->tipo->setSourceDescriptionField('descrizione');
        $this->fields->tipo->setSource($this->ds_tipo);
        $this->fields->tipo->setLabel( $table->cols->tipo->getLabel() );
        $this->fields->tipo->setWidth(250);
        
		// Campo codice
		$this->fields->codice->setLabel('Codice');
        $this->fields->codice->disable();

		// Campo descrizione
		$this->fields->descrizione->setLabel('Descrizione');
		$this->fields->descrizione->setWidth(250);


		// Fieldset con l'elenco dei campi
		$fset=& $this->build("p4a_fieldset", "frame");
        $fset->setTitle("Dettaglio");
        $fset->setWidth( E3G_FIELDSET_DATI_WIDTH );
        $fset->anchor($this->fields->tipo);
        $fset->anchor($this->fields->codice);
 		$fset->anchor($this->fields->descrizione);


        // ---------------------------------------------------- Frame principale
        $frm=& $this->build("p4a_frame", "frm");
        $frm->setWidth( E3G_MAIN_FRAME_WIDTH );
        $frm->anchor($table);
		$frm->anchor($message);
        $frm->anchor($fset);

		e3g_scrivi_footer( $this, $frm );

  		// Display
		$this->display("main", $frm);
		$this->display("menu", $p4a->menu);
		$this->display("top", $this->toolbar);
	}


	function main()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();

		// Se non ci sono record, allora la finestra si predispone
		// in inserimento, ma bisogna generare l'evento newRow()
		$n = $db->queryOne("SELECT COUNT(*) FROM " . $p4a->e3g_prefix . "catmerceologica");
		if ( $n == 0 )
			$this->newRow();

			
		parent::main();

		foreach($this->mf as $mf){
			$this->fields->$mf->unsetStyleProperty("border");
		}
	}


	function newRow()
	{	
		parent::newRow();	

		$this->fields->codice->enable();
	}


	function saveRow()
	{	
		parent::saveRow();	
		$this->fields->codice->disable();
	}


}

?>