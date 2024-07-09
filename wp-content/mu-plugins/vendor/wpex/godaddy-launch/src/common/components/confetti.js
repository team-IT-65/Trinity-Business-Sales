/**
 * WordPress dependencies
 */
import { useCallback, useEffect, useRef } from '@wordpress/element';

/**
 * External dependencies
 */
import ReactCanvasConfetti from 'react-canvas-confetti';

/**
 * @function Confetti
 * @param {boolean} confettiConditional Conditional variable used to render the Confetti.
 * @param {boolean} fullScreen          Whether to use geometry settings that should take the full page.
 *
 * @return {JSX} Confetti component
 */
const Confetti = ( confettiConditional, fullScreen = false ) => {
	const refAnimationInstance = useRef( null );
	const getInstance = useCallback( ( instance ) => {
		refAnimationInstance.current = instance;
	}, [ confettiConditional ] );

	const makeShot = useCallback( ( particleRatio, opts ) => {
		if ( !! refAnimationInstance.current ) {
			refAnimationInstance.current( {
				...opts,
				colors: [
					'#09757a',
					'#00a4a6',
					'#1bdbdb',
				],
				gravity: 0.5,
				origin: { y: 0.55 },
				particleCount: Math.floor( 250 * particleRatio ),
			} );
		}
	}, [] );

	useEffect( () => {
		makeShot( 0.25, {
			spread: 26,
			startVelocity: fullScreen ? 155 : 15,
		} );

		makeShot( 0.2, {
			spread: 60,
		} );

		makeShot( 0.35, {
			decay: 0.91,
			scalar: 1.5,
			spread: 100,
		} );

		makeShot( 0.1, {
			decay: 0.92,
			scalar: 1.2,
			spread: 120,
			startVelocity: fullScreen ? 25 : 10,
		} );

		makeShot( 0.1, {
			spread: 200,
			startVelocity: fullScreen ? 45 : 10,
		} );
	}, [ confettiConditional ] );

	return (
		<ReactCanvasConfetti
			disableForReducedMotion={ true }
			refConfetti={ getInstance }
			style={ {
				bottom: 0,
				height: '100%',
				left: 0,
				pointerEvents: 'none',
				position: 'absolute',
				right: 0,
				top: 0,
				width: '100%',
				zIndex: 1000000,
			} }
			useWorker={ true } />
	);
};

export default Confetti;

