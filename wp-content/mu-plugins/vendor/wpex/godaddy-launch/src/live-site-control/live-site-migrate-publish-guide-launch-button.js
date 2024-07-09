/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { createPortal, useEffect, useState } from '@wordpress/element';

export default function MigratePublishGuideLaunchButton( { launchWorkflow } ) {
	const [ publishGuideTriggerNode, setPublishGuideTriggerNode ] = useState( null );

	const {
		isPublishGuideFabActive,
	} = useSelect( ( select ) => ( {
		isPublishGuideFabActive: select( 'godaddy-launch/publish-guide' ).isPublishGuideFabActive(),
	} ), [] );

	const classSelector = '.publish-guide-popover__footer';

	useEffect( () => {
		let newPublishGuideTrigger = document.querySelector( classSelector );

		if ( ! newPublishGuideTrigger ) {
			setTimeout( () => {
				newPublishGuideTrigger = document.querySelector( classSelector );
				setPublishGuideTriggerNode( newPublishGuideTrigger );
			} );
		} else {
			setPublishGuideTriggerNode( newPublishGuideTrigger );
		}
	}, [ isPublishGuideFabActive ] );

	if ( ! publishGuideTriggerNode ) {
		return null;
	}

	return createPortal(
		<div className="postbox wrap gdl-live-site-button__container">
			<Button
				isPrimary
				onClick={ launchWorkflow }
			>
				{ __( 'Launch My Site', 'godaddy-launch' ) }
			</Button>
		</div>,
		publishGuideTriggerNode
	);
}
