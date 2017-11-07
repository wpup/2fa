<?php

namespace WPUP\TwoFactory;

class Plugin {

	/**
	 * Authentication instance.
	 *
	 * @var \WPUP\TwoFactory\Plugin
	 */
	protected static $instance;

	/**
	 * Get plugin instance.
	 *
	 * @return \WPUP\TwoFactory\Plugin
	 */
	public static function instance() {
		if ( ! isset( static::$instance ) ) {
			static::$instance = new static;
		}

		return static::$instance;
	}

	/**
	 * Plugin constructor.
	 */
	protected function __construct() {
		$this->load_textdomain();
		$this->setup_classes();
		$this->setup_hooks();
	}

	/**
	 * Remove 2FA plugin from updates since it's match another 2FA plugin.
	 *
	 * @param  object $value
	 *
	 * @return void
	 */
	public function filter_plugin_updates( $value ) {
		if ( isset( $value->response['2fa/plugin.php'] ) ) {
			unset( $value->response['2fa/plugin.php'] );
		}

		return $value;
	}

	/**
	 * Load Localisation files.
	 *
	 * Locales found in:
	 * - WP_LANG_DIR/2fa/2fa-LOCALE.mo
	 * - WP_CONTENT_DIR/[mu-]plugins/2fa/languages/2fa-LOCALE.mo
	 */
	protected function load_textdomain() {
		$locale = function_exists( 'get_user_local' ) ? get_user_local() : get_locale();
		$locale = apply_filters( 'plugin_locale', $locale, '2fa' );
		load_textdomain( '2fa', WP_LANG_DIR . '/2fa/2fa-' . $locale . '.mo' );
		$mu_dir = trailingslashit( sprintf( '%s/%s/src', WPMU_PLUGIN_DIR, basename( dirname( __DIR__ ) ) ) );
		$mu_dir = is_dir( $mu_dir ) ? $mu_dir : trailingslashit( __DIR__ );
		load_textdomain( '2fa', $mu_dir . '../languages/2fa-' . $locale . '.mo' );
	}

	/**
	 * Setup classes.
	 *
	 * @return void
	 */
	protected function setup_classes() {
		new Authentication;

		if ( is_admin() ) {
			new Assets;
			new User;
		}
	}

	/**
	 * Setup WordPress hooks.
	 */
	protected function setup_hooks() {
		add_filter( 'site_transient_update_plugins', [$this, 'filter_plugin_updates'] );
	}
}
