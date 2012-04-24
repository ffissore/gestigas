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
require_once( dirname(__FILE__) . '/../config.php' );


class sqldbcreate extends P4A_Mask
{
	function sqldbcreate()
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();

		$this->SetTitle('Creazione Database');

		// Toolbar
		$this->build("p4a_quit_toolbar", "toolbar");

		// Message
		$message =& $this->build("p4a_message", "message");
		$message->setWidth("300");

		//Button per la execute
		$this->build("p4a_button", "bu_esegui");
		$this->bu_esegui->setLabel("Crea Database");
		$this->bu_esegui->setIcon("execute");
		$this->bu_esegui->addAction("onClick");
		$this->intercept($this->bu_esegui, "onClick", "bu_esegui_click");

		
		// Campo prefisso
		$prefisso=& $this->build("p4a_field", "prefisso");
		$prefisso->setLabel("Prefisso Tabelle");
		$prefisso->setWidth(200);

		// Campo Intestazione
		$intestazione=& $this->build("p4a_field", "intestazione");
		$intestazione->setLabel("Intestazione");
		$intestazione->setWidth(200);

		// Campo Nome Utente
		$nome=& $this->build("p4a_field", "nome");
		$nome->setLabel("Nome Utente");
		$nome->setWidth(200);

		// Campo Nome Utente
		$cognome=& $this->build("p4a_field", "cognome");
		$cognome->setLabel("Cognome Utente");
		$cognome->setWidth(200);

		// Campo Nome Utente
		$email=& $this->build("p4a_field", "email");
		$email->setLabel("E-mail");
		$email->setWidth(200);


		// Campo Password
		$password=& $this->build("p4a_field", "password");
		$password->setLabel("Password Admin");
		$password->setWidth(200);
		
		
		// Visualizzazione oggetti ---------------------------------------------
		
		$fset=& $this->build("p4a_fieldset", "frame");
		//$fset->setTitle("");
 		$fset->anchor($this->prefisso);
 		$fset->anchor($this->intestazione);
 		$fset->anchor($this->nome);
 		$fset->anchorLeft($this->cognome);
 		$fset->anchor($this->email);
 		$fset->anchor($this->password);
 		$fset->anchorRight($this->bu_esegui);
		$fset->setWidth(730);


		// Frame
		$frm=& $this->build("p4a_frame", "frm");
		$frm->setWidth(730);

		$frm->anchor($message);
		$frm->anchor($fset);
			

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
		$e3g_main =& e3g_main::singleton(); 
		$db =& p4a_db::singleton();
		
		if ( E3G_TIPO_GESTIONE == 'G' )
		{	
			$nome_file1 = dirname(__FILE__) . "/../db/gestigas_db_init_multi.sql";
			$nome_file2 = dirname(__FILE__) . "/../db/gestigas_db_init_cond.sql"; 		
		}	
		else
		{	
			$nome_file1 = dirname(__FILE__) . "/../db/equogest_db_init_multi.sql";
			$nome_file2 = dirname(__FILE__) . "/../db/equogest_db_init_cond.sql"; 					
		}	
				
		// creazione tabelle multi
		if ( !file_exists($nome_file1) )
			return array( false, "Creazione impossibile causa mancanza del file '$nome_file1'" );
	
		$idfile = fopen( $nome_file1, "r" )
			or die( "Impossibile leggere il file ($nome_file1)" );
		$dati = file( $nome_file1 );
	
		foreach ( $dati as $riga ) {
			if ( trim($riga) != "" ) {
				$strdata = str_replace( "[PREFIX]", $this->prefisso->getnewValue(), $riga );
				$db->query( $strdata );
			}
		}
		fclose( $idfile );
		
		// creazione tabelle condivise
		if ( !file_exists($nome_file2) )
			return array( false, "Creazione impossibile causa mancanza del file '$nome_file2'" );
	
		$idfile = fopen( $nome_file2, "r" )
			or die( "Impossibile leggere il file ($nome_file2)" );
		$dati = file( $nome_file2 );
	
		foreach ( $dati as $riga ) {
			if ( trim($riga) != "" ) {
				$strdata = str_replace( "[PREFIX]", $this->prefisso->getnewValue(), $riga );
				$db->query( $strdata );
			}
		}
		fclose( $idfile );
		
		
		$maxid = $db->queryOne("SELECT MAX( idanag ) FROM " . $this->prefisso->getnewValue() . "anagrafiche" );	
		if ( is_numeric($maxid) )
		{
			$maxid++;
		}					
		else 
		{
			$maxid = 1;
		}					

		if ( E3G_TIPO_GESTIONE == 'G' )
		{
			$tipocfa = 'C';
		}	
		else
		{
			$tipocfa = 'U';
		}	
		
		// creazione utente admin
		$db->query( "INSERT INTO ".$this->prefisso->getnewValue()."anagrafiche VALUES('".$maxid."', '".$maxid."', '".$this->nome->getnewValue()."', '".$this->cognome->getnewValue()."', '".$this->nome->getnewValue()." ".$this->cognome->getnewValue()."', '', '', '', '', '', '', '', '', '', '".$this->email->getnewValue()."', '', '', 'AS', '', '', '', '0', '".$tipocfa."', '0', '', 'A', '', MD5('".$this->password->getnewValue()."'), '', '1', NOW(), NOW(), '', '');" );

		// creazione record _aziende
		$db->query( "INSERT INTO _aziende (id_azienda, rag_soc, prefix, dbver, indirizzo, etichette_max, gg_cod_doc_ordine, gg_cod_doc_ordine_fam, eg_cod_doc_scontrino, n_decimali_prezzi, etichette_path, show_new_account, data_inizio, data_agg) SELECT 1+COALESCE(MAX(id_azienda), 0), '".$this->intestazione->getnewValue()."', '".$this->prefisso->getnewValue()."', '0009', 'indirizzo...', 12, '00024', '00023', '00005', 2, 'etichette/', '0', CURDATE(), NOW() FROM _aziende;");
		
		$e3g_main->esciClick(); 	
		
		
	}
		

}


?>