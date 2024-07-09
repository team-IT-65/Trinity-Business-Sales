/**
 * WordPress dependencies
 */
import { dispatch } from '@wordpress/data';

const callArray = [];

// Debounce function that delays calls until the user stops invoking
const debounce = ( func, delay ) => {
	let debounceTimer;
	return async function( ...args ) {
		// Collect call arguments and combine into single entity record call.
		callArray.push( { [ args[ 0 ] ]: args[ 1 ] } );
		const combinedArguments = Object.assign( {}, ...callArray );

		const context = this;
		clearTimeout( debounceTimer );
		debounceTimer = setTimeout(
			async () => await func.apply( context, [ combinedArguments ] ),
			delay
		);
	};
};

// Save the entity record once with all arguments.
const saveEntityRecord = async ( passedArguments ) => {
	await dispatch( 'core' ).saveEntityRecord( 'root', 'site', passedArguments );
};

// Add a debounce wrapper to the saveEntityRecord function
const debouncedSaveEntityRecord = debounce( saveEntityRecord, 1000 );

export default debouncedSaveEntityRecord;
