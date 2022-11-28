import './editor.scss';

function importAll( r ) {
	r.keys().forEach( r );
}

importAll( require.context( '../editor/plugins/', true, /\/index\.js$/ ) );
