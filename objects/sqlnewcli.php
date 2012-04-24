<?php
/**
 * Progetto e3g - Equogest/gestigas
 *   Software gestionali per l'economia solidale
 *   <http://www.progettoe3g.org>
 *
 * Copyright (C) 2003-2009
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

class sqlnewcli extends P4A_Mask
{
	function &sqlnewcli()
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();

		$oldcli=& $this->build("p4a_field", "oldcli");		
		$oldcli->setWidth("100");
		
		$newcli=& $this->build("p4a_field", "newcli");
		$newcli->setWidth("100");
		
		//Button per la execute
		$this->build("p4a_button", "esegui");
		$this->esegui->setLabel("Modifica");
		$this->esegui->addAction("onClick");
		$this->intercept($this->esegui, "onClick", "esegui_click");
		
		
		if ( E3G_TIPO_GESTIONE == 'G' )
		{
			//gestigas
			$oldcli->setLabel('Vecchio Codice Famiglia');
			$newcli->setLabel('Nuovo Codice Famiglia');
			$this->SetTitle('Modifica Codice Famiglia');			
		}
		else 
		{
			// equogest
			$oldcli->setLabel('Vecchio Codice Cliente');
			$newcli->setLabel('Nuovo Codice Cliente');
			$this->SetTitle('Modifica Codice Cliente');
		}


		// Toolbar
		$this->build("p4a_quit_toolbar", "toolbar");



		// Message
		$message =& $this->build("p4a_message", "message");
		$message->setWidth("300");

		//Fieldset con l'elenco dei campi
		$fset=& $this->build("p4a_fieldset", "frame");
		//$fset->setTitle("Scheda Articolo");

 		$fset->anchor($this->oldcli);
 		$fset->anchor($this->newcli);
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
				
		$db->query("UPDATE ".$p4a->e3g_prefix."anagrafiche  SET codice ='".$this->newcli->getNewValue()."' WHERE codice ='".$this->oldcli->getNewValue()."'");
		$db->query("UPDATE ".$p4a->e3g_prefix."doct SET codclifor ='".$this->newcli->getNewValue()."' WHERE codclifor='".$this->oldcli->getNewValue()."'");

		$db->query("UPDATE ".$p4a->e3g_prefix."docr SET codutente ='".$this->newcli->getNewValue()."' WHERE codutente='".$this->oldcli->getNewValue()."'");
		$db->query("UPDATE ".$p4a->e3g_prefix."carrello SET codutente ='".$this->newcli->getNewValue()."' WHERE codutente='".$this->oldcli->getNewValue()."'");

		$db->query("UPDATE ".$p4a->e3g_prefix."referenti SET codanag='".$this->newcli->getNewValue()."' WHERE codanag='".$this->oldcli->getNewValue()."'");
		
		$db->query("UPDATE ".$p4a->e3g_prefix."movconr SET codconto='".$this->newcli->getNewValue()."' WHERE codconto='".$this->oldcli->getNewValue()."'");
					
		$this->message->setValue($this->oldcli->getNewValue()." >> ".$this->newcli->getNewValue());
			
		$this->oldcli->setNewValue("");
		$this->newcli->setNewValue("");
		
	}
		

}
?>