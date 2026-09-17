<?php
/**
 * EduTurn — Academic masters: Subjects + Groups (বিভাগ).
 * Option-backed stores (no schema change); teachers link via
 * user meta _ut_subject_id / _ut_group_id, with legacy _ut_subject
 * text kept in sync for directory/frontend/routine displays.
 */

defined( 'ABSPATH' ) || exit;

define( 'UTURN_SUBJECTS_OPT', 'uturn_subjects' );
define( 'UTURN_GROUPS_OPT', 'uturn_groups' );

/* ================= stores ================= */
function uturn_groups_all( $active_only = false ) {
	$g = get_option( UTURN_GROUPS_OPT, null );
	if ( null === $g ) {
		uturn_academic_seed();
		$g = get_option( UTURN_GROUPS_OPT, array() );
	}
	$g = is_array( $g ) ? array_values( $g ) : array();
	if ( $active_only ) {
		$g = array_values( array_filter( $g, function ( $r ) { return ! empty( $r['active'] ); } ) );
	}
	return $g;
}

function uturn_subjects_all( $active_only = false ) {
	$s = get_option( UTURN_SUBJECTS_OPT, null );
	if ( null === $s ) {
		uturn_academic_seed();
		$s = get_option( UTURN_SUBJECTS_OPT, array() );
	}
	$s = is_array( $s ) ? array_values( $s ) : array();
	if ( $active_only ) {
		$s = array_values( array_filter( $s, function ( $r ) { return ! empty( $r['active'] ); } ) );
	}
	usort(
		$s,
		function ( $a, $b ) {
			$oa = (int) ( $a['ord'] ?? 0 );
			$ob = (int) ( $b['ord'] ?? 0 );
			if ( $oa === $ob ) {
				return strcmp( (string) ( $a['name'] ?? '' ), (string) ( $b['name'] ?? '' ) );
			}
			return $oa - $ob;
		}
	);
	return $s;
}

function uturn_group_get( $id ) {
	foreach ( uturn_groups_all() as $g ) {
		if ( (int) $g['id'] === (int) $id ) {
			return $g;
		}
	}
	return null;
}

function uturn_subject_get( $id ) {
	foreach ( uturn_subjects_all() as $s ) {
		if ( (int) $s['id'] === (int) $id ) {
			return $s;
		}
	}
	return null;
}

function uturn_group_name( $id ) {
	$g = $id ? uturn_group_get( $id ) : null;
	return $g ? $g['name'] : '';
}

/** Default full/pass marks for a subject name (master data; setup rows override per exam). */
function uturn_subject_marks_by_name( $name ) {
	$full = 100;
	$pass = 33;
	foreach ( uturn_subjects_all() as $r ) {
		if ( (string) ( $r['name'] ?? '' ) === (string) $name ) {
			$full = (float) ( $r['full'] ?? 100 );
			$pass = (float) ( $r['pass'] ?? 33 );
			break;
		}
	}
	if ( $full <= 0 ) {
		$full = 100;
	}
	if ( $pass < 0 ) {
		$pass = 0;
	}
	if ( $pass > $full ) {
		$pass = $full;
	}
	return array( 'full' => $full, 'pass' => $pass );
}
function uturn_subject_name( $id ) {
	$s = $id ? uturn_subject_get( $id ) : null;
	return $s ? $s['name'] : '';
}

/* Multi-subject teachers.
 * `_ut_subject_ids` (array of subject ids) is the source of truth;
 * `_ut_subject_id` / `_ut_subject` stay as the PRIMARY mirror for back-compat. */
function uturn_teacher_subject_ids( $user_id ) {
	$ids = get_user_meta( (int) $user_id, '_ut_subject_ids', true );
	$ids = is_array( $ids ) ? array_values( array_unique( array_map( 'absint', $ids ) ) ) : array();
	$ids = array_values( array_filter( $ids ) );
	$primary = (int) get_user_meta( (int) $user_id, '_ut_subject_id', true );
	if ( $primary && ! in_array( $primary, $ids, true ) ) {
		array_unshift( $ids, $primary );
	}
	return $ids;
}
function uturn_teacher_subject_names( $user_id ) {
	$names = array();
	foreach ( uturn_teacher_subject_ids( $user_id ) as $sid ) {
		$n = uturn_subject_name( $sid );
		if ( '' !== $n ) {
			$names[] = $n;
		}
	}
	if ( ! $names ) {
		$legacy = get_user_meta( (int) $user_id, '_ut_subject', true );
		if ( '' !== $legacy ) {
			$names[] = $legacy;
		}
	}
	return array_values( array_unique( $names ) );
}
/* CPT-side reader for frontend cards (mirrors the user-meta source). */
function uturn_post_subjects_text( $post_id, $sep = ', ' ) {
	$names = array();
	$ids = get_post_meta( (int) $post_id, '_ut_subject_ids', true );
	if ( is_array( $ids ) ) {
		foreach ( array_map( 'absint', $ids ) as $sid ) {
			$n = $sid ? uturn_subject_name( $sid ) : '';
			if ( '' !== $n ) {
				$names[] = $n;
			}
		}
	}
	if ( ! $names ) {
		$legacy = get_post_meta( (int) $post_id, '_ut_subject', true );
		if ( '' !== $legacy ) {
			$names[] = $legacy;
		}
	}
	return implode( $sep, array_values( array_unique( $names ) ) );
}

function uturn_academic_next_id( $rows ) {
	$max = 0;
	foreach ( (array) $rows as $r ) {
		if ( isset( $r['id'] ) ) {
			$max = max( $max, (int) $r['id'] );
		}
	}
	return $max + 1;
}

/** First-run seed: Science / Humanities / Business Studies + core subjects. */
function uturn_academic_seed() {
	if ( null !== get_option( UTURN_GROUPS_OPT, null ) || null !== get_option( UTURN_SUBJECTS_OPT, null ) ) {
		return;
	}
	$groups = array(
		array( 'id' => 1, 'name' => 'বিজ্ঞান (Science)', 'desc' => 'বিজ্ঞান বিভাগ — ৯ম-১০ম শ্রেণি', 'full' => 100, 'pass' => 33, 'ord' => 0, 'active' => 1 ),
		array( 'id' => 2, 'name' => 'মানবিক (Humanities)', 'desc' => 'মানবিক বিভাগ — ৯ম-১০ম শ্রেণি', 'full' => 100, 'pass' => 33, 'ord' => 0, 'active' => 1 ),
		array( 'id' => 3, 'name' => 'ব্যবসায় শিক্ষা (Business Studies)', 'desc' => 'ব্যবসায় শিক্ষা বিভাগ — ৯ম-১০ম শ্রেণি', 'full' => 100, 'pass' => 33, 'ord' => 0, 'active' => 1 ),
	);
	$subjects = array(
		array( 'id' => 1, 'name' => 'পদার্থবিজ্ঞান', 'code' => 'PHY', 'group_ids' => array( 1 ), 'full' => 100, 'pass' => 33, 'ord' => 0, 'active' => 1 ),
		array( 'id' => 2, 'name' => 'রসায়ন', 'code' => 'CHE', 'group_ids' => array( 1 ), 'full' => 100, 'pass' => 33, 'ord' => 0, 'active' => 1 ),
		array( 'id' => 3, 'name' => 'জীববিজ্ঞান', 'code' => 'BIO', 'group_ids' => array( 1 ), 'full' => 100, 'pass' => 33, 'ord' => 0, 'active' => 1 ),
		array( 'id' => 4, 'name' => 'উচ্চতর গণিত', 'code' => 'HMATH', 'group_ids' => array( 1 ), 'full' => 100, 'pass' => 33, 'ord' => 0, 'active' => 1 ),
		array( 'id' => 5, 'name' => 'গণিত', 'code' => 'MATH', 'group_ids' => array( 1, 2, 3 ), 'full' => 100, 'pass' => 33, 'ord' => 0, 'active' => 1 ),
		array( 'id' => 6, 'name' => 'ইংরেজি', 'code' => 'ENG', 'group_ids' => array( 1, 2, 3 ), 'full' => 100, 'pass' => 33, 'ord' => 0, 'active' => 1 ),
		array( 'id' => 7, 'name' => 'বাংলা', 'code' => 'BAN', 'group_ids' => array( 1, 2, 3 ), 'full' => 100, 'pass' => 33, 'ord' => 0, 'active' => 1 ),
		array( 'id' => 8, 'name' => 'ইতিহাস', 'code' => 'HIS', 'group_ids' => array( 2 ), 'full' => 100, 'pass' => 33, 'ord' => 0, 'active' => 1 ),
		array( 'id' => 9, 'name' => 'পৌরনীতি', 'code' => 'CIV', 'group_ids' => array( 2 ), 'full' => 100, 'pass' => 33, 'ord' => 0, 'active' => 1 ),
		array( 'id' => 10, 'name' => 'হিসাববিজ্ঞান', 'code' => 'ACC', 'group_ids' => array( 3 ), 'full' => 100, 'pass' => 33, 'ord' => 0, 'active' => 1 ),
		array( 'id' => 11, 'name' => 'ফিন্যান্স', 'code' => 'FIN', 'group_ids' => array( 3 ), 'full' => 100, 'pass' => 33, 'ord' => 0, 'active' => 1 ),
		array( 'id' => 12, 'name' => 'ব্যবসায় উদ্যোগ', 'code' => 'BUS', 'group_ids' => array( 3 ), 'full' => 100, 'pass' => 33, 'ord' => 0, 'active' => 1 ),
	);
	update_option( UTURN_GROUPS_OPT, $groups, false );
	update_option( UTURN_SUBJECTS_OPT, $subjects, false );
}

/* ================= usage guards ================= */
function uturn_teachers_by_subject( $sid, $sname = '' ) {
	$mq = array( 'relation' => 'OR' );
	if ( $sid ) {
		$mq[] = array( 'key' => '_ut_subject_id', 'value' => (string) (int) $sid, 'compare' => '=' );
		$mq[] = array( 'key' => '_ut_subject_ids', 'value' => 'i:' . (int) $sid . ';', 'compare' => 'LIKE' );
	}
	if ( $sname !== '' ) {
		$mq[] = array( 'key' => '_ut_subject', 'value' => $sname, 'compare' => '=' );
	}
	if ( count( $mq ) === 1 ) {
		return 0;
	}
	$q = new WP_User_Query( array( 'role' => 'uturn_teacher', 'fields' => 'ID', 'number' => -1, 'meta_query' => $mq ) );
	return count( (array) $q->get_results() );
}

function uturn_teachers_by_group( $gid ) {
	if ( ! $gid ) {
		return 0;
	}
	$q = new WP_User_Query( array( 'role' => 'uturn_teacher', 'fields' => 'ID', 'number' => -1, 'meta_key' => '_ut_group_id', 'meta_value' => (string) (int) $gid ) );
	return count( (array) $q->get_results() );
}

function uturn_subject_usage( $s ) {
	$sid = (int) $s['id'];
	$teachers = uturn_teachers_by_subject( $sid, $s['name'] );
	$groups = 0;
	foreach ( (array) ( $s['group_ids'] ?? array() ) as $gid ) {
		if ( uturn_group_get( $gid ) ) {
			$groups++;
		}
	}
	$routines = substr_count( strtolower( wp_json_encode( get_option( 'uturn_routines', array() ) ) ), strtolower( $s['name'] ) );
	$results = 0;
	$rq = new WP_Query( array( 'post_type' => 'ut_result', 'posts_per_page' => -1, 'post_status' => 'any', 'fields' => 'ids', 'no_found_rows' => true ) );
	foreach ( (array) $rq->posts as $rid ) {
		if ( false !== strpos( (string) get_post_meta( $rid, '_ut_subjects', true ), $s['name'] ) ) {
			$results++;
		}
	}
	return array( 'teachers' => $teachers, 'groups' => $groups, 'routines' => $routines, 'results' => $results );
}

function uturn_group_usage( $g ) {
	$gid = (int) $g['id'];
	$subjects = 0;
	foreach ( uturn_subjects_all() as $s ) {
		if ( in_array( $gid, array_map( 'intval', (array) ( $s['group_ids'] ?? array() ) ), true ) ) {
			$subjects++;
		}
	}
	return array( 'teachers' => uturn_teachers_by_group( $gid ), 'subjects' => $subjects );
}

function uturn_subject_can_delete( $s ) {
	$u = uturn_subject_usage( $s );
	return ( $u['teachers'] + $u['groups'] + $u['routines'] + $u['results'] ) === 0 ? true : $u;
}

function uturn_group_can_delete( $g ) {
	$u = uturn_group_usage( $g );
	return ( $u['teachers'] + $u['subjects'] ) === 0 ? true : $u;
}

/* ================= mutations ================= */
function uturn_subject_save_row( $data ) {
	$rows = uturn_subjects_all();
	$id = isset( $data['id'] ) ? (int) $data['id'] : 0;
	$row = array(
		'id' => $id ? $id : uturn_academic_next_id( $rows ),
		'name' => str_replace( '|', '', sanitize_text_field( $data['name'] ?? '' ) ),
		'code' => sanitize_text_field( $data['code'] ?? '' ),
		'group_ids' => array_values( array_unique( array_map( 'absint', (array) ( $data['group_ids'] ?? array() ) ) ) ),
		'full' => max( 1, (float) ( $data['full'] ?? 100 ) ),
		'pass' => max( 0, (float) ( $data['pass'] ?? 33 ) ),
		'ord' => absint( $data['ord'] ?? 0 ),
		'active' => ! empty( $data['active'] ) ? 1 : 0,
	);
	if ( $row['pass'] > $row['full'] ) {
		$row['pass'] = $row['full'];
	}
	if ( $row['name'] === '' ) {
		return new WP_Error( 'name', 'বিষয়ের নাম দিন।' );
	}
	$found = false;
	foreach ( $rows as &$r ) {
		if ( (int) $r['id'] === $row['id'] ) {
			$r = $row;
			$found = true;
		}
	}
	unset( $r );
	if ( ! $found ) {
		$rows[] = $row;
	}
	update_option( UTURN_SUBJECTS_OPT, array_values( $rows ), false );
	return $row['id'];
}

function uturn_group_save_row( $data ) {
	$rows = uturn_groups_all();
	$id = isset( $data['id'] ) ? (int) $data['id'] : 0;
	$row = array(
		'id' => $id ? $id : uturn_academic_next_id( $rows ),
		'name' => str_replace( '|', '', sanitize_text_field( $data['name'] ?? '' ) ),
		'desc' => sanitize_text_field( $data['desc'] ?? '' ),
		'active' => ! empty( $data['active'] ) ? 1 : 0,
	);
	if ( $row['name'] === '' ) {
		return new WP_Error( 'name', 'বিভাগের নাম দিন।' );
	}
	$found = false;
	foreach ( $rows as &$r ) {
		if ( (int) $r['id'] === $row['id'] ) {
			$r = $row;
			$found = true;
		}
	}
	unset( $r );
	if ( ! $found ) {
		$rows[] = $row;
	}
	update_option( UTURN_GROUPS_OPT, array_values( $rows ), false );
	/* group form also carries subject links (missing = uncheck-all = unlink) */
	if ( ( $id || $found ) && ! empty( $data['sync_subjects'] ) ) {
		$sids = array_map( 'absint', (array) ( $data['subject_ids'] ?? array() ) );
		{
			$subs = uturn_subjects_all();
			foreach ( $subs as &$s ) {
				$gids = array_map( 'intval', (array) ( $s['group_ids'] ?? array() ) );
				$has = in_array( $row['id'], $gids, true );
				$want = in_array( (int) $s['id'], $sids, true );
				if ( $want && ! $has ) {
					$gids[] = $row['id'];
				} elseif ( ! $want && $has ) {
					$gids = array_values( array_diff( $gids, array( $row['id'] ) ) );
				}
				$s['group_ids'] = array_values( $gids );
			}
			unset( $s );
			update_option( UTURN_SUBJECTS_OPT, $subs, false );
		}
	}
	return $row['id'];
}

/* ================= handlers ================= */
add_action( 'admin_post_uturn_subject_save', 'uturn_subject_save' );
function uturn_subject_save() {
	if ( ! current_user_can( 'uturn_manage_academic' ) || ! check_admin_referer( 'uturn_academic' ) ) {
		wp_die( 'Unauthorized.' );
	}
	$r = uturn_subject_save_row( array( 'id' => absint( $_POST['sid'] ?? 0 ), 'name' => wp_unslash( $_POST['name'] ?? '' ), 'code' => wp_unslash( $_POST['code'] ?? '' ), 'group_ids' => wp_unslash( $_POST['group_ids'] ?? array() ), 'full' => wp_unslash( $_POST['full'] ?? 100 ), 'pass' => wp_unslash( $_POST['pass'] ?? 33 ), 'ord' => absint( $_POST['ord'] ?? 0 ), 'active' => ! empty( $_POST['active'] ) ) );
	if ( is_wp_error( $r ) ) {
		wp_safe_redirect( uturn_dash_view_url( 'subjects', array( 'uturn_msg' => 'error' ) ) );
		exit;
	}
	wp_safe_redirect( uturn_dash_view_url( 'subjects', array( 'uturn_msg' => 'saved', 'uturn_toast' => rawurlencode( '✅ বিষয় সংরক্ষণ করা হয়েছে।' ) ) ) );
	exit;
}

add_action( 'admin_post_uturn_subject_toggle', 'uturn_subject_toggle' );
function uturn_subject_toggle() {
	$id = absint( $_GET['id'] ?? 0 );
	$s = $id ? uturn_subject_get( $id ) : null;
	if ( ! $s || ! current_user_can( 'uturn_manage_academic' ) || ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'uturn_academic_' . $id ) ) {
		wp_die( 'Unauthorized.' );
	}
	$s['active'] = empty( $s['active'] ) ? 1 : 0;
	uturn_subject_save_row( $s );
	wp_safe_redirect( uturn_dash_view_url( 'subjects', array( 'uturn_toast' => rawurlencode( $s['active'] ? '✅ বিষয় সক্রিয় করা হয়েছে।' : '⏸️ বিষয় নিষ্ক্রিয় করা হয়েছে।' ) ) ) );
	exit;
}

add_action( 'admin_post_uturn_subject_delete', 'uturn_subject_delete' );
function uturn_subject_delete() {
	$id = absint( $_GET['id'] ?? 0 );
	$s = $id ? uturn_subject_get( $id ) : null;
	if ( ! $s || ! current_user_can( 'uturn_manage_academic' ) || ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'uturn_academic_' . $id ) ) {
		wp_die( 'Unauthorized.' );
	}
	$can = uturn_subject_can_delete( $s );
	if ( true !== $can ) {
		wp_safe_redirect( uturn_dash_view_url( 'subjects', array( 'uturn_msg' => 'blocked', 'why' => rawurlencode( "শিক্ষক: {$can['teachers']} · বিভাগ: {$can['groups']} · রুটিন: {$can['routines']} · ফলাফল: {$can['results']}" ) ) ) );
		exit;
	}
	update_option( UTURN_SUBJECTS_OPT, array_values( array_filter( uturn_subjects_all(), function ( $r ) use ( $id ) { return (int) $r['id'] !== $id; } ) ), false );
	wp_safe_redirect( uturn_dash_view_url( 'subjects', array( 'uturn_toast' => rawurlencode( '🗑️ বিষয় মুছে ফেলা হয়েছে।' ) ) ) );
	exit;
}

add_action( 'admin_post_uturn_group_save', 'uturn_group_save' );
function uturn_group_save() {
	if ( ! current_user_can( 'uturn_manage_academic' ) || ! check_admin_referer( 'uturn_academic' ) ) {
		wp_die( 'Unauthorized.' );
	}
	$r = uturn_group_save_row( array( 'id' => absint( $_POST['gid'] ?? 0 ), 'name' => wp_unslash( $_POST['name'] ?? '' ), 'desc' => wp_unslash( $_POST['desc'] ?? '' ), 'active' => ! empty( $_POST['active'] ), 'subject_ids' => wp_unslash( $_POST['subject_ids'] ?? array() ), 'sync_subjects' => true ) );
	if ( is_wp_error( $r ) ) {
		wp_safe_redirect( uturn_dash_view_url( 'groups', array( 'uturn_msg' => 'error' ) ) );
		exit;
	}
	wp_safe_redirect( uturn_dash_view_url( 'groups', array( 'uturn_msg' => 'saved', 'uturn_toast' => rawurlencode( '✅ বিভাগ সংরক্ষণ করা হয়েছে।' ) ) ) );
	exit;
}

add_action( 'admin_post_uturn_group_toggle', 'uturn_group_toggle' );
function uturn_group_toggle() {
	$id = absint( $_GET['id'] ?? 0 );
	$g = $id ? uturn_group_get( $id ) : null;
	if ( ! $g || ! current_user_can( 'uturn_manage_academic' ) || ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'uturn_academic_' . $id ) ) {
		wp_die( 'Unauthorized.' );
	}
	$g['active'] = empty( $g['active'] ) ? 1 : 0;
	uturn_group_save_row( $g );
	wp_safe_redirect( uturn_dash_view_url( 'groups', array( 'uturn_toast' => rawurlencode( $g['active'] ? '✅ বিভাগ সক্রিয় করা হয়েছে।' : '⏸️ বিভাগ নিষ্ক্রিয় করা হয়েছে।' ) ) ) );
	exit;
}

add_action( 'admin_post_uturn_group_delete', 'uturn_group_delete' );
function uturn_group_delete() {
	$id = absint( $_GET['id'] ?? 0 );
	$g = $id ? uturn_group_get( $id ) : null;
	if ( ! $g || ! current_user_can( 'uturn_manage_academic' ) || ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'uturn_academic_' . $id ) ) {
		wp_die( 'Unauthorized.' );
	}
	$can = uturn_group_can_delete( $g );
	if ( true !== $can ) {
		wp_safe_redirect( uturn_dash_view_url( 'groups', array( 'uturn_msg' => 'blocked', 'why' => rawurlencode( "শিক্ষক: {$can['teachers']} · বিষয়: {$can['subjects']}" ) ) ) );
		exit;
	}
	update_option( UTURN_GROUPS_OPT, array_values( array_filter( uturn_groups_all(), function ( $r ) use ( $id ) { return (int) $r['id'] !== $id; } ) ), false );
	wp_safe_redirect( uturn_dash_view_url( 'groups', array( 'uturn_toast' => rawurlencode( '🗑️ বিভাগ মুছে ফেলা হয়েছে।' ) ) ) );
	exit;
}

/* ================= shell views ================= */
function uturn_academic_notice() {
	if ( isset( $_GET['uturn_msg'] ) && $_GET['uturn_msg'] === 'saved' ) {
		echo '<div class="notice notice-success" role="status"><p>✅ সংরক্ষণ করা হয়েছে।</p></div>';
	} elseif ( isset( $_GET['uturn_msg'] ) && $_GET['uturn_msg'] === 'error' ) {
		echo '<div class="notice notice-error" role="alert"><p>⚠️ নাম খালি রাখা যাবে না।</p></div>';
	} elseif ( isset( $_GET['uturn_msg'] ) && $_GET['uturn_msg'] === 'blocked' ) {
		echo '<div class="notice notice-error" role="alert"><p>⛔ মুছা যাবে না — এই রেকর্ডটি ব্যবহৃত হচ্ছে (' . esc_html( wp_unslash( $_GET['why'] ?? '' ) ) . ')। আগে সংযোগ সরান।</p></div>';
	}
}

function uturn_dash_subjects() {
	$s = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
	$gf = isset( $_GET['group'] ) ? absint( $_GET['group'] ) : 0;
	$rows = uturn_subjects_all();
	if ( $s !== '' ) {
		$rows = array_values( array_filter( $rows, function ( $r ) use ( $s ) { return false !== mb_stripos( $r['name'] . ' ' . ( $r['code'] ?? '' ), $s ); } ) );
	}
	if ( $gf ) {
		$rows = array_values( array_filter( $rows, function ( $r ) use ( $gf ) { return in_array( $gf, array_map( 'intval', (array) ( $r['group_ids'] ?? array() ) ), true ); } ) );
	}
	$groups = uturn_groups_all();
	uturn_academic_notice();
	echo '<div class="utx-toolbar"><form method="get" action="' . esc_url( uturn_dash_url() ) . '" class="utx-filters"><input type="hidden" name="view" value="subjects">';
	echo '<input type="search" name="s" value="' . esc_attr( $s ) . '" placeholder="🔍 বিষয় / কোড খুঁজুন…">';
	echo '<select name="group"><option value="0">সব বিভাগ</option>';
	foreach ( $groups as $g ) {
		echo '<option value="' . (int) $g['id'] . '"' . selected( $gf, (int) $g['id'], false ) . '>' . esc_html( $g['name'] ) . '</option>';
	}
	echo '</select><button class="button button-secondary" type="submit">ফিল্টার</button></form>';
	echo '<button class="button button-primary" data-utx-modal="utx-subject-modal" data-utx-edit="{}">＋ নতুন বিষয়</button></div>';
	if ( ! $rows ) {
		echo '<div class="utx-empty"><div class="utx-empty-ic">📚</div><h3>কোনো বিষয় পাওয়া যায়নি</h3><p>নতুন বিষয় যোগ করুন — শিক্ষক ফরমের ড্রপডাউনে সাথে সাথে দেখাবে।</p></div>';
	} else {
		echo '<div class="utd-card utx-table-card"><table class="widefat striped utx-table"><thead><tr><th scope="col">বিষয়</th><th scope="col">কোড</th><th scope="col">পূর্ণমান</th><th scope="col">পাস</th><th scope="col">বিভাগ</th><th scope="col">শিক্ষক</th><th scope="col">অবস্থা</th><th scope="col">অ্যাকশন</th></tr></thead><tbody>';
		foreach ( $rows as $r ) {
			$u = uturn_subject_usage( $r );
			$gb = array();
			foreach ( (array) ( $r['group_ids'] ?? array() ) as $gid ) {
				$gn = uturn_group_name( $gid );
				if ( $gn ) {
					$gb[] = '<span class="utd-pill blue">' . esc_html( $gn ) . '</span>';
				}
			}
			$edit = array( 'id' => (int) $r['id'], 'name' => $r['name'], 'code' => $r['code'] ?? '', 'full' => $r['full'] ?? 100, 'pass' => $r['pass'] ?? 33, 'ord' => (int) ( $r['ord'] ?? 0 ), 'group_ids' => array_map( 'intval', (array) ( $r['group_ids'] ?? array() ) ), 'active' => ! empty( $r['active'] ) );
			echo '<tr><td><strong>' . esc_html( $r['name'] ) . '</strong></td><td><code>' . esc_html( $r['code'] ?? '—' ) . '</code></td>';
			echo '<td>' . esc_html( (string) ( $r['full'] ?? 100 ) ) . '</td><td>' . esc_html( (string) ( $r['pass'] ?? 33 ) ) . '</td>';
			echo '<td>' . ( $gb ? implode( ' ', $gb ) : '<span class="utd-pill">সাধারণ (বিভাগ নেই)</span>' ) . '</td>';
			echo '<td><span class="utd-pill">' . esc_html( (string) $u['teachers'] ) . ' জন</span></td>';
			echo '<td>' . ( ! empty( $r['active'] ) ? '<span class="utd-pill green">সক্রিয়</span>' : '<span class="utd-pill amber">নিষ্ক্রিয়</span>' ) . '</td><td class="utx-actions">';
			echo '<button class="button button-small" data-utx-modal="utx-subject-modal" data-utx-edit="' . esc_attr( wp_json_encode( $edit ) ) . '">সম্পাদনা</button> ';
			echo '<a class="button button-small" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?ut_shell=1&action=uturn_subject_toggle&id=' . (int) $r['id'] ), 'uturn_academic_' . (int) $r['id'] ) ) . '">' . ( ! empty( $r['active'] ) ? 'নিষ্ক্রিয়' : 'সক্রিয়' ) . '</a> ';
			$can = uturn_subject_can_delete( $r );
			if ( true === $can ) {
				echo '<a class="button button-small utx-danger" data-confirm="“' . esc_attr( $r['name'] ) . '” মুছে ফেলবেন?" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?ut_shell=1&action=uturn_subject_delete&id=' . (int) $r['id'] ), 'uturn_academic_' . (int) $r['id'] ) ) . '">মুছুন</a>';
			} else {
				echo '<span class="utd-muted" title="শিক্ষক: ' . (int) $can['teachers'] . ' · বিভাগ: ' . (int) $can['groups'] . ' · রুটিন: ' . (int) $can['routines'] . ' · ফলাফল: ' . (int) $can['results'] . '">🔒 ব্যবহৃত</span>';
			}
			echo '</td></tr>';
		}
		echo '</tbody></table></div>';
	}
	/* modal form */
	echo '<div class="utx-modal" id="utx-subject-modal" hidden><div class="utx-modal-box"><div class="utx-modal-head"><h3>📚 বিষয়</h3><button type="button" class="utx-modal-x" data-utx-close>✕</button></div>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="uturn_subject_save"><input type="hidden" name="ut_shell" value="1"><input type="hidden" name="sid" value="0">';
	wp_nonce_field( 'uturn_academic' );
	echo '<p><label>বিষয়ের নাম *<input type="text" name="name" required class="large-text" placeholder="যেমন: পদার্থবিজ্ঞান"></label></p>';
	echo '<p><label>কোড<input type="text" name="code" class="regular-text" placeholder="যেমন: PHY"></label></p>';
	echo '<p style="display:flex;gap:12px;flex-wrap:wrap"><label>পূর্ণমান<br><input type="number" name="full" value="100" min="1" step="any" style="width:8em"></label><label>পাস নম্বর<br><input type="number" name="pass" value="33" min="0" step="any" style="width:8em"></label><label>ক্রম<br><input type="number" name="ord" value="0" min="0" style="width:6em"></label></p>';
	echo '<div class="utx-checks"><b>বিভাগ (একাধিক হতে পারে)</b>';
	foreach ( $groups as $g ) {
		echo '<label><input type="checkbox" name="group_ids[]" value="' . (int) $g['id'] . '"> ' . esc_html( $g['name'] ) . '</label>';
	}
	echo '<p class="utd-muted">কোনো বিভাগে টিক না দিলে বিষয়টি <b>সাধারণ</b> (সব বিভাগের বাইরে) হিসেবে থাকবে।</p>';
	echo '</div><p><label><input type="checkbox" name="active" value="1" checked> সক্রিয়</label></p>';
	echo '<div class="utx-modal-foot"><button type="button" class="button" data-utx-close>বাতিল</button> <button class="button button-primary" type="submit">সংরক্ষণ করুন</button></div></form></div></div>';
}

function uturn_dash_acad_groups() {
	$s = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
	$rows = uturn_groups_all();
	if ( $s !== '' ) {
		$rows = array_values( array_filter( $rows, function ( $r ) use ( $s ) { return false !== mb_stripos( $r['name'] . ' ' . ( $r['desc'] ?? '' ), $s ); } ) );
	}
	$subjects = uturn_subjects_all();
	uturn_academic_notice();
	echo '<div class="utx-toolbar"><form method="get" action="' . esc_url( uturn_dash_url() ) . '" class="utx-filters"><input type="hidden" name="view" value="groups">';
	echo '<input type="search" name="s" value="' . esc_attr( $s ) . '" placeholder="🔍 বিভাগ খুঁজুন…"><button class="button button-secondary" type="submit">খুঁজুন</button></form>';
	echo '<button class="button button-primary" data-utx-modal="utx-group-modal" data-utx-edit="{}">＋ নতুন বিভাগ</button></div>';
	if ( ! $rows ) {
		echo '<div class="utx-empty"><div class="utx-empty-ic">🏛️</div><h3>কোনো বিভাগ নেই</h3><p>বিজ্ঞান, মানবিক, ব্যবসায় শিক্ষা — আপনার প্রতিষ্ঠানের বিভাগ যোগ করুন।</p></div>';
	} else {
		echo '<div class="utx-grid">';
		foreach ( $rows as $r ) {
			$u = uturn_group_usage( $r );
			$my_sids = array();
			foreach ( $subjects as $sb ) {
				if ( in_array( (int) $r['id'], array_map( 'intval', (array) ( $sb['group_ids'] ?? array() ) ), true ) ) {
					$my_sids[] = (int) $sb['id'];
				}
			}
			$edit = array( 'id' => (int) $r['id'], 'name' => $r['name'], 'desc' => $r['desc'] ?? '', 'active' => ! empty( $r['active'] ), 'subject_ids' => $my_sids );
			echo '<div class="utd-card utx-gcard"><div class="utx-gcard-top"><h3>' . esc_html( $r['name'] ) . '</h3>' . ( ! empty( $r['active'] ) ? '<span class="utd-pill green">সক্রিয়</span>' : '<span class="utd-pill amber">নিষ্ক্রিয়</span>' ) . '</div>';
			if ( ! empty( $r['desc'] ) ) {
				echo '<p class="utd-muted">' . esc_html( $r['desc'] ) . '</p>';
			}
			echo '<div class="utx-counts"><span>👩‍🏫 ' . esc_html( (string) $u['teachers'] ) . ' শিক্ষক</span><span>📚 ' . esc_html( (string) $u['subjects'] ) . ' বিষয়</span></div>';
			echo '<div class="utx-actions"><button class="button button-small" data-utx-modal="utx-group-modal" data-utx-edit="' . esc_attr( wp_json_encode( $edit ) ) . '">সম্পাদনা</button> ';
			echo '<a class="button button-small" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?ut_shell=1&action=uturn_group_toggle&id=' . (int) $r['id'] ), 'uturn_academic_' . (int) $r['id'] ) ) . '">' . ( ! empty( $r['active'] ) ? 'নিষ্ক্রিয়' : 'সক্রিয়' ) . '</a> ';
			if ( true === uturn_group_can_delete( $r ) ) {
				echo '<a class="button button-small utx-danger" data-confirm="“' . esc_attr( $r['name'] ) . '” মুছে ফেলবেন?" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?ut_shell=1&action=uturn_group_delete&id=' . (int) $r['id'] ), 'uturn_academic_' . (int) $r['id'] ) ) . '">মুছুন</a>';
			} else {
				echo '<span class="utd-muted">🔒 ব্যবহৃত</span>';
			}
			echo '</div></div>';
		}
		echo '</div>';
	}
	echo '<div class="utx-modal" id="utx-group-modal" hidden><div class="utx-modal-box"><div class="utx-modal-head"><h3>🏛️ বিভাগ</h3><button type="button" class="utx-modal-x" data-utx-close>✕</button></div>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="uturn_group_save"><input type="hidden" name="ut_shell" value="1"><input type="hidden" name="gid" value="0">';
	wp_nonce_field( 'uturn_academic' );
	echo '<p><label>বিভাগের নাম *<input type="text" name="name" required class="large-text" placeholder="যেমন: বিজ্ঞান (Science)"></label></p>';
	echo '<p><label>বিবরণ<input type="text" name="desc" class="large-text" placeholder="যেমন: বিজ্ঞান বিভাগ — ৯ম-১০ম শ্রেণি"></label></p>';
	echo '<div class="utx-checks"><b>এই বিভাগের বিষয়সমূহ</b>';
	foreach ( $subjects as $sb ) {
		echo '<label><input type="checkbox" name="subject_ids[]" value="' . (int) $sb['id'] . '"> ' . esc_html( $sb['name'] ) . '</label>';
	}
	echo '</div><p><label><input type="checkbox" name="active" value="1" checked> সক্রিয়</label></p>';
	echo '<div class="utx-modal-foot"><button type="button" class="button" data-utx-close>বাতিল</button> <button class="button button-primary" type="submit">সংরক্ষণ করুন</button></div></form></div></div>';
}
