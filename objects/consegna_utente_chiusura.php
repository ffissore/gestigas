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


class consegna_utente_chiusura extends P4A_Mask
{
	
    // -------------------------------------------------------------------------
	function consegna_utente_chiusura()
    // -------------------------------------------------------------------------
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();
		
		
		$this->SetTitle( "Chiusura Ordine" );


		// Message
		$message =& $this->build("p4a_message", "message");
		$message->setWidth("300");

		
		// COMBO UTENTI
		$this->build("p4a_db_source", "ds_fam");
		$this->ds_fam->setTable($p4a->e3g_prefix."anagrafiche");
		$this->ds_fam->setPk("idanag");
		$this->ds_fam->setQuery("SELECT DISTINCT anag.idanag as idanag, anag.codice as codice, anag.descrizione as descrizione FROM ".$p4a->e3g_prefix."anagrafiche anag ".
							" INNER JOIN ".$p4a->e3g_prefix."docr d ON d.codutente = anag.codice".
							" WHERE anag.tipocfa = 'C' AND (d.estratto <> 'S' OR ISNULL( d.estratto )) AND d.codtipodoc='".$p4a->e3g_azienda_gg_cod_doc_ordine."'");

		//$this->ds_fam->setWhere("tipocfa='C'");
		$this->ds_fam->load();
		$this->ds_fam->firstRow();
		
		$this->build("p4a_db_source", "ds_anagc");
		$this->ds_anagc->setTable($p4a->e3g_prefix."anagrafiche");
		$this->ds_anagc->setWhere("tipocfa='C'");		
		$this->ds_anagc->setPk("codice");		
		$this->ds_anagc->load();
		$this->ds_anagc->firstRow();


		$codfam=& $this->build("p4a_field", "codfam");
		$codfam->setLabel('Utenti con articoli non consegnati');
		$codfam->setWidth("200");
		$codfam->addAction("onChange");
		$this->intercept($codfam, "onChange","codfam_change");

	
		$this->codfam->setLabel('Utente');
		$this->codfam->setWidth(200);
		$this->codfam->setType('select');
		$this->codfam->setSourceValueField('codice');
		$this->codfam->setSourceDescriptionField('descrizione');
		$this->codfam->setSource($this->ds_fam);


		$this->build("p4a_db_source", "ds_orig");
		$this->ds_orig->setTable($p4a->e3g_prefix."docr");
		$this->ds_orig->setWhere("visibile='N' AND codtipodoc ='".$p4a->e3g_azienda_gg_cod_doc_ordine."' AND (codutente<>'' OR ISNULL(codutente)) AND (estratto<>'S' OR ISNULL(estratto)) AND codutente='".$this->ds_anagc->fields->codice->getValue()."'") ;
        $this->ds_orig->addOrder("data");
        
        $this->ds_orig->setPk("idriga");
        $this->ds_orig->setPageLimit( $p4a->e3g_utente_db_source_page_limit );
		$this->ds_orig->load();
        $this->ds_orig->firstRow();
		
		$this->setSource($this->ds_orig);

		
		// campi invisibili per stampa
		$this->build("p4a_field", "fldcodtipo");
		$this->fldcodtipo->setNewValue("");

		$this->build("p4a_field", "flddatadoc");
		$this->flddatadoc->setNewValue("");
		
		$this->build("p4a_field", "fldfornitore");
		$this->fldfornitore->setNewValue("");
		
		$this->build("p4a_field", "fldiddoc");
		$this->fldiddoc->setNewValue("");

		$this->build("p4a_field", "fldnumdocum");
		$this->fldnumdocum->setNewValue("");


		$this->build("p4a_button", "chiudiordine");
		$this->chiudiordine->setLabel("Chiudi Ordine");
		$this->chiudiordine->setIcon("exit");
		$this->chiudiordine->addAction("onClick");
		$this->intercept($this->chiudiordine, "onClick", "chiudiordine_click");
		$this->chiudiordine->requireConfirmation( "onClick", "Vuoi veramente chiudere l'ordine ?" );
		
		$this->chiudiordine->setWidth(200);

		
		$tab_row =& $this->build( "p4a_table", "tab_row" );
        $tab_row->setTitle( "Elenco Ordini" );
		$tab_row->setWidth( E3G_TABLE_WIDTH );
		$tab_row->setSource( $this->ds_orig );
		$tab_row->setVisibleCols( array("codutente", "codice", "descrizione", "quantita","prezzo","codiva") );

		$this->tab_row->cols->codice->setLabel('codice');
		$this->tab_row->cols->prezzo->setLabel('prezzo');
		$this->tab_row->cols->codiva->setLabel("iva");
		
		$this->tab_row->cols->codutente->setLabel('Utente');
		$this->tab_row->cols->codutente->setSourceValueField('codice');
		$this->tab_row->cols->codutente->setSourceDescriptionField('descrizione');
		$this->tab_row->cols->codutente->setSource($this->ds_anagc);
		
		// Toolbar
		$this->build("p4a_quit_toolbar", "toolbar");
		//$this->toolbar->setMask($this);


		$sh_campi =& $this->build("p4a_sheet", "sh_campi");
        $this->sh_campi->defineGrid(3, 2);
		$this->sh_campi->setWidth(730);
		
		
		// ancoro i campi fields
		$this->sh_campi->anchor($this->codfam,1,1);
		$this->sh_campi->anchor($this->chiudiordine,1,2);
		
		$frm=& $this->build("p4a_frame", "frm");
		$frm->setWidth(730);
		
		$this->frm->anchor($sh_campi);
		$this->frm->anchor($this->flddata);
		$this->frm->anchor($this->tab_row);
		
		e3g_scrivi_footer( $this, $frm );

		// Display
		$this->display("main", $frm);
		$this->display("menu", $p4a->menu);
		$this->display("top", $this->toolbar);
	}


    // -------------------------------------------------------------------------
	function main()
    // -------------------------------------------------------------------------
	{
		parent::main();
	}


    // -------------------------------------------------------------------------
	function docstampa_click()
    // -------------------------------------------------------------------------
	{		
		if ( $this->fldcodtipo->getNewValue() != "" && $this->fldfornitore->getNewValue() != "" && 
             $this->fldiddoc->getNewValue() != "" && $this->fldnumdocum->getNewValue() != "" && 
             $this->flddatadoc->getNewValue() != "" )
		{
			$this->stampa( 
                $this->fldcodtipo->getNewValue(), $this->fldfornitore->getNewValue(), 
                $this->fldiddoc->getNewValue(), $this->fldnumdocum->getNewValue(), 
                $this->flddatadoc->getNewValue());
		}	
	}


    // -------------------------------------------------------------------------
	function codfam_change()
    // -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();

		$this->ds_orig->setWhere("visibile='N' AND codtipodoc ='".$p4a->e3g_azienda_gg_cod_doc_ordine."' AND (estratto<>'S' OR ISNULL(estratto)) AND codutente='".$this->codfam->getNewValue()."'");
		$this->ds_orig->load();
		
		$this->tab_row->setSource($this->ds_orig);
		$this->tab_row->setVisibleCols(array("codutente", "codice", "descrizione","quantita","prezzo","codiva"));
		
		$this->tab_row->cols->codice->setLabel('codice');
		$this->tab_row->cols->prezzo->setLabel('prezzo');
		$this->tab_row->cols->codiva->setLabel("iva");
		
		$this->tab_row->cols->codutente->setLabel('Utente');
		$this->tab_row->cols->codutente->setSourceValueField('codice');
		$this->tab_row->cols->codutente->setSourceDescriptionField('descrizione');
		$this->tab_row->cols->codutente->setSource($this->ds_anagc);
	}	


    // -------------------------------------------------------------------------
	function chiudiordine_click()
    // -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
	
		$query = "UPDATE ".$p4a->e3g_prefix."docr SET estratto = 'S' WHERE (estratto <>'S' OR estratto IS NULL) AND codtipodoc ='".$p4a->e3g_azienda_gg_cod_doc_ordine."'";
		//$db->query($query);
	}
	

}

?>