<!DOCTYPE html>
<html>
  <head>
    <title>// hundredfourty // Test Imageupload library</title>
    <meta charset="UTF-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="description" content="Content Description" />
    <meta name="author" content="Ikaros Kappler" />

    <meta name="date" content="2016-12-21" />

    <script src="../js/jquery-3.1.1.min.js"></script>
    <script src="../js/dropzone.js"></script>
    <link rel="stylesheet" href="../css/dropzone.css" />
    <style>
      #upload-widget {
    display : flex;
    align-items: center;
    width       : 500px;
    height      : 200px;
    border      : 1px dashed #6b8cb6;
      }

.dropzone .dz-preview .dz-image {
   width: 64px;
   height: 64px;
 }
    </style>
  </head>

  <body>
      Select image:
      <form id="upload-widget" method="post" action="../ajax/imageupload.ajax.php" class="dropzone">
      </form>
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
      
      /*
      Dropzone.options.dz0 = {
        url : '../ajax/imageupload.ajax.php',
        acceptedFiles : 'image/*,application/pdf,.psd',
        dictDefaultMessage : 'Put your files here',
        previewsContainer: document.getElementById("dz-preview-wrapper"),
        paramName: "file", // The name that will be used to transfer the file
        maxFilesize: 2, // MB
        _accept: function(file, done) {
              //if (file.name == "justinbieber.jpg") {
              //    done("Naha, you don't.");
              //}
              //else {
                  console.debug('accept');
                  done();
              //}
          },
        _complete : function( file ) {
              console.log('complete: '+file );
            // Move preview wrapper into different container
            //$('#dz-preview-wrapper').append( $('#dz0 .dz-preview').detach() );
        }     
      };
      */

/*      
      $( '#dz0' ).dropzone( { url : '../ajax/imageupload.ajax.php',
              acceptedFiles : 'image/*,application/pdf,.psd,.xcf,.ai',
              //dictDefaultMessage: "Put your files here",
              init : function() {
                 this.on('addedfile',function(file) { console.log('Added file: '+file+'.'); } );
                 //$('#dz0').addClass('dropzone');
          },
              //thumbnail : function( file, dataURI ) {
              //console.debug( 'thumbnail created: ' + file  );
              //},
              _complete : function( data, result ) {
              console.log('complete: '+data + ", result: " + result );
              // Move preview wrapper into different container
              //$('#dz-preview-wrapper').append( $('#dz0 .dz-preview').detach() );
          },
              _success : function(file,serverFileName) {
              console.debug('success: ' + serverFileName);
              //$('#dz-preview-wrapper').append( $('#dz0 .dz-preview').detach() );
                  }
      } ).on( 'success', function( data, result ) {
          console.debig( "Complete: " + data );
      } );
*/    
      
      </script>
  </body>
</html>