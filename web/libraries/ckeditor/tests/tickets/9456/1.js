/* bender-tags: editor,unit,clipboard,pastefromword */
/* bender-ckeditor-plugins: clipboard,pastefromword,format,ajax */
/* global assertPasteEvent */

( function() {
	'use strict';

	bender.editor = true;

	var compat = bender.tools.compatHtml;

	function testWordFilter( editor ) {
		return function( input, output ) {
			assertPasteEvent( editor, { dataValue: compat( input, 1 ) }, function( data, msg ) {
				assert.areSame( compat( output ), compat( data.dataValue ), msg );
			}, 'tc1', true );
		};
	}

	bender.test( {
		'test list transformation': function() {
			bender.tools.testInputOut( 'list_1', testWordFilter( this.editor ) );
		}
	} );

} )();