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


class login_gest extends P4A_Mask
{
	function &login_gest ()
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();

		// data sources
		$this->build("p4a_db_source", "ds_log");
		$this->ds_log->setTable("login");
		$this->ds_log->setPk("idutente");
		$this->ds_log->setQuery("SELECT * FROM login INNER JOIN login_database ON login_database.idutente = login.idutente WHERE login_database.prefix='".$p4a->e3g_prefix."'");
		$this->ds_log->load();

		$this->setSource($this->ds_log);
		$this->ds_log->firstRow();

		// Fields properties
		$fields =& $this->fields;

		// Campi Obbligatori Fields
	    $this->mf = array("username","password");
		foreach($this->mf as $mf){
			$fields->$mf->label->setFontWeight("bold");
		}


		// Aggiungo i campi della maschera
		//Campo codice
		$this->fields->username->setLabel('User Name');
		$this->fields->password->setLabel('Password');

		$this->build("p4a_db_source", "ds_anag");
		$this->ds_anag->setTable($p4a->e3g_prefix."anagrafiche");
		$this->ds_anag->setPk("codice");
		if ( E3G_TIPO_GESTIONE == 'G' )
		{
			// gestigas --> anche i "Clienti" = Famiglie
		}
		else 
		{
			// gestione Equogest --> solo utenti

			$this->ds_anag->setWhere("tipocfa='U'");
		}
		//$this->ds_anag->setWhere("tipocfa<>'F'");
		$this->ds_anag->load();

		$this->build("p4a_db_source", "ds_tipo");
		$this->ds_tipo->setTable("_login_tipo_utente");
		$this->ds_tipo->setPk("codice");
		$this->ds_tipo->load();

		
		//Campo descrizione
		$this->fields->codutente->setLabel('descrizione');
		$this->fields->codutente->setWidth(250);
		$this->fields->codutente->setType('select');
		$this->fields->codutente->setSourceValueField('codice');
		$this->fields->codutente->setSourceDescriptionField('descrizione');
		$this->fields->codutente->setSource($this->ds_anag);
		
		$this->fields->tipoutente->setLabel('tipo Accesso');
		$this->fields->tipoutente->setWidth(250);
		$this->fields->tipoutente->setType('select');
		$this->fields->tipoutente->setSourceValueField('codice');
		$this->fields->tipoutente->setSourceDescriptionField('descrizione');
		$this->fields->tipoutente->setSource($this->ds_tipo);
				

		$table =& $this->build("p4a_table", "table");
		$table->setWidth(730);
		$table->setSource($this->ds_log);
		$table->setVisibleCols(array("codutente", "tipoutente"));
		
		$table->cols->codutente->setLabel('Utente');
		$table->cols->codutente->setSource($this->ds_anag);
		$table->cols->codutente->setSourceValueField("codice");
		$table->cols->codutente->setSourceDescriptionField("descrizione");

		$table->cols->tipoutente->setLabel('tipo Utente');
		$table->cols->tipoutente->setSource($this->ds_tipo);
		$table->cols->tipoutente->setSourceValueField("codice");
		$table->cols->tipoutente->setSourceDescriptionField("descrizione");



		while ($col =& $table->cols->nextItem()) {
			$col->setWidth(160);
		}
		$table->showNavigationBar();



		// Toolbar
		$this->build("p4a_standard_toolbar", "toolbar");
		$this->toolbar->setMask($this);


		//Setto il titolo della maschera
		$this->SetTitle('Gestione Login');


		// Message
		$message =& $this->build("p4a_message", "message");
		$message->setWidth("300");



		//Fieldset con l'elenco dei campi
		$fset=& $this->build("p4a_fieldset", "frame");

 		$fset->anchor($this->fields->codutente);
 		$fset->anchor($this->fields->username);
 		$fset->anchorLeft($this->fields->password,100);
 		$fset->anchor($this->fields->tipoutente);
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

	function main()
	{
		parent::main();

		foreach($this->mf as $mf){
			$this->fields->$mf->unsetStyleProperty("border");
		}
	}

	function saveRow()
	{	
		$db =& p4a_db::singleton();
		$valid = true;

		foreach($this->mf as $mf){
			$value = $this->fields->$mf->getNewValue();
			if(trim($value) === ""){
				$this->fields->$mf->setStyleProperty("border", "1px solid red");
				$valid = false;
			}
		}

		if (!is_numeric($this->fields->idutente->getNewValue()))
		{
			$maxid = $db->queryOne("SELECT MAX(idutente) FROM login");
			if (is_numeric($maxid))
			{
				$maxid++;
			}
			else 
			{
				$maxid=1;
			}	
			$this->fields->idutente->setNewValue($maxid);
		}

		$descriz = $db->queryOne("SELECT descrizione FROM ".$p4a->e3g_prefix."anagrafiche WHERE codice='".$this->fields->codutente->getnewValue()."'");
		$this->fields->descrizione->setnewValue($descriz);
				
		if ($valid) {
			parent::saveRow();
		}else{
			$this->message->setValue("Compilare i campi obbligatori");
		}
	}


}
?>