<?php
/**
 * Progetto e3g - Equogest/gestigas
 *   Software gestionali per l'economia solidale
 *   <http://www.progettoe3g.org>
 *
 * Copyright (C) 2003-2012
 *   Andrea Piazza <http://www.andreapiazza.it>
 *   Marco Munari  <http://www.marcomunari.it>
 *
 * @package Progetto e3g - Equogest/gestigas
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

class sqlnewcode extends P4A_Mask
{
	function &sqlnewcode()
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();

		$oldarticolo=& $this->build("p4a_field", "oldarticolo");
		$oldarticolo->setLabel('Vecchio Codice Articolo');
		$oldarticolo->setWidth("100");
		
		$newarticolo=& $this->build("p4a_field", "newarticolo");
		$newarticolo->setLabel('Nuovo Codice Articolo');
		$newarticolo->setWidth("100");
		
		//Button per la execute
		$this->build("p4a_button", "esegui");
		$this->esegui->setLabel("Modifica");
		$this->esegui->addAction("onClick");
		$this->intercept($this->esegui, "onClick", "esegui_click");
		


		// Toolbar
		$this->build("p4a_quit_toolbar", "toolbar");

		$this->SetTitle('Modifica Codice Articolo');


		// Message
		$message =& $this->build("p4a_message", "message");
		$message->setWidth("300");

		//Fieldset con l'elenco dei campi
		$fset=& $this->build("p4a_fieldset", "frame");
		//$fset->setTitle("Scheda Articolo");

 		$fset->anchor($this->oldarticolo);
 		$fset->anchor($this->newarticolo);
 		$fset->anchor($this->esegui);

		 		
		$fset->setWidth(730);


		// Frame
		$frm=& $this->build("p4a_frame", "frm");
		$frm->setWidth(730);

		$frm->anchor($message);
		$frm->anchor($fset);
			

		scrivi_footer( $this, $frm );

  		// Display
		$this->display("main", $frm);
		$this->display("menu", $p4a->menu);
		$this->display("top", $this->toolbar);
	}



	function main()
	{
		parent::main();

	}

	function esegui_click()
	{
		$db =& p4a_db::singleton();
				
		$db->query("UPDATE ".$p4a->e3g_prefix."articoli SET codice ='".$this->newarticolo->getNewValue()."' WHERE codice ='".$this->oldarticolo->getNewValue()."'");
		$db->query("UPDATE ".$p4a->e3g_prefix."articoloperiodo SET codice ='".$this->newarticolo->getNewValue()."' WHERE codice ='".$this->oldarticolo->getNewValue()."'");
		$db->query("UPDATE ".$p4a->e3g_prefix."docr SET codice ='".$this->newarticolo->getNewValue()."' WHERE codice ='".$this->oldarticolo->getNewValue()."'");
		
		$db->query("UPDATE ".$p4a->e3g_prefix."carrello SET codarticolo ='".$this->newarticolo->getNewValue()."' WHERE codarticolo ='".$this->oldarticolo->getNewValue()."'");
		$db->query("UPDATE ".$p4a->e3g_prefix."movmagr SET codarticolo ='".$this->newarticolo->getNewValue()."' WHERE codarticolo ='".$this->oldarticolo->getNewValue()."'");
		$db->query("UPDATE ".$p4a->e3g_prefix."docr_temp SET codarticolo ='".$this->newarticolo->getNewValue()."' WHERE codarticolo ='".$this->oldarticolo->getNewValue()."'");
					
		$this->message->setValue($this->oldarticolo->getNewValue()." >> ".$this->newarticolo->getNewValue());
			
		$this->oldarticolo->setNewValue("");
		$this->newarticolo->setNewValue("");
		
	}
		

}
?>