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

class documentiestratti extends P4A_Mask
{
	
	// questo modulo e' stato inserito per il progetto Gesti.GAS


	// Variabile per definire il tipo Anagrafica Cliente / Fornitore
	var $codicetipodoc = '';

	function &documentiestratti()
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();

		$this->build("p4a_db_source", "ds_doct");
		$this->ds_doct->setTable($p4a->e3g_prefix."docr");
		//$this->ds_doct->addOrder("numdocum");
		//$this->ds_doct->addOrder("codice");

		//if ($_SESSION['admin']=="S")
		if ($p4a->e3g_utente_tipo == "A" || $p4a->e3g_utente_tipo == "AS" || $p4a->e3g_utente_tipo == "R")
		{
			$this->ds_doct->setWhere("visibile = 'N' AND codtipodoc='00024'");
		} 
		else
		{
			$this->ds_doct->setWhere("visibile = 'N' AND codtipodoc='00024' AND codutente='$p4a->e3g_utente_codice'");
		}
		$this->ds_doct->setPk('idriga');
		$this->ds_doct->addOrder("data");
		$this->ds_doct->addOrder("codutente");
		
		$this->ds_doct->load();
		
		$this->setSource($this->ds_doct);
		$this->ds_doct->firstRow();


		$tab_row =& $this->build("p4a_table", "tab_row");
		$tab_row->setWidth(730);
		$tab_row->setTitle('Ordini');
		$tab_row->setSource($this->ds_doct);
		$tab_row->setVisibleCols(array("codice", "descrizione","quantita","prezzo","codutente"));
				
		// TIPO DOCUMENTO
		$this->build("p4a_db_source", "ds_anag");
		$this->ds_anag->setTable($p4a->e3g_prefix."anagrafiche");
		$this->ds_anag->setWhere("tipocfa='C'");		
		$this->ds_anag->setPk("codice");
		$this->ds_anag->load();
		
		

		$this->tab_row->cols->codiva->setLabel('iva');
		$this->tab_row->cols->numdocum->setLabel('Num. Docum.');
		$this->tab_row->cols->data->setLabel('data Docum.');
		
		$tab_row->cols->codutente->setLabel('Utente');
		$tab_row->cols->codutente->setSource($this->ds_anag);
		$tab_row->cols->codutente->setSourceValueField("codice");
		$tab_row->cols->codutente->setSourceDescriptionField("descrizione");
		
		
		
		//Aggiungo alla maschera una nuova standard toolbar.
		// Toolbar
		$this->build("p4a_standard_toolbar", "toolbar");
		$this->toolbar->setMask($this);


		//Setto il titolo della maschera
		$this->SetTitle('Estrazione Ordini Estratti');

		// Message
		$message =& $this->build("p4a_message", "message");
		$message->setWidth("300");


		//$sh_campi =& $this->build("p4a_sheet", "sh_campi");
        //$this->sh_testa->defineGrid(3, 2);
		//$this->sh_testa->setWidth(730);

		$frm=& $this->build("p4a_frame", "frm");
		$frm->setWidth(800);
		$frm->anchor($message);
		//$frm->anchor($this->sh_campi);
		$frm->anchor($this->tab_row);
		
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

	function estraiart_click()
	{
		$p4a =& p4a::singleton();
		
		// estraggo le righe con l'articolo indicato
		// genero l'ordine a fornitore 
		// setto le righe Stato = A --> Stato = L
		$fornitore = $this->mask->codforn->get_new_value();
		$articolo = $this->mask->codart->get_new_value();
		$codtipodoc ="00024";	
		$datadoc = data_to_sql($this->mask->flddata->get_new_value());
		
		// DocT
		// iddoc codclifor numdocum codtipodoc anno
		// DocR
		// idriga iddocr anno codtipodoc numdocum

		$registro = $this->merlin->db->queryOne("SELECT codregdoc FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$codtipodoc."'");
		$numdocum = $this->merlin->db->queryOne("SELECT MAX(seriale) FROM ".$p4a->e3g_prefix."docregistri WHERE codice='".$registro."'");
		$numdocum++;
		$query = "UPDATE ".$p4a->e3g_prefix."docregistri SET seriale=".$numdocum." WHERE codice='".$registro."'";
		$this->merlin->db->query($query);
		
		$iddoc = $this->merlin->db->queryOne("SELECT iddoc FROM ".$p4a->e3g_prefix."doct ORDER BY iddoc DESC");
		$iddoc++;	
		
		// Inserisco la testa del documento 
		$this->merlin->db->query("INSERT INTO FROM ".$p4a->e3g_prefix."doct (iddoc,codclifor,numdocum,codtipodoc,anno,data) VALUES (".$iddoc.",'".$fornitore."',".$numdocum.",'".$codtipodoc."','".$p4a->e3g_azienda_anno_contabile."','".$datadoc."')");
		
		// Inserisco le righe 
		$ds_docr =& new db_source('ds_docr');
		$this->mask->add_object($ds_docr);
		$ds_docr->load_from_table('docr');
		$ds_docr->set_pk('idriga');

		$riga = $this->merlin->db->queryOne("SELECT idriga FROM ".$p4a->e3g_prefix."docr ORDER BY idriga DESC");
		while($this->mask->data->get_num_rows())
		{
			$riga++;
			$row = $this->mask->data->get_current_row();

			// Creo i Movimenti di Magazzino per lo scontrino appena registrato
			$docr['idriga'] = $riga;			
			$docr['codarticolo'] = $row['codarticolo'];
			$docr['iddocr'] = $iddoc;
			$docr['anno'] = $p4a->e3g_azienda_anno_contabile;
			$docr['codtipodoc'] = $codtipodoc;
			$docr['data'] = $datadoc;
			$docr['numdocum'] = $numdocum;
			$docr['quantita'] = $row['qta'];
			$docr['codiva'] = $row['codiva'];
			$docr['prezzo'] = $row['prezzoven'];
			
			$ds_docr->insert_row($docr);

			// cancello la tabella temporanea carrello
			$this->mask->delete_row();

			$riga++;

		}
	
		
	}

	function estraifam_click()
	{
		// estraggo le righe con la famiglia indicata
		// genero l'ordine a fornitore 
		// setto le righe Stato = A --> Stato = L
		$fornitore = $this->mask->codforn->get_new_value();
		$famiglia = $this->mask->codanag->get_new_value();
			
	}

	function estraiforn_click()
	{
		// estraggo le righe con il fornitore indicato
		// genero l'ordine a fornitore 
		// setto le righe Stato = A --> Stato = L
		$fornitore = $this->mask->codforn->get_new_value();
		
		$this->mask->data->drop_filter('codfornitore');
		$this->mask->data->add_filter('codfornitore', '=', $fornitore);
		$this->mask->data->load();
			
		$codtipodoc ="00024";	
		$datadoc = $this->data_to_sql($this->mask->flddata->get_new_value());
		
		
		// DocT
		// iddoc codclifor numdocum codtipodoc anno
		// DocR
		// idriga iddocr anno codtipodoc numdocum

		$registro = $this->merlin->db->queryOne("SELECT codregdoc FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$codtipodoc."'");
		$numdocum = $this->merlin->db->queryOne("SELECT MAX(seriale) FROM ".$p4a->e3g_prefix."docregistri WHERE codice='".$registro."'");
		$numdocum++;
		$query = "UPDATE ".$p4a->e3g_prefix."docregistri SET seriale=".$numdocum." WHERE codice='".$registro."'";
		$this->merlin->db->query($query);
		
		$iddoc = $this->merlin->db->queryOne("SELECT iddoc FROM ".$p4a->e3g_prefix."doct ORDER BY iddoc DESC");
		$iddoc++;	
		
		// Inserisco la testa del documento 
		$this->merlin->db->query("INSERT INTO doct (iddoc,codclifor,numdocum,codtipodoc,anno,data) VALUES (".$iddoc.",'".$fornitore."',".$numdocum.",'".$codtipodoc."','".$p4a->e3g_azienda_anno_contabile."','".$datadoc."')");
		
		// Inserisco le righe 
		$ds_docr =& new db_source('ds_docr');
		$this->mask->add_object($ds_docr);
		$ds_docr->load_from_table('docr');
		$ds_docr->set_pk('idriga');

		$riga = $this->merlin->db->queryOne("SELECT idriga FROM ".$p4a->e3g_prefix."docr ORDER BY idriga DESC");
		$riga++;
		$rigarel = 1 ;
		while($rigarel <= $this->mask->data->get_num_rows())
		{
			$row = $this->mask->data->get_current_row();

			// Creo i Movimenti di Magazzino per lo scontrino appena registrato
			$docr['idriga'] = $riga;			
			$docr['codice'] = $row['codarticolo'];
			$docr['iddocr'] = $iddoc;
			$docr['anno'] = $p4a->e3g_azienda_anno_contabile;
			$docr['codtipodoc'] = $codtipodoc;
			$docr['data'] = $datadoc;
			$docr['numdocum'] = $numdocum;
			$docr['quantita'] = $row['qta'];
			$docr['codiva'] = $row['codiva'];
			$docr['prezzo'] = $row['prezzoven'];
			$docr['nriga'] = $rigarel;
			
			
			$ds_docr->insert_row($docr);

			// cancello la tabella temporanea carrello
			$this->mask->fields['stato']->set_value("L");
			$this->mask->update_row();
			
			$this->mask->data->move_next();
			
			$rigarel++;
			$riga++;
		
		}
			
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
	

}

?>