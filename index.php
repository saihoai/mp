<?php
/** Image PlaceHolder for PHP.
 * Usage : iph.php?<WIDTH>x<HEIGHT>/<COLOR>
 * Ex : iph.php?800x600/FF00FF
 * Author : Léo Peltier
 * LICENSE : WTFPL
 * */?>

<?php if( !isset( $_GET[ 'mholder' ] ) && !isset( $_GET[ 'msearch' ] ) ) : ?>
<style>
	a { display: block; margin: 0 0 10px; }
</style>
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script>
	jQuery(function($){
		$( document ).delegate( '.mholder', 'click', function() {
			var $t = $( this ), u = $t.attr( 'href' );
			$.get( 'index.php', { mholder: u }, function() {
				$t.remove();
			} );
			return false;
		} ).delegate( '.msearch', 'click', function() {
			var $t = $( this ), u = $t.attr( 'href' );
			$.get( 'index.php', { msearch: u }, function( r ) {
				$t.replaceWith( r );
			} );
			return false;
		} );
	});
</script>

<?php endif ?>


 <?php

// step 1: 
// return copy_directory(dirname( __FILE__ ) . '/master', dirname( __FILE__ ) . '/placeholder') ;
// step 2: read folder

 if( isset( $_GET[ 'mholder' ] ) )
 {
 	mholder( $_GET[ 'mholder' ] );die();
 }
readfolder( isset( $_GET[ 'msearch' ] ) ? $_GET[ 'msearch' ] : '' );die();





function copy_directory($src,$dst) { 
    $dir = opendir($src); 
    @mkdir($dst); 
    while(false !== ( $file = readdir($dir)) ) { 
        if (( $file != '.' ) && ( $file != '..' )) { 
            if ( is_dir($src . '/' . $file) ) { 
                copy_directory($src . '/' . $file,$dst . '/' . $file); 
            } 
            else { 
                copy($src . '/' . $file,$dst . '/' . $file); 
            } 
        } 
    } 
    closedir($dir); 
} 

function readfolder( $p = '' )
{
	if( !$p )
	{
		$p = dirname( __FILE__ ) . '/master';
	}
	

	$files = glob($p.'/*.*');

	if( count( $files ) )
	{
		foreach( $files as $file )
		{
			echo '<a href="'.$file.'" class="mholder">'.$file.'</a>';		
		}
	}
	
	$list = glob($p.'/*', GLOB_ONLYDIR|GLOB_NOSORT);
	if( !count( $list ) )
	{
		return;
	}
	foreach( $list as $item )
	{
		echo '<a href="'.$item.'" class="msearch">'.$item.'</a>';
	}
}


/** Fetch the requested image size from $_SERVER['QUERY_STRING'].
 * \returns ({x:int, y:int}) : queried image size.
 * */
function get_size_from_query() {
    if(empty($_SERVER['QUERY_STRING']))
        return null;

    $matches = array();
    preg_match('`\d+(x|\*|⋅|×)\d+`i', $_SERVER['QUERY_STRING'], $matches);
    if(count($matches) != 2)
        return null;

    list($x, $y) = array_map('intval', explode($matches[1], $matches[0]));
    return (object) compact('x', 'y');
}


/** Fetch the requested image color.
 * \return (int) : image color.
 * */
function get_filling_from_query() {
    if(empty($_SERVER['QUERY_STRING']))
        return null;

    $matches = array();
    preg_match('`[a-f0-9]{6}`i', $_SERVER['QUERY_STRING'], $matches);
    if(!count($matches))
        return null;

    return (int) base_convert($matches[0], 16, 10);
}


/** Draw an outer border.
 * \param $img (GD2 image) : image.
 * \param $size ({x:int, y:int}) : image size.
 * \param $color (GD2 color) : color to use for the border.
 * \param $thickness (int>0) : border thickness.
 * */
function imageborder(&$img, $size, $color = null, $thickness = 4) {
    if(!$color)
        $color = 0;

    $offset = floor($thickness/2);
    $box = (object) array(
        'ax' => $offset,
        'ay' => $offset,
        'bx' => $size->x - $offset - 1,
        'by' => $size->y - $offset - 1
    );

    imagesetthickness($img, $thickness);
    imagerectangle($img, $box->ax, $box->ay, $box->bx, $box->by, $color);
}


/// Get rid of output buffering.
function ob_end_clean_all() {
    $level = ob_get_level();
    for($i=0; $i<$level; $i++) {
        ob_end_clean();
    }
}


/// Script entry point.
function mholder( $file ) {

	$dest = str_replace( '/master/', '/placeholder/', $file );
    list($imgWidth, $imgHeight, $type, $attr) = getimagesize( $file );
    
    
    switch ($type) {
	    case 3:
	        $type = 'imagepng';
	        break;
	    case 1:
	        $type = 'imagegif';
	        break;
	    case 2:
	        $type = 'imagejpeg';
	        break;
	}

	if(!is_string( $type ) )
	{
		continue;
	}

    /**
	 * Define the typeface settings.
	 */
    $text = "{$imgWidth}x{$imgHeight}";
	$fontFile = realpath(__DIR__) . DIRECTORY_SEPARATOR . 'RobotoMono-Regular.ttf';
	if ( ! is_readable($fontFile)) {
	    $fontFile = 'arial';
	}

	$fontSize = round(($imgWidth - 50) / 8);
	if ($fontSize <= 9) {
	    $fontSize = 9;
	}
	/**
	 * Generate the image.
	 */
	$image     = imagecreatetruecolor($imgWidth, $imgHeight);
	$colorFill = imagecolorallocate($image, 0, 0, 0);
	$bgFill    = imagecolorallocate($image, 204, 204, 179);
	imagefill($image, 0, 0, $bgFill);
	$textBox = imagettfbbox($fontSize, 0, $fontFile, $text);
	while ($textBox[4] >= $imgWidth) {
	    $fontSize -= round($fontSize / 2);
	    $textBox  = imagettfbbox($fontSize, 0, $fontFile, $text);
	    if ($fontSize <= 9) {
	        $fontSize = 9;
	        break;
	    }
	}
	$textWidth  = abs($textBox[4] - $textBox[0]);
	$textHeight = abs($textBox[5] - $textBox[1]);
	$textX      = ($imgWidth - $textWidth) / 2;
	$textY      = ($imgHeight + $textHeight) / 2;
	imagettftext($image, $fontSize, 0, $textX, $textY, $colorFill, $fontFile, $text);
	
	ob_end_clean_all();
    call_user_func( $type, $image, $dest );
    imagedestroy($image);
}
