<?php

namespace Wptool\adminDash\services;

use WPaaS\Auto_Updates;
use Wptool\adminDash\constants\AutoUpdatesStatus;

class AutoUpdateService {

	/**
	 * Toggle auto updates.
	 *
	 * @return bool | null
	 */
	public function toggle_auto_update() {

		if ( class_exists( Auto_Updates::class ) && method_exists( Auto_Updates::class, 'toggle_auto_updates' ) ) {

			$auto_update_status = get_option( 'mwp_auto_updates_status', AutoUpdatesStatus::DISABLED );
			$result             = Auto_Updates::toggle_auto_updates( $auto_update_status );

			if ( empty( $result ) || $auto_update_status === $result ) {
				return null;
			}

			return AutoUpdatesStatus::ENABLED === $result;
		}

		return null;
	}

	/**
	 * Returns auto updates status.
	 *
	 * @return bool
	 */
	public function is_mwp_auto_updates_enabled() {
		return get_option( 'mwp_auto_updates_status', AutoUpdatesStatus::DISABLED ) === AutoUpdatesStatus::ENABLED;
	}

}
