import { render, screen } from '@testing-library/react';

import PublishGuideItem from '../publish-guide-item';

const defaultProps = {
	children: 'someChildrenElement',
	title: 'someTitle',
	toggleComplete: jest.fn(),
};

describe( 'publish-guide-item', () => {
	beforeEach( () => {
		render( <PublishGuideItem { ...defaultProps } /> );
	} );

	describe( '#render', () => {
		it( 'should be visible with correct values', () => {
			expect( screen.queryAllByTestId( 'publish-guide-item__container' ) ).toHaveLength( 1 );
			expect( screen.getByText( defaultProps.title ) ).not.toBeNull();
			expect( screen.getByText( defaultProps.children ) ).not.toBeNull();
		} );
	} );
} );
