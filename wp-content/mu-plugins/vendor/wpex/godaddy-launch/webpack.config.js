const path = require( 'path' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

const packages = [
	'live-site-control',
	'publish-guide',
];

module.exports = {
	...defaultConfig,

	entry: {
		...packages.reduce( ( memo, packageName ) => {
			memo[ packageName ] = [ path.resolve( process.cwd(), 'src', packageName, 'index.js' ) ];
			return memo;
		}, {} ),
	},

	output: {
		...defaultConfig.output,
		publicPath: 'auto',
	},
};
