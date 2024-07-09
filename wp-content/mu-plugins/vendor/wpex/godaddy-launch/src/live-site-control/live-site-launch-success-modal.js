/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { GlobeIcon } from '@godaddy-wordpress/coblocks-icons';
import { useCopyToClipboard } from '@wordpress/compose';
import { useSelect } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { Button, Modal, Icon as WordpressIcon } from '@wordpress/components';
import { Icon, pages } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import Confetti from '../common/components/confetti';
import FacebookIcon from './icons/facebook';
import GoogleIcon from './icons/google';
import InstagramIcon from './icons/instagram';
import { logInteractionEvent } from '../common/utils/instrumentation';
import TwitterIcon from './icons/twitter';
import YelpIcon from './icons/yelp';
import { EID_PREFIX, EidWrapper } from '../common/components/eid-wrapper';

const SocialMediaCTA = ( { SocialMediaIcon, label, link } ) => {
	return (
		<a
			className="gdl-launch-site-success-modal__social-media-cta"
			href={ link }
			onClick={ () => logInteractionEvent( { eid: `${ EID_PREFIX }.launch_site_success_modal/${ label.toLowerCase() }/social_media_cta.icon` } ) }
			rel="noopener noreferrer"
			target="_blank"
		>
			<div
				className="icon-container"
			>
				<WordpressIcon icon={ SocialMediaIcon } />
			</div>
			<p className="label-container">{ label }</p>
		</a>
	);
};

const SitePreview = ( { url } ) => {
	return (
		<div className="gdl-live-site-preview">
			<div className="gdl-live-site-preview__container">
				<iframe src={ url + '?gdl-live-control-preview=true' } title="site-preview" />
			</div>
		</div>
	);
};

const LiveSiteLaunchSuccessModal = ( { closeModal } ) => {
	const [ copyText, setCopyText ] = useState( __( 'Copy the URL', 'godaddy-launch' ) );

	const forceHttps = ( url ) => {
		if ( url.includes( '//localhost' ) || url.includes( '//gdl.test' ) ) {
			return url;
		}

		return url.replace( 'http:/', 'https:/' );
	};

	const { url } = useSelect( ( select ) => {
		return {
			url: forceHttps( select( 'core' ).getSite()?.url || window.location.origin ),
		};
	} );

	const ref = useCopyToClipboard( url, () => setCopyText( __( 'Copied!', 'godaddy-launch' ) ) );

	return (
		<>
			<Modal
				className="gdl-launch-site-success-modal godaddy-styles"
				onRequestClose={ closeModal }
				title={ __( 'Good work! Your site is live.', 'godaddy-launch' ) }
			>
				<div className="gdl-post-publish-social-media-modal__site-preview">
					<SitePreview url={ url } />
				</div>
				<p className="gdl-launch-site-success-modal__description">{ __( 'Share it and get people excited about your new site.', 'godaddy-launch' ) }</p>
				<div className="gdl-launch-site-success-modal__social-media">
					<SocialMediaCTA label="Instagram" link="https://www.instagram.com" SocialMediaIcon={ InstagramIcon } />
					<SocialMediaCTA label="Twitter" link="https://www.twitter.com" SocialMediaIcon={ TwitterIcon } />
					<SocialMediaCTA label="Google" link="https://www.google.com" SocialMediaIcon={ GoogleIcon } />
					<SocialMediaCTA label="Facebook" link="https://www.facebook.com/" SocialMediaIcon={ FacebookIcon } />
					<SocialMediaCTA label="Yelp" link="https://www.yelp.com/" SocialMediaIcon={ YelpIcon } />
				</div>
				<div className="gdl-launch-site-success-modal__content">
					<div className="gdl-launch-site-success-modal__site-description-container">
						<div className="gdl-launch-site-success-modal__site-description-container__icon-container border-right">
							<Icon icon={ GlobeIcon } size={ 18 } />
						</div>
						<p className="gdl-launch-site-success-modal__site-description-container__site-name">{ url }</p>
					</div>
					<EidWrapper
						action="click"
						section="gdl_launch_site_success_modal/copy_url"
						target="button"
					>
						<Button
							icon={ <Icon icon={ pages } /> }
							isSecondary
							ref={ ref }
						>
							{ copyText }
						</Button>
					</EidWrapper>
				</div>
				<div className="gdl-launch-site-success-modal__cta-container">
					<Button
						href={ url }
						isPrimary
						target="_blank"
					>
						{ __( 'View Site', 'godaddy-launch' ) }
					</Button>
				</div>
			</Modal>
			{ Confetti( true, true ) }
		</>
	);
};

export default LiveSiteLaunchSuccessModal;
