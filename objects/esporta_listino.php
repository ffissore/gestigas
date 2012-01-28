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


class esporta_listino extends P4A_Mask
{

    // -------------------------------------------------------------------------
	function esporta_listino()
    // -------------------------------------------------------------------------
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
		$this->setTitle( "Esportazione Listino Articoli" );
 
		// ---------------------------------------------------------------- DATI
        $this->build( "p4a_db_source", "ds_forn" );
        $this->ds_forn->setTable( $p4a->e3g_prefix . "anagrafiche" );
        $this->ds_forn->setPk( "codice" );        
        $this->ds_forn->setWhere( "tipocfa = 'F' OR codice = '00'" );             
        $this->ds_forn->addOrder( "descrizione" );     
        $this->ds_forn->load();
        $this->ds_forn->firstRow();

		$this->build( "p4a_db_source", "ds_tipo" );
		$this->ds_tipo->setTable( $p4a->e3g_prefix . "tipiarticoli" );
		$this->ds_tipo->setPk( "codice" );		
		$this->ds_tipo->addOrder( "codice" );		
		$this->ds_tipo->load();
		$this->ds_tipo->firstRow();
		
		$this->build( "p4a_db_source", "ds_cat" );
		$this->ds_cat->setTable( $p4a->e3g_prefix . "catmerceologica" );
		$this->ds_cat->setWhere( "tipo = '" . $this->ds_tipo->fields->codice->getNewValue() . "'" );		
		$this->ds_cat->setPk( "codice" );		
		$this->ds_cat->addOrder( "codice" );		
		$this->ds_cat->load();
		

        // ------------------------------------------------------------- Toolbar
        $this->build("p4a_quit_toolbar", "toolbar");

        // --------------------------------------------------- Eventuale warning
        $this->build( "p4a_message", "msg_info" );
        $this->msg_info->setWidth( 700 );

		
		// Array per ORDER BY 	
		$a_order_by = array(); 
		$a_order_by[] = array( "id" => "0", "desc" => "Fornitore, categoria, articolo" );
        $a_order_by[] = array( "id" => "1", "desc" => "Categoria, articolo" );
		$as_order_by =& $this->build("p4a_array_source", "as_order_by"); 
		$as_order_by->load( $a_order_by ); 
		$as_order_by->setPk( "id" ); 
			
		$this->build("p4a_field", "fld_order_by");
		$this->fld_order_by->setLabel("Righe ordinate per");
		$this->fld_order_by->setType("select");
		$this->fld_order_by->setSourceValueField("id");
		$this->fld_order_by->setSourceDescriptionField("desc");
		$this->fld_order_by->setSource($as_order_by);
		$this->fld_order_by->label->setWidth(150);
		$this->fld_order_by->setWidth(250);

		
		
		// -------------------------------------------------------- Campi filtro
        $fld_forn=& $this->build("p4a_field", "fld_forn");
        $fld_forn->setLabel( "Fornitore" );
        $fld_forn->label->setWidth( 150 );
        $fld_forn->setWidth( 250 );
        $fld_forn->setType("select");
        $fld_forn->setSource($this->ds_forn);
        $fld_forn->setSourceValueField("codice");
        $fld_forn->setSourceDescriptionField("descrizione");

		$fld_tipo=& $this->build("p4a_field", "fld_tipo");
		$fld_tipo->setLabel( "Categoria" );
		$fld_tipo->label->setWidth( 150 );
        $fld_tipo->setWidth( 250 );
		$fld_tipo->setType("select");
		$fld_tipo->setSource($this->ds_tipo);
		$fld_tipo->setSourceValueField("codice");
		$fld_tipo->setSourceDescriptionField("descrizione");
		$fld_tipo->addAction("OnChange");
		$this->intercept($this->fld_tipo, "onChange","seleztipo_click");		

		$fld_cat=& $this->build("p4a_field", "fld_cat");
		$fld_cat->setLabel( "Sottocategoria" );
		$fld_cat->label->setWidth( 150 );
        $fld_cat->setWidth( 250 );
		$fld_cat->setType("select");
		$fld_cat->setSource($this->ds_cat);
		$fld_cat->setSourceValueField("codice");
		$fld_cat->setSourceDescriptionField("descrizione");

        $this->build( "p4a_field", "ck_solo_bio" );
        $this->ck_solo_bio->setType( "checkbox" );
        $this->ck_solo_bio->setLabel( "Solo articoli bio" );
        $this->ck_solo_bio->setTooltip( "Solo articoli da agricoltura biologica" );
        $this->ck_solo_bio->label->setWidth( 150 );

		$this->build("p4a_field", "ck_solo_ordini_aperti");
        $this->ck_solo_ordini_aperti->setLabel( "Solo articoli ordinabili" );
        $this->ck_solo_ordini_aperti->setTooltip("Solo articoli attualmente ordinabili");
		$this->ck_solo_ordini_aperti->label->setWidth( 150 );
        $this->ck_solo_ordini_aperti->setType("checkbox");
        $this->ck_solo_ordini_aperti->setValue("1");


		// ------------------------------------------------------------- Bottoni
		$this->build("p4a_button", "bu_esporta_Pdf");
		$this->bu_esporta_Pdf->setLabel("Esporta come PDF");
		$this->bu_esporta_Pdf->setIcon( "pdf" );
		$this->bu_esporta_Pdf->addAction("onClick");
		$this->intercept($this->bu_esporta_Pdf, "onClick", "bu_esporta_PdfClick");

		$this->build("p4a_button", "bu_esporta_Csv");
		$this->bu_esporta_Csv->setLabel("Esporta come CSV (foglio elettronico)");
		$this->bu_esporta_Csv->setIcon( "spreadsheet" );
		$this->bu_esporta_Csv->addAction("onClick");
		$this->intercept($this->bu_esporta_Csv, "onClick", "bu_esporta_CsvClick");


		// ---------------------------------------------------------- Ancoraggio		
        $fs_filtro =& $this->build( "p4a_fieldset", "fs_filtro" );
        $fs_filtro->setTitle( "Filtro esportazione" );
        $fs_filtro->setWidth( E3G_FIELDSET_SEARCH_WIDTH );
        $fs_filtro->anchor( $this->fld_forn );
        $fs_filtro->anchor( $this->fld_tipo );
        $fs_filtro->anchorLeft( $this->fld_cat );
        $fs_filtro->anchor( $this->ck_solo_bio );
        if ( E3G_TIPO_GESTIONE == 'G' ) 
            $fs_filtro->anchor( $this->ck_solo_ordini_aperti );
        $fs_filtro->anchor( $this->fld_order_by );

		$frm=& $this->build("p4a_frame", "frm");
		$frm->setWidth( E3G_MAIN_FRAME_WIDTH );
        $frm->anchor( $this->msg_info );
        $frm->anchor( $this->fs_filtro );
		$frm->anchor($this->bu_esporta_Pdf);        
		$frm->anchorLeft($this->bu_esporta_Csv);        
        		
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
	function seleztipo_click()
    // -------------------------------------------------------------------------
	{
		$this->ds_cat->setWhere( "tipo = '" . $this->fld_tipo->getNewValue() . "' OR codice = '000'" );		
		$this->ds_cat->load();
	}

		
    // -------------------------------------------------------------------------
	function prepara_query()
    // -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();

		$strwhere = ""; 
		if ($this->fld_forn->getnewValue() != "00")
			$strwhere .= " AND ( art.centrale = '" . $this->fld_forn->getnewValue() . "' )";
			
		if ($this->fld_tipo->getnewValue() != "00")
			$strwhere .= " AND ( art.tipo = '" . $this->fld_tipo->getnewValue() . "' )";
			
		if ($this->fld_cat->getnewValue() != "000")
			$strwhere .= " AND ( art.catmerce = '" . $this->fld_cat->getnewValue() . "' )";		
			
        // Articolo biologico
        if ( $this->ck_solo_bio->getNewValue() != 0 )
            $strwhere .= " AND bio = 1";
        
        $query = 
            "SELECT cat.descrizione AS desc_categoria, " .  
            "       sottocat.descrizione AS desc_sottocategoria, " .  
            "       f.codice AS cod_fornitore, f.descrizione AS desc_fornitore, " .
            "       art.codice, " .
            "       IF( art.bio=1, 'Bio', '') AS bio, " .
            "       art.descrizione, " .
            "       CONCAT_WS( ' ', art.um_qta, art.um ) AS um_qta_um, " .  // CONCAT_WS non è vuoto se manca l'UM 
            "       CONCAT( FORMAT( art.prezzoven/art.um_qta, $p4a->e3g_azienda_n_decimali_prezzi), '/', art.um ) AS prezzo_ven_um, " .  // CONCAT è vuoto se manca l'UM 
            "       FORMAT( prezzoven, $p4a->e3g_azienda_n_decimali_prezzi ) AS prezzoven, " .
            "       codiva, qtaminperfamiglia " .
            "  FROM " . $p4a->e3g_prefix . "articoli AS art " .
            "         INNER JOIN  " . $p4a->e3g_prefix . "anagrafiche AS f ON art.centrale = f.codice " .
            "         INNER JOIN  " . $p4a->e3g_prefix . "tipiarticoli AS cat ON art.tipo = cat.codice " .
            "         INNER JOIN  " . $p4a->e3g_prefix . "catmerceologica AS sottocat ON art.catmerce = sottocat.codice ";

  		if ( E3G_TIPO_GESTIONE == 'G' )
		{
	      	if ( $this->ck_solo_ordini_aperti->getNewValue() == "1" ) {
	  			// Solo articoli ordinabili
	  			$query .= 
	  				"         LEFT JOIN  " . $p4a->e3g_prefix . "fornitoreperiodo fp ON fp.fornitore = art.centrale " .
	  				" WHERE ( art.stato = 1 ) $strwhere AND " . e3g_where_ordini_aperti( "fp" ); 
	  		}
	  		else {
	  			// Tutti gli articoli, anche quelli non ordinabili
	  			$query .= 
	  				" WHERE ( art.stato = 1 ) $strwhere " ;
	  		}
    	}
    	else
    	{
    		$query .= " WHERE ( 1=1 ) $strwhere " ;
    	}
        
    	
        // Mantenere l'ordinamento (di default) come quello dell'elenco articoli da scegliere 
        // nel carrello (cassa_gg_singolo.php)
       	switch ( $this->fld_order_by->getNewValue() ) {
            case "0": // Order By Fornitore (default)
                $query .= " ORDER BY f.descrizione, art.catmerce, art.descrizione";
        		break;
            case "1": // Order By Cat Merceol.
                $query .= " ORDER BY art.catmerce, art.descrizione";
                break;
        }

		$this->build( "p4a_db_source", "ds_art" );
		$this->ds_art->setTable( $p4a->e3g_prefix . "articoli" );
		$this->ds_art->setQuery( $query );

		$this->ds_art->load();
		$this->ds_art->firstRow();

        if ( $this->ds_art->getNumRows() == 0 ) {
            $this->msg_info->setIcon("warning");
            $this->msg_info->setValue( "Nessun articolo da esportare: modificare i filtri." );
            return false;
        }
        else
            return true;
	}
		

    // -------------------------------------------------------------------------
	function bu_esporta_PDFClick()
    // -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();

		if ( $this->prepara_query() )
        {
    		require( "class.report.php" );
    		
    		$pdf = new Creport( 'a4','portrait' );
    	
            $colonne = array (
                "desc_fornitore"    => "Fornitore",
                "bio"               => "Bio",
                "descrizione"       => "Articolo",
                "um_qta_um"         => "Conf.",
                "prezzo_ven_um"     => "Prezzo/UM",
                "prezzoven"         => "Prezzo",
                "qtaminperfamiglia" => "Q.ta' min."
            );
    
    		//$opt = array('options' => array('prezzoven'=>array('justification'=>'right')));
    
    		// Il terzo parametro è il titolo della finestra che diventa anche nome del file: non inserirvi al momento degli spazi
    		$pdf->stampareport( $this->ds_art->getAll(), $colonne, "Listino Articoli", 
                "Listino_" . $p4a->e3g_azienda_rag_soc );
        }
    }


    // -------------------------------------------------------------------------
	function bu_esporta_CSVClick()
    // -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();

        if ( $this->prepara_query() ) {
            // MM_2009-01-26 Attenzione: causa probabile bug di p4a 2.2.3, non è possibile 
            // esportare le colonne in un ordine diverso da come sono presenti in tabella/query
            $colonne = array (
                "desc_categoria"     => "Categoria",
                "desc_sottocategoria"=> "Sottocategoria",
                "cod_fornitore"      => "Codice For.",
                "desc_fornitore"     => "Fornitore",
                "codice"             => "Codice Art.",
                "bio"                => "Bio",
                "descrizione"        => "Descrizione Articolo",
                "um_qta_um"          => "Conf.",
                "prezzo_ven_um"      => "Prezzo/UM",
                "prezzoven"          => "Prezzo unitario",
                "qtaminperfamiglia"  => "Q.ta' minima"
            );
            
            e3g_db_source_exportToCsv( $this->ds_art, $colonne, "Listino_" . $p4a->e3g_azienda_rag_soc );
        }
    }
	
}
?>