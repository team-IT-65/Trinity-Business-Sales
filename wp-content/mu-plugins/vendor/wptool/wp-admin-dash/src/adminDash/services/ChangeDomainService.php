<?php

namespace Wptool\adminDash\services;
use WPaaS\Plugin;
use Wptool\adminDash\utils\ClassUtils;

class ChangeDomainService {

	/**
	 * Provides change domain redirection url
	 *
	 * @return string
	 */
	public function get_domain_change_url() {
		$url = '';
		if ( ClassUtils::check_methods_exsist( 'WPaaS\Plugin', array( 'is_temp_domain', 'is_staging_site', 'account_url' ) )
			&& Plugin::is_temp_domain() && ! Plugin::is_staging_site() ) {

			$url = str_replace( '&#038;', '&', Plugin::account_url( 'changedomain' ) );
		}

		return $url;
	}
}
