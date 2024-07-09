/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';

/**
 * fetchWithRetry is a custom wrapper around the `@wordpress/api-fetch` utility to add auto-retry.
 *
 * @param {string} url
 * @param {Object} options
 * @param {number} attempts
 * @return {JSON} JSON fetch value
 */
export const fetchWithRetry = async ( url, options = {}, attempts = 3 ) => {
	let error;
	for ( let i = 0; i < attempts; i++ ) {
		try {
			return await apiFetch( url, options );
		} catch ( err ) {
			error = err;
		}
	}
	throw error;
};
