import { __ } from '@wordpress/i18n';
import { Popover } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';

import { unregisterPlugin } from '@wordpress/plugins';

export const PublishGuideMenuItemTooltip = () => {
	const [ visible, setVisible ] = useState( false );
	const [ anchor, setAnchor ] = useState( null );

	const handleConfirm = () => {
		unregisterPlugin( 'gdl-publish-menu-item-tooltip' );
	};

	const handleClickOutside = ( e ) => {
		e.stopPropagation();
		e.preventDefault();

		const tooltipContainer = document.querySelector( '.publish-guide-tooltip' );

		if ( tooltipContainer && ! tooltipContainer.contains( e.target ) ) {
			unregisterPlugin( 'gdl-publish-menu-item-tooltip' );
		}
	};

	const handleInsideContainerClick = ( e ) => {
		e.stopPropagation();
		e.preventDefault();
	};

	useEffect( () => {
		document.addEventListener( 'mousedown', handleClickOutside );
		document.addEventListener( 'click', handleClickOutside );

		return () => {
			document.removeEventListener( 'mousedown', handleClickOutside );
			document.removeEventListener( 'click', handleClickOutside );
		};
	}, [] );

	useEffect( () => {
		setTimeout( () => {
			const quickStartGuideMenuButton = document.querySelector( '#publish-guide-more-menu-item' );

			if ( quickStartGuideMenuButton ) {
				quickStartGuideMenuButton.focus();
			}

			setVisible( true );

			const menuItemContainer = document.querySelector( '#publish-guide-more-menu-item' );

			if ( menuItemContainer ) {
				const menuContainerRect = menuItemContainer.getBoundingClientRect();

				setAnchor( new DOMRect(
					menuContainerRect.x - 20,
					menuContainerRect.y,
					menuContainerRect.width,
					menuContainerRect.height
				) );
			}
		}, 200 );
	}, [] );

	if ( ! visible || ! anchor ) {
		return null;
	}

	return (
		<Popover
			anchorRect={ anchor }
			className="publish-guide-tooltip publish-guide-menu-item-tooltip"
			focusOnMount={ false }
			noArrow={ false }
			offset={ 50 }
			onMouseDown={ handleInsideContainerClick }
			position="middle right"
		>
			<p className="publish-guide-menu-item__header">
				{ __( 'Just so you know…', 'godaddy-launch' ) }
			</p>
			<p>{ __( 'The Quick Start Guide is now disabled, but you can re-enable it any time from here.', 'godaddy-launch' ) }</p>
			<button className="publish-guide-menu-item__confirm godaddy-styles" onMouseDown={ handleConfirm }>{ __( 'Got it', 'godaddy-launch' ) }</button>
		</Popover>
	);
};

export default PublishGuideMenuItemTooltip;
