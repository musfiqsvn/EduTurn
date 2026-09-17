<?php
/**
 * EduTurn — Portal data layer: student results/fees/attendance lookups,
 * teacher class roster, and the attendance-taking handler.
 */

defined( 'ABSPATH' ) || exit;

/** Current user's linked ut_student post (or null). */
function uturn_my_student() {
	$uid = get_current_user_id();
	if ( ! $uid ) {
		return null;
	}
	$pid = (int) get_user_meta( $uid, '_ut_student_post', true );
	if ( $pid && 'ut_student' === get_post_type( $pid ) ) {
		return get_post( $pid );
	}
	$found = get_posts( array( 'post_type' => 'ut_student', 'posts_per_page' => 1, 'meta_key' => '_ut_user_id', 'meta_value' => $uid ) );
	return $found ? $found[0] : null;
}

/** Current user's linked ut_teacher post (or null). */
function uturn_my_teacher() {
	$uid = get_current_user_id();
	if ( ! $uid ) {
		return null;
	}
	$pid = (int) get_user_meta( $uid, '_ut_teacher_post', true );
	if ( $pid && 'ut_teacher' === get_post_type( $pid ) ) {
		return get_post( $pid );
	}
	$found = get_posts( array( 'post_type' => 'ut_teacher', 'posts_per_page' => 1, 'meta_key' => '_ut_user_id', 'meta_value' => $uid ) );
	return $found ? $found[0] : null;
}

/* ---------- Student lookups ---------- */
function uturn_my_results( $student ) {
	if ( ! $student ) {
		return array();
	}
	/* Published only; linked by student id so promotion never breaks history. */
	$by_id = get_posts(
		array(
			'post_type' => 'ut_result', 'posts_per_page' => -1, 'post_status' => 'publish',
			'orderby' => 'ID', 'order' => 'DESC',
			'meta_key' => '_ut_student_id', 'meta_value' => $student->ID,
		)
	);
	if ( $by_id ) {
		return $by_id;
	}
	$class = get_post_meta( $student->ID, '_ut_class', true );
	$roll = get_post_meta( $student->ID, '_ut_roll', true );
	if ( ! $class || ! $roll ) {
		return array();
	}
	return get_posts(
		array(
			'post_type' => 'ut_result', 'posts_per_page' => -1, 'post_status' => 'publish',
			'orderby' => 'ID', 'order' => 'DESC',
			'meta_query' => array(
				array( 'key' => '_ut_class', 'value' => $class ),
				array( 'key' => '_ut_roll', 'value' => $roll ),
			),
		)
	);
}

function uturn_my_fees( $student ) {
	$uid = get_current_user_id();
	$by_user = $uid ? get_posts( array( 'post_type' => 'ut_fee', 'posts_per_page' => -1, 'meta_key' => '_ut_user_id', 'meta_value' => $uid ) ) : array();
	if ( $by_user ) {
		return $by_user;
	}
	if ( $student ) {
		return get_posts( array( 'post_type' => 'ut_fee', 'posts_per_page' => -1, 'meta_key' => '_ut_student_id', 'meta_value' => $student->ID ) );
	}
	return array();
}

function uturn_my_attendance( $student ) {
	$out = array( 'present' => 0, 'total' => 0, 'pct' => 0, 'recent' => array() );
	if ( ! $student ) {
		return $out;
	}
	$class = get_post_meta( $student->ID, '_ut_class', true );
	if ( ! $class ) {
		return $out;
	}
	$records = get_posts( array( 'post_type' => 'ut_attendance', 'posts_per_page' => -1, 'meta_key' => '_ut_date', 'orderby' => 'meta_value', 'order' => 'DESC', 'meta_query' => array( array( 'key' => '_ut_class', 'value' => $class ) ) ) );
	foreach ( $records as $r ) {
		$present = array_filter( array_map( 'trim', explode( ',', (string) get_post_meta( $r->ID, '_ut_present', true ) ) ) );
		$is_in = in_array( (string) $student->ID, $present, true );
		$out['total']++;
		if ( $is_in ) {
			$out['present']++;
		}
		if ( count( $out['recent'] ) < 8 ) {
			$out['recent'][] = array( 'date' => get_post_meta( $r->ID, '_ut_date', true ), 'in' => $is_in );
		}
	}
	$out['pct'] = $out['total'] ? round( $out['present'] * 100 / $out['total'] ) : 0;
	return $out;
}

/* ---------- Teacher lookups ---------- */
function uturn_class_students( $class ) {
	if ( ! $class ) {
		return array();
	}
	return get_posts(
		array(
			'post_type' => 'ut_student', 'posts_per_page' => -1,
			'meta_key' => '_ut_class', 'meta_value' => $class, 'orderby' => 'title', 'order' => 'ASC',
			'meta_query' => array(
				array(
					'relation' => 'OR',
					array( 'key' => '_ut_status', 'compare' => 'NOT EXISTS' ),
					array( 'key' => '_ut_status', 'value' => array( 'active', 'promoted', 'retained' ), 'compare' => 'IN' ),
				),
			),
		)
	);
}

/**
 * Class scoping for plain teachers (Req 18): null = all classes (office roles);
 * array = only these class names (class-teacher / subject-teacher mapping).
 */
function uturn_scoped_classes() {
	$u = wp_get_current_user();
	if ( ! $u || ! $u->ID ) {
		return array();
	}
	if ( current_user_can( 'manage_options' ) || current_user_can( 'uturn_manage_academic' ) ) {
		return null;
	}
	if ( ! in_array( 'uturn_teacher', (array) $u->roles, true ) ) {
		return null;
	}
	if ( ! function_exists( 'uturn_teacher_classes' ) ) {
		return array();
	}
	return array_keys( uturn_teacher_classes( $u->ID ) );
}

function uturn_find_attendance( $class, $date ) {
	$found = get_posts(
		array(
			'post_type' => 'ut_attendance', 'posts_per_page' => 1, 'fields' => 'ids',
			'meta_query' => array(
				array( 'key' => '_ut_class', 'value' => $class ),
				array( 'key' => '_ut_date', 'value' => $date ),
			),
		)
	);
	return $found ? $found[0] : 0;
}

/* ---------- Attendance taking ---------- */
add_action( 'admin_post_uturn_attendance', 'uturn_attendance_save' );
function uturn_attendance_save() {
	eduturn_license_require( 'erp' );
	$class = isset( $_POST['att-class'] ) ? sanitize_text_field( wp_unslash( $_POST['att-class'] ) ) : '';
	$date = isset( $_POST['att-date'] ) ? sanitize_text_field( wp_unslash( $_POST['att-date'] ) ) : '';
	$present = array_map( 'absint', (array) ( isset( $_POST['present'] ) ? $_POST['present'] : array() ) );
	$back = uturn_url( 'teacher-portal' );
	if ( ! empty( $_REQUEST['ut_shell'] ) && function_exists( 'uturn_dash_view_url' ) ) {
		$back = uturn_dash_view_url( 'take-attendance', array_filter( array( 'att_class' => $class, 'att_date' => $date ) ) );
	}
	$msgback = function ( $msg ) use ( $back ) {
		wp_safe_redirect( add_query_arg( 'uturn_msg', $msg, $back ) );
		exit;
	};
	if ( ! check_admin_referer( 'uturn_attendance' ) || ! current_user_can( 'edit_ut_attendances' ) ) {
		$msgback( 'form-error' );
		exit;
	}
	if ( ! $class || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
		$msgback( 'form-error' );
		exit;
	}
	if ( function_exists( 'uturn_scoped_classes' ) ) {
		$allowed = uturn_scoped_classes();
		if ( is_array( $allowed ) && ! in_array( $class, $allowed, true ) ) {
			$msgback( 'form-error' );
			exit;
		}
	}
	$existing = uturn_find_attendance( $class, $date );
	if ( $existing ) {
		update_post_meta( $existing, '_ut_present', implode( ',', $present ) );
	} else {
		$id = wp_insert_post( array( 'post_title' => wp_slash( $date . ' — ' . $class ), 'post_type' => 'ut_attendance', 'post_status' => 'publish' ) );
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_ut_date', $date );
			update_post_meta( $id, '_ut_class', $class );
			update_post_meta( $id, '_ut_present', implode( ',', $present ) );
		}
	}
	$att_id = $existing ? $existing : ( isset( $id ) && $id && ! is_wp_error( $id ) ? (int) $id : 0 );
	if ( $att_id && ! empty( $_POST['no_sms'] ) && ( current_user_can( 'uturn_manage_sms' ) || current_user_can( 'manage_options' ) ) && get_post_meta( $att_id, '_ut_absent_sent', true ) === '' ) {
		update_post_meta( $att_id, '_ut_absent_sent', 'skipped-manual|' . current_time( 'mysql' ) );
	}
	if ( $att_id && function_exists( 'uturn_notify_attendance' ) ) {
		uturn_notify_attendance( $att_id, get_current_user_id() );
	}
	$msgback( 'attendance-saved' );
	exit;
}

/* ---------- Portal self-service: password change (teacher/student) ---------- */
add_action( 'init', 'uturn_password_change_handle' );
function uturn_password_change_handle() {
	if ( ! isset( $_POST['uturn_pwchange'] ) || ! is_user_logged_in() ) {
		return;
	}
	$user = wp_get_current_user();
	/* Every logged-in account may change its own password (current password + nonce required). */
	$back = wp_get_referer() ? wp_get_referer() : ( function_exists( 'uturn_dash_view_url' ) ? uturn_dash_view_url( 'profile' ) : home_url( '/' ) );
	$fail = function ( $msg ) use ( $back ) {
		wp_safe_redirect( add_query_arg( array( 'pw' => 'err', 'pwmsg' => rawurlencode( $msg ) ), $back ) );
		exit;
	};
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'uturn_pwchange' ) ) {
		$fail( 'নিরাপত্তা যাচাই ব্যর্থ। আবার চেষ্টা করুন।' );
	}
	$cur = isset( $_POST['pw_cur'] ) ? wp_unslash( $_POST['pw_cur'] ) : '';
	$n1  = isset( $_POST['pw_new'] ) ? wp_unslash( $_POST['pw_new'] ) : '';
	$n2  = isset( $_POST['pw_new2'] ) ? wp_unslash( $_POST['pw_new2'] ) : '';
	if ( ! wp_check_password( $cur, $user->user_pass, $user->ID ) ) {
		$fail( 'বর্তমান পাসওয়ার্ড ভুল।' );
	}
	if ( strlen( $n1 ) < 6 ) {
		$fail( 'নতুন পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে।' );
	}
	if ( $n1 !== $n2 ) {
		$fail( 'নতুন পাসওয়ার্ড দুটি মিলছে না।' );
	}
	/* DB write happens before the confirmation email: even if a broken mail
	 * transport throws, the password IS saved — never white-screen this. */
	try {
		wp_update_user( array( 'ID' => $user->ID, 'user_pass' => $n1 ) );
		$args = array( 'pw' => 'ok' );
	} catch ( Throwable $e ) {
		error_log( 'EduTurn portal password mail failed: ' . $e->getMessage() );
		$args = array( 'pw' => 'ok', 'mailfail' => '1' );
	}
	wp_safe_redirect( add_query_arg( $args, $back ) );
	exit;
}

function uturn_password_change_form() {
	$user = wp_get_current_user();
	echo '<h2 class="section-title" style="margin-top:32px">🔑 পাসওয়ার্ড পরিবর্তন</h2>';
	echo '<div class="card" style="max-width:520px"><p class="muted small">আপনার লগইন আইডি: <b>' . esc_html( $user->user_login ) . '</b></p>';
	if ( isset( $_GET['pw'] ) && $_GET['pw'] === 'ok' ) {
		echo '<p class="rc-pass">✅ পাসওয়ার্ড সফলভাবে বদলে গেছে।</p>';
		if ( isset( $_GET['mailfail'] ) ) {
			echo '<p class="rc-fail">⚠️ কনফার্মেশন ইমেইল পাঠানো যায়নি (সার্ভারে ইমেইল বন্ধ)। পাসওয়ার্ড ঠিকই বদলেছে।</p>';
		}
	} elseif ( isset( $_GET['pw'] ) && $_GET['pw'] === 'err' ) {
		echo '<p class="rc-fail">⚠️ ' . esc_html( isset( $_GET['pwmsg'] ) ? wp_unslash( $_GET['pwmsg'] ) : 'ত্রুটি হয়েছে।' ) . '</p>';
	}
	echo '<form method="post" action="">' . wp_nonce_field( 'uturn_pwchange', '_wpnonce', true, false );
	echo '<div class="field"><label>বর্তমান পাসওয়ার্ড</label><input class="input" type="password" name="pw_cur" required autocomplete="current-password"></div>';
	echo '<div class="field"><label>নতুন পাসওয়ার্ড (কমপক্ষে ৬ অক্ষর)</label><input class="input" type="password" name="pw_new" required autocomplete="new-password"></div>';
	echo '<div class="field"><label>নতুন পাসওয়ার্ড (আবার)</label><input class="input" type="password" name="pw_new2" required autocomplete="new-password"></div>';
	echo '<p><button class="btn" type="submit" name="uturn_pwchange" value="1">পাসওয়ার্ড বদলান</button></p></form></div>';
}
