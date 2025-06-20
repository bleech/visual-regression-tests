const cache = new Map();

export const cachedFetch = async ( url ) => {
	if ( cache.has( url ) ) {
		return cache.get( url );
	}

	const response = await fetch( url );
	const data = await response.text();

	cache.set( url, data );

	return data;
};

export const clearFetchCache = ( url ) => {
	if ( url ) {
		cache.delete( url );
	} else {
		cache.clear();
	}
};

export const setFetchCache = ( url, data ) => {
	cache.set( url, data );
};
