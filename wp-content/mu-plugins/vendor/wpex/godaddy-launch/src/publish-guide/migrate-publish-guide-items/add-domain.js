/* global gdlLiveSiteControlData, gdlPublishGuideItems, gdvLinks */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useEntityProp } from '@wordpress/core-data';
import { Button, Icon } from '@wordpress/components';

/**
 * Internal dependencies
 */
import CircleChecked from '../icons/circleChecked';
import { EidWrapper } from '../../common/components/eid-wrapper';
import World from '../icons/world';

export default function AddDomain() {
	const changeDomainUrl = gdvLinks?.changeDomain.replace( '{{DOMAIN}}', window.location.hostname );

	const navigateSiteSettings = () => {
		window.open( changeDomainUrl, '_self' );
	};

	const optionName = gdlPublishGuideItems.AddDomain.propName;
	const [ addDomainComplete = gdlPublishGuideItems.AddDomain.default ] = useEntityProp( 'root', 'site', optionName );

	const renderStepText = () => {
		return (
			<p>
				{ addDomainComplete ? gdlLiveSiteControlData.siteUrl : __( 'Choose the domain you used before, or another one if you prefer', 'godaddy-launch' ) }
			</p>
		);
	};

	return (
		<li className="publish-guide-popover__item">
			<div className="publish-guide-popover__item__content">
				<div className="item">
					<Icon
						icon={ addDomainComplete ? CircleChecked : World }
					/>
					<button className="publish-guide-popover__item__title">
						{ addDomainComplete ? (
							__( 'Domain added', 'godaddy-launch' )
						) : (
							__( 'Add your domain', 'godaddy-launch' )
						) }
					</button>
				</div>
				<div className="info">
					{ renderStepText() }
					{ ! addDomainComplete && (
						<EidWrapper
							action="click"
							section="migrate_guide/item/add_domain"
							target="edit"
						>
							<Button
								className="publish-guide-popover__link components-button is-link"
								onClick={ navigateSiteSettings }
								variant="link"
							>
								{ __( 'Select a domain', 'godaddy-launch' ) }
							</Button>
						</EidWrapper>
					) }
				</div>
			</div>
		</li>
	);
}
