import { render, screen } from '@testing-library/react';

import AddDomainGuide from '../add-domain';

const defaultProps = {
	isComplete: false,
	toggleComplete: jest.fn(),
};

describe( 'publish-guide-items, add-domain', () => {
	describe( '#render', () => {
		it( 'should be visible with correct values', () => {
			render( <AddDomainGuide { ...defaultProps } /> );

			expect( screen.queryAllByTestId( 'publish-guide-item__container' ) ).toHaveLength( 1 );
		} );

		it( 'should display button when completed', () => {
			render( <AddDomainGuide { ...defaultProps } isComplete={ true } /> );

			expect( screen.queryAllByTestId( 'publish-guide-popover__item__title' ) ).toHaveLength( 1 );
		} );
	} );
} );
