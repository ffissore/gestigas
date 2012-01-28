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
require_once( dirname(__FILE__) . '/../config.php' );


class luoghi_consegna extends P4A_Mask
{
	
	function luoghi_consegna ()
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();

		$this->SetTitle('Luoghi Consegna');

		//Sorgente dati principale
		$this->build("p4a_db_source", "ds_luo");
		$this->ds_luo->setTable("_luoghi_cons");
		$this->ds_luo->setWhere( "prefix = '" . $p4a->e3g_prefix . "'" );
		$this->ds_luo->setPk("id_luogo_cons");
		$this->ds_luo->addOrder("descrizione");
		$this->ds_luo->load();
		$this->ds_luo->firstRow();
		$this->setSource($this->ds_luo);

		// Fields properties
		$fields =& $this->fields;

		// Campi Obbligatori Fields
	    $this->mf = array("descrizione");
		foreach($this->mf as $mf){
			$fields->$mf->label->setFontWeight("bold");
		}


		// -------------------------- Toolbar e campo descrizione enable/disable
		if ( E3G_TIPO_GESTIONE == 'G' )
		{
           	switch ($p4a->e3g_utente_tipo) {
                case "A":
                case "AS":
					$this->build("p4a_standard_toolbar", "toolbar");
					$this->fields->descrizione->enable();				
					break;
					
				default:
					$this->build("p4a_navigation_toolbar", "toolbar");
					$this->fields->descrizione->disable();					
					break;
            }
        }
		else
		{
			// Equogest
			$this->build("p4a_standard_toolbar", "toolbar");
		}
		$this->toolbar->setMask($this);


		// --------------------------------------------------------------- Table
		$table =& $this->build("p4a_table", "table");
		$table->setWidth(730);
		$table->setSource($this->ds_luo);
		$table->setVisibleCols(array("descrizione"));


		// ------------------------------------------------------------- Message
		$message =& $this->build("p4a_message", "message");
		$message->setWidth("300");


		// ----------------------------------------------------- Vista dettaglio
		//Campo descrizione
		$this->fields->descrizione->setLabel('Descrizione');
		$this->fields->descrizione->setWidth(250);

		//Fieldset con l'elenco dei campi
		$fset=& $this->build("p4a_fieldset", "frame");
		$fset->setWidth(700);
 		$fset->anchor($this->fields->descrizione);


		// ---------------------------------------------------- Frame principale
		$frm=& $this->build("p4a_frame", "frm");
		$frm->setWidth(730);
		$frm->anchor($this->table);
		$frm->anchor($message);
		$frm->anchor($fset);

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

		// Verifica campi obbligatori
		$error_text = "";	
		foreach ( $this->mf as $mf ) {
			$value = $this->fields->$mf->getNewValue();
			if(trim($value) === ""){
				$this->fields->$mf->setStyleProperty("border", "1px solid red");
				$error_text = "Compilare i campi obbligatori";
			}
		}

		if ( $error_text != "" ) {
			$this->message->setValue( $error_text );
		} 
		else {
			if ( !is_numeric($this->fields->id_luogo_cons->getNewValue()) )
			{
				// sono in New Row
				$maxid = $db->queryOne("SELECT MAX( id_luogo_cons ) FROM _luoghi_cons" );	
				if ( is_numeric($maxid) )
				{
					$maxid++;
				}					
				else 
				{
					$maxid = 1;
				}					
				$this->fields->id_luogo_cons->setNewValue( $maxid );					
					 
			}
			$this->fields->prefix->setNewValue($p4a->e3g_prefix);					
				
			parent::saveRow();
		} 


	}


}
?>