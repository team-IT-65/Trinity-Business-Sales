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

const SEOGuide = ( props ) => {
	const {
		setTooltip,
		handleMilestoneStatusUpdate,
	} = props;

	const milestoneName = gdlPublishGuideItems.SEO.milestoneName;
	const optionName = gdlPublishGuideItems.SEO.propName;
	const [ siteSEOComplete = gdlPublishGuideItems.SEO.default, setSiteSEOComplete ] = useEntityProp( 'root', 'site', optionName );

	const {	createErrorNotice } = useDispatch( noticesStore );
	/**
	 * This function marks the guide item as complete
	 *
	 * @param {boolean} newValue Represents the new value of the entity record / setting.
	 */
	const setIsCompleted = async ( newValue ) => {
		try {
			await setSiteSEOComplete( newValue );
			await debouncedSaveEntityRecord( optionName, newValue );
			await handleMilestoneStatusUpdate( milestoneName, newValue );
		} catch ( error ) {
			const errorMessage =
					error.message && error.code !== 'unknown_error'
						? error.message
						: __( 'An error occurred while saving SEO guide item', 'godaddy-launch' );
			createErrorNotice( errorMessage, { type: 'snackbar' } );
		}
	};

	useEffect( () => {
		if ( !! siteSEOComplete ) {
			const siteSEOString = 'siteSEOEvent';
			if ( ! localStorage.getItem( siteSEOString ) ) {
				localStorage.setItem( siteSEOString, '1' );
				logImpressionEvent( `${ EID_PREFIX }.guide/item/seo.complete` );
			}
		}
	}, [ siteSEOComplete ] );

	return (
		<PublishGuideItem
			isCompleted={ siteSEOComplete }
			name="seo"
			skipAction={ () => setIsCompleted( 'skipped' ) }
			testId="seo-container"
			text={ __( "Let's make sure your site shows up on Google and other search engines.", 'godaddy-launch' ) }
			title={ __( 'Optimize SEO', 'godaddy-launch' ) }
			{ ...props }
		>
			<EidWrapper
				action="click"
				section="guide/item/seo"
				target="edit"
			>
				<Button
					className="publish-guide-popover__link"
					isLink
					onClick={ () => {
						if ( !! gdvLinks.seoRedirectUrl ) {
							window.location.assign( gdvLinks.seoRedirectUrl );
						} else {
							setTooltip( 'SEO' );
						}
					} }
				>
					{ __( 'Get Started', 'godaddy-launch' ) }
				</Button>

			</EidWrapper>
			<EidWrapper
				action="click"
				section="guide/item/seo"
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

export default SEOGuide;
