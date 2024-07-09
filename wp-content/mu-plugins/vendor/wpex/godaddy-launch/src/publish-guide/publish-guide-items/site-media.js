/* global gdlPublishGuideItems */

/**
 * External dependencies
 */
import { defaultTo } from 'lodash';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { compose } from '@wordpress/compose';
import { mediaUpload } from '@wordpress/editor';
import { store as noticesStore } from '@wordpress/notices';
import { useEntityProp } from '@wordpress/core-data';
import { Button, withNotices } from '@wordpress/components';
import { dispatch, useDispatch, useSelect } from '@wordpress/data';
import { Fragment, useEffect, useState } from '@wordpress/element';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import debouncedSaveEntityRecord from '../../common/utils/debouncedSaveEntityRecord';
import { EidWrapper } from '../../common/components/eid-wrapper';
import PublishGuideItem from '../publish-guide-item';

/**
 * Module constants
 */
const ALLOWED_MEDIA_TYPES = [ 'image' ];

const SiteMediaGuide = ( props ) => {
	const { handleMilestoneStatusUpdate } = props;

	// In the case we are outside a block editor screen, we need to update the state with expected properties for the media upload permission.
	const {
		hasUploadPermissions,
		getSettings,
	} = useSelect( ( select ) => {
		return {
			getSettings: select( 'core/block-editor' ).getSettings,
			hasUploadPermissions: defaultTo(
				select( 'core' ).canUser( 'create', 'media' ),
				true
			),
		};
	} );
	const { updateSettings } = useDispatch( 'core/block-editor' );
	useEffect( () => {
		const settings = getSettings();
		updateSettings( {
			...settings,
			hasUploadPermissions,
			mediaUpload,
		} );
	}, [] );

	const milestoneName = gdlPublishGuideItems.SiteMedia.milestoneName;
	const optionName = gdlPublishGuideItems.SiteMedia.propName;
	const [ isComplete = gdlPublishGuideItems.SiteMedia.default, setIsComplete ] = useEntityProp( 'root', 'site', optionName );

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
						: __( 'An error occurred while saving Site Media guide item', 'godaddy-launch' );
			createErrorNotice( errorMessage, { type: 'snackbar' } );
		}
	};

	const [ logoUrl, setLogoUrl ] = useState();

	/* istanbul ignore next */
	const { mediaItemData, sitelogo } = useSelect( ( select ) => {
		const siteSettings = select( 'core' ).getEditedEntityRecord( 'root', 'site' );
		const mediaItem = select( 'core' ).getEntityRecord( 'root', 'media', siteSettings.sitelogo );

		return {
			mediaItemData: mediaItem && {
				alt: mediaItem.alt_text,
				url: mediaItem.source_url,
			},
			sitelogo: siteSettings.sitelogo,
		};
	}, [] );

	const { editEntityRecord } = useDispatch( 'core' );

	const setLogo = ( newValue ) =>
		editEntityRecord( 'root', 'site', undefined, {
			sitelogo: newValue,
		} );

	const onSelectLogo = ( media ) => {
		if ( ! media ) {
			return;
		}

		if ( ! media.id && media.url ) {
			setLogo( '' );
			setLogoUrl( media.url );
		}

		if ( media.id ) {
			dispatch( 'core' ).saveEntityRecord( 'root', 'site', {
				sitelogo: media.id.toString() ?? '',
			} );
			setIsCompleted( 'true' );
		}
	};

	// Set the image URL once retrieved.
	if ( mediaItemData && logoUrl !== mediaItemData.url ) {
		setLogoUrl( mediaItemData.url );
	}

	return (
		<PublishGuideItem
			isCompleted={ isComplete }
			name="site_media"
			skipAction={ () => setIsCompleted( 'skipped' ) }
			text={ __( 'Build greater brand awareness and trust with customers.', 'godaddy-launch' ) }
			title={ __( 'Upload your logo', 'godaddy-launch' ) }
			{ ...props }
		>
			<MediaUploadCheck>
				<MediaUpload
					allowedTypes={ ALLOWED_MEDIA_TYPES }
					onSelect={ onSelectLogo }
					render={ ( { open } ) => (
						<Fragment>
							<EidWrapper
								action="click"
								section="guide/item/site_media"
								target="edit"
							>
								<Button
									className="publish-guide-popover__link"
									isLink
									onClick={ open }
								>
									{ __( 'Add Logo', 'godaddy-launch' ) }
								</Button>
							</EidWrapper>
							<EidWrapper
								action="click"
								section="guide/item/site_media"
								target="skip"
							>
								<Button
									className="publish-guide-popover__link components-button is-link is-skip"
									onClick={ () => setIsCompleted( 'skipped' ) }
								>
									{ __( 'Skip', 'godaddy-launch' ) }
								</Button>
							</EidWrapper>
						</Fragment>
					) }
					title={ __( 'Upload site logo', 'godaddy-launch' ) }
					value={ sitelogo }
				/>
			</MediaUploadCheck>
		</PublishGuideItem>
	);
};

export default compose( [
	withNotices,
] )( SiteMediaGuide );
