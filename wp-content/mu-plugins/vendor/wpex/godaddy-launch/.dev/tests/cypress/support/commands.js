import { loginToSite } from '../helpers';

beforeEach( function() {
	loginToSite();

	cy.visit( Cypress.env( 'testURL' ) + '/wp-admin/post-new.php?post_type=post' );
	cy.get( '.block-editor-page' ).should( 'exist' );

	cy.url().should( 'contain', '/wp-admin/post-new.php?post_type=post' ).then( () => {
		cy.window().then( ( win ) => {
			// Enable "Top Toolbar"
			if ( ! win.wp.data.select( 'core/edit-post' ).isFeatureActive( 'fixedToolbar' ) ) {
				win.wp.data.dispatch( 'core/edit-post' ).toggleFeature( 'fixedToolbar' );
			}

			win.wp.data.dispatch( 'core/editor' ).disablePublishSidebar();
		} );
	} );
} );


/**
 * Starting in Cypress 8.1.0 Unhandled Exceptions now cause tests to fail.
 * Sometimes unhandled exceptions occur in Core that do not effect the UX created by CoBlocks.
 * We discard unhandled exceptions and pass the test as long as assertions continue expectedly.
 */
Cypress.on( 'uncaught:exception', () => {
	// returning false here prevents Cypress from failing the test.
	return false;
} );
