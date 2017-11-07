<?php

use WPUP\TwoFactory\User;

class User_Test extends \WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
		$this->class = new User;
	}

	public function tearDown() {
		parent::tearDown();
		unset( $this->class );
	}

	public function test_columns() {
		$columns = $this->class->columns( [] );
		$this->assertSame( ['2fa' => '2FA'], $columns );
	}

	public function test_column_value() {
		$user_id = $this->factory->user->create();

		$value = $this->class->column_value( '', '2fa', $user_id );
		$this->assertSame( 'No', $value );

		update_user_option( $user_id, '2fa_enabled', 'on' );
		update_user_option( $user_id, '2fa_secret', 'on' );

		$value = $this->class->column_value( '', '2fa', $user_id );
		$this->assertSame( 'Yes', $value );
	}

	public function test_save_fields() {
		$user_id = $this->factory->user->create( ['user_login' => 'test', 'role' => 'administrator'] );

		wp_set_current_user( $user_id );

		$_POST['2fa_nonce'] = wp_create_nonce( '2fa_update' );
		$_POST['2fa_enabled'] = 'on';
		$_POST['2fa_secret'] = 'XXX';

		$this->class->save_fields( $user_id );

		wp_set_current_user( 0 );

		$this->assertSame( 'on', get_user_option( '2fa_enabled', $user_id ) );
		$this->assertNotSame( 'XXX', get_user_option( '2fa_secret', $user_id ) );
	}
}
