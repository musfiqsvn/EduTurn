<?php
/**
 * EduTurn — Login with mobile number.
 * Any user with a unique "মোবাইল" saved (entity screens or profile) can sign
 * in with that number instead of username/email. Numbers are normalized
 * (spaces/dashes/country-code stripped) so 01XXXXXXXXX and 8801XXXXXXXXX match.
 */

defined( 'ABSPATH' ) || exit;

/** Normalize to plain digits: 8801XXXXXXXXX => 01XXXXXXXXX. */
function uturn_norm_phone( $p ) {
	$d = preg_replace( '/\D+/', '', (string) $p );
	if ( strlen( $d ) > 11 && substr( $d, 0, 3 ) === '880' ) {
		$d = '0' . substr( $d, 3 );
	}
	return $d;
}

/**
 * Find the user owning a normalized number.
 * Returns user ID, 0 when free/invalid, -1 when claimed by 2+ users (ambiguous).
 */
function uturn_phone_user_id( $norm, $exclude = 0 ) {
	if ( ! preg_match( '/^\d{6,15}$/', (string) $norm ) ) {
		return 0;
	}
	$args = array( 'meta_key' => '_ut_phone_norm', 'meta_value' => $norm, 'number' => 2, 'fields' => 'ID' );
	if ( $exclude ) {
		$args['exclude'] = array( (int) $exclude );
	}
	$ids = ( new WP_User_Query( $args ) )->get_results();
	if ( count( $ids ) > 1 ) {
		return -1;
	}
	return $ids ? (int) $ids[0] : 0;
}

/* Authenticate by phone: runs before core's username/password check. */
add_filter( 'authenticate', 'uturn_phone_authenticate', 15, 3 );
function uturn_phone_authenticate( $user, $username, $password ) {
	if ( $user instanceof WP_User || $username === '' || $password === '' ) {
		return $user;
	}
	if ( username_exists( $username ) ) {
		return $user;
	}
	if ( is_email( $username ) && email_exists( $username ) ) {
		return $user;
	}
	$id = uturn_phone_user_id( uturn_norm_phone( $username ) );
	if ( $id <= 0 ) {
		return $user;
	}
	$u = get_userdata( $id );
	if ( ! $u ) {
		return $user;
	}
	if ( ! wp_check_password( $password, $u->user_pass, $u->ID ) ) {
		return new WP_Error( 'incorrect_password', sprintf( __( '<strong>Error:</strong> The password you entered for the mobile number %s is incorrect.' ), '<strong>' . esc_html( $username ) . '</strong>' ) );
	}
	return $u;
}

/* Login form label: this exact string only appears on the login form. */
add_filter( 'gettext', 'uturn_login_label', 20, 3 );
function uturn_login_label( $t, $text, $domain ) {
	if ( 'Username or Email Address' === $text && 'default' === $domain ) {
		return 'ইউজারনেম / মোবাইল / ইমেইল';
	}
	return $t;
}

/* ---------- profile + add-user fields ---------- */
add_action( 'show_user_profile', 'uturn_phone_profile_field' );
add_action( 'edit_user_profile', 'uturn_phone_profile_field' );
function uturn_phone_profile_field( $u ) {
	echo '<h2>📱 মোবাইল লগইন</h2><table class="form-table"><tr><th scope="row"><label for="uturn_phone">মোবাইল নম্বর</label></th><td>'
		. '<input type="text" id="uturn_phone" name="uturn_phone" value="' . esc_attr( get_user_meta( $u->ID, '_ut_phone', true ) ) . '" class="regular-text">'
		. '<p class="description">এই নম্বর দিয়েও লগইন করা যাবে। প্রতিটি নম্বর শুধু একজনের হতে হবে।</p></td></tr>'
		. '<tr><th scope="row"><label for="uturn_wa">হোয়াটসঅ্যাপ নম্বর</label></th><td><input type="text" id="uturn_wa" name="uturn_wa" value="' . esc_attr( get_user_meta( $u->ID, '_ut_wa', true ) ) . '" class="regular-text"><p class="description">খালি রাখলে মোবাইল নম্বরেই হোয়াটসঅ্যাপ যাবে।</p></td></tr></table>';
}

add_action( 'user_profile_update_errors', 'uturn_phone_profile_validate', 10, 3 );
function uturn_phone_profile_validate( $errors, $update, $userdata ) {
	if ( empty( $_POST['uturn_phone'] ) ) {
		return;
	}
	$norm = uturn_norm_phone( wp_unslash( $_POST['uturn_phone'] ) );
	$uid = ( is_object( $userdata ) && ! empty( $userdata->ID ) ) ? (int) $userdata->ID : 0;
	if ( uturn_phone_user_id( $norm, $uid ) !== 0 ) {
		$errors->add( 'phone_dup', '<strong>ত্রুটি:</strong> এই মোবাইল নম্বর অন্য অ্যাকাউন্টে ব্যবহৃত।' );
	}
}

add_action( 'personal_options_update', 'uturn_phone_profile_save' );
add_action( 'edit_user_profile_update', 'uturn_phone_profile_save' );
function uturn_phone_profile_save( $uid ) {
	if ( ! current_user_can( 'edit_user', $uid ) || ! isset( $_POST['uturn_phone'] ) ) {
		return;
	}
	$raw = sanitize_text_field( wp_unslash( $_POST['uturn_phone'] ) );
	update_user_meta( $uid, '_ut_phone', $raw );
	update_user_meta( $uid, '_ut_phone_norm', $raw === '' ? '' : uturn_norm_phone( $raw ) );
	if ( isset( $_POST['uturn_wa'] ) ) {
		update_user_meta( $uid, '_ut_wa', sanitize_text_field( wp_unslash( $_POST['uturn_wa'] ) ) );
	}
}

/* Users → Add New (how Headmasters get added): same phone field. */
add_action( 'user_new_form', 'uturn_phone_new_form' );
function uturn_phone_new_form() {
	echo '<table class="form-table"><tr class="form-field"><th scope="row"><label for="uturn_phone">মোবাইল (লগইন)</label></th>'
		. '<td><input type="text" id="uturn_phone" name="uturn_phone" class="regular-text"><p class="description">এই নম্বর দিয়েও লগইন করা যাবে (অনন্য হতে হবে)।</p></td></tr>'
		. '<tr class="form-field"><th scope="row"><label for="uturn_wa">হোয়াটসঅ্যাপ</label></th><td><input type="text" id="uturn_wa" name="uturn_wa" class="regular-text"></td></tr></table>';
}

add_action( 'user_register', 'uturn_phone_user_register' );
function uturn_phone_user_register( $uid ) {
	if ( empty( $_POST['uturn_phone'] ) ) {
		return;
	}
	$norm = uturn_norm_phone( wp_unslash( $_POST['uturn_phone'] ) );
	if ( uturn_phone_user_id( $norm, $uid ) !== 0 ) {
		return; // duplicate: skip silently, profile will show the clash on next save
	}
	update_user_meta( $uid, '_ut_phone', sanitize_text_field( wp_unslash( $_POST['uturn_phone'] ) ) );
	update_user_meta( $uid, '_ut_phone_norm', $norm );
	if ( ! empty( $_POST['uturn_wa'] ) ) {
		update_user_meta( $uid, '_ut_wa', sanitize_text_field( wp_unslash( $_POST['uturn_wa'] ) ) );
	}
}

/* ---------- backfill for numbers saved before this feature ---------- */
add_action( 'admin_init', 'uturn_phone_backfill', 20 );
function uturn_phone_backfill() {
	if ( get_option( 'uturn_phone_backfill_v' ) === '1.7.0' || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$q = new WP_User_Query( array( 'meta_key' => '_ut_phone', 'number' => -1, 'fields' => 'ID' ) );
	foreach ( (array) $q->get_results() as $uid ) {
		if ( get_user_meta( $uid, '_ut_phone_norm', true ) !== '' ) {
			continue;
		}
		$norm = uturn_norm_phone( get_user_meta( $uid, '_ut_phone', true ) );
		if ( $norm !== '' && uturn_phone_user_id( $norm, $uid ) === 0 ) {
			update_user_meta( $uid, '_ut_phone_norm', $norm );
		}
	}
	update_option( 'uturn_phone_backfill_v', '1.7.0' );
}

add_action( 'wp_login', 'uturn_phone_login_touch', 10, 2 );
function uturn_phone_login_touch( $login, $u ) {
	if ( $u instanceof WP_User && get_user_meta( $u->ID, '_ut_phone_norm', true ) === '' ) {
		$raw = get_user_meta( $u->ID, '_ut_phone', true );
		if ( $raw !== '' ) {
			$norm = uturn_norm_phone( $raw );
			if ( uturn_phone_user_id( $norm, $u->ID ) === 0 ) {
				update_user_meta( $u->ID, '_ut_phone_norm', $norm );
			}
		}
	}
}
