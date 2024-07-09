const { defineConfig } = require( 'cypress' );

module.exports = defineConfig( {
	chromeWebSecurity: false,
	defaultCommandTimeout: 30000,
	e2e: {
		// We've imported your old cypress plugins here.
		// You may want to clean this up later by importing these.
		setupNodeEvents( on, config ) {
			return require( './.dev/tests/cypress/plugins/index.js' )( on, config );
		},
		specPattern: './/**/*.cypress.js',
		supportFile: '.dev/tests/cypress/support/commands.js',
	},
	env: {
		testURL: 'http://localhost:8889',
		wpPassword: 'password',
		wpUsername: 'admin',
	},
	fixturesFolder: 'languages',
	pageLoadTimeout: 120000,
	retries: {
		openMode: 0,
		runMode: 0,
	},
	screenshotsFolder: '.dev/tests/cypress/screenshots',
	videosFolder: '.dev/tests/cypress/videos',
	viewportHeight: 1440,
	viewportWidth: 2560,
} );
