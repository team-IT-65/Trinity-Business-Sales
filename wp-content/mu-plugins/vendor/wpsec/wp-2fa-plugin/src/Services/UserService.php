<?php

namespace Wpsec\twofa\Services;

use WP_User_Query;
use Wpsec\twofa\API\TwoFactorApiClient;
use Wpsec\twofa\Constants\GoogleAuthenticatorConstants;
use Wpsec\twofa\Constants\MailAuthConstants;
use Wpsec\twofa\Constants\TwoFactorConstants;
use Wpsec\twofa\Constants\UserConstants;
use Wpsec\twofa\Constants\YubikeyAuthConstants;
use Wpsec\twofa\DTO\User;
use Wpsec\twofa\utils\SiteUtils;
use Wpsec\twofa\utils\UserUtils;

class UserService {

	/**
	 * TwoFactorAuthService instance .
	 ** @var TwoFactorAuthService $tfa_auth_service.
	 */
	private $tfa_auth_service;

	/**
	 * TwoFactorAuthService instance .
	 ** @var TwoFactorApiClient $tfa_api_client.
	 */
	private $tfa_api_client;

	public function __construct( $tfa_auth_service, $tfa_api_client ) {
		$this->tfa_auth_service = $tfa_auth_service;
		$this->tfa_api_client   = $tfa_api_client;
	}


	/**
	 * This function filter user based on passed criteria parameter.
	 * @param string $criteria
	 * @return User[]
	 * @since 1.0.0
	 */
	public function filter_users( $criteria, $roles ) {
		if ( UserConstants::FILTER_BY_2FA_SET_UP === $criteria ) {
			return $this->get_with_2fa_enabled( $roles );
		}
		if ( UserConstants::FILTER_BY_2FA_NOT_SET_UP === $criteria ) {
			return $this->get_with_2fa_disabled( $roles );
		}
		return $this->get_all( $roles );
	}

	/**
	 * This function filter user based on passed criteria parameter
	 * @param $action string
	 * @param $users string[]
	 * @param $validation_method string
	 * @param $code string
	 * @return bool
	 */
	public function bulk_action( $action, $users, $validation_method, $code ) {
		if ( UserConstants::BULK_ACTION_RESTART_2FA !== $action ) {
			return false;
		}
		return $this->restart_2fa( $users, $validation_method, $code );
	}

	/**
	 * Gets users for site page user table.
	 *
	 * @param string $search_username
	 * @return array
	 * @since 1.0.0
	 */
	public function search_by_username( $username, $roles ) {
		$users = new WP_User_Query(
			array(
				'role__in'       => $roles,
				'search'         => '*' . esc_attr( $username ) . '*',
				'search_columns' => array( 'user_login' ),
			)
		);
		return $this->transform( $users->get_results() );
	}

	/**
	 * Returns all available roles and if they are selected or not.
	 * @return string[]
	 */
	public function get_roles() {
		$roles          = array();
		$wp_roles       = wp_roles()->get_names();
		$selected_roles = json_decode( get_option( TwoFactorConstants::WPSEC_2FA_ROLES_SELECTED, json_encode( array() ) ) );
		foreach ( $wp_roles as $wp_role ) {
			if ( in_array( $wp_role, $selected_roles, true ) ) {
				$roles[] = array(
					'selected' => true,
					'name'     => $wp_role,
				);
			} else {
				$roles[] = array(
					'selected' => false,
					'name'     => $wp_role,
				);
			}
		}

		return $roles;
	}

	/**
	 * This function update list of roles
	 * that are forces to use Two-Factor Auth.
	 * @return bool
	 */
	public function select_roles( $selected_roles ) {

		if ( empty( $selected_roles ) ) {
			return update_option( TwoFactorConstants::WPSEC_2FA_ROLES_SELECTED, json_encode( array() ) );
		}
		$all_roles = wp_roles()->get_names();

		$roles_for_save = array();
		foreach ( $selected_roles as $selected_role ) {

			if ( in_array( $selected_role, $all_roles, true ) ) {
				$roles_for_save[] = $selected_role;
			}
		}

		return update_option( TwoFactorConstants::WPSEC_2FA_ROLES_SELECTED, json_encode( $roles_for_save ) );
	}
	/**
	 *  This function deactivates all 2 FA methods for given users.
	 * @param $user_ids string[]
	 * @param $validation_method string
	 * @param $code string
	 * @return bool
	 */
	private function restart_2fa( $user_ids, $validation_method, $code ) {
		$current_user = UserUtils::get_current_user();
		$res          = $this->tfa_api_client->remove_method( SiteUtils::get_site_origin(), $current_user->ID, $validation_method, $user_ids, $code );

		if ( 204 !== wp_remote_retrieve_response_code( $res ) ) {
			return false;
		}

		foreach ( $user_ids as $user_id ) {
			$this->tfa_auth_service->deactivate_all_methods( $user_id );
		}

		return true;
	}

	/**
	 * Gets users with at least one 2fa method set up.
	 *
	 * @return User[]
	 * @since 1.0.0
	 */
	private function get_with_2fa_enabled( $roles = array() ) {
		$users = $this->get_all( $roles );
		foreach ( $users as $key => $user ) {
			if ( ! $user[ YubikeyAuthConstants::VALIDATION_METHOD ] && ! $user[ GoogleAuthenticatorConstants::VALIDATION_METHOD ] && ! $user[ MailAuthConstants::VALIDATION_METHOD ] ) {
				unset( $users[ $key ] );
			}
		}
		return $users;
	}

	/**
	 * Gets users with no 2fa set up.
	 *
	 * @return User[]
	 * @since 1.0.0
	 */
	private function get_with_2fa_disabled( $roles = array() ) {
		$users = $this->get_all( $roles );
		foreach ( $users as $key => $user ) {
			if ( $user[ YubikeyAuthConstants::VALIDATION_METHOD ] || $user[ GoogleAuthenticatorConstants::VALIDATION_METHOD ] || $user[ MailAuthConstants::VALIDATION_METHOD ] ) {
				unset( $users[ $key ] );
			}
		}
		return $users;
	}

	/**
	 * Gets all users.
	 *
	 * @return User[]
	 * @since 1.0.0
	 */
	private function get_all( $roles ) {
		$users = new WP_User_Query(
			array(
				'role__in' => $roles,
			)
		);
		return $this->transform( $users->get_results() );
	}

	/**
	 * This function transform WP User[], take just necessary data
	 *
	 * @param $wp_users \WP_User[]
	 * @return array
	 */
	private function transform( $wp_users ) {
		$users = array();
		foreach ( $wp_users as $wp_user ) {
			$user_2fa_enabled = $this->tfa_auth_service->get_active_2fa_methods( $wp_user->ID );
			$roles            = array();
			if ( ! empty( $wp_user->roles ) ) {
				$roles = $wp_user->roles;
			}

			$users[] = array(
				'id'                                    => $wp_user->ID,
				YubikeyAuthConstants::VALIDATION_METHOD => in_array( YubikeyAuthConstants::AUTH_ACTIVE, $user_2fa_enabled, true ),
				GoogleAuthenticatorConstants::VALIDATION_METHOD => in_array( GoogleAuthenticatorConstants::AUTH_ACTIVE, $user_2fa_enabled, true ),
				MailAuthConstants::VALIDATION_METHOD    => in_array( MailAuthConstants::AUTH_ACTIVE, $user_2fa_enabled, true ),
				'roles'                                 => $roles,
				'username'                              => $wp_user->user_login,
				'avatar'                                => get_avatar_url( $wp_user->ID ),
			);
		}
		return $users;
	}

	/**
	 * This function transform WP User, take just necessary data
	 *
	 * @param $wp_user \WP_User
	 * @return array
	 */
	public function transform_one( $wp_user ) {
		$user_2fa_enabled = $this->tfa_auth_service->get_active_2fa_methods( $wp_user->ID );
		$roles            = array();

		if ( ! empty( $wp_user->roles ) ) {
			$roles = $wp_user->roles;
		}

		$user = array(
			'id'                                    => $wp_user->ID,
			YubikeyAuthConstants::VALIDATION_METHOD => in_array( YubikeyAuthConstants::AUTH_ACTIVE, $user_2fa_enabled, true ),
			GoogleAuthenticatorConstants::VALIDATION_METHOD => in_array( GoogleAuthenticatorConstants::AUTH_ACTIVE, $user_2fa_enabled, true ),
			MailAuthConstants::VALIDATION_METHOD    => in_array( MailAuthConstants::AUTH_ACTIVE, $user_2fa_enabled, true ),
			'roles'                                 => $roles,
			'username'                              => $wp_user->user_login,
			'avatar'                                => get_avatar_url( $wp_user->ID ),
		);

		return $user;
	}
}

