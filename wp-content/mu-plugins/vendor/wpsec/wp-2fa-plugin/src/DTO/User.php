<?php

namespace Wpsec\twofa\DTO;

/**
 * DTO for WordPress user.
 *
 * @package Wpsec
 * @subpackage Wpsec/DTO
 */
class User {

	/**
	 * The unique identifier of the user.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      int  $id.
	 */
	private $id;

	/**
	 * Is 2fa app set up for this user.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      bool $is_2fa_app_enabled
	 */
	private $is_2fa_app_enabled;

	/**
	 * Is 2fa yubikey set up for this user.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      bool $is_2fa_app_enabled
	 */
	private $is_2fa_yubikey_enabled;

	/**
	 * Is 2fa mail set up for this user.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      bool $is_2fa_mail_enabled
	 */
	private $is_2fa_mail_enabled;

	/**
	 * Role of this user.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $role
	 */
	private $role;

	/**
	 * Username of this user.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $role
	 */
	private $username;

	/**
	 * Username of this user.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      bool $is_current_user_this_user
	 */
	private $is_current_user_this_user;

	/**
	 * Avatar of this user.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $avatar
	 */
	private $avatar;

	/**
	 * Constructor.
	 *
	 * @param int $id
	 * @param bool $is_2fa_yubikey_enabled
	 * @param bool $is_2fa_app_enabled
	 * @param bool $is_2fa_mail_enabled
	 * @param string $role
	 * @param string $username
	 * @param bool $is_current_user_this_user
	 */
	public function __construct( $id, $is_2fa_yubikey_enabled, $is_2fa_app_enabled, $is_2fa_mail_enabled, $role, $username, $is_current_user_this_user, $avatar ) {
		$this->id                        = $id;
		$this->is_2fa_yubikey_enabled    = $is_2fa_yubikey_enabled;
		$this->is_2fa_app_enabled        = $is_2fa_app_enabled;
		$this->is_2fa_mail_enabled       = $is_2fa_mail_enabled;
		$this->role                      = $role;
		$this->username                  = $username;
		$this->is_current_user_this_user = $is_current_user_this_user;
		$this->avatar                    = $avatar;
	}

	/**
	 * @return string
	 */
	public function get_avatar() {
		return $this->avatar;
	}

	/**
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * @return bool
	 */
	public function is_2fa_app_enabled() {
		return $this->is_2fa_app_enabled;
	}

	/**
	 * @return bool
	 */
	public function is_2fa_yubikey_enabled() {
		return $this->is_2fa_yubikey_enabled;
	}

	/**
	 * @return bool
	 */
	public function is_2fa_mail_enabled() {
		return $this->is_2fa_mail_enabled;
	}

	/**
	 * @return string
	 */
	public function get_role() {
		return $this->role;
	}

	/**
	 * @return string
	 */
	public function get_username() {
		return $this->username;
	}

	/**
	 * @return bool
	 */
	public function is_current_user_this_user() {
		return $this->is_current_user_this_user;
	}
}
