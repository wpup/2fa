<?php

use WPUP\TwoFactor\Crypto;

class Crypto_Test extends \WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
		$this->class = new Crypto;
	}

	public function tearDown() {
		parent::tearDown();
		unset( $this->class );
	}

	public function test_crypto() {
		$val = Crypto::encrypt( 'test' );
		$val = Crypto::decrypt( $val );
		$this->assertSame( 'test', $val );
	}
}
