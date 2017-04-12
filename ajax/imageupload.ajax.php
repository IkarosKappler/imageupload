<?php
/**
 * Well, uhm, this script handles (image) upload requests.
 *
 * If the passed file is an image then the resizer will be triggered (if configured).
 *
 * @author   Ikaros Kappler
 * @date     2016-12-21
 * @modified 2017-01-11 Changed the directory structure to /uploads/<year>/<month>/.
 * @version  1.0.2
 **/


require_once( "../env.inc.php" );
$envErrors = false;
try { mkenv( "../.env" ); }
catch( Exception $e ) { $envErrors = true; }
require_once( "../config.inc.php" );
require_once( "../file.class.php" );


$_ORIGIN_WHITELIST = explode( ',', _env('CORS_REFERERS','') );

// Validate referer.
$validReferer   = false;
$matchedReferer = null;
if( count($_ORIGIN_WHITELIST) > 0 && array_key_exists('HTTP_REFERER',$_SERVER) && $_SERVER['HTTP_REFERER'] ) {
    foreach( $_ORIGIN_WHITELIST as $h) {
        if( strpos($_SERVER['HTTP_REFERER'],$h) !== FALSE ) {
            $validReferer   = true;
            $matchedReferer = $h;
        }
    }
    if( !$validReferer ) {
        // BUG:
        // SOME BROWSERS SEND A PREFLIGHT 'OPTIONS' REQUEST FIRST.
        // VALIDATION FAILS IN THIS CASE.
        //header( 'HTTP/1.1 401 Unauthorized' );
        //die( 'Unauthorized referer: ' . $_SERVER['HTTP_REFERER'] . '.' );
    }
}


// Include the Laravel Eloquent component.
require_once( "../database/bootstrap/autoload.php" );



// Only allow max 20 uploads within 5 minutes.
$list = DB::table('uploads')
    ->where('remote_address',$_SERVER['REMOTE_ADDR'])
    ->where('created_at', '>=', DB::raw('DATE_SUB(NOW(), INTERVAL 5 MINUTE)'))
    ->take(21)
    ->get();

if( count($list) > 20 ) {
    header( 'HTTP/1.1 449 Too Many Requests' );
    echo json_encode( array( 'message' => 'Too many requests. Try again in 5 minutes.' ) );
    die();
}


/**
 * Load laravel's ImageUpload component.
 **/
require_once( '../lib/Imageupload.php' );



/**
 * Here comes some CORS (Cross-Orgin-Resource-Scripting)
 * tweakin to allow access from other domains.
 **/
header( 'Content-Type: text/json; charset=utf-8' );
header( 'Access-Control-Allow-Origin: *', true );
header( 'Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Cache-Control, Accept, Origin, X-Session-ID, Access-Control-Allow-Headers, x-csrf-token', true );
// Some jQuery libraries such as Dropzone also send an OPTIONS request
if( $_SERVER['REQUEST_METHOD'] == 'OPTIONS' ) {
    header( 'Allow: POST,PUT,GET,OPTIONS', true );
    die();
}


/**
 * Here starts the actual magic ^^
 * -------------------------------
 **/
if( $_SERVER['REQUEST_METHOD'] != 'POST' ) {
    header( 'HTTP/1.1 400 Bad Request' );
    echo json_encode( array( 'message' => "Request method must be POST." ) );
    die();
}

if( !$_FILES || count($_FILES) == 0 ) {
    header( 'HTTP/1.1 400 Bad Request' );
    echo json_encode( array( 'message' => "No files passed." ) );
    die();
}

/**
 * Function found at
 *    http://stackoverflow.com/questions/2021624/string-sanitizer-for-filename
 *
 * 1) Strip HTML Tags
 * 2) Remove Break/Tabs/Return Carriage
 * 3) Remove Illegal Chars for folder and filename
 * 4) Put the string in lower case
 * 5) Remove foreign accents such as Éàû by convert it into html entities and then remove the code and keep the letter.
 * 6) Replace Spaces with dashes
 * 7) Encode special chars that could pass the previous steps and enter in conflict filename on server. ex. "中文百强网"
 * 8) Replace "%" with dashes to make sure the link of the file will not be rewritten by the browser when querying th file.
 **/
function normalizeString( $str = '' ) {
    $str = strip_tags($str);
    $str = preg_replace('/[\r\n\t ]+/', ' ', $str);
    $str = preg_replace('/[\"\*\/\:\<\>\?\'\|]+/', ' ', $str);
    $str = strtolower($str);
    $str = html_entity_decode( $str, ENT_QUOTES, "utf-8" );
    $str = htmlentities($str, ENT_QUOTES, "utf-8");
    $str = preg_replace("/(&)([a-z])([a-z]+;)/i", '$2', $str);
    $str = str_replace(' ', '-', $str);
    $str = rawurlencode($str);
    $str = str_replace('%', '-', $str);
    return $str;
}

$uploader  = new Imageupload();
$result    = array();
$cleanName = null;
foreach( $_FILES as $key => $file ) {
    $cleanName = normalizeString( $file['name'] );
    $uploader->results['original_filename'] = $cleanName;
    $result[] = $uploader->upload( new File($file['tmp_name'],$file),
                                   date('Ymd-His') . '-' . $cleanName
    );
}


// Keep Track of all uploads in the database and
// build a small JSON response.
$json = array();
foreach( $result as $index => $file ) {
    $insert = array( 'filename' => $file['filename'], 'original_filename' => $file['original_filename'], 'created_at' => date('Y-m-d H:i:s'), 'remote_address' => $_SERVER['REMOTE_ADDR'], 'referrer' => $_SERVER['HTTP_REFERER'] );
    $insertID = DB::table('uploads')->insertGetId($insert);
    $result[$index]['id'] = $insertID;

    $tmp = array();
    $tmp['id']  = $insertID;
    $tmp['uri'] = $file['uri'];
    $tmp['dimensions'] = array();
    if( array_key_exists('dimensions',$file) ) {
        foreach( $file['dimensions'] as $dname => $dim ) {
            $tmp['dimensions'][$dname] = array( 'uri' => $dim['uri'], 'width' => $dim['width'], 'height' => $dim['height'] );
        }
    }

    // Add icon path
    $iconPath = _env('ICONPATH','/img/icon/').strtolower($file['original_extension']).'.png';
    if( file_exists('..'.$iconPath) ) $tmp['icon_uri'] = $iconPath;
    else                              $tmp['icon_uri'] = _env('ICONPATH','/img/icon/').'_blank.png';
    
    $json[] = $tmp;
}


// Email address configured?
$mailto = _env('MAILTO',false);
if( $mailto )
    mail( $mailto, 'File uploaded ('.$cleanName.')', json_encode( $result, JSON_PRETTY_PRINT ) );


// Print the result.
echo json_encode( $json, JSON_PRETTY_PRINT );


// After all clean up old IP addresses that are older than one month
// (we do not store them forever)
$list = DB::table('uploads')
    ->where('created_at', '<', DB::raw('DATE_SUB(NOW(), INTERVAL 1 MONTH)'))
    ->update( ['remote_address' => ''] );

?>