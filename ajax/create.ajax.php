<?php
/**
 * @author  Ikaros Kappler
 * @date    2016-11-30
 * @version 1.0.0
 **/

require_once( "../database/bootstrap/autoload.php" );

header( 'Content-Type: text/json; charset=utf-8' );

//use Illuminate\Database\Eloquent\Model;
//$notes = Note::all();
//print_r( $notes );

if( !array_key_exists('note',$_GET) ) {
    header( 'HTTP/1.1 400 Bad Request' );
    echo json_encode( array( 'message' => "Param 'note' is missing." ) );
    die();
}

$note = $_GET['note'];
$cat  = (array_key_exists('cat',$_GET)?$_GET['cat']:'');

if( strlen($note) > 140 ) {
    header( 'HTTP/1.1 400 Bad Request' );
    echo json_encode( array( 'message' => "Param 'note' is too long." ) );
    die();
}
if( !strlen($note) ) {
    header( 'HTTP/1.1 400 Bad Request' );
    echo json_encode( array( 'message' => "Param 'note' is empty." ) );
    die();
}


$sha256 = hash( 'sha256', time().'_'.$_SERVER['REMOTE_ADDR']."$".$note );


// Check if there are already more than 10 notes from withing the last 5 minutes
$list = Note::
      where('remote_address',$_SERVER['REMOTE_ADDR'])
    ->where('created_at', '>=', DB::raw('DATE_SUB(NOW(), INTERVAL 5 MINUTE)'))
    ->take(11)
    ->get()
    ->toArray();

if( count($list) > 10 ) {
    header( 'HTTP/1.1 449 Too Many Requests' );
    echo json_encode( array( 'message' => "Too many requests. Try again in 5 minutes." ) );
    die();
}


$noteObject = new Note( array( 'data'           => $note,
                               'category'       => $cat,
                               'sha256'         => $sha256,
                               'remote_address' => $_SERVER['REMOTE_ADDR']
) );
$noteObject->save();


echo json_encode( array( "message" => "Note stored (" . strlen($note) . " chars).",
'note' => $noteObject
) );


// Send notification email
mail( 'info@int2byte.de',
      'Note stored (id='.$noteObject->id.'), cat='.$cat.'.',
      'Note was stored (remote_address=' . $noteObject->remote_address . ")\n".
        'data: ' . $note
);

?>