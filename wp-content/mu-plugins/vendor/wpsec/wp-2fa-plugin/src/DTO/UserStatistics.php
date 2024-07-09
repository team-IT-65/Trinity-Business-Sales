<?php

namespace Wpsec\twofa\DTO;

/**
 * DTO for user statistic.
 *
 * @package Wpsec
 * @subpackage Wpsec/DTO
 */
class UserStatistics {

	/**
	 * Users.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      User[] $users
	 */
	private $users;

	/**
	 * Count of users with 2fa not set up.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      int $not_set_up_2fa_count
	 */
	private $not_set_up_2fa_count;

	/**
	 * @param User[] $users
	 * @param int $not_set_up_2fa_count
	 */
	public function __construct( array $users, $not_set_up_2fa_count ) {
		$this->users                = $users;
		$this->not_set_up_2fa_count = $not_set_up_2fa_count;
	}

	/**
	 * Returns filtered user array
	 * @return User[]
	 */
	public function get_users() {
		return $this->users;
	}

	/**
	 * Returns all users on site
	 * @return User[]
	 */

	public function get_all_users() {
		return get_users();
	}

	/**
	 * @return int
	 */
	public function get_not_set_up_2fa_count() {
		return $this->not_set_up_2fa_count;
	}
}
