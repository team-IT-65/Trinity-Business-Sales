/* global gdlLiveSiteControlData */
/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import domReady from '@wordpress/dom-ready';
import { store as noticesStore } from '@wordpress/notices';
import {
	store as coreStore,
	useEntityProp,
} from '@wordpress/core-data';
import { createPortal, unmountComponentAtNode, useCallback, useEffect, useState } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { fetchWithRetry } from '../common/utils/fetchWithRetry';
import GoDaddyLaunchToolTip from '../common/components/gdl-tool-tip';
import LiveSiteConfirmModal from './live-site-confirm-modal';
import LiveSiteLaunchSuccessModal from './live-site-launch-success-modal';
import LiveSitePublishGuideButton from './live-site-publish-guide-button';
import LiveSitePublishGuideLaunchContinueButton from './live-site-publish-guide-launch-continue-button';
import MigratePublishGuideLaunchButton from './live-site-migrate-publish-guide-launch-button';
import { store as publishGuideStore } from '../publish-guide/store';
import './index.scss';

/**
 * To reset this plugin. Run this in the browser console:
 * wp.apiFetch( { path: '/wp/v2/settings', method: 'POST', data: { 'gdl_site_published': false } } );
 * wp.apiFetch( { path: '/wp/v2/settings', method: 'POST', data: { 'gdl_live_site_dismiss': false } } );
 *
 * You can also use the npm command: `npm run reset:live-site-control`
 */
const LiveSiteControl = () => {
	const { saveEditedEntityRecord } = useDispatch( coreStore );

	const {
		createErrorNotice,
		createSuccessNotice,
	} = useDispatch( noticesStore );

	const [ tooltip, setTooltip ] = useState( null );
	const [ displayedTooltips, setDisplayedTooltips ] = useState( [] );
	const [ currentStep, setCurrentStep ] = useState( 'choice' );

	const [ , setSitePublish ] = useEntityProp( 'root', 'site', gdlLiveSiteControlData.settings.publishState );
	const [ , setBlogPublic ] = useEntityProp( 'root', 'site', gdlLiveSiteControlData.settings.blogPublic );

	const htmlActiveBannerClassname = 'wp-admin-bar-gdl-live-site-banner-enabled';

	const { isCompleted } = useSelect( ( select ) => ( { isCompleted: select( publishGuideStore ).getGuideItemsComplete() } ), [] );
	const { activatePublishGuideFab, closePublishGuide } = useDispatch( 'godaddy-launch/publish-guide' );

	const closeTooltip = () => setTooltip( null );

	const handleSetTooltip = ( newTooltip ) => {
		if ( displayedTooltips.find( ( name ) => name === newTooltip ) ) {
			return null;
		}

		const activeTooltip = tooltips[ newTooltip ];

		if ( ! activeTooltip ) {
			return null;
		}

		const tooltipTargetElement = document.getElementById( activeTooltip.refID );

		setTooltip( {
			...activeTooltip,
			anchorRect: tooltipTargetElement.getBoundingClientRect(),
		} );
	};

	const { isWelcomeGuideActive } = useSelect( ( select ) => {
		if ( gdlLiveSiteControlData.page !== 'post-new.php' ) {
			return false;
		}
		const { isFeatureActive } = select( 'core/edit-post' );
		return {
			isWelcomeGuideActive: isFeatureActive( 'welcomeGuide' ),
		};
	}, [] );

	const tooltips = {
		launchLater: {
			autoHide: 10000,
			collapseSidePanel: false,
			description: __( 'When you\'re ready for visitors, open the menu here and find the "Launch my site" button.', 'godaddy-launch' ),
			refID: 'gdl-publish-guide-trigger-button',
			title: __( 'Go live for the world to see', 'godaddy-launch' ),
		},
	};

	/**
	 * When clicking on launch my site button we need to persist the modal even though isModalDismissed is true.
	 */
	const [ forceModalOpen, setForceModalOpen ] = useState( false );

	const removeGlobalEventListeners = useCallback( () => {
		window.removeEventListener( gdlLiveSiteControlData.eventName, launchWorkflow );
	}, [] );

	/**
	 * This is used to catch the event triggered by the admin toolbar button Go Live.
	 */
	useEffect( () => {
		window.addEventListener( gdlLiveSiteControlData.eventName, launchWorkflow );

		return removeGlobalEventListeners;
	}, [] );

	/**
	 * Add extra CSS if the 'not live' banner is displayed
	 */
	useEffect( () => {
		if ( document.getElementById( 'wp-admin-bar-gdl-live-site' ) ) {
			document.documentElement.classList.add( htmlActiveBannerClassname );
		}
	}, [] );

	const launchWorkflow = useCallback( () => {
		setCurrentStep( 'confirm' );
		setForceModalOpen( true );
	}, [] );

	const handlePublishSiteConfirm = () => {
		milestonePublishNow();

		// Remove admin bar notice.
		document.getElementById( 'wp-admin-bar-gdl-live-site' )?.remove();
		document.documentElement.classList.remove( htmlActiveBannerClassname );

		setCurrentStep( 'success' );
	};
	// Check if the URL has the query param to launch the workflow.
	const queryParams = new URLSearchParams( window.location.search );
	const actionParam = queryParams.get( 'gdl_action' );

	useEffect( () => {
		switch ( actionParam ) {
			case 'launch-now':
				launchWorkflow();
				break;
			case 'share-on-social':
				setCurrentStep( 'success' );
				setForceModalOpen( true );
				break;
		}
	}, [ actionParam ] );

	/**
	 * Milestone: Publish Now
	 *
	 * This function will publish the website (removes the comming soon page),
	 * sends the milestone event to the WPNUX API, and cleans up by saving
	 * additional options in the site.
	 */
	async function milestonePublishNow() {
		try {
			// Publish the site
			await setBlogPublic( true );
			await setSitePublish( true );
			await saveEditedEntityRecord( 'root', 'site' );

			// Send milestone event.
			await fetchWithRetry( { method: 'POST', path: 'gdl/v1/milestone/publish/' } );

			createSuccessNotice( __( 'Site published!', 'godaddy-launch' ), { type: 'snackbar' } );
		} catch ( error ) {
			const errorMessage =
				error.message && error.code !== 'unknown_error'
					? error.message
					: __( 'An error occurred while publishing the site', 'godaddy-launch' );

			createErrorNotice( errorMessage, { type: 'snackbar' } );
		}
	}

	const closeModal = useCallback( () => {
		setForceModalOpen( false );
		setTimeout( () => activatePublishGuideFab(), 500 );
	}, [] );

	// Timed delay to activate publish guide fab.
	useEffect( () => {
		setTimeout( () => activatePublishGuideFab(), 500 );
	}, [] );

	const workflow = {
		confirm: {
			Component: LiveSiteConfirmModal,
			props: {
				isCaaSGenerated: gdlLiveSiteControlData?.isCaaSGenerated === 'true',
				isCompleted,
				onCloseModal: () => {
					closeModal();

					setTimeout( () => {
						handleSetTooltip( 'launchLater' );
						setDisplayedTooltips( [ ...displayedTooltips, 'launchLater' ] );
					}, 500 );
				},
				onCompleteStep: handlePublishSiteConfirm,
			},
		},
		success: {
			Component: LiveSiteLaunchSuccessModal,
			props: {
				closeModal: () => {
					closeModal();
					setTimeout( () => {
						unmountComponentAtNode( document.getElementById( gdlLiveSiteControlData.appContainerClass ) );
						removeGlobalEventListeners();
					}, 500 );
				},
			},
		},
	};

	if ( isWelcomeGuideActive ) {
		return null;
	}

	const WorkflowStep = () => {
		if ( ! forceModalOpen ) {
			return null;
		}

		const currentWorkflowStep = workflow[ currentStep ];

		const DerivedComponent = currentWorkflowStep?.Component;
		const workflowProps = currentWorkflowStep?.props;

		return (
			<DerivedComponent { ...workflowProps } />
		);
	};

	const PublishGuideButton = () => {
		if ( gdlLiveSiteControlData.isMigratedSite ) {
			return (
				<MigratePublishGuideLaunchButton
					launchWorkflow={ () => {
						closePublishGuide();
						setForceModalOpen( true );
						handlePublishSiteConfirm();
					} }
				/>
			);
		}

		if ( isCompleted ) {
			return (
				<LiveSitePublishGuideLaunchContinueButton
					launchWorkflow={ () => {
						closePublishGuide();
						gdlLiveSiteControlData?.isCaaSGenerated === 'true' ? launchWorkflow() : milestonePublishNow(); // eslint-disable-line no-unused-expressions
					} }
				/>
			);
		}

		return (
			<LiveSitePublishGuideButton
				launchWorkflow={ () => {
					closePublishGuide();
					launchWorkflow();
				} }
			/>
		);
	};

	const RenderTooltip = () => {
		if ( ! tooltip ) {
			return null;
		}

		return createPortal(
			<GoDaddyLaunchToolTip
				closeCallback={ closeTooltip }
				tooltip={ tooltip } />,
			document.body
		);
	};

	return (
		<>
			{ WorkflowStep() }
			{ PublishGuideButton() }
			{ RenderTooltip() }
		</>
	);
};

domReady( () => {
	const rootElement = document.getElementById( gdlLiveSiteControlData.appContainerClass );

	if ( ! rootElement ) {
		return;
	}

	if ( gdlLiveSiteControlData.shouldUseReact18Syntax === 'true' ) {
		const { createRoot } = require( '@wordpress/element' );
		const root = createRoot( rootElement );

		root.render( <LiveSiteControl /> );
	} else {
		const { render } = require( '@wordpress/element' );

		render( <LiveSiteControl />, rootElement );
	}
} );
