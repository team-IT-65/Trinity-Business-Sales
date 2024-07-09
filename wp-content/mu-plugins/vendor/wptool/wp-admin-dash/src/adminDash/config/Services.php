<?php

namespace Wptool\adminDash\config;

use Wptool\adminDash\clients\ga\GAClient;
use Wptool\adminDash\services\AutoUpdateService;
use Wptool\adminDash\services\CacheService;
use Wptool\adminDash\services\GDLoginService;
use Wptool\adminDash\services\MinificationService;
use Wptool\adminDash\services\OnboardingService;
use Wptool\adminDash\services\SiteHealthService;
use Wptool\adminDash\services\SupportService;
use Wptool\adminDash\services\ChangeDomainService;
use Wptool\adminDash\services\CaptchaService;
use Wptool\adminDash\services\CourseService;
use Wptool\adminDash\services\TrackingService;
use Wptool\adminDash\services\SiteTrafficDataService;

class Services {

	public static function get_services() {

		$services = array();

		$services['auto_updates_service'] = function () {
			return new AutoUpdateService();
		};

		$services['cache_service'] = function () {
			return new CacheService();
		};

		$services['site_health_service'] = function () {
			return new SiteHealthService();
		};

		$services['support_service'] = function () {
			return new SupportService();
		};

		$services['change_domain_service'] = function () {
			return new ChangeDomainService();
		};

		$services['captcha_service'] = function () {
			return new CaptchaService();
		};

		$services['course_service'] = function () {
			return new CourseService();
		};

		$services['tracking_service'] = function ( $c ) {
			return new TrackingService( $c['ga_client'] );
		};

		$services['ga_client'] = function() {
			return new GAClient();
		};

		$services['onboarding_service'] = function () {
			return new OnboardingService();
		};

		$services['gd_login_service'] = function () {
			return new GDLoginService();
		};

		$services['site_traffic_data_service'] = function () {
			return new SiteTrafficDataService();
		};

		$services['minification_service'] = function () {
			return new MinificationService();
		};

		return $services;
	}
}
