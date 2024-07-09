/* global gdvPublishGuideDefaults, gdlPublishGuideItems, gdlLiveSiteControlData */

/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';
import { store as noticesStore } from '@wordpress/notices';
import { registerPlugin } from '@wordpress/plugins';
import { useEntityProp } from '@wordpress/core-data';
import { __, sprintf } from '@wordpress/i18n';
import { createPortal, Fragment, useEffect, useState } from '@wordpress/element';
import { dispatch, useDispatch, useSelect } from '@wordpress/data';
import { Popover, SlotFillProvider } from '@wordpress/components';
import { BrowserRouter as Router, useSearchParams } from 'react-router-dom';

/**
 * Internal dependencies
 */
import AddDomainGuide from './publish-guide-items/add-domain';
import Animated from './components/animated';
import Confetti from '../common/components/confetti';
import { fetchWithRetry } from '../common/utils/fetchWithRetry';
import GoDaddyLaunchToolTip from '../common/components/gdl-tool-tip';
import MigratePublishGuide from './migrate-publish-guide';
import PublishGuideButton from './publish-guide-button';
import PublishGuideList from './publish-guide-list';
import PublishGuideMenuItem from './publish-guide-menu-item';
import PublishGuideMenuItemTooltip from './publish-guide-menu-item-tooltip';
import PublishGuideProgress from './publish-guide-progress';
import { store as publishGuideStore } from './store';
import SEOGuide from './publish-guide-items/seo';
import SiteContentGuide from './publish-guide-items/site-content';
import SiteDesignGuide from './publish-guide-items/site-design';
import SiteInfoGuide from './publish-guide-items/site-info';
import SiteMediaGuide from './publish-guide-items/site-media';
import './hooks.js';
import './index.scss';

import { logInteractionEvent } from '../common/utils/instrumentation';
import { EID_PREFIX, EidWrapper } from '../common/components/eid-wrapper';

import GoDaddyLogo from './icons/godaddy-logo';

const wpUserData = ( typeof gdvPublishGuideDefaults === 'undefined' ) ?? JSON.parse( window.localStorage.getItem( 'WP_DATA_USER_' + gdvPublishGuideDefaults.userId ) );

// Safe reference to object needed for useEntityProp call.
const gdlLiveSiteControl = typeof gdlLiveSiteControlData !== 'undefined' ? gdlLiveSiteControlData : {};

export function PublishGuide() {
	const wpex3590active = gdlLiveSiteControlData.WPEX_3590_active;

	let tooltipTargetElement;
	let tooltipAutoHideTimeout;

	const [ searchParams ] = useSearchParams();
	const [ isMwcDialogOpened, setIsMwcDialogOpened ] = useState( false );

	useEffect( () => {
		const handleQueryParamChange = () => {
			const params = new URL( document.location ).searchParams;
			setIsMwcDialogOpened( params.get( 'isMwcDialogOpened' ) );
		};

		handleQueryParamChange();

		const interval = setInterval( handleQueryParamChange, 500 );

		return () => {
			clearInterval( interval );
		};
	}, [ searchParams ] );

	const tooltips = {
		publishGuideHere: {
			autoHide: 10000,
			collapseSidePanel: false,
			description: __( 'Once you\'re done selecting your site style and colors, click here to continue publishing your site.', 'godaddy-launch' ),
			refID: 'gdl-publish-guide-trigger-button',
			title: __( 'Getting Started Guide', 'godaddy-launch' ),
		},
		siteContent: {
			autoHide: false,
			collapseSidePanel: true,
			description: __( 'This button will open up your page manager.', 'godaddy-launch' ),
			refID: 'coblocks-menu-icon-site-content',
			title: __( 'Open the site content tab', 'godaddy-launch' ),
		},
		siteDesign: {
			autoHide: false,
			collapseSidePanel: true,
			description: __( 'If you want to update the feel of a site and bring in your own brand, this is a good place to start.', 'godaddy-launch' ),
			refID: 'coblocks-menu-icon-site-design',
			title: __( 'Change any global styles here', 'godaddy-launch' ),
		},
	};

	const {
		isCompleted,
		guideItems,
		isPublishGuideFabActive,
		isPublishGuideOpened,
		stepsCompleted,
	} = useSelect( ( select ) => ( {
		guideItems: select( publishGuideStore ).getGuideItems(),
		isCompleted: select( publishGuideStore ).getGuideItemsComplete(),
		isPublishGuideFabActive: select( publishGuideStore ).isPublishGuideFabActive(),
		isPublishGuideOpened: select( publishGuideStore ).isPublishGuideOpened(),
		stepsCompleted: select( publishGuideStore ).getGuideItems().filter( ( item ) => item.props.hasCompleted ).length,
	} ), [] );

	const {
		activatePublishGuideFab,
		closePublishGuide,
		setGuideItems,
		togglePublishGuide,
	} = useDispatch( publishGuideStore );

	const { saveEditedEntityRecord } = useDispatch( 'core' );

	const [ selectedItem, setSelectedItem ] = useState( null );
	const [ tooltip, setTooltip ] = useState( null );

	// Determine when the FAB welcome tooltip should show.
	const [ showWelcomeTooltip, setShowWelcomeTooltip ] = useState( false );

	useEffect( () => {
		if ( ( isPublishGuideFabActive ) || typeof gdlLiveSiteControlData === 'undefined' ) {
			setTimeout( () => setShowWelcomeTooltip( true ), 500 );
		}
	}, [ isPublishGuideFabActive ] );

	useEffect(
		() => {
			if ( isPublishGuideOpened ) {
				setShowWelcomeTooltip( false );
			}
		},
		[ isPublishGuideOpened ]
	);

	const editPostDispatch = useDispatch( 'core/edit-post' );

	const { isWelcomeGuideActive } = useSelect( ( select ) => {
		return {
			isWelcomeGuideActive: select( 'core/edit-post' )?.isFeatureActive( 'welcomeGuide' ),
		};
	}, [] );

	// TODO: refactor global completion counting.
	const [ addDomainComplete = gdlPublishGuideItems.AddDomain?.default ] = useEntityProp( 'root', 'site', gdlPublishGuideItems.AddDomain?.propName );
	const [ siteContentComplete = gdlPublishGuideItems.SiteContent?.default ] = useEntityProp( 'root', 'site', gdlPublishGuideItems.SiteContent?.propName );
	const [ siteSEOComplete = gdlPublishGuideItems.SEO?.default ] = useEntityProp( 'root', 'site', gdlPublishGuideItems.SEO?.propName );
	const [ siteDesignComplete = gdlPublishGuideItems.SiteDesign?.default ] = useEntityProp( 'root', 'site', gdlPublishGuideItems.SiteDesign?.propName );
	const [ siteInfoComplete = gdlPublishGuideItems.SiteInfo?.default ] = useEntityProp( 'root', 'site', gdlPublishGuideItems.SiteInfo?.propName );
	const [ siteMediaComplete = gdlPublishGuideItems.SiteMedia?.default ] = useEntityProp( 'root', 'site', gdlPublishGuideItems.SiteMedia?.propName );

	// Has the publish guide menu ever been opened?
	const [ publishGuideInteracted = false, setPublishGuideInteracted ] = useEntityProp( 'root', 'site', gdvPublishGuideDefaults.optionInteracted );

	const [
		publishGuideOptOut = !! gdvPublishGuideDefaults.isOptOut,
		setPublishGuideOptOut,
	] = useEntityProp( 'root', 'site', gdvPublishGuideDefaults.optionOptOut );

	const handleOptOutPublishGuide = () => {
		const moreMenuIcon = document.querySelector( '.interface-more-menu-dropdown > button' );

		if ( moreMenuIcon ) {
			moreMenuIcon.click();
		}

		closePublishGuide();
		setPublishGuideOptOut( true );
		saveEditedEntityRecord( 'root', 'site' );

		registerPlugin( 'gdl-publish-menu-item-tooltip', {
			icon: null,
			render: PublishGuideMenuItemTooltip,
		} );
	};

	const setPublishGuideMenuOpened = () => {
		setPublishGuideInteracted( true );
		dispatch( 'core' ).saveEntityRecord( 'root', 'site', { [ gdvPublishGuideDefaults.optionInteracted ]: true } );
	};

	const [
		// If gdlLiveSiteControlData is undefined, the site has published because we do not localize the data otherwise.
		sitePublished = ( typeof gdlLiveSiteControlData === 'undefined' ),
	] = useEntityProp( 'root', 'site', gdlLiveSiteControl?.settings?.publishState );
	// Seemingly because of the way that useEntityProp requires separate saving of the entities using saveEditedEntityRecord
	// there are situations where the sitePublished value will inexplicably be false despite the site being launched.
	const [ siteLaunched, setSiteLaunched ] = useState( false );

	const { removeNotice, createNotice } = useDispatch( noticesStore );
	useEffect( () => {
		if ( ! sitePublished && ! siteLaunched ) {
			createNotice( 'warning gdl-notice',
				__( 'Your site is not launched yet. Visitors will see a temporary Coming Soon page until you launch your site.' ),
				{ actions: [
					{
						className: 'is-link',
						label: __( 'Launch My Site' ),
						onClick: () => {
							logInteractionEvent( { eid: `${ EID_PREFIX }.notice.launch` } );
							window.dispatchEvent( new Event( gdlLiveSiteControl.eventName ) );
						},
					},
				], id: 'gdl-launch-notice' }
			);
		}
		if ( sitePublished ) {
			removeNotice( 'gdl-launch-notice' );
			setSiteLaunched( true );
		}
	}, [ sitePublished ] );

	useEffect( () => {
		// Hide the welcomeGuide if no localStorage is present (normally on first load).
		if ( ! wpUserData && isWelcomeGuideActive && editPostDispatch?.toggleFeature ) {
			editPostDispatch?.toggleFeature( 'welcomeGuide' );
		}

		setGuideItems(
			[
				!! gdlPublishGuideItems.SiteInfo?.enabled && <SiteInfoGuide handleMilestoneStatusUpdate={ handleMilestoneStatusUpdate } hasCompleted={ !! siteInfoComplete } key="SiteInfoGuide" />,
				!! gdlPublishGuideItems.SiteMedia?.enabled && <SiteMediaGuide handleMilestoneStatusUpdate={ handleMilestoneStatusUpdate } hasCompleted={ !! siteMediaComplete } key="SiteMediaGuide" />,
				!! gdlPublishGuideItems.SiteContent?.enabled && <SiteContentGuide handleMilestoneStatusUpdate={ handleMilestoneStatusUpdate } hasCompleted={ !! siteContentComplete } key="SiteContentGuide" setTooltip={ triggerTooltip } />,
				!! gdlPublishGuideItems.SiteDesign?.enabled && <SiteDesignGuide handleMilestoneStatusUpdate={ handleMilestoneStatusUpdate } hasCompleted={ !! siteDesignComplete } key="SiteDesignGuide" setTooltip={ triggerTooltip } />,
				!! gdlPublishGuideItems.AddDomain?.enabled && <AddDomainGuide handleMilestoneStatusUpdate={ handleMilestoneStatusUpdate } hasCompleted={ !! addDomainComplete } key="AddDomainGuide" />,
				!! gdlPublishGuideItems.SEO?.enabled && <SEOGuide handleMilestoneStatusUpdate={ handleMilestoneStatusUpdate } hasCompleted={ !! siteSEOComplete } key="SEOGuide" setTooltip={ triggerTooltip } />,
			].filter( ( item ) => item !== false ) // filter disabled items.
		);

		return () => {
			if ( ! tooltipTargetElement ) {
				return null;
			}

			unbindTooltipTargetElementEvent();
		};
	}, [
		addDomainComplete,
		siteContentComplete,
		siteSEOComplete,
		siteDesignComplete,
		siteInfoComplete,
		siteMediaComplete,
	] );

	useEffect( () => {
		if ( ! tooltip ) {
			return;
		}

		tooltipTargetElement = document.getElementById( tooltip.refID );

		if ( ! tooltipTargetElement ) {
			return;
		}

		tooltipTargetElement.addEventListener( 'click', handleTooltipIconClick );
	}, [ tooltip ] );

	useEffect( () => {
		const params = {};

		window.location.search.substr( 1 ).split( '&' ).forEach( ( item ) => {
			params[ item.split( '=' )[ 0 ] ] = item.split( '=' )[ 1 ];
		} );

		if ( params.tooltip ) {
			setTimeout(	() => triggerTooltip( params.tooltip ), 2000 );
		}
	}, [] );

	/**
	 * Trigger the Tooltip
	 *
	 * @param {string} el - Name of the key to display
	 */
	const triggerTooltip = ( el ) => {
		const activeTooltip = tooltips[ el ];

		if ( ! activeTooltip ) {
			return null;
		}

		if ( tooltipTargetElement ) {
			closeTooltip();
		}

		tooltipTargetElement = document.getElementById( activeTooltip.refID );

		if ( ! tooltipTargetElement ) {
			return null;
		}

		if ( activeTooltip.collapseSidePanel ) {
			editPostDispatch.closeGeneralSidebar();
		}

		if ( activeTooltip.autoHide ) {
			this.tooltipAutoHideTimeout = setTimeout( closeTooltip, activeTooltip.autoHide );
		}

		closePublishGuide();

		setTooltip( {
			...activeTooltip,
			anchorRect: tooltipTargetElement.getBoundingClientRect(),
		} );
	};

	const handleMilestoneStatusUpdate = async ( milestoneName, status ) => {
		await fetchWithRetry(
			{
				data: {
					milestoneName,
					status,
				},
				method: 'POST',
				path: `gdl/v1/milestone/${ milestoneName }`,
			}
		);
	};

	/**
	 * Handle click actions on the element associated with the Tooltip
	 */
	const handleTooltipIconClick = () => {
		unbindTooltipTargetElementEvent();

		if ( tooltip && tooltip.nextTooltip ) {
			setTimeout( () => triggerTooltip( tooltip.nextTooltip ), 200 );
			return;
		}

		closeTooltip();
	};

	/**
	 * Close the Tooltip
	 */
	const closeTooltip = () => {
		clearTimeout( tooltipAutoHideTimeout );
		setTooltip( null );

		if ( ! tooltipTargetElement ) {
			return null;
		}

		unbindTooltipTargetElementEvent();

		tooltipTargetElement = null;
	};

	/**
	 * Remove the eventListener on the element associated with the Tooltip
	 */
	const unbindTooltipTargetElementEvent = () => tooltipTargetElement.removeEventListener( 'click', handleTooltipIconClick );

	// If we've published the site, we need to activate the FAB if the publish guide has not been completed.
	useEffect( () => {
		if ( typeof gdlLiveSiteControlData === 'undefined' ) {
			activatePublishGuideFab();
		}
	}, [] );

	// We only show the steps complete confetti if the publish guide was open at the time.
	const StepsCompleteConfetti = (	Confetti( isCompleted )	);

	// Show and hide Popover.
	const publishGuidePopoverClasses = [
		'publish-guide-popover',
		isPublishGuideOpened ? null : 'publish-guide-popover__display-none',
	].join( ' ' ).trim();

	const renderPopover = () => {
		return (
			<Popover
				className={ publishGuidePopoverClasses }
				focusOnMount="container"
				placement="bottom-start"
			>
				<div className="publish-guide-popover__header godaddy-styles components-modal__header">
					<div className={ classnames( 'publish-guide-popover__header__content', {
						WPEX_3590_active: wpex3590active,
					} ) }>
						{ wpex3590active && (
							<GoDaddyLogo />
						) }
						<h1 className="publish-guide-popover__header__title components-modal__header-heading">
							{ __( 'Quick Start Guide', 'godaddy-launch' ) }
						</h1>
						<p>{ sprintf(
							/* translators: field type eg: checkbox */
							__( 'Completed %1$d of %2$d', 'godaddy-launch' ),
							stepsCompleted, guideItems.length
						) }</p>
					</div>
					<div className="publish-guide-popover__header__progress">
						<PublishGuideProgress stepsCompleted={ stepsCompleted } stepsTotal={ guideItems.length } />
					</div>
				</div>

				<PublishGuideList
					isCollapsed={ isCompleted }
					selectedItem={ selectedItem }
					setSelectedItem={ ( name ) => setSelectedItem( name ) }>
					{ guideItems }
				</PublishGuideList>

				<Animated isCollapsed={ ! isCompleted }>
					<div className="publish-guide-popover__success">
						<h3>
							{ __( 'All tasks complete!', 'godaddy-launch' ) }
						</h3>
						<div>
							{ __( 'Ready to launch? You can always publish more updates any time.', 'godaddy-launch' ) }
						</div>
					</div>
				</Animated>

				<Animated>
					<div className="publish-guide-popover__footer"></div>
				</Animated>

				<div className="publish-guide-popover__footer"></div>
				<EidWrapper
					action="click"
					section="panel"
					target="opt_out"
				>
					<button
						className="publish-guide-popover__opt-out-link"
						onClick={ handleOptOutPublishGuide }
					>
						{ __( "I'm not interested in a guide", 'godaddy-launch' ) }
					</button>
				</EidWrapper>
				{ isCompleted && ( StepsCompleteConfetti ) }

			</Popover>
		);
	};

	return isPublishGuideFabActive && ! isMwcDialogOpened && ( ! isCompleted || ( ! sitePublished && ! siteLaunched ) ) && (
		<Fragment>
			<div className="publish-guide-default publish-guide-trigger godaddy-styles">
				{ ! publishGuideOptOut && (
					<PublishGuideButton
						isCompleted={ isCompleted }
						placement="bottom-start"
						publishGuideInteracted={ publishGuideInteracted }
						setPublishGuideMenuOpened={ setPublishGuideMenuOpened }
					/>
				) }

				<Popover.Slot />

				{ renderPopover() }

				{ showWelcomeTooltip && ! isCompleted && ! isPublishGuideOpened && ! publishGuideOptOut && ! publishGuideInteracted && (
					<EidWrapper
						action="click"
						section={ wpex3590active ? 'WPEX_3590_guide' : 'guide' }
						target={ 'welcome' }
					>
						<Popover
							className={ classnames( 'publish-guide-tooltip godaddy-styles', {
								wpex3590active,
							} ) }
							noArrow={ false }
							onClick={ togglePublishGuide }
							placement="bottom-end"
						>
							<div className="publish-guide-tooltip__description">
								{ __( 'Welcome! We put together a small list of tasks to help you get started.', 'godaddy-launch' ) }
							</div>
						</Popover>
					</EidWrapper>
				) }
			</div>

			{ tooltip && createPortal(
				<GoDaddyLaunchToolTip
					closeCallback={ closeTooltip }
					tooltip={ tooltip } />,
				document.body
			) }
		</Fragment>
	);
}

registerPlugin( 'gdl-publish-menu-item', {
	icon: null,
	render: PublishGuideMenuItem,
} );

domReady( () => {
	const rootElement = document.getElementById( gdvPublishGuideDefaults.appContainerClass );

	if ( ! rootElement ) {
		return;
	}

	const PublishGuideRoot = (
		<SlotFillProvider>
			<Router>
				{ !! gdlLiveSiteControlData.isMigratedSite ? (
					<MigratePublishGuide />
				) : (
					<PublishGuide />
				) }
			</Router>
		</SlotFillProvider>
	);

	if ( gdvPublishGuideDefaults.shouldUseReact18Syntax === 'true' ) {
		const { createRoot } = require( '@wordpress/element' );
		const root = createRoot( rootElement );

		root.render( PublishGuideRoot );
	} else {
		const { render } = require( '@wordpress/element' );
		render( PublishGuideRoot, rootElement );
	}
} );
