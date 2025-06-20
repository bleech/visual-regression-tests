export function requestIdleCallback( callback, options = {} ) {
	if ( typeof window.requestIdleCallback === 'function' ) {
		return window.requestIdleCallback( callback, options );
	}

	const timeout = options.timeout || 50;
	const start = Date.now();

	return setTimeout( () => {
		callback( {
			didTimeout: false,
			timeRemaining: () =>
				Math.max( 0, timeout - ( Date.now() - start ) ),
		} );
	}, 1 );
}

export function cancelIdleCallback( id ) {
	if ( typeof window.cancelIdleCallback === 'function' ) {
		return window.cancelIdleCallback( id );
	}

	clearTimeout( id );
}
