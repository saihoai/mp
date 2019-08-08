
<?php if( !isset( $_GET[ 'mholder' ] ) && !isset( $_GET[ 'msearch' ] ) ) : ?>
<style>
	body { text-align: center; }
	a { display: block; margin: 0 0 10px; }
	.cholder, .cmholder { padding: 10px 40px; text-decoration: none; display: inline-block; background: red; color: #fff; }
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
		} ).delegate( '.cholder', 'click', function() {
			$( 'body' ).prepend( '<a href="/" class="cmholder">Make placeholder</a>' );
			return false;
		} ).delegate( '.cmholder', 'click', function() {
			$( '.mholder' ).trigger( 'click' );
			return false;
		} );
	});
</script>

<?php endif ?>


 <?php

if( !isset( $_GET[ 'mholder' ] ) && !isset( $_GET[ 'msearch' ] ) ) 
{
	copy_directory(dirname( __FILE__ ) . '/master', dirname( __FILE__ ) . '/placeholder') ;
	echo '<a href="" class="msearch cholder">Start</a>';
	die();
}

if( isset( $_GET[ 'mholder' ] ) )
{
	mholder( $_GET[ 'mholder' ] );die();
}

if( isset( $_GET[ 'msearch' ] ) )
{
	readfolder( isset( $_GET[ 'msearch' ] ) ? $_GET[ 'msearch' ] : '' );die();
}


function copy_directory($src,$dst)
{ 
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


/// Get rid of output buffering.
function ob_end_clean_all()
{
    $level = ob_get_level();
    for($i=0; $i<$level; $i++) {
        ob_end_clean();
    }
}


/// Script entry point.
function mholder( $file ) 
{

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
	$fontFile = realpath(__DIR__)  . '/RobotoMono-Regular.ttf';
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
