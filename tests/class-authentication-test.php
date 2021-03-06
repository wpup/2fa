<?php

use WPUP\TwoFactor\Authentication;
use WPUP\TwoFactor\Crypto;
use PragmaRX\Recovery\Recovery;

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
		update_user_option( $user_id, 'two_fa_enabled', 'on', true );
		$output = $this->class->authenticate( $user, $user->user_login, '' );
		$this->assertNotFalse( strpos( $output->get_error_message(), 'Invalid 2FA code' ) );

		// Invalid 2FA secret.
		$_POST['two_fa_code'] = 'XXX';
		$output = $this->class->authenticate( $user, $user->user_login, '' );
		$this->assertNotFalse( strpos( $output->get_error_message(), 'Invalid 2FA secret' ) );

		// Invalid 2FA code.
		update_user_option( $user_id, 'two_fa_secret', Crypto::encrypt( 'ADUMJO5634NPDEKW' ), true );
		$output = $this->class->authenticate( $user, $user->user_login, '' );
		$this->assertNotFalse( strpos( $output->get_error_message(), 'The 2FA code is incorrect' ) );
	}

	public function test_authenticate_recovery_codes() {
		$user_id = $this->factory->user->create();
		$user = get_user_by( 'ID', $user_id );
		$codes = ( new Recovery )->setChars(5)->setCount(10)->toArray();

		update_user_option( $user_id, 'two_fa_enabled', 'on', true );
		update_user_option( $user_id, 'two_fa_secret', Crypto::encrypt( 'ADUMJO5634NPDEKW' ), true );
		update_user_option( $user_id, 'two_fa_recovery_codes', maybe_serialize( array_map( function ($code) {
			return password_hash($code, PASSWORD_DEFAULT);
		}, $codes ) ), true );

		$this->assertSame( 10, count( $codes ) );

		$_POST['two_fa_code'] = 'XXXXX-XXXXX';
		$output = $this->class->authenticate( $user, $user->user_login, '' );
		$this->assertNotFalse( strpos( $output->get_error_message(), 'The recovery code is incorrect' ) );

		$_POST['two_fa_code'] = array_shift( $codes );
		$output = $this->class->authenticate( $user, $user->user_login, '' );
		$this->assertSame( $user->ID, $output->ID );

		$hashes = two_fa_get_user_option( 'two_fa_recovery_codes', $user_id );
		$hashes = maybe_unserialize( $hashes );
		$this->assertSame( 9, count( $hashes ) );

		foreach ( $hashes as $index => $hash ) {
			if ( password_verify( $_POST['two_fa_code'], $hash ) ) {
				$this->assertFalse( true );
			}
		}
	}
}
