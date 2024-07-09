/**
 * WordPress dependencies
 */
import { registerPlugin } from '@wordpress/plugins';
import { check } from '@wordpress/icons';
import { MenuItem } from '@wordpress/components';
import { PluginMoreMenuItem } from '@wordpress/edit-post';
import { __ } from '@wordpress/i18n';
import { useEntityProp } from '@wordpress/core-data';
import { useDispatch } from '@wordpress/data';

export const HIRE_EXPERT_SETTINGS_ID = 'wpex-expert-banner-hidden';

export function HireExpertSettings() {
    const { saveEditedEntityRecord } = useDispatch( 'core' );

    const [ hireExpertHidden, setHireExpertHidden ] = useEntityProp( 'root', 'site', wpexExpertBanner.optionHidden );

    const handleHireExpertClick = () => {
        setHireExpertHidden( !hireExpertHidden );
        saveEditedEntityRecord( 'root', 'site' );
    };

    return (
        <PluginMoreMenuItem
            as={ MenuItem }
            id={ HIRE_EXPERT_SETTINGS_ID }
            icon={ !!hireExpertHidden ? null : check }
            onClick={ handleHireExpertClick }
        >
            { __( 'Hire an expert', 'gd-system-plugin' ) }
        </PluginMoreMenuItem>
    );
};

registerPlugin( 'wpex-eb-settings-plugin', {
    icon: '',
    render: HireExpertSettings,
} );
