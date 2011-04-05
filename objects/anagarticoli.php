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


class anagarticoli extends P4A_Mask
{
	var $newrecord = false;

    // -------------------------------------------------------------------------
	function anagarticoli()
    // -------------------------------------------------------------------------
	{
		$this->p4a_mask();
		$this->addCss(E3G_TEMPLATE_DIR . 'css/style.css');
		$p4a =& p4a::singleton();
        $db =& p4a_db::singleton();
		
		
		if ( E3G_TIPO_GESTIONE == 'G' )
			$this->SetTitle( "Listino Articoli" );
		else 
			$this->SetTitle( "Scheda Articolo" );	
        $this->setIcon( "articoli" );


		// -------------------------------------------- Sorgente dati principale
		$this->build( "p4a_db_source", "ds_articoli" );

		if ( E3G_TIPO_GESTIONE == 'G' ) {
			$this->ds_articoli->setFields( array( 
                "idarticolo", "codice", "descrizione", "centrale", "bio",
                "CONCAT_WS( ' ', um_qta, um ) AS um_qta_um, " .  // CONCAT_WS non è vuoto se manca l'UM 

				"FORMAT( prezzoven, $p4a->e3g_azienda_n_decimali_prezzi) AS prezzoven",
				"FORMAT( prezzoacq, $p4a->e3g_azienda_n_decimali_prezzi) AS prezzoacq", 
				"CONCAT( FORMAT( prezzoven/um_qta, $p4a->e3g_azienda_n_decimali_prezzi), '/', um ) AS prezzo_ven_um",  // CONCAT è vuoto se manca l'UM 

				"um_qta", "pzperconf", "qtaminordine", "qtaminperfamiglia", "barcode" ,  
				"codiva", "tipo", "giacenza", "progetto", "sconto1", "sconto2", "sconto3",
				"catmerce", "tipoarticolo", "paese", "contovendita", "contoacquisto",
				"posizione", "periodo","um" ,"scortaminima", "stato", "data_ins", "data_agg", "ingredienti", "data_agg_ing",
                "desc_agg", "gestione_a_peso" ) );
		}
		else {
			$this->ds_articoli->setFields( array(
                "idarticolo", "barcode", "codice", "descrizione", "bio", "prezzoven", "prezzoacq", "bio",
				"codiva", "tipo", "giacenza", "centrale", "progetto", "sconto1", "sconto2", "sconto3", "catmerce", "tipoarticolo",
				"paese", "contovendita", "contoacquisto", "posizione", "periodo", "um", "scortaminima", "pzperconf",
				"qtaminordine", "qtaminperfamiglia", "um_qta", "stato", "data_ins", "data_agg", "ingredienti", "data_agg_ing", 
                "desc_agg", "gestione_a_peso", "'AGGIUNGI' as aggiungi") );
		}
        $this->ds_articoli->setTable( $p4a->e3g_prefix . "articoli" );
		$this->ds_articoli->addOrder( "descrizione" );
        $this->ds_articoli->setPk( "idarticolo" );
        $this->ds_articoli->setPageLimit( $p4a->e3g_utente_db_source_page_limit );
		$this->ds_articoli->load();  // WHERE impostato in bu_cerca_click()
        $this->ds_articoli->firstRow();

		$this->setSource( $this->ds_articoli );

		$fields =& $this->fields;
		
		$fields->idarticolo->setType("decimal");
		
		
		// Campi Obbligatori Fields
	    if ( E3G_TIPO_GESTIONE == 'G' )
			$this->mf = array( "codice", "descrizione", "centrale", "catmerce", "stato" );
		else 
			$this->mf = array( "codice", "descrizione", "centrale", "catmerce", "tipo");
			
		foreach ( $this->mf as $mf )
			$this->fields->$mf->label->setFontWeight("bold");

		if ( E3G_TIPO_GESTIONE == 'G' ) {
			// Campi calcolati da non salvare		
			$this->ds_articoli->fields->prezzo_ven_um->setReadOnly(TRUE);
			 
//			$this->ds_articoli->fields->prezzoven->setReadOnly(TRUE);  // Viene aggiornato tramite query
		}


		// ----------------------------------------------------- Altri db source
		
		// Fornitore per ricerca
		$this->build( "p4a_db_source", "ds_forn_ricerca" );
        $this->ds_forn_ricerca->setSelect( "codice, CONCAT_WS( ' / ', descrizione, desc_agg ) AS descrizione" );
		$this->ds_forn_ricerca->setTable( $p4a->e3g_prefix . "anagrafiche" );
		$this->ds_forn_ricerca->setWhere( "tipocfa = 'F' OR idanag = 0" );
		$this->ds_forn_ricerca->setPk( "codice" );		
		$this->ds_forn_ricerca->addOrder( "descrizione" );		
		$this->ds_forn_ricerca->load();		

		// Fornitore 
		$this->build( "p4a_db_source", "ds_forn" );
// Meglio di no perchè si ripercuote anche nella colonna della griglia e la ragione sociale diventa lunga da far
// sempre scattare la doppia riga per ogni record        
//      $this->ds_forn->setSelect( "codice, CONCAT_WS( ' / ', descrizione, desc_agg ) AS descrizione" );
		$this->ds_forn->setTable( $p4a->e3g_prefix ."anagrafiche" );
		$this->ds_forn->setWhere( "tipocfa = 'F'" );
		$this->ds_forn->setPk( "codice" );		
		$this->ds_forn->addOrder( "descrizione" );		
		$this->ds_forn->load();		
		$this->ds_forn->firstRow();

		// Tipi articoli (categorie)
		$this->build("p4a_db_source", "ds_tipo");
		$this->ds_tipo->setTable($p4a->e3g_prefix."tipiarticoli");
		$this->ds_tipo->setPk("codice");		
		$this->ds_tipo->addOrder("codice");		
		$this->ds_tipo->load();
		$this->ds_tipo->firstRow();

		// Categoria merceologica (sottocategoria)		
		$this->build( "p4a_db_source", "ds_cat" );
		$this->ds_cat->setTable( $p4a->e3g_prefix . "catmerceologica" );
		$this->ds_cat->setWhere( "tipo = '" . $this->ds_tipo->fields->codice->getNewValue() . "'" );		
		$this->ds_cat->setPk( "codice" );		
		$this->ds_cat->addOrder( "codice" );		
		$this->ds_cat->load();

		// Stato articoli
		$this->build( "p4a_db_source", "ds_stato" );
		$this->ds_stato->setTable( "_anagrafiche_stato" );
        $this->ds_stato->setWhere( "codice = 1 OR codice = 2" );  // 1:Abilitato 2:Disabilitato (Non si usa 0:In attesa)
		$this->ds_stato->setPk( "codice" );
		$this->ds_stato->load();
		
		// Unità di misura
		$this->build("p4a_db_source", "ds_um");
		$this->ds_um->setTable($p4a->e3g_prefix."um");
		$this->ds_um->setPk("codice");		
		$this->ds_um->addOrder("ordine");		
		$this->ds_um->load();

		// "Categoria : sottocategoria" 
		$this->build("p4a_db_source", "ds_cat_sotcat");
		$this->ds_cat_sotcat->setTable($p4a->e3g_prefix."catmerceologica");
		$this->ds_cat_sotcat->setPk("codice");
		if ( E3G_TIPO_GESTIONE == 'G' )
			$this->ds_cat_sotcat->setQuery(
				"SELECT sc.codice, CONCAT_WS( ' : ', c.descrizione , sc.descrizione ) descrizione " .
				"  FROM ".$p4a->e3g_prefix."tipiarticoli c, ".$p4a->e3g_prefix."catmerceologica sc " .
				" WHERE sc.tipo = c.codice " .
				" ORDER BY c.descrizione, sc.descrizione" );
		$this->ds_cat_sotcat->load();

        if ( E3G_TIPO_GESTIONE == 'E' ) {
            // Sottoconti contabili vendita e acquisto
            $this->build("p4a_db_source", "ds_con_ven");
            $this->ds_con_ven->setTable($p4a->e3g_prefix."anagrafiche");
            $this->ds_con_ven->setWhere("(segnocontabile ='A' AND tipocfa='S') OR idanag = 0");
            $this->ds_con_ven->setPk("codice");     
            $this->ds_con_ven->addOrder("idanag");      
            $this->ds_con_ven->addOrder("descrizione");     
            $this->ds_con_ven->load();      
            
            $this->build("p4a_db_source", "ds_con_acq");
            $this->ds_con_acq->setTable($p4a->e3g_prefix."anagrafiche");
            $this->ds_con_acq->setWhere("(segnocontabile ='D' AND tipocfa='S') OR idanag = 0");
            $this->ds_con_acq->setPk("codice");     
            $this->ds_con_acq->addOrder("idanag");      
            $this->ds_con_acq->addOrder("descrizione");     
            $this->ds_con_acq->load();      
            
            // Codice IVA
            $this->build("p4a_db_source", "ds_iva");
            $this->ds_iva->setTable($p4a->e3g_prefix."aliquoteiva");
            $this->ds_iva->setPk("codice");
            $this->ds_iva->load();
    
            $this->build("p4a_db_source", "ds_progetto");
            $this->ds_progetto->setTable($p4a->e3g_prefix."progetti");
            $this->ds_progetto->setPk("codice");        
            $this->ds_progetto->addOrder("descrizione");        
            $this->ds_progetto->load();     
            
            $fields->progetto->setType("select");
            $fields->progetto->setSource($this->ds_progetto);
            $fields->progetto->setSourceValueField("codice");
            $fields->progetto->setSourceDescriptionField("descrizione");    
        }
        

		// ------------------------------------------------------------- Toolbar
		if ( E3G_TIPO_GESTIONE == 'G' )
		{
           	switch ($p4a->e3g_utente_tipo) {
                case "A":
                case "AS":
                case "R":
					$this->build("p4a_standard_toolbar", "toolbar");
                    break;
                case "U":
                    if ( $p4a->e3g_utente_modifica_ingredienti ) {
                        $this->build("p4a_standard_toolbar", "toolbar");  // Utente abilitato alla modifica degli ingredienti
                        $this->toolbar->buttons->new->setInvisible();
                        $this->toolbar->buttons->delete->setInvisible();
                    }
                    else
    					$this->build("p4a_navigation_toolbar", "toolbar");  // Normale utente
    	            break;
                default:
                    $this->build("p4a_navigation_toolbar", "toolbar");
                    break;
            }
		}
		else 
			$this->build("p4a_standard_toolbar", "toolbar");

		$this->toolbar->setMask( $this );
		

		// ------------------------------------------------------------- Message
		$message =& $this->build( "p4a_message", "message" );
		$message->setWidth( 600 );


		// ------------------------------------------------- Pannello di ricerca
        $label_width = 140;
		// Filtro fornitore
		$fld_cerca_forn=& $this->build("p4a_field", "fld_cerca_forn");
		$fld_cerca_forn->setLabel( "Fornitore" );
		$fld_cerca_forn->label->setWidth( $label_width );
		$fld_cerca_forn->setType("select");
		$fld_cerca_forn->setSource($this->ds_forn_ricerca);
		$fld_cerca_forn->setSourceValueField("codice");
		$fld_cerca_forn->setSourceDescriptionField("descrizione");
		$fld_cerca_forn->setWidth( 250 );
		$fld_cerca_forn->setNewValue("00");
        
		// Filtro categoria		
		$fld_cerca_cat=& $this->build("p4a_field", "fld_cerca_cat");
		$fld_cerca_cat->setLabel( "Categoria" );
		$fld_cerca_cat->label->setWidth( $label_width );
		$fld_cerca_cat->setType("select");
		$fld_cerca_cat->setSource($this->ds_tipo);
		$fld_cerca_cat->setSourceValueField("codice");
		$fld_cerca_cat->setSourceDescriptionField("descrizione");
		$fld_cerca_cat->setWidth( 250 );
        $fld_cerca_cat->setNewValue( "00" );
        $fld_cerca_cat->addAction("OnChange");
		$this->intercept($this->fld_cerca_cat, "onChange","fld_cerca_cat_change");		

		// Filtro sottocategoria		
		$fld_cerca_sottocat=& $this->build("p4a_field", "fld_cerca_sottocat");
		$fld_cerca_sottocat->setLabel( "Sottocategoria" );
		$fld_cerca_sottocat->label->setWidth( $label_width );
		$fld_cerca_sottocat->setType("select");
		$fld_cerca_sottocat->setSource($this->ds_cat);
		$fld_cerca_sottocat->setSourceValueField("codice");
		$fld_cerca_sottocat->setSourceDescriptionField("descrizione");
		$fld_cerca_sottocat->setWidth( 250 );
        $fld_cerca_sottocat->setNewValue( "000" );

		// Descrizione Articolo
		$fld_cerca_desc=& $this->build("p4a_field", "fld_cerca_desc");
		$fld_cerca_desc->setLabel( "Descrizione" );
        $fld_cerca_desc->label->setWidth( $label_width );
		$fld_cerca_desc->setWidth( 250 );
		
		// Codice articolo per ricerca (solo Equogest)
		$codarticolo=& $this->build("p4a_field", "codarticolo");
		$codarticolo->setLabel( "Codice articolo" );
        $codarticolo->label->setWidth( $label_width );
		$codarticolo->setWidth( 250 );
		$codarticolo->addAction( "onReturnPress" );
		$this->intercept($codarticolo, "onReturnPress","bu_cerca_codice_click");

        // Filtro solo biologico
        $this->build( "p4a_field", "ck_solo_bio" );
        $this->ck_solo_bio->setType( "checkbox" );
        $this->ck_solo_bio->setLabel( "Solo articoli bio" );
        $this->ck_solo_bio->setTooltip( "Solo articoli da agricoltura biologica" );
        $this->ck_solo_bio->label->setWidth( $label_width );
        
        // Filtro anche articoli disabilitati
        $this->build( "p4a_field", "ck_anche_disabilitati" );
        $this->ck_anche_disabilitati->setType( "checkbox" );
        $this->ck_anche_disabilitati->setLabel( "Anche disabilitati" );
        $this->ck_anche_disabilitati->setTooltip( "Visualizza anche gli articoli disabilitati" );
        $this->ck_anche_disabilitati->label->setWidth( $label_width );

        // Eventuali campi filtro sugli ingredienti        
        $this->build( "p4a_field", "fld_ingredienti" );
        $this->fld_ingredienti->setType( "textarea" );
        $this->fld_ingredienti->setLabel( "Ingredienti da escludere" );
        $this->fld_ingredienti->setTooltip( "Visualizza solo articoli che non includono gli ingredienti specificati (separarli con una virgola)" );
        $this->fld_ingredienti->label->setWidth( 250 );
        $this->fld_ingredienti->setWidth( 670 );
        $this->fld_ingredienti->setHeight( 50 );
        
        $result = $db->queryRow(
            "SELECT ingredienti_escludi FROM " . $p4a->e3g_prefix . "anagrafiche " .
            " WHERE idanag = " . $p4a->e3g_utente_idanag );
        $this->fld_ingredienti->setNewValue( $result["ingredienti_escludi"] );

		// Bottone "Cerca" 
		$this->build("p4a_button", "bu_cerca");
		$this->bu_cerca->setLabel("Cerca");
		$this->bu_cerca->setIcon("find");
		$this->bu_cerca->addAction("onClick");
		$this->bu_cerca->setSize( 16 );
		$this->bu_cerca->setWidth( 100 );
		$this->intercept($this->bu_cerca, "onClick", "bu_cerca_click");

		// Bottone "Annulla Ricerca"
		$this->build("p4a_button", "bu_annulla_cerca");
		$this->bu_annulla_cerca->setLabel("Annulla");
		$this->bu_annulla_cerca->setIcon("cancel");
		$this->bu_annulla_cerca->addAction("onClick");
		$this->bu_annulla_cerca->setSize( 16 );
		$this->bu_annulla_cerca->setWidth( 100 );
		$this->intercept($this->bu_annulla_cerca, "onClick", "bu_annulla_cerca_click");

		// Bottone "Cerca codice" (solo Equogest)
		$this->build("p4a_button", "bu_cerca_codice");
		$this->bu_cerca_codice->setLabel('Cerca');
		$this->bu_cerca_codice->setIcon("find");
		$this->bu_cerca_codice->addAction("onClick");
		$this->bu_cerca_codice->setSize( 16 );
		$this->bu_cerca_codice->setWidth( 100 );
		$this->intercept($this->bu_cerca_codice, "onClick", "bu_cerca_codice_click");


		// Fieldset filtro di ricerca
		$fs_ricerca=& $this->build( "p4a_fieldset", "fs_ricerca" );
		$fs_ricerca->setTitle( "Ricerca" );
		$fs_ricerca->setWidth( E3G_FIELDSET_SEARCH_WIDTH );
		$fs_ricerca->anchor( $this->fld_cerca_forn );
		$fs_ricerca->anchor( $this->fld_cerca_cat );
		$fs_ricerca->anchorLeft( $this->fld_cerca_sottocat );
		$fs_ricerca->anchor( $this->fld_cerca_desc );
        $fs_ricerca->anchor( $this->ck_solo_bio );
        $fs_ricerca->anchor( $this->ck_anche_disabilitati );
        $fs_ricerca->anchorRight( $this->bu_annulla_cerca );
        $fs_ricerca->anchorRight( $this->bu_cerca );
        if ( $p4a->e3g_utente_filtro_ingredienti ) 
            $fs_ricerca->anchor( $this->fld_ingredienti, "167px" );
		if ( E3G_TIPO_GESTIONE == 'E' ) {
			$fs_ricerca->anchor( $this->codarticolo );
			$fs_ricerca->anchorLeft( $this->bu_cerca_codice );
		}


		// ------------------------------------------------------- Vista tabella
		$table =& $this->build( "p4a_table", "table" );
		$table->setWidth( E3G_TABLE_WIDTH );
		$table->setSource( $this->ds_articoli );
        $table->showNavigationBar();
        $this->intercept( $table->rows, "afterClick", "tableAfterClick" );
        if ( E3G_TIPO_GESTIONE == 'G' ) 
			$this->intercept( $table->rows, "beforeDisplay", "tableBeforeDisplay" );  

		if ( E3G_TIPO_GESTIONE == 'G' ) {
            $table->setVisibleCols( array(
                "idarticolo", "centrale", "tipo", "bio", "descrizione", "um_qta_um", "um_qta", "gestione_a_peso", "prezzoven", "prezzo_ven_um") );
			
            $table->cols->idarticolo->setVisible( false );
            $table->cols->um_qta->setVisible( false );

            $table->cols->um_qta_um->setOrderable( false );
			$table->cols->prezzo_ven_um->setOrderable(false);
			
			$table->cols->centrale->setLabel( "Fornitore" );
            $table->cols->tipo->setLabel( "Categoria" );
            $table->cols->descrizione->setLabel( "Descrizione" );             
            $table->cols->um_qta_um->setLabel( "Conf." );
            $table->cols->gestione_a_peso->setLabel( "A peso" );
			$table->cols->prezzoven->setLabel( "Prezzo [euro]" );
			$table->cols->prezzo_ven_um->setLabel( "Prezzo/UM [euro]" );

			$table->cols->centrale->setWidth( 170 );
            $table->cols->tipo->setWidth( 140 );
            $table->cols->bio->setWidth( 20 );
//			$table->cols->descrizione->setWidth(); si prende lo spazio rimanente
            $table->cols->um_qta_um->setWidth( 50 );
            $table->cols->gestione_a_peso->setWidth( 20 );            
			$table->cols->prezzoven->setWidth( 50 );
			$table->cols->prezzo_ven_um->setWidth( 75 );
			
			$table->cols->centrale->setType("select");
			$table->cols->centrale->setSource($this->ds_forn);
			$table->cols->centrale->setSourceValueField("codice");
			$table->cols->centrale->setSourceDescriptionField("descrizione");
			
            $table->cols->tipo->setType("select");
            $table->cols->tipo->setSource($this->ds_tipo);
            $table->cols->tipo->setSourceValueField("codice");
            $table->cols->tipo->setSourceDescriptionField("descrizione");
            
	//		TODO Allineamento a destra: non ne funziona nemmeno uno!
	//		$fields->prezzo_ven_um->setStyleProperty('text-align', 'right');
	//		$table->cols->prezzo_ven_um->setStyleProperty('text-align', 'right');
	//		$table->cols->prezzo_ven_um->setFormat('number');
	//		$fields->prezzo_ven_um->data_field->setAlign('right');
	//		$fields->prezzo_ven_um->setAlign('right');
		}
		else {  // Equogest
			$table->setVisibleCols(array("barcode", "codice", "bio", "descrizione","prezzoven","codiva","aggiungi"));

			$table->cols->barcode->setLabel('Codice barre');
			$table->cols->codice->setLabel('Codice');
			$table->cols->descrizione->setLabel('Descrizione');
			$table->cols->prezzoven->setLabel('Prezzo');
			$table->cols->codiva->setLabel('IVA');
			$table->cols->aggiungi->setLabel('CARRELLO');
			
			$table->cols->barcode->setWidth(50);
			$table->cols->codice->setWidth(80);
            $table->cols->bio->setWidth(20);
//			$table->cols->descrizione->setWidth(); si prende lo spazio rimanente
			$table->cols->prezzoven->setWidth(40);
			$table->cols->codiva->setWidth(20);
			$table->cols->aggiungi->setWidth(60);
		}


		// ----------------------------------------------------- Campi dettaglio
        // Giacenza Attuale (campo specifico per Equogest)
        $fld_giacenza=& $this->build( "p4a_field", "fld_giacenza" );

		// Descrizioni etichette
        $fields->stato->setLabel( "Stato" );
        $fields->descrizione->setLabel( "Descrizione" );
        $fields->bio->setLabel( "Da agricoltura biologica" );
        $fields->desc_agg->setLabel( "Descrizione aggiuntiva" );
        $fields->centrale->setLabel( "Fornitore" );
        $fields->catmerce->setLabel( "Categoria merceol." );  // Sottocategoria
        $fields->prezzoacq->setLabel( "Prezzo acquisto [euro]" );
        $fields->prezzoven->setLabel( "Prezzo vendita [euro]" );
        $fields->gestione_a_peso->setLabel( "Gestione a peso" );
        $fields->um_qta->setLabel( "Peso/Volume" );
        $fields->um->setLabel ("Unita' di misura" );
        $fields->qtaminordine->setLabel( "Pezzi per cartone" );
        $fields->pzperconf->setLabel( "Q.ta' solo multipla di [pz]" );
        $fields->qtaminperfamiglia->setLabel( "Quantita' minima [pezzi]" );

        $fields->ingredienti->setLabel( "Ingredienti" );
        $fields->data_agg_ing->setLabel( "Ultima modifica" );

        $fields->barcode->setLabel( "Barcode" );
        $fields->codiva->setLabel( "IVA" );
        $fields->sconto1->setLabel( "Sconto 1" );
        $fields->contovendita->setLabel( "Conto Ven." );
        $fields->contoacquisto->setLabel( "Conto Acq." );
        $fields->progetto->setLabel( "Progetto" );
        $fields->scortaminima->setLabel( "Scorta minima" );
        $fields->paese->setLabel( "Paese" );
        $fields->tipo->setLabel( "Tipo Articolo" );  // Categoria
        $fields->posizione->setLabel( "Posizione" );
        $fld_giacenza->setLabel( "Giacenza" );

        // Tooltip
        $fields->gestione_a_peso->setTooltip( "Per gli articoli ordinabili a peso, il peso/volume deve essere unitario" );
        $fields->ingredienti->setTooltip( "Separarli con una virgola" );
        $fields->data_agg_ing->setTooltip( "Data di ultima modifica degli ingredienti" );
        
        
        // setType
        $fields->bio->setType( "checkbox" );
        $fields->desc_agg->setType( "textarea" );
        $fields->prezzoacq->data_field->setType( "float" );
        $fields->prezzoven->data_field->setType( "float" );
        $fields->gestione_a_peso->setType( "checkbox" );
        $fields->sconto1->data_field->setType( "decimal" );
        $fields->ingredienti->setType( "textarea" );
        
        
		// Larghezze etichette
		while ( $field =& $fields->nextItem() ) 
			$field->label->setWidth( 150 );
        $fields->bio->label->setWidth( 140 );
        $fld_giacenza->label->setWidth( 75 );

		// Larghezze campi
        $fields->sconto1->setWidth(50);
        $fields->scortaminima->setWidth(50);

		$fields->codice->setWidth(100);

        $fields->desc_agg->setWidth( 660 );
        $fields->desc_agg->setHeight( 30 );  // 2 righe

        $fields->prezzoacq->setWidth(100);
        $fields->prezzoven->setWidth(100);
        $fields->um->setWidth(100);
        $fields->barcode->setWidth(100);
        $fields->pzperconf->setWidth(100);
        $fields->um_qta->setWidth(100);
        $fields->qtaminordine->setWidth(100);
        $fields->qtaminperfamiglia->setWidth(100);
        $fields->stato->setWidth(100);

        $fields->ingredienti->setWidth( 660 );
        $fields->ingredienti->setHeight( 45 );  // 3 righe

        $fields->contovendita->setWidth(150);
        $fields->contoacquisto->setWidth(150);
        
		$fields->descrizione->setWidth(400);
        $fields->centrale->setWidth(400);
		$fields->catmerce->setWidth(400);
        $fld_giacenza->setWidth(50);

        // Colori        
        $fields->descrizione->setFontColor( "black" );
        $fields->descrizione->label->setFontColor( "black" );

		// Allineamento
		$fields->prezzoacq->setStyleProperty('text-align', 'right');
		$fields->prezzoven->setStyleProperty('text-align', 'right');

		// Select "stato"
		$fields->stato->setType("select");
		$fields->stato->setSource($this->ds_stato);
		$fields->stato->setSourceValueField("codice");
		$fields->stato->setSourceDescriptionField("descrizione");

		// Select "unità misura"
		$fields->um->setType('select');
		$fields->um->setSourceValueField('codice');
		$fields->um->setSourceDescriptionField('desc_plurale');
		$fields->um->setSource($this->ds_um);
		
		// Select "fornitore"
		$fields->centrale->setType('select');
		$fields->centrale->setSourceValueField('codice');
		$fields->centrale->setSourceDescriptionField('descrizione');
		$fields->centrale->setSource($this->ds_forn);

        // Select "categoria merceologica"
        $fields->catmerce->setType('select');
        $fields->catmerce->setSourceValueField('codice');
        $fields->catmerce->setSourceDescriptionField('descrizione');
        $fields->catmerce->setSource($this->ds_cat_sotcat);

        if ( E3G_TIPO_GESTIONE == 'E' ) {
            // Select "Tipo articolo"
            $fields->tipo->setType('select');
            $fields->tipo->setSource($this->ds_tipo);
            $fields->tipo->setSourceValueField('codice');
            $fields->tipo->setSourceDescriptionField('descrizione');
            $fields->tipo->addAction("OnChange");
            $this->intercept($fields->tipo, "onChange","seleztipo_click");
            
    		// Select "IVA"
    		$fields->codiva->setType('select');
            $fields->codiva->setSource($this->ds_iva);
    		$fields->codiva->setSourceValueField('codice');
    		$fields->codiva->setSourceDescriptionField('descrizione');
    
            // Select "Conto vendite"
            $fields->contovendita->setType("select");
            $fields->contovendita->setSource($this->ds_con_ven);
            $fields->contovendita->setSourceValueField("codice");
            $fields->contovendita->setSourceDescriptionField("descrizione");
            
            // Select "Conto acquisto"
            $fields->contoacquisto->setType("select");
            $fields->contoacquisto->setSource($this->ds_con_acq);
            $fields->contoacquisto->setSourceValueField("codice");
            $fields->contoacquisto->setSourceDescriptionField("descrizione");
        }
        

        // ---------------------------------------------------- Ancoraggio campi
        
		if ( E3G_TIPO_GESTIONE == 'G' ) {
			$fs_gg_dettaglio =& $this->build( "p4a_fieldset", "fs_gg_dettaglio" );
			$fs_gg_dettaglio->setTitle( "Dettaglio" );
			$fs_gg_dettaglio->setWidth( E3G_FIELDSET_DATI_WIDTH );

			$fs_gg_dettaglio->anchor( $fields->codice );
            $fs_gg_dettaglio->anchorLeft( $fields->stato );
			$fs_gg_dettaglio->anchor( $fields->descrizione );
            $fs_gg_dettaglio->anchorLeft( $fields->bio );
            $fs_gg_dettaglio->anchor( $fields->desc_agg, "177px" );
			$fs_gg_dettaglio->anchor( $fields->centrale );
			$fs_gg_dettaglio->anchor( $fields->catmerce );

			$fs_gg_dettaglio->anchor( $fields->prezzoacq );  
			if ( $p4a->e3g_azienda_mostra_prezzo_sorgente )
				$fs_gg_dettaglio->anchorLeft( $fields->prezzoven );
            $fs_gg_dettaglio->anchorLeft( $fields->gestione_a_peso );  

			$fs_gg_dettaglio->anchor( $fields->um_qta );
			$fs_gg_dettaglio->anchorLeft( $fields->um );
			$fs_gg_dettaglio->anchorLeft( $fields->qtaminordine );

			$fs_gg_utente =& $this->build( "p4a_fieldset", "fs_gg_utente" );
			$fs_gg_utente->setTitle( "Ordine utente");
			$fs_gg_utente->setWidth( E3G_FIELDSET_DATI_WIDTH );
			$fs_gg_utente->anchor( $fields->pzperconf );
			$fs_gg_utente->anchorLeft( $fields->qtaminperfamiglia );

            $fs_gg_ingredienti =& $this->build( "p4a_fieldset", "fs_gg_ingredienti" );
            $fs_gg_ingredienti->setTitle( "Ingredienti" );
            $fs_gg_ingredienti->setWidth( E3G_FIELDSET_DATI_WIDTH );
            $fs_gg_ingredienti->anchor( $fields->ingredienti, "177px" );
            $fs_gg_ingredienti->anchor( $fields->data_agg_ing );
		}
		else {  // Ancoraggio campi Equogest
			$sh_eq_dettaglio =& $this->build("p4a_sheet", "sh_eq_dettaglio");
	        $this->sh_eq_dettaglio->defineGrid(14, 4);
	        $this->sh_eq_dettaglio->setWidth( E3G_FIELDSET_DATI_WIDTH );
	
			$this->sh_eq_dettaglio->anchor( $fields->codice,1,1,1,2);
			$this->sh_eq_dettaglio->anchor( $fields->barcode,1,3,1,2);
	        $this->sh_eq_dettaglio->anchor( $fields->descrizione,2,1,1,3);
			$this->sh_eq_dettaglio->anchor( $this->giacart,2,4);
			$this->sh_eq_dettaglio->anchor( $fields->prezzoven,3,1);
			$this->sh_eq_dettaglio->anchor( $fields->codiva,3,3);
			$this->sh_eq_dettaglio->anchor( $fields->sconto1,3,4);
			$this->sh_eq_dettaglio->anchor( $fields->um,4,1,1,2);
			$this->sh_eq_dettaglio->anchor( $fields->um_qta,4,3,1,2);
			$this->sh_eq_dettaglio->anchor( $fields->centrale,5,1,1,4);
            $this->sh_eq_dettaglio->anchor( $fields->tipo,6,1,1,3);
            $this->sh_eq_dettaglio->anchor( $fields->bio,6,4);
			$this->sh_eq_dettaglio->anchor( $fields->catmerce,7,1,1,4);
			$this->sh_eq_dettaglio->anchor( $fields->pzperconf,8,1,1,2);
			$this->sh_eq_dettaglio->anchor( $fields->scortaminima,8,3,1,2);
			$this->sh_eq_dettaglio->anchor( $fields->progetto,10,1,1,4);
			$this->sh_eq_dettaglio->anchor( $fields->paese,11,1,1,4);
			$this->sh_eq_dettaglio->anchor( $fields->posizione,12,1,1,4);
			$this->sh_eq_dettaglio->anchor( $fields->contovendita,13,1,1,4);
			$this->sh_eq_dettaglio->anchor( $fields->contoacquisto,14,1,1,4);
		}


		// ---------------------------------------------------------------- Date
		$fields->data_ins->setLabel("Inserimento");
		$fields->data_agg->setLabel("Ultima modifica");

		// Fieldset con le date ins e agg
		$fs_date=& $this->build("p4a_fieldset", "fs_date");
		$fs_date->setTitle("Date");
		$fs_date->setWidth( E3G_FIELDSET_DATI_WIDTH );
		$fs_date->anchor($fields->data_ins);
		$fs_date->anchorLeft($fields->data_agg);


		// --------------------------- Abilitazione campi in base al tipo utente 
		$this->abilitazione_campi();


		// ---------------------------------------------------- Frame principale
		$frm=& $this->build("p4a_frame", "frm");
		$frm->setWidth( E3G_MAIN_FRAME_WIDTH );

		$frm->anchor( $fs_ricerca );
        $frm->anchor( $message );
		$frm->anchor( $this->table );
		if ( E3G_TIPO_GESTIONE == 'G' ) {
			$frm->anchor( $this->fs_gg_dettaglio );
			$frm->anchor( $this->fs_gg_utente );
            $frm->anchor( $this->fs_gg_ingredienti );
		}
		else {
			$frm->anchor( $this->sh_eq_dettaglio );
		}
		$frm->anchor( $fs_date );
		
		e3g_scrivi_footer( $this, $frm );

  		// Display
		$this->display("main", $frm);
		$this->display("menu", $p4a->menu);
		$this->display("top", $this->toolbar);
        
        $this->bu_cerca_click();
	}

	
    // -------------------------------------------------------------------------
    function main()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();

        // Se non ci sono record di articoli, allora la finestra si predispone
        // in inserimento, ma bisogna generare l'evento newRow()
        if ( $this->data->getNumRows() == 0 ) {
            $this->newRow();
            // devo abilitare comunque i campi 
            // perchè se entro come REF me li trovo disabilitati 
            $this->abilita_campi();                    
        }
                    
        parent::main();

        if ( $this->newrecord ) {
            // se sono passato per newRow abilito comunque il campo codice 
            $this->fields->codice->enable();
            $this->newrecord = false;
        }
        
        
        foreach($this->mf as $mf)
            $this->fields->$mf->unsetStyleProperty("border");
    }

    
    // -------------------------------------------------------------------------
	function tableAfterClick ($tmp, $parametri)
    // -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();

		$this->setGiacenza( $this->fields->codice->getNewValue() );
		$this->abilitazione_campi();
	}
	
	
    // -------------------------------------------------------------------------
	function seleztipo_click ()
    // -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();

		$this->ds_cat_sotcat->setTable($p4a->e3g_prefix."catmerceologica");
		$this->ds_cat_sotcat->setWhere("tipo='".$this->fields->tipo->getNewValue()."'");
		$this->ds_cat_sotcat->load();
		$this->ds_cat_sotcat->firstRow();
	}

	
    // -------------------------------------------------------------------------
	function creabarcode( $idart )
    // -------------------------------------------------------------------------
	{
	    $codice = "";
	    $numerobc = 0;
	    $numerobc2 = 0;
	    $cifra13 = 0;

	    $codice = "80" . "00000" . str_replace(",", "", strval($idart));
		
	    $numerobc = $numerobc + (intval(substr($codice, 0, 1)) * 1);
	    $numerobc = $numerobc + (intval(substr($codice, 1, 1)) * 3);
	    $numerobc = $numerobc + (intval(substr($codice, 2, 1)) * 1);
	    $numerobc = $numerobc + (intval(substr($codice, 3, 1)) * 3);
	    $numerobc = $numerobc + (intval(substr($codice, 4, 1)) * 1);
	    $numerobc = $numerobc + (intval(substr($codice, 5, 1)) * 3);
	    $numerobc = $numerobc + (intval(substr($codice, 6, 1)) * 1);
	    $numerobc = $numerobc + (intval(substr($codice, 7, 1)) * 3);
	    $numerobc = $numerobc + (intval(substr($codice, 8, 1)) * 1);
	    $numerobc = $numerobc + (intval(substr($codice, 9, 1)) * 3);
	    $numerobc = $numerobc + (intval(substr($codice, 10, 1)) * 1);
	    $numerobc = $numerobc + (intval(substr($codice, 11, 1)) * 3);

	    $numerobc2 = $numerobc / 10;
	    if ( intval($numerobc2) == $numerobc2 )  
	        $cifra13 = 0;
	    else 
	    	$cifra13  = ((intval($numerobc2) + 1) * 10) - $numerobc;
				
		return $codice.strval($cifra13);
	}

	
    // -------------------------------------------------------------------------
	function fld_cerca_cat_change()
    // -------------------------------------------------------------------------
	{
		$this->ds_cat->setWhere( "tipo = '" . $this->fld_cerca_cat->getNewValue() . "' OR codice = '000'" );		
		$this->ds_cat->load();
	}


    // -------------------------------------------------------------------------
	function bu_cerca_click()
    // -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();

		$str_where = "1 = 1";
		
		// Fornitore
		if ( $this->fld_cerca_forn->getNewValue() != "00" )
			$str_where .= " AND centrale = '" . $this->fld_cerca_forn->getNewValue() . "'";

		// Categoria/tipo
		if ( $this->fld_cerca_cat->getNewValue() != "00" )
			$str_where .= " AND tipo = '" . $this->fld_cerca_cat->getNewValue() . "'";
		
		// Sottocategoria/catmerceologica
		if ( $this->fld_cerca_sottocat->getNewValue() != "000" )
			$str_where .= " AND catmerce = '" . $this->fld_cerca_sottocat->getNewValue() . "'";
		
		// DESCRIZIONE 
		if ( $this->fld_cerca_desc->getNewValue() != "" )
			$str_where .= " AND UCASE(descrizione) LIKE '%" . addslashes( strtoupper(trim($this->fld_cerca_desc->getNewValue())) ) . "%'";

        // Articolo biologico
        if ( $this->ck_solo_bio->getNewValue() == 1 ) $str_where .= " AND bio = 1";
        
        // Visualizza anche articoli disabilitati [ 1:Abilitato 2:Disabilitato (Non si usa 0:In attesa) ]
        if ( $this->ck_anche_disabilitati->getNewValue() == 0 ) $str_where .= " AND stato = 1";

        // Filtro ingredienti (esclude articoli con ingredienti elencati; con questa query 
        // vengono giustamente esclusi anche quegli articoli che non hanno la specifica degli ingredienti)
        if ( $p4a->e3g_utente_filtro_ingredienti and ( $this->fld_ingredienti->getNewValue() <> "" )  ) {
            $where_ing = "";
            $this->fld_ingredienti->setNewValue( ucfirst(strtolower(trim( $this->fld_ingredienti->getNewValue() ))) );
            $ingredienti = explode( ",", $this->fld_ingredienti->getNewValue() );
            foreach( $ingredienti as $ingrediente ) 
                $where_ing .= " AND NOT FIND_IN_SET( '" . addslashes( strtoupper(trim($ingrediente)) ) . "', REPLACE( UCASE(ingredienti), ' ', '' ) ) ";
            $str_where .= $where_ing;
        }

        $oldwhere = $this->data->getWhere();
		$this->data->setWhere( $str_where );

		if ( $this->data->getNumRows() == 0 ) {
			$this->message->setValue( "Nessun articolo trovato." );
            $this->data->setWhere( $oldwhere );
		}
        $this->data->firstRow();
        $this->table->syncPageWithSource();
        $this->table->setTitle( $this->data->getNumRows() . " articol" . ( $this->data->getNumRows()==1 ? "o" : "i" ) );
        
		$this->abilitazione_campi();


		$this->fld_cerca_desc->setValue( "" );

		// carico la giacenza per l'articolo richiamato
        $this->setGiacenza( $this->fields->codice->getNewValue() );
	}

	
    // -------------------------------------------------------------------------
	function bu_cerca_codice_click()
    // -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();

		$cod_art_old = $this->codarticolo->getNewValue();
		$this->data->setWhere( "codice = '" . $cod_art_old . "' OR barcode = '" . $cod_art_old . "'" );
		$this->data->firstRow();
		$num_rows = $this->data->getNumRows();

		if ( !$num_rows ) {
			$this->message->setValue( "Nessun articolo trovato." );
			$this->data->setWhere( null) ;
			$this->data->firstRow();
		}

		$this->abilitazione_campi();

		$this->codarticolo->setValue( '' );

		// carico la giacenza per l'articolo richiamato
        $this->setGiacenza( $cod_art_old );
	}

	
    // -------------------------------------------------------------------------
	function bu_annulla_cerca_click()
    // -------------------------------------------------------------------------
	{
		$this->fld_cerca_forn->setNewValue( "00" );
		$this->fld_cerca_cat->setNewValue( "00" );
		$this->fld_cerca_sottocat->setNewValue( "000" );
		$this->fld_cerca_desc->SetNewValue( "" );
        $this->ck_solo_bio->setNewValue( 0 );
        $this->ck_anche_disabilitati->setNewValue( 0 );
		$this->codarticolo->SetNewValue( "" );
        $this->fld_ingredienti->SetNewValue( "" );

		$this->bu_cerca_click();
	}


    // Provo a Calcolare la Giacenza (solo Equogest)    
    // -------------------------------------------------------------------------
  	function setGiacenza( $codicearticolo )
    // -------------------------------------------------------------------------
  	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
        if ( E3G_TIPO_GESTIONE == 'E' ) {
    		// Azzero le variabili
    		$oggi = date( "y-m-d" );
    		$ultimagiacenza = "";
    		$totale = 0;
    
    		// recupero la data dell'ultimo movimento di Giacenza
    		$query = "SELECT data FROM " . $p4a->e3g_prefix . "movmagr WHERE carscar ='G' AND codarticolo='".$codicearticolo."' AND data<='".$oggi."' ORDER BY data DESC";
    		$ultimagiacenza = $db->queryOne($query);
    
    		if ( $ultimagiacenza != "" ) {
    			// ho la data dell'ultima giacenza
    			// ricavo subito la giacenza
    			$query = "SELECT SUM(qta) AS quantita FROM ".$p4a->e3g_prefix."movmagr WHERE codarticolo='".$codicearticolo."' AND data>='".$ultimagiacenza."' AND data<='".$oggi."'";
    			$totale = $db->queryOne($query);
    		}
    		else {
    			// non ho l'ultima giacenza, quindi non filtro per data
    			// Ricavo la qta caricata dall'inizio del database
    			$query = "SELECT SUM(qta) AS quantita FROM ".$p4a->e3g_prefix."movmagr WHERE codarticolo='".$codicearticolo."' AND data<='".$oggi."'";
    			$totale = $db->queryOne($query);
    		}
    
    		// mostro la giacenza ad oggi
            $this->fld_giacenza->setValue( $totale );
        }
    }

			
    // -------------------------------------------------------------------------
	function newRow()
    // -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
		$this->newrecord = true;
		
		// Memorizza alcuni campi per riproporli
		$prec_centrale = $this->fields->centrale->getNewValue();
   		$prec_catmerce = $this->fields->catmerce->getNewValue();
        $prec_bio = $this->fields->bio->getNewValue();
        $prec_a_peso = $this->fields->gestione_a_peso->getNewValue();
		
		
		parent::newRow();


		$this->fields->centrale->setNewValue( $prec_centrale );
   		$this->fields->catmerce->setNewValue( $prec_catmerce );
        if ( isset($prec_bio) )
            $this->fields->bio->setNewValue( $prec_bio );
        else 
            $this->fields->bio->setNewValue( 0 );
        if ( isset($prec_a_peso) )
            $this->fields->gestione_a_peso->setNewValue( $prec_a_peso );
        else 
            $this->fields->gestione_a_peso->setNewValue( 0 );

		$this->fields->pzperconf->setNewValue( 1 );
		$this->fields->qtaminordine->setNewValue( 1 );
		$this->fields->qtaminperfamiglia->setNewValue( 1 );
		$this->fields->stato->setNewValue( 1 );  // 1:Abilitato
		
		// Propone un codice del tipo A0000 (il controllo di unicità è nel saveRow)
		$maxid = $db->queryOne(
    		"SELECT MAX( idarticolo ) FROM " . $p4a->e3g_prefix . "articoli" );
		if ( is_numeric($maxid) )
			$maxid++;
		else 
			$maxid = 1;	
		
        $this->abilitazione_campi();
		
		$this->fields->codice->setNewValue( "A" . sprintf( "%04d", $maxid ) );
		$this->fields->codice->enable();  // Solo in inserimento è possibile modificarlo
	}
			
			
    // -------------------------------------------------------------------------
	function saveRow()
    // -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();

		$error_text = "";

				
		// Verifica campi obbligatori
		foreach ( $this->mf as $mf ) {
			$value = $this->fields->$mf->getNewValue();
			if (trim($value) === "") {
				$this->fields->$mf->setStyleProperty( "border", "1px solid red" );
				$error_text = "Compilare i campi obbligatori.";
			}
		}
        
        // Verifica assegnazione categoria/sotto-categoria (obbligatori)
        if ( $this->fields->catmerce->getNewValue() == "000" ) {
            $this->fields->catmerce->setStyleProperty( "border", "1px solid red" );
            $error_text = "Compilare la categoria.";
        }
        // Articoli a peso: verifica che il peso/volume sia unitario
        elseif ( $this->fields->gestione_a_peso->getNewValue() and !( $this->fields->um_qta->getUnformattedNewValue() == 1 ) ) { 
            $this->fields->um_qta->setStyleProperty( "border", "1px solid red" );
            $error_text = "Per gli articoli ordinabili a peso, il peso/volume deve essere unitario.";
        }
		
		// Verifica associazione Referente/Fornitore
		if ( $error_text == "" ) {
			if ( $p4a->e3g_utente_tipo == "R" ) {
				$pos = strpos( $p4a->e3g_where_referente, "'" . $this->fields->centrale->getNewValue() . "'" );
				if ( $pos === false ) 
					$error_text = "Questo fornitore e' assegnato ad un altro referente.";
			}
		}
			
			
		if ( $error_text == "" )
		{
			//if ( $this->newrecord )
			if ( !is_numeric($this->fields->idarticolo->getNewValue()) )
			{
				$maxid = $db->queryOne(
					"SELECT MAX( idarticolo ) FROM " . $p4a->e3g_prefix . "articoli" );
				if ( is_numeric ($maxid) )
					$maxid++;
				else
					$maxid = 1;
				$this->fields->idarticolo->SetNewValue( $maxid );
				$this->fields->data_ins->setNewValue( date ("Y-m-d H:i:s") );
			}

			// Verifica campo codice non duplicato 
			if ( $this->fields->codice->getNewValue() != "" && $this->newrecord ) 
			{
				$n = $db->queryOne("SELECT COUNT(*) FROM " . $p4a->e3g_prefix . "articoli WHERE codice = '" . $this->fields->codice->getNewValue() . "'" );
				if ( $n > 0 )
				{
					$error_text = "Codice '" . $this->fields->codice->getNewValue() . "' già presente.";
					$this->fields->codice->enable();
				}
			}
		}
		
		
		if ( $error_text == "" ) {
			// Setta alcuni valori prima del salvataggio vero e proprio			
			$tipo = $db->queryOne( 
				"SELECT tipo FROM " . $p4a->e3g_prefix . "catmerceologica WHERE codice = '" . $this->fields->catmerce->getnewValue() . "'" );
			$this->fields->tipo->SetNewValue($tipo);

            // Campi gestione ingredienti
            $this->fields->ingredienti->setNewValue( ucfirst(strtolower(trim( $this->fields->ingredienti->getNewValue() ))) );
            if ( $this->fields->ingredienti->getNewValue() == "" ) 
                $this->fields->data_agg_ing->setNewValue( "" );
            elseif ( $this->fields->ingredienti->getNewValue() <> $this->fields->ingredienti->getValue() ) 
                $this->fields->data_agg_ing->setNewValue( date ("Y-m-d H:i:s") );

			if ( E3G_TIPO_GESTIONE == 'E' ) 
			{
				// In ogni caso se il codice a barre è vuoto lo compilo io
				if ( trim($this->fields->barcode->getNewValue() ) == '' ) {
					$numeroid = str_pad(str_replace(".","",strval($this->fields->idarticolo->GetNewValue())),5, "0", STR_PAD_LEFT );
					$codicebar = $this->creabarcode($numeroid);
					$this->fields->barcode->setNewValue($codicebar);
				}
			}

			if ( E3G_TIPO_GESTIONE == 'G' ) 
			{
				switch ( $p4a->e3g_azienda_tipo_gestione_prezzi ) {
					case 0:  // Prezzo vendita utente = prezzo acquisto fornitore
					case 1:  // Maggiorazione fissa per ogni ordine (la magg. viene eseguita all'atto dell'ordine)
						$nuovo_prezzoven = $this->fields->prezzoacq->getUnformattedNewValue();
						$this->fields->prezzoven->setValue( $nuovo_prezzoven );
						break;
					case 2:  // Maggiorazione percentuale sul prezzo d'acquisto									
						$nuovo_prezzoven = $this->fields->prezzoacq->getUnformattedNewValue() * ( 1 + $p4a->e3g_azienda_prezzi_mag_perc/100 );
						$this->fields->prezzoven->setValue( $nuovo_prezzoven );
						break;
				}
			}
			
            $this->fields->data_agg->setNewValue( date ("Y-m-d H:i:s") );
			
			parent::saveRow();


			$this->newrecord = false;
            $this->table->syncPageWithSource();
		}
		else
			$this->message->setValue( $error_text );
	}


    // -------------------------------------------------------------------------
	function nextRow()
    // -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();

		parent::nextRow();
	
        $this->setGiacenza( $this->fields->codice->getNewValue() );
		$this->abilitazione_campi();
	}

	
    // -------------------------------------------------------------------------
	function prevRow()
    // -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();

		parent::prevRow();
		
        $this->setGiacenza( $this->fields->codice->getNewValue() );
		$this->abilitazione_campi();
	}


    // -------------------------------------------------------------------------
	function firstRow()
    // -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();
		
		parent::firstRow();
		
        $this->setGiacenza( $this->fields->codice->getNewValue() );
		$this->abilitazione_campi();
	}


    // -------------------------------------------------------------------------
	function lastRow()
    // -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();
		
		parent::lastRow();
		
        $this->setGiacenza( $this->fields->codice->getNewValue() );
		$this->abilitazione_campi();
	}

	
    // -------------------------------------------------------------------------
	function deleteRow()
    // -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();
		$db =& p4a_db::singleton();
		
		$articolo_usato = $db->queryOne(
			"SELECT COUNT(*) FROM " . $p4a->e3g_prefix . "docr WHERE codice = '" . $this->fields->codice->getNewValue() . "'" );
		$articolo_carrello = $db->queryOne(
			"SELECT COUNT(*) FROM " . $p4a->e3g_prefix . "carrello WHERE codarticolo = '" . $this->fields->codice->getNewValue() . "'" );
		
        $txt_no_delete = ": se si tratta di un articolo non piu' disponibile allora e' sufficiente disabilitarlo (campo 'Stato').";
        
        if ( is_numeric($articolo_usato) and $articolo_usato > 0  ) 
            $this->message->setValue( "Eliminazione articolo NON consentita in quanto e' usato in alcuni documenti" . $txt_no_delete );
        elseif ( is_numeric($articolo_carrello) and $articolo_carrello > 0 ) 
			$this->message->setValue( "Eliminazione articolo NON consentita in quanto e' attualmente in ordine da qualche utente" . $txt_no_delete );
		else 
			parent::deleteRow();	
	}

	
    // Sia per GG che EQ, si abilita tutto, tranne il campo codice	
    // -------------------------------------------------------------------------
	function abilita_campi()
    // -------------------------------------------------------------------------
	{
		while ( $field =& $this->fields->nextItem() ) 
			$field->enable();

    	$this->fields->codice->disable();
	} 

    					
    // -------------------------------------------------------------------------
	function disabilita_campi()
    // -------------------------------------------------------------------------
	{
		while ( $field =& $this->fields->nextItem() ) 
			$field->disable();
	} 

		
    // -------------------------------------------------------------------------
	function abilitazione_campi()
    // -------------------------------------------------------------------------
	{
		$p4a =& p4a::singleton();

		if ( E3G_TIPO_GESTIONE == 'G' ) {
           	switch ($p4a->e3g_utente_tipo) {
                case "A":
                case "AS":
					$this->abilita_campi();                    
					break;

                case "R":
					$pos = strpos( $p4a->e3g_where_referente, "'" . $this->fields->centrale->getNewValue() . "'" );
					if ( $pos === false and !$this->ds_articoli->isNew() ) 
			    		$this->disabilita_campi();                    
					else 
			    		$this->abilita_campi();
			    		                    
					break;
					
                case "U":
                    $this->disabilita_campi();                    
                    if ( $p4a->e3g_utente_modifica_ingredienti )  // Utente abilitato alla modifica degli ingredienti
                        $this->fields->ingredienti->enable();
                    break;

				default:
					$this->disabilita_campi();                    
					break;
            }

			// Prezzo di vendita sempre disabilitato perchè calcolato dal sw	
			$this->fields->prezzoven->disable();
		}
		else {
			$this->abilita_campi();
		}

		// Date sempre disabilitate perchè impostate solo dal sw
        $this->fields->data_agg_ing->disable();
		$this->fields->data_ins->disable();
		$this->fields->data_agg->disable();
	}


    // Evidenzia le righe dei prodotti non attivi ($obj è l'oggetto che ha scatenato l'evento)
    // -------------------------------------------------------------------------
    function tableBeforeDisplay( $obj, $rows ) 
    // -------------------------------------------------------------------------
    {  
        for( $i=0; $i<count($rows); $i++ ) {
            // Campi visualizzati: array("idarticolo", "centrale", "descrizione", "um_qta_um", "um_qta", "prezzoven", "prezzo_ven_um", "qtaminordine", "qtaminperfamiglia"));
            if ( $rows[$i]["idarticolo"] == $this->fields->idarticolo->getNewValue() )
                $rows[$i]["descrizione"] = "<span style='color:black;font-weight:bold;'>" . $rows[$i]["descrizione"] . "</span>";
            if ( $rows[$i]["um_qta"] == "" )
                $rows[$i]["um_qta_um"] = "";
            if ( $rows[$i]["stato"] <> 1 )  // 1:Abilitato 2:Disabilitato  
                $rows[$i]["descrizione"] = "<strike>" . $rows[$i]["descrizione"] . "</strike>";

            $rows[$i]["bio"] = ( $rows[$i]["bio"] == 1 ? "Bio" : "" );
            $rows[$i]["gestione_a_peso"] = ( $rows[$i]["gestione_a_peso"] == 1 ? "Si" : "" );
        }  
        return $rows;  
    }  

}

?>