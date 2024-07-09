/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Icon } from '@wordpress/components';

import Search from '../icons/search';

export default function ReviewSite() {
	return (
		<li className="publish-guide-popover__item">
			<div className="publish-guide-popover__item__content">
				<div className="item">
					<Icon
						icon={ Search }
					/>
					<button className="publish-guide-popover__item__title">
						{ __( 'Review migrated site', 'godaddy-launch' ) }
					</button>
				</div>
				<div className="info">
					<p>{ __( 'Go through the website and make sure everything looks good', 'godaddy-launch' ) }</p>
				</div>
			</div>
		</li>
	);
}
