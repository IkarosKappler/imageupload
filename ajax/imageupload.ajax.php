<?php
/**
 * Well, uhm, this script handles (image) upload requests.
 *
 * If the passed file is an image then the resizer will be triggered (if configured).
 *
 * @author   Ikaros Kappler
 * @date     2016-12-21
 * @modified 2017-01-11 Changed the directory structure to /uploads/<year>/<month>/.
 * @version  1.0.1
 **/


// Include the Laravel Eloquent component.
require_once( "../database/bootstrap/autoload.php" );

// Only allow max 20 uploads within 5 minutes.
$list = DB::table('uploads')
    ->where('remote_address',$_SERVER['REMOTE_ADDR'])
    ->where('created_at', '>=', DB::raw('DATE_SUB(NOW(), INTERVAL 5 MINUTE)'))
    ->take(11)
    ->get();
//->toArray();

if( count($list) > 10 ) {
    header( 'HTTP/1.1 449 Too Many Requests' );
    echo json_encode( array( 'message' => 'Too many requests. Try again in 5 minutes.' ) );
    die();
}

class Config {
    public static function get( $name, $default = FALSE ) {
        if( $name == 'imageupload.library' )
            return 'imagick_raw';
        else if( $name == 'imageupload.dimensions' )
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
function public_path() {
    return '../public';
}
function public_uri() {
    return '/public';
}

require_once( '../lib/Imageupload.php' );


class File {

    protected $path;
    protected $meta;
    
    public static function isWritable($path) {
        return is_writable($path);
    }

    public static function isDirectory($path) {
        return is_dir($path);
    }

    public static function makeDirectory($path, $mode = FALSE, $_foo = FALSE) {
        mkdir($path,$mode,true); // true -> recursive
        chmod($path,$mode);
    }

    public function __construct($path, $uploadMeta) {
        $this->path = $path;
        $this->meta = $uploadMeta;
    }

    public function getPath() { return $this->path; }
    
    public function move( $newDir, $newName ) {
        //echo "file exists? " . file_exists($this->path) ."\n";
        //echo "Moving file to $newDir/$newName\n";
        return rename( $this->path, $newDir.DIRECTORY_SEPARATOR.$newName );
    }
    
    public function getMimeType() {
        return $this->meta['type']; // NOT SAFE!!!
    }

    public function getClientOriginalName() {
        //echo "original=" . $this->meta['name'];
        //print_r( $this->meta );
        return $this->meta['name'];
    }

    public function getClientOriginalExtension() {
        return pathinfo($this->meta['name'], PATHINFO_EXTENSION);
    }

    public function getRealPath() {
        return realpath($this->path);
    }

    public function getSize() {
        return filesize($this->path);
    }
}

header( 'Content-Type: text/json; charset=utf-8' );
header( 'Access-Control-Allow-Origin: *', true );
header( 'Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Cache-Control, Accept, Origin, X-Session-ID, Access-Control-Allow-Headers, x-csrf-token', true );
//header('Access-Control-Allow-Methods: POST,PUT,GET,OPTIONS' ); // GET,POST,PUT,HEAD,DELETE,TRACE,COPY,LOCK,MKCOL,MOVE,PROPFIND,PROPPATCH,UNLOCK,REPORT,MKACTIVITY,CHECKOUT,MERGE,M-SEARCH,NOTIFY,SUBSCRIBE,UNSUBSCRIBE,PATCH' );
//header('Access-Control-Allow-Credentials: false', true );
//header('Access-Control-Max-Age: 1000', true ); // 1728000' ); // 1000' );

// Some jQuery libraries such as Dropzone also send an OPTIONS request
if( $_SERVER['REQUEST_METHOD'] == 'OPTIONS' ) {
    //header( 'HTTP/1.1 200 OK' );
    header( 'Allow: POST,PUT,GET,OPTIONS', true );
    //echo "POST";
    die();
}

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
 * Found at
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

$uploader = new Imageupload();
$result = array();
$cleanName = null;
foreach( $_FILES as $key => $file ) {
    $cleanName = normalizeString( $file['name'] );
    $uploader->results['original_filename'] = $cleanName; // $file['name'];
    $result[] = $uploader->upload( new File($file['tmp_name'],$file),
                                   date('Ymd-His') . '-' . $cleanName // $file['name']
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
    $json[] = $tmp;
}

mail( 'info@int2byte.de', 'File uploaded ('.$cleanName.')', json_encode( $result, JSON_PRETTY_PRINT ) );

echo json_encode( $json, JSON_PRETTY_PRINT );

?>