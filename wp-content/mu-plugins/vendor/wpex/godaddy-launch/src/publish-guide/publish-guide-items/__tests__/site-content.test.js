import { render, screen } from '@testing-library/react';

import SiteContentGuide from '../site-content';

const defaultProps = {
	isComplete: false,
	setTooltip: jest.fn(),
	toggleComplete: jest.fn(),
};

describe( 'publish-guide-items, site-content', () => {
	describe( '#render', () => {
		it( 'should be visible with correct values', () => {
			render( <SiteContentGuide { ...defaultProps } /> );

			expect( screen.queryAllByTestId( 'site-content-container' ) ).toHaveLength( 1 );
		} );
	} );
} );
