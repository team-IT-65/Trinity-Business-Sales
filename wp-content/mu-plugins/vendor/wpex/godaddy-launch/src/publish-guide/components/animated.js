/**
 * External dependencies
 */
import useMeasure from 'react-use-measure';
import { animated, config as springConfig, useSpring } from 'react-spring';

/**
 * WordPress dependencies
 */
import { cloneElement } from '@wordpress/element';

export default function Animated( props ) {
	const {
		children,
		config = springConfig.default,
		isCollapsed = false,
	} = props;

	const [ ref, { height } ] = useMeasure();

	const styles = useSpring( {
		config,
		from: { height: 0, opacity: 0 },
		to: { height: isCollapsed ? 0 : height, opacity: isCollapsed ? 0 : 1 },
	} );

	return (
		<animated.div style={ { overflow: 'hidden', ...styles } }>
			{ cloneElement( children, { ref } ) }
		</animated.div>
	);
}
