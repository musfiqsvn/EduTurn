<?php
/**
 * EduTurn — Dashboard user management (headmaster only) + role matrix.
 * Self profile lives in dash.php (uturn_dash_profile).
 */

defined( 'ABSPATH' ) || exit;

/** Allowed role choices inside the dashboard user editor. */
function uturn_dash_role_choices() {
	$c = array(
		'uturn_headmaster'   => 'প্রধান শিক্ষক',
		'uturn_school_admin' => 'স্কুল অ্যাডমিন',
		'uturn_staff'        => 'সহকারী / স্টাফ',
		'uturn_teacher'      => 'শিক্ষক',
		'uturn_student'      => 'শিক্ষার্থী',
		'uturn_accountant'   => 'হিসাবরক্ষক',
	);
	if ( ! current_user_can( 'manage_options' ) ) {
		unset( $c['uturn_headmaster'] ); // headmaster can't mint headmasters (mirrors editable_roles)
	}
	return $c;
}

function uturn_dash_users_list() {
	if ( ! current_user_can( 'list_users' ) ) {
		return;
	}
	set_current_screen( 'users' );
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-users-list-table.php';
	echo '<div class="utd-card"><p><a class="button button-primary" href="' . esc_url( uturn_dash_view_url( 'users', array( 'sub' => 'new' ) ) ) . '">＋ নতুন ব্যবহারকারী</a></p>';
	echo '<form method="get" action="' . esc_url( uturn_dash_url() ) . '" class="utd-search"><input type="hidden" name="view" value="users"><input type="search" name="s" value="' . esc_attr( isset( $_GET['s'] ) ? wp_unslash( $_GET['s'] ) : '' ) . '" placeholder="নাম / ইমেইল খুঁজুন"> ';
	submit_button( 'খুঁজুন', 'secondary', '', false );
	echo '</form>';
	ob_start();
	$wp_list_table = _get_list_table( 'WP_Users_List_Table' );
	$wp_list_table->prepare_items();
	$wp_list_table->views();
	$wp_list_table->display();
	$html = ob_get_clean();
	// Bulk actions + user deletion post to wp-admin (blocked for shell roles) — strip them.
	$html = preg_replace( '/<div class="alignleft actions bulkactions">.*?<\/div>/s', '', $html );
	$html = preg_replace( '/<a[^>]*href="[^"]*users\.php\?action=delete[^"]*"[^>]*>.*?<\/a>/s', '<span class="utd-muted">মুছুন</span>', $html );
	$html = preg_replace( '/<input[^>]*id="bulk-action-selector-[^"]*"[^>]*>/', '', $html );
	echo uturn_dash_remap( $html );
	echo '</div>';
}

function uturn_dash_user_form( $is_new ) {
	$u = null;
	if ( ! $is_new ) {
		$id = isset( $_GET['id'] ) ? (int) $_GET['id'] : ( isset( $_GET['user_id'] ) ? (int) $_GET['user_id'] : 0 );
		$u = $id ? get_userdata( $id ) : false;
		if ( ! $u ) {
			echo '<div class="utd-card"><p class="utd-muted">ব্যবহারকারী পাওয়া যায়নি।</p></div>';
			return;
		}
		$me = wp_get_current_user();
		$super = $me && in_array( 'administrator', (array) $me->roles, true );
		if ( ! $super && ( $u->has_cap( 'administrator' ) || in_array( 'uturn_headmaster', (array) $u->roles, true ) ) ) {
			echo '<div class="utd-card"><p class="utd-muted">এই অ্যাকাউন্ট সম্পাদনার অনুমতি নেই।</p></div>';
			return;
		}
	}
	$choices = uturn_dash_role_choices();
	$role = $u && $u->roles ? $u->roles[0] : 'uturn_student';
	echo '<div class="utd-card"><h2>' . ( $is_new ? '＋ নতুন ব্যবহারকারী' : '✏️ ' . esc_html( $u->user_login ) . ' সম্পাদনা' ) . '</h2>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="uturn_user_save"><input type="hidden" name="ut_shell" value="1">';
	wp_nonce_field( 'uturn_user_save' );
	if ( ! $is_new ) {
		echo '<input type="hidden" name="uid" value="' . (int) $u->ID . '">';
	}
	echo '<table class="form-table">';
	if ( $is_new ) {
		echo '<tr><th scope="row">ইউজারনেম *</th><td><input type="text" name="user_login" required class="regular-text"></td></tr>';
	}
	echo '<tr><th scope="row">পুরো নাম *</th><td><input type="text" name="display_name" required class="regular-text" value="' . esc_attr( $u ? $u->display_name : '' ) . '"></td></tr>';
	echo '<tr><th scope="row">ইমেইল *</th><td><input type="email" name="email" required class="regular-text" value="' . esc_attr( $u ? $u->user_email : '' ) . '"></td></tr>';
	echo '<tr><th scope="row">ভূমিকা *</th><td><select name="role">';
	foreach ( $choices as $k => $label ) {
		echo '<option value="' . esc_attr( $k ) . '"' . selected( $role, $k, false ) . '>' . esc_html( $label ) . '</option>';
	}
	echo '</select></td></tr>';
	$sid = $u ? get_user_meta( $u->ID, '_ut_student_id', true ) : '';
	echo '<tr><th scope="row">লিংকড শিক্ষার্থী (শিক্ষার্থী ভূমিকার জন্য)</th><td><input type="text" name="student_id" class="regular-text" value="' . esc_attr( $sid ) . '" placeholder="যেমন: student1"><p class="description">শিক্ষার্থীর ইউজারনেম — ফলাফল/ফি/হাজিরা মেলাতে ব্যবহৃত হয়।</p></td></tr>';
	$ph = $u ? get_user_meta( $u->ID, '_ut_phone', true ) : '';
	echo '<tr><th scope="row">মোবাইল (লগইনের জন্য)</th><td><input type="text" name="phone" class="regular-text" value="' . esc_attr( $ph ) . '" placeholder="01XXXXXXXXX"></td></tr>';
	echo '<tr><th scope="row">' . ( $is_new ? 'পাসওয়ার্ড *' : 'নতুন পাসওয়ার্ড' ) . '</th><td><input type="password" name="pass" class="regular-text"' . ( $is_new ? ' required' : '' ) . '>' . ( $is_new ? '' : '<p class="description">খালি রাখলে পাসওয়ার্ড বদলাবে না।</p>' ) . '</td></tr>';
	echo '</table><p>';
	submit_button( $is_new ? 'ব্যবহারকারী তৈরি করুন' : 'সংরক্ষণ করুন', 'primary', 'submit', false );
	echo ' <a class="button" href="' . esc_url( uturn_dash_view_url( 'users' ) ) . '">ফিরে যান</a></p></form></div>';
}

function uturn_dash_users( $view = 'users' ) {
	if ( ! current_user_can( 'list_users' ) ) {
		echo '<div class="utd-card"><p class="utd-muted">এই পেজটি শুধু প্রধান শিক্ষকের জন্য।</p></div>';
		return;
	}
	$sub = isset( $_GET['sub'] ) ? sanitize_key( $_GET['sub'] ) : '';
	if ( isset( $_GET['role_warn'] ) ) {
		echo '<div class="notice notice-warning" role="status"><p>⚠️ অনুরোধ করা ভূমিকা অনুমোদিত নয় — নিরাপত্তার জন্য “শিক্ষার্থী” নির্ধারণ করা হয়েছে।</p></div>';
	}
	if ( isset( $_GET['uturn_msg'] ) ) {
		echo '<div class="notice notice-success" role="status"><p>' . ( $_GET['uturn_msg'] === 'user-created' ? '✅ ব্যবহারকারী তৈরি হয়েছে।' : '✅ সংরক্ষণ করা হয়েছে।' ) . '</p></div>';
	}
	if ( $sub === 'new' ) {
		uturn_dash_user_form( true );
	} elseif ( $sub === 'edit' ) {
		uturn_dash_user_form( false );
	} else {
		uturn_dash_users_list();
	}
}

/** Read-only role → capability matrix (headmaster). */
function uturn_dash_roles_cap() {
	echo '<div class="utd-card"><h2>🔐 ভূমিকা ও ক্ষমতা</h2><p class="utd-muted">ক্ষমতাগুলো থিম দ্বারা নির্ধারিত — এখান থেকে শুধু দেখা যায়।</p>';
	$roles = array(
		'uturn_headmaster'   => 'প্রধান শিক্ষক',
		'uturn_school_admin' => 'স্কুল অ্যাডমিন',
		'uturn_staff'        => 'সহকারী / স্টাফ',
		'uturn_teacher'      => 'শিক্ষক',
		'uturn_student'      => 'শিক্ষার্থী',
		'uturn_accountant'   => 'হিসাবরক্ষক',
	);
	$labels = array(
		'read' => 'লগইন', 'upload_files' => 'আপলোড', 'edit_ut_students' => 'শিক্ষার্থী', 'edit_ut_teachers' => 'শিক্ষক',
		'edit_ut_results' => 'ফলাফল', 'edit_ut_fees' => 'ফি', 'edit_ut_attendances' => 'হাজিরা',
		'edit_ut_notices' => 'নোটিশ', 'edit_ut_news_items' => 'সংবাদ', 'edit_ut_events' => 'ইভেন্ট',
		'edit_ut_albums' => 'গ্যালারি', 'edit_ut_downloads' => 'ডাউনলোড', 'edit_ut_faqs' => 'জিজ্ঞাসা',
		'edit_ut_testimonials' => 'মতামত', 'edit_ut_applications' => 'ভর্তি', 'edit_ut_messages' => 'বার্তা',
		'uturn_manage_routines' => 'রুটিন', 'uturn_manage_sms' => 'এসএমএস', 'uturn_manage_academic' => 'একাডেমিক', 'uturn_manage_settings' => 'সেটিংস', 'list_users' => 'ব্যবহারকারী',
	);
	echo '<div style="overflow-x:auto"><table class="widefat striped" aria-label="ভূমিকা ও অনুমতি"><thead><tr><th scope="col">ভূমিকা</th>';
	foreach ( $labels as $label ) {
		echo '<th scope="col"><small>' . esc_html( $label ) . '</small></th>';
	}
	echo '</tr></thead><tbody>';
	foreach ( $roles as $rk => $rlabel ) {
		$ro = get_role( $rk );
		echo '<tr><td><b>' . esc_html( $rlabel ) . '</b></td>';
		foreach ( $labels as $cap => $label ) {
			$has = $ro && $ro->has_cap( $cap );
			echo '<td style="text-align:center">' . ( $has ? '<span class="utd-ok">●</span>' : '<span class="utd-muted">○</span>' ) . '</td>';
		}
		echo '</tr>';
	}
	echo '</tbody></table></div></div>';
}

/* ================= handlers ================= */
function uturn_dash_user_save() {
	$me = wp_get_current_user();
	$super = $me && in_array( 'administrator', (array) $me->roles, true );
	if ( ! current_user_can( 'list_users' ) || ! check_admin_referer( 'uturn_user_save' ) ) {
		wp_die( 'Unauthorized' );
	}
	$choices = array_keys( uturn_dash_role_choices() );
	$role = isset( $_POST['role'] ) ? sanitize_key( $_POST['role'] ) : '';
	$role_warn = false;
	if ( ! in_array( $role, $choices, true ) ) {
		$role = 'uturn_student';
		$role_warn = true; // B-8: blocked escalation attempt — warn instead of silent downgrade.
	}
	$uid = isset( $_POST['uid'] ) ? (int) $_POST['uid'] : 0;
	if ( $uid && ! current_user_can( 'edit_users' ) ) {
		wp_die( 'Unauthorized' );
	}
	if ( ! $uid && ! current_user_can( 'create_users' ) ) {
		wp_die( 'Unauthorized' );
	}
	if ( $uid ) {
		$u = get_userdata( $uid );
		if ( ! $u || $u->has_cap( 'administrator' ) ) {
			wp_die( 'Unauthorized' );
		}
		if ( ! $super && in_array( 'uturn_headmaster', (array) $u->roles, true ) ) {
			wp_die( 'Unauthorized' );
		}
		$args = array(
			'ID' => $uid,
			'display_name' => isset( $_POST['display_name'] ) ? sanitize_text_field( wp_unslash( $_POST['display_name'] ) ) : $u->display_name,
			'user_email' => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : $u->user_email,
		);
		if ( ! empty( $_POST['pass'] ) ) {
			$args['user_pass'] = wp_unslash( $_POST['pass'] );
		}
		$r = wp_update_user( $args );
		if ( is_wp_error( $r ) ) {
			wp_die( esc_html( $r->get_error_message() ) );
		}
		$u->set_role( $role );
		if ( isset( $_POST['student_id'] ) ) {
			update_user_meta( $uid, '_ut_student_id', sanitize_text_field( wp_unslash( $_POST['student_id'] ) ) );
		}
		if ( isset( $_POST['phone'] ) ) {
			$ph = sanitize_text_field( wp_unslash( $_POST['phone'] ) );
			update_user_meta( $uid, '_ut_phone', $ph );
			update_user_meta( $uid, '_ut_phone_norm', function_exists( 'uturn_norm_phone' ) && $ph !== '' ? uturn_norm_phone( $ph ) : '' );
		}
		wp_safe_redirect( uturn_dash_view_url( 'users', array( 'sub' => 'edit', 'id' => $uid, 'uturn_msg' => 'saved' ) + ( $role_warn ? array( 'role_warn' => '1' ) : array() ) ) );
	} else {
		$login = isset( $_POST['user_login'] ) ? sanitize_user( wp_unslash( $_POST['user_login'] ) ) : '';
		if ( $login === '' || username_exists( $login ) || empty( $_POST['pass'] ) ) {
			wp_die( 'ব্যবহারকারী তৈরি করা যায়নি — ইউজারনেম/পাসওয়ার্ড দেখুন।' );
		}
		$uid2 = wp_insert_user( array(
			'user_login' => $login,
			'user_pass' => wp_unslash( $_POST['pass'] ),
			'display_name' => isset( $_POST['display_name'] ) ? sanitize_text_field( wp_unslash( $_POST['display_name'] ) ) : $login,
			'user_email' => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
			'role' => $role,
		) );
		if ( is_wp_error( $uid2 ) ) {
			wp_die( esc_html( $uid2->get_error_message() ) );
		}
		if ( isset( $_POST['student_id'] ) ) {
			update_user_meta( $uid2, '_ut_student_id', sanitize_text_field( wp_unslash( $_POST['student_id'] ) ) );
		}
		if ( isset( $_POST['phone'] ) ) {
			$ph = sanitize_text_field( wp_unslash( $_POST['phone'] ) );
			update_user_meta( $uid2, '_ut_phone', $ph );
			update_user_meta( $uid2, '_ut_phone_norm', function_exists( 'uturn_norm_phone' ) && $ph !== '' ? uturn_norm_phone( $ph ) : '' );
		}
		wp_safe_redirect( uturn_dash_view_url( 'users', array( 'sub' => 'edit', 'id' => $uid2, 'uturn_msg' => 'user-created' ) + ( $role_warn ? array( 'role_warn' => '1' ) : array() ) ) );
	}
	exit;
}
add_action( 'admin_post_uturn_user_save', 'uturn_dash_user_save' );
