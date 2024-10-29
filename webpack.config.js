const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const { resolve } = require( 'path' );

const config = {
	...defaultConfig,
	entry: {
		editor: resolve( process.cwd(), 'assets', 'editor.js' ),
		admin: resolve( process.cwd(), 'assets', 'admin.js' ),
	},
	output: {
		filename: '[name].js',
		path: resolve( process.cwd(), 'build' ),
	},
	resolve: {
		...defaultConfig.resolve,
		alias: {
			...defaultConfig.resolve.alias,
			scripts: resolve( process.cwd(), 'assets/scripts' ),
			editor: resolve( process.cwd(), 'editor' ),
		},
	},
};

module.exports = config;
