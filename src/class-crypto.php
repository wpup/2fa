<?php

namespace WPUP\TwoFactory;

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
		if ( ! defined( '2FA_KEY' ) ) {
			return defined( 'AUTH_KEY' ) ? AUTH_KEY : '';
		}

		return constant( '2FA_KEY' );
	}

	/**
	 * Encrypt value.
	 *
	 * @param  string $value
	 *
	 * @return string
	 */
	public static function encrypt( $value ) {
		$nonceSize = openssl_cipher_iv_length( static::METHOD );
        $nonce = openssl_random_pseudo_bytes( $nonceSize );

        $ciphertext = openssl_encrypt(
            $value,
            static::METHOD,
            static::key(),
            OPENSSL_RAW_DATA,
            $nonce
        );

        return base64_encode( $nonce.$ciphertext );
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

        $nonceSize = openssl_cipher_iv_length( static::METHOD );
        $nonce = mb_substr( $value, 0, $nonceSize, '8bit' );
        $ciphertext = mb_substr( $value, $nonceSize, null, '8bit' );

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
