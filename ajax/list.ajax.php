<?php

header( "Content-Type: text/json; charset=utf-8" );

require_once( "../database/bootstrap/autoload.php" );

$skip  = 0;
$limit = 25;
if( array_key_exists('skip',$_GET) ) {
    $skip = $_GET['skip'];
    if( !is_numeric($skip) ) {
         header( 'HTTP/1.1 400 Bad Request' );
         echo json_encode( array( 'message' => "Param 'skip' is not numeric." ) );
         die();
    }
}
if( array_key_exists('limit',$_GET) ) {
    $limit = $_GET['limit'];
    if( !is_numeric($limit) ) {
         header( 'HTTP/1.1 400 Bad Request' );
         echo json_encode( array( 'message' => "Param 'limit' is not numeric." ) );
         die();
    }
}

$cat = (array_key_exists('cat',$_GET) ? $_GET['cat'] : '');

    
//$results = DB::select('select * from notes;', array(1));

$list = Note::
    whereNull('deleted_at')
    ->where('category',$cat)
    ->skip($skip)
    ->take($limit)
    ->orderBy('created_at','desc')
    ->get()
    ->toArray();

//print_r( $list );
$result = array( 'meta' => array( 'skip' => $skip, 'limit' => $limit ),
                 'list' => $list );
echo json_encode( $result, JSON_PRETTY_PRINT );


?>