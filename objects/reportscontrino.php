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

class reportscontrino extends P4A_Mask
{
	function &reportscontrino()
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
		//Setto il titolo della maschera
		$this->setTitle('Report Scontrino');

		//Button per Chiudere
		$chiudi =& $this->build("p4a_button", "chiudi");
		$chiudi->setLabel('CONFERMA');
		$this->intercept($this->chiudi, 'onClick', 'chiudi_click');

		// button torna indietro
		$torna=& $this->build("p4a_button", "torna");
		$torna->setLabel('TORNA INDIETRO');
		$this->intercept($this->torna, 'onClick', 'torna_click');



		$fld_data =& $this->build("p4a_field", "fld_data");
		$this->fld_data->setLabel('Data Doc.');
		$this->fld_data->setType('Date');
		$this->fld_data->setValue(date("d/m/Y"));

		$fld_cliente =& $this->build("p4a_field", "fld_cliente");
		$this->fld_cliente->setLabel('CODICE CLIENTE');

		//Sorgente dati tipo doc
		$this->build("p4a_db_source", "ds_tipo");
		$this->ds_tipo->setTable($p4a->e3g_prefix."doctipidoc");
		$this->ds_tipo->setPk("codice");
		$this->ds_tipo->setWhere("tipoanagrafica='C' AND codice<>'00000'");
		$this->ds_tipo->load();
		$this->ds_tipo->firstRow();

		$fld_tipo_doc =& $this->build("p4a_field", "fld_tipo_doc");
		$this->fld_tipo_doc->setLabel('Tipo Documento');
		$this->fld_tipo_doc->setType('select');
		$this->fld_tipo_doc->setSourceValueField('codice');
		$this->fld_tipo_doc->setSourceDescriptionField('descrizione');
		$this->fld_tipo_doc->setSource($this->ds_tipo);
		
		$fld_gen_doc =& $this->build("p4a_field", "fld_gen_doc");
		$this->fld_gen_doc->setLabel('Genera Documento');
		$this->fld_gen_doc->setType('checkbox');
		
		//Sorgente dati della maschera
			
		$this->setSource($p4a->masks->cassa_eg->ds_report);
		//$this->Source->firstRow();
		
		//$db =& p4a_db::singleton();
		
		//Apro il data_Source della Tabella Totali
		//$my_array = $db->queryAll("SELECT codiva AS iva, SUM((prezzoven * qta) - (prezzoven * qta) * sconto/100)  AS Totale FROM ".$p4a->e3g_prefix."carrello WHERE idsessione='".session_id()."' GROUP BY codiva");
		
		
		//Creo una Tabella riepilogativa dei Totali per iva ed imposto il data_Source
		$tab_totali =& $this->build("p4a_table", "tab_totali");
		$tab_totali->setWidth(730);
		$tab_totali->setSource($p4a->masks->cassa_eg->ds_tot);
		
		$tab_pezzi =& $this->build("p4a_table", "tab_pezzi");
		$tab_pezzi->setWidth(730);
		$tab_pezzi->setSource($p4a->masks->cassa_eg->ds_pezzi);
		$tab_pezzi->cols->pezzi->setLabel("");
		$tab_pezzi->cols->importo->setLabel("");
		$tab_pezzi->hideHeaders();
		$tab_pezzi->hideNavigationBar();
		 		
		//Toolbar
		$this->build("p4a_standard_toolbar", "toolbar");
		$this->toolbar->setMask($this);

		// Message
		$message =& $this->build("p4a_message", "message");
		$message->setWidth("300");
			
			
		$sh_top =& $this->build("p4a_sheet", "sh_top");
        $this->sh_top->defineGrid(4, 3);
        $this->sh_top->setWidth(700);

		$this->sh_top->anchor( $this->fld_cliente,1,1);
		$this->sh_top->anchor( $this->fld_gen_doc,1,3);
        $this->sh_top->anchor( $this->fld_data,2,1);
		$this->sh_top->anchor( $this->fld_tipo_doc,2,3);
		$this->sh_top->anchor( $this->chiudi,4,1);
		$this->sh_top->anchor( $this->torna,4,3);
		
			
		$frm=& $this->build("p4a_frame", "frm");
		$frm->setWidth(730);
		$frm->anchor($this->message);
		$frm->anchor($this->sh_top);
		$frm->anchor($this->tab_totali);
		$frm->anchor($this->tab_pezzi);
		
		
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

	function torna_click()
	{
		$this->maskClose('reportscontrino');
		$this->showPrevMask();
		
	}

	function chiudi_click()
	{
			$db =& p4a_db::singleton();
			$p4a =& p4a::singleton();

			
			// Genero un Documento con lo scontrino		
			// Genero un Documento con lo scontrino		
			if ($this->fld_gen_doc->getNewValue() == 1)
			{
				// documento selezionato da generare
				$coddocsco = $this->fld_tipo_doc->getNewValue();
				
				// recupero il codice cliente da memorizzare nei mov. magazzino per gestione soci/non soci 
				$codcliente = $this->fld_cliente->getNewValue();
		
			}
			else
			{
				// Recupero codtipodoc Scontrino
				// documento di default per lo scontrino
				$coddocsco = $db->queryOne("SELECT eg_cod_doc_scontrino  FROM _aziende WHERE prefix='".$p4a->e3g_prefix."'");
				
				// recupero il codice cliente da memorizzare nei mov. magazzino per gestione soci/non soci 
				if ($this->fld_cliente->getNewValue() =="")
				{
					// recupero il codice cliente da memorizzare nei mov. magazzino per gestione soci/non soci 
					$codcliente = $this->fld_cliente->getNewValue();	
				}
				else 
				{
					// cliente fittizio VENDITA DETTAGLIO per registrare mov contabili
					$codcliente = "00000";
				}
			}
			$genmovmag = $db->queryOne("SELECT genmovmag FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$coddocsco."'");	
			$genmovcon = $db->queryOne("SELECT genmovcon FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$coddocsco."'");



			// Recupero il Registro progressivi
			$registro = $db->queryOne("SELECT codregdoc FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$coddocsco."'");
			// Recupero ultimo numero del registro e incremento di 1
			$ultimo = $db->queryOne("SELECT MAX(seriale) FROM ".$p4a->e3g_prefix."docregistri WHERE codice='".$registro."'");
			$ultimo++;
			
			// recupero il codice cliente da memorizzare nei mov. magazzino per gestione soci/non soci 
			$codcliente = $this->fld_cliente->getNewValue();

			$sconto_cli = $db->queryOne("SELECT sconto FROM ".$p4a->e3g_prefix."anagrafiche WHERE codice='".$codcliente."'");
			if (!is_numeric($sconto_cli))
			{
				$sconto_cli = 0; 
			}
			
			// Recupero annocontabile
			
			// Recupero Ultimo IdDoc
			$iddoc = $db->queryOne("SELECT MAX(iddoc) FROM ".$p4a->e3g_prefix."doct ");
			$iddoc++;

			// CREO LA TESTA DEL DOCUMENTO 
			if ($this->fld_data->getValue() == "")
			{
				$data_query = date("Y-m-d");
			}
			else
			{
				$mia_data = explode("/", date("d/m/Y"));
				$day_o = $mia_data[0];
				$mon_o = $mia_data[1];
				$yea_o = $mia_data[2];
				$data_mia = mktime(0, 0, 0, $mon_o, $day_o, $yea_o, 0);
				$data_query = date("Y-m-d", $data_mia);
			}
			
			
					
			$query = "INSERT INTO ".$p4a->e3g_prefix."doct (iddoc, data, numdocum, anno, codtipodoc, data_ins, idanag, codclifor) VALUES (".$iddoc.", '".$data_query."', '".$ultimo."', '".$p4a->e3g_azienda_anno_contabile."', '".$coddocsco."','".date ("Y-m-d H:i:s")."',".$p4a->e3g_utente_idanag.", '".$codcliente."')";				
			$db->query($query);
			
			
			// Aggiorno il registro
			$query = "UPDATE ".$p4a->e3g_prefix."docregistri SET seriale=".$ultimo." WHERE codice='".$registro."'";
			$db->query($query);
			
			
			// CREO LE RIGHE DEL DOCUMENTO
			// Recupero Ultima idriga
			$idriga = $db->queryOne("SELECT MAX(idriga) FROM ".$p4a->e3g_prefix."docr ");
			$idriga++;

			$nriga = 1;
			while($p4a->masks->cassa_eg->ds_report->getNumRows())
    		{

				// Creo le righe del documento
    			$p4a->masks->cassa_eg->ds_docr->newRow();
    			$p4a->masks->cassa_eg->ds_report->firstRow();
    			 
    			$p4a->masks->cassa_eg->ds_docr->fields->idriga->SetNewValue($idriga);
    			$p4a->masks->cassa_eg->ds_docr->fields->codice->SetNewValue($p4a->masks->cassa_eg->ds_report->fields->codarticolo->getNewValue());
    			$p4a->masks->cassa_eg->ds_docr->fields->descrizione->SetNewValue($p4a->masks->cassa_eg->ds_report->fields->descrizione->getNewValue());
    			$p4a->masks->cassa_eg->ds_docr->fields->quantita->SetValue(($p4a->masks->cassa_eg->ds_report->fields->qta->getNewValue()));
    			$p4a->masks->cassa_eg->ds_docr->fields->prezzo->SetValue($p4a->masks->cassa_eg->ds_report->fields->prezzoven->getNewValue());

				$p4a->masks->cassa_eg->ds_docr->fields->codiva->SetNewValue($p4a->masks->cassa_eg->ds_report->fields->codiva->getNewValue());
    			if ($p4a->masks->cassa_eg->ds_report->fields->sconto->getNewValue() > 0 )
    			{
    				// se ho inserito uno sconto lo tengo
    				$p4a->masks->cassa_eg->ds_docr->fields->sconto->SetNewValue($p4a->masks->cassa_eg->ds_report->fields->sconto->getNewValue());    			
    			}
				else 
				{
    				// se non ho lo sconto lo prendo dallo sconto cliente
    				$p4a->masks->cassa_eg->ds_docr->fields->sconto->SetNewValue($sconto_cli);
    			}
    			
				$p4a->masks->cassa_eg->ds_docr->fields->visibile->SetNewValue("S");

				$iva = str_replace(",", ".",$db->queryOne("SELECT iva FROM ".$p4a->e3g_prefix."aliquoteiva WHERE codice='".$p4a->masks->cassa_eg->ds_report->fields->codiva->getNewValue()."'"));

				//OK echo $iva."<br>";
				
				$quantita = $p4a->masks->cassa_eg->ds_report->fields->qta->getValue();
				if (is_numeric($quantita))
		        {		
		       	}
		       	else
		       	{
		       		$quantita = 0;
		       	}

				$prezzo = $p4a->masks->cassa_eg->ds_report->fields->prezzoven->getValue();
				if (is_numeric($prezzo))
		        {
		       	}
		       	else
		       	{
		       		$prezzo = 0;
		       	}
				
    			$sconto = 0;
		       	if ($p4a->masks->cassa_eg->ds_report->fields->sconto->getNewValue() > 0 )
    			{
    				// se ho inserito uno sconto lo tengo
					$sconto = $p4a->masks->cassa_eg->ds_report->fields->sconto->getValue();
    			}
				else 
				{
    				// se non ho lo sconto lo prendo dallo sconto cliente
    				$sconto = $sconto_cli;
    
    			}
				

    			$imponibile = round((($quantita * $prezzo) * (1 - $sconto / 100) / (100 + $iva)) * 100,2);
		    	$imposta = round((($quantita * $prezzo) * (1 - $sconto / 100)) - $imponibile,2);
		    	$totriga = $imponibile + $imposta ; // (($quantita * $prezzo) * (1 - $sconto / 100));

				$p4a->masks->cassa_eg->ds_docr->fields->imponibile->SetValue($imponibile);
    			$p4a->masks->cassa_eg->ds_docr->fields->imposta->SetValue($imposta);
    			$p4a->masks->cassa_eg->ds_docr->fields->totale->SetValue($totriga);
    			
    			$p4a->masks->cassa_eg->ds_docr->fields->nriga->SetNewValue($nriga);			
    			$p4a->masks->cassa_eg->ds_docr->fields->iddocr->SetNewValue($iddoc);
    			
    			$p4a->masks->cassa_eg->ds_docr->fields->codtipodoc->SetNewValue($coddocsco);
    			$p4a->masks->cassa_eg->ds_docr->fields->anno->SetNewValue($p4a->e3g_azienda_anno_contabile);
    			$p4a->masks->cassa_eg->ds_docr->fields->numdocum->SetNewValue($ultimo);
				
				
				$p4a->masks->cassa_eg->ds_docr->fields->data->SetNewValue($data_query);
				//$p4a->masks->cassa_eg->ds_docr->fields->data->SetNewValue($p4a->masks->cassa_eg->ds_report->fields->data->getNewValue());

    			$p4a->masks->cassa_eg->ds_docr->fields->conto->SetNewValue($db->queryOne("SELECT contovendita FROM ".$p4a->e3g_prefix."articoli WHERE codice='".$p4a->masks->cassa_eg->ds_report->fields->codarticolo->getNewValue()."'"));


    			$p4a->masks->cassa_eg->ds_docr->fields->conto->SetNewValue($db->queryOne("SELECT contovendita FROM ".$p4a->e3g_prefix."articoli WHERE codice='".$p4a->masks->cassa_eg->ds_report->fields->codarticolo->getNewValue()."'"));

				$p4a->masks->cassa_eg->ds_docr->fields->delta_prezzo->SetNewValue(0);
    			
								
    			$p4a->masks->cassa_eg->ds_docr->saveRow();
				// cancello la tabella temporanea carrello
    			$p4a->masks->cassa_eg->ds_report->deleteRow();
				
				
			    $idriga++;
				$nriga++;
				
							
    		}
			
			//die;
			// calcolo i totali del documento 
			$totale_t = $db->queryOne("SELECT SUM(totale) FROM ".$p4a->e3g_prefix."docr WHERE iddocr=".$iddoc);
			$imposta_t = $db->queryOne("SELECT SUM(imposta) FROM ".$p4a->e3g_prefix."docr WHERE iddocr=".$iddoc);
			$imponibile_t = $db->queryOne("SELECT SUM(imponibile) FROM ".$p4a->e3g_prefix."docr WHERE iddocr=".$iddoc);
				
			//Aggiorno DocT
			$query = "UPDATE ".$p4a->e3g_prefix."doct SET totdoc=".$totale_t.", imponibile=".$imponibile_t.", imposta=".$imposta_t." WHERE iddoc=".$iddoc;
			$db->query($query);
			

			$this->fld_gen_doc->setNewValue(0);
			$this->fld_cliente->setNewValue("");


			
			require("class.movimenti.php");
			$mov = new Cmovimenti();
	
			// Genero MovMagazzino
			if ($genmovmag == 'S')
			{			
				$mov->movimentimag($coddocsco, $ultimo, $codcliente, $data_query, $p4a->e3g_azienda_anno_contabile, $iddoc);
			}			
			
			// Genero MovContabili
			if ($genmovcon == 'S')
			{
				$mov->movimenticon($coddocsco, $ultimo, $codcliente, $data_query, $p4a->e3g_azienda_anno_contabile, $iddoc);
			}
			
			
			// Distruggo il ds			
			$p4a->masks->cassa_eg->ds_docr->destroy();
			
			$p4a->masks->cassa_eg->tab_carrello->setTitle("Totale : 0 euro");
	
			
    
			$this->maskClose('reportscontrino');
			$this->showPrevMask();
			
			
	}
}
?>