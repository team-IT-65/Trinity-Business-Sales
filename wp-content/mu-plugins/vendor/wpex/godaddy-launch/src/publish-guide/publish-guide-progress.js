/**
 * WordPress dependencies
 */
import { useEffect, useState } from '@wordpress/element';

export default function PublishGuideProgress( props ) {
	const {
		stepsCompleted,
		stepsTotal,
	} = props;

	const [ percent, setPercent ] = useState( 0 );

	useEffect( () => {
		const newPercent = ( ! stepsCompleted || ! stepsTotal ) ? 0 : Math.round( 100 * stepsCompleted / stepsTotal );

		setPercent( newPercent );
	}, [ stepsCompleted ] );

	const barForegroundWidth = {
		width: `${ percent }%`,
	};

	return (
		<div className="publish-guide-progress">
			<div className="publish-guide-progress-bar-background">
				<div className="publish-guide-progress-bar-foreground" style={ barForegroundWidth }>
				</div>
			</div>
		</div>
	);
}
