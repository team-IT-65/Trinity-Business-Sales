/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { createPortal, useEffect, useState } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { EidWrapper } from '../common/components/eid-wrapper';

export default function LiveSiteDashboardLaunchContinueButton( { launchWorkflow } ) {
	const [ publishGuideTriggerNode, setPublishGuideTriggerNode ] = useState( null );

	const {
		isPublishGuideFabActive,
	} = useSelect( ( select ) => ( {
		isPublishGuideFabActive: select( 'godaddy-launch/publish-guide' ).isPublishGuideFabActive(),
	} ), [] );

	const {	closePublishGuide } = useDispatch( 'godaddy-launch/publish-guide' );

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
			<EidWrapper
				action="click"
				section="guide"
				target="launch"
			>
				<Button
					isPrimary
					onClick={ launchWorkflow }
				>
					{ __( 'Yes! Launch My Site', 'godaddy-launch' ) }
				</Button>
			</EidWrapper>
			<EidWrapper
				action="click"
				section="guide"
				target="no"
			>
				<Button
					className="publish-guide-continue-button"
					onClick={ closePublishGuide }
					variant="tertiary"
				>
					{ __( 'Continue Editing', 'godaddy-launch' ) }
				</Button>
			</EidWrapper>
		</div>,
		publishGuideTriggerNode
	);
}

