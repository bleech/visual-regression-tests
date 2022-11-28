const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const { resolve } = require( 'path' );

const url = 'https://bleech-vrtt-mvp.local.blee.ch';
const isProduction = process.env.NODE_ENV === 'production';

let config = {
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

if ( ! isProduction ) {
	const BrowserSyncPlugin = require( 'browser-sync-webpack-plugin' );

	config = {
		...config,
		plugins: [
			...defaultConfig.plugins,
			new BrowserSyncPlugin(
				{
					host: 'localhost',
					port: 3000,
					proxy: url,
					files: [
						{
							match: [ '**/*.php' ],
							fn( event ) {
								if ( event === 'change' ) {
									const bs =
										require( 'browser-sync' ).get(
											'bs-webpack-plugin'
										);
									bs.reload();
								}
							},
						},
					],
				},
				{
					reload: true,
				}
			),
		],
	};
}

module.exports = config;
