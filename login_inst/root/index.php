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


require_once( './local_config.php' );

$nome_sw = (E3G_TIPO_GESTIONE=='E' ? 'Equogest' : 'GestiGAS' );

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo E3G_NOME_LUNGO; ?></title>
<link href="<?php echo E3G_ROOT_URL; ?>login_inst/style.css" rel="stylesheet" type="text/css" media="all" />
<link rel="shortcut icon" href="<?php echo E3G_ROOT_URL . 'images/favicon_' . (E3G_TIPO_GESTIONE=='E' ? 'eg.ico' : 'gg.ico' ); ?>" />
</head>

<body>
<div id="header">
<h1><?php echo E3G_NOME_LUNGO; ?></h1>
</div>

<div id="content">		
<p><img src="<?php echo E3G_ROOT_URL . 'images/' . (E3G_TIPO_GESTIONE=='E' ? 'equogest_01.jpg' : 'gestigas_01.jpg' ); ?>" alt="<?php echo $nome_sw; ?>" border="0" /></p>
<p>&nbsp;</p>
<h2><a href="<?php echo E3G_ROOT_URL . 'index.php?prefix=' . E3G_PREFIX ?>"><?php echo $nome_sw; ?> :: Entra</a></h2>
<p>&nbsp;</p>
<hr width="80%" />
<p>&nbsp;</p>
<p><?php if ( E3G_EMAIL <> '' ) {
  require_once( E3G_ROOT_DIR . 'libraries/e3g_utils.php' );
  echo e3g_email_encode( E3G_EMAIL );
} ?></p>
<p>&nbsp;</p>
</div>

<div id="footer">
<?php
	$powered_by = 'Powered by <a href="http://www.' . (E3G_TIPO_GESTIONE=='E' ? 'equogest.org" target="_blank">Equogest</a>' : 'gestigas.org" target="_blank">GestiGAS</a>' );
	$image = '<img src="' . E3G_ROOT_URL . 'images/' . (E3G_TIPO_GESTIONE=='E' ? 'simb_eg_H30.png" alt="Powered by Equogest"' : 'simb_gg_H30.png" alt="Powered by GestiGAS"' ) . ' border="0" align="absmiddle" />';
	$hosting = 'Hosting by <a href="http://www.lillinet.org" target="_blank">Lillinet</a>';
	echo ( LILLINET_HOSTING ? "$powered_by :: $image :: $hosting" : "$image :: $powered_by" );
?>
</div>
</body>
</html>
