<?php

namespace WPUP\TwoFactor;

class Crypto {

	/**
	 * Encryption method.
	 */
	const METHOD = 'aes-256-ctr';

	/**
	 * Get the crypto key.
	 *
	 * @return string
	 */
	protected static function key() {
		if ( ! defined( 'TWO_FA_KEY' ) ) {
			return defined( 'AUTH_KEY' ) ? AUTH_KEY : '';
		}

		return constant( 'TWO_FA_KEY' );
	}

	/**
	 * Encrypt value.
	 *
	 * @param  string $value
	 *
	 * @return string
	 */
	public static function encrypt( $value ) {
		$nonce_size = openssl_cipher_iv_length( static::METHOD );
		$nonce      = openssl_random_pseudo_bytes( $nonce_size );

		$ciphertext = openssl_encrypt(
			$value,
			static::METHOD,
			static::key(),
			OPENSSL_RAW_DATA,
			$nonce
		);

		return base64_encode( $nonce . $ciphertext );
	}

	/**
	 * Decrypt value.
	 *
	 * @param  string $value
	 *
	 * @return string
	 */
	public static function decrypt( $value ) {
		$value = base64_decode( $value, true );
		if ( $value === false ) {
			return '';
		}

		$nonce_size = openssl_cipher_iv_length( static::METHOD );
		$nonce      = mb_substr( $value, 0, $nonce_size, '8bit' );
		$ciphertext = mb_substr( $value, $nonce_size, null, '8bit' );

		$plaintext = openssl_decrypt(
			$ciphertext,
			static::METHOD,
			static::key(),
			OPENSSL_RAW_DATA,
			$nonce
		);

		return $plaintext;
	}
}
