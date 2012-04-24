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


class anag_utenti_eg extends P4A_Mask
{
	var $newrecord = 0;
	
	function anag_utenti_eg()
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();

		$this->setTitle("Anagrafica Utenti");

		//Sorgente dati principale
		// data sources
		$this->build("p4a_db_source", "ds_anagr");
		$this->ds_anagr->setTable($p4a->e3g_prefix."anagrafiche");
		switch ($p4a->e3g_utente_tipo) {
            case "A":
                $this->ds_anagr->setWhere("tipocfa='U' ");
                break;
            default :
        		$this->ds_anagr->setWhere("tipocfa='U' and tipoutente<>'A'");
        		break;
		}
		
		$this->ds_anagr->setPk("idanag");
		$this->ds_anagr->setPageLimit(5);


		$this->ds_anagr->addOrder("descrizione");
		$this->ds_anagr->load();
		//$this->ds_anagr->fields->idanag->setSequence("anag_id");
		

		$this->setSource($this->ds_anagr);
		$this->ds_anagr->firstRow();

		$this->fields->idanag->setType('decimal');
		
		// Fields properties
		//$this->setFieldsProperties();
		$fields =& $this->fields;

		$this->fields->datainizio->setType('date');
		$this->fields->datafine->setType('date');
		$this->fields->datainizio->setLabel('data Inizio Servizio');
		$this->fields->datafine->setLabel('data Fine Servizio');

		// Campo per la modifica della password
		$new_pwd1 =& $this->build("p4a_field", "new_pwd1");
		$new_pwd1->setLabel('Nuova password:');
		$new_pwd1->setType('password');
		$new_pwd1->setWidth("200");
		$new_pwd1->setValue( "" );
		$new_pwd1->unsetStyleProperty("border");

		$new_pwd2 =& $this->build("p4a_field", "new_pwd2");
		$new_pwd2->setLabel('Verifica nuova password:');
		$new_pwd2->setType('password');
		$new_pwd2->setWidth("200");
		$new_pwd2->setValue( "" );
		$new_pwd2->unsetStyleProperty("border");

		

		// Campi Ricerca
		$fs_search =& $this->build("p4a_fieldset","fs_search");
		$fs_search->setTitle("Cerca");
		$txt_search =& $this->build("p4a_field", "txt_search");
		$txt_search->addAction("onReturnPress");
		$this->intercept($txt_search, "onReturnPress","search");
		$txt_search->setLabel("Rag. Sociale");

		//$txt_tipo =& $this->build("p4a_field", "txt_tipo");
		//$txt_tipo->addAction("onReturnPress");
		//$this->intercept($txt_tipo, "onReturnPress","search");
		//$txt_tipo->setLabel("tipo");

		$cmd_search =& $this->build("p4a_button","cmd_search");
		$cmd_search->setValue("Cerca");
		$this->intercept($cmd_search, "onClick","search");
		$fs_search->anchor($txt_search);
		//$fs_search->anchor($txt_tipo);
		$fs_search->anchorLeft($cmd_search);


		// Campi Obbligatori Fields
	    $this->mf = array("idanag", "codice", "descrizione");
		foreach($this->mf as $mf){
			$fields->$mf->label->setFontWeight("bold");
		}


		//Tabella dei donatori
		// Toolbar
		$this->build("p4a_standard_toolbar", "toolbar");
		$this->toolbar->setMask($this);


		// Table
		$table =& $this->build("p4a_table", "tabdon");
 		$table->setWidth(300);
		$table->setSource($this->ds_anagr);
		$table->setVisibleCols(array("codice","descrizione"));
		// ,"CodtipoContatto"


		while ($col =& $table->cols->nextItem()) {
			$col->setWidth(150);
		}
		$table->showNavigationBar();



		// Message
		$message =& $this->build("p4a_message", "message");
		$message->setWidth("300");



		//Fieldset con l'elenco dei campi
		$fset=& $this->build("p4a_fieldset", "frame");
		$fset->setTitle("Anagrafica");

 		$fset->anchor($this->fields->codice);
 		$fset->anchorLeft($this->fields->CodtipoContatto);
		$fset->anchor($this->fields->Titolo);
		$fset->anchor($this->fields->descrizione);
		$fset->anchor($this->fields->indirizzo);
		$fset->anchor($this->fields->cap);
 		$fset->anchorLeft($this->fields->localita);
 		$fset->anchor($this->fields->provincia);
 		$fset->anchor($this->fields->telefono);
 		$fset->anchorLeft($this->fields->fax);
		$fset->anchor($this->fields->email);
		$fset->anchor($this->new_pwd1);
		$fset->anchor($this->new_pwd2);
			
		
		$fset->anchor($this->fields->datainizio);
		$fset->anchorLeft($this->fields->datafine);
			
			
		$fset->setWidth(700);


		//Fieldset con l'elenco dei campi
		$fset1=& $this->build("p4a_fieldset", "frame");
		$fset1->setTitle("Dati Contabili");

 		//$fset1->anchor($this->fields->Quote);
 		//$fset1->anchorLeft($this->fields->TesseraNumero);
		//$fset1->anchor($this->fields->annoUltimaIscrizione);
		//$fset1->anchor($this->fields->UltimaSomma);
		//$fset1->anchorLeft($this->fields->dataIscrizione);
		//$fset1->setWidth(730);




		// Frame
		$frm=& $this->build("p4a_frame", "frm");
		$frm->setWidth(730);


		$frm->anchor($message);
		$frm->anchorLeft($fs_search);
		$frm->anchorRight($table);
  		$frm->anchor($fset);
  		//$frm->anchor($fset1);

		e3g_scrivi_footer( $this, $frm );

		// Display
		$this->display("main", $frm);
		$this->display("menu", $p4a->menu);
		$this->display("top", $this->toolbar);
	}


	function main()
	{
		parent::main();
		//$this->new_pwd1->setValue( "" );
		//$this->new_pwd2->setValue( "" );
		//$this->new_pwd1->unsetStyleProperty("border");
		//$this->new_pwd2->unsetStyleProperty("border");

		//foreach($this->mf as $mf){
		//	$this->fields->$mf->unsetStyleProperty("border");
		//}
	}



	function dopo_record_cambiato()
	{
		//$row = $this->mask->data->get_current_row();
	}


	function deleteRow()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();

		// Si controlla se utente cerca di auto eliminarsi
		if ($p4a->e3g_utente_idanag == $this->fields->idanag->getNewValue())
		{
			// sto cercando di cancellare me stesso
			$this->message->setIcon( "error" );
			$this->message->setValue( "Eliminazione non consentita: utente utilizzato per l'accesso corrente." );			 
		}	
		else
			parent::deleteRow();	
	}


	function newRow()
	{
		$db =& p4a_db::singleton();
		
		$this->newrecord = 1;

		parent::newRow();
	}
	
	
	function saveRow()
	{
		$db =& p4a_db::singleton();
		$this->fields->tipocfa->setNewValue("U");
		$p4a =& p4a::singleton();

		if (!is_numeric($this->fields->idanag->getNewValue()))
		{
			$this->fields->data_ins->setNewValue( date ("Y-m-d H:i:s") );
			$this->fields->tipocfa->setNewValue( "U" );
			$this->fields->stato->setNewValue( 1 );	
			$this->fields->n_login->setNewValue( 0 );

			// Equogest per ora esiste solo il tipo AS amministratore
			$this->fields->tipoutente->setNewValue( "AS");	

			
			$maxid = $db->queryOne("SELECT MAX(idanag) FROM ".$p4a->e3g_prefix."anagrafiche");
			if (is_numeric($maxid))
			{
				$maxid++;
			}
			else 
			{
				$maxid=1;
			}	
			$this->fields->idanag->setNewValue($maxid);
			
			$this->fields->data_ins->setNewValue(date ("Y-m-d H:i:s"));

		}	

		// Compongo il codice utente 
		if ($this->fields->codice->getNewValue() == "")
		{
			$this->fields->codice->setNewValue($this->fields->tipocfa->getNewValue() .
				str_pad($this->fields->idanag->getNewValue(), 4, "0", STR_PAD_LEFT));	  
		}

		
		$valid = true;
		$error_text = "";

		foreach($this->mf as $mf){
			$value = $this->fields->$mf->getNewValue();
			if(trim($value) === ""){
				$this->fields->$mf->setStyleProperty("border", "1px solid red");
				$valid = false;
				$error_text = "Compilare la password.";

			}
		}

		if ( $this->fields->email->getNewValue() =="" ) {
			// indirizzo e-mail non valido
			$error_text = "L'indirizzo e-mail indicato non sembra essere valido.";
			$this->fields->email->setStyleProperty("border", "1px solid red");
		}
		elseif ( $this->newrecord and $this->new_pwd1->getNewValue() == "" ) {
			// Nuovo utente, ma senza password
			$error_text = "Compilare la password.";
			$this->new_pwd1->setStyleProperty("border", "1px solid red");
		}
		elseif ( $this->new_pwd1->getNewValue() != $this->new_pwd2->getNewValue() ) {
			// Password diversa da seconda password di verifica
			$error_text = "Le due password non coincidono, prova a riscriverle.";
			$this->new_pwd1->setStyleProperty("border", "1px solid red");
			$this->new_pwd2->setStyleProperty("border", "1px solid red");
		}

		// Verifica e-mail non duplicato
		if ( $error_text == "" and  $this->fields->email->getNewValue() != "" ) {
			$n = $db->queryOne(
				"SELECT COUNT(*) FROM " . $p4a->e3g_prefix . "anagrafiche " .
				" WHERE email = '" . $this->fields->email->getNewValue() . "' ".
				"   AND idanag <> " . $this->fields->idanag->getNewValue() );
								
			if ( $n > 0 ) {
				$error_text = "indirizzo e-mail '" . $this->fields->email->getNewValue() . "' gia' presente.";
				$this->fields->email->setStyleProperty("border", "1px solid red");
			}
		}


		if ($error_text == "") 
		{
			if ( $this->new_pwd1->getNewValue() != "" ) 
			{
				$this->fields->password->setValue( $this->new_pwd1->getNewValue() );	
			}
			$this->fields->data_agg->setNewValue( date ("Y-m-d H:i:s") );

			parent::saveRow();
			$this->newrecord = 0;
			$error_text = "";	
		}else{
			$this->message->setValue($error_text);
		}
	}


	function search()
	{
		$value = $this->txt_search->getNewValue();
		//$value2 = $this->txt_tipo->getNewValue();


		$this->data->setWhere("descrizione LIKE '%{$value}%'");
		$this->data->firstRow();
		$num_rows = $this->data->getNumRows();

		if (!$num_rows) {
			$this->message->setValue("Nessun record trovato!");
			$this->data->setWhere(null);
			$this->data->firstRow();
		}
	}


}


?>