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


class informazioni extends P4A_Mask
{
	function informazioni()
	{
		$this->p4a_mask();
		
		$this->addCss( E3G_TEMPLATE_DIR . 'css/style.css' );
		
 		$p4a =& p4a::singleton();
		
		$this->setTitle( "Informazioni su $p4a->e3g_nome_sw" );
		
		// Toolbar
		$this->build( "p4a_quit_toolbar", "toolbar" );
//		$this->toolbar->setMask( $this );
		
		// Immagine del logo
		if ( E3G_TIPO_GESTIONE == 'G' ) 
			$src_logo = 'images/gestigas_02.jpg';
		else 
			$src_logo = 'images/equogest_02.jpg';

		// HTML box
		$box =& $this->build("p4a_box", "box");
		$box->setValue( '
<p align="center"><img src="' . $src_logo . '" alt="Progetto e3g - Equogest/GestiGAS" /></p>
<p align="center"><a href="http://www.progettoe3g.org" target="_blank">Progetto e3g - Equogest/GestiGAS</a><br />
  <em>Software gestionali per l\'economia solidale</em>
<p align="center">   Copyright &copy; 2003-2012<br />
   <a href="http://www.andreapiazza.it" target="_blank">Andrea Piazza</a> e <a href="http://www.marcomunari.it" target="_blank">Marco
   Munari</a>
<p align="center"> Questo programma &egrave; software libero;
  &egrave; lecito redistribuirlo o<br />
   modificarlo secondo i termini della Licenza Pubblica Generica GNU<br />
   come pubblicata dalla Free Software Foundation; o la versione 2<br />
   della licenza o (a propria scelta) una versione successiva.<br />
<br />
   Questo programma &egrave; distribuito nella speranza che sia utile, ma<br />
  SENZA ALCUNA GARANZIA; senza neppure la garanzia implicita di<br />
   NEGOZIABILITA\' o di APPLICABILITA\' PER UN PARTICOLARE SCOPO. Si<br />
   veda la Licenza Pubblica Generica GNU per avere maggiori dettagli.<br />
<br />
   Questo programma deve essere distribuito assieme ad una copia<br />
   della Licenza Pubblica Generica GNU.</p>
<p align="center"><strong>Equogest e GestiGAS sono software libero distribuito gratuitamente, ma se desideri esprimere il tuo apprezzamento per il tempo e le risorse che il gruppo di lavoro sta impiegando per lo sviluppo ed il mantenimento del software, ed inoltre vuoi aiutare a sostenere i costi di esercizio del server, sono apprezzate ed accettate donazioni in denaro.</strong></p>
<p align="center">Mettiti in contatto con gli autori, oppure utilizza il servizio offerto da PayPal:</p>
<form></form>
<form align="center" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
<input type="hidden" name="cmd" value="_xclick">
<input type="hidden" name="business" value="donazioni@progettoe3g.org">
<input type="hidden" name="item_name" value="Contributo a sostegno del Progetto e3g">
<input type="hidden" name="no_shipping" value="2">
<input type="hidden" name="no_note" value="1">
<input type="hidden" name="currency_code" value="EUR">
<input type="hidden" name="tax" value="0">
<input type="hidden" name="lc" value="IT">
<input type="hidden" name="bn" value="PP-DonationsBF">
<input type="image" src="https://www.paypal.com/it_IT/i/btn/x-click-but04.gif" border="0" name="submit" alt="Effettua la tua donazione con PayPal: un sistema rapido, gratuito e sicuro.">
<img alt="" border="0" src="https://www.paypal.com/it_IT/i/scr/pixel.gif" width="1" height="1">
</form>');
				
		// Message		
		$this->build("p4a_message", "message_info");
		$this->message_info->setWidth( 700 );
		$this->message_info->setIcon( "info" );
		

		// Frame principale
		$frame =& $this->build("p4a_frame", "frame");
		$frame->setWidth(730);
		$frame->anchorCenter($this->box);
		$frame->anchorCenter($this->message_info);
		
		e3g_scrivi_footer( $this, $frame );

  		// Display
		$this->display( "main", $frame );
		$this->display( "menu", $p4a->menu );
		$this->display( "top", $this->toolbar );
	}

		
	function main()
	{
		$p4a =& p4a::singleton();

		e3g_update_var_azienda();
		
		$info_testo = "$p4a->e3g_nome_sw v. " . E3G_VERSIONE . 
			" - database v. $p4a->e3g_db_cond_versione/$p4a->e3g_azienda_db_multi_versione " . 
			
			( ( $p4a->e3g_db_cond_versione == E3G_DB_COND_VERSIONE_ATTESA and  
			    $p4a->e3g_azienda_db_multi_versione == E3G_DB_MULTI_VERSIONE_ATTESA ) ? 
				"" : 
				"(anziche' " . E3G_DB_COND_VERSIONE_ATTESA . "/" . E3G_DB_MULTI_VERSIONE_ATTESA . ")" ) . 		
			
			( STATO_DEBUG ? " - Modo debug attivo" : "" );
		$this->message_info->setValue( $info_testo );

		parent::main();
	}

}

?>
