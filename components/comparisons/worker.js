function getColoredPixelsWithLimit( imageData, xLimit, direction = 'left' ) {
	const coloredPixels = new Set();
	for ( let i = 0; i < imageData.data.length; i += 4 ) {
		const r = imageData.data[ i ];
		const g = imageData.data[ i + 1 ];
		const b = imageData.data[ i + 2 ];
		const a = imageData.data[ i + 3 ];
		if ( ( r !== g || g !== b ) && a !== 0 ) {
			const x = ( i / 4 ) % imageData.width;
			if ( direction === 'left' ? x < xLimit : x > xLimit ) {
				// push x and y coordinates
				coloredPixels.add( Math.floor( i / 4 / imageData.width ) );
			}
		}
	}
	return Array.from( coloredPixels );
}

self.onmessage = function ( e ) {
	if ( e.data?.action === 'analyzeImage' ) {
		const { imageData } = e.data;
		const coloredPixels = getColoredPixelsWithLimit(
			imageData,
			0,
			'right'
		);
		self.postMessage( { action: 'analyzedImage', coloredPixels } );
	}
};
