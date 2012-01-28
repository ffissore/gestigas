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

class gesdocumenti1_righe extends P4A_Mask
{
	// Variabile per definire il tipo Anagrafica Cliente / Fornitore
	var $codtipodoc ='';
	var $numdoc = 0;
	var $strdata = '';
	var $codclifor = '';
	var $sconto_cli = 0;
	var $nomeclifor = '';
	var $iddoc = 0;
	var $nuovariga = 0;
	var $vengodaricerca = 0;			

	function gesdocumenti1_righe()
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();


		$this->carica_doct();


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


		$this->carica_doct2();

		
		
		$this->build("p4a_db_source", "ds_docr");
		$this->ds_docr->setTable($p4a->e3g_prefix."docr");		
		$this->ds_docr->setPk("idriga");
		if ( E3G_TIPO_GESTIONE == 'G' )
		{
			// gestione Visible / Invisible solo per Gestigas
			$this->ds_docr->setWhere("visibile='S' AND iddocr=".$this->iddoc);			
		}
		else 
		{
			$this->ds_docr->setWhere("iddocr=".$this->iddoc);
						
		}
		
		
		$this->ds_docr->addOrder("iddocr");
		$this->ds_docr->addOrder("nriga");
		$this->ds_docr->load();
		$this->setSource($this->ds_docr);
		$this->ds_docr->firstRow();

		$this->build("p4a_field", "codicefinto");
		$this->codicefinto->setLabel("Ricerca");

		//Pulsante VAI alla CODA 
		$this->build("p4a_button", "coda");
		$this->coda->setLabel("Vai coda Doc.");
		$this->coda->setIcon("next");
		
		$this->coda->addAction("onClick");
		$this->intercept($this->coda, "onClick", "coda_click");
		$this->coda->setWidth(200);

		
		$this->build("p4a_button", "etichette");
		$this->etichette->setIcon("labels");
		$this->etichette->setLabel("Stampa Etichette");
		$this->etichette->addAction("onClick");
		$this->intercept($this->etichette, "onClick", "etichette_click");
		$this->etichette->setWidth(200);

		
		// Stampa 
		$this->build("p4a_button", "stampa");
		$this->stampa->setLabel("Esporta come PDF...");
		$this->stampa->setIcon("pdf");
		$this->stampa->addAction("onClick");
		$this->stampa->setAccessKey("p");

		$this->intercept($this->stampa, "onClick", "stampa_click");
		$this->stampa->setWidth("200");

		//Pulsante Aggiungi alle Righe
		$this->build("p4a_button", "aggiungi");
		$this->aggiungi->setLabel("Salva Riga");
		$this->aggiungi->setIcon("save");
		$this->aggiungi->addAction("onClick");
		$this->intercept($this->aggiungi, "onClick", "aggiungi_click");
		$this->aggiungi->setWidth(200);
		$this->aggiungi->setAccessKey("s"); 
		
		//Pulsante Nuova Riga 
		$this->build("p4a_button", "bu_nuovariga");
		$this->bu_nuovariga->setLabel("Nuova Riga");
		$this->bu_nuovariga->setIcon("new");
		$this->bu_nuovariga->addAction("onClick");
		$this->intercept($this->bu_nuovariga, "onClick", "bu_nuovariga_click");
		$this->bu_nuovariga->setAccessKey("n"); 
		$this->aggiungi->setWidth(200);




		//Pulsante Nuova Riga
		$this->build("p4a_button", "nuovo");
		$this->nuovo->setLabel("Nuovo Articolo");
		$this->nuovo->addAction("onClick");
		$this->intercept($this->nuovo, "onClick", "nuovo_click");
		$this->nuovo->setWidth(200);



		//Pulsante Elimina Riga
		$this->build("p4a_button", "elimina");
		$this->elimina->setLabel("Elimina Riga");
		$this->elimina->addAction("onClick");
		$this->intercept($this->elimina, "onClick", "elimina_click");
		$this->elimina->setWidth(200);

		//Pulsante Esci dalla Maschera
		$this->build("p4a_button", "esci");
		$this->esci->setLabel("Esci");
		$this->esci->addAction("onClick");
		$this->intercept($this->esci, "onClick", "esci_click");
		$this->esci->setWidth(200);



	
		//Pulsante Salva Doc
		$this->build("p4a_button", "salva");
		$this->salva->setLabel("Salva Doc.");
		$this->salva->addAction("onClick");
		$this->intercept($this->salva, "onClick", "salva_click");
		$this->salva->setWidth(200);


		//Aggiungo la Tabella Righe
		$tab_row =& $this->build("p4a_table", "tab_row");
		$tab_row->setWidth(E3G_TABLE_WIDTH);
		$tab_row->setSource($this->ds_docr);
		$tab_row->setVisibleCols(array("codice", "descrizione","quantita","conto"));
		$this->intercept($tab_row->rows, "afterClick", "tab_row_click");

		while ($col =& $tab_row->cols->nextItem()) {
			$col->setWidth(160);
		}
		$tab_row->showNavigationBar();



		//////////////////////////////////////////////////////////////////
		// Aggiungo i campi collegati alla tabella del corpo del documento
		$this->fields->quantita->data_field->setType("decimal");
		$this->fields->quantita2->data_field->setType("decimal");
		$this->fields->prezzo->data_field->setType("decimal");
		$this->fields->sconto->data_field->setType("decimal");
		$this->fields->imponibile->data_field->setType("decimal");
		$this->fields->imposta->data_field->setType("decimal");
		$this->fields->totale->data_field->setType("decimal");

		$this->fields->codice->setLabel('Codice Articolo');
		// aggiungo evento OnReturnPress
		$this->fields->codice->addAction('onReturnPress');
		$this->intercept($this->fields->codice,'onReturnPress','ricerca_click');
		
		// aggiungo evento OnBlur
		$this->fields->codice->addAction('onBlur');
		$this->intercept($this->fields->codice,'onBlur','ricerca_click');


		$this->fields->descrizione->setLabel('descrizione');
		$this->fields->barcode->setLabel('Codice Barre');
		$this->fields->quantita->setLabel('quantita');
		$this->fields->quantita2->setLabel('quantita2');

		$this->fields->descrizione->setWidth('200');
		$this->fields->barcode->setWidth('100');
		$this->fields->quantita->setWidth('60');
		$this->fields->quantita2->setWidth('60');
		$this->fields->prezzo->setWidth('60');
		$this->fields->sconto->setWidth('60');
		$this->fields->conto->setWidth('60');


		$this->fields->prezzo->setLabel('prezzo');
		$this->fields->codiva->setLabel('Iva');

		// TIPO DOCUMENTO
		$this->build("p4a_db_source", "ds_iva");
		$this->ds_iva->setTable($p4a->e3g_prefix."aliquoteiva");
		$this->ds_iva->setPk("codice");
		$this->ds_iva->load();


		$this->fields->codiva->setLabel('iva');
		$this->fields->codiva->setType('select');
		$this->fields->codiva->setSourceValueField('codice');
		$this->fields->codiva->setSourceDescriptionField('descrizione');
		$this->fields->codiva->setSource($this->ds_iva);


		$this->fields->sconto->setLabel('sconto');
		$this->fields->imponibile->setLabel('imponibile');
		$this->fields->imponibile->setInvisible();
		$this->fields->imposta->setLabel('imposta');
		$this->fields->imposta->setInvisible();
		$this->fields->totale->setLabel('Totale');
		$this->fields->totale->setInvisible();

		//Aggiungo alla maschera una nuova standard toolbar.
		$this->build("p4a_standard_toolbar", "toolbar");
		$this->toolbar->setMask($this);
		
		$this->toolbar->buttons->save->setAccessKey("s");


		// Message
		$message =& $this->build("p4a_message", "message");
		$message->setWidth("300");

		//Setto il titolo della maschera
		$this->setTitle('Documenti: Corpo');


		$sh_doc =& $this->build("p4a_sheet", "sh_doc");
        $this->sh_doc->defineGrid(2, 2);

		$this->sh_doc->anchor($this->tipodoc,1,1);
		$this->sh_doc->anchor($this->numerodoc,1,2);
		$this->sh_doc->anchor($this->datadoc,2,1);
		$this->sh_doc->anchor($this->clifor,2,2);






        // Ancoro uno Sheet per i campi del corpo
        $sh_campi =& $this->build("p4a_sheet", "sh_campi");

        // applico la grid allo sheet campi
        $this->sh_campi->defineGrid(11, 3);
        //$this->sh_campi->anchor($this->codicefinto,1,1);
        $this->sh_campi->anchor($this->bu_nuovariga,1,1);
        $this->sh_campi->anchor($this->fields->codice,2,1);
        $this->sh_campi->anchor($this->fields->descrizione,2,2);
        
		$this->sh_campi->anchor($this->fields->barcode,3,1);
        $this->sh_campi->anchor($this->fields->quantita,4,1);
        $this->sh_campi->anchor($this->fields->quantita2,4,2);
        $this->sh_campi->anchor($this->fields->prezzo,5,1);
        $this->sh_campi->anchor($this->fields->codiva,5,2);
        $this->sh_campi->anchor($this->fields->sconto,6,2);
        $this->sh_campi->anchor($this->fields->conto,7,1);
        $this->sh_campi->anchor($this->aggiungi,7,2);
        $this->sh_campi->anchor($this->fields->imponibile,8,1);
        $this->sh_campi->anchor($this->fields->imposta,8,2);
        $this->sh_campi->anchor($this->fields->totale,8,3);
        $this->sh_campi->anchor($this->coda,10,1);
        $this->sh_campi->anchor($this->stampa,10,3);
        if ( E3G_TIPO_GESTIONE == 'G' )
		{
			// non visualizzo il pulsante Etichette
		}
		else 
		{
			// Visualizzo il pulsante Etichette per Equogest
			$this->sh_campi->anchor($this->etichette,11,2);			
		}

		$frm=& $this->build("p4a_frame", "frm");
		$frm->setWidth( E3G_MAIN_FRAME_WIDTH );

		$frm->anchor($message);
		$frm->anchor($sh_doc);
		$frm->anchor($sh_campi);
		$frm->anchor($this->tab_row);
		
		e3g_scrivi_footer( $this, $frm );

  		// Display
		$this->display("main", $frm);
		$this->display("menu", $p4a->menu);
		$this->display("top", $this->toolbar);


	}

	function main()
	{

		$this->carica_doct();
		
		$this->carica_doct2();
				
		//$this->ds_docr->setWhere("visibile='S' AND iddocr=".$this->iddoc);
		//$this->ds_docr->addOrder("iddocr");
		//$this->ds_docr->addOrder("nriga");
		//$this->ds_docr->firstRow();
		//$this->ds_docr->load();
		//$this->setSource($this->ds_docr);
		
		//Gestione Vengo da Ricerca perchè con il caricasource qui non mi funzionava più il NewRow ed il SaveRow
		if ( $this->vengodaricerca == 0 )
		{
			$this->caricasource();
		}	
		$this->vengodaricerca = 0; 
		
		parent::main();
		
		
		 
		//foreach($this->mf as $mf){
		//	$this->fields->$mf->unsetStyleProperty("border");
		//}

		
				
	}


	function coda_click()
	{
		$p4a =& p4a::singleton();
		
		//$this->maskClose('gesdocumenti1_righe');
			
		$p4a->openMask('gesdocumenti1_coda');

	}

	function carica_click()
	{
		$p4a =& p4a::singleton();

		$query = "codtipodoc='".$this->fields->codtipodoc->getNewValue()."' AND anno='".$p4a->e3g_azienda_anno_contabile."' AND numdocum='".$this->mask->numerodoc->getNewValue()."'";
		$this->mask->data->set_where($query);
		$this->mask->data->load();
		$this->mask->reload_row();

		$this->carica_campi();


		//$this->gestione_campi();

	}

	function tutti_click()
	{

		$query = "1=1";
		$this->mask->data->set_where($query);
		$this->mask->data->load();
		$this->mask->reload_row();


		$this->carica_campi();


		//$this->gestione_campi();


	}


	function dopo_record_cambiato()
	{
		$this->carica_campi();

		//$this->gestione_campi();
	}




	function dopo_nuova_riga()
	{
		//$this->nuovariga = 1;
	}



	function ricerca_click()
	{
		$db =& p4a_db::singleton();
		$p4a =& p4a::singleton();
		
		
		///////////////////////////////////////////////////////////////
		//ROUTINE PER CARICARE I CAMPI dal RETURN PRESS   			//
		/////////////////////////////////////////////////////////////
		if ($this->fields->codice->getNewValue() != '')
		{
			// vado a cercare l'articolo cercando il codice o il barcode
			$this->build("p4a_db_source", "ds_art");
    		$this->ds_art->setTable($p4a->e3g_prefix."articoli");
    		$this->ds_art->setPk("idarticolo");
    		$query = "codice='".$this->fields->codice->getNewValue()."' OR barcode='".$this->fields->codice->getNewValue()."'";
    		$this->ds_art->setWhere($query);
    		//$this->ds_art->setPageLimit(10);
			$this->ds_art->load();
			
			$this->ds_art->firstRow();
		

			$numerorighe = $this->ds_art->getNumRows();	
			if ($numerorighe > 0)
			{	
				$this->vengodaricerca = 1;			
				$this->fields->codice->setValue($this->ds_art->fields->codice->getValue());
					
				$this->fields->descrizione->setNewValue($this->ds_art->fields->descrizione->getValue());
				$this->fields->barcode->setNewValue($this->ds_art->fields->barcode->getValue());
				if (!is_numeric($this->fields->prezzo->getValue()))
				{
					$this->fields->prezzo->setValue($this->ds_art->fields->prezzoven->getValue());
				}											
				if ($this->sconto_cli == 0 )
				{
					if (!is_numeric($this->fields->sconto->getValue()))
					{
						$this->fields->sconto->setValue($this->ds_art->fields->sconto1->getValue());
					}											
				}					
				else
				{
					$this->fields->sconto->setValue($this->sconto_cli);
				}											
				$this->fields->codiva->setNewValue($this->ds_art->fields->codiva->getValue());
				if (!is_numeric($this->fields->quantita->getValue()))
				{
					$this->fields->quantita->setValue(1);
				}											
				
				
				$tipocf = $db->queryOne("SELECT tipoanagrafica FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$this->codtipodoc."'");
								
				switch ($tipocf) {
				    case 'C':						
						$this->fields->conto->setNewValue($this->ds_art->fields->contovendita->getValue());
				    	break;

				    case 'F':
				    	$this->fields->conto->setNewValue($this->ds_art->fields->contoacquisto->getValue());
				    	break;

				    case '':
				    	break;
				}
				
				
				    	
				//$this->aggiungi_click();
				
				$this->setFocus($this->fields->quantita);
				//$this->setFocus($this->codicefinto);
				
				
				
			}
			else
			{
			 	// non ho trovato niente
			}

		}
		else
		{
			// non ho il codice articolo --> niente
		}

				

	}

	function carica_campi()
	{
		///////////////////////////////////////////////////////////////
		//ROUTINE PER CARICARE I CAMPI NON ASSOCIATI AL DB_SOURCE   //
		/////////////////////////////////////////////////////////////
		
	}




	function elimina_click()
	{
		// vado ad eliminare prima i movimenti magazzino

		// poi elimino la riga
		$this->mask->delete_row();

		$this->mask->new_row();
	}

	function salva_click()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();

		// calcolo i totali del documento
		if ( E3G_TIPO_GESTIONE == 'G' )
		{
			$totale_t = $db->queryOne("SELECT SUM(totale) FROM ".$p4a->e3g_prefix."docr WHERE visibile='S' AND iddocr=".$this->iddoc);
			$imposta_t = $db->queryOne("SELECT SUM(imposta) FROM ".$p4a->e3g_prefix."docr WHERE visibile='S' AND iddocr=".$this->iddoc);
			$imponibile_t = $db->queryOne("SELECT SUM(imponibile) FROM ".$p4a->e3g_prefix."docr WHERE visibile='S' AND iddocr=".$this->iddoc);
		}
		else 
		{
			$totale_t = $db->queryOne("SELECT SUM(totale) FROM ".$p4a->e3g_prefix."docr WHERE iddocr=".$this->iddoc);
			$imposta_t = $db->queryOne("SELECT SUM(imposta) FROM ".$p4a->e3g_prefix."docr WHERE iddocr=".$this->iddoc);
			$imponibile_t = $db->queryOne("SELECT SUM(imponibile) FROM ".$p4a->e3g_prefix."docr WHERE iddocr=".$this->iddoc);
		}
	
	
	
		//Aggiorno DocT
		$query = "UPDATE ".$p4a->e3g_prefix."doct SET totdoc=".$totale_t.", imponibile=".$imponibile_t.", imposta=".$imposta_t." WHERE codtipodoc='".$this->codtipodoc."' AND numdocum='".$this->numdoc."' AND anno='".$p4a->e3g_azienda_anno_contabile."'";
		
		$db->query($query);


		//Creo una Tabella riepilogativa degli imponibili per iva ed imposto il data_Source
		//$my_array = $db->queryAll("SELECT codiva, SUM(imponibile) AS imponibile, SUM(imposta) AS imposta FROM ".$p4a->e3g_prefix."docr WHERE numdocum=".$this->numdoc." AND codtipodoc='".$this->codtipodoc."' AND anno='".$this->annocontabile."' GROUP BY codiva");
		//$data_coda =& new data_source('data_coda');
		//$data_coda->load_with_pk($my_array,'codiva');
		//$this->mask->add_object($data_coda);


		require("class.movimenti.php");
		$mov = new Cmovimenti();		
					
		// controllo se devo salvare i movimenti di magazzino
		$genmovmag = $db->queryOne("SELECT genmovmag FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$this->codtipodoc."'");
		if ($genmovmag == 'S')
		{			
			$mov->movimentimag($this->codtipodoc, $this->numdoc, $this->codclifor, $this->strdata, $p4a->e3g_azienda_anno_contabile, $this->iddoc);
		}

		// controllo se devo salvare i movimenti contabili
		$genmovcon = $db->queryOne("SELECT genmovcon FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$this->codtipodoc."'");
		if ($genmovcon == 'S')
		{
			// in prova
			$mov->movimenticon($this->codtipodoc, $this->numdoc, $this->codclifor, $this->strdata, $p4a->e3g_azienda_anno_contabile, $this->iddoc);
		}


		$nomereport = $db->queryOne("SELECT nomeReport1 FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$this->codtipodoc."'");
		if ($nomereport != '')
		{
			// controllo if file exist
			//return $p4a->openReport($nomereport);
		}
		else
		{
			// controllo if file exist
			//return $p4a->openMask("anagarticoli");
		}

	}


	
	function gestione_campi()
	{

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
			$field->label->setWidth(140);
		}


		$this->fields->idriga->setVisible();
	}


	function data_to_sql($miadata)
	{
		// funzione per la conversione della data da un formato "umano" mm/gg/aa o mm/dd/aaaa
		// al formato utilizzato da mysql aaaa-mm-gg
		if ($miadata != "")
		{
			$pos1 = strpos ($miadata, '/');
			$pos2 = strpos ($miadata, '/',$pos1 + 1);
			$day = substr ($miadata, 0,$pos1);
			$month = substr ($miadata,$pos1 + 1,$pos2 - $pos1 - 1);
			$year = substr ($miadata,$pos2 + 1,strlen($miadata) - $pos2 - 1);

			return $year."-".$month."-".$day;
		}
		else 
		{
			return "";
		}
		

	}

	function sql_to_data($miadata)
	{
		// funzione per la conversione della data da un formato "umano" mm/gg/aa o mm/dd/aaaa
		// al formato utilizzato da mysql aaaa-mm-gg

		if ($miadata != "")
		{
			$pos1 = strpos ($miadata, '-');
			$pos2 = strpos ($miadata, '-',$pos1 + 1);
			$year = substr ($miadata, 0,$pos1);
			$month = substr ($miadata,$pos1 + 1,$pos2 - $pos1 - 1);
			$day = substr ($miadata,$pos2 + 1,strlen($miadata) - $pos2 - 1);

			return $day."/".$month."/".$year;
		}
		else 
		{
			return "";
		}

	}


	function calcola_tot_riga()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();

		$iva = $db->queryOne("SELECT iva FROM ".$p4a->e3g_prefix."aliquoteiva WHERE codice='".$this->fields->codiva->getNewValue()."'");

		$quantita = $this->fields->quantita->getUnformattedNewValue();
		if ($quantita >0)
        {

       	}
       	else
       	{
       		$quantita = 0;
       	}

		$prezzo = $this->fields->prezzo->getUnformattedNewValue();
		if ($prezzo >0)
        {

       	}
       	else
       	{
       		$prezzo = 0;
       	}

		$sconto = $this->fields->sconto->getUnformattedNewValue();
		if ($sconto >0)
        {

       	}
       	else
       	{
       		$sconto = 0;
       	}

		
       	$imponibile = round((($quantita * $prezzo) * (1 - $sconto / 100) / (100 + $iva)) * 100, 2);
        $imposta = round((($quantita * $prezzo) * (1 - $sconto / 100)) - $imponibile, 2);
        $totriga = $imponibile + $imposta ; // (($quantita * $prezzo) * (1 - $sconto / 100));
     
        $this->fields->imponibile->setValue(round($imponibile, 2));
        $this->fields->imposta->setValue(round($imposta,2));
        $this->fields->totale->setValue(round($totriga,2));
		

        return 0;

	}


	function carica_doct()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();

		$this->iddoc = $p4a->masks->gesdocumenti1->iddoc;
		$this->codtipodoc = $p4a->masks->gesdocumenti1->fld_cod_tipo_doc->getNewValue();
		$this->numdoc = $p4a->masks->gesdocumenti1->numerodoc->getNewValue();
		$this->codclifor = $p4a->masks->gesdocumenti1->codclifor;
		
		$this->strdata = $p4a->masks->gesdocumenti1->strdata;
		$this->nomeclifor = $db->queryOne("SELECT descrizione FROM ".$p4a->e3g_prefix."anagrafiche WHERE codice='".$this->codclifor."'");
		$this->sconto_cli = $db->queryOne("SELECT sconto FROM ".$p4a->e3g_prefix."anagrafiche WHERE codice='".$this->codclifor."'");
		if (!is_numeric($this->sconto_cli))
		{
			$this->sconto_cli = 0; 
		}
	
		
		return 0;

	}

	function carica_doct2()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();

		$this->nometipodoc = $db->queryOne("SELECT descrizione FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$this->codtipodoc."'");
		$this->tipodoc->setValue('tipo Documento: '.$this->nometipodoc);
		$this->clifor->setValue($this->nomeclifor);
		$this->datadoc->setValue("del ".$this->sql_to_data($this->strdata));
		$this->numerodoc->setValue($this->numdoc);
		
		return 0;

	}
	
		
	function saveRow()
	{
		$db =& p4a_db::singleton();
		$p4a =& p4a::singleton();
		
		
		if (is_numeric($this->fields->idriga->getNewValue()))
		{
    		// ho l'idriga quindi sono su di un articolo gia' esistente
			$idriga = $this->fields->idriga->getNewValue();    		
    	}
		else
		{
			// ho gia' l'idriga quindi sono su di un articolo gia' esistente
    		
    		// Id progressivo univoco delle righe
    		$idriga = $db->queryOne("SELECT idriga FROM ".$p4a->e3g_prefix."docr ORDER BY idriga DESC");
    		$idriga++;
			
    		// numero della riga
    		$nriga = $db->queryOne("SELECT nriga FROM ".$p4a->e3g_prefix."docr WHERE codtipodoc='".$this->codtipodoc."' AND anno='".$p4a->e3g_azienda_anno_contabile."' AND numdocum='".$this->numdoc."' ORDER BY nriga DESC");

    		if (is_numeric ($nriga))
    		{
    			$nriga++;
    		}
    		else
    		{
    			$nriga = 1;
    		}
    		    		
    		
    		$this->fields->idriga->setNewValue($idriga);
    		$this->fields->nriga->setNewValue($nriga);
		}
		
		
		if (is_numeric($this->fields->quantita->getValue()))
		{
		}
		else
		{
			$this->fields->quantita->setValue(1);
		}
		
		if (is_numeric($this->fields->quantita2->getValue()))
		{
		}
		else
		{
			$this->fields->quantita2->setValue(0);
		}
		
		if (!is_numeric($this->fields->sconto->getValue()))
		{
			$this->fields->sconto->setValue(0);
		}
		
		$this->fields->delta_prezzo->setValue(0);
		$this->fields->codtipodoc->setNewValue($this->codtipodoc);
		$this->fields->numdocum->setNewValue($this->numdoc);
		$this->fields->anno->setNewValue($p4a->e3g_azienda_anno_contabile);
		$this->fields->iddocr->setNewValue($this->iddoc);
		
		// LA METTO DOPO NELLA CODA DEL DOCUMENTO
		//if ($this->data == "")
		//{
		//	$this->data =date("Y-m-d");
		//}
		//$this->fields->data->setNewValue($this->data);


		
		$this->fields->visibile->setNewValue("S");
		
			
		//faccio tutti i calcoli totriga imponibile...
		//calcolo tot riga 
		$this->calcola_tot_riga();
		$query = "UPDATE ".$p4a->e3g_prefix."docr SET totale=".$this->fields->totale->getValue().", imponibile=".$this->fields->imponibile->getValue().", imposta=".$this->fields->imposta->getValue()." WHERE idriga=".$idriga;
		$db->query($query);
	
						
		//AGGIORNO RIGA NEL CARRELLO se il documento e' estratto 
		//Gesti.GAS

		if ($this->codtipodoc =="00024")
		{
			// QTA = QTA 2 --> la riga e' chiusa 
			if ($this->fields->quantita2->getValue() == $this->fields->quantita->getValue())
    		{
   				if (is_numeric($this->fields->rifidriga->getNewValue()))
        		{
       				// ho un riferimento alla riga estratta del carrello 
					// Quindi chiudo la riga 
					$query = "UPDATE ".$p4a->e3g_prefix."carrello SET stato='C' WHERE idriga=".$this->fields->rifidriga->getNewValue();
					$db->query($query);
       			}
    		}	
		}	
		
		// il saveRow va messo dopo le altre operazioni perchè altrimenti perdo il riferimento alla riga corrente
		parent::saveRow();

		// calcolo i totali del documento 
		if ( E3G_TIPO_GESTIONE == 'G' )
		{
			//echo "SELECT SUM(totale) FROM ".$p4a->e3g_prefix."docr WHERE visibile='S' iddocr=".$this->iddoc;
			//echo "<br>SELECT SUM(imponibile) FROM ".$p4a->e3g_prefix."docr WHERE visibile='S' AND iddocr=".$this->iddoc;
			
			$totale_t = $db->queryOne("SELECT SUM(totale) FROM ".$p4a->e3g_prefix."docr WHERE visibile='S' AND iddocr=".$this->iddoc);
			$imposta_t = $db->queryOne("SELECT SUM(imposta) FROM ".$p4a->e3g_prefix."docr WHERE visibile='S' AND iddocr=".$this->iddoc);
			$imponibile_t = $db->queryOne("SELECT SUM(imponibile) FROM ".$p4a->e3g_prefix."docr WHERE visibile='S' AND iddocr=".$this->iddoc);
		}
		else 
		{
			$totale_t = $db->queryOne("SELECT SUM(totale) FROM ".$p4a->e3g_prefix."docr WHERE iddocr=".$this->iddoc);
			$imposta_t = $db->queryOne("SELECT SUM(imposta) FROM ".$p4a->e3g_prefix."docr WHERE iddocr=".$this->iddoc);
			$imponibile_t = $db->queryOne("SELECT SUM(imponibile) FROM ".$p4a->e3g_prefix."docr WHERE iddocr=".$this->iddoc);
		}
	
	
	
		//Aggiorno DocT
		$query = "UPDATE ".$p4a->e3g_prefix."doct SET totdoc=".$totale_t.", imponibile=".$imponibile_t.", imposta=".$imposta_t." WHERE iddoc=".$this->iddoc;
		//echo "<br>".$query;
		//die;
		$db->query($query);
					
		//parent::newRow();
		//$this->setFocus($this->fields->codice);
		$this->setFocus($this->bu_nuovariga);
		
		//$this->setFocus($this->codicefinto);

	}
	
	
	function bu_nuovariga_click()
	{
		$this->newRow();
		
	}
	
	function newRow()
	{
		$this->vengodaricerca = 1;
		parent::newRow();
		$this->setFocus($this->fields->codice);
		//$this->setFocus($this->codicefinto);
	}
	
	function aggiungi_click()
	{
		$this->vengodaricerca = 1;			
		$this->saveRow();
	}
	
	function tab_row_click()
	{
		$this->vengodaricerca = 1;			
	}
	
	function nextRow()
	{
		$this->vengodaricerca = 1;			
		parent::nextRow();
	}
	
	function prevRow()
	{
		$this->vengodaricerca = 1;			
		parent::prevRow();
	}
	
	function firstRow()
	{
		$this->vengodaricerca = 1;			
		parent::firstRow();
	}
	
	function lastRow()
	{
		$this->vengodaricerca = 1;			
		parent::lastRow();
	}
	
	function stampa_click()
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();

		$codtipopag = $db->queryOne("SELECT codtipopag FROM ".$p4a->e3g_prefix."doct WHERE iddoc=".$this->iddoc);
				
		genera_stampa_pdf($this->numdoc, $this->iddoc, $this->codtipodoc, $this->codclifor, $codtipopag, 0); // lo 0 indica che vengono mostrate solo le righe visibili

	}

	function caricasource()
	{
		//$this->build("p4a_db_source", "ds_docr");
		//$this->ds_docr->setTable($p4a->e3g_prefix."docr");
		//$this->ds_docr->setPk("idriga");
		//$this->ds_docr->setWhere("iddocr=".$this->iddoc);
		//$this->ds_docr->addOrder("iddocr");
		//$this->ds_docr->addOrder("nriga");
		//$this->ds_docr->load();

		$this->ds_docr->setWhere("visibile='S' AND iddocr=".$this->iddoc);
		$this->ds_docr->addOrder("iddocr");
		$this->ds_docr->addOrder("nriga");
		
		$this->ds_docr->firstRow();

		//$this->setSource($this->ds_docr);
		//$this->tab_row->setSource($this->ds_docr);

	}


	function etichette_click ()
	{
		$db =& p4a_db::singleton();
		$p4a =& p4a::singleton();
		

		$this->build("p4a_db_source", "ds_eti");
		$this->ds_eti->setTable($p4a->e3g_prefix."docr");
		$this->ds_eti->setPk("idriga");
		$this->ds_eti->setQuery("SELECT ".$p4a->e3g_prefix."articoli.barcode FROM ".$p4a->e3g_prefix."articoli INNER JOIN ".$p4a->e3g_prefix."docr ON ".$p4a->e3g_prefix."docr.codice = ".$p4a->e3g_prefix."articoli.codice WHERE ".$p4a->e3g_prefix."docr.iddocr=".$this->iddoc);	
		$this->ds_eti->load();
		$this->ds_eti->firstRow();
		
		// tolgo i caratteri strani dalla stringa del nome file
		// questo perchè il num.doc. potrebbe essere ad es. 100/bis
		$tmpnum = $this->numdoc;
		for ($num = 1; $num<=254; $num += 1)
		{		
		    switch (TRUE){
		        case ($num <= 57 AND $num >= 48):    
					break;
		        case ($num <= 90 AND $num >= 65):    
		        	break;
		        case ($num <= 122 AND $num >= 97):    
		        	break;                           
		        default:                
		            $tmpnum2 = str_replace(chr($num), "_", $tmpnum);
		    		$tmpnum = $tmpnum2;
		        	break;
		    }
		}
		
		
		$documento = $tmpnum."_".$this->codtipodoc."_";
		
		// scorro le righe  
		$riga = 1 ;
		$ogni6  = 1 ;
		$kount = 1 ; 
		
		while($riga<=$this->ds_eti->getNumRows())
		{		
			$stampate = 0; 
			switch ($ogni6) 
			{
				case 1:
					$cod1 = $this->ds_eti->fields->barcode->getNewValue();
				    break;
				case 2:
					$cod2 = $this->ds_eti->fields->barcode->getNewValue();
				    break;
				case 3:
					$cod3 = $this->ds_eti->fields->barcode->getNewValue();
				    break;
				case 4:
					$cod4 = $this->ds_eti->fields->barcode->getNewValue();
				    break;
				case 5:
					$cod5 = $this->ds_eti->fields->barcode->getNewValue();
				    break;
				case 6:
					$cod6 = $this->ds_eti->fields->barcode->getNewValue();
					////echo $cod1." - ".$cod2." - ".$cod3." - ".$cod4." - ".$cod5." - ".$cod6."<br>";
					$this->stampa_etichette($cod1, $cod2, $cod3, $cod4, $cod5, $cod6, $documento.$kount.".png");
					unset($cod1, $cod2, $cod3, $cod4, $cod5, $cod6);
					
					//$cod1 = "";
					//$cod2 = "";
					//$cod3 = "";
					//$cod4 = "";
					//$cod5 = "";
					//$cod6 = "";
					
					$ogni6 = 0; 
					$stampate = 1; 
				    $kount++;
					break;
			}
						
			$this->ds_eti->nextRow();
			$ogni6++;
			$riga++;
		}
		//echo $stampate."<br>";
		
		if ($stampate != 1)
		{
			//echo $cod1." - ".$cod2." - ".$cod3." - ".$cod4." - ".$cod5." - ".$cod6."<br>";
			$this->stampa_etichette($cod1, $cod2, $cod3, $cod4, $cod5, $cod6, $documento.$kount.".png");
			$kount++;
		}
		
	}

	
	function stampa_etichette($cod1, $cod2, $cod3, $cod4, $cod5, $cod6, $nomefile)
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
	
		require_once(dirname(__FILE__) . "/../config.php");
		require_once( P4A_ROOT_DIR . "/p4a/include.php");
		require_once(dirname(__FILE__) . "/../libraries/Image/Barcode.php");

		$num     = '800000011111';
		$type    = 'ean13';
		$imgtype = 'png';

		
		$numcols = 2;
		$numrows = 3;
		$ystart = 10;
		$xstart = 10;
		
		unset($arr);
        $arr[] = $cod1;
        $arr[] = $cod2;
        $arr[] = $cod3;
        $arr[] = $cod4;
        $arr[] = $cod5;
    	$arr[] = $cod6;
    	
    	unset($Array);
		$Array = array();
    	for ($riga = 0; $riga < 6; $riga++) {  
    		
    		switch ($riga) {
                case 0:
            		$codice = $db->queryOne("SELECT codice FROM ".$p4a->e3g_prefix."articoli WHERE barcode='".$cod1."'");
            		$descrizione = $db->queryOne("SELECT descrizione FROM ".$p4a->e3g_prefix."articoli WHERE barcode='".$cod1."'");
            		$prezzo = $db->queryOne("SELECT prezzoven FROM ".$p4a->e3g_prefix."articoli WHERE barcode='".$cod1."'");
            		$iva = $db->queryOne("SELECT codiva FROM ".$p4a->e3g_prefix."articoli WHERE barcode='".$cod1."'");;
            		$paese = $db->queryOne("SELECT paese FROM ".$p4a->e3g_prefix."articoli WHERE barcode='".$cod1."'");                         
                    break;
                case 1:
            		$codice = $db->queryOne("SELECT codice FROM ".$p4a->e3g_prefix."articoli WHERE barcode='".$cod2."'");
            		$descrizione = $db->queryOne("SELECT descrizione FROM ".$p4a->e3g_prefix."articoli WHERE barcode='".$cod2."'");
            		$prezzo = $db->queryOne("SELECT prezzoven FROM ".$p4a->e3g_prefix."articoli WHERE barcode='".$cod2."'");
            		$iva = $db->queryOne("SELECT codiva FROM ".$p4a->e3g_prefix."articoli WHERE barcode='".$cod2."'");;
            		$paese = $db->queryOne("SELECT paese FROM ".$p4a->e3g_prefix."articoli WHERE barcode='".$cod2."'");                         
        	        break;
                case 2:
            		$codice = $db->queryOne("SELECT codice FROM ".$p4a->e3g_prefix."articoli WHERE barcode='".$cod3."'");
            		$descrizione = $db->queryOne("SELECT descrizione FROM ".$p4a->e3g_prefix."articoli WHERE barcode='".$cod3."'");
            		$prezzo = $db->queryOne("SELECT prezzoven FROM ".$p4a->e3g_prefix."articoli WHERE barcode='".$cod3."'");
            		$iva = $db->queryOne("SELECT codiva FROM ".$p4a->e3g_prefix."articoli WHERE barcode='".$cod3."'");;
            		$paese = $db->queryOne("SELECT paese FROM ".$p4a->e3g_prefix."articoli WHERE barcode='".$cod3."'");                         
        	        break;
                case 3:
            		$codice = $db->queryOne("SELECT codice FROM ".$p4a->e3g_prefix."articoli WHERE barcode='".$cod4."'");
            		$descrizione = $db->queryOne("SELECT descrizione FROM ".$p4a->e3g_prefix."articoli WHERE barcode='".$cod4."'");
            		$prezzo = $db->queryOne("SELECT prezzoven FROM ".$p4a->e3g_prefix."articoli WHERE barcode='".$cod4."'");
            		$iva = $db->queryOne("SELECT codiva FROM ".$p4a->e3g_prefix."articoli WHERE barcode='".$cod4."'");;
            		$paese = $db->queryOne("SELECT paese FROM ".$p4a->e3g_prefix."articoli WHERE barcode='".$cod4."'");                         
        	        break;
                case 4:
            		$codice = $db->queryOne("SELECT codice FROM ".$p4a->e3g_prefix."articoli WHERE barcode='".$cod5."'");
            		$descrizione = $db->queryOne("SELECT descrizione FROM ".$p4a->e3g_prefix."articoli WHERE barcode='".$cod5."'");
            		$prezzo = $db->queryOne("SELECT prezzoven FROM ".$p4a->e3g_prefix."articoli WHERE barcode='".$cod5."'");
            		$iva = $db->queryOne("SELECT codiva FROM ".$p4a->e3g_prefix."articoli WHERE barcode='".$cod5."'");;
            		$paese = $db->queryOne("SELECT paese FROM ".$p4a->e3g_prefix."articoli WHERE barcode='".$cod5."'");                         
        	        break;
                case 5:
            		$codice = $db->queryOne("SELECT codice FROM ".$p4a->e3g_prefix."articoli WHERE barcode='".$cod6."'");
            		$descrizione = $db->queryOne("SELECT descrizione FROM ".$p4a->e3g_prefix."articoli WHERE barcode='".$cod6."'");
            		$prezzo = $db->queryOne("SELECT prezzoven FROM ".$p4a->e3g_prefix."articoli WHERE barcode='".$cod6."'");
            		$iva = $db->queryOne("SELECT codiva FROM ".$p4a->e3g_prefix."articoli WHERE barcode='".$cod6."'");;
            		$paese = $db->queryOne("SELECT paese FROM ".$p4a->e3g_prefix."articoli WHERE barcode='".$cod6."'");                         
        	        break;
            }
			
			
    	   	$Array[$riga]=array("codice" => $codice." " , "descrizione" => substr($descrizione, 0, 30)." ", "prezzo" => $prezzo." ", "Iva" => $iva." ", "Paese" => $paese." ");		
    	   	
    	}
    	 
    	Image_Barcode::draw($num, $type, $imgtype, $numcols , $numrows , $ystart , $xstart, $arr, $Array, $nomefile);
	}	
	
}	

?>