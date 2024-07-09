import { render, screen } from '@testing-library/react';

import SiteDesignGuide from '../site-design';

const defaultProps = {
	isComplete: false,
	setTooltip: jest.fn(),
	toggleComplete: jest.fn(),
};

describe( 'publish-guide-items, site-design', () => {
	describe( '#render', () => {
		it( 'should be visible with correct values', () => {
			render( <SiteDesignGuide { ...defaultProps } /> );

			expect( screen.queryAllByTestId( 'site-design-container' ) ).toHaveLength( 1 );
		} );
	} );
} );
