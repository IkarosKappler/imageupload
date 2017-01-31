<?php
/**
 * Creata a mockup for Laravel's env function and an .env file reader.
 *
 * @author  Ikaros Kappler
 * @date    2017-01-31
 * @version 1.0.0
 **/


/**
 * This function reads the given (or default) environment file into a global constant.
 **/
function mkenv( $filename = '.env' ) {
    if( file_exists($filename) && is_readable($filename) ) {
        $envvars = parse_ini_file( $filename );
        define( '_CUSTOM_ENV_VARS', $envvars );
    }
}

/**
 * This is the actual mockup function.
 **/
function _env( $name, $fallback = null ) {
    if( !defined('_CUSTOM_ENV_VARS') )
        return $fallback;
    if( !array_key_exists($name,_CUSTOM_ENV_VARS) )
        return $fallback;
    if( !_CUSTOM_ENV_VARS[$name] )
        return $fallback;
    return _CUSTOM_ENV_VARS[$name];
}


?>