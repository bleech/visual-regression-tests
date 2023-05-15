const path = require( 'path' );

module.exports = {
	extends: [ 'plugin:@wordpress/eslint-plugin/recommended' ],
	ignorePatterns: [ 'svn/*' ],
	settings: {
		'import/resolver': {
			alias: {
				map: [
					[ 'scripts', path.resolve( __dirname, 'assets/scripts' ) ],
					[ 'editor', path.resolve( __dirname, 'editor' ) ],
				],
			},
		},
	},
};
