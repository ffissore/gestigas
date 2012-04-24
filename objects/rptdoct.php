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

class rptdoct extends P4A_Mask
{
	function &rptdoct()
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();


		//Sorgente dati principale
		// data sources
		$this->build("p4a_db_source", "ds_campi");
		$this->ds_campi->setTable($p4a->e3g_prefix."rptdoc");
		$this->ds_campi->setWhere("tipo='T'");
		$this->ds_campi->setPk("idtable");
		
		$this->ds_campi->load();
		$this->setSource($this->ds_campi);
		$this->ds_campi->firstRow();
		$this->ds_campi->fields->idtable->setSequence("rptdoc_id");

		// Fields properties
		$fields =& $this->fields;

		// Campi Obbligatori Fields
	    $this->mf = array("codtipodoc");
		foreach($this->mf as $mf){
			$fields->$mf->label->setFontWeight("bold");
		}


		// Aggiungo i campi della maschera
		//Campo codice
		$this->build("p4a_db_source", "ds_tipo");
		$this->ds_tipo->setTable($p4a->e3g_prefix."doctipidoc");
		$this->ds_tipo->setPk("codice");
		$this->ds_tipo->load();

		$fields->codtipodoc->setLabel('tipo Doc.');
		$fields->codtipodoc->setType('select');
		$fields->codtipodoc->setSource($this->ds_tipo);
		$fields->codtipodoc->setSourceValueField("codice");
		$fields->codtipodoc->setSourceDescriptionField("descrizione");
	
		$this->fields->campo->setLabel('Campo');
		$this->fields->campostampa->setLabel('nome in Stampa');

		
		$table =& $this->build("p4a_table", "table");
 		$table->setWidth(730);
		$table->setSource($this->ds_campi);
		$table->setVisibleCols(array('codtipodoc', 'campo', 'campostampa'));
		
		$table->cols->campo->setLabel('Campo');
		$table->cols->campostampa->setLabel('nome in Stampa');
		
		$table->cols->codtipodoc->setLabel('tipo Doc.');
		$table->cols->codtipodoc->setSource($this->ds_tipo);
		$table->cols->codtipodoc->setSourceValueField("codice");
		$table->cols->codtipodoc->setSourceDescriptionField("descrizione");
		
			
		// Toolbar
		$this->build("p4a_standard_toolbar", "toolbar");
		$this->toolbar->setMask($this);


		//Setto il titolo della maschera
		$this->SetTitle('impostazione Stampa Corpo Doc.');


		// Message
		$message =& $this->build("p4a_message", "message");
		$message->setWidth("300");



		//Fieldset con l'elenco dei campi
		$fset=& $this->build("p4a_fieldset", "frame");

 		$fset->anchor($this->fields->codtipodoc);
 		$fset->anchor($this->fields->campo);
		$fset->anchor($this->fields->campostampa);
		$fset->anchor($this->table);
		
		$fset->setWidth(730);


		// Frame
		$frm=& $this->build("p4a_frame", "frm");
		$frm->setWidth(730);

		$frm->anchor($message);
		$frm->anchor($fset);

		e3g_scrivi_footer( $this, $frm );

  		// Display
		$this->display("main", $frm);
		$this->display("menu", $p4a->menu);
		$this->display("top", $this->toolbar);
	}
	
	
function saveRow()
	{
		$valid = true;

		foreach($this->mf as $mf){
			$value = $this->fields->$mf->getNewValue();
			if(trim($value) === ""){
				$this->fields->$mf->setStyleProperty("border", "1px solid red");
				$valid = false;
			}
		}

	
		if ($valid) {
			$this->fields->tipo->setNewValue("T");


			
			parent::saveRow();
		}
		else
		{
			$this->message->setValue("Compilare i campi obbligatori");
		}
	}
	
	

	function main()
	{
		parent::main();

		foreach($this->mf as $mf){
			$this->fields->$mf->unsetStyleProperty("border");
		}
	}


}
?>