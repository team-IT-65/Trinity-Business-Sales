/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';
import { useEffect, useRef } from '@wordpress/element';

/**
 * Internal dependencies
 */

const LiveSitePreview = () => {
	const shadowHost = useRef();

	useEffect( () => {
		const shadowRoot = shadowHost.current.attachShadow( { mode: 'open' } );

		// Reproducing what is done in includes/LiveSiteControl/template-coming-soon.php
		shadowRoot.innerHTML = `
			<style>
				.coming-soon {
					align-items: center;
					background:
						linear-gradient(
							101.44deg,
							rgba(252, 204, 203, 0.2) 16.81%,
							rgba(239, 230, 212, 0.2) 53.67%,
							rgba(211, 220, 243, 0.2) 67.17%,
							rgba(217, 245, 253, 0.2) 76.66%,
							rgba(255, 255, 255, 0.2) 86.88%
						),
						linear-gradient(0deg, #FFFFFF, #FFFFFF);
					bottom: 0;
					display: flex;
					flex-direction: column;
					font-size: 12px;
					justify-content: center;
					left: 0;
					margin: 0;
					padding: 40px 20px 20px;
					pointer-events: none;
					position: absolute;
					right: 0;
					top: 0;
				}

				nav {
					background-color: #d6d6d6;
					height: 20px;
					left: 0;
					position: absolute;
					right: 0;
					top: 0;
				}

				nav::after {
					background-color: #f1f1f1;
					border-radius: 3px;
					bottom: 6px;
					content: '';
					display: block;
					left: 41px;
					position: absolute;
					right: 8px;
					top: 6px;
				}

				ul {
					display: flex;
					left: 10px;
					list-style-type: none;
					margin: 0;
					padding: 0;
					position: absolute;
					top: 7px;
				}

				li {
					background-color: #f5f5f5;
					border-radius: 6px;
					height: 6px;
					margin-right: 3px;
					width: 6px;
				}

				h1, p {
					margin: 0;
				}

				h1 {
					color: #000;
					font-family: Baskerville, Georgia, Cambria, "Times New Roman", Times, serif;
					font-size: 2.6em;
					font-weight: normal;
					line-height: 1.15em;
					text-align: center;
					width: 85%;
				}

				p {
					color: #2b2b2b;
					font-family: Avenir, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
					font-size: .9em;
					font-weight: 500;
					line-height: 1.5em;
					margin-top: 2em;
					text-align: center;
				}
			</style>
			<div class="coming-soon">
				<nav><ul><li></li><li></li><li></li></ul></nav>
				<h1>${ __( 'Great things are coming soon', 'godaddy-launch' ) }</h1>
				<p>${ __( 'Stay tuned', 'godaddy-launch' ) }</p>
			</div>
		`;
	}, [] );

	return (
		<div className="gdl-live-site-preview">
			<div className="gdl-live-site-preview__container" ref={ shadowHost }></div>
		</div>
	);
};

export default LiveSitePreview;
