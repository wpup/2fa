<?php

namespace WPUP\TwoFactory;

use PragmaRX\Google2FA\Google2FA;

class User {

	/**
	 * Google 2FA instance.
	 *
	 * @var \PragmaRX\Google2FA\Google2FA
	 */
	protected $google2fa;

	/**
	 * User constructor.
	 */
	public function __construct() {
		$this->setup_hooks();
		$this->setup_properties();
	}

	/**
	 * Add 2FA column value.
	 *
	 * @param  mixed  $value
	 * @param  string $column
	 * @param  int    $user_id
	 *
	 * @return mixed
	 */
	public function column_value( $value, $column, $user_id ) {
		switch ( $column ) {
			case '2fa':
				return trim( get_user_option( '2fa_enabled', $user_id ) ) === 'on' && ! empty( get_user_option( '2fa_secret', $user_id ) )
					? esc_html__( 'Yes', '2fa' )
					: esc_html__( 'No', '2fa' );
			default:
				break;
		}

		return $value;
	}

	/**
	 * Add 2FA column.
	 *
	 * @param  array $column
	 *
	 * @return array
	 */
	public function columns( $column ) {
		$column['2fa'] = esc_html__( '2FA', '2fa' );

		return $column;
	}

	/**
	 * Output user fields for 2FA.
	 *
	 * @param  \WP_User $user
	 */
	public function fields( $user ) {
		$hidden = trim( get_user_option( '2fa_enabled', $user->ID ) ) !== 'on';
		$secret = get_user_option( '2fa_secret', $user->ID );
		$secret = empty( $secret ) ? $this->google2fa->generateSecretKey() : Crypto::decrypt( $secret );
		?>
		<h3><?php echo esc_html__( 'Two-Factory Authentication Management', '2fa' ); ?></h3>
		<table class="form-table">
			<tr>
				<th><label for="2fa_enabled"><?php echo esc_html__( 'Enable' ); ?></label></th>
				<td>
					<input type="checkbox" name="2fa_enabled" id="2fa_enabled" value="on" <?php checked( 'on', get_user_option( '2fa_enabled', $user->ID ), true ); ?> />
					<?php wp_nonce_field( '2fa_update', '2fa_nonce' ); ?>
				</td>
			</tr>
			<tr>
				<th class="2fa_qr <?php echo $hidden ? 'hidden' : ''; ?>"><label for="2fa_qr"><?php echo esc_html__( 'QR Barcode', '2fa' ); ?></label></th>
				<td class="2fa_qr <?php echo $hidden ? 'hidden' : ''; ?>">
					<p><?php echo esc_html__( 'Open up your 2FA mobile app and scan the following QR barcode:', '2fa' ); ?></p>
					<img src="<?php echo esc_attr( $this->get_qr_code_url( $user, $secret ) ); ?>" alt="<?php echo esc_html__( 'QR Barcode', '2fa' ); ?>" />
					<input type="hidden" name="2fa_secret" id="2fa_secret" value="<?php echo esc_attr( $secret ); ?>" />
					<p><?php echo esc_html__( 'If your 2FA mobile app does not support QR barcodes, enter in the following number:', '2fa' ); ?><code><?php echo esc_html( $secret ); ?></code></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Get QR-Code for 2FA.
	 *
	 * @param  \WP_User $user
	 * @param string    $secret
	 *
	 * @return string
	 */
	protected function get_qr_code_url( $user, $secret ) {
		if ( function_exists( 'imagecreatetruecolor' ) ) {
			return $this->google2fa->getQRCodeInline(
				get_bloginfo( 'name' ),
				$user->user_email,
				$secret
			);
		}

		return $this->google2fa->getQRCodeGoogleUrl(
			get_bloginfo( 'name' ),
			$user->user_email,
			$secret
		);
	}

	/**
	 * Save fields.
	 *
	 * @param  int $user_id
	 *
	 * @return mixed
	 */
	public function save_fields( $user_id ) {
		// Bail if we can't edit the user.
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}

		// Bail if bad nonce.
		if ( empty( $_POST['2fa_nonce'] ) || ! wp_verify_nonce( $_POST['2fa_nonce'], '2fa_update' ) ) {
			return false;
		}

		$fields = [
			'2fa_enabled',
			'2fa_secret',
		];

		foreach ( $fields as $field ) {
			if ( ! empty( $_POST[$field] ) ) {
				$value = sanitize_text_field( $_POST[$field] );

				if ( $field === '2fa_secret' ) {
					$value = Crypto::encrypt( $value );
				}

				update_user_option( $user_id, $field, $value );
			}
		}
	}

	/**
	 * Setup WordPress hooks.
	 */
	protected function setup_hooks() {
		add_action( 'edit_user_profile', [$this, 'fields'] );
		add_action( 'show_user_profile', [$this, 'fields'] );
		add_action( 'personal_options_update', [$this, 'save_fields'] );
		add_action( 'edit_user_profile_update', [$this, 'save_fields'] );
		add_filter( 'manage_users_columns', [$this, 'columns'] );
		add_filter( 'manage_users_custom_column', [$this, 'column_value'], 10, 3 );
	}

	/**
	 * Setup class properties.
	 */
	protected function setup_properties() {
		$this->google2fa = new Google2FA;
	}
}
