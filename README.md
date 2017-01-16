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

[2017-01-16]
 * Added the 'referrer' field to the database.