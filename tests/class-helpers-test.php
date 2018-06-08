<?php

class Helpers_Test extends \WP_UnitTestCase {
	public function test_two_fa_get_user_option() {
		$user_id = $this->factory->user->create();

		update_user_option( $user_id, 'name', 'test', true );
		$this->assertSame( 'test', two_fa_get_user_option( 'name', $user_id ) );
	}
}
