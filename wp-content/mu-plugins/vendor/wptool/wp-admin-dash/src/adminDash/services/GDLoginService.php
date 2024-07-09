<?php

namespace Wptool\adminDash\services;

use Wptool\adminDash\constants\ToggleStatus;

class GDLoginService {
	/**
	 * This function update wp_options, if the option does not exist, it will be created.
	 * Otherwise, it will set opposite status from one in database.
	 *
	 * @return bool
	 */
	public function toggle_gd_login() {
		$login_enabled = get_option( 'wpaas_gd_sso_button_enabled', ToggleStatus::ENABLED );
		$status        = ToggleStatus::ENABLED;

		if ( ToggleStatus::ENABLED === $login_enabled ) {
			$status = ToggleStatus::DISABLED;
		}

		update_option( 'wpaas_gd_sso_button_enabled', $status );

		return ToggleStatus::ENABLED === $status;
	}

	/**
	 * This function retrieves current onboarding status for a user
	 *
	 * @return bool
	 */
	public function is_gd_login_enabled() {
		return ToggleStatus::ENABLED === get_option( 'wpaas_gd_sso_button_enabled', ToggleStatus::ENABLED );
	}
}
