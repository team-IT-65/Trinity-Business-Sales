<?php

namespace Wptool\adminDash\controllers;

use Wptool\adminDash\constants\UserRole;
use Wptool\adminDash\services\container\ServiceContainer;

class BaseController extends \WP_REST_Controller {

	protected $container;

	/**
	 * Setting Service container and namespace for API endpoints.
	 *
	 * @param ServiceContainer $container
	 */
	public function __construct( $container ) {

		$this->container = $container;
		$this->namespace = 'hosting-admin';
	}

	/**
	 * Checks if user is authenticated.
	 *
	 * @return bool
	 */
	public function is_authenticated() {

		return is_user_logged_in();
	}

	/**
	 * Check if user is authenticated and administrator.
	 *
	 * @return bool
	 */
	public function is_authenticated_administrator() {
		return $this->is_authenticated() && $this->is_admin();
	}

	/**
	 * Check if logged user is administrator
	 * @return bool
	 */
	public function is_admin() {
		$user = wp_get_current_user();

		if ( in_array( UserRole::ADMINISTRATOR, $user->roles, true ) ) {
			return true;
		}

		return false;
	}
}
