<?php

namespace Wptool\adminDash\services;


use Wptool\adminDash\constants\ToggleStatus;
use Wptool\adminDash\exceptions\CaptchaDisabledException;

class CaptchaService {

	/**
	 * This function update wp_options, if the option does not exist, it will be created.
	 * Otherwise, it will set opposite status from one in database.
	 * Return mwp captcha status, zero or one as string.
	 *
	 * @return bool
	 */
	public function toggle_wpsec_captcha() {
		$captcha = get_option( 'wpsec_captcha_enabled' );
		$status  = ToggleStatus::ENABLED;

		if ( ToggleStatus::ENABLED === $captcha ) {
			$status = ToggleStatus::DISABLED;
		}
		update_option( 'wpsec_captcha_enabled', $status );

		return ToggleStatus::ENABLED === $status;
	}

	/**
	 *
	 * This function return either captcha is disabled or not.
	 * Captcha should be considered to be disabled only if option is present and set to 0.
	 *
	 * @return bool
	 *
	 */
	public function is_wpsec_captcha_enabled() {
		return get_option( 'wpsec_captcha_enabled', ToggleStatus::ENABLED ) === ToggleStatus::ENABLED;
	}

	/**
	 *
	 *  This function update value in wp_options. If the option does not exist, it will be created
	 *  By default captcha is enabled.
	 * @return bool
	 * @throws CaptchaDisabledException
	 */
	public function toggle_wpsec_captcha_comment() {
		if ( ! $this->is_wpsec_captcha_enabled() ) {
			throw new CaptchaDisabledException();
		}

		$comment_captcha = get_option( 'wpsec_comment_captcha_enabled' );
		$status          = ToggleStatus::ENABLED;

		if ( ToggleStatus::ENABLED === $comment_captcha ) {
			$status = ToggleStatus::DISABLED;
		}

		update_option( 'wpsec_comment_captcha_enabled', $status );

		return ToggleStatus::ENABLED === $status;
	}

	/**
	 *
	 * This function return either captcha is enabled for comments or not.
	 * @return bool
	 */
	public function is_wpsec_captcha_comment_enabled() {
		if ( ! $this->is_wpsec_captcha_enabled() ) {
			return false;
		}

		return get_option( 'wpsec_comment_captcha_enabled', ToggleStatus::ENABLED ) === ToggleStatus::ENABLED;
	}


	/**
	 *
	 *  This function update value in wp_options. If the option does not exist, it will be created
	 *  By default captcha is enabled.
	 * @return bool
	 * @throws CaptchaDisabledException
	 */
	public function toggle_wpsec_captcha_login() {
		if ( ! $this->is_wpsec_captcha_enabled() ) {
			throw new CaptchaDisabledException();
		}

		$comment_captcha = get_option( 'wpsec_login_captcha_enabled' );
		$status          = ToggleStatus::ENABLED;

		if ( ToggleStatus::ENABLED === $comment_captcha ) {
			$status = ToggleStatus::DISABLED;
		}

		update_option( 'wpsec_login_captcha_enabled', $status );

		return ToggleStatus::ENABLED === $status;
	}

	/**
	 *
	 * This function return either captcha is enabled for comments or not.
	 * @return bool
	 */
	public function is_wpsec_captcha_login_enabled() {
		if ( ! $this->is_wpsec_captcha_enabled() ) {
			return false;
		}

		return get_option( 'wpsec_login_captcha_enabled', ToggleStatus::ENABLED ) === ToggleStatus::ENABLED;
	}
}
