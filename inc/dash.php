<?php
/**
 * EduTurn — Standalone dashboard shell.
 * Teacher / student / accountant / school-admin / headmaster / staff work
 * ENTIRELY here: no wp-admin, no site chrome. Only exit: "Back to Main Site".
 * Legacy wp-admin screens are reused via output buffering + URL remapping;
 * native CPT screens are replaced by the generic CRUD engine (dash-crud.php).
 */

defined( 'ABSPATH' ) || exit;

/* ================= roles + access ================= */
function uturn_shell_roles() {
	return array( 'uturn_teacher', 'uturn_student', 'uturn_accountant', 'uturn_school_admin', 'uturn_headmaster', 'uturn_staff' );
}

function uturn_is_shell_user() {
	if ( ! is_user_logged_in() ) {
		return false;
	}
	if ( current_user_can( 'manage_options' ) ) {
		return true; // super admin may preview the shell
	}
	return (bool) array_intersect( uturn_shell_roles(), (array) wp_get_current_user()->roles );
}

/* ================= dashboard page ================= */
function uturn_dash_page_id() {
	$p = get_page_by_path( 'dashboard', OBJECT, 'page' );
	if ( $p && 'publish' === $p->post_status ) {
		return (int) $p->ID;
	}
	return 0;
}

function uturn_dash_ensure_page() {
	if ( uturn_dash_page_id() ) {
		return;
	}
	$id = wp_insert_post( array( 'post_type' => 'page', 'post_title' => 'ড্যাশবোর্ড', 'post_name' => 'dashboard', 'post_status' => 'publish', 'post_content' => '' ) );
	if ( $id && ! is_wp_error( $id ) ) {
		update_post_meta( $id, '_wp_page_template', 'page-dashboard.php' );
	}
}
add_action( 'init', 'uturn_dash_ensure_page', 20 );

/* Shell list screens reuse ?s= / ?paged= for their own tables — stop WP from
 * hijacking them as blog-search / page-pagination (would 404 the dashboard). */
add_filter( 'request', 'uturn_dash_free_query_vars' );
function uturn_dash_free_query_vars( $qv ) {
	if ( ! is_array( $qv ) ) {
		return $qv;
	}
	$dash = 0;
	if ( isset( $qv['page_id'] ) ) {
		$dash = (int) $qv['page_id'];
	} elseif ( isset( $qv['pagename'] ) && $qv['pagename'] === 'dashboard' ) {
		$dash = uturn_dash_page_id();
	}
	if ( $dash && $dash === uturn_dash_page_id() ) {
		unset( $qv['s'], $qv['paged'] );
	}
	return $qv;
}

function uturn_dash_url() {
	$id = uturn_dash_page_id();
	if ( $id ) {
		return get_permalink( $id );
	}
	return home_url( '/dashboard/' );
}

function uturn_dash_view_url( $view, $args = array() ) {
	return add_query_arg( array_merge( array( 'view' => $view ), $args ), uturn_dash_url() );
}

/* ================= URL remap (admin → shell) ================= */
function uturn_dash_page_map() {
	return array(
		'eduturn-students' => 'students', 'eduturn-teachers' => 'teachers', 'eduturn-staffs' => 'staffs',
		'eduturn-routines' => 'routines', 'eduturn-result-import' => 'result-import', 'eduturn-sms' => 'sms',
		'eduturn-settings' => 'settings', 'eduturn-homepage' => 'settings',
		'eduturn-license' => 'home', 'eduturn-seeder' => 'home',
	);
}

function uturn_dash_cpt_map() {
	return array(
		'ut_attendance' => 'attendance', 'ut_result' => 'results', 'ut_fee' => 'fees',
		'ut_application' => 'applications', 'ut_message' => 'messages', 'ut_notice' => 'notices',
		'ut_news' => 'news', 'ut_event' => 'events', 'ut_album' => 'albums', 'ut_download' => 'downloads',
		'ut_faq' => 'faqs', 'ut_testimonial' => 'testimonials', 'ut_achievement' => 'achievements',
		'ut_teacher' => 'teachers', 'ut_student' => 'students', 'ut_staff' => 'staffs',
	);
}

/** Remap one admin URL to its shell equivalent (used by uturn_back + router). */
function uturn_dash_remap_url( $url ) {
	$parts = wp_parse_url( $url );
	$site  = wp_parse_url( home_url() );
	if ( ! empty( $parts['host'] ) && strcasecmp( $parts['host'], $site['host'] ) !== 0 ) {
		return $url; // never touch off-site URLs
	}
	$path = isset( $parts['path'] ) ? $parts['path'] : '';
	$q    = array();
	if ( ! empty( $parts['query'] ) ) {
		parse_str( $parts['query'], $q );
	}
	$pmap = uturn_dash_page_map();
	$cmap = uturn_dash_cpt_map();
	$view = null;
	if ( substr( $path, -9 ) === 'admin.php' && ! empty( $q['page'] ) ) {
		$pg = $q['page'];
		unset( $q['page'] );
		if ( $pg === 'eduturn' ) {
			$view = 'home';
		} elseif ( $pg === 'eduturn-homepage' ) {
			$view = 'settings';
			$q['tab'] = 'home';
		} elseif ( isset( $pmap[ $pg ] ) ) {
			$view = $pmap[ $pg ];
		}
	} elseif ( substr( $path, -8 ) === 'edit.php' && ! empty( $q['post_type'] ) && isset( $cmap[ $q['post_type'] ] ) ) {
		$view = $cmap[ $q['post_type'] ];
		unset( $q['post_type'] );
	} elseif ( substr( $path, -8 ) === 'post.php' && ! empty( $q['post'] ) ) {
		$view = 'edit';
		$q['id'] = absint( $q['post'] );
		unset( $q['post'], $q['action'] );
	} elseif ( substr( $path, -12 ) === 'post-new.php' && ! empty( $q['post_type'] ) ) {
		$view = 'edit';
		$q['cpt'] = sanitize_key( $q['post_type'] );
		unset( $q['post_type'] );
	} elseif ( substr( $path, -9 ) === 'users.php' || substr( $path, -13 ) === 'user-edit.php' ) {
		$view = 'users';
		if ( ! empty( $q['user_id'] ) ) {
			$q['sub'] = 'edit';
		}
	} elseif ( substr( $path, -11 ) === 'profile.php' ) {
		$view = 'profile';
	} elseif ( strpos( $path, 'teacher-portal' ) !== false ) {
		$view = 'take-attendance';
		$q = array();
	} elseif ( strpos( $path, 'student-portal' ) !== false ) {
		$view = 'home';
		$q = array();
	}
	if ( $view === null ) {
		return $url;
	}
	unset( $q['ut_shell'] );
	return uturn_dash_view_url( $view, $q );
}

/** Remap legacy screen HTML: admin links → shell links + shell marker in forms. */
function uturn_dash_remap( $html ) {
	$admin = trailingslashit( admin_url() );
	// longest page keys first so eduturn-students wins over bare eduturn
	$pmap = uturn_dash_page_map();
	uksort( $pmap, function ( $a, $b ) { return strlen( $b ) - strlen( $a ); } );
	foreach ( $pmap as $pg => $view ) {
		$extra = $pg === 'eduturn-homepage' ? array( 'tab' => 'home' ) : array();
		$html  = str_replace( $admin . 'admin.php?page=' . $pg, uturn_dash_view_url( $view, $extra ), $html );
	}
	$html = str_replace( $admin . 'admin.php?page=eduturn', uturn_dash_view_url( 'home' ), $html );
	foreach ( uturn_dash_cpt_map() as $cpt => $view ) {
		$html = str_replace( $admin . 'edit.php?post_type=' . $cpt, uturn_dash_view_url( $view ), $html );
	}
	$qm = strpos( uturn_dash_url(), '?' ) === false ? '?' : '&'; // plain permalinks already carry ?page_id=
	$html = preg_replace(
		'~https?://\S+?/wp-admin/post\.php\?post=(\d+)(&#038;|&|&)action=edit~',
		uturn_dash_url() . $qm . 'view=edit&id=$1', $html
	);
	$html = preg_replace(
		'~https?://\S+?/wp-admin/post-new\.php\?post_type=(ut_[a-z]+)~',
		uturn_dash_url() . $qm . 'view=edit&cpt=$1', $html
	);
	$html = preg_replace(
		'~https?://\S+?/wp-admin/user-edit\.php\?user_id=(\d+)~',
		uturn_dash_url() . $qm . 'view=users&sub=edit&user_id=$1', $html
	);
	foreach ( $pmap as $pg => $view ) {
		$html = str_replace( 'name="page" value="' . $pg . '"', 'name="view" value="' . $view . '"', $html );
		$html = str_replace( "name='page' value='" . $pg . "'", "name='view' value='" . $view . "'", $html );
	}
	$html = str_replace( $admin . 'users.php?', uturn_dash_view_url( 'users' ) . '&', $html );
	$html = str_replace( $admin . 'users.php', uturn_dash_view_url( 'users' ), $html );
	$html = str_replace( $admin . 'profile.php?', uturn_dash_view_url( 'profile' ) . '&', $html );
	$html = str_replace( $admin . 'profile.php', uturn_dash_view_url( 'profile' ), $html );
	// relative admin URLs (WP list tables emit these) — same maps, quote-anchored
	$du = uturn_dash_url();
	foreach ( $pmap as $pg => $view ) {
		$html = str_replace( '"admin.php?page=' . $pg . '&', '"' . uturn_dash_view_url( $view ) . '&', $html );
		$html = str_replace( '"admin.php?page=' . $pg . '"', '"' . uturn_dash_view_url( $view ) . '"', $html );
	}
	$html = str_replace( '"admin.php?page=eduturn&', '"' . uturn_dash_view_url( 'home' ) . '&', $html );
	foreach ( uturn_dash_cpt_map() as $cpt => $view ) {
		$html = str_replace( '"edit.php?post_type=' . $cpt . '&', '"' . uturn_dash_view_url( $view ) . '&', $html );
		$html = str_replace( '"edit.php?post_type=' . $cpt . '"', '"' . uturn_dash_view_url( $view ) . '"', $html );
	}
	$html = preg_replace( '~"post\.php\?post=(\d+)(&#038;|&|&)action=edit~', '"' . $du . $qm . 'view=edit&id=$1', $html );
	$html = preg_replace( '~"post-new\.php\?post_type=(ut_[a-z]+)~', '"' . $du . $qm . 'view=edit&cpt=$1', $html );
	$html = preg_replace( '~"user-edit\.php\?user_id=(\d+)~', '"' . $du . $qm . 'view=users&sub=edit&user_id=$1', $html );
	$html = str_replace( '"users.php?', '"' . uturn_dash_view_url( 'users' ) . '&', $html );
	$html = str_replace( '"users.php"', '"' . uturn_dash_view_url( 'users' ) . '"', $html );
	$html = str_replace( '"profile.php"', '"' . uturn_dash_view_url( 'profile' ) . '"', $html );
	// entity screens use ?view=list|form internally — free the "view" param for the router
	$html = preg_replace( '~(\?|&|&#038;)view=(form|list)\b~', '${1}v2=${2}', $html );
	// shell marker: every form + every admin-post GET link
	$html = preg_replace( '#<form\\b([^>]*)>#i', '<form$1>' . "\n" . '<input type="hidden" name="ut_shell" value="1">', $html );
	$html = str_replace( 'admin-post.php?action=', 'admin-post.php?ut_shell=1&action=', $html );
	return $html;
}

/** Handler redirect helper: back to the shell when the request came from it. */
function uturn_back( $admin_url ) {
	if ( ! empty( $_REQUEST['ut_shell'] ) ) {
		return uturn_dash_remap_url( $admin_url );
	}
	return $admin_url;
}

/* ================= assets ================= */
add_action( 'wp_enqueue_scripts', 'uturn_dash_assets' );
function uturn_dash_assets() {
	if ( ! is_page() || get_the_ID() !== uturn_dash_page_id() ) {
		return;
	}
	wp_enqueue_style( 'dashicons' );
	if ( function_exists( 'uturn_font_url' ) ) {
		wp_enqueue_style( 'uturn-dash-fonts', uturn_font_url(), array(), null );
	}
	wp_enqueue_style( 'uturn-dash', get_template_directory_uri() . '/assets/css/dashboard.css', array(), UTURN_VERSION );
	wp_enqueue_style( 'uturn-dash-pro', get_template_directory_uri() . '/assets/css/dash-pro.css', array( 'uturn-dash' ), UTURN_VERSION );
	wp_enqueue_script( 'uturn-dash-pro', get_template_directory_uri() . '/assets/js/dash-pro.js', array( 'jquery' ), UTURN_VERSION, true );
	wp_enqueue_media();
	wp_enqueue_style( 'wp-color-picker' );
	if ( wp_script_is( 'wp-color-picker', 'registered' ) ) {
		wp_enqueue_script( 'wp-color-picker' );
	}
	wp_enqueue_script( 'jquery' );
	if ( file_exists( get_template_directory() . '/assets/js/admin.js' ) ) {
		$deps = array( 'jquery' );
		if ( wp_script_is( 'wp-color-picker', 'registered' ) ) {
			$deps[] = 'wp-color-picker';
		}
		wp_enqueue_script( 'eduturn-admin', get_template_directory_uri() . '/assets/js/admin.js', $deps, UTURN_VERSION, true );
	}
	add_action( 'wp_print_footer_scripts', 'uturn_dash_footer_js', 20 );
}

/* Media + color pickers (same behavior as wp-admin meta boxes). */
function uturn_dash_footer_js() {
	echo "<script>jQuery(function($){if($.fn.wpColorPicker)$('.eduturn-color').wpColorPicker();var f;$(document).on('click','.eduturn-media-btn',function(e){e.preventDefault();var b=$(this);f=wp.media({title:'মিডিয়া বেছে নিন',button:{text:'ব্যবহার করুন'},multiple:false});f.on('select',function(){var a=f.state().get('selection').first().toJSON();b.siblings('.eduturn-media-id').val(a.id);var p=b.siblings('.eduturn-media-prev');if(p.length&&a.sizes&&a.sizes.thumbnail){p.html('<img src=\"'+a.sizes.thumbnail.url+'\" alt=\"\" style=\"max-width:90px;height:auto;display:block;margin-bottom:6px\">');}});f.open();});$(document).on('click','.eduturn-media-clear',function(e){e.preventDefault();$(this).siblings('.eduturn-media-id').val('');$(this).siblings('.eduturn-media-prev').html('');});});</script>";
}

/* ================= view registry + nav ================= */
function uturn_dash_views() {
	return array(
		'home'            => array( 'হোম', 'dashicons-admin-home', null, 'uturn_dash_home', 'ড্যাশবোর্ড' ),
		'profile'         => array( 'প্রোফাইল', 'dashicons-admin-users', null, 'uturn_dash_profile', 'আমার প্রোফাইল' ),
		'students'        => array( 'শিক্ষার্থীবৃন্দ', 'dashicons-graduation-cap', 'uturn_manage_academic', 'uturn_dash_legacy_students', 'শিক্ষার্থীবৃন্দ' ),
		'teachers'        => array( 'শিক্ষকমণ্ডলী', 'dashicons-businessman', 'uturn_manage_academic', 'uturn_dash_legacy_teachers', 'শিক্ষকমণ্ডলী' ),
		'staffs'          => array( 'কর্মচারীবৃন্দ', 'dashicons-id', 'uturn_manage_academic', 'uturn_dash_legacy_staffs', 'কর্মচারীবৃন্দ' ),
		'attendance'      => array( 'হাজিরা রেকর্ড', 'dashicons-schedule', 'edit_ut_attendances', 'uturn_dash_crud_dispatch', 'হাজিরা রেকর্ড' ),
		'routines'        => array( 'রুটিন বিল্ডার', 'dashicons-clock', 'uturn_manage_routines', 'uturn_dash_legacy_routines', 'রুটিন বিল্ডার' ),
		'result-import'   => array( 'ফলাফল ইমপোর্ট', 'dashicons-upload', 'publish_ut_results', 'uturn_dash_legacy_import', 'ফলাফল ইমপোর্ট' ),
		'results'         => array( 'ফলাফল ডাটাবেজ', 'dashicons-chart-bar', 'publish_ut_results', 'uturn_dash_crud_dispatch', 'ফলাফল ডাটাবেজ' ),
		'result-entry'    => array( 'নম্বর এন্ট্রি', 'dashicons-edit-large', 'edit_ut_results', 'uturn_dash_result_entry', 'নম্বর এন্ট্রি' ),
		'result-review'   => array( 'ফলাফল পর্যালোচনা', 'dashicons-yes-alt', 'publish_ut_results', 'uturn_dash_result_review', 'ফলাফল পর্যালোচনা ও প্রকাশ' ),
		'promotion'       => array( 'শ্রেণি উন্নীতকরণ', 'dashicons-graduation-cap', 'uturn_manage_academic', 'uturn_dash_promotion', 'শ্রেণি উন্নীতকরণ' ),
		'grading'         => array( 'গ্রেডিং স্কেল', 'dashicons-chart-line', 'uturn_manage_academic', 'uturn_dash_grading', 'গ্রেডিং স্কেল' ),
		'fee-receipt'     => array( '', '', 'edit_ut_fees', 'uturn_dash_fee_receipt', 'ফি রসিদ' ),
		'student-sheet'   => array( '', '', 'uturn_manage_academic', 'uturn_dash_student_sheet', 'শিক্ষার্থী প্রোফাইল' ),
		'fees'            => array( 'ফি রেকর্ড', 'dashicons-money-alt', 'edit_ut_fees', 'uturn_dash_crud_dispatch', 'ফি রেকর্ড' ),
		'applications'    => array( 'ভর্তি আবেদন', 'dashicons-clipboard', 'edit_ut_applications', 'uturn_dash_crud_dispatch', 'ভর্তি আবেদন' ),
		'messages'        => array( 'ইনবক্স', 'dashicons-email-alt', 'edit_ut_messages', 'uturn_dash_crud_dispatch', 'ইনবক্স' ),
		'notices'         => array( 'নোটিশ', 'dashicons-megaphone', 'edit_ut_notices', 'uturn_dash_crud_dispatch', 'নোটিশ' ),
		'news'            => array( 'সংবাদ', 'dashicons-media-document', 'edit_ut_news_items', 'uturn_dash_crud_dispatch', 'সংবাদ' ),
		'events'          => array( 'ইভেন্ট', 'dashicons-calendar-alt', 'edit_ut_events', 'uturn_dash_crud_dispatch', 'ইভেন্ট' ),
		'albums'          => array( 'গ্যালারি', 'dashicons-format-gallery', 'edit_ut_albums', 'uturn_dash_crud_dispatch', 'গ্যালারি অ্যালবাম' ),
		'downloads'       => array( 'ডাউনলোড', 'dashicons-download', 'edit_ut_downloads', 'uturn_dash_crud_dispatch', 'ডাউনলোড' ),
		'faqs'            => array( 'জিজ্ঞাসা', 'dashicons-editor-help', 'edit_ut_faqs', 'uturn_dash_crud_dispatch', 'জিজ্ঞাসা' ),
		'testimonials'    => array( 'মতামত', 'dashicons-format-quote', 'edit_ut_testimonials', 'uturn_dash_crud_dispatch', 'মতামত' ),
		'achievements'    => array( 'অর্জন', 'dashicons-awards', 'edit_ut_achievements', 'uturn_dash_crud_dispatch', 'অর্জন' ),
		'sms'             => array( 'SMS পাঠান', 'dashicons-smartphone', 'uturn_manage_sms', 'uturn_dash_legacy_sms', 'SMS পাঠান' ),
		'settings'        => array( 'সেটিংস', 'dashicons-admin-generic', 'uturn_manage_settings', 'uturn_dash_legacy_settings', 'সেটিংস' ),
		'users'           => array( 'ব্যবহারকারী', 'dashicons-groups', 'list_users', 'uturn_dash_users', 'ব্যবহারকারী' ),
		'edit'            => array( '', '', null, 'uturn_dash_crud_edit_dispatch', 'সম্পাদনা' ),
		'my-results'      => array( 'আমার ফলাফল', 'dashicons-chart-bar', array( 'uturn_student' ), 'uturn_dash_my_results', 'আমার ফলাফল' ),
		'my-fees'         => array( 'আমার ফি', 'dashicons-money-alt', array( 'uturn_student' ), 'uturn_dash_my_fees', 'আমার ফি' ),
		'my-attendance'   => array( 'আমার হাজিরা', 'dashicons-schedule', array( 'uturn_student' ), 'uturn_dash_my_attendance', 'আমার হাজিরা' ),
		'take-attendance' => array( 'হাজিরা নিন', 'dashicons-yes-alt', array( 'uturn_teacher', 'uturn_school_admin', 'uturn_headmaster' ), 'uturn_dash_take_attendance', 'হাজিরা নিন' ),
		'my-students'     => array( 'আমার শিক্ষার্থী', 'dashicons-groups', array( 'uturn_teacher' ), 'uturn_dash_my_students', 'শিক্ষার্থী তালিকা' ),
		'my-classes'      => array( 'আমার ক্লাস', 'dashicons-welcome-learn-more', array( 'uturn_teacher' ), 'uturn_dash_my_classes', 'আমার ক্লাস' ),
		'classes'         => array( 'শ্রেণি ব্যবস্থাপনা', 'dashicons-welcome-learn-more', 'uturn_manage_academic', 'uturn_dash_classes', 'শ্রেণি ব্যবস্থাপনা' ),
		'exams'           => array( 'পরীক্ষাসমূহ', 'dashicons-awards', 'uturn_manage_academic', 'uturn_dash_exams', 'পরীক্ষা ব্যবস্থাপনা' ),
		'assignments'     => array( 'অ্যাসাইনমেন্ট', 'dashicons-clipboard', 'edit_ut_assignments', 'uturn_dash_assignments', 'অ্যাসাইনমেন্ট ও অনলাইন টেস্ট' ),
		'my-assignments'  => array( 'আমার অ্যাসাইনমেন্ট', 'dashicons-clipboard', array( 'uturn_student' ), 'uturn_dash_my_assignments', 'আমার অ্যাসাইনমেন্ট' ),
		'admit-cards'     => array( 'প্রবেশপত্র', 'dashicons-id-alt', 'edit_ut_results', 'uturn_dash_admit_cards', 'প্রবেশপত্র তৈরি' ),
		'results-view'    => array( 'ফলাফল দেখুন', 'dashicons-search', array( 'uturn_teacher' ), 'uturn_dash_results_view', 'ফলাফল দেখুন' ),
		'routine-view'    => array( 'ক্লাস রুটিন', 'dashicons-clock', array( 'uturn_teacher', 'uturn_student' ), 'uturn_dash_routine_view', 'ক্লাস রুটিন' ),
		'downloads-view'  => array( 'ডাউনলোড', 'dashicons-download', array( 'uturn_teacher', 'uturn_student' ), 'uturn_dash_downloads_view', 'ডাউনলোড' ),
		'snotices'        => array( 'নোটিশ বোর্ড', 'dashicons-megaphone', array( 'uturn_teacher', 'uturn_student' ), 'uturn_dash_snotices', 'নোটিশ বোর্ড' ),
		'notice'          => array( '', '', null, 'uturn_dash_notice_read', 'নোটিশ' ),
		'directory'       => array( 'ডিরেক্টরি', 'dashicons-book-alt', array( 'uturn_headmaster', 'uturn_school_admin', 'uturn_staff', 'uturn_accountant' ), 'uturn_dash_directory', 'ডিরেক্টরি' ),
		'roles'           => array( 'ভূমিকা ও ক্ষমতা', 'dashicons-shield', 'list_users', 'uturn_dash_roles_cap', 'ভূমিকা ও ক্ষমতা' ),
		'subjects'        => array( 'বিষয়সমূহ', 'dashicons-book', 'uturn_manage_academic', 'uturn_dash_subjects', 'বিষয় ব্যবস্থাপনা' ),
		'groups'          => array( 'বিভাগসমূহ', 'dashicons-category', 'uturn_manage_academic', 'uturn_dash_acad_groups', 'বিভাগ ব্যবস্থাপনা' ),
		'board'           => array( 'পরিচালনা পর্ষদ', 'dashicons-bank', 'edit_ut_boards', 'uturn_dash_board', 'পরিচালনা পর্ষদ' ),
		'editor'          => array( 'ভিজ্যুয়াল এডিটর', 'dashicons-welcome-view-site', 'uturn_manage_settings', 'uturn_dash_editor', 'ভিজ্যুয়াল এডিটর' ),
	);
}

function uturn_dash_can( $view ) {
	$views = uturn_dash_views();
	if ( ! isset( $views[ $view ] ) ) {
		return false;
	}
	$need = $views[ $view ][2];
	if ( $need === null ) {
		return true;
	}
	if ( is_array( $need ) ) {
		return (bool) array_intersect( $need, (array) wp_get_current_user()->roles ) || current_user_can( 'manage_options' );
	}
	return current_user_can( $need );
}

function uturn_dash_nav() {
	$u = wp_get_current_user();
	$r = (array) $u->roles;
	$is_student = in_array( 'uturn_student', $r, true );
	$is_teacher = in_array( 'uturn_teacher', $r, true );
	$nav = array( array( 'sec' => 'প্রধান', 'items' => array( 'home', 'profile' ) ) );
	if ( $is_student && ! current_user_can( 'manage_options' ) ) {
		$nav[] = array( 'sec' => 'আমার পড়াশোনা', 'items' => array( 'my-results', 'my-fees', 'my-attendance', 'my-assignments', 'routine-view', 'downloads-view', 'snotices' ) );
		return $nav;
	}
	if ( $is_teacher && ! current_user_can( 'manage_options' ) ) {
		$nav[] = array( 'sec' => 'শ্রেণি কার্যক্রম', 'items' => array( 'take-attendance', 'my-students', 'assignments', 'result-entry', 'results-view', 'routine-view', 'downloads-view', 'snotices' ) );
		return $nav;
	}
	$nav[] = array( 'sec' => '📚 একাডেমিক', 'items' => array( 'subjects', 'groups', 'classes', 'exams', 'grading', 'routines', 'assignments', 'result-entry', 'result-review', 'result-import', 'results', 'promotion', 'admit-cards', 'attendance', 'take-attendance' ) );
	$nav[] = array( 'sec' => '👥 শিক্ষক ও শিক্ষার্থী', 'items' => array( 'teachers', 'students', 'staffs', 'fees' ) );
	$nav[] = array( 'sec' => '🏛️ প্রশাসন', 'items' => array( 'board', 'directory', 'users', 'roles' ) );
	$nav[] = array( 'sec' => '🌐 ওয়েবসাইট', 'items' => array( 'editor', 'notices', 'news', 'events', 'albums', 'downloads', 'faqs', 'testimonials', 'achievements', 'applications', 'messages', 'sms' ) );
	$nav[] = array( 'sec' => '⚙️ সিস্টেম', 'items' => array( 'settings' ) );
	return $nav;
}

/* ================= render ================= */
function uturn_dash_render() {
	if ( ! uturn_is_shell_user() ) {
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}
	/* Admin-only helpers the shell + legacy screens rely on (definition-only files). */
	if ( ! function_exists( 'submit_button' ) ) {
		require_once ABSPATH . 'wp-admin/includes/template.php';
	}
	if ( ! class_exists( 'WP_Screen' ) ) {
		require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
	}
	if ( ! function_exists( 'set_current_screen' ) ) {
		require_once ABSPATH . 'wp-admin/includes/screen.php';
	}
	if ( ! function_exists( '_get_list_table' ) ) {
		require_once ABSPATH . 'wp-admin/includes/list-table.php';
	}
	if ( ! function_exists( 'get_editable_roles' ) ) {
		require_once ABSPATH . 'wp-admin/includes/user.php';
	}
	$view  = isset( $_GET['view'] ) ? sanitize_key( $_GET['view'] ) : 'home';
	$views = uturn_dash_views();
	if ( ! isset( $views[ $view ] ) ) {
		$view = 'home';
	}
	$def = $views[ $view ];
	if ( ! uturn_dash_can( $view ) ) {
		$view = 'home';
		$def  = $views['home'];
		if ( ! uturn_dash_can( 'home' ) ) {
			wp_die( 'অ্যাক্সেস নেই।' );
		}
	}
	$st    = function_exists( 'uturn_dash_stats' ) ? uturn_dash_stats() : array();
	$bn    = function_exists( 'uturn_bn' ) ? 'uturn_bn' : 'strval';
	$u     = wp_get_current_user();
	$school = function_exists( 'uturn_opt' ) ? uturn_opt( 'school_name_bn', get_bloginfo( 'name' ) ) : get_bloginfo( 'name' );
	$logo  = get_template_directory_uri() . '/assets/images/logo.svg';
	$elogo = get_template_directory_uri() . '/assets/images/eduturn-logo.png';
	$role  = function_exists( 'uturn_role_label' ) ? uturn_role_label() : '';
	echo '<div class="utd-app"><div class="utx-scrim" data-utx-scrim></div><aside class="utd-side" id="utx-side">';
	echo '<div class="utd-ebrand"><img src="' . esc_url( $elogo ) . '" alt="EduTurn"></div>';
	echo '<div class="utd-brand"><img src="' . esc_url( $logo ) . '" alt=""><div><b>' . esc_html( $school ) . '</b><span>ড্যাশবোর্ড</span></div></div>';
	foreach ( uturn_dash_nav() as $g ) {
		$items = '';
		foreach ( $g['items'] as $v ) {
			if ( ! uturn_dash_can( $v ) || ! isset( $views[ $v ] ) ) {
				continue;
			}
			$badge = '';
			if ( $v === 'applications' && ! empty( $st['pending'] ) ) {
				$badge = '<span class="cnt">' . esc_html( call_user_func( $bn, $st['pending'] ) ) . '</span>';
			} elseif ( $v === 'fees' && ! empty( $st['due'] ) ) {
				$badge = '<span class="cnt">' . esc_html( call_user_func( $bn, $st['due'] ) ) . '</span>';
			} elseif ( $v === 'notices' && ! empty( $st['notices'] ) ) {
				$badge = '<span class="cnt blue">' . esc_html( call_user_func( $bn, $st['notices'] ) ) . '</span>';
			}
			$items .= '<a href="' . esc_url( uturn_dash_view_url( $v ) ) . '" class="' . ( $v === $view ? 'on' : '' ) . '"><span class="dashicons ' . esc_attr( $views[ $v ][1] ) . '"></span>' . esc_html( $views[ $v ][0] ) . $badge . '</a>';
		}
		if ( $items !== '' ) {
			echo '<div class="utd-sec">' . esc_html( $g['sec'] ) . '</div><nav class="utd-nav">' . $items . '</nav>';
		}
	}
	if ( current_user_can( 'manage_options' ) ) {
		echo '<div class="utd-sec">সুপার</div><nav class="utd-nav"><a href="' . esc_url( admin_url() ) . '"><span class="dashicons dashicons-wordpress"></span>WP Admin →</a></nav>';
	}
	echo '<div class="utd-side-foot"><div class="utd-me">👤 <b>' . esc_html( $u->display_name ) . '</b><br>' . esc_html( $role ) . '</div>';
	echo '<a class="utd-back" href="' . esc_url( home_url( '/' ) ) . '">← Back to Main Site</a>';
	echo '<a class="utd-out" href="' . esc_url( wp_logout_url( home_url( '/' ) ) ) . '">সাইন আউট</a>';
	echo uturn_dev_credit( 'dark' ) . '</div></aside>';
	echo '<a class="skip-link" href="#utdMain">মূল কনটেন্টে যান</a>';
	echo '<main class="utd-main" id="utdMain"><div class="utd-top"><button type="button" class="utx-burger" data-utx-burger aria-label="মেনু">☰</button><h1>' . esc_html( $def[4] ) . '</h1><span class="pill">' . esc_html( $role ) . ' · ' . esc_html( wp_date( 'j F Y' ) ) . '</span></div><div class="utd-wrap">';
	ob_start();
	call_user_func( $def[3], $view );
	$out = ob_get_clean();
	if ( preg_match( '/[?&]page_id=(\\d+)/', uturn_dash_url(), $mm ) ) {
		/* Plain permalinks: GET forms submit to the path and would lose page_id. */
		$out = preg_replace( '#<form\\b([^>]*\\bmethod=(["\'])get\\2[^>]*)>#i', '<form$1><input type="hidden" name="page_id" value="' . absint( $mm[1] ) . '">', $out );
	}
	echo $out;
	echo '</div></main><div class="utx-toasts" id="utx-toasts" aria-live="polite"></div></div>';
}

/* Developer credit: "EduTurn · Developed by U-Turn" + logo (white on dark). */
function uturn_dev_credit( $theme = 'dark' ) {
	$logo = get_template_directory_uri() . '/assets/images/' . ( $theme === 'dark' ? 'uturn-logo-white.png' : 'uturn-logo.png' );
	return '<div class="utd-credit"><span>EduTurn · Developed by</span><a href="https://uturndigital.com.bd" target="_blank" rel="noopener"><img src="' . esc_url( $logo ) . '" alt="U-Turn Digital Solutions"></a></div>';
}

/* Legacy wp-admin screens, buffered + remapped into the shell. */
function uturn_dash_legacy( $fn, $arg = null ) {
	if ( $arg !== null ) {
		/* Entity screens read view=list|form — shell uses v2; default to list
		 * so the "add new" button + list branch render (else $view='students'). */
		$_GET['view'] = isset( $_GET['v2'] ) ? sanitize_key( $_GET['v2'] ) : 'list';
	}
	ob_start();
	if ( $arg !== null ) {
		call_user_func( $fn, $arg );
	} else {
		call_user_func( $fn );
	}
	echo '<div class="utd-card utd-legacy">' . uturn_dash_remap( ob_get_clean() ) . '</div>';
}
function uturn_dash_legacy_students() { uturn_dash_legacy( 'uturn_entity_screen', 'student' ); }
function uturn_dash_legacy_teachers() { uturn_dash_legacy( 'uturn_entity_screen', 'teacher' ); }
function uturn_dash_legacy_staffs() { uturn_dash_legacy( 'uturn_entity_screen', 'staff' ); }
function uturn_dash_legacy_routines() { uturn_dash_legacy( 'uturn_routines_page' ); }
function uturn_dash_legacy_import() { uturn_dash_legacy( 'uturn_result_import_page' ); }
function uturn_dash_legacy_sms() { uturn_dash_legacy( 'uturn_sms_page' ); }
function uturn_dash_legacy_settings() { uturn_dash_legacy( 'uturn_options_page' ); }

/* ================= home ================= */
function uturn_dash_home() {
	$r = (array) wp_get_current_user()->roles;
	if ( in_array( 'uturn_student', $r, true ) && ! current_user_can( 'manage_options' ) ) {
		uturn_dash_student_home();
		return;
	}
	if ( in_array( 'uturn_teacher', $r, true ) && ! current_user_can( 'manage_options' ) ) {
		uturn_dash_teacher_home();
		return;
	}
	$st = uturn_dash_stats();
	$bn = function_exists( 'uturn_bn' ) ? 'uturn_bn' : 'strval';
	$cards = array(
		array( 'শিক্ষার্থী', $st['students'], 'students', 'uturn_manage_academic' ),
		array( 'শিক্ষক', $st['teachers'], 'teachers', 'uturn_manage_academic' ),
		array( 'নোটিশ', $st['notices'], 'notices', 'edit_ut_notices' ),
		array( 'অপেক্ষমান আবেদন', $st['pending'], 'applications', 'edit_ut_applications' ),
		array( 'বকেয়া ফি', $st['due'], 'fees', 'edit_ut_fees' ),
		array( 'এ মাসে আদায় (৳)', $st['collected_total'], 'fees', 'edit_ut_fees' ),
		array( 'আজ হাজিরা (শ্রেণি)', $st['att_today'], 'attendance', 'edit_ut_attendances' ),
	);
	echo '<div class="utd-stats">';
	foreach ( $cards as $c ) {
		if ( ! current_user_can( $c[3] ) ) {
			continue;
		}
		echo '<a class="utd-stat" href="' . esc_url( uturn_dash_view_url( $c[2] ) ) . '"><b>' . esc_html( call_user_func( $bn, $c[1] ) ) . '</b><span>' . esc_html( $c[0] ) . '</span></a>';
	}
	echo '</div><div class="utd-cols"><div>';
	$views = uturn_dash_views();
	foreach ( uturn_dash_nav() as $g ) {
		if ( $g['sec'] === 'প্রধান' || $g['sec'] === 'সুপার' ) {
			continue;
		}
		$tiles = '';
		foreach ( $g['items'] as $v ) {
			if ( ! uturn_dash_can( $v ) || $views[ $v ][0] === '' ) {
				continue;
			}
			$badge = '';
			if ( $v === 'applications' && ! empty( $st['pending'] ) ) {
				$badge = ' <span class="utd-badge">' . esc_html( call_user_func( $bn, $st['pending'] ) ) . '</span>';
			} elseif ( $v === 'fees' && ! empty( $st['due'] ) ) {
				$badge = ' <span class="utd-badge">' . esc_html( call_user_func( $bn, $st['due'] ) ) . '</span>';
			}
			$tiles .= '<a class="utd-tile" href="' . esc_url( uturn_dash_view_url( $v ) ) . '"><span class="dashicons ' . esc_attr( $views[ $v ][1] ) . '"></span><span><b>' . esc_html( $views[ $v ][0] ) . $badge . '</b></span></a>';
		}
		if ( $tiles !== '' ) {
			echo '<div class="utd-card"><h2>' . esc_html( $g['sec'] ) . '</h2><div class="utd-tiles">' . $tiles . '</div></div>';
		}
	}
	echo '</div><div>';
	if ( current_user_can( 'edit_ut_applications' ) ) {
		$apps = get_posts( array( 'post_type' => 'ut_application', 'posts_per_page' => 5, 'meta_key' => '_ut_status', 'meta_value' => 'pending', 'orderby' => 'date', 'order' => 'DESC' ) );
		echo '<div class="utd-card"><h2>⏳ করণীয় — ভর্তি আবেদন</h2>';
		if ( $apps ) {
			echo '<ul class="utd-list">';
			foreach ( $apps as $a ) {
				echo '<li><a href="' . esc_url( uturn_dash_view_url( 'edit', array( 'cpt' => 'ut_application', 'id' => $a->ID ) ) ) . '">' . esc_html( $a->post_title ) . '</a><br><small class="utd-muted">' . esc_html( get_post_meta( $a->ID, '_ut_class', true ) ) . ' · ' . esc_html( mysql2date( 'j F', $a->post_date ) ) . '</small></li>';
			}
			echo '</ul><p><a class="button button-secondary" href="' . esc_url( uturn_dash_view_url( 'applications' ) ) . '">সব আবেদন দেখুন</a></p>';
		} else {
			echo '<p class="utd-ok">✅ কোনো অপেক্ষমান আবেদন নেই।</p>';
		}
		echo '</div>';
	}
	if ( in_array( 'uturn_headmaster', (array) wp_get_current_user()->roles, true ) && function_exists( 'eduturn_license_effective' ) ) {
		$eff = eduturn_license_effective( eduturn_license_state(), time() );
		$lbl = array( 'active' => 'সক্রিয়', 'expired' => 'মেয়াদোত্তীর্ণ', 'suspended' => 'স্থগিত', 'revoked' => 'বাতিল', 'trial_expired' => 'ট্রায়াল শেষ', 'link_dead' => 'সংযোগ বিচ্ছিন্ন', 'inactive' => 'সক্রিয় নয়' );
		$txt = isset( $lbl[ $eff['code'] ] ) ? $lbl[ $eff['code'] ] : $eff['code'];
		echo '<div class="utd-card"><h2>🔑 লাইসেন্স</h2><p>অবস্থা: <span class="' . ( $eff['code'] === 'active' ? 'utd-ok' : 'utd-warn' ) . '">' . esc_html( $txt ) . '</span>';
		if ( $eff['code'] === 'active' && $eff['remaining'] !== null ) {
			echo '<br>বাকি: <b>' . esc_html( call_user_func( $bn, $eff['remaining'] ) ) . ' দিন</b>';
		}
		echo '</p><p><small class="utd-muted">নবায়ন / অ্যাক্টিভেশন: সুপার অ্যাডমিন করবেন।</small></p></div>';
	}
	echo '</div></div>';
}

/* ================= profile ================= */
function uturn_dash_profile() {
	$u = wp_get_current_user();
	echo '<div class="utd-cols"><div><div class="utd-card"><h2>👤 অ্যাকাউন্ট তথ্য</h2><div class="utd-kv">';
	echo '<span>নাম</span><b>' . esc_html( $u->display_name ) . '</b>';
	echo '<span>লগইন আইডি</span><b>' . esc_html( $u->user_login ) . '</b>';
	echo '<span>ইমেইল</span><b>' . esc_html( $u->user_email ? $u->user_email : '—' ) . '</b>';
	echo '<span>মোবাইল</span><b>' . esc_html( get_user_meta( $u->ID, '_ut_phone', true ) ? get_user_meta( $u->ID, '_ut_phone', true ) : '—' ) . '</b>';
	echo '<span>হোয়াটসঅ্যাপ</span><b>' . esc_html( get_user_meta( $u->ID, '_ut_wa', true ) ? get_user_meta( $u->ID, '_ut_wa', true ) : '—' ) . '</b>';
	echo '</div></div>';
	echo '<div class="utd-card"><h2>✏️ তথ্য হালনাগাদ</h2>';
	if ( isset( $_GET['prof'] ) && $_GET['prof'] === 'ok' ) {
		echo '<div class="notice notice-success" role="status"><p>✅ সংরক্ষণ করা হয়েছে।</p></div>';
	} elseif ( isset( $_GET['prof'] ) ) {
		echo '<div class="notice notice-error" role="alert"><p>⚠️ ' . esc_html( wp_unslash( $_GET['profmsg'] ?? 'ত্রুটি হয়েছে।' ) ) . '</p></div>';
	}
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="uturn_dash_profile"><input type="hidden" name="ut_shell" value="1">';
	wp_nonce_field( 'uturn_dash_profile' );
	echo '<table class="form-table">';
	echo '<tr><th scope="row">পুরো নাম</th><td><input type="text" class="regular-text" name="display_name" value="' . esc_attr( $u->display_name ) . '" required></td></tr>';
	echo '<tr><th scope="row">ইমেইল</th><td><input type="email" class="regular-text" name="email" value="' . esc_attr( $u->user_email ) . '"></td></tr>';
	echo '<tr><th scope="row">মোবাইল</th><td><input type="text" class="regular-text" name="phone" value="' . esc_attr( get_user_meta( $u->ID, '_ut_phone', true ) ) . '"><p class="description">এই নম্বর দিয়েও লগইন করা যাবে (অনন্য হতে হবে)।</p></td></tr>';
	echo '<tr><th scope="row">হোয়াটসঅ্যাপ</th><td><input type="text" class="regular-text" name="wa" value="' . esc_attr( get_user_meta( $u->ID, '_ut_wa', true ) ) . '"></td></tr>';
	echo '</table>';
	submit_button( 'সংরক্ষণ করুন' );
	echo '</form></div></div><div><div class="utd-card"><h2>🔑 পাসওয়ার্ড পরিবর্তন</h2>';
	if ( isset( $_GET['pw'] ) && $_GET['pw'] === 'ok' ) {
		echo '<div class="notice notice-success" role="status"><p>✅ পাসওয়ার্ড সফলভাবে বদলে গেছে।</p></div>';
	} elseif ( isset( $_GET['pw'] ) && $_GET['pw'] === 'err' ) {
		echo '<div class="notice notice-error" role="alert"><p>⚠️ ' . esc_html( wp_unslash( $_GET['pwmsg'] ?? 'ত্রুটি হয়েছে।' ) ) . '</p></div>';
	}
	echo '<form method="post" action="">' . wp_nonce_field( 'uturn_pwchange', '_wpnonce', true, false );
	echo '<table class="form-table">';
	echo '<tr><th scope="row">বর্তমান পাসওয়ার্ড</th><td><input type="password" class="regular-text" name="pw_cur" required autocomplete="current-password"></td></tr>';
	echo '<tr><th scope="row">নতুন পাসওয়ার্ড</th><td><input type="password" class="regular-text" name="pw_new" required autocomplete="new-password"><p class="description">কমপক্ষে ৬ অক্ষর।</p></td></tr>';
	echo '<tr><th scope="row">নতুন পাসওয়ার্ড (আবার)</th><td><input type="password" class="regular-text" name="pw_new2" required autocomplete="new-password"></td></tr>';
	echo '</table><p><button class="button button-primary" type="submit" name="uturn_pwchange" value="1">পাসওয়ার্ড বদলান</button></p></form>';
	echo '</div></div></div>';
}

add_action( 'admin_post_uturn_dash_profile', 'uturn_dash_profile_save' );
function uturn_dash_profile_save() {
	$back = uturn_dash_view_url( 'profile' );
	if ( ! is_user_logged_in() || ! check_admin_referer( 'uturn_dash_profile' ) ) {
		wp_die( 'Unauthorized.' );
	}
	$uid  = get_current_user_id();
	$name = sanitize_text_field( wp_unslash( $_POST['display_name'] ?? '' ) );
	$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$phone = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
	$wa    = sanitize_text_field( wp_unslash( $_POST['wa'] ?? '' ) );
	if ( $name === '' ) {
		wp_safe_redirect( add_query_arg( array( 'prof' => 'err', 'profmsg' => rawurlencode( 'নাম খালি রাখা যাবে না।' ) ), $back ) );
		exit;
	}
	if ( $email !== '' && ( ! is_email( $email ) || ( email_exists( $email ) && email_exists( $email ) !== $uid ) ) ) {
		wp_safe_redirect( add_query_arg( array( 'prof' => 'err', 'profmsg' => rawurlencode( 'ইমেইলটি সঠিক নয় বা অন্যের।' ) ), $back ) );
		exit;
	}
	if ( $phone !== '' && function_exists( 'uturn_norm_phone' ) ) {
		$norm = uturn_norm_phone( $phone );
		if ( function_exists( 'uturn_phone_user_id' ) && uturn_phone_user_id( $norm, $uid ) !== 0 ) {
			wp_safe_redirect( add_query_arg( array( 'prof' => 'err', 'profmsg' => rawurlencode( 'এই মোবাইল নম্বর অন্য অ্যাকাউন্টে ব্যবহৃত।' ) ), $back ) );
			exit;
		}
		update_user_meta( $uid, '_ut_phone_norm', $norm );
	} else {
		update_user_meta( $uid, '_ut_phone_norm', '' );
	}
	wp_update_user( array( 'ID' => $uid, 'display_name' => $name, 'user_email' => $email ) );
	update_user_meta( $uid, '_ut_phone', $phone );
	update_user_meta( $uid, '_ut_wa', $wa );
	wp_safe_redirect( add_query_arg( 'prof', 'ok', $back ) );
	exit;
}

/* ================= boundaries ================= */
/* Old portal pages are login gates now: signed-in users go straight to the shell. */
add_action( 'template_redirect', 'uturn_portal_to_dash' );
function uturn_portal_to_dash() {
	if ( ! is_user_logged_in() || ! is_page_template( array( 'page-student-portal.php', 'page-teacher-portal.php' ) ) ) {
		return;
	}
	if ( uturn_is_shell_user() ) {
		wp_safe_redirect( uturn_dash_url() );
		exit;
	}
}

add_filter( 'wp_robots', 'uturn_dash_noindex' );
function uturn_dash_noindex( $robots ) {
	if ( is_page() && get_the_ID() === uturn_dash_page_id() ) {
		return array( 'noindex' => true, 'nofollow' => true );
	}
	return $robots;
}
