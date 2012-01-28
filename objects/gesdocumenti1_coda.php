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


require_once( dirname(__FILE__) . '/../libraries/e3g_doc_routines.php' );


class gesdocumenti1_coda	extends P4A_Mask
{
	// Variabile per definire il tipo Anagrafica Cliente / Fornitore
	var $codtipodoc ='';
	var $numdoc = 0;
	var $date = '';
	var $codclifor = '';
	var $iddoc = 0;
	var $nuovariga = 0;

	
	function gesdocumenti1_coda()
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
		
		//Setto il titolo della maschera
		$this->setTitle('Documenti: Coda');


		// Numero Documento
		$numerodoc=& $this->build("p4a_label", "numerodoc");
		$numerodoc->setValue('Num. Docum.');
		$numerodoc->setWidth("200");

		// tipo Documento
		$tipodoc=& $this->build("p4a_label", "tipodoc");
		$tipodoc->setValue('tipo Doc.');
		$tipodoc->setWidth("200");

		
		
		$this->carica_doct();

		
		//Sorgente dati principale
		// data sources
		$this->build("p4a_db_source", "ds_doct");
		$this->ds_doct->setTable($p4a->e3g_prefix."doct");
		$this->ds_doct->setPk("iddoc");
		$this->ds_doct->setWhere("iddoc=".$this->iddoc);
		$this->ds_doct->load();

		$this->setSource($this->ds_doct);
		$this->ds_doct->firstRow();

		// Fields properties
		$fields =& $this->fields;

		// Campi Obbligatori Fields
	    $this->mf = array("numdocum");
		foreach($this->mf as $mf){
			$fields->$mf->label->setFontWeight("bold");
		}



		// TIPO DOCUMENTO
		$this->build("p4a_db_source", "ds_tipo");
		$this->ds_tipo->setTable($p4a->e3g_prefix."doctipidoc");
		$this->ds_tipo->setPk("codice");
		$this->ds_tipo->setWhere("codice <> '00000'");
		$this->ds_tipo->load();


		$this->fields->codtipodoc->setLabel('tipo Documento');
		$this->fields->codtipodoc->setWidth(200);
		$this->fields->codtipodoc->setType('select');
		$this->fields->codtipodoc->setSourceValueField('codice');
		$this->fields->codtipodoc->setSourceDescriptionField('descrizione');
		$this->fields->codtipodoc->setSource($this->ds_tipo);
		//$this->fields->codtipodoc->addAction("OnChange");
		//$this->intercept($this->fields->codtipodoc, "onChange","seleztipo_click");
		// FILTRA TIPO DOC 
		$this->build("p4a_button", "flttipo");
		$this->flttipo->setLabel("Filtra questo tipo Doc.");
		$this->flttipo->addAction("onClick");
		$this->intercept($this->flttipo, "onClick", "flttipo_click");
		$this->flttipo->setWidth("150");

		$this->build("p4a_button", "autonum");
		$this->autonum->setLabel("Auto Num. Doc.");
		$this->autonum->addAction("onClick");
		$this->intercept($this->autonum, "onClick", "autonum_click");

		$this->fields->imponibile->data_field->setType("decimal");
		$this->fields->imposta->data_field->setType("decimal");
		$this->fields->totdoc->data_field->setType("decimal");
		$this->fields->spesetrasporto->data_field->setType("decimal");
		$this->fields->spesevarie->data_field->setType("decimal");

		// cerco il tipo Anagrafica
		//$tipocf = $db->queryOne("SELECT tipoanagrafica FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$this->fields->codtipodoc->getNewValue()."'");
		$tipocf = $db->queryOne("SELECT tipoanagrafica FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$this->codtipodoc."'");
			
		
		// tipo Fattura / Nota di Accredito
		$this->fields->tipofn->setLabel('tipo Fatt./Nota Accr.');

		// Campo data Documento
		$this->fields->data->setLabel('Data documento');
		$this->fields->data->setType('date');
		//$this->fields->data->setWidth(200);


		// SELEZIONA TUTTE LE RIGHE
		$tutterighe=& $this->build("p4a_field", "tutterighe");
		$tutterighe->setLabel('Tutte le Righe');
		$tutterighe->label->setWidth(140);
		$tutterighe->setType("checkbox");
		$tutterighe->setWidth("200");

		// Famiglia per cui estrarre il documento 
		$codute=& $this->build("p4a_field", "codute");
		$codute->setLabel('Utente');
		$codute->label->setWidth(140);
		$codute->setWidth("200");

		// ESTRAI DOC 
		$this->build("p4a_button", "estrai");
		$this->estrai->setLabel("Estrai");
		$this->estrai->addAction("onClick");
		$this->intercept($this->estrai, "onClick", "estrai_click");
		
		$this->build("p4a_db_source", "ds_anagc");
		$this->ds_anagc->setTable($p4a->e3g_prefix."anagrafiche");
		$query = "tipocfa='C'";
		$this->ds_anagc->setWhere($query);
		$this->ds_anagc->setPk("codice");
		$this->ds_anagc->load();
		
		$this->codute->setType('select');
		$this->codute->setSourceValueField('codice');
		$this->codute->setSourceDescriptionField('descrizione');
		$this->codute->setSource($this->ds_anagc);
		
		
		// Stampa 
		$this->build("p4a_button", "stampa");
		$this->stampa->setLabel("Esporta come PDF...");
		$this->stampa->setIcon("pdf");
		$this->stampa->addAction("onClick");
		$this->stampa->setAccessKey("p");
		$this->intercept($this->stampa, "onClick", "stampa_click");
		$this->stampa->setWidth("200");


		// Numero Effettivo Documento
		$this->fields->numdoceff->setLabel('Num. Effettivo Doc.');


		// Cliente / Fornitore
		$this->build("p4a_db_source", "ds_anag");
		$this->ds_anag->setTable($p4a->e3g_prefix."anagrafiche");
		
		$query = "tipocfa='".$tipocf."'";
		$this->ds_anag->setWhere($query);
		$this->ds_anag->setPk("idanag");
		$this->ds_anag->load();



		if ($tipocf=='C')
		{
			$this->fields->codclifor->setLabel('Cliente');
		}
		else
		{
			if ($tipocf=='F')
			{
				$this->fields->codclifor->setLabel('Fornitore');
			}
			else
			{
				$this->fields->codclifor->setLabel('Cliente/Fornitore');
			}
		}



		$this->fields->codclifor->setType('select');
		$this->fields->codclifor->setSourceValueField('codice');
		$this->fields->codclifor->setSourceDescriptionField('descrizione');
		$this->fields->codclifor->setSource($this->ds_anag);

		$this->gestione_campi();

		//////////////////////////////////////////////////
		//CODA DEL DOCUMENTO

		$this->fields->spesevarie->setLabel('Spese Varie');
		$this->fields->spesetrasporto->setLabel('Spese Trasporto');
		
		$this->fields->imposta->setLabel('imposta');
		$this->fields->imponibile->setLabel('imponibile');
		$this->fields->totdoc->setLabel('Tot. Docum.');

		$this->fields->imposta->setWidth(60);
		$this->fields->imponibile->setWidth(60);
		$this->fields->totdoc->setWidth(60);
		
		
		$this->fields->destalter1->setLabel('Dest. Alter. 1');
		$this->fields->destalter2->setLabel('Dest. Alter. 2');
		$this->fields->note->setLabel('note');
		$this->fields->docchiuso->setLabel('Doc. Chiuso');
		$this->fields->docchiuso->setType('checkbox');


		// tipo Pagamento
		$this->build("p4a_db_source", "ds_pag");
		$this->ds_pag->setTable($p4a->e3g_prefix."pagamenti");
		$this->ds_pag->setPk("codice");
		$this->ds_pag->addOrder("codice");
		$this->ds_pag->load();

		$this->fields->codtipopag->setLabel('tipo Pagamento');
		$this->fields->codtipopag->setType('select');
		$this->fields->codtipopag->setSourceValueField('codice');
		$this->fields->codtipopag->setSourceDescriptionField('descrizione');
		$this->fields->codtipopag->setSource($this->ds_pag);


		// Toolbar
		$this->build("p4a_standard_toolbar", "toolbar");
		$this->toolbar->setMask($this);
		
		$this->toolbar->buttons->save->setAccessKey("s");

		
		$this->toolbar->buttons->new->setInvisible();
		$this->toolbar->buttons->delete->setInvisible();
		$this->toolbar->buttons->cancel->setInvisible();
    	            
					
		// Message
		$message =& $this->build("p4a_message", "message");
		$message->setWidth("300");



        $sh_campi =& $this->build("p4a_sheet", "sh_campi");

        // applico la grid allo sheet campi
        $this->sh_campi->defineGrid(6, 2);
        $this->sh_campi->anchorText("",2,1);
        $this->sh_campi->anchorText("",2,2);
        $this->sh_campi->anchor($this->tipodoc,3,1);
        $this->sh_campi->anchor($this->numerodoc,4,1);
        $this->sh_campi->anchorText("",5,1);
        $this->sh_campi->anchorText("",5,2);    
        $this->sh_campi->anchor($this->stampa,6,2);
        
        for($i = 1; $i<=6; $i++){
			
			$this->sh_campi->grid[$i][1]->setWidth('365');
			$this->sh_campi->grid[$i][2]->setWidth('365');
			
			if ($i == 2 || $i == 5 )
			{
				$this->sh_campi->grid[$i][1]->setHeight('20');
				$this->sh_campi->grid[$i][2]->setHeight('20');
			}
			
			$this->sh_campi->grid[$i][2]->setProperty('Align','Left');
			
		}
        

		$fset2=& $this->build("p4a_fieldset", "frame2");
        $fset2->setWidth(E3G_FIELDSET_DATI_WIDTH);
		$fset2->anchor($this->fields->data);
		$fset2->anchor($this->fields->codclifor);
		$fset2->anchor($this->fields->numdoceff);
		$fset2->anchor($this->codclifor);

		// FRAME ESTRAZIONE 
		$fset3=& $this->build("p4a_fieldset", "fset3");
        $fset3->setWidth(E3G_FIELDSET_DATI_WIDTH);
		$fset3->anchor($this->estrai);
		$fset3->anchorLeft($this->codute,50);
		$fset3->anchorLeft($this->tutterighe,50);



		//$shg1->sheets['sh2']->anchor($this->mask->tab_row);


		$fset4=& $this->build("p4a_fieldset", "frame4");
        $fset4->setWidth(E3G_FIELDSET_DATI_WIDTH);
		$fset4->anchor($this->fields->imposta);
		$fset4->anchorLeft($this->fields->imponibile,100);
		$fset4->anchorLeft($this->fields->totdoc,100);
		
		$fset4->anchor($this->fields->spesetrasporto);
		$fset4->anchorLeft($this->fields->spesevarie,100);
		
		$fset4->anchor($this->fields->destalter1);
		$fset4->anchorLeft($this->fields->destalter2,100);

		$fset4->anchor($this->fields->note);
		$fset4->anchorLeft($this->fields->docchiuso,100);

		$fset4->anchor($this->fields->codtipopag);


		// Frame
		$frm=& $this->build("p4a_frame", "frm");
		$frm->setWidth( E3G_MAIN_FRAME_WIDTH );

		$frm->anchor($message);
		//$frm->anchor($fset1);
		$frm->anchor($this->sh_campi); 
		$frm->anchor($fset2);
		$frm->anchor($fset4);
		
		if ( E3G_TIPO_GESTIONE == 'G' )
			$frm->anchor($fset3);

		e3g_scrivi_footer( $this, $frm );

  		// Display
		$this->display("main", $frm);
		$this->display("menu", $p4a->menu);
		$this->display("top", $this->toolbar);

		$this->tutti_click();
	}

	function main()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();

		$this->carica_doct();
				
		
		$this->ds_doct->setWhere("iddoc=".$this->iddoc);
		$this->ds_doct->setPk("iddoc");
		$this->ds_doct->load();
		$this->ds_doct->firstRow();
		$this->setSource($this->ds_doct);

		$tipocf = $db->queryOne("SELECT tipoanagrafica FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$this->codtipodoc."'");		
		$query = "tipocfa='".$tipocf."'";
		$this->ds_anag->setWhere($query);
		$this->ds_anag->setPk("idanag");
		$this->ds_anag->load();

		
		parent::main();

		foreach($this->mf as $mf){
			$this->fields->$mf->unsetStyleProperty("border");
		}
	}


	function autonum_click()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
		$registro = $db->queryOne("SELECT codregdoc FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$this->fields->codtipodoc->getNewValue()."'");
		$ultimo = $db->queryOne("SELECT MAX(seriale) FROM ".$p4a->e3g_prefix."docregistri WHERE codice='".$registro."'");
		
		$this->numerodoc->setNewValue($ultimo++);
	}
	
	
	function carica_click()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();


		//$query = "codtipodoc='".$this->fields->codtipodoc->getNewValue()."' AND anno='".$p4a->e3g_azienda_anno_contabile."' AND numdocum='".$this->numerodoc->getNewValue()."'";
		$query = "codtipodoc='".$this->fields->codtipodoc->getNewValue()."' AND anno='".$p4a->e3g_azienda_anno_contabile."' AND numdocum='".$this->fields->numdocum->getNewValue()."'";


		$this->data->setWhere($query);
		$this->data->firstRow();
		$num_rows = $this->data->getNumRows();
		if (!$num_rows) {
			$this->message->setValue("No results were found");
			$this->data->setWhere(null);
			$this->data->firstRow();
		}
		else
		{
			$this->carica_campi();
			$this->gestione_campi();
		}
	}

	
	function caricacorpo_click()
	{
		$p4a =& p4a::singleton();
		
		// 3) apro la maschera delle righe
		$p4a->openMask('gesdocumenti_righe');
	}

		
	function tutti_click()
	{
		$query = "1=1";
		// devo azzerare il set_where come faccio ???
		$this->data->setWhere($query);
		$this->data->load();
		$this->reloadRow();


		$this->carica_campi();

		$this->gestione_campi();
	}


	function flttipo_click()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();

		$query = "codtipodoc='".$this->fields->codtipodoc->getNewValue()."' AND anno='".$p4a->e3g_azienda_anno_contabile."' ";

		$this->data->setWhere($query);
		$this->data->firstRow();
		$num_rows = $this->data->getNumRows();
		if (!$num_rows) {
			$this->message->setValue("No results were found");
			$this->data->setWhere(null);
			$this->data->firstRow();
		}
		else
		{
			$this->carica_campi();
			$this->gestione_campi();
		}
	}
	
	
	function estrai_click()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
		// trovo il cod tipo doc da generare estrarre 
		$estraidoc = $db->queryOne("SELECT codaltridoc FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$this->fields->codtipodoc->getNewValue()."'");
		
		$clifor = $this->codute->getNewValue();
		$datadoc = date("Y-m-d");
		
		$this->build("p4a_db_source", "ds_docr");
		$this->ds_docr->setTable($p4a->e3g_prefix."docr");
		$this->ds_docr->setPk("iddocr");
		$this->ds_docr->setWhere("1=0");
		$this->ds_docr->load();
		
		$this->build("p4a_db_source", "ds_righe");
		$this->ds_righe->setTable($p4a->e3g_prefix."docr");
		$this->ds_righe->setPk('idriga');
		$this->ds_righe->setPageLimit(10);
		$this->ds_righe->setWhere("visibile='S' AND iddocr=".$this->fields->iddoc->getNewValue());
		$this->ds_righe->addOrder("idriga");
		$this->ds_righe->load();
		
		
		// creo la testata del nuovo doc $estraidoc
		$this->ds_doct->newRow(); 
		$this->ds_doct->fields->data->setNewValue($datadoc); 
		$this->ds_doct->fields->codtipodoc->setNewValue($estraidoc);
		$this->ds_doct->fields->codclifor->setNewValue($clifor);
		$this->ds_doct->fields->anno->setNewValue($p4a->e3g_azienda_anno_contabile);
		// mettere tutti i riferimenti rifestrnum RifEstranno rifiddoc rifestrtipodoc
		$this->ds_doct->fields->rifestrnum->setNewValue($this->fields->numdocum->getNewValue());
		$this->ds_doct->fields->rifestranno->setNewValue($this->fields->anno->getNewValue());
		$this->ds_doct->fields->rifestrtipodoc->setNewValue($this->fields->codtipodoc->getNewValue());
		$this->ds_doct->fields->rifiddoc->setNewValue($this->fields->iddoc->getNewValue());
		
		
		// Recupero ultimo numero documento
		$registro = $db->queryOne("SELECT codregdoc FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$estraidoc."'");
		$ultimo = $db->queryOne("SELECT MAX(seriale) FROM ".$p4a->e3g_prefix."docregistri WHERE codice='".$registro."'");
		$ultimo++;
		$this->ds_doct->fields->numdocum->setNewValue($ultimo);
		$numdoct = $ultimo;
		$query = "UPDATE ".$p4a->e3g_prefix."docregistri SET seriale=".$ultimo." WHERE codice='".$registro."'";
		$db->query($query);
			
		// Recupero ultimo iddoc
		$ultimo = $db->queryOne("SELECT MAX(iddoc) FROM ".$p4a->e3g_prefix."doct");
		$ultimo++;
		$this->ds_doct->fields->iddoc->setNewValue($ultimo);
			
		// questo sotto va in errore perche' ? 
		$this->ds_doct->saveRow(); 
			
		// faccio scorrere le righe del documento
		$this->ds_righe->firstRow();
		$riga = 1 ;
		$idriga = $db->queryOne("SELECT MAX(idriga) FROM ".$p4a->e3g_prefix."docr");
		$idriga++;
		
    	while($riga <= $this->ds_righe->getNumRows())
    	{
    		$this->ds_docr->newRow();
    		if ($this->tutterighe->getNewValue())
    		{
    			// ho selezionato tutte le righe
				$this->ds_docr->fields->codtipodoc->setNewValue($estraidoc);
				$this->ds_docr->fields->numdocum->setNewValue($numdoct);
				$this->ds_docr->fields->anno->setNewValue($p4a->e3g_azienda_anno_contabile);
				
				$this->ds_docr->fields->codice->setNewValue($this->ds_righe->fields->codice->getNewValue());
				$this->ds_docr->fields->descrizione->setNewValue($this->ds_righe->fields->descrizione->getNewValue());
				$this->ds_docr->fields->barcode->setNewValue($this->ds_righe->fields->barcode->getNewValue());
				$this->ds_docr->fields->prezzo->setValue($this->ds_righe->fields->prezzo->getNewValue());
				$this->ds_docr->fields->codiva->setNewValue($this->ds_righe->fields->codiva->getNewValue());
				$this->ds_docr->fields->quantita->setValue($this->ds_righe->fields->quantita->getNewValue());
				$this->ds_docr->fields->codutente->setNewValue($this->ds_righe->fields->codutente->getNewValue());

        		// Recupero ultimo iddocr
        		$this->ds_docr->fields->idriga->setNewValue($idriga);
				$this->ds_docr->fields->nriga->setNewValue($idriga);
				$this->ds_docr->fields->iddocr->setNewValue($ultimo);
			}
    		else 
    		{
    			// NON ho selezionato tutte le righe cerco = $clifor selezionato
				if ($this->ds_righe->fields->codutente->getNewValue() == $clifor) 
        		{		
					$this->ds_docr->fields->codtipodoc->setNewValue($estraidoc);
    				$this->ds_docr->fields->numdocum->setNewValue($numdoct);
    				$this->ds_docr->fields->anno->setNewValue($p4a->e3g_azienda_anno_contabile);
    				
    				$this->ds_docr->fields->codice->setNewValue($this->ds_righe->fields->codice->getNewValue());
    				$this->ds_docr->fields->descrizione->setNewValue($this->ds_righe->fields->descrizione->getNewValue());
    				$this->ds_docr->fields->barcode->setNewValue($this->ds_righe->fields->barcode->getNewValue());
    				$this->ds_docr->fields->prezzo->setValue($this->ds_righe->fields->prezzo->getNewValue());
    				$this->ds_docr->fields->codiva->setNewValue($this->ds_righe->fields->codiva->getNewValue());
    				$this->ds_docr->fields->quantita->setValue($this->ds_righe->fields->quantita->getNewValue());
    				$this->ds_docr->fields->codutente->setNewValue($this->ds_righe->fields->codutente->getNewValue());
    
            		// Recupero ultimo iddocr
            		$this->ds_docr->fields->idriga->setNewValue($idriga);
    				$this->ds_docr->fields->nriga->setNewValue($idriga);
    				$this->ds_docr->fields->iddocr->setNewValue($ultimo);
					
        		}
    		}	
    		
    		$this->ds_docr->saveRow(); 
    		$this->ds_righe->nextRow();
    		$riga++;
			$idriga++;

    	}	
		
		// aggiorno la testata con i totali		
	}


	function salva_click()
	{
		// ???
		
	}


	function avanti_click()
	{
		// ???
	}


	function indietro_click()
	{
		$p4a =& p4a::singleton();

		$query = "codtipodoc='".$this->fields->codtipodoc->getNewValue()."' AND anno='".$p4a->e3g_azienda_anno_contabile."' ";

		$this->data->setWhere($query);
		$this->data->firstRow();

		$this->prevRow();
	}


	function carica_campi()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();

		///////////////////////////////////////////////////////////////
		//ROUTINE PER CARICARE I CAMPI NON ASSOCIATI AL DB_SOURCE   //
		/////////////////////////////////////////////////////////////

		// copio il numero documento nel campo specifico
		$this->numerodoc->setValue("Num. Doc. : ".$this->fields->numdocum->getNewValue());

		// Ricarico il tipo Anagrafica dopo ogni RecordCambiato perch? non so intercettare il click sul OPTION codtipodoc
		//$tipocf = $db->queryOne("SELECT tipoanagrafica FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$this->fields->codtipodoc->getNewValue()."'");
		$tipocf = $db->queryOne("SELECT tipoanagrafica FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$this->codtipodoc."'");


		// Cliente Fornitore

		$query = "tipocfa='".$tipocf."'" ;
		$this->ds_anag->setWhere($query);
		$this->ds_anag->load();
		if ($tipocf=='C')
		{
			$this->fields->codclifor->setLabel('Cliente');
		}
		else
		{
			if ($tipocf=='F')
			{
				$this->fields->codclifor->setLabel('Fornitore');
			}
			else
			{
				$this->fields->codclifor->setLabel('Cliente/Fornitore');
			}
		}
	}


	function gestione_campi()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();

		///////////////////////////////////////////////////////////////
		//ROUTINE PER SETTARE I CAMPI VISIBILI O INVISIBILI 		//
		/////////////////////////////////////////////////////////////

		///////////////////////////////////////////////////////////////////////////////
		// IMPOSTAZIONE CAMPI INVISIBILI
		// Apro la testa e le righe del Documento

		// setto i campi invisibili TESTATA / CODA
		// faccio scorrere i campi della testata
		while ($field =& $this->fields->nextItem()) {
			if ($field->getName() == "codtipodoc" || $field->getName() == "data")
			{
				$field->setVisible(TRUE);
			}
			else
			{
				$isvisible = $db->queryOne("SELECT visible FROM ".$p4a->e3g_prefix."doccampi WHERE codtipodoc='".$this->fields->codtipodoc->getNewValue()."' AND nomecampo='".$field->getName()."' AND testatarighe='T'");
				if ($isvisible=='N')
				{
					$field->setVisible(FALSE);
				}
				else
				{
					if ($isvisible=='S')
					{
						$field->setVisible(TRUE);
					}
				}
			}
			$field->label->setWidth(100);
		}

		$estraidoc = $db->queryOne("SELECT codaltridoc FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$this->fields->codtipodoc->getNewValue()."'");
		if ($estraidoc !="")
		{
			$this->estrai->setVisible();
		}
		else
		{
			$this->estrai->setInvisible();
		}
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

		// ricalcolo tot. di ogni riga e del documento  
		$this->build("p4a_db_source", "ds_righe");
		$this->ds_righe->setTable($p4a->e3g_prefix."docr d");
		$this->ds_righe->setSelect("d.iddocr, d.codice,d.prezzo, d.quantita, d.totale, d.codiva, d.nriga, d.quantita2, d.sconto, d.imponibile, d.imposta, d.totale");
    	$this->ds_righe->setPk("iddocr");
		$this->ds_righe->setWhere("iddocr=".$this->iddoc." AND visibile='S' AND quantita > 0 ");
		$this->ds_righe->addOrder("iddocr");
		$this->ds_righe->addOrder("nriga");
		$this->ds_righe->load();
		$this->ds_righe->firstRow();

		
		$riga = 1 ;
		while($riga<=$this->ds_righe->getNumRows())
		{		
			$iva = $db->queryOne("SELECT iva FROM ".$p4a->e3g_prefix."aliquoteiva WHERE codice='".$this->ds_righe->fields->codiva->getNewValue()."'");
			$quantita = $this->ds_righe->fields->quantita->getValue();
			if ($quantita >0)
	        {
	       	}
	       	else
	       	{
	       		$quantita = 0;
	       	}
			$prezzo = $this->ds_righe->fields->prezzo->getValue();
			if ($prezzo >0)
	        {
	       	}
	       	else
	       	{
	       		$prezzo = 0;
	       	}
			$sconto = $this->ds_righe->fields->sconto->getValue();
			if ($sconto >0)
	        {
	       	}
	       	else
	       	{
	       		$sconto = 0;
	       	}
	        $imponibile = (($quantita * $prezzo) * (1 - $sconto / 100) / (100 + $iva)) * 100;
	        $imposta = (($quantita * $prezzo) * (1 - $sconto / 100)) - $imponibile;
	        $totriga = (($quantita * $prezzo) * (1 - $sconto / 100));
	        $this->ds_righe->fields->imponibile->setValue(round($imponibile, 2));
	        $this->ds_righe->fields->imposta->setValue(round($imposta,2));
	        $this->ds_righe->fields->totale->setValue(round($totriga,2));
			$this->ds_righe->saveRow();		
			$this->ds_righe->nextRow();
			$riga++;
		}
		if ( E3G_TIPO_GESTIONE == 'G' )
		{			
			$this->fields->totdoc->setValue($db->queryOne("SELECT SUM(totale) FROM ".$p4a->e3g_prefix."docr WHERE visibile='S' AND iddocr=".$this->iddoc));
			$this->fields->imposta->setValue($db->queryOne("SELECT SUM(imposta) FROM ".$p4a->e3g_prefix."docr WHERE visibile='S' AND iddocr=".$this->iddoc));
			$this->fields->imponibile->setValue($db->queryOne("SELECT SUM(imponibile) FROM ".$p4a->e3g_prefix."docr WHERE visibile='S' AND iddocr=".$this->iddoc));
		}
		else 
		{
			$this->fields->totdoc->setValue($db->queryOne("SELECT SUM(totale) FROM ".$p4a->e3g_prefix."docr WHERE iddocr=".$this->iddoc));
			$this->fields->imposta->setValue($db->queryOne("SELECT SUM(imposta) FROM ".$p4a->e3g_prefix."docr WHERE iddocr=".$this->iddoc));
			$this->fields->imponibile->setValue($db->queryOne("SELECT SUM(imponibile) FROM ".$p4a->e3g_prefix."docr WHERE iddocr=".$this->iddoc));
		}	
		




		if ($valid) {
			// se il documento e' settato per Gen. Auto Numero Doc vado a cercare
			// il numero Documento nel registro relativo
			$autonum = $db->queryOne("SELECT genautonum FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$this->fields->codtipodoc->getNewValue()."'");


			//$strdata = $this->data_to_sql($this->fields->data->getNewValue());
			//$this->fields->data->setNewValue($strdata);


			//if ($autonum == 'S')
			//{
			//	$registro = $this->merlin->db->queryOne("SELECT codregdoc FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$this->mask->fields['codtipodoc']->get_new_value()."'");
			//	$seriale = $this->merlin->db->queryOne("SELECT seriale FROM ".$p4a->e3g_prefix."docregistri WHERE codice='".$registro."'");
			//	$seriale++;
			//	$this->mask->fields['numdocum']->set_new_value($seriale);
			//	$query = "UPDATE ".$p4a->e3g_prefix."docregistri SET seriale=".$seriale." WHERE codice='".$registro."'";
			//	$this->merlin->db->query($query);
			//}
			//else
			//{
			//	$this->fields->numdocum->setNewValue($this->numerodoc->get_new_value());
			//}
			
			$this->fields->anno->setNewValue($p4a->e3g_azienda_anno_contabile);



			//if ($this->fields->numdocum->getNewValue() != '')
			//{
				// Aggiorno la data sulle righe del documento
				$strdata = $this->data_to_sql($this->fields->data->getNewValue());
				$query = "UPDATE ".$p4a->e3g_prefix."docr SET data='".$strdata."' WHERE iddocr=".$this->fields->iddoc->getNewValue();
				$db->query($query);
								
				// 1) procedo a salvare i dati della Testata
				parent::saveRow();

				
					
				
				// GESTIONE SCADENZE
				//$genscad = $this->merlin->db->queryOne("SELECT genscadenze FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$this->codtipodoc."'");
				//if ($genscad == 'S')
				//{
				//	$this->merlin->open_mask('movscad');
				//	$this->merlin->masks['movscad->listener->scadenze();
				//}
		
				
				require("class.movimenti.php");
				$mov = new Cmovimenti();
		
				// GESTIONE MOVIMENTI MAGAZZINO
				$genmovmag = $db->queryOne("SELECT genmovmag FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$this->codtipodoc."'");
				if ($genmovmag == 'S')
				{
					$mov->movimentimag($this->codtipodoc, $this->numdoc, $this->fields->codclifor->getNewValue(), $this->date, $p4a->e3g_azienda_anno_contabile, $this->iddoc);
				}
		
				// GESTIONE MOVIMENTI CONTABILI
				$genmovcon = $db->queryOne("SELECT genmovcon FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$this->codtipodoc."'");
				if ($genmovcon == 'S')
				{
					// in prova
					$mov->movimenticon($this->codtipodoc, $this->numdoc, $this->fields->codclifor->getNewValue(), $this->date, $p4a->e3g_azienda_anno_contabile, $this->iddoc);
				}
		
				
				// Aggiorno il Registro (SOLO SE e' NUMERICO PERCHE' LASCIO LA LIBERTA' DI METTERE AD ES. UN NUMERO / BIS)
				if (is_numeric($this->fields->numdocum->getNewValue()))
				{
					$registro = $db->queryOne("SELECT codregdoc FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$this->fields->codtipodoc->getNewValue()."'");
					$ultimo = $db->queryOne("SELECT MAX(seriale) FROM ".$p4a->e3g_prefix."docregistri WHERE codice='".$registro."'");
					if ($this->fields->numdocum->getNewValue()>$ultimo)
					{
						$query = "UPDATE ".$p4a->e3g_prefix."docregistri SET seriale=".$this->fields->numdocum->getNewValue." WHERE codice='".$registro."'";
						$db->query($query);
					}
				}
			//}
		}
		else
		{
			$this->message->setValue("Compilare i campi obbligatori");
		}
	}


	function nextRow()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();

		$query = "codtipodoc='".$this->fields->codtipodoc->getNewValue()."' AND anno='".$p4a->e3g_azienda_anno_contabile."' ";

		$this->data->setWhere($query);

		parent::nextRow();
	}


	function prevRow()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();

		$query = "codtipodoc='".$this->fields->codtipodoc->getNewValue()."' AND anno='".$p4a->e3g_azienda_anno_contabile."' ";

		$this->data->setWhere($query);

		parent::prevRow();
	}


	function newRow()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();

		// prima della nuova riga
		$this->codicetipodoc = $this->fields->codtipodoc->getNewValue();

		parent::newRow();
		
		$registro = $db->queryOne("SELECT codregdoc FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$this->codicetipodoc."'");
		$numero = $db->queryOne("SELECT seriale FROM ".$p4a->e3g_prefix."docregistri WHERE codice='".$registro."'");
		$numero++;

		
		$this->numerodoc->setNewValue($numero);


		// Dopo della Nuova Riga
 		$this->fields->codtipodoc->setValue($this->codicetipodoc);
 		$this->fields->numdocum->setValue($numero);
 		// cerco il tipo Anagrafica
		//$tipocf = $db->queryOne("SELECT tipoanagrafica FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$this->fields->codtipodoc->getNewValue()."'");
		$tipocf = $db->queryOne("SELECT tipoanagrafica FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$this->codtipodoc."'");

		// Cliente / Fornitore
		$query = "tipocfa='".$tipocf."'";
		$this->ds_anag->setWhere($query);
		$this->ds_anag->load();
		if ($tipocf=='C')
		{
			$this->fields->codclifor->setLabel('Cliente');
		}
		else
		{
			if ($tipocf=='F')
			{
				$this->fields->codclifor->setLabel('Fornitore');
			}
			else
			{
				$this->fields->codclifor->setLabel('Cliente/Fornitore');
			}
		}

 		// Attribuisco l'Id del Documento
 		$iddoc = $db->queryOne("SELECT iddoc FROM ".$p4a->e3g_prefix."doct ORDER BY iddoc DESC");
		$iddoc++;
		$this->fields->iddoc->setNewValue($iddoc);
	}
	
	
	function stampa_click()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
		genera_stampa_pdf($this->fields->numdocum->getNewValue(), $this->ds_doct->fields->iddoc->getNewValue(), $this->fields->codtipodoc->getNewValue(), $this->fields->codclifor->getNewValue(), $this->fields->codtipopag->getNewValue(), 0); // lo 0 indica che vengono mostrate solo le righe visibili
		
	}
	
	function data_to_sql($miadata)
	{
		// funzione per la conversione della data da un formato mm/gg/aa o mm/dd/aaaa
		// al formato utilizzato da mysql aaaa-mm-gg
		if ($miadata == '')
		{
			$miadata = str_replace("-", "/", date ("d-m-y")); //date ("d-m-y");
		}

		$pos1 = strpos ($miadata, '/');
		$pos2 = strpos ($miadata, '/',$pos1 + 1);
		$day = substr ($miadata, 0,$pos1);
		$month = substr ($miadata,$pos1 + 1,$pos2 - $pos1 - 1);
		$year = substr ($miadata,$pos2 + 1,strlen($miadata) - $pos2 - 1);

		return $year."-".$month."-".$day;
		//return $day."/".$month."/".$year;
	}


	function carica_doct()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
	
		$this->iddoc = $p4a->masks->gesdocumenti1_righe->iddoc;
			
		$this->codtipodoc = $p4a->masks->gesdocumenti1->fld_cod_tipo_doc->getNewValue();
		$this->numdoc = $p4a->masks->gesdocumenti1->numerodoc->getNewValue();
		$this->codclifor = $p4a->masks->gesdocumenti1->codclifor;
		$this->date = $p4a->masks->gesdocumenti1->data;
		
		$this->numerodoc->setValue("Num. Docum. : ".$this->numdoc);
		$desdocumento = $db->queryOne("SELECT descrizione FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$this->codtipodoc."'");
		
		$this->tipodoc->setValue("tipo Doc. : ".$desdocumento);
		
		return 0;
	}


}

?>