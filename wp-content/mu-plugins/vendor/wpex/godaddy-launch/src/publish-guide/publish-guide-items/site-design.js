/* global gdlPublishGuideItems, gdvLinks, globalThis */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { store as noticesStore } from '@wordpress/notices';
import { useEntityProp } from '@wordpress/core-data';
import { useDispatch, useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import debouncedSaveEntityRecord from '../../common/utils/debouncedSaveEntityRecord';
import { logImpressionEvent } from '../../common/utils/instrumentation';
import PublishGuideItem from '../publish-guide-item';
import { useEffect } from '@wordpress/element';
import { EID_PREFIX, EidWrapper } from '../../common/components/eid-wrapper';

const SiteDesignGuide = ( props ) => {
	const {
		setTooltip,
		handleMilestoneStatusUpdate,
	} = props;

	const milestoneName = gdlPublishGuideItems.SiteDesign.milestoneName;
	const optionName = gdlPublishGuideItems.SiteDesign.propName;
	const [ isComplete = gdlPublishGuideItems.SiteDesign.default, setIsComplete ] = useEntityProp( 'root', 'site', optionName );

	const {	createErrorNotice } = useDispatch( noticesStore );
	/**
	 * This function marks the guide item as complete
	 *
	 * @param {boolean} newValue Represents the new value of the entity record / setting.
	 */
	const setIsCompleted = async ( newValue ) => {
		try {
			await setIsComplete( newValue );
			await debouncedSaveEntityRecord( optionName, newValue );
			await handleMilestoneStatusUpdate( milestoneName, newValue );
		} catch ( error ) {
			const errorMessage =
					error.message && error.code !== 'unknown_error'
						? error.message
						: __( 'An error occurred while saving Site Design guide item', 'godaddy-launch' );
			createErrorNotice( errorMessage, { type: 'snackbar' } );
		}
	};

	// Determine if the user modified the site design so we can trigger isCompleted appropriately.
	const {
		coblocksSiteDesign,
		selectedColors,
		selectedDesignStyle,
		selectedFonts,
	} = useSelect( ( select ) => ( {
		coblocksSiteDesign: globalThis?.siteDesign || {},
		selectedColors: select( 'coblocks/site-design' )?.getCurrentColors(),
		selectedDesignStyle: select( 'coblocks/site-design' )?.getDesignStyle(),
		selectedFonts: select( 'coblocks/site-design' )?.getSelectedFonts(),
	} ), [] );

	useEffect( () => {
		if (
			! coblocksSiteDesign.hasOwnProperty( 'currentColors' ) ||
			! coblocksSiteDesign.hasOwnProperty( 'currentDesignStyle' ) ||
			! coblocksSiteDesign.hasOwnProperty( 'currentFonts' )
		) {
			return;
		}

		const initialColors = Object.fromEntries( Object.entries( coblocksSiteDesign.currentColors ).filter( ( color ) => color[ 1 ] ) );
		const initialDesignStyle = coblocksSiteDesign.currentDesignStyle;
		const initialFonts = Object.entries( coblocksSiteDesign.currentFonts );

		if (
			( JSON.stringify( initialColors ) === JSON.stringify( selectedColors ) ) &&
			( JSON.stringify( initialDesignStyle ) === JSON.stringify( selectedDesignStyle ) ) &&
			( JSON.stringify( initialFonts ) === JSON.stringify( selectedFonts ) )
		) {
			return;
		}

		if ( ! isComplete ) {
			setIsCompleted( 'true' );
			logImpressionEvent( `${ EID_PREFIX }.guide/item/site_design.complete` );
		}
	}, [ selectedColors, selectedDesignStyle, selectedFonts ] );

	return (
		<PublishGuideItem
			isCompleted={ isComplete }
			name="site_design"
			skipAction={ () => setIsCompleted( 'skipped' ) }
			testId="site-design-container"
			text={ __( 'Change site colors and fonts to match your brand.', 'godaddy-launch' ) }
			title={ __( 'Customize styles', 'godaddy-launch' ) }
			{ ...props }
		>
			<EidWrapper
				action="click"
				section="guide/item/site_design"
				target="edit"
			>
				<Button
					className="publish-guide-popover__link"
					isLink
					onClick={ () => {
						if ( !! gdvLinks.editorRedirectUrl ) {
							window.location.assign( gdvLinks.editorRedirectUrl + '&tooltip=siteDesign&adminClick=true' );
						} else {
							setTooltip( 'siteDesign' );
						}
					} }
				>
					{ __( 'Make It Your Own', 'godaddy-launch' ) }
				</Button>
			</EidWrapper>
			<EidWrapper
				action="click"
				section="guide/item/site_design"
				target="skip"
			>
				<Button
					className="publish-guide-popover__link components-button is-link is-skip"
					onClick={ () => setIsCompleted( 'skipped' ) }
				>
					{ __( 'Skip', 'godaddy-launch' ) }
				</Button>
			</EidWrapper>
		</PublishGuideItem>
	);
};

export default SiteDesignGuide;
