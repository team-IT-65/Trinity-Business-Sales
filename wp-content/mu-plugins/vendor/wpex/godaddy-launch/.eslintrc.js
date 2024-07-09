/* eslint-disable sort-keys */
module.exports = {
	extends: [ '@godaddy-wordpress/eslint-config' ],

	// CoBlocks config
	env: {
		browser: true,
		'cypress/globals': true,
		jest: true,
	},
	plugins: [
		'cypress',
	],

	rules: {
		ignoreCase: 0,
	},

	// Specific Globals used in CoBlocks
	globals: {
		page: true,
	},

};
