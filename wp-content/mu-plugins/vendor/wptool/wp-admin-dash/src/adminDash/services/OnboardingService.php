<?php

namespace Wptool\adminDash\services;

use Wptool\adminDash\constants\OnboardingConstants;

class OnboardingService {
	/**
	 * This function update wp_options, if the option does not exist, it will be created.
	 * Otherwise, it will set opposite status from one in database.
	 *
	 * @return bool
	 */
	public function complete_onboarding() {
		return update_option( OnboardingConstants::OPTION_KEY, OnboardingConstants::COMPLETED );
	}

	/**
	 * This function retrieves current onboarding status for a user
	 *
	 * @return bool
	 */
	public function is_user_onboarded() {
		return get_option( OnboardingConstants::OPTION_KEY, OnboardingConstants::NOT_COMPLETED ) === OnboardingConstants::COMPLETED;
	}
}
