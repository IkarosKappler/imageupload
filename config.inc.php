<?php
/**
 * @author  Ikaros Kappler
 * @version 1.0.0
 * @date    2017-01-31
 **/

// A whitelist for the CORS configuration
// (Not in use at the moment)
$_ORIGIN_WHITELIST = array( 'func.name', 'int2byte.de' );



/**
 * Add a custom fake config that's compatible with
 * Laravel's default Config face/interface.       
 *
 * (The original one would read the values from
 * a config text file).
 **/
class Config {
    public static function get( $name, $default = FALSE ) {
        if( $name == 'imageupload.library' )
            return 'imagick_raw';
        else if( $name == 'imageupload.dimensions' )
            // This is the thumbnail configuration.
            // Remove or add more array elements for
            // your needs.
            return array( '64x64'   => array(64,64,false),
            '128x128' => array(128,128,false)
            );
        else if( $name == 'imageupload.path' )
            return public_path() . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('m');
        else if( $name == 'imageupload.uribase' )
            return public_uri() . '/uploads/' . date('Y') . '/' . date('m');
        else if( $name == 'imageupload.fileowner' )
            return 'www-data';
        else if( $name == 'imageupload.filegroup' )
            return 'www-data';
        else if( $name == 'imageupload.newfilename' )
            return 'custom';
        else
            return $default;
    }
}


// The uploader class requires these two functions.
function public_path() {
    return '../public';
}
function public_uri() {
    return '/public';
}




?>