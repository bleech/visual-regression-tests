/**
 * Client-side cache and prefetch for alert page navigation.
 */

const CACHE_TTL = 5 * 60 * 1000;
const CACHE_MAX_ENTRIES = 10;

const cache = new Map();

let navigationSequence = 0;

const warmedAlerts = new Map();
const MAX_ACTIVE_WARMS = 2;
let activeWarms = 0;

export function startNavigation() {
	navigationSequence += 1;
	return navigationSequence;
}

export function isCurrentNavigation( token ) {
	return token === navigationSequence;
}

export function fetchPage( url ) {
	const existing = cache.get( url );

	if ( existing ) {
		if ( window.Date.now() - existing.time < CACHE_TTL ) {
			cache.delete( url );
			cache.set( url, existing );
			return existing.promise;
		}

		cache.delete( url );
	}

	const entry = {
		time: window.Date.now(),
	};

	entry.promise = fetch( url )
		.then( ( response ) => {
			if ( ! response.ok ) {
				throw new Error( `HTTP ${ response.status }` );
			}

			return response.text();
		} )
		.catch( ( error ) => {
			if ( cache.get( url ) === entry ) {
				cache.delete( url );
			}

			throw error;
		} );

	cache.set( url, entry );

	while ( cache.size > CACHE_MAX_ENTRIES ) {
		cache.delete( cache.keys().next().value );
	}

	return entry.promise;
}

/**
 * Cache an alert's page and pre-load its comparison images. Best-effort:
 * the marker means "attempt spent", not "images cached" — the alert's
 * `<img>` tags load anything still missing when it is opened.
 *
 * @param {string} href Alert page URL.
 */
export function warmAlert( href ) {
	if ( ! href || window.navigator.connection?.saveData ) {
		return;
	}

	const warmedAt = warmedAlerts.get( href );

	if (
		warmedAt &&
		window.Date.now() - warmedAt < CACHE_TTL &&
		cache.has( href )
	) {
		return;
	}

	if ( activeWarms >= MAX_ACTIVE_WARMS ) {
		return;
	}

	warmedAlerts.set( href, window.Date.now() );
	activeWarms += 1;

	while ( warmedAlerts.size > 100 ) {
		warmedAlerts.delete( warmedAlerts.keys().next().value );
	}

	fetchPage( href )
		.then( ( html ) => {
			const doc = new window.DOMParser().parseFromString(
				html,
				'text/html'
			);

			return Promise.all(
				[
					...doc.querySelectorAll(
						'vrts-comparisons img[crossorigin]'
					),
				].map(
					( $img ) =>
						new Promise( ( resolve ) => {
							const image = new window.Image();

							// Match the rendered image request so the browser can
							// reuse this in-flight or cached response on insertion.
							image.crossOrigin = 'anonymous';
							image.fetchPriority = 'low';
							image.onload = resolve;
							image.onerror = resolve;
							image.src = $img.getAttribute( 'src' );
						} )
				)
			);
		} )
		.catch( () => {} )
		.finally( () => {
			activeWarms -= 1;
		} );
}

export function invalidateAlert( alertId ) {
	cache.forEach( ( entry, url ) => {
		const params = new window.URL( url, window.location.origin )
			.searchParams;

		if ( params.get( 'alert_id' ) === String( alertId ) ) {
			cache.delete( url );
		}
	} );
}
