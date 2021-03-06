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

class periodoarticoli extends P4A_Mask
{
	function &periodoarticoli ()
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();

		$this->SetTitle("Disponibilita' Stagionale Prodotti");

		// Sorgente dati principale
		$this->build("p4a_db_source", "ds_per");
		$this->ds_per->setTable($p4a->e3g_prefix."articoloperiodo");
		$this->ds_per->setPk("idtable");
		//$this->ds_per->addJoin ("articoli", "codice=codice", "INNER");
		//$this->ds_per->setSelect("articoloperiodo.codice, articoloperiodo.dalmese, articoloperiodo.almese, articoli.descrizione");
		
		
		$this->ds_per->addOrder("dalmese");
		$this->ds_per->load();

		$this->setSource($this->ds_per);
		$this->ds_per->firstRow();
		
		// Bottone "Cerca" 
		$this->build("p4a_button", "bu_cerca");
		$this->bu_cerca->setLabel("Cerca");
		$this->bu_cerca->setIcon("find");
		$this->bu_cerca->addAction("onClick");
		$this->bu_cerca->setSize(16);
		$this->bu_cerca->setWidth(150);
		$this->intercept($this->bu_cerca, "onClick", "bu_cerca_click");

		// Bottone "Annulla Ricerca"
		$this->build("p4a_button", "bu_annulla_cerca");
		$this->bu_annulla_cerca->setLabel("Annulla ricerca");
		$this->bu_annulla_cerca->setIcon("cancel");
		$this->bu_annulla_cerca->addAction("onClick");
		$this->bu_annulla_cerca->setSize(16);
		$this->bu_annulla_cerca->setWidth(150);
		$this->intercept($this->bu_annulla_cerca, "onClick", "bu_annulla_cerca_click");


		// Fornitore per ricerca
		$this->build("p4a_db_source", "ds_forn_ricerca");
		$this->ds_forn_ricerca->setTable($p4a->e3g_prefix."anagrafiche");
		$this->ds_forn_ricerca->setWhere("tipocfa='F' OR idanag = 0");
		$this->ds_forn_ricerca->setPk("codice");		
		$this->ds_forn_ricerca->addOrder("descrizione");		
		$this->ds_forn_ricerca->load();		

		// Filtro fornitore
		$this->fld_cerca_forn=& $this->build("p4a_field", "fld_cerca_forn");
		$this->fld_cerca_forn->setLabel('Fornitore');
		$this->fld_cerca_forn->label->setWidth(60);
		$this->fld_cerca_forn->setType("select");
		$this->fld_cerca_forn->setSource($this->ds_forn_ricerca);
		$this->fld_cerca_forn->setSourceValueField("codice");
		$this->fld_cerca_forn->setSourceDescriptionField("descrizione");
		$this->fld_cerca_forn->setWidth(190);
		$this->fld_cerca_forn->setNewValue("00");

		$this->build("p4a_db_source", "ds_cat_sotcat");
		$this->ds_cat_sotcat->setTable($p4a->e3g_prefix."catmerceologica");
		$this->ds_cat_sotcat->setPk("codice");
		if ( E3G_TIPO_GESTIONE == 'G' )
			$this->ds_cat_sotcat->setQuery(
				"SELECT sc.codice, CONCAT_WS( ' : ', c.descrizione , sc.descrizione ) descrizione " .
				"  FROM ".$p4a->e3g_prefix."tipiarticoli c, ".$p4a->e3g_prefix."catmerceologica sc " .
				" WHERE sc.tipo = c.codice " .
				" ORDER BY c.descrizione, sc.descrizione" );
		$this->ds_cat_sotcat->load();


		// Filtro Categoria Articolo
		$this->fld_cerca_cat=& $this->build("p4a_field", "fld_cerca_cat");
		$this->fld_cerca_cat->setLabel('Categoria');
		$this->fld_cerca_cat->label->setWidth(60);
		$this->fld_cerca_cat->setType("select");
		$this->fld_cerca_cat->setSource($this->ds_cat_sotcat);
		$this->fld_cerca_cat->setSourceValueField("codice");
		$this->fld_cerca_cat->setSourceDescriptionField("descrizione");
		$this->fld_cerca_cat->setWidth(190);
		$this->fld_cerca_cat->setNewValue("00");


		// ds articoli per ricerca
		$this->build("p4a_db_source", "ds_art");
		$this->ds_art->setTable($p4a->e3g_prefix."articoli");
		$this->ds_art->setPk("codice");		
		$this->ds_art->setWhere("1=1");		
		$this->ds_art->addOrder("descrizione");	
		$this->ds_art->setPageLimit(5);	
		$this->ds_art->load();		


		$tab_art =& $this->build("p4a_table", "tab_art");
 		$tab_art->setWidth(350);
		$tab_art->setSource($this->ds_art);
		$this->intercept($this->tab_art->rows, "afterClick", "tabart_click");
		
		$tab_art->setVisibleCols(array('codice','descrizione', 'centrale'));
		$tab_art->cols->codice->setLabel('Codice');
		$tab_art->cols->descrizione->setLabel('Descrizione');
		$tab_art->cols->centrale->setLabel('Fornitore');

		$tab_art->cols->codice->setWidth('50');
		$tab_art->cols->descrizione->setWidth('200');
		$tab_art->cols->centrale->setWidth('100');




		// Fields properties
		$fields =& $this->fields;

		// Campi Obbligatori Fields
	    $this->mf = array("codice");
		foreach($this->mf as $mf){
			$fields->$mf->label->setFontWeight("bold");
		}


		// Aggiungo i campi della maschera
		//Campo codice
		$this->build("p4a_db_source", "ds_anagr");
		$this->ds_anagr->setTable($p4a->e3g_prefix."articoli");
		$this->ds_anagr->setPk("codice");
		$this->ds_anagr->setPageLimit(5);
		$this->ds_anagr->addOrder("descrizione");
		$this->ds_anagr->load();
		
		$values = array(); 
		$values[] = array("id" => "1", "desc" => "Gennaio");
		$values[] = array("id" => "2", "desc" => "Febbraio");
		$values[] = array("id" => "3", "desc" => "Marzo");
		$values[] = array("id" => "4", "desc" => "Aprile");
		$values[] = array("id" => "5", "desc" => "Maggio");
		$values[] = array("id" => "6", "desc" => "Giugno");
		$values[] = array("id" => "7", "desc" => "Luglio");
		$values[] = array("id" => "8", "desc" => "Agosto");
 		$values[] = array("id" => "9", "desc" => "Settembre");
		$values[] = array("id" => "10", "desc" => "Ottobre");
		$values[] = array("id" => "11", "desc" => "Novembre");
		$values[] = array("id" => "12", "desc" => "Dicembre");

		$array_source =& $this->build("p4a_array_source", "array_source"); 
		$array_source->load($values); 
		$array_source->setPk("id"); 
 

		$fields->codice->setLabel('Cod. Articolo');
		$fields->codice->disable();
		
		$this->fields->dalmese->setLabel('Dal Mese');
		$this->fields->dalmese->setType('select');
		$this->fields->dalmese->setSource($array_source);
		$this->fields->dalmese->setSourceValueField("id");
		$this->fields->dalmese->setSourceDescriptionField("desc");
		
		$this->fields->almese->setLabel('Al Mese');
		$this->fields->almese->setType('select');
		$this->fields->almese->setSource($array_source);
		$this->fields->almese->setSourceValueField("id");
		$this->fields->almese->setSourceDescriptionField("desc");
	
				
		$table =& $this->build("p4a_table", "table");
 		$table->setWidth(730);
		$table->setSource($this->ds_per);
		//$table->setVisibleCols(array('codice', 'descrizione','dalmese', 'almese'));
		$table->setVisibleCols(array('codice','dalmese', 'almese'));
		//$this->intercept($table->rows, "afterClick", "cambia_click");
		
		$table->cols->dalmese->setLabel('Dal Mese');
		$table->cols->dalmese->setSource($array_source);
		$table->cols->dalmese->setSourceValueField("id");
		$table->cols->dalmese->setSourceDescriptionField("desc");
		
		$table->cols->almese->setLabel('Al Mese');
		$table->cols->almese->setSource($array_source);
		$table->cols->almese->setSourceValueField("id");
		$table->cols->almese->setSourceDescriptionField("desc");
		
		
		$table->cols->codice->setLabel('codice');
		//$table->cols->codice->setSource($this->ds_anagr);
		//$table->cols->codice->setSourceValueField("codice");
		//$table->cols->codice->setSourceDescriptionField("descrizione");
		
				
		// Toolbar
		$this->build("p4a_standard_toolbar", "toolbar");
		$this->toolbar->setMask($this);


		// Message
		$message =& $this->build("p4a_message", "message");
		$message->setWidth("300");


		$sh_campi =& $this->build("p4a_sheet", "sh_campi");
	    $this->sh_campi->defineGrid(4, 3);
	    $this->sh_campi->setWidth(700);
	
		$this->sh_campi->anchor($this->fld_cerca_forn,1,1,1,2);
		$this->sh_campi->anchor($this->tab_art,1,3,4,1);
		$this->sh_campi->anchor($this->fld_cerca_cat,2,1,1,2);
		$this->sh_campi->anchor($this->bu_cerca,3,1);
		$this->sh_campi->anchor($this->bu_annulla_cerca,3,2);


		//Fieldset con l'elenco dei campi
		$fset=& $this->build("p4a_fieldset", "frame");

 		
 		$fset->anchor($this->sh_campi);
 		$fset->anchor($this->fields->codice);
 		$fset->anchor($this->fields->dalmese);
		$fset->anchor($this->fields->almese);
		$fset->anchor($this->table);
		
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
	
	
	function saveRow()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
		$valid = true;

		foreach($this->mf as $mf){
			$value = $this->fields->$mf->getNewValue();
			if(trim($value) === ""){
				$this->fields->$mf->setStyleProperty("border", "1px solid red");
				$valid = false;
			}
		}

	
		if ($valid) {
			if ( !is_numeric($this->fields->idtable->getNewValue()) )
			{
				// sono in New Row 	
				$maxid = $db->queryOne("SELECT MAX( idtable) FROM " . $p4a->e3g_prefix . "articoloperiodo" );
				if ( is_numeric($maxid) )
					$maxid++;
				else 
					$maxid = 1;
				$this->fields->idtable->setNewValue( $maxid );
			}
		
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

	function tabart_click()
	{
		$this->fields->codice->setnewValue($this->tab_art->data->fields->codice->getNewValue());
			
	}	

	function bu_cerca_click()
	{
		$p4a =& p4a::singleton();

		$strwhere = " 1=1 ";
		if ($this->fld_cerca_forn->getNewValue() != "00") 
		{
			$strwhere .= " AND centrale = '".$this->fld_cerca_forn->getNewValue()."'";	
		}

		if ($this->fld_cerca_cat->getNewValue() != "00") 
		{
			$strwhere .= " AND catmerce = '".$this->fld_cerca_cat->getNewValue()."'";	
		}
		
		$this->ds_art->setWhere($strwhere);
		$this->ds_art->firstRow();
		
	}

	
	function bu_annulla_cerca_click()
	{
		$this->fld_cerca_forn->setNewValue("00");
		$this->fld_cerca_cat->setNewValue("00");
		
		$this->ds_art->setWhere("1=1");
		$this->ds_art->firstRow();
	}


}
?>