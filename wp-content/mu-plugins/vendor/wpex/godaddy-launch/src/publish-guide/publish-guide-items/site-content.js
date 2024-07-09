/* global gdlPublishGuideItems, gdvLinks */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { store as noticesStore } from '@wordpress/notices';
import { useDispatch } from '@wordpress/data';
import { useEffect } from '@wordpress/element';
import { useEntityProp } from '@wordpress/core-data';

/**
 * Internal dependencies
 */
import debouncedSaveEntityRecord from '../../common/utils/debouncedSaveEntityRecord';
import { logImpressionEvent } from '../../common/utils/instrumentation';
import PublishGuideItem from '../publish-guide-item';
import { EID_PREFIX, EidWrapper } from '../../common/components/eid-wrapper';

const SiteContentGuide = ( props ) => {
	const {
		setTooltip,
		handleMilestoneStatusUpdate,
	} = props;

	const milestoneName = gdlPublishGuideItems.SiteContent.milestoneName;
	const optionName = gdlPublishGuideItems.SiteContent.propName;
	const [ siteContentComplete = gdlPublishGuideItems.SiteContent.default, setSiteContentComplete ] = useEntityProp( 'root', 'site', optionName );

	const {	createErrorNotice } = useDispatch( noticesStore );
	/**
	 * This function marks the guide item as complete
	 *
	 * @param {boolean} newValue Represents the new value of the entity record / setting.
	 */
	const setIsCompleted = async ( newValue ) => {
		try {
			await setSiteContentComplete( newValue );
			await debouncedSaveEntityRecord( optionName, newValue );
			await handleMilestoneStatusUpdate( milestoneName, newValue );
		} catch ( error ) {
			const errorMessage =
					error.message && error.code !== 'unknown_error'
						? error.message
						: __( 'An error occurred while saving Site Content guide item', 'godaddy-launch' );
			createErrorNotice( errorMessage, { type: 'snackbar' } );
		}
	};

	useEffect( () => {
		if ( !! siteContentComplete ) {
			const siteContentString = 'siteContentEvent';
			if ( ! localStorage.getItem( siteContentString ) ) {
				localStorage.setItem( siteContentString, '1' );
				logImpressionEvent( `${ EID_PREFIX }.guide/item/site_content.complete` );
			}
		}
	}, [ siteContentComplete ] );

	return (
		<PublishGuideItem
			isCompleted={ siteContentComplete }
			name="site_content"
			skipAction={ () => setIsCompleted( 'skipped' ) }
			testId="site-content-container"
			text={ __( 'Help visitors get to know you and and how they can reach you.', 'godaddy-launch' ) }
			title={ __( 'Add a new page', 'godaddy-launch' ) }
			{ ...props }
		>
			<EidWrapper
				action="click"
				section="guide/item/site_content"
				target="edit"
			>
				<Button
					className="publish-guide-popover__link"
					isLink
					onClick={ () => {
						if ( !! gdvLinks.editorRedirectUrl ) {
							window.location.assign( gdvLinks.editorRedirectUrl + '&tooltip=siteContent' );
						} else {
							setTooltip( 'siteContent' );
						}
					} }
				>
					{ __( 'Get Started', 'godaddy-launch' ) }
				</Button>

			</EidWrapper>
			<EidWrapper
				action="click"
				section="guide/item/site_content"
				target="skip"
			>
				<Button
					className="publish-guide-popover__link is-skip"
					isLink
					onClick={ () => setIsCompleted( 'skipped' ) }
				>
					{ __( 'Skip', 'godaddy-launch' ) }
				</Button>
			</EidWrapper>
		</PublishGuideItem>
	);
};

export default SiteContentGuide;
