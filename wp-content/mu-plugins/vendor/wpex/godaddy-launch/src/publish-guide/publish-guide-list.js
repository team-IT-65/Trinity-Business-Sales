/**
 * WordPress dependencies
 */
import { cloneElement } from '@wordpress/element';

/**
 * Internal dependencies
 */
import Animated from './components/animated';

export default function PublishGuideList( props ) {
	const {
		children,
		isCollapsed,
		selectedItem,
		setSelectedItem,
	} = props;

	return (
		<Animated isCollapsed={ isCollapsed }>
			<ul className="publish-guide-popover__items">
				{ children?.map( ( child, childIndex ) => (
					<div data-testid="publish-guide-item" key={ `publish-guide-item-${ childIndex }` }>
						{ cloneElement( child, {
							key: `publish-guide-item-${ childIndex }`,
							selectedItem,
							setSelectedItem,
						} ) }
					</div>
				) ) }
			</ul>
		</Animated>
	);
}
