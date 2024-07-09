/* eslint-disable sort-keys */
module.exports = {
	preset: '@wordpress/jest-preset-default',
	rootDir: '../../../',
	resolver: '<rootDir>/.dev/tests/jest/resolver.js',
	moduleNameMapper: {
		'@godaddy-wordpress/coblocks-icons': require.resolve(
			'@wordpress/jest-preset-default/scripts/style-mock.js'
		),
	},
	setupFilesAfterEnv: [
		require.resolve( '@wordpress/jest-preset-default/scripts/setup-globals.js' ),
		'<rootDir>/.dev/tests/jest/setup-globals.js',
	],
	testMatch: [ '**/__tests__/*.test.js' ],
};
