<?php

namespace WPUP\TwoFactor;

class Assets {

	/**
	 * Plugin url.
	 *
	 * @var string
	 */
	protected $plugin_url;

	/**
	 * Assets constructor.
	 */
	public function __construct() {
		$this->setup_hooks();
		$this->setup_properties();
	}

	/**
	 * Enqueue CSS.
	 */
	public function enqueue_css() {
		wp_enqueue_style( '2fa-main', $this->plugin_url . 'assets/main.css', false, null );
	}

	/**
	 * Enqueue JavaScript.
	 */
	public function enqueue_js() {
		wp_enqueue_script( '2fa-main', $this->plugin_url . 'assets/main.js', [
			'jquery',
		], '', true );
	}

	/**
	 * Setup WordPress hooks.
	 */
	protected function setup_hooks() {
		add_action( 'admin_enqueue_scripts', [$this, 'enqueue_css'] );
		add_action( 'admin_enqueue_scripts', [$this, 'enqueue_js'] );
	}

	/**
	 * Setup class properties.
	 */
	protected function setup_properties() {
		$this->plugin_url = plugin_dir_url( __FILE__ );
		$this->plugin_url = is_ssl() ? str_replace( 'http://', 'https://', $this->plugin_url ) : $this->plugin_url;
	}
}
