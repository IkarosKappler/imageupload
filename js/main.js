/**
 * @require jquery
 * @require jquery-confirm
 * @require jquery-linkify
 * @require getURIParams
 *
 * @author  Ikaros Kappler
 * @version 1.0.0
 * @date    2016-12-01
 **/


/**
 * Initialize the app.
 **/
$( document ).ready( function() {

    // First of all catch the GET params. We are especially interested
    // in the 'cat' param (category). If left blank, use the empty
    // category ''.
    var URI_PARAMS = getURIParams();
    var CATEGORY   = ('cat' in URI_PARAMS) ? URI_PARAMS['cat'].toLowerCase() : '';

    // Fetch frequently asked questions ... ehm ... fetch frequently used DOM objects.
    var $hundretforty = $( '#hundretforty' );                    // The main container.
    var $container    = $hundretforty.find( '#notes' );          // The sub container for all notes.
    var $template     = $container.find( '#_template-note' );    // The note template.
    var $btnMore      = $( '#btn-more' );                        // The button for loading more notes.
    var $inputArea    = $hundretforty.find( '#note-text' );      // The text input area (max 140 chars).
    var $_loading     = $( '<img/>', { src : 'img/loading3.svg', width : 32, height : 32 } ); // A loading animation.

    
    // Add loading animations to the loading containers.
    var $loadingSend  = $( '#loading-send' ).append( $_loading.clone() );
    var $loadingMore  = $( '#loading-more' ).append( $_loading.clone() );

    
    
    // Install 'send' button handler.
    $hundretforty.find( '#btn-sendnote' ).click( function() {
	if( !$inputArea.val() ) {
	    setErrorStatus( "You should enter some text." );
	    return;
	}
	clearErrorStatus();
	$loadingSend.removeClass('invisible');
	$_btn = $(this);
	$_btn.prop( 'disabled', 'disabled' );
	window.setTimeout( function() {
	    var jqxhr = $.getJSON( 'ajax/create.ajax.php?cat=' + encodeURIComponent(CATEGORY)+ '&note=' + encodeURIComponent( $inputArea.val() ) )
		.done( function( json ) {
		    // // Note was posted. Add new note element to DOM and clear input area.
		    $note = createNoteNode( json.note );		    
		    $note.insertAfter( $template ); 
		    $inputArea.val('');
		    $textLength.empty().html( '0' );
		} )
		.fail( function(jqxhr, textStatus, error) {
		    setErrorStatus( error + ": " + textStatus + " " + jqxhr.responseJSON.message );
		} )
		.always( function() {
		    $_btn.prop( 'disabled', false );
		    $loadingSend.addClass('invisible');
		} );
	}, 1000 );
    } );
    

    /**
     * This function sends a request to the remote server for
     * deleting the passed note.
     **/
    var requestDelete = function(noteData) {
	$.confirm({
	    title        : 'Delete Note',
	    content      : 'Do you really want to delete note #'+noteData.id+'?',
	    boxWidth     : '300px',
	    useBootstrap : false,  // Required so 'boxWidth' can override bootstrap
	    buttons: {	
		confirm : {
		    text: 'Yes',
		    btnClass: '',
		    keys: ['enter', 'shift'],
		    action: function(){
			deleteNote(noteData);
		    }
		},
		deny : {
		    text: 'No',
		    btnClass: '',
		    keys: ['esc'],
		    action: function(){
			// NOOP
		    }
		}
	    }
	});
    }

    /**
     * This function sends a request to the remote server for
     * deleting the passed note.
     **/
    var deleteNote = function(noteData) {
	var url = 'admin/delete.ajax.php?id=' + encodeURIComponent(noteData.id) + '&key=' + encodeURIComponent(noteData.sha256);
	var jqxhr = $.getJSON( url )
	    .done( function( json ) {
		// Remove from DOM
		$( '#note_'+noteData.id ).detach();
	    } )
	    .fail( function(jqxhr, textStatus, error) {
		// 401 Unauthorized?
		if( jqxhr.status == '401' ) {
		    $.alert({
			title        : 'Unauthorized',
			boxWidth     : '300px',
			useBootstrap : false,  // Required so 'boxWidth' can override bootstrap
			content      : 'It seems that authentication failed.<br/><br/>Want to try <a href="'+url+'" target="_blank">manually</a>?',
		    });
		} else {
		    setErrorStatus( '[' + jqxhr.status + '] ' + error + ": " + textStatus + " " + jqxhr.responseText );
		}
	    } );
    };

    /**
     * Replaces special characters (such as lines breaks, by '<br/>').
     **/
    var formatText = function( msg ) {
	msg = msg.replace(/\n/g, '<br/>');
	return msg;
    }
    
    var createNoteNode = function( noteData ) {
	var $container = $hundretforty.find( '#notes' );

	var rand       = noteData.id; 
	var $note      = $template.clone().removeClass('hidden').attr('id','note_'+rand);
	var $showLink  = $( '<span/>', { name : 'note_'+noteData.id } ).addClass('inner-link').addClass('clickable').html( '&#x1f517;' ).click( function() { showNoteData(noteData); } );
	$note.find( '#_template-date' ).attr('id','template-date-'+rand).empty().html( $showLink ).append( ' '+noteData.created_at );
	$note.find('a.boxclose').click( function() { requestDelete(noteData); } ); 
	$note.find( '#_template-data' ).attr('id','template-data-'+rand).empty().html( formatText(noteData.data) ).linkify().hashtagify();
	return $note;
    };
    
    var processResult = function( data ) {
	clearErrorStatus();
	console.debug( data );
	data.list.forEach( function(noteData) {
	    console.debug( JSON.stringify(noteData) );
	    var $note = createNoteNode( noteData );
	    // Attach to DOM.
	    $container.append( $note );
	} );
	// Update button settings.
	$btnMore.data( 'skip', parseInt(data.meta.skip)+5 );
	// Disable 'more' button if history is fully loaded.
	if( data.list.length == 0 )
	    $btnMore.prop('disabled',true);
    };

    
    var setErrorStatus = function( msg ) {
	$( '#error-status' ).empty().html( msg );
    };

    var clearErrorStatus = function( ) {
	$( '#error-status' ).empty().html( '&nbsp;' );
    };

    
    $btnMore.click( function() {
	console.debug( "btn.data.skip=" + $btnMore.data('skip') + ", btn.data.limit=" + $btnMore.data('limit') );
	loadNotes( $btnMore.data('skip'), $btnMore.data('limit') );
    } );


    /**
     * Send the next load-request to the server. The answer should be
     * a JSON obejct with n more tweets .... ehhm notes.
     **/
    var loadNotes = function( skip, limit ) {
	$loadingMore.removeClass('invisible');
	//$btnMore = $(this);
	$btnMore.prop( 'disabled', 'disabled' );
	window.setTimeout( function() {
	    var jqxhr = $.getJSON( 'ajax/list.ajax.php?cat=' + encodeURIComponent(CATEGORY) + '&skip=' + skip + '&limit=' + limit )
		.done( function( json ) {
		    processResult(json);
		} )
		.fail( function(jqxhr, textStatus, error) {
		    setErrorStatus( error + ": " + textStatus + " " + jqxhr.responseJSON.message );
		} )
		.always( function() {
		    $btnMore.prop( 'disabled', false );
		    $loadingMore.addClass('invisible');
		} );
	}, 200 );
    };


    var showNoteData = function( noteData ) {
	setErrorStatus( 'Sorry, not yet implemented.' );

	$( '#dialog' ).dialog( {
	    closeText : 'X',
	    width     : '35%',
	    height    : 400,
	    modal     : true
	} );
    }
    
    
    /**
     * A wrapper for the text-length display.
     **/
    var $textLength = $( 'span#textlength' );
    $inputArea.keyup( function() {
	$textLength.empty().html( $inputArea.val().length );
    } );


    
    /**
     * Load the first chunk of notes.
     **/
    $btnMore.data('skip',0);
    $btnMore.data('limit',7);
    loadNotes( $btnMore.data('skip'), $btnMore.data('limit') );
} );
