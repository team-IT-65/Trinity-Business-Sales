/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { Icon } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { EidWrapper } from '../common/components/eid-wrapper';

import CaretDown from './icons/caretDown';
import Circle from './icons/circle';
import CircleChecked from './icons/circleChecked';

export default function PublishGuideItem( props ) {
	const {
		children,
		isCompleted,
		name,
		selectedItem,
		setSelectedItem,
		skipAction,
		title,
		text,
		testId,
	} = props;

	return (
		<li
			className={ classnames(
				'publish-guide-popover__item', {
					'is-completed': isCompleted,
					'is-selected': selectedItem === name,
				}
			) }
			data-testid={ testId ? testId : 'publish-guide-item__container' }
		>
			<div className="publish-guide-popover__item__content" data-testid="publish-guide-popover__item__content">
				<div className="item">
					<EidWrapper
						action="click"
						section={ `guide/item/${ name }` }
						target="skip"
					>
						<Icon
							className="publish-guide-popover__item__checkmark-icon"
							icon={ ! isCompleted ? Circle : CircleChecked }
							onClick={ ! isCompleted ? skipAction : null }
						/>
					</EidWrapper>
					<EidWrapper
						action="click"
						section={ `guide/item/${ name }` }
						target="panel"
					>
						<button
							className="publish-guide-popover__item__title"
							data-testid="publish-guide-popover__item__title"
							onClick={ () => {
								setSelectedItem( selectedItem === name ? null : name );
							} }>
							{ title }
							{ ! isCompleted && <Icon icon={ CaretDown } /> }
						</button>
					</EidWrapper>
				</div>
				{ selectedItem === name && ! isCompleted && (
					<div className="info">
						<p>{ text }</p>
						{ children }
					</div>
				) }
			</div>
		</li>
	);
}
