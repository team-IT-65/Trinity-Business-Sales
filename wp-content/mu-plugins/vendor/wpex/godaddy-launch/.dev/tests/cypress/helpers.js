/**
 * Login to our test WordPress site
 */
export function loginToSite() {
	const username = Cypress.env('wpUsername');
	const password = Cypress.env('wpPassword');

	cy.session(
		[username, password],
		() => {
			cy.visit( Cypress.env( 'testURL' ) + '/wp-login.php' );
			cy.wait( 250 );

			cy.get( '#user_login' ).type( username );
			cy.get( '#user_pass' ).type( password );
			cy.get( '#wp-submit' ).click();

			cy.url().should('contain', '/wp-admin');
		},
		{
			cacheAcrossSpecs: true,
		}
	);
}

/**
 * Go to a specific URI.
 *
 * @param {string} path The URI path to go to.
 */
export function goTo( path = '/wp-admin' ) {
	cy.visit( Cypress.env( 'testURL' ) + path );

	return getWindowObject();
}

/**
 * Safely obtain the window object or error
 * when the window object is not available.
 */
export function getWindowObject() {
	const editorUrlStrings = [ 'post-new.php', 'action=edit' ];
	return cy.window().then( ( win ) => {
		const isEditorPage = editorUrlStrings.filter( ( str ) => win.location.href.includes( str ) );

		if ( isEditorPage.length === 0 ) {
			throw new Error( 'Check the previous test, window property was invoked outside of Editor.' );
		}

		if ( ! win?.wp ) {
			throw new Error( 'Window property was invoked within Editor but `win.wp` is not defined.' );
		}

		return win;
	} );
}
