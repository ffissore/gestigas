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

class doctipidoc extends P4A_Mask
{
	var $lblwidth = 100;
	
	function &doctipidoc()
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
		//Sorgente dati principale
		// data sources
		$this->build("p4a_db_source", "ds_doctipi");
		$this->ds_doctipi->setTable($p4a->e3g_prefix."doctipidoc");
		$this->ds_doctipi->setPk("codice");
		$this->ds_doctipi->setWhere("codice <> '00000'");
		$this->ds_doctipi->load();
		$this->setSource($this->ds_doctipi);
		$this->ds_doctipi->firstRow();


		//while ($field =& $this->fields->nextItem()) {

		//	$field->label->setWidth(200); 
		//}

		$table =& $this->build("p4a_table", "table");
		$table->setWidth(730);
		$table->setSource($this->ds_doctipi);
		$table->setVisibleCols(array("codice", "descrizione","tipoanagrafica"));
			
		$this->table->cols->codice->setLabel("Codice");
		$this->table->cols->descrizione->setLabel("Descrizione");
		$this->table->cols->tipoanagrafica->setLabel("Tipo Anagrafica");
		
		// Aggiungo i campi della maschera
		//Campo codice
		$this->fields->codice->setLabel('codice');
		$this->fields->codice->label->setWidth($this->lblwidth); 
		
		//Campo descrizione
		$this->fields->descrizione->setLabel('descrizione');
		$this->fields->descrizione->setProperty('size','30');
		$this->fields->descrizione->label->setWidth($this->lblwidth);
		
		//desbreve
		$this->fields->desbreve->setLabel('descrizione Breve');
		$this->fields->desbreve->label->setWidth($this->lblwidth);

		$this->fields->codaltridoc->setLabel('Estrai dal Documento');
		$this->fields->codaltridoc->label->setWidth($this->lblwidth);
		
			
		//nomeReport1
		$this->fields->nomereport1->setLabel('nome Report');
		$this->fields->nomereport1->label->setWidth($this->lblwidth);
		
		
		//tipo Fat Nac
		$this->build("p4a_db_source", "ds_tipofn");
		$this->ds_tipofn->setTable($p4a->e3g_prefix."tipofatnac");
		$this->ds_tipofn->setPk("codice");
		$this->ds_tipofn->load();

		$this->fields->tipofn->setLabel('Fattura/Nota Credito');
		$this->fields->tipofn->label->setWidth($this->lblwidth);
		$this->fields->tipofn->setType('select');
		$this->fields->tipofn->setSourceValueField('codice');
		$this->fields->tipofn->setSourceDescriptionField('descrizione');
		$this->fields->tipofn->setSource($this->ds_tipofn);
		
		
		//codcaumag
		$this->build("p4a_db_source", "ds_caumovmag");
		$this->ds_caumovmag->setTable($p4a->e3g_prefix."movmagcausali");
		$this->ds_caumovmag->setPk("codice");
		$this->ds_caumovmag->load();

		$this->fields->codcaumag->setLabel('Causale Mov. Magaz.');
		$this->fields->codcaumag->label->setWidth($this->lblwidth);
		$this->fields->codcaumag->label->setWidth($this->lblwidth);
		$this->fields->codcaumag->setType('select');
		$this->fields->codcaumag->setSourceValueField('codice');
		$this->fields->codcaumag->setSourceDescriptionField('descrizione');
		$this->fields->codcaumag->setSource($this->ds_caumovmag);


		//tipoanagrafica
		$this->build("p4a_db_source", "ds_tipoanag");
		$this->ds_tipoanag->setTable($p4a->e3g_prefix."tipoanagrafiche");
		$this->ds_tipoanag->setPk("codice");
		$this->ds_tipoanag->load();

		$this->fields->tipoanagrafica->setLabel('tipo Anagrafica');
		$this->fields->tipoanagrafica->label->setWidth($this->lblwidth);
		$this->fields->tipoanagrafica->setType('select');
		$this->fields->tipoanagrafica->setSourceValueField('codice');
		$this->fields->tipoanagrafica->setSourceDescriptionField('descrizione');
		$this->fields->tipoanagrafica->setSource($this->ds_tipoanag);


		//Gen Auto Numerazione (genera la numerazione automatica dei documenti)
		//$this->build("p4a_db_source", "ds_sino");
		//$this->ds_sino->setTable( "_si_no" );
		//$this->ds_sino->setPK("codice");
		//$this->ds_sino->load();

		$values = array(); 
		$values[] = array("id" => "S", "desc" => "Si");
		$values[] = array("id" => "N", "desc" => "No");

		$array_source =& $this->build("p4a_array_source", "array_source"); 
		$array_source->load($values); 
		$array_source->setPk("id"); 

		
		$this->fields->genautonum->setLabel('Auto Numerazione');
		$this->fields->genautonum->label->setWidth($this->lblwidth);
		$this->fields->genautonum->setType('select');
		$this->fields->genautonum->setSourceValueField('id');
		$this->fields->genautonum->setSourceDescriptionField('desc');
		$this->fields->genautonum->setSource($array_source);

		$this->fields->genprestito->setLabel('Genera Prestito');
		$this->fields->genprestito->label->setWidth($this->lblwidth);
		$this->fields->genprestito->setType('select');
		$this->fields->genprestito->setSourceValueField('id');
		$this->fields->genprestito->setSourceDescriptionField('desc');
		$this->fields->genprestito->setSource($array_source);


		//genmovcon
		$this->fields->genmovcon->setLabel('Genera Mov. Contabili');
		$this->fields->genmovcon->label->setWidth($this->lblwidth);
		$this->fields->genmovcon->setType('select');
		$this->fields->genmovcon->setSourceValueField('id');
		$this->fields->genmovcon->setSourceDescriptionField('desc');
		$this->fields->genmovcon->setSource($array_source);

		//genmovmag
		$this->fields->genmovmag->setLabel('Genera Mov. Magazzino');
		$this->fields->genmovmag->label->setWidth($this->lblwidth);
		$this->fields->genmovmag->setType('select');
		$this->fields->genmovmag->setSourceValueField('id');
		$this->fields->genmovmag->setSourceDescriptionField('desc');
		$this->fields->genmovmag->setSource($array_source);
		
		//codregdoc
		$this->build("p4a_db_source", "ds_codreg");
		$this->ds_codreg->setTable($p4a->e3g_prefix."docregistri");
		$this->ds_codreg->setPK("codice");
		$this->ds_codreg->load();

		$this->fields->codregdoc->setLabel('Registro Documenti');
		$this->fields->codregdoc->label->setWidth($this->lblwidth);
		$this->fields->codregdoc->setType('select');
		$this->fields->codregdoc->setSourceValueField('codice');
		$this->fields->codregdoc->setSourceDescriptionField('descrizione');
		$this->fields->codregdoc->setSource($this->ds_codreg);


		//printetichette
		$this->fields->printetichette->setLabel('Genera Stampa Etichette');
		$this->fields->printetichette->label->setWidth($this->lblwidth);
		$this->fields->printetichette->setType('checkbox');

		//gesscontrino
		$this->fields->gesscontrino->setLabel('Gestione Scontrino');
		$this->fields->gesscontrino->label->setWidth($this->lblwidth);
		$this->fields->gesscontrino->setType('select');
		$this->fields->gesscontrino->setSourceValueField('id');
		$this->fields->gesscontrino->setSourceDescriptionField('desc');
		$this->fields->gesscontrino->setSource($array_source);

		
		//filtroartforn
		$this->fields->filtroartforn->setLabel('Filtro Articoli Fornitore');
		$this->fields->filtroartforn->label->setWidth($this->lblwidth);
		$this->fields->filtroartforn->setType('checkbox');

		//Dati Contabili 
		//Contro Partita iva	
		$this->fields->codcontropiva->setLabel('conto iva');
		$this->fields->codcontropiva->label->setWidth($this->lblwidth);
		
		//Contro Partita Spese Varie
		$this->fields->codcontropspvarie->setLabel('conto Sp. Varie');
		$this->fields->codcontropspvarie->label->setWidth($this->lblwidth);
				
		//Contro Partita Spese Trasporto
		$this->fields->codcontropsptrasp->setLabel('conto Sp. Trasp.');
		$this->fields->codcontropsptrasp->label->setWidth($this->lblwidth);
			

		//Setto il titolo della maschera
		$this->setTitle('Tipi Documento');

		// Toolbar
		$this->build("p4a_standard_toolbar", "toolbar");
		$this->toolbar->setMask($this);

		// Message
		$message =& $this->build("p4a_message", "message");
		$message->setWidth("300");


		// Aggiungo gli Sheet 
        $sh1 =& $this->build("p4a_sheet", "sh1");
		$this->sh1->setWidth(730);
		$this->sh1->setLabel('Dati Documento');
		
		$sh2 =& $this->build("p4a_sheet", "sh2");
		$this->sh2->setWidth(730);
		$this->sh2->setLabel('Dati Contabili');

		// Definisco la Grid Principale per la Maschera (righe,colonne)
		$this->sh1->defineGrid(20, 2);
		$this->sh2->defineGrid(20, 2);
		
        
		// ancoro i campi fields
		$this->sh1->anchor($this->fields->codice,1,1);
		$this->sh1->anchor($this->fields->descrizione,2,1);
		$this->sh1->anchor($this->fields->desbreve,3,1);
		$this->sh1->anchor($this->fields->tipofn,3,2);
		$this->sh1->anchor($this->fields->tipoanagrafica,4,1);
		$this->sh1->anchor($this->fields->codaltridoc,6,1);
		$this->sh1->anchor($this->fields->genmovmag,8,1);
		$this->sh1->anchor($this->fields->codcaumag,8,2);
		$this->sh1->anchor($this->fields->genmovcon,10,1);
		$this->sh1->anchor($this->fields->genprestito,12,1);
		$this->sh1->anchor($this->fields->genautonum,12,2);

		$this->sh1->anchor($this->fields->codregdoc,14,1);
		$this->sh1->anchor($this->fields->printetichette,15,1);
		$this->sh1->anchor($this->fields->gesscontrino,16,1);
		$this->sh1->anchor($this->fields->filtroartforn,17,1);
		
		$this->sh1->anchor($this->fields->nomeReport1,19,1);
				
		
		$this->sh2->anchor($this->fields->codcontropiva,1,1);
		$this->sh2->anchor($this->fields->codcontropspvarie,2,1);
		$this->sh2->anchor($this->fields->codcontropsptrasp,3,1);

		// Frame
		$frm=& $this->build("p4a_frame", "frm");
		$frm->setWidth(730);

		$frm->anchor($message);
        $frm->anchor($this->tutti);
		$frm->anchor($this->flttipo);
		$frm->anchor($this->table); 
		$frm->anchor($this->sh1); 
		$frm->anchor($this->sh2);
		
		e3g_scrivi_footer( $this, $frm );

  		// Display
		$this->display("main", $frm);
		$this->display("menu", $p4a->menu);
		$this->display("top", $this->toolbar);
	}

	function main()
	{
		parent::main();

		//foreach($this->mf as $mf){
		//	$this->fields->$mf->unsetStyleProperty("border");
		//}


	}



	function dopo_record_cambiato()
	{

	}


	function prima_di_salvare()
	{

	}






}
?>