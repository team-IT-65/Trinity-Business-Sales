/* global gdvPublishGuideDefaults, gdlLiveSiteControlData */

import { __ } from '@wordpress/i18n';
import { Popover } from '@wordpress/components';
import { useEntityProp } from '@wordpress/core-data';
import { dispatch, useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import Animated from './components/animated';
import MigratePublishGuideList from './migrate-publish-guide-list';
import PublishGuideButton from './publish-guide-button';
import PublishGuideProgress from './publish-guide-progress';
import { store as publishGuideStore } from './store';

// Safe reference to object needed for useEntityProp call.
const gdlLiveSiteControl = typeof gdlLiveSiteControlData !== 'undefined' ? gdlLiveSiteControlData : {};

export default function MigratePublishGuide() {
	const {
		isPublishGuideFabActive,
		isPublishGuideOpened,
	} = useSelect( ( select ) => ( {
		isPublishGuideFabActive: select( publishGuideStore ).isPublishGuideFabActive(),
		isPublishGuideOpened: select( publishGuideStore ).isPublishGuideOpened(),
	} ), [] );

	const [
		// If gdlLiveSiteControlData is undefined, the site has published because we do not localize the data otherwise.
		sitePublished = ( typeof gdlLiveSiteControlData === 'undefined' ),
	] = useEntityProp( 'root', 'site', gdlLiveSiteControl?.settings?.publishState );

	// Has the publish guide menu ever been opened?
	const [ publishGuideInteracted = false, setPublishGuideInteracted ] = useEntityProp( 'root', 'site', gdvPublishGuideDefaults.optionInteracted );

	const setPublishGuideMenuOpened = () => {
		setPublishGuideInteracted( true );
		dispatch( 'core' ).saveEntityRecord( 'root', 'site', { [ gdvPublishGuideDefaults.optionInteracted ]: true } );
	};

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
					<div className="publish-guide-popover__header__content">
						<h1 className="publish-guide-popover__header__title components-modal__header-heading">
							{ __( 'Migration Guide', 'godaddy-launch' ) }
						</h1>
					</div>
					<div className="publish-guide-popover__header__progress">
						<PublishGuideProgress stepsCompleted={ 0 } stepsTotal={ 2 } />
					</div>
				</div>

				<MigratePublishGuideList />

				<Animated>
					<div className="publish-guide-popover__footer"></div>
				</Animated>

			</Popover>
		);
	};

	return isPublishGuideFabActive && ! sitePublished && (
		<div className="publish-guide-migrate publish-guide-trigger godaddy-styles">
			<PublishGuideButton
				isCompleted={ false }
				placement="bottom-start"
				publishGuideInteracted={ publishGuideInteracted }
				setPublishGuideMenuOpened={ setPublishGuideMenuOpened }
			/>

			<Popover.Slot />

			{ renderPopover() }
		</div>
	);
}
