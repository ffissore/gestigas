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


class esporta_dati extends P4A_Mask
{
	function esporta_dati()
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
		$this->setTitle('Esportazione Dati Archivio');
 
				// ------------------------------------------------------------- Toolbar
		$this->build("p4a_quit_toolbar", "toolbar");
		//$this->toolbar->setMask($this);

		
		// --------------------------------------------------- Label spiegazione
		$this->build("p4a_label", "lbl_campi");
		$this->lbl_campi->setValue("");
		$this->lbl_campi->setWidth("600");
		
		$this->build("p4a_db_source", "ds_campi");
		$this->ds_campi->setTable($p4a->e3g_prefix."articoli");
		$this->ds_campi->setPk("idarticolo");		
		$this->ds_campi->addOrder("codice");		
		$this->ds_campi->load();
		$this->ds_campi->firstRow();
		

		$values = array(); 
		while ($field =& $this->ds_campi->fields->nextItem()) {
			$values[] = array("col" => $field->getName(), "desc" => $field->getName());
		}
		
		$array_source =& $this->build("p4a_array_source", "array_source"); 
		$array_source->load($values); 
		$array_source->setPk("col"); 
		
		$this->build("p4a_field", "fld_campi");
		$this->fld_campi->setLabel('Campi');
		$this->fld_campi->label->setWidth(100);
		$this->fld_campi->setWidth(300);
		$this->fld_campi->setType('select');
		$this->fld_campi->setSourceValueField('col');
		$this->fld_campi->setSourceDescriptionField('desc');
		$this->fld_campi->setSource($array_source);
		

		$values_tab = array(); 
		$values_tab[] = array("val" => "ART", "desc" => "Articoli");
		$values_tab[] = array("val" => "CLI", "desc" => "Clienti");
		$values_tab[] = array("val" => "FOR", "desc" => "Fornitori");
		
		$array_tabelle =& $this->build("p4a_array_source", "array_tabelle"); 
		$array_tabelle->load($values_tab); 
		$array_tabelle->setPk("val"); 
		
		$this->build("p4a_field", "fld_tabelle");
		$this->fld_tabelle->setLabel('Tabella');
		$this->fld_tabelle->label->setWidth(100);
		$this->fld_tabelle->setWidth(300);
		$this->fld_tabelle->setType('select');
		$this->fld_tabelle->setSourceValueField('val');
		$this->fld_tabelle->setSourceDescriptionField('desc');
		$this->fld_tabelle->setSource($array_tabelle);
		$this->fld_tabelle->addAction("OnChange");
		$this->intercept($this->fld_tabelle, "onChange","fld_tabelle_change");		


		$this->build("p4a_button", "stampacsv");
		$this->stampacsv->setLabel("Esporta dati (CSV)");
		$this->stampacsv->setIcon( "spreadsheet" );
		$this->stampacsv->setWidth( "150" );
		$this->stampacsv->addAction("onClick");
		$this->intercept($this->stampacsv, "onClick", "stampacsv_click");

		$this->build("p4a_button", "bu_aggiungi");
		$this->bu_aggiungi->setLabel("Aggiungi Campo");
		$this->bu_aggiungi->addAction("onClick");
		$this->intercept($this->bu_aggiungi, "onClick", "aggiungiclick");

		$this->build("p4a_button", "bu_rimuovi");
		$this->bu_rimuovi->setLabel("Rimuovi Campo");
		$this->bu_rimuovi->addAction("onClick");
		$this->intercept($this->bu_rimuovi, "onClick", "rimuoviclick");

		$sh_campi =& $this->build("p4a_sheet", "sh_campi");
        $this->sh_campi->defineGrid(4, 2);
        $this->sh_campi->setWidth(700);

		$this->sh_campi->anchor($this->fld_tabelle,1,1);
		$this->sh_campi->anchor($this->fld_campi,2,1);
		$this->sh_campi->anchor($this->bu_aggiungi,2,2);
		$this->sh_campi->anchor($this->bu_rimuovi,3,2);
		$this->sh_campi->anchor($this->stampacsv,4,1);

	
		// --------------------------------------------------------- Ancoraggio		
		$frm=& $this->build("p4a_frame", "frm");
		$frm->setWidth(730);
		$frm->anchor($this->sh_campi);
		$frm->anchor($this->lbl_campi);
				
		e3g_scrivi_footer( $this, $frm );

  		// Display
		$this->display("main", $frm);
		$this->display("menu", $p4a->menu);
		$this->display("top", $this->toolbar);
	}


	function main()
	{
		parent::main();
	}

	
			

	function fld_tabelle_change()
	{
		$p4a =& p4a::singleton();
		
		
		$this->build("p4a_db_source", "ds_campi");
		
		switch ( $this->fld_tabelle->getNewValue()) {
			case "ART":
				$this->ds_campi->setTable($p4a->e3g_prefix."articoli");
				$this->ds_campi->setPk("idarticolo");		
				break;
			  
			case "CLI":  
				$this->ds_campi->setTable($p4a->e3g_prefix."anagrafiche");
				$this->ds_campi->setPk("idanag");		
				$this->ds_campi->setWhere("tipocfa='C'");		
				break;

			case "FOR":  
				$this->ds_campi->setTable($p4a->e3g_prefix."anagrafiche");
				$this->ds_campi->setPk("idanag");		
				$this->ds_campi->setWhere("tipocfa='F'");		
				break;
		}
		
		
		$this->ds_campi->load();
		$this->ds_campi->firstRow();
		
		$values = array(); 
		while ($field =& $this->ds_campi->fields->nextItem()) {
			$values[] = array("col" => $field->getName(), "desc" => $field->getName());
		}
		
		$array_source =& $this->build("p4a_array_source", "array_source"); 
		$array_source->load($values); 
		$array_source->setPk("col"); 
		
		$this->fld_campi->setSource($array_source);
		
		$this->lbl_campi->setValue("");
		
	
	}

	function aggiungiclick()
	{
		$tmp = $this->lbl_campi->getValue();
		$this->lbl_campi->setValue($tmp.$this->fld_campi->getNewValue().", ");
		 	
	}
	
	function rimuoviclick()
	{
		$tmp = str_replace($this->fld_campi->getNewValue().", ", "", $this->lbl_campi->getValue());
		$this->lbl_campi->setValue($tmp);
	}

	function stampacsv_click()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();

		$campi = substr($this->lbl_campi->getValue(), 0, strlen($this->lbl_campi->getValue())-2); 
		$this->build("p4a_db_source", "ds_art");
		
		switch ( $this->fld_tabelle->getNewValue()) {
			case "ART":
				$this->ds_art->setTable($p4a->e3g_prefix."articoli");
				$this->ds_art->setPk("idarticolo");		
				break;
			  
			case "CLI":  
				$this->ds_art->setTable($p4a->e3g_prefix."anagrafiche");
				$this->ds_art->setPk("idanag");		
				$this->ds_art->setWhere("tipocfa='C'");		
				break;

			case "FOR":  
				$this->ds_art->setTable($p4a->e3g_prefix."anagrafiche");
				$this->ds_art->setPk("idanag");		
				$this->ds_art->setWhere("tipocfa='F'");		
				break;
		}
		
		
		$this->ds_art->setSelect($campi);
		$this->ds_art->load();
		$this->ds_art->firstRow();

		$colonne = explode(", ", $this->lbl_campi->getValue());
		
        $nome_file = P4A_Get_Valid_File_Name( "Dati_" . $p4a->e3g_azienda_rag_soc . ".csv" );

    	$this->ds_art->exportToCsv( $nome_file, ";", $colonne );
	}

	
}


?>
