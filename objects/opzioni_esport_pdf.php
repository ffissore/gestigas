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

class opzioni_esport_pdf extends P4A_Mask
{
	function opzioni_esport_pdf ()
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();


		$this->SetTitle( "Opzioni esportazione PDF" );
		$this->setIcon( "misc" );


		// Toolbar
		$this->build( "p4a_standard_toolbar", "toolbar" );
		$this->toolbar->setMask($this);

		// Message
		$message =& $this->build( "p4a_message", "message" );
		$message->setWidth("300");


		// Sorgente dati principale
		$this->build( "p4a_db_source", "ds_campi" );
		$this->ds_campi->setTable( $p4a->e3g_prefix . "doccampireport" );
		$this->ds_campi->setPk( "idtable" );
		$this->ds_campi->addOrder( "codtipodoc" );		
		$this->ds_campi->addOrder( "ordine" );
		$this->ds_campi->load();
		$this->ds_campi->firstRow();
		$this->setSource( $this->ds_campi );

		// Fields properties
		$fields =& $this->fields;

		// Campi Obbligatori Fields
	    $this->mf = array("campo");
		foreach($this->mf as $mf){
			$fields->$mf->label->setFontWeight("bold");
		}


        // DB source tipi documento 
		$this->build( "p4a_db_source", "ds_tipo" );
		$this->ds_tipo->setTable( $p4a->e3g_prefix . "doctipidoc" );
		$this->ds_tipo->setPk( "codice" );
		$this->ds_tipo->setWhere( "codice <> '00000'" );
		$this->ds_tipo->load();

		$this->fields->codtipodoc->setLabel( "Tipo documento" );
		$this->fields->codtipodoc->setWidth( 300 );
		$this->fields->codtipodoc->setType( "select" );
		$this->fields->codtipodoc->setSourceValueField( "codice" );
		$this->fields->codtipodoc->setSourceDescriptionField( "descrizione" );
		$this->fields->codtipodoc->setSource( $this->ds_tipo );
		

		// Array per il campo di database
		$a_nome_campo = array(); 
		$a_nome_campo[] = array("id" => "fornitore",   "desc" => "Fornitore");
		$a_nome_campo[] = array("id" => "codice",      "desc" => "Codice");
		$a_nome_campo[] = array("id" => "descrizione", "desc" => "Descrizione");
		$a_nome_campo[] = array("id" => "prezzo",      "desc" => "Prezzo");

		if ( E3G_TIPO_GESTIONE == 'G' )
		{
			$a_nome_campo[] = array("id" => "prezzo_originale",      "desc" => "Prezzo Orig.");
			$a_nome_campo[] = array("id" => "delta_prezzo",      "desc" => "Var. Prezzo");
		}
			
		if ( E3G_TIPO_GESTIONE == 'E' )
			$a_nome_campo[] = array("id" => "codiva",  "desc" => "IVA");
		
		$a_nome_campo[] = array("id" => "totale",      "desc" => "Importo");
		$a_nome_campo[] = array("id" => "imponibile",  "desc" => "Imponibile");
		$a_nome_campo[] = array("id" => "imposta",     "desc" => "Imposta");
		$a_nome_campo[] = array("id" => "sconto",      "desc" => "Sconto");

		$a_nome_campo[] = array("id" => "quantita2",   "desc" => "Q.ta' aggiunta");
		$a_nome_campo[] = array("id" => "quantita",    "desc" => "Q.ta'");  // Q.tà consegnata (alla famiglia) | Q.tà ordine (al fornitore) 
		
		$as_nome_campo=& $this->build("p4a_array_source", "as_nome_campo"); 
		$as_nome_campo->load($a_nome_campo); 
		$as_nome_campo->setPk("id"); 


		// Nome campo database
		$this->fields->campo->setLabel( "Campo database" );
		$this->fields->campo->setWidth( 300 );
		$this->fields->campo->setType( "Select" );
		$this->fields->campo->setSourceValueField( "id" );
		$this->fields->campo->setSourceDescriptionField( "desc" );
		$this->fields->campo->setSource( $as_nome_campo );
		
		
		// Nome del campo in stampa
		$this->fields->nomecampo->setLabel( "Titolo in stampa" );
		$this->fields->nomecampo->setWidth( 100 );

        // Ordinamento del campo in stampa
		$this->fields->ordine->setLabel( "Ordinamento" );
		$this->fields->ordine->setWidth( 50 );
		

		$table =& $this->build("p4a_table", "table");
		$table->setWidth( 730 );
		$table->setSource( $this->ds_campi );
		$table->setVisibleCols( array("codtipodoc","campo","nomecampo","ordine") );
		$table->cols->codtipodoc->setLabel( "Tipo documento" );
		$table->cols->codtipodoc->setSource( $this->ds_tipo );
		$table->cols->codtipodoc->setSourceValueField( "codice" );
		$table->cols->codtipodoc->setSourceDescriptionField( "descrizione" );
		
		$table->cols->nomecampo->setLabel( "Titolo in stampa" );
		$table->cols->campo->setLabel( "Campo database" );
		$table->cols->ordine->setLabel( "Ordinamento" );
		
		$table->showNavigationBar();


		//Fieldset con l'elenco dei campi
		$fset=& $this->build("p4a_fieldset", "frame");

 		$fset->anchor($this->fields->codtipodoc);
 		$fset->anchor($this->fields->campo);
 		$fset->anchor($this->fields->nomecampo);
 		$fset->anchor($this->fields->ordine);

		$fset->setWidth(700);


		// Frame
		$frm=& $this->build("p4a_frame", "frm");
		$frm->setWidth(730);

		$frm->anchor($message);
		$frm->anchor($fset);
		$frm->anchor($this->table);

		e3g_scrivi_footer( $this, $frm );

  		// Display
		$this->display("main", $frm);
		$this->display("menu", $p4a->menu);
		$this->display("top", $this->toolbar);
	}


	function main()
	{
		parent::main();

		foreach($this->mf as $mf){
			$this->fields->$mf->unsetStyleProperty("border");
		}
	}

	
	function saveRow()
	{
		$db =& p4a_db::singleton();
		$p4a =& p4a::singleton();
		
		// attribuisco IdTable se la riga � nuova 
		if ( !is_numeric( $this->ds_campi->fields->idtable->getNewValue() ) )
		{
			$ultimoid = $db->queryOne( "SELECT MAX( idtable ) FROM " . $p4a->e3g_prefix . "doccampireport" );
			if ( $ultimoid == '' )
				$ultimoid = 0;
			$ultimoid++;
			$this->ds_campi->fields->idtable->setNewValue( $ultimoid );		
		}
		parent::saveRow();
	}
	
}
	
?>