<?php
/**
 * This is a lightweight mockup of laravel's File class.
 *
 * @author  Ikaros Kappler
 * @date    2017-01-11
 * @version 1.0.0
 **/


/**
 * Define a custom File class that's compatible (in this
 * usage) with Laravel's file facade/interface.
 **/
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
        return rename( $this->path, $newDir.DIRECTORY_SEPARATOR.$newName );
    }

    public function getMimeType() {
        return $this->meta['type']; // NOT SAFE!
    }

    public function getClientOriginalName() {
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




?>