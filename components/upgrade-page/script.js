import iFrameResize from 'iframe-resizer/js/iframeResizer';

const isVrtsUpgradePage = document.querySelector( '.vrts_upgrade_page' );

if ( isVrtsUpgradePage ) {
	iFrameResize(
		{
			checkOrigin: false,
			heightCalculationMethod: 'taggedElement',
		},
		'#vrts_upgrade_iframe'
	);
}
