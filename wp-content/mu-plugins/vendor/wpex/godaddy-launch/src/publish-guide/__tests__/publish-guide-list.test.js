import PublishGuideItem from '../publish-guide-item';
import PublishGuideList from '../publish-guide-list';

import { render, screen } from '@testing-library/react';

global.ResizeObserver = require( 'resize-observer-polyfill' );

const defaultProps = {
	children: [
		<PublishGuideItem
			key="someKey"
			setTooltip={ () => {} }
			text="someText"
			title="someTitle" />,
	],
	onListComplete: jest.fn(),
	toggleComplete: jest.fn(),
};

describe( 'publish-guide-list', () => {
	it( 'should render correctly', () => {
		render( <PublishGuideList { ...defaultProps } /> );
		expect( screen.queryAllByTestId( 'publish-guide-item' ) ).toHaveLength( 1 );
	} );
} );
