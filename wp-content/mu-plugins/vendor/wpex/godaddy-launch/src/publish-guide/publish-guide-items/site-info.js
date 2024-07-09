/* global gdlPublishGuideItems */

/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { compose } from '@wordpress/compose';
import { decodeEntities } from '@wordpress/html-entities';
import { store as noticesStore } from '@wordpress/notices';
import { useEntityProp } from '@wordpress/core-data';
import { useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { Button, Modal, TextControl, withNotices } from '@wordpress/components';
import { dispatch, useDispatch, useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import debouncedSaveEntityRecord from '../../common/utils/debouncedSaveEntityRecord';
import { EidWrapper } from '../../common/components/eid-wrapper';
import PublishGuideItem from '../publish-guide-item';

export const SiteInfoGuide = ( props ) => {
	const { handleMilestoneStatusUpdate } = props;

	const [ modalOpen, setModalOpen ] = useState( false );

	const milestoneName = gdlPublishGuideItems.SiteInfo.milestoneName;
	const optionName = gdlPublishGuideItems.SiteInfo.propName;
	const [ siteInfoComplete = gdlPublishGuideItems.SiteInfo.default, setSiteInfoComplete ] = useEntityProp( 'root', 'site', optionName );

	const {	createErrorNotice } = useDispatch( noticesStore );

	/**
	 * This function marks the guide item as complete
	 *
	 * @param {boolean} newValue Represents the new value of the entity record / setting.
	 */
	const setIsCompleted = async ( newValue ) => {
		try {
			await setSiteInfoComplete( newValue );
			await debouncedSaveEntityRecord( optionName, newValue );
			await handleMilestoneStatusUpdate( milestoneName, newValue );
		} catch ( error ) {
			const errorMessage =
				error.message && error.code !== 'unknown_error'
					? error.message
					: __( 'An error occurred while saving Site Info guide item', 'godaddy-launch' );
			createErrorNotice( errorMessage, { type: 'snackbar' } );
		}
	};

	const closeModal = () => setModalOpen( false );

	return (
		<PublishGuideItem
			isCompleted={ siteInfoComplete }
			name="site_info"
			skipAction={ () => setIsCompleted( 'skipped' ) }
			text={ __( 'Customize your site title to help users identify your business.', 'godaddy-launch' ) }
			title={ __( 'Add site details', 'godaddy-launch' ) }
			{ ...props }
		>
			<EidWrapper
				action="click"
				section="guide/item/site_info"
				target="edit"
			>
				<Button
					className="publish-guide-popover__link"
					isLink
					onClick={ () => setModalOpen( true ) }
				>
					{ __( 'Edit Site Title', 'godaddy-launch' ) }
				</Button>
			</EidWrapper>
			<EidWrapper
				action="click"
				section="guide/item/site_info"
				target="skip"
			>
				<Button
					className="publish-guide-popover__link components-button is-link is-skip"
					onClick={ () => setIsCompleted( 'skipped' ) }
				>
					{ __( 'Skip', 'godaddy-launch' ) }
				</Button>
			</EidWrapper>
			{ modalOpen && <SiteInfoModal closeModal={ closeModal } setIsCompleted={ setIsCompleted } { ...props } /> }
		</PublishGuideItem>
	);
};

const SiteInfoModal = ( { closeModal, setIsCompleted } ) => {
	const [ title ] = useEntityProp( 'root', 'site', 'title' );
	const [ description ] = useEntityProp( 'root', 'site', 'description' );

	const [ newTitle, setNewTitle ] = useState( title );
	const [ newDescription, setNewDescription ] = useState( description );
	const [ isSaving, setIsSaving ] = useState( false );

	// Save the new settings.
	const saveSettings = () => {
		return dispatch( 'core' ).saveEntityRecord( 'root', 'site', {
			description: newDescription,
			title: newTitle,
		} );
	};

	const { url } = useSelect( ( select ) => {
		return {
			url: select( 'core' ).getSite()?.url,
		};
	} );

	return (
		<Modal
			className="publish-guide-popover__modal godaddy-styles"
			isOpen={ true }
			onRequestClose={ closeModal }
			title={ __( 'Add your site info', 'godaddy-launch' ) }
		>
			<>
				<p>
					{ __( 'Adding your site\'s name and a quick description is helpful not only for Google and other search engines to find you but also for potential customers to know if this is the right place for them.', 'godaddy-launch' ) }
				</p>
				<div className="publish-guide-site-info__content">
					<div className="publish-guide-site-info__section form-section">
						<EidWrapper
							action="click"
							section="guide/item/site_info"
							target="input_title"
						>
							<TextControl
								label={ __( 'What\'s the name of your site?', 'godaddy-launch' ) }
								onChange={ ( newValue ) => setNewTitle( newValue ) }
								value={ decodeEntities( newTitle ) }
							/>
						</EidWrapper>
						<EidWrapper
							action="click"
							section="guide/item/site_info"
							target="input_description"
						>
							<TextControl
								label={ __( 'Describe in one sentence what you do.', 'godaddy-launch' ) }
								onChange={ ( newValue ) => setNewDescription( newValue ) }
								value={ newDescription }
							/>
						</EidWrapper>
					</div>
					<div className="publish-guide-site-info__section preview-section">
						<div className="preview-info">
							<p className="preview-info__title"><strong>{ decodeEntities( newTitle ) }</strong></p>
							<p className="preview-info__site-url">{ url }</p>
							<p>{ newDescription }</p>
						</div>
						<p className="preview-subtext" dangerouslySetInnerHTML={ { __html: (
							sprintf(
								// translators: %s: what-is-a-domain-name link.
								__(
									'Note you can change the %s in a different step',
									'godaddy-launch',
								),
								`<a target="_blank" href="https://www.godaddy.com/resources/skills/what-is-a-domain-name">${ __( 'domain name', 'godaddy-launch' ) }</a>`
							)
						) } } />
					</div>
				</div>
				<div className="publish-guide-site-info__action">
					<EidWrapper
						action="click"
						section="guide/item/site_info"
						target="close"
					>
						<Button
							isSecondary
							onClick={ closeModal }
						>
							{ __( 'Back', 'godaddy-launch' ) }
						</Button>
					</EidWrapper>
					<EidWrapper
						action="click"
						section="guide/item/site_info"
						target="save"
					>
						<Button
							className={ classnames(
								'publish-guide-popover__button',
								{ 'is-busy': isSaving }
							) }
							disabled={ isSaving }
							isPrimary
							onClick={ () => {
								setIsSaving( true );
								saveSettings().then( () => {
									setIsSaving( false );
									setIsCompleted( 'true' );
									closeModal();
								} );
							} }
						>
							{ __( 'Done', 'godaddy-launch' ) }
						</Button>
					</EidWrapper>
				</div>
			</>
		</Modal>
	);
};

export default compose( [
	withNotices,
] )( SiteInfoGuide );
