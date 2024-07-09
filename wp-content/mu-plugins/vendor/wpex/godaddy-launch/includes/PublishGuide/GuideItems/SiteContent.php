<?php
/**
 * The SiteContent class.
 *
 * @package GoDaddy
 */

namespace GoDaddy\WordPress\Plugins\Launch\PublishGuide\GuideItems;

/**
 * The SiteContent class.
 */
class SiteContent extends GuideItemAbstract {
	/**
	 * Determines if the guide item should be enabled.
	 *
	 * @return bool
	 */
	public function is_enabled() {
		$wpex_3549_experiment_active = isset( $GLOBALS['wpaas_feature_flag'] ) && $GLOBALS['wpaas_feature_flag']->get_feature_flag_value( 'wpex-3549-remove-site-content-step', false );

		if ( true === $wpex_3549_experiment_active ) {
			return false;
		}

		return ! empty( get_option( 'coblocks_site_content_controls_enabled' ) );
	}

	/**
	 * Return if the guide item has been completed.
	 *
	 * @return bool
	 */
	public function is_complete() {
		if ( get_option( $this->option_name() ) ) {
			return true;
		}

		$conditions = array(
			$this->has_new_content(),
		);

		$has_incomplete = array_filter(
			$conditions,
			function( $val ) {
				return empty( $val );
			}
		);

		return empty( $has_incomplete );
	}

	/**
	 * Returns the option_name of the GuideItem used in the wp_options table.
	 *
	 * @return string
	 */
	public function option_name() {
		return 'gdl_pgi_site_content';
	}

	/**
	 * Returns the milestone name of the GuideItem used in the nux api.
	 *
	 * @return string
	 */
	public function milestone_name() {
		return 'site-content';
	}

	/**
	 * Determines if the site contains content beyond the default.
	 *
	 * @return bool
	 */
	private function has_new_content() {
		$wp_query = new \WP_Query();

		$posts = $wp_query->query(
			array(
				'order'          => 'ASC',
				'orderby'        => 'ID',
				'post_status'    => array( 'draft', 'publish' ),
				'post_type'      => array( 'page', 'post' ),
				'posts_per_page' => 20,
			)
		);

		// Filter default install posts & pages.
		$posts = array_filter(
			$posts,
			function( $post ) {
				$default_posts = array(
					'hello-world',
					'sample-page',
					'privacy-policy',
					'shop',
					'cart',
					'checkout',
					'my-account',
					'refund_returns',
				);

				return ! in_array( $post->post_name, $default_posts, true );
			}
		);

		$wpnux_export_data = json_decode( get_option( 'wpnux_export_data', '{}' ), true );

		if (
			! empty( $wpnux_export_data ) &&
			! empty( $wpnux_export_data['content'] ) &&
			! empty( $wpnux_export_data['content']['posts'] )
		) {
			$excluded_posts = wp_list_pluck( $wpnux_export_data['content']['posts'], 'post_title' );

			// Filter default posts defined in wpnux_export_data.
			$posts = array_filter(
				$posts,
				function( $post ) use ( $excluded_posts ) {
					return ! in_array( $post->post_title, $excluded_posts, true );
				}
			);
		}

		wp_reset_postdata();

		return ! empty( $posts );
	}
}
