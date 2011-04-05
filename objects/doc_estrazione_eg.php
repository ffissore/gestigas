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

class doc_estrazione_eg extends P4A_Mask
{
	
	// Variabile per definire il tipo Anagrafica Cliente / Fornitore
	var $codicetipodoc = '';

	function doc_estrazione_eg()
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
		//Setto il titolo della maschera
		$this->setTitle('Estrazione Documenti');

		$this->build("p4a_db_source", "ds_orig");
		$this->ds_orig->setTable($p4a->e3g_prefix."doctipidoc");
		$this->ds_orig->setPk("codice");
		$this->ds_orig->setWhere("codaltridoc <>'' AND codice<>'00000'");
		$this->ds_orig->addOrder("codaltridoc");
		$this->ds_orig->load();
	
		$this->setSource($this->ds_orig);
		$this->ds_orig->firstRow();
		
		$this->setTitle("Estrazione ".$this->fields->descrizione->getNewValue());

		
		$primodoc = $db->queryOne("SELECT codaltridoc FROM " . $p4a->e3g_prefix . "doctipidoc WHERE codaltridoc <>'' ORDER BY codaltridoc" );		
		
		$fldanag=& $this->build("p4a_field", "fldanag");
		$fldanag->setLabel('Utente');
		$fldanag->setWidth("150");

		$flddata=& $this->build("p4a_field", "flddata");
		$flddata->setLabel('data');
		$flddata->label->setWidth("40");
		$flddata->setWidth("80");
		$flddata->setLabel("Data");
		$flddata->setValue(date("d/m/Y"));
		$flddata->setType('date');

		$this->build("p4a_button", "elimina");
		$this->elimina->setLabel("Elimina");
		$this->elimina->addAction("onClick");
		$this->intercept($this->elimina, "onClick", "elimina_click");
		$this->elimina->requireConfirmation( "onClick", "Vuoi veramente ELIMINARE i documenti selezionati?" );

		$this->elimina->setIcon("delete");
		$this->elimina->setWidth(150);

		$this->build("p4a_button", "estrai");
		$this->estrai->setLabel("Estrazione");
		$this->estrai->addAction("onClick");
		$this->intercept($this->estrai, "onClick", "estrai_click");
		$this->estrai->requireConfirmation( "onClick", "Vuoi veramente ESTRARRE i documenti selezionati?" );
		$this->estrai->setIcon("spreadsheet");
		$this->estrai->setWidth(150);



		// Documenti estraibili
		$this->build("p4a_db_source", "ds_doct");
		$this->ds_doct->setTable($p4a->e3g_prefix."doct");
		$this->ds_doct->setQuery("SELECT iddoc, CONCAT('n.', numdocum, ' del ', DATE_FORMAT(data, '%d/%m/%y'), ' [', LEFT(a.descrizione, 25),'...]') as documento  FROM ".$p4a->e3g_prefix."doct d INNER JOIN ".$p4a->e3g_prefix."anagrafiche a ON  d.codclifor = a.codice WHERE (ISNULL(docchiuso) OR docchiuso <>1) AND codtipodoc ='".$primodoc."' ORDER BY data DESC, iddoc DESC");
		
		$this->ds_doct->setPk("iddoc");
		/*
		$this->ds_doct->setSelect("iddoc, docchiuso, data,  CONCAT('n.', numdocum, ' del ', DATE_FORMAT(data, '%d/%m/%Y'), ' [', a.descrizione ,']') as documento, codclifor");
		$this->ds_doct->setWhere("ISNULL(docchiuso) OR docchiuso <>1");
		$this->ds_doct->addJoin($p4a->e3g_prefix . "anagrafiche", $p4a->e3g_prefix . "doct.codclifor = ".$p4a->e3g_prefix."anagrafiche.codice");
		$this->ds_doct->addFilter("codtipodoc = ?", $this->fields->codaltridoc);
		$this->ds_doct->addOrder("data", "DESC");
		$this->ds_doct->addOrder("iddoc", "DESC");
		*/
	
		$this->ds_doct->load();

				
		$fldrow=& $this->build("p4a_field", "fldrow");
		$fldrow->setType('multiselect');
		$fldrow->setLabel('Doc. Estraibili');
		$fldrow->setSource($this->ds_doct);
		$fldrow->setSourceDescriptionField("documento"); 
		$fldrow->setSourceValueField("iddoc"); 


		
				
		$this->fields->descrizione->setLabel('Tipo Documento');
		$this->fields->descrizione->disable();
	
		
		
		$this->settacolonne();
				
		// Message
		$message =& $this->build("p4a_message", "message");
		$message->setWidth("300");

		
		//Aggiungo alla maschera una nuova standard toolbar.
		$this->build("p4a_navigation_toolbar", "toolbar");
		$this->toolbar->setMask($this);
				

		
		$sh_campi =& $this->build("p4a_sheet", "sh_campi");
        $this->sh_campi->defineGrid(4, 6);
        $this->sh_campi->setWidth(700);
		
		//$this->sh_campi->anchor($this->fields->descrizione,1,1,1,6);
		$this->sh_campi->anchor($this->fldrow,2,1,1,3);
		
		$this->sh_campi->anchor($this->estrai,3,4);
		$this->sh_campi->anchor($this->elimina,3,6);
		$this->sh_campi->anchor($this->flddata,4,4,1,3);

		
		
		// Frame
		$frm=& $this->build("p4a_frame", "frm");
		$frm->setWidth(700);

		$frm->anchor($message);
		//$frm->anchor($fset1);
		$frm->anchor($sh_campi);
		
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

	
	function data_to_sql($miadata)
	{
		// funzione per la conversione della data da un formato "umano" mm/gg/aa o mm/dd/aaaa
		// al formato utilizzato da mysql aaaa-mm-gg

		$pos1 = strpos ($miadata, '/');
		$pos2 = strpos ($miadata, '/',$pos1 + 1);
		$day = substr ($miadata, 0,$pos1);
		$month = substr ($miadata,$pos1 + 1,$pos2 - $pos1 - 1);
		$year = substr ($miadata,$pos2 + 1,strlen($miadata) - $pos2 - 1);

		return $year."-".$month."-".$day;

	}

	function sql_to_data($miadata)
	{
		// funzione per la conversione della data da un formato "umano" mm/gg/aa o mm/dd/aaaa
		// al formato utilizzato da mysql aaaa-mm-gg

		$pos1 = strpos ($miadata, '-');
		$pos2 = strpos ($miadata, '-',$pos1 + 1);
		
		$year = substr ($miadata, 0,$pos1);
		$month = substr ($miadata,$pos1 + 1,$pos2 - $pos1 - 1);
		$day = substr ($miadata,$pos2 + 1,strlen($miadata) - $pos2 - 1);

		return $day."/".$month."/".$year;

	}

	
	function elimina_click()
	{	
		$db =& p4a_db::singleton();

		$db->query("DELETE FROM ".$p4a->e3g_prefix."doct_estraz WHERE idtable IN ".$this->ricava_numeri($this->fldrow->getNewValue()));
		
	}
	
	function estrai_click()
	{	
		$db =& p4a_db::singleton();
		$p4a =& p4a::singleton();
		
		
		if ($this->flddata->getNewValue()!='')
		{	
			
			$iddocumenti = $this->ricava_numeri($this->fldrow->getNewValue());
			
			// ho inserito l'elenco dei documenti, li estraggo
    		$strdocorigine = $this->fields->codaltridoc->getNewValue();
									
			$this->build("p4a_db_source", "ds_docr");
			$this->ds_docr->setTable($p4a->e3g_prefix."docr");
			$this->ds_docr->setPk("idriga");
			//$this->ds_docr->setWhere("iddocr IN (SELECT iddoc FROM ".$p4a->e3g_prefix."doct_estraz WHERE codtipodoc='".$strdocorigine."')");
			$this->ds_docr->setWhere("iddocr IN ".$iddocumenti);
			
			$this->ds_docr->addOrder("idriga");
			$this->ds_docr->load();
		
		
			$this->build("p4a_db_source", "ds_newr");
			$this->ds_newr->setTable($p4a->e3g_prefix."docr");
			$this->ds_newr->setPk("idriga");
			$this->ds_newr->setWhere("1=0");
			$this->ds_newr->load();

			
    	
			// Genero la Testata del Documento Estratto 
			$codtipodoc = $this->fields->codice->getNewValue();			
			$datadoc = $this->data_to_sql($this->flddata->getNewValue());
			
			$registro = $db->queryOne("SELECT codregdoc FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$codtipodoc."'");
    		$numdocum = $db->queryOne("SELECT MAX(seriale) FROM ".$p4a->e3g_prefix."docregistri WHERE codice='".$registro."'");
    		$numdocum++;
    		$query = "UPDATE ".$p4a->e3g_prefix."docregistri SET seriale=".$numdocum." WHERE codice='".$registro."'";
    		

    		$db->query($query);
    		$iddoc = $db->queryOne("SELECT MAX(iddoc) FROM ".$p4a->e3g_prefix."doct ");
    		$iddoc++;	
			
			// creo la TESTA del documento 
			$db->query("INSERT INTO ".$p4a->e3g_prefix."doct (iddoc,numdocum,codtipodoc,anno,data, data_ins, idanag) VALUES (".$iddoc.",'".$numdocum."','".$codtipodoc."','".$p4a->e3g_azienda_anno_contabile."','".$datadoc."','".date ("Y-m-d H:i:s")."',".$p4a->e3g_utente_idanag.")");
    	
		
    		
    		// faccio scorrere le righe dei documenti 
			// ad ogni riga metto il riferimento e genero una riga del documento estratto 
			$riga = $db->queryOne("SELECT idriga FROM ".$p4a->e3g_prefix."docr ORDER BY idriga DESC");
		
			$riga++;
						
			$rigarel = 1 ;
			$nriga = 1;
			$oldnumdoc = "-1111111";		 
			
			$this->ds_docr->firstRow(); 
		
		// QUI QUI
			$primariga = $this->ds_docr->fields->idriga->getNewValue();
			$stridriga = "(";
			while($rigarel <= $this->ds_docr->getNumRows())
			{
				$this->ds_newr->newRow();
					
				if ($oldnumdoc != $this->ds_docr->fields->numdocum->getNewValue())
				{
					//Inserisco Riga Riferimento 
        			$descrizionedoc = $db->queryOne("SELECT desbreve FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$this->ds_docr->fields->codtipodoc->getNewValue()."'");
            		$this->ds_newr->fields->idriga->setNewValue($riga);			
        			$this->ds_newr->fields->codice->setNewValue("");
        			$this->ds_newr->fields->quantita->setNewValue("0");
        			$this->ds_newr->fields->codiva->setNewValue("");
        			$this->ds_newr->fields->prezzo->setNewValue("0");
        			$this->ds_newr->fields->descrizione->setNewValue("Rif. ".$descrizionedoc." Num. ".$this->ds_docr->fields->numdocum->getNewValue()." del ".$this->sql_to_data($this->ds_docr->fields->data->getNewValue())) ;
        			$this->ds_newr->fields->iddocr->setNewValue($iddoc);
        			$this->ds_newr->fields->anno->setNewValue($p4a->e3g_azienda_anno_contabile);
        			$this->ds_newr->fields->codtipodoc->setNewValue($codtipodoc);
        			$this->ds_newr->fields->data->setNewValue($datadoc);
        			$this->ds_newr->fields->numdocum->setNewValue($numdocum);
        			$this->ds_newr->fields->nriga->setNewValue($nriga);
        			$this->ds_newr->fields->rifidriga->setNewValue($this->ds_docr->fields->idriga->getNewValue());
        			$this->ds_newr->fields->rifiddoc->setNewValue($this->ds_docr->fields->iddocr->getNewValue());
        			$this->ds_newr->fields->codutente->setNewValue($this->ds_docr->fields->codutente->getNewValue());
        			$this->ds_newr->fields->visibile->setNewValue("S");
					$this->ds_newr->saveRow($docr);
					$riga++;
        			
					$oldnumdoc = $this->ds_docr->fields->numdocum->getNewValue();

				}
				else
				{
					//Inserisco Riga Vera e Propria
        			$this->ds_newr->fields->idriga->setNewValue($riga);			
        			$this->ds_newr->fields->codice->setNewValue($this->ds_docr->fields->codice->getValue()); 
        			$this->ds_newr->fields->descrizione->setNewValue($this->ds_docr->fields->descrizione->getValue());
        			$this->ds_newr->fields->iddocr->setNewValue($iddoc);
        			$this->ds_newr->fields->anno->setNewValue($p4a->e3g_azienda_anno_contabile);
        			$this->ds_newr->fields->codtipodoc->setNewValue($codtipodoc);
        			$this->ds_newr->fields->data->setNewValue($datadoc);
        			$this->ds_newr->fields->numdocum->setNewValue($numdocum);
        			$this->ds_newr->fields->quantita->setNewValue($this->ds_docr->fields->quantita->getValue());
        			$this->ds_newr->fields->codiva->setNewValue($this->ds_docr->fields->codiva->getValue());
        			$this->ds_newr->fields->prezzo->setNewValue($this->ds_docr->fields->prezzo->getValue());
        			$this->ds_newr->fields->sconto->setNewValue($this->ds_docr->fields->sconto->getValue());
        			
        			$this->ds_newr->fields->nriga->setNewValue($nriga);
        			$this->ds_newr->fields->rifidriga->setNewValue($this->ds_docr->fields->idriga->getValue());
        			$this->ds_newr->fields->codutente->setNewValue($this->ds_docr->fields->codutente->getValue());
        			//$this->ds_newr->fields->visibile->setNewValue($this->ds_docr->fields->visibile->getNewValue());
        			$this->ds_newr->fields->visibile->setNewValue("S");

					$this->calcola_tot_riga($this->ds_docr->fields->quantita->getValue(), $this->ds_docr->fields->codiva->getValue(), $this->ds_docr->fields->prezzo->getValue(), $this->ds_docr->fields->sconto->getValue());
					//$this->calcola_tot_riga();

						        			
					
					$oldnumdoc = $this->ds_docr->fields->numdocum->getNewValue();
        			
        			$this->ds_newr->saveRow();
					$riga++;
        			
					$this->ds_docr->nextRow();				
					$rigarel++;							
				}
				$nriga++;
			}
			$fornitore = $db->queryOne("SELECT codclifor FROM ".$p4a->e3g_prefix."doct WHERE iddoc=".$this->ds_docr->fields->iddocr->getNewValue());
	
			//faccio l'UPDATE della TESTATA per inserire il codice cli/for
    		$query = "UPDATE ".$p4a->e3g_prefix."doct SET codclifor='".$fornitore."' WHERE iddoc=".$iddoc;
    		$db->query($query);
			
			// faccio l'update dei documenti estratti 
			$query = "UPDATE ".$p4a->e3g_prefix."doct SET rifiddoc=".$iddoc.", docchiuso=1 WHERE iddoc IN ".$iddocumenti;
    		$db->query($query);
			
			// cancello dalla tabella dei documenti in attesa di estrazione 
			//$query = "DELETE FROM ".$p4a->e3g_prefix."doct_estraz WHERE codtipodoc='".$strdocorigine."'";		
    		//$db->query($query);
			
		
			
			require("class.movimenti.php");
			$genmov = new Cmovimenti();


			// controllo se devo salvare i movimenti di magazzino 
            $genmovmag = $db->queryOne("SELECT genmovmag FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$codtipodoc."'");
            if ($genmovmag == 'S')
            {
				$genmov->movimentimag($codtipodoc, $numdocum, $fornitore, $datadoc, $p4a->e3g_azienda_anno_contabile, $iddoc);
			}
            
            // controllo se devo salvare i movimenti contabili
            $genmovcon = $db->queryOne("SELECT genmovcon FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$codtipodoc."'");
            if ($genmovcon == 'S')
            {                        
				$genmov->movimenticon($codtipodoc, $numdocum, $fornitore, $datadoc, $p4a->e3g_azienda_anno_contabile, $iddoc);
            }
		
			// aggiorno la tabella 
			//$this->ds_doct->setQuery("SELECT iddoc, CONCAT('n.', numdocum, ' del ', DATE_FORMAT(data, '%d/%m/%y'), ' [', LEFT(a.descrizione, 25),'...]') as documento  FROM ".$p4a->e3g_prefix."doct d INNER JOIN ".$p4a->e3g_prefix."anagrafiche a ON  d.codclifor = a.codice WHERE (ISNULL(docchiuso) OR docchiuso <>1) AND codtipodoc ='".$this->fields->codaltridoc->getNewValue()."' ORDER BY data DESC, iddoc DESC");

		}
		else 
		{
			$this->message->setValue("Inserire una data valida");
		}
        
		
	}


	
	function settacolonne()
	{
		$p4a =& p4a::singleton();

		$this->build("p4a_db_source", "ds_anag");
		$this->ds_anag->setTable($p4a->e3g_prefix."anagrafiche");
		$this->ds_anag->setPk("idanag");		
		$this->ds_anag->load();

		$this->build("p4a_db_source", "ds_tipo");
		$this->ds_tipo->setTable($p4a->e3g_prefix."doctipidoc");
		$this->ds_tipo->setPk("codice");		
		$this->ds_tipo->setWhere("codice <> '00000'");		
		$this->ds_tipo->load();

	}	

	
	function calcola_tot_riga($quantita, $codiva, $prezzo, $sconto)
	{
		$db =& p4a_db::singleton();
		$p4a =& p4a::singleton();
		
		$iva = $db->queryOne("SELECT iva FROM ".$p4a->e3g_prefix."aliquoteiva WHERE codice='".$codiva."'");

		//$quantita = str_replace(",", ".",$this->ds_newr->fields->quantita->getValue());
		
		
		if (is_numeric($quantita))
        {

       	}
       	else
       	{
       		$quantita = 0;
       	}
		
		
		//$prezzo = str_replace(",", ".",$this->ds_newr->fields->prezzo->getValue());
		
		
		if (is_numeric($prezzo))
        {

       	}
       	else
       	{
       		$prezzo = 0;
       	}
		
		//$sconto = str_replace(",", ".",$this->ds_newr->fields->sconto->getValue());
		
		if (is_numeric($sconto))
        {

       	}
       	else
       	{
       		$sconto = 0;
       	}
		
        $imponibile = (($quantita * $prezzo) * (1 - $sconto / 100) / (100 + $iva)) * 100;
        $imposta = (($quantita * $prezzo) * (1 - $sconto / 100)) - $imponibile;
        $totriga = (($quantita * $prezzo) * (1 - $sconto / 100));

		
        $this->ds_newr->fields->imponibile->setNewValue(round($imponibile, 2));
        $this->ds_newr->fields->imposta->setNewValue(round($imposta,2));
        $this->ds_newr->fields->totale->setNewValue(round($totriga,2));

		return 0;

	}

	
	function ricava_numeri($myarr)
	{	
		$strtmp = "";
		foreach ( $myarr as $key => $value) {
			if ($strtmp == "")
				$strtmp = $value;
			else 
				$strtmp .= ", ".$value;				
				
		}		
		
		return "(".$strtmp.")";
		
	}
	
	function nextRow()
	{
		$p4a =& p4a::singleton();
		parent::nextRow();
		
		$this->ds_doct->setQuery("SELECT iddoc, CONCAT('n.', numdocum, ' del ', DATE_FORMAT(data, '%d/%m/%y'), ' [', LEFT(a.descrizione, 25),'...]') as documento  FROM ".$p4a->e3g_prefix."doct d INNER JOIN ".$p4a->e3g_prefix."anagrafiche a ON  d.codclifor = a.codice WHERE (ISNULL(docchiuso) OR docchiuso <>1) AND codtipodoc ='".$this->fields->codaltridoc->getNewValue()."' ORDER BY data DESC, iddoc DESC");
		$this->setTitle("Estrazione ".$this->fields->descrizione->getNewValue());

		
		
	}

	function prevRow()
	{
		$p4a =& p4a::singleton();
		parent::prevRow();
		
		$this->ds_doct->setQuery("SELECT iddoc, CONCAT('n.', numdocum, ' del ', DATE_FORMAT(data, '%d/%m/%y'), ' [', LEFT(a.descrizione, 25),'...]') as documento  FROM ".$p4a->e3g_prefix."doct d INNER JOIN ".$p4a->e3g_prefix."anagrafiche a ON  d.codclifor = a.codice WHERE (ISNULL(docchiuso) OR docchiuso <>1) AND codtipodoc ='".$this->fields->codaltridoc->getNewValue()."' ORDER BY data DESC, iddoc DESC");
		$this->setTitle("Estrazione ".$this->fields->descrizione->getNewValue());
		
	}
	
	function firstRow()
	{
		$p4a =& p4a::singleton();
		parent::firstRow();
		
		$this->ds_doct->setQuery("SELECT iddoc, CONCAT('n.', numdocum, ' del ', DATE_FORMAT(data, '%d/%m/%y'), ' [', LEFT(a.descrizione, 25),'...]') as documento  FROM ".$p4a->e3g_prefix."doct d INNER JOIN ".$p4a->e3g_prefix."anagrafiche a ON  d.codclifor = a.codice WHERE (ISNULL(docchiuso) OR docchiuso <>1) AND codtipodoc ='".$this->fields->codaltridoc->getNewValue()."' ORDER BY data DESC, iddoc DESC");
		$this->setTitle("Estrazione ".$this->fields->descrizione->getNewValue());

	}

	function lastRow()
	{
		$p4a =& p4a::singleton();
		parent::lastRow();
		
		$this->ds_doct->setQuery("SELECT iddoc, CONCAT('n.', numdocum, ' del ', DATE_FORMAT(data, '%d/%m/%y'), ' [', LEFT(a.descrizione, 25),'...]') as documento  FROM ".$p4a->e3g_prefix."doct d INNER JOIN ".$p4a->e3g_prefix."anagrafiche a ON  d.codclifor = a.codice WHERE (ISNULL(docchiuso) OR docchiuso <>1) AND codtipodoc ='".$this->fields->codaltridoc->getNewValue()."' ORDER BY data DESC, iddoc DESC");
		$this->setTitle("Estrazione ".$this->fields->descrizione->getNewValue());
		
	}

	

		
}

?>