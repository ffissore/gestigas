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

class documenti_righe_estratte extends P4A_Mask
{
	// Variabile per definire il tipo Anagrafica Cliente / Fornitore
	var $codtipodoc ='';
	var $numdoc = 0;
	var $strdata = '';
	var $codclifor = '';
	var $nomeclifor = '';
	var $iddoc = 0;
	var $nuovariga = 0;
	var $vengodaricerca = 0;			

	function documenti_righe_estratte ()
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();



		// TIPO DOCUMENTO
		$tipodoc =& $this->build("p4a_label", "tipodoc");
		$tipodoc->setWidth("200");
		//$tipodoc->setProperty('align','left');


		// Campo Cli For
		$clifor =& $this->build("p4a_label", "clifor");
		$this->nomeclifor = $db->queryOne("SELECT descrizione FROM ".$p4a->e3g_prefix."anagrafiche WHERE codice='".$this->codclifor."'");
		$clifor->setWidth("200");
		//$clifor->setProperty('align','left');

		// Campo data Documento
		$datadoc =& $this->build("p4a_label", "datadoc");
		$datadoc->setWidth("200");
		//$datadoc->setProperty('align','left');

		// Numero Documento
		$numerodoc =& $this->build("p4a_label", "numerodoc");
		$numerodoc->setWidth("200");
		//$numerodoc->setProperty('align','left');



		
		
		$this->build("p4a_db_source", "ds_docr");
		$this->ds_docr->setSelect("idriga, codutente, codice, descrizione, quantita, estratto, codtipodoc, numdocum, data");
		
		$this->ds_docr->setTable($p4a->e3g_prefix."docr");
				
		$this->ds_docr->setPk("idriga");
		if ( E3G_TIPO_GESTIONE == 'G' )
		{
			$this->ds_docr->setWhere("estratto='S' AND visibile = 'N'");			
		}	
		else
		{
			$this->ds_docr->setWhere("estratto='S'");			
		}
		
		
		$this->ds_docr->addOrder("data","DESC");
		$this->ds_docr->addOrder("codtipodoc");
		$this->ds_docr->addOrder("numdocum","DESC");
		$this->ds_docr->addOrder("codutente");
		$this->ds_docr->load();
		$this->setSource($this->ds_docr);
		$this->ds_docr->firstRow();
		
		

		//Aggiungo la Tabella Righe
		$tab_row =& $this->build("p4a_table", "tab_row");
		$tab_row->setWidth(730);
		$tab_row->setSource($this->ds_docr);
		$tab_row->setVisibleCols(array("codutente", "descrizione","quantita","codtipodoc","numdocum","data"));
		
		$tab_row->showNavigationBar();

		// array SI NO
		//$values = array(); 
		//$values[] = array("id" => "S", "desc" => "Si");
		//$values[] = array("id" => "N", "desc" => "No");

		//$array_source =& $this->build("p4a_array_source", "array_source"); 
		//$array_source->load($values); 
		//$array_source->setPk("id"); 
		
		$this->fields->estratto->setLabel('Estratto');
		//$this->tab_row->cols->estratto->setLabel('Estratto');
		//$this->tab_row->cols->estratto->setWidth(100);


		// Desc Utente
		$this->build("p4a_db_source", "ds_ana");
		$this->ds_ana->setTable($p4a->e3g_prefix."anagrafiche");
		$this->ds_ana->setPk("codice");
		$this->ds_ana->load();

		// TIPO DOCUMENTO
		$this->build("p4a_db_source", "ds_tipo");
		$this->ds_tipo->setTable($p4a->e3g_prefix."doctipidoc");
		$this->ds_tipo->setPk("codice");
		$this->ds_tipo->setWhere("codice<>'00000'");
		$this->ds_tipo->load();

		$this->tab_row->cols->codutente->setLabel('Utente');
		$this->tab_row->cols->codutente->setWidth(150);
		$this->tab_row->cols->codutente->setType('select');
		$this->tab_row->cols->codutente->setSourceValueField('codice');
		$this->tab_row->cols->codutente->setSourceDescriptionField('descrizione');
		$this->tab_row->cols->codutente->setSource($this->ds_ana);

		$this->tab_row->cols->codtipodoc->setLabel('Doc.');
		$this->tab_row->cols->codtipodoc->setWidth(150);
		$this->tab_row->cols->codtipodoc->setType('select');
		$this->tab_row->cols->codtipodoc->setSourceValueField('codice');
		$this->tab_row->cols->codtipodoc->setSourceDescriptionField('descrizione');
		$this->tab_row->cols->codtipodoc->setSource($this->ds_tipo);
		
		
		
		
		$this->tab_row->cols->descrizione->setLabel('Descrizione');
		$this->tab_row->cols->descrizione->setWidth(200);
		
		$this->tab_row->cols->numdocum->setLabel('Num.');
		$this->tab_row->cols->numdocum->setWidth(60);
		$this->tab_row->cols->quantita->setLabel('Qta');
		$this->tab_row->cols->quantita->setWidth(60);
		$this->tab_row->cols->data->setLabel('Del');
		$this->tab_row->cols->data->setWidth(110);
		
		
		//$this->fields->estratto->setType('select');
		//$this->fields->estratto->setSourceValueField('id');
		//$this->fields->estratto->setSourceDescriptionField('desc');
		//$this->fields->estratto->setSource($array_source);



		
		//Aggiungo alla maschera una nuova standard toolbar.
		$this->build("p4a_standard_toolbar", "toolbar");
		$this->toolbar->setMask($this);
		$this->toolbar->buttons->new->setInvisible();
		$this->toolbar->buttons->delete->setInvisible();
		$this->toolbar->buttons->cancel->setInvisible();
		
		$this->toolbar->buttons->save->setAccessKey("s");


		// Message
		$message =& $this->build("p4a_message", "message");
		$message->setWidth("300");

		//Setto il titolo della maschera
		$this->setTitle('Documenti Estratti');


		$sh_doc =& $this->build("p4a_sheet", "sh_doc");
        $this->sh_doc->defineGrid(2, 2);

		



        // Ancoro uno Sheet per i campi del corpo
        $sh_campi =& $this->build("p4a_sheet", "sh_campi");
		$this->sh_campi->setWidth(800);

        // applico la grid allo sheet campi
        $this->sh_campi->defineGrid(1, 3);
        $this->sh_campi->anchor($this->fields->estratto,2,1);




		$frm=& $this->build("p4a_frame", "frm");
		$frm->setWidth(730);

		$frm->anchor($message);
		$frm->anchor($this->tab_row);
		$frm->anchor($sh_campi);

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
		
		// se ho inserito S o N allora salvo 		
		if (strtoupper($this->fields->estratto->getNewValue()) == "S" || strtoupper($this->fields->estratto->getNewValue()) == "N" )
		{
			parent::saveRow();
		}	
		
	}


	function main()
	{		
		parent::main();			
	}




	
		
	
}	

?>