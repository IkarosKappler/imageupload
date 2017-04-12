# imageupload

This is a simple image/file upload script for AJAX uploads (e.g. with DropZone.js) written in PHP.
It uses the laravel/eloquent framework for database access.



Files
-----
.env
.env_example

```text
; A coma separated list of allowed referers for cross-origin requests.
; Examples:
;  CORS_REFERERS=yourdomain.net
;  CORS_REFERERS=yourcomain.net,otherdomain.org

; CORS_REFERERS=

; Put an email address here for info mails each time a file was uploaded.
; Example:
;  MAILTO=info@yourserver.net
; MAILTO=
```


 ./database/.env
 
```text
 This file should contain your DB credentials
  APP_KEY=SomeRandomString
  DB_USERNAME=
  DB_PASSWORD=
  DB_DATABASE=
  DB_HOST=
```

Directories
-----------

 public/
The upload directory.

Disallow script execution in your upload directory!
---------------------------------------------------
 public/.htaccess

```text
Options -Indexes
php_flag engine off
```

Or a better way: disable via your webserver configuration.



Basic Setup
-----------
```html
      Select image:
      <form id="upload-widget" method="post" action="../ajax/imageupload.ajax.php" class="dropzone">
      </form>
```


Javascript Uploader
-------------------
And here goes a basic uploader javascript using Dropzone.js:
```javascript
      <script>
      Dropzone.autoDiscover = false;

var uploader = new Dropzone('#upload-widget', {
  paramName: 'file',
        maxFilesize: 2, // MB
        maxFiles: 5,
        thumbnailWidth: 64,
        thumbnailHeight: 64,
        dictDefaultMessage: 'Drag an image here to upload, or click to select one',
        headers: {
      'x-csrf-token': 'abcdef0123456789'
            },
        acceptedFiles: '*/*', // 'image/*',
  init: function() {
        this.on('success', function( file, response ) {
          
            console.debug('success. file=' + file + ', response=' + response );
                      });
    }
}  );
```

Create a MySQL table
--------------------
```sql
CREATE TABLE IF NOT EXISTS `uploads` (
`id` int(11) unsigned NOT NULL,
  `filename` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `original_filename` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `remote_address` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `referrer` varchar(256) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'The referring website.'
) ENGINE=InnoDB AUTO_INCREMENT=203 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Tracks all uploaded files.';
```

Changelog
---------
[2017-04-12]
 * Added an icon set for non-image files ('Free-file-icons' by teambox. Thank you!).
 * Added the ICONPATH to the .env file.
 * Added the 'icon_path' attribute to the JSON response.
 * Added the IMAGE_FILES_ONLY to the .env file (if set to false the uploader also
   accepts non-image files; it does not create any thumbnails then).

[2017-03-07]
 * Added the option to 'crop' thumbails to the required size.
 * Changed the thumbail size to 'bestfit' for non-crop resizing (keeps aspect ratio).

[2017-02-19]
 * Purging old IP addresses (older than one month) now.

[2017-02-12]
 * Added a simple .env file reader.
 * Added .env file for easy configuration (in the root directory).

[2017-01-31]
 * Optimized the code in imageupload.ajax.php.
 * Optimized the code for php >= 7.0.
 
[2017-01-16]
 * Added the 'referrer' field to the database.




Uses
----
* Free-file-icons
  https://github.com/teambox/Free-file-icons
  