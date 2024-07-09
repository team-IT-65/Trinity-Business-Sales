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
						gdl_live_site_dismiss: false,
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

	it( 'Critical Path: Publish Guide Launch Now -> Confirm -> Success', () => {
		cy.get( '[data-test-eid="wp.editor.guide.open.click"]' ).click();
		cy.get( '[data-test-eid="wp.editor.guide.launch.click"]' ).click();
		cy.get( '[data-test-eid="wp.editor.launch/modal/finish/choices.yes.click"]' ).click();

		cy.intercept( 'POST', `*${ routeMilestonePublishNow }*`, interceptSuccess ).as( 'milestone-publish-now' );
		cy.wait( '@milestone-publish-now' );
	} );

	it( 'Critical Path: Publish Guide Launch Now -> Nevermind', () => {
		cy.get( '[data-test-eid="wp.editor.guide.open.click"]' ).click();
		cy.get( '[data-test-eid="wp.editor.guide.launch.click"]' ).click();
		cy.get( '[data-test-eid="wp.editor.launch/modal/finish/choices.no.click"]' ).click();

		// Check that Launch Now button still exists in the Publish Guide.
		cy.get( '[data-test-eid="wp.editor.guide.open.click"]' ).click();
		cy.get( '[data-test-eid="wp.editor.guide.launch.click"]' );
	} );
} );
