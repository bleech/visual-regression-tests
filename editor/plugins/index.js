// Native
import { registerPlugin } from '@wordpress/plugins';
import {
	PluginSidebar,
	PluginSidebarMoreMenuItem,
	PluginDocumentSettingPanel,
} from '@wordpress/edit-post';
import { PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { createElement } from '@wordpress/element';

// Custom
import Metabox from 'editor/components/metabox';
const vrtsLogoIcon = createElement(
	'svg',
	{ width: 20, height: 20, viewBox: '0 0 20 20' },
	createElement( 'path', {
		d: 'M10.66 19a8.906 8.906 0 0 0 4.914-1.903H10.66V19zm0-3.194h6.254a9.27 9.27 0 0 0 1.236-1.935h-7.49v1.935zm0-3.226h7.992c.188-.63.305-1.279.348-1.936h-8.34v1.936zm7.992-5.16H10.66v1.936H19a8.772 8.772 0 0 0-.348-1.936zm-1.738-3.226H10.66V6.13h7.49v-.001a9.365 9.365 0 0 0-1.236-1.935zM10.66 1v1.904h4.914A8.913 8.913 0 0 0 10.66 1zM1 10a9.047 9.047 0 0 0 2.423 6.145 9.018 9.018 0 0 0 5.949 2.854V1a9.016 9.016 0 0 0-5.949 2.854A9.049 9.049 0 0 0 1 10z',
	} )
);
const pluginName = window.vrts_editor_vars.plugin_name;

registerPlugin( 'visual-regression-tests-plugin-sidebar', {
	render: () => {
		return (
			<>
				<PluginDocumentSettingPanel
					className="vrts_post_options_metabox"
					name="visual-regression-tests-document-setting-panel"
					title={ pluginName }
					icon={ vrtsLogoIcon }
				>
					<Metabox />
				</PluginDocumentSettingPanel>

				<PluginSidebarMoreMenuItem
					target="visual-regression-tests-sidebar"
					icon={ vrtsLogoIcon }
				>
					{ pluginName }
				</PluginSidebarMoreMenuItem>

				<PluginSidebar
					className="vrts_post_options_metabox"
					name="visual-regression-tests-sidebar"
					title={ pluginName }
					icon={ vrtsLogoIcon }
				>
					<PanelBody
						title={ __( 'Options', 'visual-regression-tests' ) }
						intialOpen={ true }
					>
						<Metabox />
					</PanelBody>
				</PluginSidebar>
			</>
		);
	},
} );
