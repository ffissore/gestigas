<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4: */

/**
 * Image_Barcode_ean13 class
 *
 * Renders EAN 13 barcodes
 *
 * PHP versions 4
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Image
 * @package    Image_Barcode
 * @author     Didier Fournout <didier.fournout@nyc.fr>
 * @copyright  2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id: ean13.php,v 1.9 2008/10/02 19:03:13 equogest Exp $
 * @link       http://pear.php.net/package/Image_Barcode
 */

require_once "PEAR.php";
require_once "Image/Barcode.php";

/**
 * Image_Barcode_ean13 class
 *
 * Package which provides a method to create EAN 13 barcode using GD library.
 *
 * @category   Image
 * @package    Image_Barcode
 * @author     Didier Fournout <didier.fournout@nyc.fr>
 * @copyright  2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/Image_Barcode
 * @since      Image_Barcode 0.4
 */
class Image_Barcode_ean13 extends Image_Barcode
{
    /**
     * Barcode type
     * @var string
     */
    var $_type = 'ean13';

    /**
     * Barcode height
     *
     * @var integer
     */
    var $_barcodeheight = 40;

    /**
     * Font use to display text
     *
     * @var integer
     */
    var $_font = 2;  // gd internal small font

    /**
     * Bar width
     *
     * @var integer
     */
    var $_barwidth = 1;


    /**
     * Number set
     * @var array
     */
    var $_number_set = array(
           '0' => array(
                    'A' => array(0,0,0,1,1,0,1),
                    'B' => array(0,1,0,0,1,1,1),
                    'C' => array(1,1,1,0,0,1,0)
                        ),
           '1' => array(
                    'A' => array(0,0,1,1,0,0,1),
                    'B' => array(0,1,1,0,0,1,1),
                    'C' => array(1,1,0,0,1,1,0)
                        ),
           '2' => array(
                    'A' => array(0,0,1,0,0,1,1),
                    'B' => array(0,0,1,1,0,1,1),
                    'C' => array(1,1,0,1,1,0,0)
                        ),
           '3' => array(
                    'A' => array(0,1,1,1,1,0,1),
                    'B' => array(0,1,0,0,0,0,1),
                    'C' => array(1,0,0,0,0,1,0)
                        ),
           '4' => array(
                    'A' => array(0,1,0,0,0,1,1),
                    'B' => array(0,0,1,1,1,0,1),
                    'C' => array(1,0,1,1,1,0,0)
                        ),
           '5' => array(
                    'A' => array(0,1,1,0,0,0,1),
                    'B' => array(0,1,1,1,0,0,1),
                    'C' => array(1,0,0,1,1,1,0)
                        ),
           '6' => array(
                    'A' => array(0,1,0,1,1,1,1),
                    'B' => array(0,0,0,0,1,0,1),
                    'C' => array(1,0,1,0,0,0,0)
                        ),
           '7' => array(
                    'A' => array(0,1,1,1,0,1,1),
                    'B' => array(0,0,1,0,0,0,1),
                    'C' => array(1,0,0,0,1,0,0)
                        ),
           '8' => array(
                    'A' => array(0,1,1,0,1,1,1),
                    'B' => array(0,0,0,1,0,0,1),
                    'C' => array(1,0,0,1,0,0,0)
                        ),
           '9' => array(
                    'A' => array(0,0,0,1,0,1,1),
                    'B' => array(0,0,1,0,1,1,1),
                    'C' => array(1,1,1,0,1,0,0)
                        )
        );

    var $_number_set_left_coding = array(
           '0' => array('A','A','A','A','A','A'),
           '1' => array('A','A','B','A','B','B'),
           '2' => array('A','A','B','B','A','B'),
           '3' => array('A','A','B','B','B','A'),
           '4' => array('A','B','A','A','B','B'),
           '5' => array('A','B','B','A','A','B'),
           '6' => array('A','B','B','B','A','A'),
           '7' => array('A','B','A','B','A','B'),
           '8' => array('A','B','A','B','B','A'),
           '9' => array('A','B','B','A','B','A')
        );

    /**
     * Draws a EAN 13 image barcode
     *
     * @param  string $text     A text that should be in the image barcode
     * @param  string $imgtype  The image type that will be generated
     *
     * @return image            The corresponding Interleaved 2 of 5 image barcode
     *
     * @access public
     *
     * @author     Didier Fournout <didier.fournout@nyc.fr>
     *
     */
    //function draw($text, $imgtype = 'png', $numcols , $numrows , $ystart , $xstart )
    function draw($text, $imgtype, $numcols , $numrows , $ystart , $xstart, $arr, $Array, $nomefile )
    {
        $p4a =& p4a::singleton();
		
        if ($nomefile == "")
		{	
			$nomefile = "barcode.png";
			
        }
        //TODO: Check if $text is number and len=13
		
        // Calculate the barcode width
        $barcodewidth = ((strlen($text)) * (7 * $this->_barwidth)
            + 3 // left
            + 5 // center
            + 3 // right
            + imagefontwidth($this->_font)+1) 
          	; //* $numcols

        //$barcodelongheight = (int) ((imagefontheight($this->_font)/2)+$this->_barcodeheight ) * $numrows;
		  $barcodelongheight = (int) ((imagefontheight($this->_font)/2)+$this->_barcodeheight );
		
        // Create the image
        $img = ImageCreate(($barcodewidth * $numcols + (($barcodewidth * 1.7) * ($numcols - 1) )) * 1.2, ($barcodelongheight * $numrows * 2) + (imagefontheight($this->_font) * 12 )+1);

        // Alocate the black and white colors
        $black = ImageColorAllocate($img, 0, 0, 0);
        $white = ImageColorAllocate($img, 255, 255, 255);

        // Fill image with white color
        imagefill($img, 0, 0, $white);

        // get the first digit which is the key for creating the first 6 bars
     /*    
        1  2   3   4
        5  6   7   8
        9 10  11  12
       13 14  15  16
       17 18  19  20
       21 22  23  24
       25 26  27  28
       29 30  31  32
       33 34  35  36
       37 38  39  40
    */ 

		//for ($idbar = 1; $idbar < 7; $idbar++) {
		for ($idbar = 1; $idbar <= ($numcols * $numrows); $idbar++) {
			$text = $arr[$idbar - 1]; 
		
			if ($text != "")
			{	
				$key = substr($text,0,1);
              switch ($idbar) {
                  case "1":
                  case "5": 
                  case "9": 
                  case "13": 
                  case "17": 
                  case "21": 
                  case "25": 
                  case "29": 
                  case "33": 
                  case "37":
      	    		     $moltip_y = (($idbar - 1) / 4);		
                     $xpos = $xstart;
      	    		     $ypos = $ystart + $barcodelongheight * $moltip_y + imagefontheight($this->_font) * 5 * $moltip_y;              
                    break;
                  case "2": 
                  case "6": 
                  case "10": 
                  case "14": 
                  case "18": 
                  case "22": 
                  case "26": 
                  case "30": 
                  case "34": 
                  case "38":
              			 $moltip_y = (($idbar - 2) / 4);
              			 $moltip_x = 1 ;				
                     $xpos = $xstart + $barcodewidth  * $moltip_x + $barcodewidth * 1;
      	    		     $ypos = $ystart + $barcodelongheight * $moltip_y + imagefontheight($this->_font) * 5 * $moltip_y;              
                    break;
                  case "3": 
                  case "7": 
                  case "11": 
                  case "15": 
                  case "19": 
                  case "23": 
                  case "27": 
                  case "31": 
                  case "35": 
                  case "39":
    				$moltip_y = (($idbar - 3) / 4);
    				$moltip_x = 2 ;		
                    $xpos = $xstart + $barcodewidth  * $moltip_x + $barcodewidth * 2;
      	    		$ypos = $ystart + $barcodelongheight * $moltip_y + imagefontheight($this->_font) * 5 * $moltip_y;              
                    break;
                  case "4": 
                  case "8": 
                  case "12": 
                  case "16": 
                  case "20": 
                  case "24": 
                  case "28": 
                  case "32": 
                  case "36": 
                  case "40":
    				$moltip_y = (($idbar - 4) / 4);
    				$moltip_x = 3 ;		
                    $xpos = $xstart + $barcodewidth  * $moltip_x + $barcodewidth * 3;
      	    		    $ypos = $ystart + $barcodelongheight * $moltip_y + imagefontheight($this->_font) * 5 * $moltip_y;              
                    break;
              }

/*				    
	            // Initiate x position
	            switch($idbar) {
	               case 1:
	    				$xpos = $xstart;
	    				$ypos = $ystart; 
	                	break;    
	               case 2:
	    				$xpos = $xstart + $barcodewidth  + $barcodewidth * 1.5 ;
	    				$ypos = $ystart; 
		                break;
	               case 3:
	    				$xpos = $xstart;
	    				$ypos = $ystart + $barcodelongheight  + imagefontheight($this->_font) * 5.7;              
		                break;
	               case 4:
	    				$xpos = $xstart + $barcodewidth  + $barcodewidth * 1.5  ;
	    				$ypos = $ystart + $barcodelongheight +  imagefontheight($this->_font) * 5.7;              
		                break;
	               case 5:
	    				$xpos = $xstart;
	    				$ypos = $ystart + $barcodelongheight * 2  + imagefontheight($this->_font) * 12;             
		                break;
	               case 6:
	    				$xpos = $xstart + $barcodewidth  + $barcodewidth * 1.5 ;
	    				$ypos = $ystart + $barcodelongheight * 2 + imagefontheight($this->_font) * 12;               
		                break;
	            }
	*/
				
	            // descrizione
	            imagestring ($img, $this->_font, $xpos+1, $ypos + $this->_barcodeheight + 12, $Array[$idbar - 1]["descrizione"], $black);
	    		    // prezzo - Iva
	            imagestring ($img, $this->_font, $xpos+1, $ypos + $this->_barcodeheight + 24, $Array[$idbar - 1]["prezzo"]." euro  Iva: ".$Array[$idbar - 1]["iva"], $black);
	    		     // Paese
	            imagestring ($img, $this->_font, $xpos+1, $ypos + $this->_barcodeheight + 36, $Array[$idbar - 1]["paese"], $black);
	    		
	            // print first digit
	            imagestring($img, $this->_font, $xpos, $ypos + $this->_barcodeheight, $key, $black);
	            $xpos += imagefontwidth($this->_font) + 1;
	    
	            // Draws the left guard pattern (bar-space-bar)
	            // bar
	            imagefilledrectangle($img, $xpos, $ypos, $xpos + $this->_barwidth - 1, $ypos + $barcodelongheight, $black);
	            $xpos += $this->_barwidth;
	            // space
	            $xpos += $this->_barwidth;
	            // bar
	            imagefilledrectangle($img, $xpos, $ypos, $xpos + $this->_barwidth - 1, $ypos + $barcodelongheight, $black);
	            $xpos += $this->_barwidth;
	    
	            // Draw left $text contents
	            $set_array=$this->_number_set_left_coding[$key];
	            for ($idx = 1; $idx < 7; $idx ++) {
	                $value=substr($text,$idx,1);
	                imagestring ($img, $this->_font, $xpos+1, $ypos + $this->_barcodeheight, $value, $black);
	                foreach ($this->_number_set[$value][$set_array[$idx-1]] as $bar) {
	                    if ($bar) {
	                        imagefilledrectangle($img, $xpos, $ypos, $xpos + $this->_barwidth - 1, $ypos + $this->_barcodeheight, $black);
	                    }
	                    $xpos += $this->_barwidth;
	                }
	            }
	    
	            // Draws the center pattern (space-bar-space-bar-space)
	            // space
	            $xpos += $this->_barwidth;
	            // bar
	            imagefilledrectangle($img, $xpos, $ypos, $xpos + $this->_barwidth - 1, $ypos + $barcodelongheight, $black);
	            $xpos += $this->_barwidth;
	            // space
	            $xpos += $this->_barwidth;
	            // bar
	            imagefilledrectangle($img, $xpos, $ypos, $xpos + $this->_barwidth - 1, $ypos + $barcodelongheight, $black);
	            $xpos += $this->_barwidth;
	            // space
	            $xpos += $this->_barwidth;
	    
	    
	            // Draw right $text contents
	            for ($idx = 7; $idx < 13; $idx ++) {
	                $value=substr($text,$idx,1);
	                imagestring ($img, $this->_font, $xpos+1, $ypos + $this->_barcodeheight, $value, $black);
	                foreach ($this->_number_set[$value]['C'] as $bar) {
	                    if ($bar) {
	                        imagefilledrectangle($img, $xpos, $ypos, $xpos + $this->_barwidth - 1, $ypos + $this->_barcodeheight, $black);
	                    }
	                    $xpos += $this->_barwidth;
	                }
	            }
	       
	            // Draws the right guard pattern (bar-space-bar)
	            // bar
	            imagefilledrectangle($img, $xpos, $ypos, $xpos + $this->_barwidth - 1, $ypos + $barcodelongheight, $black);
	            $xpos += $this->_barwidth;
	            // space
	            $xpos += $this->_barwidth;
	            // bar
	            imagefilledrectangle($img, $xpos, $ypos, $xpos + $this->_barwidth - 1, $ypos + $barcodelongheight, $black);
	            $xpos += $this->_barwidth;
			}
		        
		}
		
        // Send image to browser
        switch($imgtype) {

            case 'gif':
                header("Content-type: image/gif");
				header("Content-Disposition: attachment; filename=etichette.gif");
                imagegif($img);
                imagedestroy($img);
            break;

            case 'jpg':
                header("Content-type: image/jpg");
				header("Content-Disposition: attachment; filename=etichette.jpg");
                imagejpeg($img);
                imagedestroy($img);
            break;

            default:
				if ( $nomefile == "barcode.png")
				{
					// ho il nome file di default quindi non salvo le etichette ma le propongo come attachment
					header("Content-type: image/png");
					header("Content-Disposition: attachment; filename=".$nomefile);
					imagepng($img);
					imagedestroy($img);
				}
				else 				
				{
					// ho il nome file e salvo le etichette 
					imagepng( $img, $p4a->e3g_azienda_etichette_path . $nomefile );
					imagedestroy( $img );
				}
			break;

        }

        return;

    } // function create

} // class
?>