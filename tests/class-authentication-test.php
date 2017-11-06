<?php

use WPUP\TwoFactory\Authentication;

class Authentication_Test extends \WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
		$this->class = new Authentication;
	}

	public function tearDown() {
		parent::tearDown();
		unset( $this->class );
	}

	public function test_authenticate() {
		$user_id = $this->factory->user->create();
		$user = get_user_by( 'ID', $user_id );

		// Not enabled.
		$output = $this->class->authenticate( $user, $user->user_login, '' );
		$this->assertSame( $user, $output );

		// Invalid 2FA code.
		update_user_option( $user_id, '2fa_enabled', 'on' );
		$output = $this->class->authenticate( $user, $user->user_login, '' );
		$this->assertNotFalse( strpos( $output->get_error_message(), 'Invalid 2FA code' ) );

		// Invalid 2FA secret.
		$_POST['2fa_code'] = 'XXX';
		$output = $this->class->authenticate( $user, $user->user_login, '' );
		$this->assertNotFalse( strpos( $output->get_error_message(), 'Invalid 2FA secret' ) );

		// Invalid 2FA code.
		update_user_option( $user_id, '2fa_secret', 'ADUMJO5634NPDEKW' );
		$output = $this->class->authenticate( $user, $user->user_login, '' );
		$this->assertNotFalse( strpos( $output->get_error_message(), 'The 2FA code is incorrect' ) );
	}
}
