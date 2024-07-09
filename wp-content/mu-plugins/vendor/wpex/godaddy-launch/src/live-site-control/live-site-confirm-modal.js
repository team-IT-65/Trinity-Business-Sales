/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';
import { Button, Modal } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';

/**
 * Internal dependencies.
 */
import { logImpressionEvent } from '../common/utils/instrumentation';
import RocketIcon from './illustration-rocket.png';
import { EID_PREFIX, EidWrapper } from '../common/components/eid-wrapper';

const LaunchLiveSiteConfirmModal = ( {
	isCaaSGenerated,
	isCompleted,
	onCloseModal,
	onCompleteStep,
} ) => {
	const [ isReviewConfirmed, setReviewConfirmed ] = useState( false );

	useEffect( () => {
		logImpressionEvent( `${ EID_PREFIX }.launch/modal.finish.impression` );
	}, [] );

	return (
		<Modal
			className="gdl-launch-site-confirm-modal godaddy-styles"
			isDismissible={ false }
		>
			{ ! isCompleted && (
				<div className="gdl-launch-site-confirm-modal__icon-container" >
					<img alt="" src={ RocketIcon } />
				</div>
			) }
			<p className="gdl-launch-site-confirm-modal__header">
				{
					isCaaSGenerated && isCompleted
						? __( 'Before you launch your site, review your content.', 'godaddy-launch' )
						: __( 'Ready to launch?', 'godaddy-launch' )
				}
			</p>
			<div className="gdl-launch-site-confirm-modal__list-container">
				{ ! isCaaSGenerated && (
					<p className="gdl-launch-site-confirm-modal__list-container__list-header">
						{ __( 'After your site is launched, it will be discoverable by search engines and available to the world.', 'godaddy-launch' ) }
					</p>
				) }
				{ isCaaSGenerated && (
					<>
						<p className="gdl-launch-site-confirm-modal__list-container__list-header">
							{
								isCompleted
									? __( 'Your site was built using AI generated content, and we want to make sure you’re happy with it before launching.', 'godaddy-launch' )
									: __( 'Your site will be discoverable by search engines and available to the world. We want to make sure you’re happy with the AI generated content on your site before you launch it.', 'godaddy-launch' )
							}
						</p>
						<p className="gdl-launch-site-confirm-modal__agree-checkbox">
							<label htmlFor="review-confirm">
								<input
									checked={ isReviewConfirmed }
									id="review-confirm"
									onClick={ () => setReviewConfirmed( ! isReviewConfirmed ) }
									type="checkbox"
								/>
								{ __( 'I have reviewed my content and am ready to launch.', 'godaddy-launch' ) }
							</label>
						</p>
					</>
				) }
			</div>
			<div className="gdl-launch-site-confirm-modal__cta-container">
				<EidWrapper
					action="click"
					section="launch/modal/finish/choices"
					target="yes"
				>
					<Button
						className="live-site-confirm-modal-success"
						disabled={ ( isCaaSGenerated && ! isReviewConfirmed ) ? true : false }
						onClick={ onCompleteStep }
						variant="primary"
					>
						{ __( 'Yes, launch my site', 'godaddy-launch' ) }
					</Button>
				</EidWrapper>
				<EidWrapper
					action="click"
					section="launch/modal/finish/choices"
					target="no"
				>
					<Button
						onClick={ onCloseModal }
						variant="secondary"
					>
						{ __( 'No, not yet', 'godaddy-launch' ) }
					</Button>
				</EidWrapper>
			</div>
		</Modal>
	);
};

export default LaunchLiveSiteConfirmModal;
