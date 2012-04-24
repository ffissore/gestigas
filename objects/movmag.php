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

class movmag extends P4A_Mask
{
	// Variabile per definire il tipo Anagrafica Cliente / Fornitore
	var $codtipodoc ='';
	var $numdoc = 0;
	var $date = '';
	var $codclifor = '';
	var $iddoc = 0;
	
	function &movmag()
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();
	
		$this->build("p4a_db_source", "ds_magmagr");
		$this->ds_magmagr->setTable($p4a->e3g_prefix."movmagr");
		$this->ds_magmagr->setWhere("1=0");
		$this->ds_magmagr->load();
		$this->setSource($this->ds_magmagr);
		$this->ds_magmagr->firstRow();
	}

	function main()
	{
				
	}

			
	function movimentimag($strcodtipodoc, $strnumdoc, $strcodclifor, $strdate, $strannocontabile, $striddoc)
	{
		$this->p4a_mask();
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
		$this->codtipodoc = $strcodtipodoc; 
		$this->numdoc = $strnumdoc;
		$this->codclifor = $strcodclifor;
		$this->date = $strdate;
		$this->iddoc = $striddoc;
	
		$this->build("p4a_db_source", "ds_magmagr");
		$this->ds_magmagr->setTable($p4a->e3g_prefix."movmagr");
		$this->ds_magmagr->setWhere("1=0");
		$this->ds_magmagr->setPK("idriga");
		$this->ds_magmagr->load();
		$this->setSource($this->ds_magmagr);
		$this->ds_magmagr->firstRow();

		
		// Se manca anche solo un parametro
		if ($this->codtipodoc=='' || $this->numdoc=='' || $p4a->e3g_azienda_anno_contabile=='')
		{
			return 0;
		}

		// Controllo che il Documento Generi Movimenti di Magazzino (il flag genmovmag sul tipo doc deve essere S)
		if ($db->queryOne("SELECT genmovmag FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice=".$this->codtipodoc) != 'S')
		{
			return 0;
		}

		// Recupero Causale Movimento e Flag Carico / Scarico
		$causalemov = $db->queryOne("SELECT codcaumag FROM ".$p4a->e3g_prefix."doctipidoc WHERE codice='".$this->codtipodoc."'");
		$carscar = $db->queryOne("SELECT carscar FROM ".$p4a->e3g_prefix."movmagcausali WHERE codice='".$causalemov."'");

		// Recupero Il seriale del Movimento
		$seriale = $db->queryOne("SELECT MAX(seriale) FROM ".$p4a->e3g_prefix."movmagr");
		if (is_numeric($seriale))
        {    	
        	$seriale++;
		}
       	else
       	{
   			$seriale = 1;
   		}

		// Apro le righe del  Documento che devo copiare nei movimenti di magazzino
		$query = "numdocum=".$this->numdoc." AND codtipodoc='".$this->codtipodoc."' AND anno='".$p4a->e3g_azienda_anno_contabile."'";
		$this->build("p4a_db_source", "ds_doc");
		$this->ds_doc->setTable($p4a->e3g_prefix."docr");
		$this->ds_doc->setPk("idriga");
		$this->ds_doc->setWhere($query);
		$this->ds_doc->load();
		$this->ds_doc->firstRow();


		// Cancello gli eventuali movimenti di magazzino gi� scritti per questo documento
		$db->query("DELETE FROM ".$p4a->e3g_prefix."movmagr WHERE anno='".$p4a->e3g_azienda_anno_contabile."' AND codtipodoc='".$this->codtipodoc."' AND numdocum='".$this->numdoc."'");


		//$docum = $this->ds_doc->getAll();
		$riga = $this->ds_doc->getRowNumber();
		// trovo l'ultima riga e la incremento
		$id_riga = $db->queryOne("SELECT MAX(idriga) FROM ".$p4a->e3g_prefix."movmagr");
		
		if (is_numeric($id_riga))
        {    	
        	$id_riga++;
		}
       	else
       	{
   			$id_riga = 1;
   		}
			
		
		//$id_tab = $this->merlin->db->queryOne("SELECT VAL(idtable) FROM ".$p4a->e3g_prefix."movmagr ORDER BY VAL(idtable) DESC");
			 
		while($riga<=$this->ds_doc->getNumRows())
		{		
			if ($this->ds_doc->fields->codice->getNewValue() != '')
			{
				$this->ds_magmagr->newRow();
				//parent::newRow();

				$this->ds_magmagr->fields->codarticolo->setNewValue($this->ds_doc->fields->codice->getNewValue());
				
    			if ($carscar == 'S')
    			{
    				$this->ds_magmagr->fields->qta->setNewValue(-1 * ($this->ds_doc->fields->quantita->getNewValue() - $this->ds_doc->fields->quantita2->getNewValue()));
           		}
           		else
           		{
          			$this->ds_magmagr->fields->qta->setNewValue($this->ds_doc->fields->quantita->getNewValue() - $this->ds_doc->fields->quantita2->getNewValue());
           		}
 				$this->ds_magmagr->fields->data->setNewValue($this->ds_doc->fields->data->getNewValue());
 				$this->ds_magmagr->fields->seriale->setNewValue($seriale);
 				$this->ds_magmagr->fields->codcaumov->setNewValue($causalemov);
 				$this->ds_magmagr->fields->carscar->setNewValue($carscar);
    			
    			$this->ds_magmagr->fields->prezzoven->setNewValue($this->ds_doc->fields->prezzo->getNewValue());
 				$this->ds_magmagr->fields->codtipodoc->setNewValue($this->codtipodoc);
 				$this->ds_magmagr->fields->numdocum->setNewValue($this->numdoc);
 				$this->ds_magmagr->fields->anno->setNewValue($p4a->e3g_azienda_anno_contabile);
    			$this->ds_magmagr->fields->idriga->setNewValue($id_riga);
    			$this->ds_magmagr->fields->idtable->setNewValue($id_riga);
    			
    			$this->ds_magmagr->saveRow();
    		
    			//parent::saveRow();
    			$id_riga++;
    			
			}
		
			$this->ds_doc->nextRow();
			$riga++;
		}

		//$ds_movmag->destroy();
				
					  
		return 0;
	}	
	


}
?>