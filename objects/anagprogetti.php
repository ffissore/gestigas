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


class anagprogetti extends P4A_Mask
{
	function anagprogetti ()
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();


		$this->SetTitle('Elenco Progetti');


		//Sorgente dati principale
		$this->build("p4a_db_source", "ds_prog");
		$this->ds_prog->setTable($p4a->e3g_prefix."progetti");
		$this->ds_prog->setPk("codice");
		$this->ds_prog->setWhere("codice <> '00'");
		
		$this->ds_prog->addOrder("tipocfa","DESC");
		$this->ds_prog->addOrder("descrizione");
		$this->ds_prog->load();

		$this->setSource($this->ds_prog);
		$this->ds_prog->firstRow();

		// Fields properties
		$fields =& $this->fields;

		// Campi Obbligatori Fields
	    $this->mf = array("codice");
		foreach($this->mf as $mf){
			$fields->$mf->label->setFontWeight("bold");
		}


		// Toolbar
		$this->build("p4a_standard_toolbar", "toolbar");
		$this->toolbar->setMask($this);


		// Message
		$message =& $this->build("p4a_message", "message");
		$message->setWidth("300");


		$this->fields->codice->disable();
		
		$this->build("p4a_db_source", "ds_centrale");
		$this->ds_centrale->setTable($p4a->e3g_prefix."anagrafiche");
		$this->ds_centrale->setPk("codice");
		$this->ds_centrale->setWhere("tipocfa='F'  OR idanag = 0 ");
		$this->ds_centrale->addOrder("descrizione");
		$this->ds_centrale->load();
		
		$this->fields->centrale->setType('select');
		$this->fields->centrale->setSourceValueField('codice');
		$this->fields->centrale->setSourceDescriptionField('descrizione');
		$this->fields->centrale->setSource($this->ds_centrale);
		$this->fields->centrale->setLabel("fornitore");
		
		// Aggiungo i campi della maschera
		//Campo codice
		$this->fields->codice->setLabel('Codice');

		//Campo descrizione
		$this->fields->descrizione->setLabel('Descrizione');
		$this->fields->descrizione->setWidth(300);


		//Fieldset con l'elenco dei campi
		$fset=& $this->build("p4a_fieldset", "frame");
		//$fset->setTitle("Scheda Articolo");

 		$fset->anchor($this->fields->codice);
 		$fset->anchor($this->fields->descrizione);
		$fset->anchor($this->fields->centrale);
 		
		$fset->setWidth(700);


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