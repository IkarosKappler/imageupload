<?php
/**
 * @author  Ikaros Kappler
 * @date    2016-12-13
 * @license CC-BY-SA
 * @version 1.0.0
 **/
header( "Content-Type: text/json; charset=utf-8" );

require_once( "../database/bootstrap/autoload.php" );

$id  = null;
$key = null;
if( !array_key_exists('id',$_GET) ) {
    header( 'HTTP/1.1 400 Bad Request' );
    echo json_encode( array( 'message' => "Param 'id' is missing." ) );
    die();
}

if( !array_key_exists('key',$_GET) ) {
    header( 'HTTP/1.1 400 Bad Request' );
    echo json_encode( array( 'message' => "Param 'key' is missing." ) );
    die();
}

$id  = $_GET['id'];
$key = $_GET['key'];

if( !is_numeric($id) ) {
    header( 'HTTP/1.1 400 Bad Request' );
    echo json_encode( array( 'message' => "Param 'id' is not numeric." ) );
    die();
}

$cat = (array_key_exists('cat',$_GET)?$_GET['cat']:'');



$note = Note::
        whereNull('deleted_at')
      ->where('id',$id)
      //->where('category',$cat)
      ->where('sha256',$key)
      ->get()
      ->first();
//->toArray();

if( !$note ) {
    header( 'HTTP/1.1 404 Not Found' );
    echo json_encode( array( 'message' => "Note not found." ) );
    die();    
}

echo json_encode( $note, JSON_PRETTY_PRINT );


?>