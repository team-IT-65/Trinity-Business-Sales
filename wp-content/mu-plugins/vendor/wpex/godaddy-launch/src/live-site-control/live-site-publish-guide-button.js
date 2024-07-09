/* global gdlLiveSiteControlData */
/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { useEntityProp } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { createPortal, useEffect, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { EidWrapper } from '../common/components/eid-wrapper';

export default function LiveSitePublishGuideButton( { launchWorkflow } ) {
	const WPEX3590Active = gdlLiveSiteControlData.WPEX_3590_active;

	const [ publishGuideTriggerNode, setPublishGuideTriggerNode ] = useState( null );

	const [ sitePublish ] = useEntityProp( 'root', 'site', gdlLiveSiteControlData.settings.publishState );
	const [ blogPublic ] = useEntityProp( 'root', 'site', gdlLiveSiteControlData.settings.blogPublic );

	const {
		isPublishGuideFabActive,
	} = useSelect( ( select ) => ( {
		isPublishGuideFabActive: select( 'godaddy-launch/publish-guide' ).isPublishGuideFabActive(),
	} ), [] );

	const classSelector = '.publish-guide-popover__footer';

	useEffect( () => {
		let newPublishGuideTrigger = document.querySelector( classSelector );

		setTimeout( () => {
			newPublishGuideTrigger = document.querySelector( classSelector );
			setPublishGuideTriggerNode( newPublishGuideTrigger );
		} );
	}, [ isPublishGuideFabActive ] );

	if ( ! publishGuideTriggerNode ) {
		return null;
	}

	return ( ! sitePublish && ! blogPublic ) && createPortal(
		<div className="wrap gdl-live-site-button__container">
			<EidWrapper
				action="click"
				section={ WPEX3590Active ? 'WPEX_3590_guide' : 'guide' }
				target="launch"
			>
				<Button
					isPrimary
					onClick={ launchWorkflow }
				>
					{ __( 'Launch My Site', 'godaddy-launch' ) }
				</Button>
			</EidWrapper>
		</div>,
		publishGuideTriggerNode
	);
}
