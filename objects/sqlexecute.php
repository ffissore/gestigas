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


class sqlexecute extends P4A_Mask
{
	function sqlexecute()
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();

		$this->SetTitle('Esecuzione Query');

		// Toolbar
		$this->build("p4a_quit_toolbar", "toolbar");

		// Message
		$message =& $this->build("p4a_message", "message");
		$message->setWidth("300");

		//Button per la execute
		$this->build("p4a_button", "bu_esegui");
		$this->bu_esegui->setLabel("Esegui query");
		$this->bu_esegui->setIcon("execute");
		$this->bu_esegui->addAction("onClick");
		$this->intercept($this->bu_esegui, "onClick", "bu_esegui_click");

		//$this->build("p4a_filesystem_navigator", "navigator");
		//$this->navigator->files->enablePreview(); // enable a preview link to open files
		//$this->navigator->files->no_files_message = "No files in this folder";
		//$this->navigator->folders->no_folders_message = "No folders";
		//$this->navigator->folders->collapse(); // If you want to open only the currently selected folder
		 
		//$this->navigator->f_folders->setTitle("Folders");
		//$this->navigator->f_files->setTitle("Files");
		//$this->navigator->folders->create_folder_button->setValue("Create");
		//$this->navigator->files->upload_field->setLabel("Upload a file");
		
		// Campo di testo		
		$testo_sql=& $this->build("p4a_field", "testo_sql");
		$testo_sql->setLabel("Testo della query");
		$testo_sql->setWidth(716);
		$testo_sql->setHeight(50);
		
		// DB source
		$this->build("p4a_db_source", "ds_row");
    	$this->ds_row->setQuery("SELECT * FROM ".$p4a->e3g_prefix."articoli WHERE 1=0");
    	$this->ds_row->setPageLimit(20);
    	$this->ds_row->load();
		
		// Griglia
		$tab_row =& $this->build("p4a_table", "tab_row");
		$tab_row->setSource($this->ds_row);
		$this->tab_row->setTitle("Risultato");
		//$tab_row->setWidth(730);
		$this->tab_row->setInvisible();


		// Visualizzazione oggetti ---------------------------------------------
		
		$fset=& $this->build("p4a_fieldset", "frame");
		//$fset->setTitle("");
 		$fset->anchor($this->testo_sql);
 		$fset->anchorRight($this->bu_esegui);
		$fset->setWidth(730);


		// Frame
		$frm=& $this->build("p4a_frame", "frm");
		$frm->setWidth(730);

		$frm->anchor($message);
		$frm->anchor($fset);
		$frm->anchor($this->tab_row);
			

		e3g_scrivi_footer( $this, $frm );

  		// Display
		$this->display("menu", $p4a->menu);
		$this->display("top", $this->toolbar);
		$this->display("main", $frm);
	}



	function main()
	{
		parent::main();
	}


	function bu_esegui_click()
	{
		$db =& p4a_db::singleton();
			
// TODO    	
    	// Provvisorio... non funziona se c'è un ";" che deve essere scritto in
    	// un campo di testo e che quindi non separa due istruzioni SQL
    	$query = explode(";", $this->testo_sql->getNewValue());
		
		foreach ($query as $riga)
    	{
    		$strdata = $riga;

			$pos = strpos(strtoupper($strdata), "SELECT");

			if (is_integer($pos))
			{
				//FOUND
				//$this->ds_row->setQuery($strdata);
            	//$this->ds_row->load();

        		$this->build("p4a_db_source", "ds_row2");
            	$this->ds_row2->setQuery($strdata);
            	$this->ds_row2->setPageLimit(10);
            	$this->ds_row2->load();

        		$this->tab_row->setSource($this->ds_row2);
        		$this->tab_row->setVisible();
        		
			}
			else
			{
				// NOT FOUND
				$db->query($strdata);
// TODO Non c'è modo di sapere se la query è stata eseguita correttamente o ha
// dato origine ad errori, per qualche errore di sintassi od altro...
				
			}
					
    	}
		
	}
		

}


?>