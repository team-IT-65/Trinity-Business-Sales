/** global wp */
/**
 * WordPress dependencies
 */
import { logInteractionEvent } from '../utils/instrumentation';
import { cloneElement, isValidElement } from '@wordpress/element';

const isInEditor = window.location.pathname.includes( 'post-new' ) || window.location.pathname.includes( 'post.php' );

export const EID_PREFIX = 'wp.' + ( isInEditor ? 'editor' : 'wpadmin' );

/**
 * Return an element which intercepts any child click events and logs an interaction event to the data layer before propagating
 *
 * @param    {Object} props    Should use all referenced properties in constructing the wrapper.
 * @property {Object} children `children` React component children
 * @property {string} section  `section` Reflects section within the interface
 *                             e.g., `guide/item/add_domain`, `launch/modal/finish/choices`.
 * @property {string} target   `target` Reflects interacted element and its role description.
 *                             e.g., `yes`, `no`, `panel`, `launch_later`, `launch_now`, `edit`, `skip`.
 * @property {string} action   `action` Reflects user action
 *                             e.g., `click`.
 */
export const EidWrapper = ( props ) => {
	const { children, section, target, action } = props;
	if ( ! isValidElement( children ) ) {
		return children;
	}

	return cloneElement( children, {
		...children.props,
		'data-test-eid': `${ EID_PREFIX }.${ section }.${ target }.${ action }`,
		onClick: ( ...args ) => {
			if ( children.props?.onClick ) {
				children.props?.onClick( ...args );
			}
			logInteractionEvent( { eid: `${ EID_PREFIX }.${ section }.${ target }` } );
		},
	} );
};
