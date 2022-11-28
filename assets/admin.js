import './admin.scss';

function importAll( r ) {
	r.keys().forEach( r );
}

importAll(
	require.context( '../components/', true, /\/script\.js$/ ),
	require.context( '../components/', true, /\/_style\.scss$/ ),
	require.context( '../includes/core/settings/', true, /\/field\.js$/ )
);
