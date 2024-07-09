// Import the `apiFetch` function and the `fetchWithRetry` function.
import apiFetch from '@wordpress/api-fetch';
import { fetchWithRetry } from '../../common/utils/fetchWithRetry';

// Create a mock for the `apiFetch` function using `jest.fn()`.
jest.mock( '@wordpress/api-fetch', () => jest.fn() );

describe( 'fetchWithRetry', () => {
	// Define the URL and options for the `apiFetch` call.
	const url = 'http://example.com';
	const options = {
		body: {
			someData: 'abc123',
		},
		method: 'POST',
	};

	// Define a successful response for the `apiFetch` call.
	const successResponse = {
		body: {
			someData: 'def456',
		},
		status: 200,
	};

	// Define a failed response for the `apiFetch` call.
	const failureResponse = {
		body: {
			error: 'Failed to fetch data.',
		},
		status: 500,
	};

	beforeEach( () => {
		// Reset the mock for the `apiFetch` function before each test.
		apiFetch.mockReset();
	} );

	it( 'retries on failure and returns status code', async () => {
		// Mock the `apiFetch` function to return a failed response on the first attempt
		// and a successful response on the second attempt.
		apiFetch.mockImplementationOnce( () => Promise.reject( failureResponse ) );
		apiFetch.mockImplementationOnce( () => Promise.resolve( successResponse ) );

		// Call the `fetchWithRetry` function with the URL and options.
		const response = await fetchWithRetry( url, options );

		// Assert that the `fetchWithRetry` function called the `apiFetch` function twice.
		expect( apiFetch ).toHaveBeenCalledTimes( 2 );

		// Assert that the `fetchWithRetry` function returned the expected response.
		expect( response ).toEqual( successResponse );
		expect( response.status ).toEqual( 200 );
	} );
} );
