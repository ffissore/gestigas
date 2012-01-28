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

class login_database extends P4A_Mask
{
	function &login_database ()
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();

		// data sources
		$this->build("p4a_db_source", "ds_log");
		$this->ds_log->setTable("login_database");
		$this->ds_log->setPk("idtable");
		$this->ds_log->setWhere("prefix='".$p4a->e3g_prefix."'");
		
		$this->ds_log->addOrder("idutente");
		$this->ds_log->load();

		$this->setSource($this->ds_log);
		$this->ds_log->firstRow();

		// Fields properties
		$fields =& $this->fields;

		// Campi Obbligatori Fields
	    $this->mf = array("idutente","prefix");
		foreach($this->mf as $mf){
			$fields->$mf->label->setFontWeight("bold");
		}


		// Aggiungo i campi della maschera
		//Campo codice
		$this->fields->idutente->setLabel('Utente');
		$this->fields->prefix->setLabel('database');

		$this->build("p4a_db_source", "ds_anag");
		$this->ds_anag->setTable("login");
		$this->ds_anag->setPk("idutente");
		$this->ds_anag->load();


		
		//Campo descrizione
		$this->fields->idutente->setLabel('Utente');
		$this->fields->idutente->setWidth(250);
		$this->fields->idutente->setType('select');
		$this->fields->idutente->setSourceValueField('idutente');
		$this->fields->idutente->setSourceDescriptionField('descrizione');
		$this->fields->idutente->setSource($this->ds_anag);
		
		$this->fields->prefix->setLabel('database');
				

		$table =& $this->build("p4a_table", "table");
		$table->setWidth(730);
		$table->setSource($this->ds_log);
		$table->setVisibleCols(array("idutente", "prefix"));
		
		$table->cols->idutente->setLabel('Utente');
		$table->cols->idutente->setSource($this->ds_anag);
		$table->cols->idutente->setSourceValueField("idutente");
		$table->cols->idutente->setSourceDescriptionField("descrizione");

		$table->cols->prefix->setLabel('database');
		
		while ($col =& $table->cols->nextItem()) {
			$col->setWidth(160);
		}
		$table->showNavigationBar();



		// Toolbar
		$this->build("p4a_standard_toolbar", "toolbar");
		$this->toolbar->setMask($this);


		//Setto il titolo della maschera
		$this->SetTitle('Accesso database');


		// Message
		$message =& $this->build("p4a_message", "message");
		$message->setWidth("300");



		//Fieldset con l'elenco dei campi
		$fset=& $this->build("p4a_fieldset", "frame");

 		$fset->anchor($this->table);
 		$fset->anchor($this->fields->idutente);
 		$fset->anchor($this->fields->prefix);
 		
 		

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

	function main()
	{
		parent::main();

		foreach($this->mf as $mf){
			$this->fields->$mf->unsetStyleProperty("border");
		}
	}

	function newRow()
	{	
		$db =& p4a_db::singleton();

		$idtabella = $db->queryOne("SELECT MAX(idtable) as idtable FROM login_database");
		if (is_numeric($idtabella))
		{
			$idtabella++;
		}
		else
		{
			$idtabella = 1 ;
		}
		
		
		parent::newRow();

		$this->fields->prefix->setNewValue($p4a->e3g_prefix);
				
		$this->fields->idtable->setnewValue($idtabella);
		
	}


}
?>