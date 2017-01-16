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
  </head>

  <body>
      Select image:
      <input id="imagefile" type="file" name="file0" accept="image/*" />
      <br/>
      <button onclick="uploadfile()">Upload (AJAX)</button>
      <br/>
      <div id="out"></div>

      <script>
      function uploadfile() {
          var $input = $( '#imagefile' );
          var files = $input[0].files[0];
          console.debug( "files=" + JSON.stringify(files) );

          var data = new FormData();
          /*
          for( var k in files ) {
              data.append(k,files[k]);
              console.debug('File: '+k);
              }
          */
          data.append('file0',files);
          
          $.ajax(
          { url: '../ajax/imageupload.ajax.php',
                  type: 'POST',
                  data: data,
                  cache: false,
                  dataType: 'json',
                  processData: false, // Don't process the files
                  contentType: false, // Set content type to false as jQuery will tell the server its a query string request
                  success: function(data, textStatus, jqXHR) {
                      if(typeof data.error === 'undefined') {
                          // Success so call function to process the form
                          console.debug('success: ' + JSON.stringify(data) );
                          $( '#out' ).empty().html( '<code>' + JSON.stringify(data) + '</code>' );
                      } else {
                          // Handle errors here
                          console.log('ERRORS: ' + data.error);
                          $( '#out' ).empty().html( data.error );
                      }
              },
                  error: function(jqXHR, textStatus, errorThrown)
                  {
                      // Handle errors here
                      console.log('ERRORS: ' + textStatus);
                      $( '#out' ).empty().html( "Error: " + textStatus );
                      // STOP LOADING SPINNER
                  }
          });
      }
      
      </script>
      
  </body>
</html>