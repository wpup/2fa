<?php

/**
 * Get user option without site check.
 *
 * @param  string $key
 * @param  int    $user
 *
 * @return mxied
 */
function two_fa_get_user_option( $key, $user ) {
	if ( empty( $user ) ) {
		$user = get_current_user_id();
	}

	if ( ! $user = get_userdata( $user ) ) {
		return false;
	}

	return $user->get( $key );
}
