<?php
/**
 * EduTurn — Role-Based Access Control.
 * Super Admin (administrator): god-mode, everything.
 * Headmaster (uturn_headmaster): everything School Admin can do, PLUS site
 * settings/homepage and staff-account management — but NO license, demo
 * re-sync, plugins/themes, and NO touching Super Admin accounts.
 * School Admin (uturn_school_admin): all operational content (students,
 * teachers, results, attendance, fees, applications, notices, news,
 * events, gallery, downloads...) but NO site settings, users, or system.
 * Accountant (uturn_accountant): fee records only (+ read-only directories).
 * Staff / Teacher / Student: bounded native roles as before.
 */

defined( 'ABSPATH' ) || exit;

function uturn_all_custom_caps() {
	$caps = array( 'uturn_manage_sms', 'uturn_manage_routines', 'uturn_manage_settings', 'uturn_manage_academic' );
	foreach ( uturn_cap_bases() as $single => $plural ) {
		foreach ( array( 'edit', 'read', 'delete' ) as $v ) {
			$caps[] = "{$v}_{$single}";
		}
		foreach ( array( 'edit', 'edit_others', 'publish', 'read_private', 'delete', 'delete_private', 'delete_published', 'delete_others', 'edit_private', 'edit_published' ) as $v ) {
			$caps[] = "{$v}_{$plural}";
		}
	}
	return $caps;
}

/** School Admin: full control over every content CPT + routines + uploads. No options/users/system. */
function uturn_school_admin_caps() {
	$caps = array( 'read', 'upload_files', 'uturn_manage_routines', 'uturn_manage_sms', 'uturn_manage_academic' );
	foreach ( uturn_cap_bases() as $single => $plural ) {
		foreach ( array( "edit_{$single}", "read_{$single}", "delete_{$single}" ) as $c ) {
			$caps[] = $c;
		}
		foreach ( array( 'edit', 'edit_others', 'publish', 'read_private', 'delete', 'delete_private', 'delete_published', 'delete_others', 'edit_private', 'edit_published' ) as $v ) {
			$caps[] = "{$v}_{$plural}";
		}
	}
	return $caps;
}

/** Office-assistant subset: notices, downloads, FAQs, testimonials, inbox + applications/results/fees/attendance. */
function uturn_staff_caps() {
	$caps = array( 'read', 'upload_files' );
	$pairs = array(
		'ut_notice' => 'ut_notices', 'ut_download' => 'ut_downloads', 'ut_faq' => 'ut_faqs',
		'ut_testimonial' => 'ut_testimonials', 'ut_message' => 'ut_messages',
		'ut_application' => 'ut_applications', 'ut_result' => 'ut_results', 'ut_fee' => 'ut_fees',
		'ut_attendance' => 'ut_attendances',
	);
	foreach ( $pairs as $single => $plural ) {
		foreach ( array( "edit_{$single}", "read_{$single}", "delete_{$single}", "edit_{$plural}", "publish_{$plural}", "read_private_{$plural}", "delete_{$plural}", "edit_published_{$plural}" ) as $c ) {
			$caps[] = $c;
		}
	}
	return $caps;
}

/** Headmaster: full School Admin power + settings/homepage + user accounts. */
function uturn_headmaster_caps() {
	return array_merge(
		uturn_school_admin_caps(),
		array( 'uturn_manage_settings', 'list_users', 'create_users', 'edit_users', 'delete_users', 'promote_users' )
	);
}

/** Accountant: fee records in full, directories read-only. */
function uturn_accountant_caps() {
	$caps = array( 'read', 'upload_files', 'edit_ut_fee', 'read_ut_fee', 'delete_ut_fee' );
	foreach ( array( 'edit', 'edit_others', 'publish', 'read_private', 'delete', 'delete_private', 'delete_published', 'delete_others', 'edit_private', 'edit_published' ) as $v ) {
		$caps[] = "{$v}_ut_fees";
	}
	foreach ( array( 'read_ut_student', 'read_ut_students', 'read_private_ut_students', 'read_ut_teacher', 'read_ut_teachers', 'read_private_ut_teachers' ) as $c ) {
		$caps[] = $c;
	}
	return $caps;
}

/** Teacher subset: profile media + attendance taking + result marks entry
 * (draft/submit own-class results; publishing stays with SA/Headmaster). */
function uturn_teacher_caps() {
	return array(
		'read', 'upload_files',
		'edit_ut_attendance', 'read_ut_attendance',
		'edit_ut_attendances', 'publish_ut_attendances', 'read_private_ut_attendances',
		'edit_ut_assignment', 'read_ut_assignment', 'delete_ut_assignment',
		'edit_ut_assignments', 'publish_ut_assignments', 'read_private_ut_assignments',
		'read_ut_submission', 'edit_ut_submission',
		'read_ut_submissions', 'edit_ut_submissions', 'read_private_ut_submissions',
		'edit_ut_result', 'read_ut_result',
		'edit_ut_results', 'read_ut_results', 'read_private_ut_results', 'edit_private_ut_results',
	);
}

add_action( 'init', 'uturn_register_roles', 11 );
function uturn_register_roles() {
	if ( get_option( 'uturn_roles_v' ) === UTURN_ROLES_V && get_role( 'uturn_school_admin' ) ) {
		return;
	}
	remove_role( 'uturn_school_admin' );
	remove_role( 'uturn_headmaster' );
	remove_role( 'uturn_accountant' );
	remove_role( 'uturn_staff' );
	remove_role( 'uturn_teacher' );
	remove_role( 'uturn_student' );

	$school = uturn_school_admin_caps();
	add_role( 'uturn_school_admin', 'স্কুল অ্যাডমিন (School Admin)', array_combine( $school, array_fill( 0, count( $school ), true ) ) );
	$hm = uturn_headmaster_caps();
	add_role( 'uturn_headmaster', 'প্রধান শিক্ষক (Headmaster)', array_combine( $hm, array_fill( 0, count( $hm ), true ) ) );
	$acc = uturn_accountant_caps();
	add_role( 'uturn_accountant', 'হিসাবরক্ষক (Accountant)', array_combine( $acc, array_fill( 0, count( $acc ), true ) ) );
	$staff = uturn_staff_caps();
	add_role( 'uturn_staff', 'স্টাফ (Staff)', array_combine( $staff, array_fill( 0, count( $staff ), true ) ) );
	$teach = uturn_teacher_caps();
	add_role( 'uturn_teacher', 'শিক্ষক (Teacher)', array_combine( $teach, array_fill( 0, count( $teach ), true ) ) );
	add_role( 'uturn_student', 'শিক্ষার্থী (Student)', array( 'read' => true, 'upload_files' => true, 'read_ut_assignment' => true, 'read_ut_assignments' => true, 'read_private_ut_assignments' => true, 'edit_ut_submission' => true, 'read_ut_submission' => true, 'edit_ut_submissions' => true, 'publish_ut_submissions' => true, 'read_ut_submissions' => true, 'read_private_ut_submissions' => true ) );

	$admin = get_role( 'administrator' );
	if ( $admin ) {
		foreach ( uturn_all_custom_caps() as $cap ) {
			$admin->add_cap( $cap );
		}
	}
	update_option( 'uturn_roles_v', UTURN_ROLES_V );
}

/* Students get a clean frontend: no admin bar. */
add_filter( 'show_admin_bar', 'uturn_admin_bar_visibility' );
function uturn_admin_bar_visibility( $show ) {
	if ( function_exists( 'uturn_is_shell_user' ) && uturn_is_shell_user() && ! current_user_can( 'manage_options' ) ) {
		return false;
	}
	if ( is_user_logged_in() && (bool) array_intersect( array( 'uturn_teacher', 'uturn_student', 'uturn_accountant', 'uturn_school_admin', 'uturn_headmaster', 'uturn_staff' ), (array) wp_get_current_user()->roles ) ) {
		return false;
	}
	return $show;
}

/* Role-aware sign-in landing: portals for students/teachers. */
add_filter( 'login_redirect', 'uturn_login_redirect', 10, 3 );
function uturn_login_redirect( $to, $requested, $user ) {
	if ( ! $user instanceof WP_User ) {
		return $to;
	}
	if ( function_exists( 'uturn_shell_roles' ) && function_exists( 'uturn_dash_url' ) && (bool) array_intersect( uturn_shell_roles(), (array) $user->roles ) ) {
		return uturn_dash_url();
	}
	return $to;
}

/* Hard wp-admin boundary: shell roles can NEVER see wp-admin — dashboard shell only.
 * Super Admin (manage_options) keeps full wp-admin. Shell form handlers post to
 * admin-post.php, media uploads use async-upload.php, pickers use admin-ajax. */
function uturn_shell_post_actions() {
	return array(
		'uturn_apply', 'uturn_contact', 'uturn_attendance',
		'uturn_entity_save', 'uturn_entity_resetpw', 'uturn_entity_delete',
		'uturn_routines_save', 'uturn_result_sample', 'uturn_result_import',
		'uturn_sms_toggles', 'uturn_sms_send', 'uturn_sms_clearlog',
		'uturn_opts_save', 'uturn_app_status', 'uturn_app_export',
		'uturn_dash_save', 'uturn_dash_delete', 'uturn_dash_setmeta',
		'uturn_dash_profile', 'uturn_user_save',
		'uturn_class_save', 'uturn_class_delete', 'uturn_class_restore',
		'uturn_assignment_save', 'uturn_assignment_delete',
		'uturn_submission_save', 'uturn_grade_save',
		'uturn_subject_save', 'uturn_subject_toggle', 'uturn_subject_delete',
		'uturn_group_save', 'uturn_group_toggle', 'uturn_group_delete',
		'uturn_board_order', 'uturn_board_toggle', 'uturn_teacher_toggle',
		'uturn_editor_save',
		'uturn_marks_save', 'uturn_result_publish', 'uturn_result_sendback',
		'uturn_result_stage', 'uturn_result_archive',
		'uturn_grading_save',
		'uturn_exam_save', 'uturn_exam_toggle', 'uturn_exam_delete',
		'uturn_exam_setup_save',
		'uturn_promote_run',
	);
}
add_action( 'admin_init', 'uturn_admin_gate' );
function uturn_admin_gate() {
	if ( wp_doing_ajax() || wp_doing_cron() ) {
		return;
	}
	$user = wp_get_current_user();
	$is_shell = function_exists( 'uturn_shell_roles' ) && (bool) array_intersect( uturn_shell_roles(), (array) $user->roles );
	$pagenow = isset( $GLOBALS['pagenow'] ) ? $GLOBALS['pagenow'] : '';
	if ( $is_shell && ! current_user_can( 'manage_options' ) ) {
		/* Media uploads + editor ajax are part of the shell experience. */
		if ( in_array( $pagenow, array( 'async-upload.php', 'media-upload.php' ), true ) ) {
			return;
		}
		/* Shell + public forms post here; each handler enforces its own nonce + caps. */
		if ( 'admin-post.php' === $pagenow ) {
			$act = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : '';
			if ( in_array( $act, uturn_shell_post_actions(), true ) ) {
				return;
			}
		}
		wp_safe_redirect( function_exists( 'uturn_dash_url' ) ? uturn_dash_url() : home_url( '/' ) );
		exit;
	}
	/* Non-shell, non-admin users keep the old boundary. */
	if ( current_user_can( 'edit_posts' ) || current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( 'profile.php' === $pagenow ) {
		return;
	}
	if ( 'admin-post.php' === $pagenow ) {
		$act = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : '';
		if ( in_array( $act, array( 'uturn_apply', 'uturn_contact', 'uturn_attendance' ), true ) ) {
			return;
		}
	}
	wp_safe_redirect( home_url( '/' ) );
	exit;
}

/* Headmaster guardrails: Super Admin accounts are untouchable. */
add_filter( 'map_meta_cap', 'uturn_headmaster_limits', 10, 4 );
function uturn_headmaster_limits( $caps, $cap, $user_id, $args ) {
	if ( ! in_array( $cap, array( 'edit_user', 'delete_user', 'promote_user', 'remove_user' ), true ) ) {
		return $caps;
	}
	$actor = get_userdata( $user_id );
	if ( ! $actor || in_array( 'administrator', (array) $actor->roles, true ) ) {
		return $caps;
	}
	if ( ! in_array( 'uturn_headmaster', (array) $actor->roles, true ) ) {
		return $caps;
	}
	$target_id = isset( $args[0] ) ? (int) $args[0] : 0;
	if ( $target_id === (int) $user_id ) {
		return $caps; // own profile stays editable
	}
	$target = $target_id ? get_userdata( $target_id ) : null;
	if ( $target && ( array_intersect( array( 'administrator', 'uturn_headmaster' ), (array) $target->roles ) || user_can( $target, 'manage_options' ) ) ) {
		return array( 'do_not_allow' );
	}
	return $caps;
}

/* Headmaster can hand out every role EXCEPT Super Admin / Headmaster. */
add_filter( 'editable_roles', 'uturn_headmaster_editable_roles' );
function uturn_headmaster_editable_roles( $roles ) {
	$u = wp_get_current_user();
	if ( $u && ! in_array( 'administrator', (array) $u->roles, true ) && in_array( 'uturn_headmaster', (array) $u->roles, true ) ) {
		unset( $roles['administrator'], $roles['uturn_headmaster'] );
	}
	return $roles;
}
