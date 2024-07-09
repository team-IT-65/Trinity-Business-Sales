<?php
/**
 * The PublishGuideServiceProvider class.
 *
 * @package GoDaddy
 */

namespace GoDaddy\WordPress\Plugins\Launch\PublishGuide;

use GoDaddy\WordPress\Plugins\Launch\Helper;
use GoDaddy\WordPress\Plugins\Launch\ServiceProvider;

require_once ABSPATH . 'wp-admin/includes/plugin.php';

/**
 * The PublishGuideServiceProvider class.
 */
class PublishGuideServiceProvider extends ServiceProvider {
	const APP_CONTAINER_CLASS     = 'gdl-publish-guide';
	const PUBLISH_GUIDE_BTN_CLASS = 'gdl-publish-guide-btn';
	const INTERACTED_SETTINGS_KEY = 'gdl_publish_guide_interacted';
	const OPTOUT_SETTINGS_KEY     = 'gdl_publish_guide_opt_out';

	const GUIDE_ITEMS = array(
		GuideItems\SiteInfo::class,
		GuideItems\SiteMedia::class,
		GuideItems\SiteContent::class,
		GuideItems\SEO::class,
		GuideItems\SiteDesign::class,
		GuideItems\AddDomain::class,
	);

	/**
	 * This method will be used for hooking into WordPress with actions/filters.
	 *
	 * @return void
	 */
	public function boot() {
		// Bail early if site is not eligible for Publish Guide.
		if (
			! current_user_can( 'activate_plugins' ) ||
			$this->is_excluded_admin_page() ||
			! $this->has_export_uid()
		) {
			return;
		}

		$this->disable_welcome_guide();

		$build_file_slug = 'publish-guide';
		$build_file_path = $this->app->basePath( 'build/' . $build_file_slug . '.asset.php' );

		$asset_file = file_exists( $build_file_path )
		? include $build_file_path
		: array(
			'dependencies' => array(),
			'version'      => $this->app->version(),
		);

		$enqueue_handle = __NAMESPACE__ . $build_file_slug;

		add_action(
			is_admin() ? 'admin_enqueue_scripts' : 'wp_enqueue_scripts',
			function( $hook_suffix ) use ( $build_file_slug, $asset_file, $enqueue_handle ) {
				/*
				 * When the publish guide is loaded on theme-install.php the theme previewer doesn't work.
				 * @see https://godaddy.slack.com/archives/C033XGA8FK7/p1693263843042239
				 */
				if ( 'theme-install.php' === $hook_suffix ) {
					return;
				}

				wp_enqueue_media();

				$is_migrated = defined( 'GD_MIGRATED_SITE' ) ? constant( 'GD_MIGRATED_SITE' ) : false;

				$edit_post_script_index = array_search( 'wp-edit-post', $asset_file['dependencies'], true );
				if ( ! is_admin() && false !== $edit_post_script_index && true === $is_migrated ) {
					unset( $asset_file['dependencies'][ $edit_post_script_index ] );
				};

				wp_enqueue_script(
					$enqueue_handle . '-script',
					$this->app->baseUrl( 'build/' . $build_file_slug . '.js' ),
					$asset_file['dependencies'],
					$asset_file['version'],
					$build_file_slug,
					true
				);

				wp_enqueue_style(
					$enqueue_handle,
					$this->app->baseUrl( 'build/' . $build_file_slug . '.css' ),
					array( 'wp-components' ),
					$asset_file['version']
				);

				wp_localize_script(
					$enqueue_handle . '-script',
					'gdvPublishGuideDefaults',
					array_merge(
						array(
							'appContainerClass'      => self::APP_CONTAINER_CLASS,
							'page'                   => $hook_suffix,
							'userId'                 => get_current_user_id(),
							'optionInteracted'       => self::INTERACTED_SETTINGS_KEY,
							'optionOptOut'           => self::OPTOUT_SETTINGS_KEY,
							'isOptOut'               => get_option( self::OPTOUT_SETTINGS_KEY ),
							'shouldUseReact18Syntax' => is_wp_version_compatible( '6.2' ) ? 'true' : 'false',
						)
					)
				);

				// Localize state of conditions used for GuideItems.
				wp_localize_script(
					$enqueue_handle . '-script',
					'gdvLinks',
					(array) apply_filters(
						'gdv_admin_links',
						array(
							'admin'             => get_admin_url(),
							'changeDomain'      => $this->get_change_domain_uri(),
							'editorRedirectUrl' => $this->editor_redirect_url(),
							'seoRedirectUrl'    => $this->yoast_seo_redirect_url(),
						)
					)
				);

				wp_set_script_translations(
					$enqueue_handle . '-script',
					'godaddy-launch',
					$this->app->basePath( 'languages' )
				);

				add_action(
					is_admin() ? 'admin_footer' : 'wp_footer',
					function() {
						printf( '<div id="%s"></div>', esc_attr( self::APP_CONTAINER_CLASS ) );
					}
				);
			}
		);

		// Register PublishGuide/GuideItems and their completed state.
		$guide_items           = self::GUIDE_ITEMS;
		$guide_items_localized = array();

		foreach ( $guide_items as $guide_item ) {
			$guide_item_object = $this->app->make( $guide_item );

			// Check if option has timestamp of completion before additional conditions.
			$option_value = ! empty( get_option( $guide_item_object->option_name() ) );
			$is_complete  = $option_value ? $option_value : $guide_item_object->is_complete();

			// send publish guide step status to NUX API only once.
			if ( empty( get_option( $guide_item_object->option_name() . '_nux_reported' ) ) && $guide_item_object->is_complete() ) {
				$this->publish_guide_step_notify_nux_api(
					$guide_item_object,
					get_option( $guide_item_object->option_name() )
				);
			}

			$class_name_parts                                  = explode( '\\', $guide_item );
			$guide_items_localized[ end( $class_name_parts ) ] = array(
				'default'       => $is_complete,
				'enabled'       => $guide_item_object->is_enabled(),
				'propName'      => $guide_item_object->option_name(),
				'milestoneName' => $guide_item_object->milestone_name(),
			);

			// Register the setting so we can use useEntityProps.
			add_action(
				'init',
				function() use ( $guide_item_object ) {
					register_setting(
						$guide_item_object->option_name(),
						$guide_item_object->option_name(),
						array(
							'show_in_rest' => true,
							'default'      => '',
							'type'         => 'string',
						)
					);

					// Initialize the option.
					if ( $guide_item_object->is_enabled() ) {
						add_option(
							$guide_item_object->option_name(),
							$guide_item_object->is_complete() ? time() : ''
						);
					}
				}
			);

			// If the value passed is boolean true, change the value to a timestamp before it's saved.
			add_filter(
				"pre_update_option_{$guide_item_object->option_name()}",
				array( Helper::class, 'update_option_convert_true_to_timestamp' )
			);

			// When pulling the value, convert back to boolean true.
			add_filter(
				"option_{$guide_item_object->option_name()}",
				array( Helper::class, 'get_skipped_or_boolean_as_string' )
			);
		}

		// Localize GuideItems.
		add_action(
			is_admin() ? 'admin_enqueue_scripts' : 'wp_enqueue_scripts',
			function() use ( $enqueue_handle, $guide_items_localized ) {
				wp_localize_script(
					$enqueue_handle . '-script',
					'gdlPublishGuideItems',
					$guide_items_localized
				);
			}
		);

		// Evaluate the guide items completion status.
		add_action(
			'init',
			function() use ( $guide_items_localized ) {
				// Count 'enabled' items for total.
				$total = count(
					array_filter(
						$guide_items_localized,
						function( $guide_item ) {
							return $guide_item['enabled'];
						}
					)
				);

				// The 'default' takes into account is_complete or existence of option with timestamp value.
				$completed = count(
					array_filter(
						$guide_items_localized,
						function( $guide_item ) {
							return $guide_item['default'];
						}
					)
				);

				if ( $total === $completed ) {
					add_option( 'gdl_all_tasks_completed', time() );
				}
			}
		);

		add_filter( 'pre_set_theme_mod_custom_logo', array( $this, 'sync_site_logo_to_theme_mod' ) );
		add_filter( 'theme_mod_custom_logo', array( $this, 'override_custom_logo_theme_mod' ) );
		add_action( 'rest_api_init', array( $this, 'register_site_logo_setting' ), 10 );
		add_action( 'rest_api_init', array( $this, 'register_publish_guide_interacted' ) );
		add_action( 'rest_api_init', array( $this, 'register_publish_guide_optout' ) );
		add_action(
			'rest_api_init',
			function() use ( $guide_items ) {
				register_rest_route(
					'gdl/v1',
					'/milestone/(?P<milestone_name>[a-zA-Z0-9_-]+)',
					array(
						'methods'             => \WP_REST_Server::EDITABLE,
						'permission_callback' => function () {
							// See https://wordpress.org/support/article/roles-and-capabilities/#activate_plugins.
							return current_user_can( 'activate_plugins' );
						},
						'show_in_index'       => false,
						'callback'            => array( $this, 'publish_guide_step_status_update' ),
						'args'                => array(
							'status'         => array(
								'required' => true,
							),
							'milestone_name' => array(
								'validate_callback' => function( $param ) use ( $guide_items ) {
									return in_array(
										$param,
										array_map(
											function( $guide_item ) {
												$guide_item_object = $this->app->make( $guide_item );
												return $guide_item_object->milestone_name();
											},
											$guide_items,
										),
										true
									);
								},
							),
						),
					)
				);
			}
		);
	}

	/**
	 * This method will be used to bind things to the container.
	 *
	 * @return void
	 */
	public function register() {}

	/**
	 * Update the status of a milestone step.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response
	 */
	public function publish_guide_step_status_update( $request ) {
		$milestone_name    = $request->get_param( 'milestone_name' );
		$guide_item_object = $this->get_guide_item_by_milestone_name( $milestone_name );
		$status            = $request->get_param( 'status' );
		$response          = $this->publish_guide_step_notify_nux_api( $guide_item_object, $status );

		return $response;
	}

	/**
	 * Publish milestone step to nux api
	 *
	 * @param object $guide_item_object The name of the milestone.
	 * @param string $status The status of the milestone.
	 * @return WP_REST_Response
	 */
	public function publish_guide_step_notify_nux_api( $guide_item_object, $status ) {
		$option_name = $guide_item_object->option_name();

		if ( ! empty( get_option( $option_name . '_nux_reported' ) ) ) {
			return;
		}

		$domain = defined( 'GD_TEMP_DOMAIN' ) ? GD_TEMP_DOMAIN : Helper::domain();
		$url    = Helper::wpnux_api_base() . '/milestones/' . $guide_item_object->milestone_name();

		$body = Helper::get_default_nux_api_request_body();

		$body['domain'] = $domain;
		$body['status'] = 'skipped' === $status ? 'skipped' : 'complete';

		$remote_post_raw = $this->perform_remote_api_post( $url, $body );
		$post_response   = $this->format_remote_post_response( $remote_post_raw );

		add_option( $option_name . '_nux_reported', time() );

		return $post_response;
	}

	/**
	 * Does the site have a export uid?
	 *
	 * @return bool
	 */
	public function has_export_uid() {
		return $this->is_local_env() ? true : ! empty( get_option( 'wpnux_export_uid', false ) );
	}

	/**
	 * Is the site in a local environment (and not a phpunit test environment)?
	 *
	 * @return bool
	 */
	public function is_local_env() {
		return 'local' === wp_get_environment_type() && empty( $_ENV['WP_PHPUNIT__TESTS_CONFIG'] );
	}

	/**
	 * Excluded admin pages on which the guide is not loaded.
	 *
	 * @return bool
	 */
	public function has_migrated_constant() {
		return defined( 'GD_MIGRATED_SITE' ) && true === constant( 'GD_MIGRATED_SITE' );
	}

	/**
	 * Excluded admin pages on which the guide is not loaded.
	 *
	 * @return bool
	 */
	public function is_excluded_admin_page() {
		global $pagenow;

		if ( empty( $pagenow ) ) {
			return false;
		}

		// Exclude Avada theme live site builder.
		if ( filter_input( INPUT_GET, 'fb-edit', FILTER_VALIDATE_BOOLEAN ) ) {
			return true;
		}

		// Pages specific to WooCommerce.
		$page_arg = empty( $_GET['page'] ) ? '' : \sanitize_key( $_GET['page'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( \is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			if ( 'admin.php' === $pagenow && ! empty( $page_arg ) ) {
				$excluded_pages = array(
					'wc-admin',
					'coupons-moved',
					'wc-reports',
					'wc-settings',
					'wc-status',
					'wc-addons',
				);

				if ( \in_array( $page_arg, $excluded_pages, true ) ) {
					return true;
				}
			}

			$post_type_arg = empty( $_GET['post_type'] ) ? '' : \sanitize_key( $_GET['post_type'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( in_array( $pagenow, array( 'edit.php', 'post-new.php', 'edit-tags.php', 'term.php' ), true ) && ! empty( $post_type_arg ) ) {
				$excluded_types = array(
					'product',
					'shop_order',
					'shop_coupon',
				);

				if ( \in_array( $post_type_arg, $excluded_types, true ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Overrides the custom logo with a site logo, if the option is set.
	 *
	 * @param string $custom_logo The custom logo set by a theme.
	 *
	 * @return string The site logo if set.
	 */
	public function override_custom_logo_theme_mod( $custom_logo ) {
		$sitelogo = get_option( 'sitelogo' );
		return false === $sitelogo ? $custom_logo : $sitelogo;
	}

	/**
	 * Syncs the site logo with the theme modified logo.
	 *
	 * @param string $custom_logo The custom logo set by a theme.
	 *
	 * @return string The custom logo.
	 */
	public function sync_site_logo_to_theme_mod( $custom_logo ) {
		if ( $custom_logo ) {
			update_option( 'sitelogo', $custom_logo );
		}
		return $custom_logo;
	}

	/**
	 * Register a core site setting for a site logo
	 */
	public function register_site_logo_setting() {
		register_setting(
			'general',
			'sitelogo',
			array(
				'show_in_rest' => array(
					'name' => 'sitelogo',
				),
				'type'         => 'string',
				'description'  => __( 'Site logo.', 'godaddy-launch' ),
			)
		);
	}

	/**
	 * Register a core site setting for "interacted" state.
	 */
	public function register_publish_guide_interacted() {
		register_setting(
			self::INTERACTED_SETTINGS_KEY,
			self::INTERACTED_SETTINGS_KEY,
			array(
				'show_in_rest' => true,
				'default'      => false,
				'type'         => 'boolean',
			)
		);

		// Initialize the option.
		add_option( self::INTERACTED_SETTINGS_KEY, false );
	}

	/**
	 * Register a core site setting for "opt-out" state
	 */
	public function register_publish_guide_optout() {
		register_setting(
			self::OPTOUT_SETTINGS_KEY,
			self::OPTOUT_SETTINGS_KEY,
			array(
				'show_in_rest' => true,
				'default'      => false,
				'type'         => 'boolean',
			)
		);

		// Initialize the option.
		add_option( self::OPTOUT_SETTINGS_KEY, false );
	}

	/**
	 * Get the URI for change domain page in MYH. This solution requires a temp domain set from gd-config.php and will return
	 * replacer short-code without it. With the short-code we can infer the temp domain on the JS side.
	 *
	 * @return string The URI for the change domain page in MYH.
	 */
	public function get_change_domain_uri() {
		if ( ! defined( 'GD_TEMP_DOMAIN' ) ) {
			define( 'GD_TEMP_DOMAIN', false );
		}

		return GD_TEMP_DOMAIN
			? 'https://host.godaddy.com/mwp/siteLookup/?domain=' . GD_TEMP_DOMAIN . '&path=changedomain'
			: 'https://host.godaddy.com/mwp/siteLookup/?domain={{DOMAIN}}&path=changedomain';
	}

	/**
	 * Determine how to redirect to the editor screen to perform various tasks.
	 *
	 * @return string
	 */
	private function editor_redirect_url() {
		// Bail if we're on a block editor screen.
		if ( false !== strpos( esc_url_raw( $_SERVER['REQUEST_URI'] ), 'wp-admin/post.php' ) || false !== strpos( esc_url_raw( $_SERVER['REQUEST_URI'] ), 'wp-admin/post-new.php' ) ) {
			return;
		}

		// If page_on_front is set, return edit url to that page.
		if ( 'page' === get_option( 'show_on_front' ) ) {
			$page_on_front = get_option( 'page_on_front' );

			if ( ! empty( $page_on_front ) ) {
				return get_edit_post_link( $page_on_front );
			}
		}

		$wp_query = new \WP_Query();

		// If we have a published page, return edit url to that page.
		$first_published_page = $wp_query->query(
			array(
				'fields'         => 'ids',
				'order'          => 'ASC',
				'orderby'        => 'ID',
				'post_status'    => array( 'publish' ),
				'post_type'      => array( 'page' ),
				'posts_per_page' => 1,
			)
		);
		if ( ! empty( $first_published_page ) ) {
			$edit_url = get_edit_post_link( end( $first_published_page ) );
			wp_reset_postdata();
			return $edit_url;
		}

		// If we have a published post, return edit url to that post.
		$first_published_post = $wp_query->query(
			array(
				'fields'         => 'ids',
				'order'          => 'ASC',
				'orderby'        => 'ID',
				'post_status'    => array( 'publish' ),
				'post_type'      => array( 'post' ),
				'posts_per_page' => 1,
			)
		);
		if ( ! empty( $first_published_post ) ) {
			$edit_url = get_edit_post_link( end( $first_published_post ) );
			wp_reset_postdata();
			return $edit_url;
		}

		wp_reset_postdata();

		// Return create new page url by default.
		return admin_url( '/post-new.php?post_type=page' );
	}

	/**
	 * Determine how to redirect to the Yoast SEO screen to perform various tasks.
	 *
	 * @return string
	 */
	private function yoast_seo_redirect_url() {
		return admin_url( '/admin.php?page=wpseo_dashboard#top#first-time-configuration' );
	}

	/**
	 * Get the guide item by milestone name
	 *
	 * @param string $milestone_name The name of the milestone.
	 * @return object
	 */
	private function get_guide_item_by_milestone_name( $milestone_name ) {
		$guide_items = self::GUIDE_ITEMS;

		$found_guide_item = null;

		foreach ( $guide_items as $guide_item ) {
			$guide_item_object = $this->app->make( $guide_item );

			$guide_item_milestone_name = $guide_item_object->milestone_name();

			if ( $guide_item_milestone_name === $milestone_name ) {
				$found_guide_item = $guide_item_object;
			}
		}

		return $found_guide_item;
	}

	/**
	 * Disable the block editor's welcome guide.
	 */
	protected function disable_welcome_guide() {
		$user_id  = get_current_user_id();
		$meta_key = 'wp_persisted_preferences';

		$user_preferences = get_user_meta( $user_id, $meta_key, true );

		if ( empty( $user_preferences ) ) {
			$user_preferences = array();
		}

		if ( ! array_key_exists( 'core/edit-post', $user_preferences ) ) {
			$user_preferences['core/edit-post'] = array();
		}

		if ( ! array_key_exists( 'welcomeGuide', $user_preferences['core/edit-post'] ) ) {
			$user_preferences['core/edit-post']['welcomeGuide'] = false;
			update_user_meta( $user_id, $meta_key, $user_preferences );
		}
	}
}
