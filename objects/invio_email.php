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


class invio_email extends P4A_Mask
{
    // -------------------------------------------------------------------------
	function invio_email()
    // -------------------------------------------------------------------------
	{
        $this->p4a_mask();
        $this->addCss( E3G_TEMPLATE_DIR . 'css/style.css' );
        $p4a =& p4a::singleton();
        $db  =& p4a_db::singleton();
        
        $this->setTitle( "Nuovo messaggio e-mail" );
        $this->setIcon( "mail_new" );
		
        
        // Toolbar con: invia, annulla, chiudi
        $this->build("p4a_actions_toolbar", "toolbar");
        $this->toolbar->buttons->save->setIcon( "mail_send" );
        $this->toolbar->buttons->save->setLabel( "Invia messaggio" );
        $this->toolbar->buttons->save->requireConfirmation( "onClick", "Confermi l'invio del messaggio ?" );
        $this->toolbar->buttons->cancel->setInvisible();
        $this->toolbar->setMask($this);

        // Eventuale warning
        $this->build( "p4a_message", "msg_info" );
        $this->msg_info->setWidth( 700 );

        // Mittente
        $values_mit = array();
        $values_mit[] = array("id" => "1", "desc" => MAIL_FROM_NAME . ' <' . MAIL_FROM . '>');
        $values_mit[] = array("id" => "2", "desc" => $p4a->e3g_utente_desc . ' <' . $p4a->e3g_utente_email . '>');
        $this->build("p4a_array_source", "array_source_mit"); 
        $this->array_source_mit->load( $values_mit ); 
        $this->array_source_mit->setPk( "id" ); 

        $this->build("p4a_field", "fld_mittente");
        $this->fld_mittente->setLabel( "Mittente" );
        $this->fld_mittente->setWidth( 550 );
        $this->fld_mittente->setType( "select" );
        $this->fld_mittente->setSource( $this->array_source_mit ); 
        $this->fld_mittente->setSourceDescriptionField( "desc" );
        $this->fld_mittente->setValue( "2" );

        $this->fld_mittente->addAction( "onChange" );
        $this->intercept( $this->fld_mittente, "onChange", "fld_mittenteChange" );


        // Rispondi a 
        $this->build("p4a_field", "fld_rispondi");
        $this->fld_rispondi->setLabel( "Rispondi a" );
        $this->fld_rispondi->setWidth( 550 );
        $this->fld_rispondi->disable();


        // DB source per il destinatario utente
        $this->build( "p4a_db_source", "ds_utenti" );
        $this->ds_utenti->setSelect( "idanag, descrizione, email," .
                " CONCAT( descrizione, ' <', email, '> ' ) AS desc_view " ); 
        $this->ds_utenti->setTable( $p4a->e3g_prefix . "anagrafiche" );
        $this->ds_utenti->setWhere( "tipocfa = 'C' AND tipoutente <> 'A' AND stato = 1" );
        $this->ds_utenti->addOrder( "descrizione" );
        $this->ds_utenti->setPk( "idanag" );
        $this->ds_utenti->load();


        // DB source per il destinatario fornitore
        $this->build( "p4a_db_source", "ds_fornitori" );
        $this->ds_fornitori->setSelect( "idanag, descrizione, email," .
                " CONCAT( descrizione, ' <', email, '> ' ) AS desc_view " ); 
        $this->ds_fornitori->setTable( $p4a->e3g_prefix . "anagrafiche" );
        $this->ds_fornitori->setWhere( "tipocfa = 'F' AND stato = 1 AND TRIM( email ) <> ''" );
        $this->ds_fornitori->addOrder( "descrizione" );
        $this->ds_fornitori->setPk( "idanag" );
        $this->ds_fornitori->load();


        // Radio button del destinatario        
        $values_dst = array();
        if ( $p4a->e3g_utente_tipo == 'A' ) {  // Superadmin può inviare anche a tutti gli amministratori 
            $values_dst[] = array("id" => "1", "desc" => "Tutti gli amministratori delle varie gestioni");
            $values_dst[] = array("id" => "2", "desc" => "Tutti gli amministratori e referenti delle varie gestioni");
        }
        if ( E3G_TIPO_GESTIONE == 'G' ) {
        	// Versione Gestigas: utenti, referenti e fornitori
        	$values_dst[] = array("id" => "3", "desc" => "Tutti i " . $this->ds_utenti->getNumRows() . " utenti attivi");
            $values_dst[] = array("id" => "4", "desc" => "Tutti i referenti");
            $values_dst[] = array("id" => "5", "desc" => "Tutti i " . $this->ds_fornitori->getNumRows() . " fornitori dotati di indirizzo e-mail e attivi");
            $values_dst[] = array("id" => "6", "desc" => "Utente:");
            $values_dst[] = array("id" => "7", "desc" => "Fornitore:");
		}
		else {
        	// Equogest: previsti solo i clienti e fornitori
        	$values_dst[] = array("id" => "3", "desc" => "Tutti i " . $ds_utenti->getNumRows() . " utenti attivi");
        	$values_dst[] = array("id" => "4", "desc" => "Tutti i Clienti");
            $values_dst[] = array("id" => "5", "desc" => "Tutti i " . $this->ds_fornitori->getNumRows() . " fornitori dotati di indirizzo e-mail e attivi");
        	$values_dst[] = array("id" => "6", "desc" => "Cliente:");			
            $values_dst[] = array("id" => "7", "desc" => "Fornitore:");
		}
        $this->build("p4a_array_source", "array_source_dst"); 
        $this->array_source_dst->load( $values_dst ); 
        $this->array_source_dst->setPk( "id" ); 
        
        $this->build("p4a_field", "fld_destinatario");
        $this->fld_destinatario->setLabel( "Destinatario" );
        $this->fld_destinatario->setWidth( 550 );
        $this->fld_destinatario->setType( "radio" );
        $this->fld_destinatario->setSource( $this->array_source_dst ); 
        $this->fld_destinatario->setValue( "3" );
        $this->fld_destinatario->addAction( "onChange" );
        $this->intercept( $this->fld_destinatario, "onChange", "fld_destinatarioChange" );
        

        // Destinatario singolo utente/cliente o fornitore
        $this->build("p4a_field", "fld_clifor");
        $this->fld_clifor->setLabel( "" );
        $this->fld_clifor->setWidth( 480 );
        $this->fld_clifor->setType( "select" );
        $this->fld_clifor->setSource( $this->ds_utenti ); 
        $this->fld_clifor->setSourceDescriptionField( "desc_view" );
        $this->fld_clifor->disable();


        // Oggetto
        $this->build("p4a_field", "fld_oggetto");
        $this->fld_oggetto->setLabel( "Oggetto" );
        $this->fld_oggetto->setWidth( 550 );
        
        // Corpo del messaggio
        $this->build("p4a_field", "fld_messaggio");
        $this->fld_messaggio->setLabel( "Messaggio" );
        $this->fld_messaggio->setType( "textarea" );
        $this->fld_messaggio->setWidth( 550 );
        $this->fld_messaggio->setHeight( 200 );
        
        // Check "Invia copia a me stesso"        
        $this->build( "p4a_field", "ck_invia_copia_a_me_stesso" );
        $this->ck_invia_copia_a_me_stesso->setType( "checkbox" );
        $this->ck_invia_copia_a_me_stesso->setLabel( "Invia ANCHE a me stesso" );
        $this->ck_invia_copia_a_me_stesso->label->setWidth( 250 );
        $this->ck_invia_copia_a_me_stesso->setNewValue( 1 );
        
        // Check "Invia solo a me stesso (test invio)"        
        $this->build( "p4a_field", "ck_invia_solo_a_me_stesso" );
        $this->ck_invia_solo_a_me_stesso->setType( "checkbox" );
        $this->ck_invia_solo_a_me_stesso->setLabel( "Invia SOLO a me stesso (test invio)" );
        $this->ck_invia_solo_a_me_stesso->label->setWidth( 250 );
        
  
        // ---------------------------------------------------- Frame principale
        $frm=& $this->build( "p4a_frame", "frm" );
        $frm->setWidth( 730 );

        $frm->anchor( $this->msg_info );
        $frm->anchor( $this->fld_mittente );
        $frm->anchor( $this->fld_rispondi );
        $frm->anchor( $this->fld_destinatario );
        $frm->anchor( $this->fld_clifor, "80px" );
        $frm->anchor( $this->fld_oggetto );
        $frm->anchor( $this->fld_messaggio, "130px" );
        $frm->anchor( $this->ck_invia_copia_a_me_stesso, "130px", "left" );
        $frm->anchor( $this->ck_invia_solo_a_me_stesso, "130px", "left" );

        e3g_scrivi_footer( $this, $frm );

        // Display
        $this->display( "main", $frm );
        $this->display( "menu", $p4a->menu );
        $this->display( "top", $this->toolbar );


        $this->fld_mittenteChange();
	}
	

    // -------------------------------------------------------------------------
    function main()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();

        parent::main();

        $this->fld_oggetto->unsetStyleProperty( "border" );
        $this->fld_messaggio->unsetStyleProperty( "border" );
    }
    

    // Invio del messaggio
    // -------------------------------------------------------------------------
    function saveRow()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        $db =& p4a_db::singleton();
        
        // Verifica campi obbligatori (oggetto e corpo del messaggio)
        $error_text = "";
        if ( trim($this->fld_oggetto->getNewValue()) === "" ) {
            $this->fld_oggetto->setStyleProperty( "border", "1px solid red" );
            $error_text = "Compilare l'oggetto del messaggio";
        }
        elseif ( trim($this->fld_messaggio->getNewValue()) === "" ) {
            $this->fld_messaggio->setStyleProperty( "border", "1px solid red" );
            $error_text = "Manca il corpo del messaggio";
        }
    
        if ( $error_text <> "" ) {
            $this->msg_info->setIcon( "warning" );
            $this->msg_info->setValue( $error_text );
            return;
        }


        $intervallo = 0.5;  // Attesa in secondi tra piu' invii
        
        $oggetto = "[" . $p4a->e3g_nome_sw . "] " . $this->fld_oggetto->getNewValue();

        // Invio solo a me stesso (a scopo di test)
        if ( $this->ck_invia_solo_a_me_stesso->getNewValue() != 0 ) {
            e3g_invia_email( $oggetto, $this->fld_messaggio->getNewValue(), $p4a->e3g_utente_email, $p4a->e3g_utente_desc,
                    $this->fld_mittente->getNewValue() );
            $this->msg_info->setIcon( "info" );
            $this->msg_info->setValue( "Invio effettuato." );
            return;
        }

        // Invio normale secondo le scelte dell'utente
        switch ( $this->fld_destinatario->getNewValue() ) {
                case "1":  // Tutti gli amministratori (delle varie gestioni)
                case "2":  // Tutti gli amministratori ed i referenti
                    $aziende = $db->getAll( "SELECT prefix FROM _aziende " ); 
                    $n_admin = 0;
                    $n_invii = 0;
                    foreach ( $aziende as $azienda ) {
                        $sql_text = 
                            "SELECT descrizione, email FROM " . $azienda["prefix"] . "anagrafiche " .
                            " WHERE tipocfa = 'C' AND stato = 1 AND ( tipoutente = 'AS' " .
                            ( $this->fld_destinatario->getNewValue()==1 ? ")" : "OR tipoutente = 'R' )" ) .  // anche i referenti  
                            " ORDER BY descrizione";
                            
                        $records = $db->getAll( $sql_text );
                        if ( !empty($records) ) {
                            foreach ( $records as $record ) {
                                if ( e3g_invia_email( $oggetto, $this->fld_messaggio->getNewValue(), $record["email"], $record["descrizione"],
                                        $this->fld_mittente->getNewValue() ) ) {
                                    $n_invii++;
                                }
                                $n_admin++;
                                sleep( $intervallo );
                            }
                        }
                    }

                    if ( empty($records) ) {
                        $this->msg_info->setIcon( "warning" );
                        $this->msg_info->setValue( "Nessun invio da effettuare." );
                    }
                    elseif ( $n_admin == $n_invii ) {
                        $this->msg_info->setIcon( "info" );
                        $this->msg_info->setValue( "Operazione conclusa con successo: $n_invii invii effettuati." );
                    }
                    else {
                        $this->msg_info->setIcon( "warning" );
                        $this->msg_info->setValue( "Si sono verificati errori: $n_invii invii effettuati per $n_admin amministratori." );
                    }

                    // Invio opzionale all'operatore
                    if ( $this->ck_invia_copia_a_me_stesso->getNewValue() != 0 ) {
                        e3g_invia_email( $oggetto, $this->fld_messaggio->getNewValue(), $p4a->e3g_utente_email, $p4a->e3g_utente_desc,
                                $this->fld_mittente->getNewValue() );
                    }
                    
                    break;

                case "3":  // Tutti gli utenti attivi
                case "4":  // Tutti i referenti (clienti nel caso Equogest)
                    $sql_text = "SELECT descrizione, email FROM " . $p4a->e3g_prefix . "anagrafiche ";
                    
                    if ( $this->fld_destinatario->getNewValue() == 3 )   
                        $sql_text .= " WHERE tipocfa = 'C' AND tipoutente <> 'A' AND stato = 1 ORDER BY descrizione";  // Tutti gli utenti
                    else
                        $sql_text .= " WHERE tipocfa = 'C' AND tipoutente = 'R' AND stato = 1 ORDER BY descrizione";   // Solo i referenti

                    $records = $db->getAll( $sql_text ); 
                    $n_utenti = 0;
                    $n_invii = 0;
                    if ( !empty($records) ) {
                        foreach ( $records as $record ) {
                            if ( e3g_invia_email( $oggetto, $this->fld_messaggio->getNewValue(), $record["email"], $record["descrizione"],
                                    $this->fld_mittente->getNewValue() ) ) {
                                $n_invii++;
                            }
                            $n_utenti++;
                            sleep( $intervallo );
                        }
                    }
                    
                    if ( empty($records) ) {
                        $this->msg_info->setIcon( "warning" );
                        $this->msg_info->setValue( "Nessun invio da effettuare." );
                    }
                    elseif ( $n_utenti == $n_invii ) {
                        $this->msg_info->setIcon( "info" );
                        $this->msg_info->setValue( "Operazione conclusa con successo: $n_invii invii effettuati." );
                    }
                    else {
                        $this->msg_info->setIcon( "warning" );
                        $this->msg_info->setValue( "Si sono verificati errori: $n_invii invii effettuati per $n_utenti utenti attivi." );
                    }
                    
                    // Invio opzionale all'operatore
                    if ( $this->ck_invia_copia_a_me_stesso->getNewValue() != 0 ) {
                        e3g_invia_email( $oggetto, $this->fld_messaggio->getNewValue(), $p4a->e3g_utente_email, $p4a->e3g_utente_desc,
                                $this->fld_mittente->getNewValue() );
                    }
                    
                    break;

                case "5":  // Tutti i fornitori
                    $sql_text = 
                        "SELECT descrizione, email FROM " . $p4a->e3g_prefix . "anagrafiche " .
                        " WHERE tipocfa = 'F' AND stato = 1 AND TRIM( email ) <> '' ORDER BY descrizione";  

                    $records = $db->getAll( $sql_text ); 
                    $n_utenti = 0;
                    $n_invii = 0;
                    if ( !empty($records) ) {
                        foreach ( $records as $record ) {
                            if ( e3g_invia_email( $oggetto, $this->fld_messaggio->getNewValue(), $record["email"], $record["descrizione"],
                                                  $this->fld_mittente->getNewValue() ) ) {
                                $n_invii++;
                            }
                            $n_utenti++;
                            sleep( $intervallo );
                        }
                    }
                    
                    if ( empty($records) ) {
                        $this->msg_info->setIcon( "warning" );
                        $this->msg_info->setValue( "Nessun invio da effettuare." );
                    }
                    elseif ( $n_utenti == $n_invii ) {
                        $this->msg_info->setIcon( "info" );
                        $this->msg_info->setValue( "Operazione conclusa con successo: $n_invii invii effettuati." );
                    }
                    else {
                        $this->msg_info->setIcon( "warning" );
                        $this->msg_info->setValue( "Si sono verificati errori: $n_invii invii effettuati per $n_utenti fornitori attivi." );
                    }
                    
                    // Invio opzionale all'operatore
                    if ( $this->ck_invia_copia_a_me_stesso->getNewValue() != 0 ) {
                        e3g_invia_email( $oggetto, $this->fld_messaggio->getNewValue(), $p4a->e3g_utente_email, $p4a->e3g_utente_desc,
                                $this->fld_mittente->getNewValue() );
                    }
                    
                    break;

                case "6":  // Singolo utente selezionato
                    $this->ds_utenti->rowByPk( $this->fld_clifor->getNewValue() );  // Sincronizza il db source
                
                    if ( e3g_invia_email( $oggetto, $this->fld_messaggio->getNewValue(), 
                                          $this->ds_utenti->fields->email->getNewValue(), $this->ds_utenti->fields->descrizione->getNewValue(),
                                          $this->fld_mittente->getNewValue() ) ) {
                        $this->msg_info->setIcon( "info" );
                        $this->msg_info->setValue( "Invio effettuato." );
                    }
                    else {
                        $this->msg_info->setIcon( "warning" );
                        $this->msg_info->setValue( "Si sono verificati errori durante l'invio." );
                    }

                    // Invio opzionale all'operatore
                    if ( $this->ck_invia_copia_a_me_stesso->getNewValue() != 0 ) {
                        e3g_invia_email( $oggetto, $this->fld_messaggio->getNewValue(), $p4a->e3g_utente_email, $p4a->e3g_utente_desc,
                                $this->fld_mittente->getNewValue() );
                    }
                    
                    break;
                    
                case "7":  // Singolo fornitore selezionato
                    $this->ds_fornitori->rowByPk( $this->fld_clifor->getNewValue() );  // Sincronizza il db source
                
                    if ( e3g_invia_email( $oggetto, $this->fld_messaggio->getNewValue(), 
                                          $this->ds_fornitori->fields->email->getNewValue(), $this->ds_fornitori->fields->descrizione->getNewValue(),
                                          $this->fld_mittente->getNewValue() ) ) {
                        $this->msg_info->setIcon( "info" );
                        $this->msg_info->setValue( "Invio effettuato." );
                    }
                    else {
                        $this->msg_info->setIcon( "warning" );
                        $this->msg_info->setValue( "Si sono verificati errori durante l'invio." );
                    }

                    // Invio opzionale all'operatore
                    if ( $this->ck_invia_copia_a_me_stesso->getNewValue() != 0 ) {
                        e3g_invia_email( $oggetto, $this->fld_messaggio->getNewValue(), $p4a->e3g_utente_email, $p4a->e3g_utente_desc,
                                $this->fld_mittente->getNewValue() );
                    }
                    
                    break;
            }           
    }


    // -------------------------------------------------------------------------
    function fld_destinatarioChange()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        
        switch ( $this->fld_destinatario->getNewValue() ) {
            case "6":  // Singolo utente
                $this->fld_clifor->setSource( $this->ds_utenti ); 
                $this->fld_clifor->enable();
                break;
            case "7":  // Singolo fornitore
                $this->fld_clifor->setSource( $this->ds_fornitori ); 
                $this->fld_clifor->enable();
                break;
            default:
                $this->fld_clifor->disable();
        }
    }
    

    // -------------------------------------------------------------------------
    function fld_mittenteChange()
    // -------------------------------------------------------------------------
    {
        $p4a =& p4a::singleton();
        
        if ( $this->fld_mittente->getNewValue() == 1 ) {
            // Mittente software Gestie3g/GAS
            $this->fld_rispondi->setValue( MAIL_REPLY_NAME . ' <' . MAIL_REPLY . '>' );
        }
        else {
            // Mittente utente corrente
            $this->fld_rispondi->setValue( $p4a->e3g_utente_desc . ' <' . $p4a->e3g_utente_email . '>' );
        }
    }
    
}

?>