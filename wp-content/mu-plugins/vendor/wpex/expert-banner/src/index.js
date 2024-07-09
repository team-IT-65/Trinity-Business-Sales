/**
 * External dependencies
 */
import { get } from 'lodash';

/**
 * WordPress dependencies
 */
import { createInterpolateElement, createPortal, render, useEffect, useState, useRef, forwardRef } from '@wordpress/element';
import { withSelect } from '@wordpress/data';
import { ifCondition, compose, pure } from '@wordpress/compose';
import { Popover, Button } from '@wordpress/components';
import { Icon, info } from '@wordpress/icons';
import { __ } from '@wordpress/i18n';
import { logInteractionEvent } from './instrumentation';
const EID_PREFIX = 'wp.editor';

/**
 * Local dependencies
 */
import { HIRE_EXPERT_SETTINGS_ID } from './settings';

import './index.scss';

function clickSettingsButton() {
  document.querySelector('.interface-more-menu-dropdown > button')?.click?.();
}

/**
 * This functional component renders null and instead uses a 'hacky' approach
 * to modify the editor toolbar to include Expert Link.
 *
 * @param {Object} props Contains the props such as 'editorToolbar'
 * @returns null
 */
function ExpertBanner(props) {
  const { editorToolbar } = props;
  const [ target, setTarget ] = useState(null);
  const [ tooltip, setTooltip ] = useState(false);
  const [ settingsPanelOpen, setSettingsPanel ] = useState(false);
  const [ settingsAnchor, setSettingsAnchor ] = useState(null);
  const iconRef = useRef(null);
  const settingsBtnId = 'wpex-eb-open-settings';
  const popoverId = 'wpex-eb-tooltip-popover';

  useEffect(() => {
    const div = document.createElement('div');
    div.id = 'wpex-eb-container';
    editorToolbar.parentNode.insertBefore(div, editorToolbar);
    setTarget(div);
  }, [editorToolbar]);

  useEffect(function settingsPanel() {
    if (!settingsPanelOpen) {
      return;
    }

    clickSettingsButton();

    setTimeout(() => {
      const settingButton = document.getElementById(HIRE_EXPERT_SETTINGS_ID);

      if (!settingButton) {
        return;
      }

      settingButton.focus();

      const settingButtonContainer = settingButton.getBoundingClientRect();

      setSettingsAnchor(new DOMRect(
        settingButtonContainer.x + 20,
        settingButtonContainer.y,
        settingButtonContainer.width,
        settingButtonContainer.height
      ));
    }, 200);

    setSettingsPanel(false);
  }, [settingsPanelOpen, setSettingsAnchor]);

  const handleClickOutside = ( e ) => {
    e.stopPropagation();
    e.preventDefault();
  };

  useEffect( () => {
    if (!settingsAnchor){
      return;
    }

    document.addEventListener( 'mousedown', handleClickOutside );
    document.addEventListener( 'click', handleClickOutside );

    return () => {
      document.removeEventListener( 'mousedown', handleClickOutside );
      document.removeEventListener( 'click', handleClickOutside );
    };
  }, [settingsAnchor] );

  if (!target) {
    return null;
  }

  const iconRect = iconRef.current?.getBoundingClientRect();
  const targetRect = new DOMRect(
    iconRect?.x,
    iconRect?.y + 10,
    iconRect?.width,
    iconRect?.height
  )

  return createPortal(
    <>
      {
        settingsAnchor && (
            <Popover
              anchorRect={ settingsAnchor }
              className="wpex-eb-tooltip"
              focusOnMount={ false }
              noArrow={ false }
              offset={ 50 }
              position="middle right"
            >
              <p className="wpex-eb-tooltip__title">
                { __( 'Just so you know…', 'gd-system-plugin' ) }
              </p>
              <p className='wpex-eb-tooltip__description'>
                { __( 'Here you can enable or disable "Hire an expert"', 'gd-system-plugin' ) }
              </p>
              <button
                className="wpex-eb-tooltip__button"
                onClick={ (e) => setSettingsAnchor(null) }
              >
                { __( 'Got it', 'gd-system-plugin' ) }
              </button>
            </Popover>
        )
      }

      <span ref={ iconRef }>
        <Icon
          icon={ info }
          onClick={ () => setTooltip((v) => !v) }
        />
      </span>

      {
        tooltip && (
          <Popover
            className="wpex-eb-tooltip wpex-eb-tooltip--black"
            anchorRect={ targetRect }
            noArrow={ false }
            focusOnMount={ false }
            position="middle top"
          >
            <div>
            {
              createInterpolateElement(
                __( 'To disable this option, go to the <button>Tools</button> menu.', 'gd-system-plugin' ),
                {
                  button: (
                    <Button
                      variant='link'
                      id={ settingsBtnId }
                      onClick={ () => {
                          setTooltip(false);
                          setSettingsPanel(true);
                        }
                      }
                    />
                  )
                }
              )
            }
            </div>
          </Popover>
        )
      }

      <a
        id='wpex-expert-banner'
        target='_blank'
        href='https://www.godaddy.com/websites/web-design'
        rel='noopener noreferrer'
        onClick={
          () => logInteractionEvent({
            eid: `${EID_PREFIX}.expert/wpex_expert/expert.link`,
          })
        }
      >
        { __('Hire an expert', 'gd-system-plugin') }
      </a>
      <div id='wpex-eb-divider' />
    </>,
    target
  );
}

/**
 * Logic based on AMP approach: https://github.com/ampproject/amp-wp/blob/81b9df742d9f78fb5ee3657622d21eb8c7926f64/assets/src/block-editor/plugins/wrapped-amp-preview-button.js#L76-L92
 *
 * Core WordPress refers to this example for how to manipulate the toolbar.
 * https://github.com/WordPress/gutenberg/issues/16988
 */
const ExpertBannerWithHOC = pure(
  compose([
    withSelect((select) => {
      const { getPostType, getEntityRecord } = select('core');
      const { getEditedPostAttribute } = select('core/editor');

      const postType = getPostType(getEditedPostAttribute('type'));
      const hireExpertHidden = getEntityRecord('root', 'site')?.[wpexExpertBanner.optionHidden] ?? false;

      return {
        hireExpertHidden,
        isViewable: get(postType, ['viewable'], false),
        editorToolbar: document.querySelector('.edit-post-header__settings'),
      };
    }),
    // This HOC creator renders the component only when the condition is true. At that point the 'Post' preview
    // button should have already been rendered (since it also relies on the same condition for rendering).
    // If Post preview is rendered then editor toolbar is also rendered and is safe to query.
    ifCondition(({ hireExpertHidden }) => !hireExpertHidden),
    ifCondition(({ isViewable }) => isViewable),
    ifCondition(({ editorToolbar }) => !!editorToolbar),
  ])(ExpertBanner),
);

/**
 * Setup container div and attach it to the element with editor id.
 * This give us a point to mount the functional react component so we can
 * use React hooks.
 */
const attachExpertBannerToDOM = () => {
  // Create a new container element
  const container = document.createElement('div');

  // Find the existing DOM node with the ID 'editor'
  const editor = document.getElementById('editor');

  // Attach the new container element to the editor node
  editor.appendChild(container);

  render(
    <ExpertBannerWithHOC />,
    container
  );
}

/**
 * 'DOMContentLoaded' Browser hook is not sufficient to have the toolbar accessible for query.
 * Because of that we use a HOC to hook into the core data-stores.
 * Once we have confirmation via data-store we can query and manipulate the toolbar.
 */
document.addEventListener('DOMContentLoaded', attachExpertBannerToDOM);
