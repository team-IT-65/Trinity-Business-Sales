/* eslint-disable jest/expect-expect */

const routeMilestonePublishNow = encodeURIComponent( '/gdl/v1/milestone/publish' );

const interceptSuccess = {
	body: {
		response: 'Created',
		status: 201,
	},
	statusCode: 201,
};

describe( 'Test Publish Guide Component', () => {
	beforeEach( () => {
		cy.visit( Cypress.env( 'testURL' ) + '/wp-admin/post-new.php' );

		cy.url().should( 'contain', '/wp-admin/post-new.php' ).then( () => {
			// Reset the publish guide.
			cy.window().then( async ( win ) => {
				await win.wp.apiFetch( {
					data: {
						blog_public: false,
						description: 'Just another WordPress site',
						gdl_all_tasks_completed: false,
						gdl_live_site_dismiss: true,
						gdl_pgi_add_domain: '',
						gdl_pgi_add_product: '',
						gdl_pgi_site_content: '',
						gdl_pgi_site_design: '',
						gdl_pgi_site_info: '',
						gdl_pgi_site_media: '',
						gdl_publish_guide_interacted: false,
						gdl_publish_guide_opt_out: false,
						gdl_site_published: false,
						sitelogo: '',
						theme_mods_go: '',
						title: 'A WordPress Site',
					},
					method: 'POST',
					path: '/wp/v2/settings',
				} );
			} );

			// Reload the page to ensure the settings are reset.
			cy.reload();

			// Ensure gdlLiveSiteControlData is defined.
			cy.window().then( ( win ) => {
				if ( win.gdlLiveSiteControlData === undefined ) {
					throw new Error( 'gdlLiveSiteControlData should be defined at this point' );
				}
			} );
		} );
	} );

	it( 'Can access the Publish Guide', () => {
		cy.get( '[data-test-eid="wp.editor.guide.open.click"]' ).click();
		cy.get( '[data-test-eid="wp.editor.guide.launch.click"]' );
	} );

	it( 'Can complete the "Add site details" section', () => {
		const randomNumber = Math.floor( ( Math.random() * 10000 ) + 1 );
		const siteName = `Some New Random Title - ${ randomNumber }`;
		const siteDescription = 'Some description';

		cy.get( '[data-test-eid="wp.editor.guide.open.click"]' ).click();

		cy.get( '[data-test-eid="wp.editor.guide/item/site_info.panel.click"]' ).click();

		cy.get( '[data-test-eid="wp.editor.guide/item/site_info.edit.click"]' ).click();

		cy.get( '[data-test-eid="wp.editor.guide/item/site_info.input_title.click"]' )
			.clear()
			.type( siteName );

		cy.get( '[data-test-eid="wp.editor.guide/item/site_info.input_description.click"]' )
			.clear()
			.type( siteDescription );

		cy.get( '.preview-info' ).contains( siteName );
		cy.get( '.preview-info' ).contains( siteDescription );

		cy.get( '[data-test-eid="wp.editor.guide/item/site_info.save.click"]' ).click();

		cy.get( '.publish-guide-popover__item' )
			.eq( 0 )
			.should( 'have.class', 'is-completed' );

		cy.reload();

		cy.title().should( 'contain', siteName );

		cy.get( '[data-test-eid="wp.editor.guide.open.click"]' ).click();
	} );

	it( 'Add your domain', () => {
		cy.get( '[data-test-eid="wp.editor.guide.open.click"]' ).click();
		cy.get( '[data-test-eid="wp.editor.guide/item/add_domain.panel.click"]' ).click();
	} );

	it( 'Launch my site', () => {
		cy.intercept( 'POST', `*${ routeMilestonePublishNow }*`, interceptSuccess ).as( 'milestone-publish-now' );

		cy.get( '[data-test-eid="wp.editor.guide.open.click"]' ).click();
		cy.get( '[data-test-eid="wp.editor.guide.launch.click"]' ).click();

		// Not yet
		cy.get( '[data-test-eid="wp.editor.launch/modal/finish/choices.no.click"]' ).click();

		// Tooltip shows
		cy.get( '.publish-guide-tooltip__title' );
		cy.get( '.publish-guide-tooltip__close' ).click();
		cy.get( '[data-test-eid="wp.editor.guide.open.click"]' ).click();
		cy.get( '[data-test-eid="wp.editor.guide.launch.click"]' ).click();

		// This time, launch
		cy.get( '[data-test-eid="wp.editor.launch/modal/finish/choices.yes.click"]' ).click();
		cy.wait( '@milestone-publish-now' );

		// There is a button to view the site
		cy.get( '.components-button' ).contains( 'View Site' );
		cy.get( '[aria-label="Close"]' ).click();

		cy.get( '[data-test-eid="wp.editor.guide.open.click"]' ).click();

		cy.get( '.components-editor-notices__snackbar' ).contains( 'Site published!' );

		// Launch button is not present anymore (TODO : make it work consistently)
		// cy.get( '[data-test-eid="wp.editor.guide.launch.click"]' ).should( 'not.exist' );
	} );

	it( 'Will hide the Publish Guide when MWC walkthrough is visible', () => {
		cy.get( '[data-test-eid="wp.editor.guide.open.click"]' ).click();
		cy.get( '#gdl-publish-guide' ).should( 'exist' );

		// Set the Query parameter to simulate the walkthrough being visible.
		cy.url().then( ( currentLocation ) => {
			const url = new URL( currentLocation );
			url.searchParams.set( 'isMwcDialogOpened', 'true' );
			cy.window().then( ( win ) => {
				win.history.pushState( null, '', url.toString() );
			} );
		} );
		cy.get( '#gdl-publish-guide-trigger-button' ).should( 'not.exist' );

		// Remove the query param to check the Publish Guide is visible again.
		cy.url().then( ( currentLocation ) => {
			const url = new URL( currentLocation );
			url.searchParams.delete( 'isMwcDialogOpened' );
			cy.window().then( ( win ) => {
				win.history.pushState( null, '', url.toString() );
			} );
		} );
		cy.get( '#gdl-publish-guide-trigger-button' ).should( 'exist' );
	} );

	it( 'Should notify the nux api when a publish guide step is skipped or completed', () => {
		const siteInfoMilestoneUpdate = encodeURIComponent( 'gdl/v1/milestone/site-info' );

		const milestoneSuccessResponse = {
			body: {
				response: 'Created',
				status: 201,
			},
			statusCode: 201,
		};

		cy.intercept( 'POST', `*${ siteInfoMilestoneUpdate }*`, milestoneSuccessResponse ).as( 'site-info-milestone-update' );

		cy.get( '[data-test-eid="wp.editor.guide.open.click"]' ).click();

		cy.get( '[data-test-eid="wp.editor.guide/item/site_info.panel.click"]' ).click();
		cy.get( '[data-test-eid="wp.editor.guide/item/site_info.edit.click"]' ).click();
		cy.get( '[data-test-eid="wp.editor.guide/item/site_info.input_title.click"]' ).click().type( 'test' );
		cy.get( '[data-test-eid="wp.editor.guide/item/site_info.input_description.click"]' ).click().type( 'test' );
		cy.get( '[data-test-eid="wp.editor.guide/item/site_info.save.click"]' ).click();

		cy.wait( '@site-info-milestone-update' );

		cy.reload();

		const addDomainMilestoneUpdate = encodeURIComponent( 'gdl/v1/milestone/add-domain' );

		cy.intercept( 'POST', `*${ addDomainMilestoneUpdate }*`, milestoneSuccessResponse ).as( 'add-domain-milestone-update' );

		cy.get( '[data-test-eid="wp.editor.guide.open.click"]' ).click();

		// trigger the skip nux-api post for add-domain
		cy.get( '[data-test-eid="wp.editor.guide/item/add_domain.panel.click"]' ).click();
		cy.get( '[data-test-eid="wp.editor.guide/item/add_domain.skip.click"]' ).first().click();

		cy.wait( '@add-domain-milestone-update' );
	} );
} );
