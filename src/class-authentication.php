<?php

namespace WPUP\TwoFactory;

use PragmaRX\Google2FA\Google2FA;
use WP_Error;

class Authentication {

	/**
	 * Google 2FA instance.
	 *
	 * @var \PragmaRX\Google2FA\Google2FA
	 */
	protected $google2fa;

	/**
	 * Authentication construct.
	 */
	public function __construct() {
		$this->setup_hooks();
		$this->setup_properties();
	}

	/**
	 * Authenticate user.
	 *
	 * @param  \WP_User $user
	 * @param  string   $username
	 * @param  string   $password
	 *
	 * @return \WP_User|\WP_Error|null
	 */
	public function authenticate( $user, $username = '', $password = '' ) {
		// Store user so we can use it later.
		$old_user = $user;

		$user = get_user_by( 'login', $username );

		// Bail if bad user.
		if ( ! isset( $user->ID ) ) {
			return $old_user;
		}

		// Bail if 2FA is not enabled.
		if ( trim( get_user_option( '2fa_enabled', $user->ID ) !== 'on' ) ) {
			return $old_user;
		}

		// Bail if the 2FA code is empty.
		if ( empty( $_POST['2fa_code'] ) ) {
			return new WP_Error( '2fa_invalid_code', __( '<strong>ERROR</strong>: Invalid 2FA code' ) );
		}

		$secret = get_user_option( '2fa_secret', $user->ID );

		// Bail if the 2FA secret is incorrect.
		if ( empty( $secret ) ) {
			return new WP_Error( '2fa_invalid_secret', __( '<strong>ERROR</strong>: Invalid 2FA secret' ) );
		}

		$secret = Crypto::decrypt( $secret );

		// Bail if the 2FA code is incorrect.
		if ( ! $this->google2fa->verifyKey( $secret, trim( $_POST['2fa_code'] ) ) ) {
			return new WP_Error( '2fa_invalid_code', __( '<strong>ERROR</strong>: The 2FA code is incorrect', '2fa' ) );
		}

		return $user;
	}

	/**
	 * Output 2FA HTML for login form.
	 */
	public function login_form() {
		?>
		<p>
			<label title="<?php echo esc_html__( 'If you don\'t have Two-Factory Authenticator enabled for your account, leave this field empty.' ); ?>">
				<?php echo esc_html__( '2FA Code' ); ?><span id="2fa-info"></span> <small>(Leave blank if not setup)</small>
				<input type="text" name="2fa_code" id="2fa_code" class="input" size="20" style="ime-mode: inactive;" autocomplete="off">
			</label>
		</p>
		<?php
	}

	/**
	 * Setup WordPress hooks.
	 */
	protected function setup_hooks() {
		add_action( 'login_form', [$this, 'login_form'] );
		add_filter( 'authenticate', [$this, 'authenticate'], 99, 3 );
	}

	/**
	 * Setup class properties.
	 */
	protected function setup_properties() {
		$this->google2fa = new Google2FA;
	}
}
