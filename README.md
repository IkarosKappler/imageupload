# imageupload

This is a simple image/file upload script for AJAX uploads (e.g. with DropZone.js) written in PHP.
It uses the laravel/eloquent framework for database access.



Files
-----
 ./database/.env

```text
 This file should contain your DB credentials
  APP_KEY=SomeRandomString
  DB_USERNAME=
  DB_PASSWORD=
  DB_DATABASE=
  DB_HOST=
```

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
[2017-01-31]
 * Optimized the code in imageupload.ajax.php.
 * Optimized the code for php >= 7.0.
 
[2017-01-16]
 * Added the 'referrer' field to the database.