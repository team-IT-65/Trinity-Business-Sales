/* global gdlPublishGuideItems, gdvLinks */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { store as noticesStore } from '@wordpress/notices';
import { useDispatch } from '@wordpress/data';
import { useEntityProp } from '@wordpress/core-data';

/**
 * Internal dependencies
 */
import debouncedSaveEntityRecord from '../../common/utils/debouncedSaveEntityRecord';
import { EidWrapper } from '../../common/components/eid-wrapper';
import PublishGuideItem from '../publish-guide-item';

const AddDomainGuide = ( props ) => {
	const { handleMilestoneStatusUpdate } = props;

	const changeDomainUrl = gdvLinks?.changeDomain.replace( '{{DOMAIN}}', window.location.hostname );

	// Used when changing the domain of the site.
	const milestoneName = gdlPublishGuideItems.AddDomain.milestoneName;
	const optionName = gdlPublishGuideItems.AddDomain.propName;
	const [ addDomainComplete = gdlPublishGuideItems.AddDomain.default, setAddDomainComplete ] = useEntityProp( 'root', 'site', optionName );

	const {	createErrorNotice } = useDispatch( noticesStore );
	/**
	 * This function marks the guide item as complete
	 *
	 * @param {boolean} newValue Represents the new value of the entity record / setting.
	 */
	const setIsCompleted = async ( newValue ) => {
		try {
			await setAddDomainComplete( newValue );
			await debouncedSaveEntityRecord( optionName, newValue );
			await handleMilestoneStatusUpdate( milestoneName, newValue );
		} catch ( error ) {
			const errorMessage =
					error.message && error.code !== 'unknown_error'
						? error.message
						: __( 'An error occurred while saving Site Domain guide item', 'godaddy-launch' );
			createErrorNotice( errorMessage, { type: 'snackbar' } );
		}
	};

	return (
		<PublishGuideItem
			isCompleted={ addDomainComplete }
			name="add_domain"
			skipAction={ () => setIsCompleted( 'skipped' ) }
			text={ __( 'Look more professional and make it easier for customers to find your website.', 'godaddy-launch' ) }
			title={ __( 'Add custom domain', 'godaddy-launch' ) }
			{ ...props }
		>
			{ ( ! addDomainComplete ) && ( <>
				<EidWrapper
					action="click"
					section="guide/item/add_domain"
					target="edit"
				>
					<Button
						className="publish-guide-popover__link components-button is-link"
						onClick={ () => window.open( changeDomainUrl, '_self' ) }
					>
						{ __( 'Add domain', 'godaddy-launch' ) }
					</Button>

				</EidWrapper>
				<EidWrapper
					action="click"
					section="guide/item/add_domain"
					target="skip"
				>
					<Button
						className="publish-guide-popover__link components-button is-link is-skip"
						onClick={ () => setIsCompleted( 'skipped' ) }
					>
						{ __( 'Skip', 'godaddy-launch' ) }
					</Button>
				</EidWrapper>
			</> ) }
		</PublishGuideItem>
	);
};

export default AddDomainGuide;
