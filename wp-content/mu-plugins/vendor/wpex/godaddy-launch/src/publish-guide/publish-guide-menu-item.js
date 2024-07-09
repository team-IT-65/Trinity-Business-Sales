/* global gdvPublishGuideDefaults */

import { __ } from '@wordpress/i18n';
import { useEntityProp } from '@wordpress/core-data';

import { PluginMoreMenuItem } from '@wordpress/edit-post';
import { useDispatch } from '@wordpress/data';

import CheckBoxList from './icons/checkbox-list-black';

import './index.scss';

export const PublishGuideMenuItem = () => {
	const { saveEditedEntityRecord } = useDispatch( 'core' );

	const [ publishGuideOptOut, setPublishGuideOptOut ] = useEntityProp( 'root', 'site', gdvPublishGuideDefaults.optionOptOut );

	const handleOptInPublishGuide = () => {
		setPublishGuideOptOut( false );
		saveEditedEntityRecord( 'root', 'site' );
	};

	return (
		<>
			{ publishGuideOptOut && (
				<PluginMoreMenuItem icon={ CheckBoxList } id="publish-guide-more-menu-item" onClick={ handleOptInPublishGuide }>
					{ __( 'Quick Start Guide', 'coblocks' ) }
				</PluginMoreMenuItem>
			) }
		</>
	);
};

export default PublishGuideMenuItem;
