<?php
/**
 * EduTurn — Results 2.0: graphical marks entry + review/publish workflow
 * + promotion engine. Teachers enter marks (auto-calculated); School Admin /
 * Headmaster reviews and publishes; only published results go public.
 *
 * Workflow (ut_result post_status): draft → pending → publish.
 * Student identity: ONE ut_student post + ONE user forever; class is meta.
 */

defined( 'ABSPATH' ) || exit;

/* ================= grading engine (configurable scale, 100 marks) ================= */
function uturn_grade_scale_default() {
	return array(
		array( 'min' => 80, 'grade' => 'A+', 'point' => 5.00 ),
		array( 'min' => 70, 'grade' => 'A', 'point' => 4.00 ),
		array( 'min' => 60, 'grade' => 'A-', 'point' => 3.50 ),
		array( 'min' => 50, 'grade' => 'B', 'point' => 3.00 ),
		array( 'min' => 40, 'grade' => 'C', 'point' => 2.00 ),
		array( 'min' => 33, 'grade' => 'D', 'point' => 1.00 ),
		array( 'min' => 0, 'grade' => 'F', 'point' => 0.00 ),
	);
}

/** Active grading scale (school-configurable via the Grading view). */
function uturn_grade_scale() {
	$s = get_option( 'uturn_grade_scale', null );
	if ( ! is_array( $s ) || ! $s ) {
		return uturn_grade_scale_default();
	}
	$out = array();
	foreach ( $s as $r ) {
		if ( ! isset( $r['min'], $r['grade'], $r['point'] ) ) {
			continue;
		}
		$out[] = array( 'min' => (float) $r['min'], 'grade' => (string) $r['grade'], 'point' => (float) $r['point'] );
	}
	if ( ! $out ) {
		return uturn_grade_scale_default();
	}
	usort( $out, function ( $a, $b ) { return $b['min'] <=> $a['min']; } );
	return $out;
}

function uturn_grade_for_marks( $m ) {
	$m = (float) $m;
	foreach ( uturn_grade_scale() as $r ) {
		if ( $m >= $r['min'] ) {
			return array( $r['grade'], $r['point'] );
		}
	}
	return array( 'F', 0.00 );
}

/**
 * Auto-calculate one student's result.
 * $marks: array( subject => mark|'' ). Empty = not entered (skipped).
 * $fulls: array( subject => full marks ). Missing = 100. Grades are computed
 * on PERCENTAGE so 50-mark and 100-mark subjects share one grading scale.
 * Returns subjects detail + total + gpa (2dp string) + pass/fail + entered count.
 */
function uturn_calc_result( $marks, $fulls = array() ) {
	$subs = array();
	$total = 0;
	$pts = 0;
	$n = 0;
	$fail = false;
	foreach ( (array) $marks as $sub => $mk ) {
		$mk = trim( (string) $mk );
		$full = isset( $fulls[ $sub ] ) ? (float) $fulls[ $sub ] : 100;
		if ( $full <= 0 ) {
			$full = 100;
		}
		if ( $mk === '' ) {
			$subs[ $sub ] = array( 'mark' => '', 'grade' => '', 'point' => '', 'full' => $full );
			continue;
		}
		$mk = max( 0, min( $full, (float) $mk ) );
		$pct = ( $mk / $full ) * 100;
		list( $gr, $pt ) = uturn_grade_for_marks( $pct );
		$subs[ $sub ] = array( 'mark' => $mk, 'grade' => $gr, 'point' => $pt, 'full' => $full );
		$total += $mk;
		$pts += $pt;
		$n++;
		if ( 'F' === $gr ) {
			$fail = true;
		}
	}
	return array(
		'subjects' => $subs,
		'total'    => $total,
		'gpa'      => $n ? number_format( $pts / $n, 2, '.', '' ) : '',
		'status'   => $n ? ( $fail ? 'fail' : 'pass' ) : '',
		'entered'  => $n,
		'count'    => count( (array) $marks ),
	);
}

/* ================= class ladder ================= */
function uturn_class_ladder() {
	return array( 'প্লে', 'নার্সারি', 'কেজি', '১ম', '২য়', '৩য়', '৪র্থ', '৫ম', '৬ষ্ঠ', '৭ম', '৮ম', '৯ম', '১০ম' );
}

function uturn_next_class( $name ) {
	$lad = uturn_class_ladder();
	$i = array_search( $name, $lad, true );
	if ( false === $i || ! isset( $lad[ $i + 1 ] ) ) {
		return '';
	}
	return $lad[ $i + 1 ];
}

function uturn_student_statuses() {
	return array(
		'active'     => 'অধ্যয়নরত (Active)',
		'promoted'   => 'উন্নীত (Promoted)',
		'retained'   => 'একই শ্রেণিতে রাখা (Retained)',
		'transferred'=> 'বদলি / TC (Transferred)',
		'left'       => 'বিদ্যালয় ত্যাগ (Left)',
		'graduated'  => 'স্নাতক / উত্তীর্ণ (Graduated)',
		'passed'     => 'উত্তীর্ণ (Legacy)',
		'inactive'   => 'নিষ্ক্রিয় (Inactive)',
	);
}

/** Statuses that keep the student on active rosters (entry, attendance, admit). */
function uturn_roster_statuses() {
	return array( 'active', 'promoted', 'retained' );
}

function uturn_student_on_roster( $student_cpt_id ) {
	$st = get_post_meta( (int) $student_cpt_id, '_ut_status', true );
	if ( '' === $st ) {
		return true; // legacy rows predate statuses
	}
	return in_array( $st, uturn_roster_statuses(), true );
}

function uturn_student_status( $student_cpt_id ) {
	$st = get_post_meta( (int) $student_cpt_id, '_ut_status', true );
	return '' !== $st ? $st : 'active';
}

/** Permanent human-readable student ID, e.g. STU-00125. */
function uturn_student_code( $student_cpt_id ) {
	return (string) get_post_meta( (int) $student_cpt_id, '_ut_student_code', true );
}

/** Generate + assign a unique code if missing (idempotent). */
function uturn_ensure_student_code( $student_cpt_id ) {
	$sp = get_post( (int) $student_cpt_id );
	if ( ! $sp || 'ut_student' !== $sp->post_type ) {
		return '';
	}
	$code = uturn_student_code( $sp->ID );
	if ( '' !== $code ) {
		return $code;
	}
	$seq = (int) get_option( 'uturn_student_seq', 0 );
	if ( $seq < 1 ) {
		/* Resume after the highest existing code. */
		global $wpdb;
		$mx = $wpdb->get_var( "SELECT MAX(CAST(SUBSTRING(meta_value,5) AS UNSIGNED)) FROM {$wpdb->postmeta} WHERE meta_key='_ut_student_code'" );
		$seq = $mx ? (int) $mx : 0;
	}
	do {
		$seq++;
		$code = 'STU-' . str_pad( (string) $seq, 5, '0', STR_PAD_LEFT );
		$dup = get_posts( array( 'post_type' => 'ut_student', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_ut_student_code', 'meta_value' => $code, 'post__not_in' => array( $sp->ID ) ) );
	} while ( $dup && $seq < 99999 );
	update_option( 'uturn_student_seq', $seq, false );
	update_post_meta( $sp->ID, '_ut_student_code', $code );
	$uid = (int) get_post_meta( $sp->ID, '_ut_user_id', true );
	if ( $uid ) {
		update_user_meta( $uid, '_ut_student_code', $code );
	}
	return $code;
}

/** One-time backfill for databases created before permanent IDs. */
add_action( 'init', 'uturn_student_code_backfill', 30 );
function uturn_student_code_backfill() {
	if ( is_admin() || ! function_exists( 'get_posts' ) ) {
		return;
	}
	if ( get_option( 'uturn_code_backfill_v1', '' ) === 'done' ) {
		return;
	}
	$ids = get_posts( array( 'post_type' => 'ut_student', 'posts_per_page' => -1, 'fields' => 'ids' ) );
	foreach ( $ids as $sid ) {
		uturn_ensure_student_code( $sid );
	}
	update_option( 'uturn_code_backfill_v1', 'done', false );
}

/* ================= entry helpers ================= */
function uturn_norm_num( $v ) {
	$v = function_exists( 'uturn_bn_to_en' ) ? uturn_bn_to_en( (string) $v ) : (string) $v;
	return trim( $v );
}

/** Active students of a class, roll-sorted (numeric-aware). */
function uturn_entry_students( $class ) {
	$posts = get_posts(
		array(
			'post_type' => 'ut_student', 'posts_per_page' => -1,
			'meta_key' => '_ut_class', 'meta_value' => $class,
		)
	);
	$out = array();
	foreach ( $posts as $p ) {
		if ( ! uturn_student_on_roster( $p->ID ) ) {
			continue;
		}
		$out[] = $p;
	}
	usort(
		$out,
		function ( $a, $b ) {
			$ra = uturn_norm_num( get_post_meta( $a->ID, '_ut_roll', true ) );
			$rb = uturn_norm_num( get_post_meta( $b->ID, '_ut_roll', true ) );
			$ia = is_numeric( $ra ) ? (int) $ra : PHP_INT_MAX;
			$ib = is_numeric( $rb ) ? (int) $rb : PHP_INT_MAX;
			if ( $ia === $ib ) {
				return strcmp( $a->post_title, $b->post_title );
			}
			return $ia - $ib;
		}
	);
	return $out;
}

/* ================= exam subject setup (which subjects + full marks) ================= */
function uturn_exam_setup_key( $cls, $exam, $year ) {
	return (string) $cls . '|' . (string) $exam . '|' . uturn_norm_num( $year );
}
/**
 * Stored setup rows: array of array( 'subject' => name, 'full' => 100 ).
 * Returns null when the office has not configured this exam yet.
 */
function uturn_exam_setup_get( $cls, $exam, $year ) {
	$all = get_option( 'uturn_exam_subjects', array() );
	$k = uturn_exam_setup_key( $cls, $exam, $year );
	if ( ! is_array( $all ) || ! isset( $all[ $k ] ) || ! is_array( $all[ $k ] ) ) {
		return null;
	}
	$out = array();
	foreach ( $all[ $k ] as $r ) {
		$nm = trim( (string) ( is_array( $r ) ? ( $r['subject'] ?? '' ) : '' ) );
		if ( '' === $nm ) {
			continue;
		}
		$full = ( is_array( $r ) && isset( $r['full'] ) ) ? (float) $r['full'] : 100;
		if ( $full <= 0 ) {
			$full = 100;
		}
		$out[] = array( 'subject' => $nm, 'full' => $full );
	}
	return $out ? $out : null;
}
function uturn_exam_setup_save_rows( $cls, $exam, $year, $rows ) {
	$all = get_option( 'uturn_exam_subjects', array() );
	if ( ! is_array( $all ) ) {
		$all = array();
	}
	$all[ uturn_exam_setup_key( $cls, $exam, $year ) ] = array_values( $rows );
	update_option( 'uturn_exam_subjects', $all, false );
}
/** Full-marks map actually stored on one result (4th line part; legacy rows = 100). */
function uturn_result_fulls( $result_id ) {
	$out = array();
	$raw = (string) get_post_meta( (int) $result_id, '_ut_subjects', true );
	$lines = function_exists( 'uturn_lines' ) ? uturn_lines( $raw ) : explode( "\n", $raw );
	foreach ( $lines as $ln ) {
		$parts = array_map( 'trim', explode( '|', $ln ) );
		if ( count( $parts ) >= 2 && '' !== $parts[0] ) {
			$out[ $parts[0] ] = ( isset( $parts[3] ) && (float) $parts[3] > 0 ) ? (float) $parts[3] : 100;
		}
	}
	return $out;
}

/** Subjects for the grid: class subject-map first, else global active subjects. */
function uturn_entry_subjects( $class ) {
	$names = array();
	if ( function_exists( 'uturn_class_term_by_name' ) && function_exists( 'uturn_class_subjects' ) ) {
		$t = uturn_class_term_by_name( $class );
		if ( $t ) {
			foreach ( uturn_class_subjects( $t->term_id ) as $s ) {
				if ( ! empty( $s['subject'] ) ) {
					$names[] = $s['subject'];
				}
			}
		}
	}
	if ( ! $names && function_exists( 'uturn_subjects_all' ) ) {
		foreach ( uturn_subjects_all( true ) as $s ) {
			if ( ! empty( $s['name'] ) ) {
				$names[] = $s['name'];
			}
		}
	}
	return array_values( array_unique( $names ) );
}
/** Extra (student-specific) subjects stored on the ut_student post. */
function uturn_student_extras( $student_id ) {
	$raw = get_post_meta( (int) $student_id, '_ut_extra_subjects', true );
	if ( ! is_array( $raw ) ) {
		return array();
	}
	$out = array();
	foreach ( $raw as $nm ) {
		$nm = trim( (string) $nm );
		if ( '' !== $nm ) {
			$out[] = $nm;
		}
	}
	return array_values( array_unique( $out ) );
}
/** Roster-wide union of extra subjects (sorted) — grid columns beyond the class set. */
function uturn_entry_union_extras( $class ) {
	$all = array();
	if ( function_exists( 'uturn_entry_students' ) ) {
		foreach ( uturn_entry_students( $class ) as $sp ) {
			foreach ( uturn_student_extras( $sp->ID ) as $nm ) {
				$all[] = $nm;
			}
		}
	}
	$all = array_values( array_unique( $all ) );
	sort( $all, SORT_STRING );
	return $all;
}

/** Classes the current user may enter marks for (teachers: own only). */
function uturn_entry_classes() {
	if ( current_user_can( 'publish_ut_results' ) || current_user_can( 'manage_options' ) ) {
		$out = array();
		if ( function_exists( 'uturn_class_terms' ) ) {
			foreach ( uturn_class_terms() as $t ) {
				$out[] = $t->name;
			}
		}
		if ( ! $out && function_exists( 'uturn_result_classes' ) ) {
			$out = uturn_result_classes();
		}
		return $out;
	}
	if ( function_exists( 'uturn_teacher_classes' ) ) {
		return array_keys( uturn_teacher_classes( get_current_user_id() ) );
	}
	return array();
}

/** Find existing ut_result for student+exam+year (BN/EN year tolerant). */
function uturn_find_result( $student_cpt_id, $exam, $year ) {
	$yn = uturn_norm_num( $year );
	$cands = get_posts(
		array(
			'post_type' => 'ut_result', 'posts_per_page' => 10, 'post_status' => 'any',
			'meta_query' => array(
				array( 'key' => '_ut_exam', 'value' => $exam ),
				array( 'key' => '_ut_student_id', 'value' => (int) $student_cpt_id ),
			),
		)
	);
	foreach ( $cands as $c ) {
		if ( uturn_norm_num( get_post_meta( $c->ID, '_ut_year', true ) ) === $yn ) {
			return $c;
		}
	}
	/* Legacy rows (CSV era) have no student link — match class+roll. */
	$cls  = get_post_meta( $student_cpt_id, '_ut_class', true );
	$roll = get_post_meta( $student_cpt_id, '_ut_roll', true );
	if ( '' === $cls || '' === $roll ) {
		return null;
	}
	$cands = get_posts(
		array(
			'post_type' => 'ut_result', 'posts_per_page' => 10, 'post_status' => 'any',
			'meta_query' => array(
				array( 'key' => '_ut_exam', 'value' => $exam ),
				array( 'key' => '_ut_class', 'value' => $cls ),
				array( 'key' => '_ut_roll', 'value' => $roll ),
			),
		)
	);
	foreach ( $cands as $c ) {
		if ( uturn_norm_num( get_post_meta( $c->ID, '_ut_year', true ) ) === $yn ) {
			return $c;
		}
	}
	return null;
}

/** Parse stored _ut_subjects lines into name => mark. */
function uturn_result_marks( $result_id ) {
	$out = array();
	$raw = (string) get_post_meta( (int) $result_id, '_ut_subjects', true );
	$lines = function_exists( 'uturn_lines' ) ? uturn_lines( $raw ) : explode( "\n", $raw );
	foreach ( $lines as $ln ) {
		$parts = array_map( 'trim', explode( '|', $ln ) );
		if ( count( $parts ) >= 2 && '' !== $parts[0] ) {
			$out[ $parts[0] ] = $parts[1];
		}
	}
	return $out;
}

/* ================= merit ================= */
/** Rank published pass-records of one exam+year+class by total or GPA (ties share). */
function uturn_recalc_merits( $exam, $year, $class ) {
	$yn = uturn_norm_num( $year );
	$cands = get_posts(
		array(
			'post_type' => 'ut_result', 'posts_per_page' => -1, 'post_status' => 'publish',
			'meta_query' => array(
				array( 'key' => '_ut_exam', 'value' => $exam ),
				array( 'key' => '_ut_class', 'value' => $class ),
			),
		)
	);
	$rows = array();
	foreach ( $cands as $c ) {
		if ( uturn_norm_num( get_post_meta( $c->ID, '_ut_year', true ) ) !== $yn ) {
			continue;
		}
		if ( get_post_meta( $c->ID, '_ut_status', true ) === 'fail' ) {
			update_post_meta( $c->ID, '_ut_merit', '' );
			continue;
		}
		$tot = function_exists( 'uturn_norm_num' ) ? uturn_norm_num( get_post_meta( $c->ID, '_ut_total', true ) ) : get_post_meta( $c->ID, '_ut_total', true );
		$gpa = function_exists( 'uturn_norm_num' ) ? uturn_norm_num( get_post_meta( $c->ID, '_ut_gpa', true ) ) : get_post_meta( $c->ID, '_ut_gpa', true );
		$rows[] = array( $c->ID, (float) $tot, (float) $gpa );
	}
	$by_gpa = get_option( 'uturn_rank_by', 'total' ) === 'gpa';
	$ki = $by_gpa ? 2 : 1;
	usort( $rows, function ( $a, $b ) use ( $ki ) { return $b[ $ki ] <=> $a[ $ki ]; } );
	$rank = 0;
	$pos = 0;
	$prev = null;
	foreach ( $rows as $r ) {
		$pos++;
		if ( null === $prev || $r[ $ki ] !== $prev ) {
			$rank = $pos;
			$prev = $r[ $ki ];
		}
		update_post_meta( $r[0], '_ut_merit', $rank );
	}
}

/** Human merit label: ১ম / ২য় / ৩য় / ৪র্থ / ৫ম … (BN digits). */
function uturn_merit_label( $rank ) {
	$n = (int) $rank;
	if ( $n <= 0 ) {
		return '—';
	}
	if ( 1 === $n ) {
		return '১ম';
	}
	if ( 2 === $n ) {
		return '২য়';
	}
	if ( 3 === $n ) {
		return '৩য়';
	}
	if ( 4 === $n ) {
		return '৪র্থ';
	}
	$bn = function_exists( 'uturn_bn' ) ? 'uturn_bn' : 'strval';
	return call_user_func( $bn, $n ) . 'ম';
}

/* ================= promotion engine ================= */
/**
 * Move ONE student (CPT id) to a new class. Identity (user + CPT id) never
 * changes; class meta + ut_class term + history are updated together.
 */
function uturn_promote_student( $student_cpt_id, $to_class ) {
	$sp = get_post( (int) $student_cpt_id );
	if ( ! $sp || 'ut_student' !== $sp->post_type ) {
		return false;
	}
	$from = get_post_meta( $sp->ID, '_ut_class', true );
	update_post_meta( $sp->ID, '_ut_class', $to_class );
	$uid = (int) get_post_meta( $sp->ID, '_ut_user_id', true );
	if ( $uid ) {
		update_user_meta( $uid, '_ut_class', $to_class );
	}
	$term = term_exists( $to_class, 'ut_class' );
	if ( ! $term ) {
		$term = wp_insert_term( $to_class, 'ut_class' );
	}
	if ( ! is_wp_error( $term ) ) {
		wp_set_object_terms( $sp->ID, (int) $term['term_id'], 'ut_class' );
	}
	$hist = get_post_meta( $sp->ID, '_ut_class_history', true );
	if ( ! is_array( $hist ) ) {
		$hist = array();
	}
	$hist[ date( 'Y' ) ] = $to_class;
	update_post_meta( $sp->ID, '_ut_class_history', $hist );
	if ( $uid ) {
		update_user_meta( $uid, '_ut_class_history', $hist );
	}
	return $from;
}

function uturn_student_set_status( $student_cpt_id, $status ) {
	$sp = get_post( (int) $student_cpt_id );
	if ( ! $sp || 'ut_student' !== $sp->post_type ) {
		return false;
	}
	$st = isset( uturn_student_statuses()[ $status ] ) ? $status : 'active';
	update_post_meta( $sp->ID, '_ut_status', $st );
	$uid = (int) get_post_meta( $sp->ID, '_ut_user_id', true );
	if ( $uid ) {
		update_user_meta( $uid, '_ut_status', $st );
	}
	return true;
}

/* ================= VIEW: marks entry ================= */
function uturn_dash_result_entry() {
	if ( function_exists( 'uturn_dash_erp_or_lock' ) && ! uturn_dash_erp_or_lock() ) {
		return;
	}
	$classes = uturn_entry_classes();
	if ( ! $classes ) {
		echo '<div class="utd-card"><p>⚠️ আপনার নামে কোনো শ্রেণি নির্ধারিত নেই। শ্রেণি-শিক্ষক / বিষয়-শিক্ষক হিসেবে যুক্ত হলে এখানে নম্বর এন্ট্রি করতে পারবেন।</p></div>';
		return;
	}
	$cls  = isset( $_GET['cls'] ) ? sanitize_text_field( wp_unslash( $_GET['cls'] ) ) : $classes[0];
	$exam = isset( $_GET['exam'] ) ? sanitize_key( $_GET['exam'] ) : 'annual';
	$exams = function_exists( 'uturn_exams' ) ? uturn_exams() : array();
	if ( ! isset( $exams[ $exam ] ) ) {
		$exam = key( $exams );
	}
	$year = isset( $_GET['year'] ) ? sanitize_text_field( wp_unslash( $_GET['year'] ) ) : ( function_exists( 'uturn_bn' ) ? uturn_bn( date( 'Y' ) ) : date( 'Y' ) );
	if ( ! in_array( $cls, $classes, true ) ) {
		$cls = $classes[0];
	}
	$can_publish = current_user_can( 'publish_ut_results' ) || current_user_can( 'manage_options' );
	if ( isset( $_GET['saved'] ) ) {
		echo '<div class="notice notice-success" role="status"><p>✅ সংরক্ষণ সম্পন্ন: খসড়া ' . esc_html( uturn_bn( absint( $_GET['saved'] ) ) ) . 'টি।</p></div>';
	}
	if ( isset( $_GET['submitted'] ) ) {
		echo '<div class="notice notice-success" role="status"><p>✅ পর্যালোচনার জন্য জমা: ' . esc_html( uturn_bn( absint( $_GET['submitted'] ) ) ) . 'টি। প্রকাশের পর শিক্ষার্থীরা দেখতে পাবে।</p></div>';
	}
	if ( isset( $_GET['published'] ) ) {
		echo '<div class="notice notice-success" role="status"><p>✅ সরাসরি প্রকাশ: ' . esc_html( uturn_bn( absint( $_GET['published'] ) ) ) . 'টি। মেধাস্থান হিসাব সম্পন্ন।</p></div>';
	}
	if ( isset( $_GET['err'] ) ) {
		echo '<div class="notice notice-error" role="alert"><p>⚠️ ' . esc_html( wp_unslash( $_GET['err'] ) ) . '</p></div>';
	}
	echo '<div class="utd-card noprint"><form method="get" action="' . esc_url( uturn_dash_url() ) . '"><input type="hidden" name="view" value="result-entry">';
	echo 'শ্রেণি <select name="cls" aria-label="শ্রেণি নির্বাচন">';
	foreach ( $classes as $c ) {
		echo '<option' . selected( $cls, $c, false ) . '>' . esc_html( $c ) . '</option>';
	}
	echo '</select> পরীক্ষা <select name="exam" aria-label="পরীক্ষা নির্বাচন">';
	foreach ( $exams as $slug => $bn ) {
		echo '<option value="' . esc_attr( $slug ) . '"' . selected( $exam, $slug, false ) . '>' . esc_html( $bn ) . '</option>';
	}
	echo '</select> বছর <input type="text" name="year" aria-label="শিক্ষাবর্ষ" value="' . esc_attr( $year ) . '" style="width:7em" required> ';
	$sec_all = uturn_entry_sections( $cls );
	$sec = isset( $_GET['sec'] ) ? sanitize_text_field( wp_unslash( $_GET['sec'] ) ) : '';
	$q = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
	if ( $sec_all ) {
		echo 'শাখা <select name="sec" aria-label="শাখা নির্বাচন"><option value="">— সব —</option>';
		foreach ( $sec_all as $snm ) {
			echo '<option' . selected( $sec, $snm, false ) . '>' . esc_html( $snm ) . '</option>';
		}
		echo '</select> ';
	}
	echo 'খুঁজুন <input type="search" name="q" value="' . esc_attr( $q ) . '" placeholder="নাম / রোল" style="width:10em" aria-label="নাম বা রোল দিয়ে খুঁজুন"> ';
	submit_button( 'এন্ট্রি গ্রিড খুলুন', 'secondary', '', false );
	echo '</form></div>';
	if ( isset( $_GET['setup'] ) ) {
		echo '<div class="notice notice-success" role="status"><p>✅ বিষয় সেটআপ সংরক্ষণ করা হয়েছে।</p></div>';
	}
	/* ---- Exam subject setup: which subjects + full marks (office configures) ---- */
	$setup = uturn_exam_setup_get( $cls, $exam, $year );
	$scope = uturn_entry_scoped_subjects( $cls );
	if ( $can_publish ) {
		$pref = $setup ? $setup : array();
		if ( ! $pref ) {
			foreach ( uturn_entry_subjects( $cls ) as $pn ) {
				$dm = function_exists( 'uturn_subject_marks_by_name' ) ? uturn_subject_marks_by_name( $pn ) : array( 'full' => 100, 'pass' => 33 );
				$pref[] = array( 'subject' => $pn, 'full' => $dm['full'] );
			}
		}
		$bnf = function_exists( 'uturn_bn' ) ? 'uturn_bn' : 'strval';
		echo '<details class="utd-card"' . ( $setup ? '' : ' open' ) . '><summary><b>📋 বিষয় ও পূর্ণমান সেটআপ</b> <span class="utd-muted">— এই পরীক্ষায় কোন কোন বিষয় থাকবে ও প্রতিটির পূর্ণমান কত</span></summary>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" id="utSetupForm"><input type="hidden" name="action" value="uturn_exam_setup_save"><input type="hidden" name="ut_shell" value="1">';
		wp_nonce_field( 'uturn_exam_setup_save' );
		echo '<input type="hidden" name="cls" value="' . esc_attr( $cls ) . '"><input type="hidden" name="exam" value="' . esc_attr( $exam ) . '"><input type="hidden" name="year" value="' . esc_attr( $year ) . '">';
		echo '<div class="table-wrap"><table class="widefat striped" aria-label="বিষয় সেটআপ" id="utSetupTbl"><thead><tr><th scope="col">বিষয়</th><th scope="col">পূর্ণমান</th><th scope="col"></th></tr></thead><tbody>';
		$ri = 0;
		foreach ( $pref as $pr ) {
			echo '<tr><td><input type="text" name="sub[' . (int) $ri . ']" value="' . esc_attr( $pr['subject'] ) . '" class="regular-text" aria-label="বিষয়ের নাম"></td><td><input type="number" name="full[' . (int) $ri . ']" value="' . esc_attr( (string) $pr['full'] ) . '" min="1" max="1000" step="1" style="width:7em" aria-label="পূর্ণমান"></td><td><button type="button" class="button button-small" onclick="this.closest(\'tr\').remove()">✕</button></td></tr>';
			$ri++;
		}
		echo '</tbody></table></div>';
		echo '<p><button type="button" class="button" id="utSetupAdd">＋ বিষয় যোগ করুন</button> ';
		submit_button( 'সেটআপ সংরক্ষণ করুন', 'primary', '', false );
		echo '</p></form>';
		echo '<script>(function(){var t=document.querySelector("#utSetupTbl tbody"),b=document.getElementById("utSetupAdd"),i=' . (int) $ri . ';if(b)b.onclick=function(){var r=document.createElement("tr");r.innerHTML="<td><input type=\"text\" name=\"sub["+i+"]\" class=\"regular-text\" aria-label=\"বিষয়ের নাম\"></td><td><input type=\"number\" name=\"full["+i+"]\" value=\"100\" min=\"1\" max=\"1000\" style=\"width:7em\" aria-label=\"পূর্ণমান\"></td><td></td>";t.appendChild(r);i++;};})();</script>';
		echo '</details>';
	} elseif ( ! $setup ) {
		echo '<div class="notice notice-info" role="status"><p>ℹ️ এই পরীক্ষার বিষয় সেটআপ এখনো হয়নি — শ্রেণির ডিফল্ট বিষয় (পূর্ণমান ১০০) দেখানো হচ্ছে।</p></div>';
	}
	$subjects = $setup ? array_column( $setup, 'subject' ) : uturn_entry_subjects( $cls );
	$fulls = array();
	if ( $setup ) {
		foreach ( $setup as $sr ) {
			$fulls[ $sr['subject'] ] = $sr['full'];
		}
	}
	if ( $scope ) {
		/* Subject-teachers can always enter their own mapped subjects — auto-added. */
		$subjects = array_values( array_unique( array_merge( $subjects, $scope ) ) );
	}
	$base_subjects = $subjects;
	foreach ( uturn_entry_union_extras( $cls ) as $xn ) {
		if ( ! in_array( $xn, $subjects, true ) ) {
			$subjects[] = $xn;
			$dm = function_exists( 'uturn_subject_marks_by_name' ) ? uturn_subject_marks_by_name( $xn ) : array( 'full' => 100, 'pass' => 33 );
			$fulls[ $xn ] = $dm['full'];
		}
	}
	$has_extras = count( $subjects ) !== count( $base_subjects );
	if ( ! $subjects ) {
		echo '<div class="utd-card"><p>⚠️ এই শ্রেণির বিষয় তালিকা পাওয়া যায়নি। প্রথমে “বিষয়সমূহ” বা “শ্রেণি ব্যবস্থাপনা”-তে বিষয় যোগ করুন।</p></div>';
		return;
	}
	$students = uturn_entry_students( $cls );
	if ( '' !== $sec ) {
		$students = array_values( array_filter( $students, function ( $sp ) use ( $sec ) { return trim( (string) get_post_meta( $sp->ID, '_ut_section', true ) ) === $sec; } ) );
	}
	if ( '' !== $q ) {
		$students = array_values( array_filter( $students, function ( $sp ) use ( $q ) { return false !== mb_stripos( $sp->post_title, $q ) || false !== mb_stripos( (string) get_post_meta( $sp->ID, '_ut_roll', true ), $q ); } ) );
	}
	if ( ! $students ) {
		echo '<div class="utd-card"><p class="utd-muted">এই শ্রেণিতে সক্রিয় শিক্ষার্থী নেই।</p></div>';
		return;
	}
	if ( $scope ) {
		echo '<div class="notice notice-info" role="status"><p>ℹ️ আপনি <b>বিষয়-শিক্ষক</b> — শুধু নিজের বিষয় (' . esc_html( implode( ', ', $scope ) ) . ') এন্ট্রি করতে পারবেন। সংরক্ষণ খসড়া হিসেবে জমা হবে; শ্রেণি-শিক্ষক সম্পূর্ণ হলে পর্যালোচনার জন্য জমা দেবেন।</p></div>';
	}
	$st_lbl = array( 'publish' => 'প্রকাশিত', 'pending' => 'পর্যালোচনাধীন', 'draft' => 'খসড়া' );
	$stages = uturn_result_stages();
	echo '<div class="utd-card"><h2>📝 ' . esc_html( $cls ) . ' — ' . esc_html( $exams[ $exam ] ) . ' ' . esc_html( $year ) . ' <small class="utd-muted">(মোট ' . esc_html( function_exists( 'uturn_bn' ) ? uturn_bn( count( $students ) ) : count( $students ) ) . ' জন · ' . esc_html( function_exists( 'uturn_bn' ) ? uturn_bn( count( $subjects ) ) : count( $subjects ) ) . ' বিষয়)</small></h2>';
	if ( $has_extras ) {
		echo '<p class="utd-muted">✳ চিহ্নিত কলাম = অতিরিক্ত বিষয় — শুধু যাদের প্রোফাইলে নির্ধারিত তাদের ঘর খোলা, বাকিদের বন্ধ। সম্পূর্ণতার হিসাব প্রত্যেকের প্রযোজ্য বিষয় অনুযায়ী।</p>';
	}
	echo '<p class="utd-muted">বিষয়ের পূর্ণমান অনুযায়ী নম্বর লিখুন — মোট, %, জিপিএ ও ফলাফল স্বয়ংক্রিয়ভাবে হিসাব হবে। খালি ঘর = এখনো এন্ট্রি হয়নি।</p>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" id="utMarksForm"><input type="hidden" name="action" value="uturn_marks_save"><input type="hidden" name="ut_shell" value="1">';
	wp_nonce_field( 'uturn_marks_save' );
	echo '<input type="hidden" name="cls" value="' . esc_attr( $cls ) . '"><input type="hidden" name="exam" value="' . esc_attr( $exam ) . '"><input type="hidden" name="year" aria-label="শিক্ষাবর্ষ" value="' . esc_attr( $year ) . '">';
	$show_sec = (bool) $sec_all;
	echo '<div class="table-wrap"><table class="widefat striped ut-marks" aria-label="নম্বর এন্ট্রি গ্রিড"><thead><tr><th scope="col">রোল</th><th scope="col">নাম</th><th scope="col">স্টুডেন্ট ID</th>' . ( $show_sec ? '<th scope="col">শাখা</th>' : '' );
	$bnf = function_exists( 'uturn_bn' ) ? 'uturn_bn' : 'strval';
	foreach ( $subjects as $si => $sn ) {
		$scoped_out = $scope && ! in_array( $sn, $scope, true );
		$is_extra = ! in_array( $sn, $base_subjects, true );
		$fl = isset( $fulls[ $sn ] ) ? $fulls[ $sn ] : 100;
		echo '<th scope="col"' . ( $scoped_out ? ' class="ut-scoped-out"' : '' ) . ( $is_extra ? ' title="অতিরিক্ত বিষয় (শুধু নির্ধারিত শিক্ষার্থী)"' : '' ) . '>' . esc_html( $sn ) . ( $is_extra ? ' ✳' : '' ) . '<br><small class="utd-muted">পূর্ণ ' . esc_html( call_user_func( $bnf, (string) $fl ) ) . '</small>' . ( $scoped_out ? ' 🔒' : '' ) . '</th>';
	}
	echo '<th scope="col">মোট</th><th scope="col">%</th><th scope="col">জিপিএ</th><th scope="col">ফল</th><th scope="col">অবস্থা</th></tr></thead><tbody>';
	foreach ( $students as $sp ) {
		$roll = get_post_meta( $sp->ID, '_ut_roll', true );
		$my_extras = uturn_student_extras( $sp->ID );
		$ex = uturn_find_result( $sp->ID, $exam, $year );
		$pre = $ex ? uturn_result_marks( $ex->ID ) : array();
		$locked = $ex && 'publish' === $ex->post_status && ! $can_publish;
		$stage = $ex ? uturn_result_stage( $ex->ID ) : '';
		echo '<tr data-row="' . (int) $sp->ID . '"><td><b>' . esc_html( $roll ) . '</b></td><td>' . esc_html( $sp->post_title ) . '</td><td><small>' . esc_html( uturn_ensure_student_code( $sp->ID ) ) . '</small></td>' . ( $show_sec ? '<td>' . esc_html( get_post_meta( $sp->ID, '_ut_section', true ) ) . '</td>' : '' );
		foreach ( $subjects as $si => $sn ) {
			$v = isset( $pre[ $sn ] ) ? $pre[ $sn ] : '';
			$scoped_out = $scope && ! in_array( $sn, $scope, true );
			$na = ! in_array( $sn, $base_subjects, true ) && ! in_array( $sn, $my_extras, true );
			$dis = ( $locked || $scoped_out || $na ) ? ' disabled' : '';
			$fl = isset( $fulls[ $sn ] ) ? (float) $fulls[ $sn ] : 100;
			if ( $fl <= 0 ) {
				$fl = 100;
			}
			echo '<td' . ( $scoped_out || $na ? ' class="ut-scoped-out"' : '' ) . '><input type="number" name="marks[' . (int) $sp->ID . '][' . (int) $si . ']" value="' . esc_attr( $v ) . '" min="0" max="' . esc_attr( (string) $fl ) . '" step="1" class="ut-mk" data-sub="' . esc_attr( $sn ) . '" data-full="' . esc_attr( (string) $fl ) . '" aria-label="' . esc_attr( 'রোল ' . $roll . ' — ' . $sn . ' (পূর্ণ ' . $fl . ')' ) . '"' . $dis . '></td>';
		}
		echo '<td class="ut-t"></td><td class="ut-p"></td><td class="ut-g"></td><td class="ut-s"></td>';
		if ( $ex && 'correction' === $stage ) {
			$pill = '<span class="utd-pill red">✏️ সংশোধন প্রয়োজন</span>';
		} elseif ( $ex ) {
			$pill = '<span class="utd-pill ' . ( 'publish' === $ex->post_status ? 'green' : ( 'pending' === $ex->post_status ? 'blue' : '' ) ) . '">' . esc_html( isset( $stages[ $stage ] ) ? $stages[ $stage ] : ( $st_lbl[ $ex->post_status ] ?? $ex->post_status ) ) . '</span>';
		} else {
			$pill = '<span class="utd-pill">নতুন</span>';
		}
		echo '<td>' . $pill . ( $locked ? '<br><small>প্রকাশিত</small>' : '' ) . '</td></tr>';
	}
	echo '</tbody></table></div>';
	echo '<p id="utMarksSum" class="utd-muted"></p>';
	echo '<p><button class="button" type="submit" name="mode" value="draft">💾 খসড়া সংরক্ষণ</button> ';
	if ( ! $scope ) {
		echo '<button class="button button-primary" type="submit" name="mode" value="submit">📤 পর্যালোচনার জন্য জমা দিন</button> ';
	}
	if ( $can_publish ) {
		echo '<button class="button button-primary" type="submit" name="mode" value="publish" style="background:#0a7b3d;border-color:#0a7b3d" onclick="return confirm(\'সরাসরি প্রকাশ করবেন? প্রকাশের সাথে সাথে ফলাফল পাবলিক হবে।\')">🚀 সরাসরি প্রকাশ করুন</button>';
	}
	echo '</p></form></div>';
	?>
	<script>
	(function(){
	  var SCALE=<?php echo wp_json_encode( uturn_grade_scale() ); ?>;
	  var G=function(pct){pct=parseFloat(pct);if(isNaN(pct))return null;for(var i=0;i<SCALE.length;i++){if(pct>=parseFloat(SCALE[i].min))return[SCALE[i].grade,parseFloat(SCALE[i].point)];}return['F',0];};
	  function row(r){
	    var t=0,p=0,n=0,f=false,ft=0;
	    r.querySelectorAll('.ut-mk').forEach(function(i){
	      if(i.disabled)return;var v=i.value.trim();if(v==='')return;
	      var fl=parseFloat(i.getAttribute('data-full'))||100;if(fl<=0)fl=100;
	      var mk=Math.max(0,Math.min(fl,parseFloat(v)));var g=G((mk/fl)*100);if(!g)return;
	      t+=mk;p+=g[1];n++;ft+=fl;if(g[0]==='F')f=true;
	    });
	    r.querySelector('.ut-t').textContent=n?t:'—';
	    var pc=r.querySelector('.ut-p');if(pc)pc.textContent=n?((t/ft)*100).toFixed(1)+'%':'—';
	    r.querySelector('.ut-g').textContent=n?(p/n).toFixed(2):'—';
	    var s=r.querySelector('.ut-s');
	    if(!n){s.textContent='—';s.style.color='';}
	    else if(f){s.textContent='অনুত্তীর্ণ';s.style.color='#c00';}
	    else{s.textContent='উত্তীর্ণ';s.style.color='#0a7b3d';}
	    return n;
	  }
	  function all(){
	    var rows=document.querySelectorAll('#utMarksForm tr[data-row]'),done=0,gp=0,gn=0,pass=0;
	    rows.forEach(function(r){
	      var t=0,p=0,n=0,f=false;
	      r.querySelectorAll('.ut-mk').forEach(function(i){
	        if(i.disabled)return;var v=i.value.trim();if(v==='')return;
	        var fl=parseFloat(i.getAttribute('data-full'))||100;if(fl<=0)fl=100;
	        var mk=Math.max(0,Math.min(fl,parseFloat(v)));var g=G((mk/fl)*100);if(!g)return;
	        t+=mk;p+=g[1];n++;if(g[0]==='F')f=true;
	      });
	      var tot=r.querySelectorAll('.ut-mk:not(:disabled)').length;
	      if(tot&&n===tot){done++;gp+=p/n;gn++;if(!f)pass++;}
	    });
	    document.getElementById('utMarksSum').textContent='সম্পূর্ণ এন্ট্রি: '+done+'/'+rows.length+' জন'+(gn?' · গড় জিপিএ: '+(gp/gn).toFixed(2)+' · সম্ভাব্য উত্তীর্ণ: '+pass+' জন':'');
	  }
	  document.querySelectorAll('#utMarksForm tr[data-row]').forEach(function(r){row(r);r.addEventListener('input',function(){row(r);all();});});
	  all();
	})();
	</script>
	<?php
}

add_action( 'admin_post_uturn_exam_setup_save', 'uturn_exam_setup_save' );
function uturn_exam_setup_save() {
	if ( function_exists( 'eduturn_license_require' ) ) {
		eduturn_license_require( 'erp' );
	}
	$cls  = isset( $_POST['cls'] ) ? sanitize_text_field( wp_unslash( $_POST['cls'] ) ) : '';
	$exam = isset( $_POST['exam'] ) ? sanitize_key( $_POST['exam'] ) : '';
	$year = isset( $_POST['year'] ) ? sanitize_text_field( wp_unslash( $_POST['year'] ) ) : '';
	$back = function_exists( 'uturn_dash_view_url' ) ? uturn_dash_view_url( 'result-entry' ) : admin_url();
	$back = add_query_arg( array( 'cls' => $cls, 'exam' => $exam, 'year' => $year ), $back );
	if ( ! check_admin_referer( 'uturn_exam_setup_save' ) || ( ! current_user_can( 'publish_ut_results' ) && ! current_user_can( 'manage_options' ) ) ) {
		wp_die( 'Unauthorized.' );
	}
	$subs = isset( $_POST['sub'] ) && is_array( $_POST['sub'] ) ? wp_unslash( $_POST['sub'] ) : array();
	$fls = isset( $_POST['full'] ) && is_array( $_POST['full'] ) ? wp_unslash( $_POST['full'] ) : array();
	$rows = array();
	$seen = array();
	foreach ( $subs as $i => $nm ) {
		$nm = str_replace( '|', '', sanitize_text_field( $nm ) );
		if ( '' === $nm || isset( $seen[ $nm ] ) ) {
			continue;
		}
		$seen[ $nm ] = true;
		$fl = isset( $fls[ $i ] ) ? (float) $fls[ $i ] : 100;
		if ( $fl <= 0 ) {
			$fl = 100;
		}
		if ( $fl > 1000 ) {
			$fl = 1000;
		}
		$rows[] = array( 'subject' => $nm, 'full' => $fl );
	}
	uturn_exam_setup_save_rows( $cls, $exam, $year, $rows );
	wp_safe_redirect( add_query_arg( 'setup', '1', $back ) );
	exit;
}

add_action( 'admin_post_uturn_marks_save', 'uturn_marks_save' );
function uturn_marks_save() {
	if ( function_exists( 'eduturn_license_require' ) ) {
		eduturn_license_require( 'erp' );
	}
	$back = function_exists( 'uturn_dash_view_url' ) ? uturn_dash_view_url( 'result-entry' ) : admin_url( 'admin.php?page=eduturn-result-import' );
	$cls  = isset( $_POST['cls'] ) ? sanitize_text_field( wp_unslash( $_POST['cls'] ) ) : '';
	$exam = isset( $_POST['exam'] ) ? sanitize_key( $_POST['exam'] ) : '';
	$year = isset( $_POST['year'] ) ? sanitize_text_field( wp_unslash( $_POST['year'] ) ) : '';
	$mode = isset( $_POST['mode'] ) ? sanitize_key( $_POST['mode'] ) : 'draft';
	$back = add_query_arg( array( 'cls' => $cls, 'exam' => $exam, 'year' => $year ), $back );
	if ( ! check_admin_referer( 'uturn_marks_save' ) || ! current_user_can( 'edit_ut_results' ) ) {
		wp_die( 'Unauthorized.' );
	}
	$exams = function_exists( 'uturn_exams' ) ? uturn_exams() : array();
	if ( '' === $cls || ! isset( $exams[ $exam ] ) || '' === $year ) {
		wp_die( 'Unauthorized.' );
	}
	/* Teachers: own classes only. */
	$scope = array();
	if ( ! current_user_can( 'publish_ut_results' ) && ! current_user_can( 'manage_options' ) ) {
		$mine = function_exists( 'uturn_teacher_classes' ) ? array_keys( uturn_teacher_classes( get_current_user_id() ) ) : array();
		if ( ! in_array( $cls, $mine, true ) ) {
			wp_die( 'Unauthorized.' );
		}
		if ( 'publish' === $mode ) {
			wp_die( 'Unauthorized.' );
		}
		$scope = uturn_entry_scoped_subjects( $cls );
		if ( $scope ) {
			/* Subject-teachers enter their own subjects only (draft); the
			 * class-teacher submits complete rows for review. */
			$mode = 'draft';
		}
	}
	$setup = uturn_exam_setup_get( $cls, $exam, $year );
	$subjects = $setup ? array_column( $setup, 'subject' ) : uturn_entry_subjects( $cls );
	$fulls = array();
	if ( $setup ) {
		foreach ( $setup as $sr ) {
			$fulls[ $sr['subject'] ] = $sr['full'];
		}
	}
	if ( $scope ) {
		$subjects = array_values( array_unique( array_merge( $subjects, $scope ) ) );
	}
	$save_base = $subjects;
	foreach ( uturn_entry_union_extras( $cls ) as $xn ) {
		if ( ! in_array( $xn, $subjects, true ) ) {
			$subjects[] = $xn;
			$dm = function_exists( 'uturn_subject_marks_by_name' ) ? uturn_subject_marks_by_name( $xn ) : array( 'full' => 100, 'pass' => 33 );
			$fulls[ $xn ] = $dm['full'];
		}
	}
	if ( ! $subjects ) {
		wp_safe_redirect( add_query_arg( 'err', rawurlencode( 'বিষয় তালিকা পাওয়া যায়নি।' ), $back ) );
		exit;
	}
	$posted = isset( $_POST['marks'] ) && is_array( $_POST['marks'] ) ? $_POST['marks'] : array();
	if ( 'submit' === $mode || 'publish' === $mode ) {
		/* Submit/publish needs complete grids — list incomplete rolls. */
		$bad = array();
		foreach ( $posted as $sid => $cells ) {
			$miss = 0;
			$sid_extras = uturn_student_extras( (int) $sid );
			foreach ( $subjects as $si => $sn ) {
				if ( ! in_array( $sn, $save_base, true ) && ! in_array( $sn, $sid_extras, true ) ) {
					continue; // extra column that doesn't apply to this student
				}
				$v = isset( $cells[ $si ] ) ? trim( (string) $cells[ $si ] ) : '';
				if ( $v === '' ) {
					$miss++;
				}
			}
			if ( $miss ) {
				$bad[] = get_post_meta( (int) $sid, '_ut_roll', true );
			}
		}
		if ( $bad ) {
			wp_safe_redirect( add_query_arg( 'err', rawurlencode( 'অসম্পূর্ণ এন্ট্রি (রোল: ' . implode( ', ', array_slice( $bad, 0, 8 ) ) . ( count( $bad ) > 8 ? '…' : '' ) . ') — সব ঘর পূরণ করুন অথবা খসড়া রাখুন।' ), $back ) );
			exit;
		}
	}
	$target = 'publish' === $mode ? 'publish' : ( 'submit' === $mode ? 'pending' : 'draft' );
	$n = 0;
	$merit_sets = array();
	foreach ( $posted as $sid => $cells ) {
		$sid = (int) $sid;
		$sp = get_post( $sid );
		if ( ! $sp || 'ut_student' !== $sp->post_type ) {
			continue;
		}
		if ( get_post_meta( $sid, '_ut_class', true ) !== $cls ) {
			continue;
		}
		if ( ! uturn_student_on_roster( $sid ) ) {
			continue;
		}
		$ex0 = uturn_find_result( $sid, $exam, $year );
		$marks = array();
		$sid_extras = uturn_student_extras( $sid );
		foreach ( $subjects as $si => $sn ) {
			if ( ( $scope && ! in_array( $sn, $scope, true ) ) || ( ! in_array( $sn, $save_base, true ) && ! in_array( $sn, $sid_extras, true ) ) ) {
				/* Out-of-scope / non-applicable column: keep whatever is already stored. */
				$marks[ $sn ] = '';
				if ( $ex0 ) {
					$old_marks = uturn_result_marks( $ex0->ID );
					if ( isset( $old_marks[ $sn ] ) ) {
						$marks[ $sn ] = $old_marks[ $sn ];
					}
				}
				continue;
			}
			$v = isset( $cells[ $si ] ) ? trim( (string) $cells[ $si ] ) : '';
			$fl = isset( $fulls[ $sn ] ) ? (float) $fulls[ $sn ] : 100;
			if ( $fl <= 0 ) {
				$fl = 100;
			}
			if ( '' !== $v && is_numeric( $v ) ) {
				$v = (string) max( 0, min( $fl, (float) $v ) );
				$v = (string) ( (float) $v == (int) $v ? (int) $v : round( (float) $v, 1 ) );
			} else {
				$v = '';
			}
			$marks[ $sn ] = $v;
		}
		$calc = uturn_calc_result( $marks, $fulls );
		if ( 0 === $calc['entered'] && 'draft' === $target ) {
			continue; // empty draft row — nothing to store
		}
		$lines = array();
		foreach ( $calc['subjects'] as $sn => $d ) {
			if ( '' === $d['mark'] ) {
				continue;
			}
			$fl = isset( $d['full'] ) ? $d['full'] : 100;
			$fls = ( (float) $fl == (int) $fl ) ? (string) (int) $fl : (string) $fl;
			$lines[] = $sn . '|' . $d['mark'] . '|' . $d['grade'] . '|' . $fls;
		}
		$ex = uturn_find_result( $sid, $exam, $year );
		if ( $ex && 'publish' === $ex->post_status && ! current_user_can( 'publish_ut_results' ) && ! current_user_can( 'manage_options' ) ) {
			continue; // teachers can't touch published rows
		}
		$title = $sp->post_title . ' — ' . $exams[ $exam ] . ' ' . $year;
		$stage = 'publish' === $mode ? 'published' : ( 'submit' === $mode ? 'submitted' : 'draft' );
		$meta = array(
			'_ut_exam' => $exam, '_ut_exam_bn' => $exams[ $exam ], '_ut_year' => $year,
			'_ut_class' => $cls, '_ut_roll' => get_post_meta( $sid, '_ut_roll', true ),
			'_ut_reg' => $ex ? get_post_meta( $ex->ID, '_ut_reg', true ) : '',
			'_ut_student_id' => $sid,
			'_ut_student_code' => uturn_ensure_student_code( $sid ),
			'_ut_section' => get_post_meta( $sid, '_ut_section', true ),
			'_ut_stage' => $stage,
			'_ut_total' => $calc['total'], '_ut_gpa' => $calc['gpa'],
			'_ut_status' => $calc['status'], '_ut_subjects' => implode( "\n", $lines ),
		);
		if ( $ex && ( 'publish' === $ex->post_status || 'publish' === $target ) ) {
			/* A published row changed state — merits must be recomputed. */
			$merit_sets[ $exam . '|' . uturn_norm_num( $year ) . '|' . $cls ] = true;
		}
		if ( $ex ) {
			wp_update_post( array( 'ID' => $ex->ID, 'post_title' => wp_slash( $title ), 'post_status' => $target ) );
			foreach ( $meta as $k => $v ) {
				update_post_meta( $ex->ID, $k, $v );
			}
			$rid = $ex->ID;
		} else {
			$id = wp_insert_post( array( 'post_title' => wp_slash( $title ), 'post_type' => 'ut_result', 'post_status' => $target ) );
			if ( $id && ! is_wp_error( $id ) ) {
				foreach ( $meta as $k => $v ) {
					update_post_meta( $id, $k, $v );
				}
				$rid = $id;
			} else {
				continue;
			}
		}
		uturn_result_audit( $rid, 'marks_save', $mode . ' · ' . $calc['entered'] . '/' . count( $subjects ) . ' বিষয়' );
		$n++;
	}
	if ( 'publish' === $mode || $merit_sets ) {
		uturn_recalc_merits( $exam, $year, $cls );
	}
	$key = 'publish' === $mode ? 'published' : ( 'submit' === $mode ? 'submitted' : 'saved' );
	wp_safe_redirect( add_query_arg( $key, $n, $back ) );
	exit;
}

/* ================= standalone landscape print: tabulation ================= */
add_action( 'template_redirect', 'uturn_tabulation_print_view' );
function uturn_tabulation_print_view() {
	if ( ! isset( $_GET['ut_print'] ) || 'tabulation' !== sanitize_key( $_GET['ut_print'] ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_ut_results' ) ) {
		wp_die( 'অনুমতি নেই।' );
	}
	$cls  = isset( $_GET['cls'] ) ? sanitize_text_field( wp_unslash( $_GET['cls'] ) ) : '';
	$exam = isset( $_GET['exam'] ) ? sanitize_key( $_GET['exam'] ) : '';
	$year = isset( $_GET['year'] ) ? sanitize_text_field( wp_unslash( $_GET['year'] ) ) : '';
	$exams = function_exists( 'uturn_exams' ) ? uturn_exams( false ) : array();
	if ( '' === $cls || ! isset( $exams[ $exam ] ) || '' === $year ) {
		wp_die( 'তথ্য অসম্পূর্ণ।' );
	}
	/* Teachers: own classes only. */
	if ( ! current_user_can( 'publish_ut_results' ) && ! current_user_can( 'manage_options' ) ) {
		$mine = function_exists( 'uturn_teacher_classes' ) ? array_keys( uturn_teacher_classes( get_current_user_id() ) ) : array();
		if ( ! in_array( $cls, $mine, true ) ) {
			wp_die( 'অনুমতি নেই।' );
		}
	}
	$yn = uturn_norm_num( $year );
	$rows = get_posts(
		array(
			'post_type' => 'ut_result', 'posts_per_page' => -1,
			'post_status' => array( 'publish', 'pending' ),
			'meta_query' => array(
				array( 'key' => '_ut_exam', 'value' => $exam ),
				array( 'key' => '_ut_class', 'value' => $cls ),
			),
		)
	);
	$sheet = array();
	foreach ( $rows as $r ) {
		if ( uturn_norm_num( get_post_meta( $r->ID, '_ut_year', true ) ) === $yn ) {
			$sheet[] = $r;
		}
	}
	usort(
		$sheet,
		function ( $a, $b ) {
			$ra = uturn_norm_num( get_post_meta( $a->ID, '_ut_roll', true ) );
			$rb = uturn_norm_num( get_post_meta( $b->ID, '_ut_roll', true ) );
			$ia = is_numeric( $ra ) ? (int) $ra : PHP_INT_MAX;
			$ib = is_numeric( $rb ) ? (int) $rb : PHP_INT_MAX;
			return $ia - $ib;
		}
	);
	$subs = array();
	$tfulls = array();
	$st = uturn_exam_setup_get( $cls, $exam, $year );
	if ( $st ) {
		foreach ( $st as $sr ) {
			$tfulls[ $sr['subject'] ] = $sr['full'];
		}
	}
	foreach ( $sheet as $r ) {
		foreach ( array_keys( uturn_result_marks( $r->ID ) ) as $sn ) {
			$subs[ $sn ] = true;
		}
		foreach ( uturn_result_fulls( $r->ID ) as $sn => $fl ) {
			if ( ! isset( $tfulls[ $sn ] ) ) {
				$tfulls[ $sn ] = $fl;
			}
		}
	}
	$subs = array_keys( $subs );
	$bnf = function_exists( 'uturn_bn' ) ? 'uturn_bn' : 'strval';
	$school = function_exists( 'uturn_opt' ) ? uturn_opt( 'school_name_bn', get_bloginfo( 'name' ) ) : get_bloginfo( 'name' );
	$addr = function_exists( 'uturn_opt' ) ? uturn_opt( 'address', '' ) : '';
	$nsub = max( 1, count( $subs ) );
	$subw = $nsub > 8 ? 62 : 74;
	$fs = $nsub > 8 ? '10px' : '11.5px';
	status_header( 200 );
	header( 'Content-Type: text/html; charset=utf-8' );
	echo '<!DOCTYPE html><html lang="bn"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>ট্যাবুলেশন শিট — ' . esc_html( $cls ) . '</title><style>';
	echo '@page{size:A4 landscape;margin:9mm}*{box-sizing:border-box}body{font-family:sans-serif;color:#111;margin:0;padding:10px}';
	echo '.ph{text-align:center;border-bottom:2px solid #111;margin-bottom:8px;padding-bottom:6px}.ph b{display:block;font-size:18px}.ph span{display:block;font-size:11.5px;color:#333}.ph i{display:block;font-style:normal;font-weight:700;font-size:13.5px}';
	echo 'table{width:100%;border-collapse:collapse;table-layout:fixed;font-size:' . $fs . '}';
	echo 'thead{display:table-header-group}th,td{border:1px solid #444;padding:3px 4px;text-align:center;word-wrap:break-word}';
	echo 'th{background:#eee}td.l,th.l{text-align:left}tr{page-break-inside:avoid}';
	echo '.sg{display:flex;justify-content:space-between;margin-top:30px;font-size:12px}.sg span{border-top:1px solid #111;padding:2px 12px 0}';
	echo '@media print{.noprint{display:none!important}body{padding:0}}';
	echo '</style></head><body>';
	echo '<div class="noprint" style="margin-bottom:10px"><button onclick="window.print()" style="padding:8px 22px;font-size:15px">🖨️ প্রিন্ট করুন</button></div>';
	echo '<div class="ph"><b>' . esc_html( $school ) . '</b><span>' . esc_html( $addr ) . '</span><i>ট্যাবুলেশন শিট — ' . esc_html( $exams[ $exam ] ) . ' ' . esc_html( $year ) . ' · শ্রেণি: ' . esc_html( $cls ) . '</i></div>';
	echo '<table><colgroup><col style="width:44px"><col><col style="width:52px">';
	foreach ( $subs as $sn ) {
		echo '<col style="width:' . (int) $subw . 'px">';
	}
	echo '<col style="width:56px"><col style="width:56px"><col style="width:60px"><col style="width:70px"></colgroup><thead><tr><th>রোল</th><th class="l">নাম</th><th>শাখা</th>';
	foreach ( $subs as $sn ) {
		$fl = isset( $tfulls[ $sn ] ) ? $tfulls[ $sn ] : 100;
		echo '<th>' . esc_html( $sn ) . '<br><small>পূর্ণ ' . esc_html( call_user_func( $bnf, (string) $fl ) ) . '</small></th>';
	}
	echo '<th>মোট</th><th>জিপিএ</th><th>মেধা</th><th>ফল</th></tr></thead><tbody>';
	foreach ( $sheet as $r ) {
		$g = function ( $k ) use ( $r ) { return get_post_meta( $r->ID, $k, true ); };
		$sid = (int) $g( '_ut_student_id' );
		$name = $sid ? get_the_title( $sid ) : preg_replace( '/\s—.*$/u', '', $r->post_title );
		$mk = uturn_result_marks( $r->ID );
		echo '<tr><td><b>' . esc_html( $g( '_ut_roll' ) ) . '</b></td><td class="l">' . esc_html( $name ) . '</td><td>' . esc_html( $g( '_ut_section' ) !== '' ? $g( '_ut_section' ) : '—' ) . '</td>';
		foreach ( $subs as $sn ) {
			echo '<td>' . esc_html( isset( $mk[ $sn ] ) ? $mk[ $sn ] : '—' ) . '</td>';
		}
		echo '<td><b>' . esc_html( $g( '_ut_total' ) ) . '</b></td><td>' . esc_html( $g( '_ut_gpa' ) ) . '</td><td><b>' . esc_html( uturn_merit_label( $g( '_ut_merit' ) ) ) . '</b></td><td>' . ( $g( '_ut_status' ) !== 'fail' ? 'উত্তীর্ণ' : 'অনুত্তীর্ণ' ) . '</td></tr>';
	}
	echo '</tbody></table>';
	echo '<div class="sg"><span>শ্রেণি শিক্ষক</span><span>পরীক্ষা নিয়ন্ত্রক</span><span>প্রধান শিক্ষক</span></div>';
	echo '</body></html>';
	exit;
}

/* ================= VIEW: review + publish ================= */
function uturn_dash_result_review() {
	if ( function_exists( 'uturn_dash_erp_or_lock' ) && ! uturn_dash_erp_or_lock() ) {
		return;
	}
	$exams = function_exists( 'uturn_exams' ) ? uturn_exams( false ) : array();
	$classes = array();
	if ( function_exists( 'uturn_class_terms' ) ) {
		foreach ( uturn_class_terms() as $t ) {
			$classes[] = $t->name;
		}
	}
	$exam = isset( $_GET['exam'] ) ? sanitize_key( $_GET['exam'] ) : 'annual';
	if ( ! isset( $exams[ $exam ] ) ) {
		$exam = key( $exams );
	}
	$year = isset( $_GET['year'] ) ? sanitize_text_field( wp_unslash( $_GET['year'] ) ) : ( function_exists( 'uturn_bn' ) ? uturn_bn( date( 'Y' ) ) : date( 'Y' ) );
	$cls  = isset( $_GET['cls'] ) ? sanitize_text_field( wp_unslash( $_GET['cls'] ) ) : '';
	if ( isset( $_GET['pub'] ) ) {
		echo '<div class="notice notice-success" role="status"><p>✅ প্রকাশ সম্পন্ন: ' . esc_html( function_exists( 'uturn_bn' ) ? uturn_bn( absint( $_GET['pub'] ) ) : absint( $_GET['pub'] ) ) . 'টি ফলাফল এখন পাবলিক।' . ( isset( $_GET['promo'] ) ? ' শ্রেণি উন্নীত: ' . esc_html( function_exists( 'uturn_bn' ) ? uturn_bn( absint( $_GET['promo'] ) ) : absint( $_GET['promo'] ) ) . ' জন।' : '' ) . '</p></div>';
	}
	if ( isset( $_GET['sb'] ) ) {
		echo '<div class="notice notice-success" role="status"><p>↩️ খসড়ায় ফেরত: ' . esc_html( function_exists( 'uturn_bn' ) ? uturn_bn( absint( $_GET['sb'] ) ) : absint( $_GET['sb'] ) ) . 'টি।</p></div>';
	}
	if ( isset( $_GET['staged'] ) ) {
		echo '<div class="notice notice-success" role="status"><p>✅ ' . esc_html( wp_unslash( $_GET['staged'] ) ) . '</p></div>';
	}
	if ( isset( $_GET['archdone'] ) ) {
		echo '<div class="notice notice-success" role="status"><p>🗄️ ' . esc_html( wp_unslash( $_GET['archdone'] ) ) . '</p></div>';
	}
	echo '<div class="utd-card noprint"><form method="get" action="' . esc_url( uturn_dash_url() ) . '"><input type="hidden" name="view" value="result-review">';
	echo 'পরীক্ষা <select name="exam" aria-label="পরীক্ষা নির্বাচন">';
	foreach ( $exams as $slug => $bn ) {
		echo '<option value="' . esc_attr( $slug ) . '"' . selected( $exam, $slug, false ) . '>' . esc_html( $bn ) . '</option>';
	}
	echo '</select> বছর <input type="text" name="year" aria-label="শিক্ষাবর্ষ" value="' . esc_attr( $year ) . '" style="width:7em"> শ্রেণি <select name="cls" aria-label="শ্রেণি নির্বাচন"><option value="">— সব —</option>';
	foreach ( $classes as $c ) {
		echo '<option' . selected( $cls, $c, false ) . '>' . esc_html( $c ) . '</option>';
	}
	echo '</select> ';
	$sec_all = '' !== $cls ? uturn_entry_sections( $cls ) : array();
	$sec = isset( $_GET['sec'] ) ? sanitize_text_field( wp_unslash( $_GET['sec'] ) ) : '';
	$stage_f = isset( $_GET['stage'] ) ? sanitize_key( $_GET['stage'] ) : '';
	$q = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
	if ( $sec_all ) {
		echo 'শাখা <select name="sec" aria-label="শাখা নির্বাচন"><option value="">— সব —</option>';
		foreach ( $sec_all as $snm ) {
			echo '<option' . selected( $sec, $snm, false ) . '>' . esc_html( $snm ) . '</option>';
		}
		echo '</select> ';
	}
	$stages = uturn_result_stages();
	echo 'ধাপ <select name="stage" aria-label="ধাপ নির্বাচন"><option value="">— সব —</option>';
	foreach ( $stages as $sk => $slbl ) {
		echo '<option value="' . esc_attr( $sk ) . '"' . selected( $stage_f, $sk, false ) . '>' . esc_html( $slbl ) . '</option>';
	}
	echo '</select> খুঁজুন <input type="search" name="q" value="' . esc_attr( $q ) . '" placeholder="নাম / রোল" style="width:9em" aria-label="নাম বা রোল দিয়ে খুঁজুন"> ';
	submit_button( 'ফলাফল দেখুন', 'secondary', '', false );
	echo '</form></div>';
	$yn = uturn_norm_num( $year );
	$mq = array( array( 'key' => '_ut_exam', 'value' => $exam ) );
	if ( '' !== $cls ) {
		$mq[] = array( 'key' => '_ut_class', 'value' => $cls );
	}
	$all = get_posts( array( 'post_type' => 'ut_result', 'posts_per_page' => -1, 'post_status' => array( 'pending', 'publish', 'draft' ), 'meta_query' => $mq ) );
	$queue = array();
	$pub_list = array();
	$arch_list = array();
	$draft_n = 0;
	$pass_n = 0;
	$gpa_sum = 0;
	$gpa_n = 0;
	foreach ( $all as $r ) {
		if ( uturn_norm_num( get_post_meta( $r->ID, '_ut_year', true ) ) !== $yn ) {
			continue;
		}
		$stage = uturn_result_stage( $r->ID );
		if ( 'publish' === $r->post_status || 'pending' === $r->post_status ) {
			if ( get_post_meta( $r->ID, '_ut_status', true ) === 'pass' ) {
				$pass_n++;
			}
			$g = get_post_meta( $r->ID, '_ut_gpa', true );
			if ( is_numeric( $g ) ) {
				$gpa_sum += (float) $g;
				$gpa_n++;
			}
		}
		if ( 'archived' === $stage ) {
			$arch_list[] = $r;
		} elseif ( 'publish' === $r->post_status ) {
			$pub_list[] = $r;
		} elseif ( 'pending' === $r->post_status ) {
			$queue[] = $r;
		} else {
			$draft_n++;
		}
	}
	/* Apply section + stage + search filters to the workflow lists. */
	$flt = function ( $r ) use ( $sec, $stage_f, $q ) {
		if ( '' !== $stage_f && uturn_result_stage( $r->ID ) !== $stage_f ) {
			return false;
		}
		if ( '' !== $sec && trim( (string) get_post_meta( $r->ID, '_ut_section', true ) ) !== $sec ) {
			return false;
		}
		if ( '' !== $q ) {
			$sid = (int) get_post_meta( $r->ID, '_ut_student_id', true );
			$name = $sid ? get_the_title( $sid ) : $r->post_title;
			if ( false === mb_stripos( $name, $q ) && false === mb_stripos( (string) get_post_meta( $r->ID, '_ut_roll', true ), $q ) ) {
				return false;
			}
		}
		return true;
	};
	$queue = array_values( array_filter( $queue, $flt ) );
	$pub_list = array_values( array_filter( $pub_list, $flt ) );
	$arch_list = array_values( array_filter( $arch_list, $flt ) );
	$bnf = function_exists( 'uturn_bn' ) ? 'uturn_bn' : 'strval';
	$stage_counts = array();
	foreach ( array_merge( $queue, $pub_list, $arch_list ) as $r ) {
		$sk = uturn_result_stage( $r->ID );
		$stage_counts[ $sk ] = isset( $stage_counts[ $sk ] ) ? $stage_counts[ $sk ] + 1 : 1;
	}
	$stage_bits = array();
	foreach ( $stages as $sk => $slbl ) {
		if ( ! empty( $stage_counts[ $sk ] ) ) {
			$stage_bits[] = $slbl . ' ' . call_user_func( $bnf, $stage_counts[ $sk ] );
		}
	}
	echo '<div class="utd-card"><p>📊 পর্যালোচনা-সারিতে: <b>' . esc_html( call_user_func( $bnf, count( $queue ) ) ) . '</b> · প্রকাশিত: <b>' . esc_html( call_user_func( $bnf, count( $pub_list ) ) ) . '</b> · খসড়া: <b>' . esc_html( call_user_func( $bnf, $draft_n ) ) . '</b> · আর্কাইভ: <b>' . esc_html( call_user_func( $bnf, count( $arch_list ) ) ) . '</b> · উত্তীর্ণ: <b>' . esc_html( call_user_func( $bnf, $pass_n ) ) . '</b>' . ( $gpa_n ? ' · গড় জিপিএ: <b>' . esc_html( number_format( $gpa_sum / $gpa_n, 2 ) ) . '</b>' : '' ) . '</p>' . ( $stage_bits ? '<p class="utd-muted">' . esc_html( implode( ' · ', $stage_bits ) ) . '</p>' : '' ) . '</div>';
	$audit_html = function ( $rid ) {
		$log = get_post_meta( (int) $rid, '_ut_audit', true );
		if ( ! is_array( $log ) || ! $log ) {
			return '<small class="utd-muted">—</small>';
		}
		$rows = array();
		foreach ( array_reverse( array_slice( $log, -8 ) ) as $e ) {
			$rows[] = esc_html( ( $e['t'] ?? '' ) . ' · ' . ( $e['u'] ?? '' ) . ' · ' . ( $e['a'] ?? '' ) . ( ! empty( $e['d'] ) ? ' — ' . $e['d'] : '' ) );
		}
		return '<details><summary>📜 ' . count( $log ) . 'টি</summary><div style="font-size:12px;max-width:340px">' . implode( '<br>', $rows ) . '</div></details>';
	};
	if ( ! $queue ) {
		echo '<div class="utd-card"><p class="utd-muted">পর্যালোচনার অপেক্ষায় কোনো ফলাফল নেই।</p></div>';
	} else {
		echo '<div class="utd-card"><h2>⏳ পর্যালোচনা-সারি (জমা / যাচাই / সংশোধন / অনুমোদিত)</h2>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="uturn_result_publish"><input type="hidden" name="ut_shell" value="1">';
		wp_nonce_field( 'uturn_result_publish' );
		echo '<input type="hidden" name="exam" value="' . esc_attr( $exam ) . '"><input type="hidden" name="year" aria-label="শিক্ষাবর্ষ" value="' . esc_attr( $year ) . '"><input type="hidden" name="cls" value="' . esc_attr( $cls ) . '">';
		echo '<div class="table-wrap"><table class="widefat striped" aria-label="পর্যালোচনা সারি"><thead><tr><th scope="col"><input type="checkbox" id="utPubAll" checked aria-label="সব নির্বাচন"></th><th scope="col">রোল</th><th scope="col">নাম</th><th scope="col">শ্রেণি</th><th scope="col">মোট</th><th scope="col">জিপিএ</th><th scope="col">ফল</th><th scope="col">ধাপ</th><th scope="col">ইতিহাস</th></tr></thead><tbody>';
		foreach ( $queue as $r ) {
			$g = function ( $k ) use ( $r ) { return get_post_meta( $r->ID, $k, true ); };
			$sid = (int) $g( '_ut_student_id' );
			$name = $sid ? get_the_title( $sid ) : preg_replace( '/\s—.*$/u', '', $r->post_title );
			$pass = $g( '_ut_status' ) !== 'fail';
			$stg = uturn_result_stage( $r->ID );
			$pill_cls = 'correction' === $stg ? 'red' : ( 'approved' === $stg ? 'green' : ( 'review' === $stg ? 'blue' : '' ) );
			echo '<tr><td><input type="checkbox" name="ids[]" value="' . (int) $r->ID . '" checked aria-label="' . esc_attr( 'নির্বাচন: ' . $name ) . '"></td><td><b>' . esc_html( $g( '_ut_roll' ) ) . '</b></td><td>' . esc_html( $name ) . '</td><td>' . esc_html( $g( '_ut_class' ) ) . '</td><td>' . esc_html( $g( '_ut_total' ) ) . '</td><td><b>' . esc_html( $g( '_ut_gpa' ) ) . '</b></td><td><span class="utd-pill ' . ( $pass ? 'green' : 'red' ) . '">' . ( $pass ? 'উত্তীর্ণ' : 'অনুত্তীর্ণ' ) . '</span></td><td><span class="utd-pill ' . $pill_cls . '">' . esc_html( $stages[ $stg ] ?? $stg ) . '</span></td><td>' . $audit_html( $r->ID ) . '</td></tr>';
		}
		echo '</tbody></table></div>';
		echo '<p><label><input type="checkbox" name="sms" value="1"> প্রকাশের সাথে SMS পাঠাও (অভিভাবকদের)</label> ';
		if ( 'annual' === $exam ) {
			echo '<label><input type="checkbox" name="promote" value="1" checked> উত্তীর্ণদের পরের শ্রেণিতে স্বয়ংক্রিয় উন্নীত করো (অনুত্তীর্ণ → Retained)</label>';
		}
		echo '</p><p><button class="button button-primary button-large" type="submit" onclick="return confirm(\'নির্বাচিত ফলাফল প্রকাশ করবেন? প্রকাশের সাথে সাথে পাবলিক দেখতে পাবে।\')">🚀 প্রকাশ করুন</button> ';
		echo '<button class="button" type="submit" formaction="' . esc_url( admin_url( 'admin-post.php?action=uturn_result_sendback&ut_shell=1' ) ) . '">↩️ খসড়ায় ফেরত পাঠান</button></p>';
		echo '<p>নির্বাচিতদের ধাপ বদলান <select name="stage_to" aria-label="নতুন ধাপ নির্বাচন"><option value="review">🔎 যাচাই চলছে</option><option value="correction">✏️ সংশোধন প্রয়োজন</option><option value="approved">✅ অনুমোদিত</option><option value="submitted">📤 জমা দেওয়া (ফেরত)</option></select> <input type="text" name="stage_note" aria-label="ধাপ পরিবর্তনের মন্তব্য" placeholder="মন্তব্য (ঐচ্ছিক)" style="width:16em"> <button class="button" type="submit" formaction="' . esc_url( admin_url( 'admin-post.php?action=uturn_result_stage&ut_shell=1' ) ) . '">ধাপ পরিবর্তন</button></p></form></div>';
	}
	if ( $pub_list ) {
		echo '<div class="utd-card"><h2>🌐 প্রকাশিত ফলাফল</h2>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php?action=uturn_result_archive&ut_shell=1' ) ) . '">';
		wp_nonce_field( 'uturn_result_publish' );
		echo '<input type="hidden" name="exam" value="' . esc_attr( $exam ) . '"><input type="hidden" name="year" aria-label="শিক্ষাবর্ষ" value="' . esc_attr( $year ) . '"><input type="hidden" name="cls" value="' . esc_attr( $cls ) . '"><input type="hidden" name="op" value="archive">';
		echo '<div class="table-wrap"><table class="widefat striped" aria-label="প্রকাশিত ফলাফল"><thead><tr><th scope="col"><input type="checkbox" id="utArchAll" aria-label="সব নির্বাচন"></th><th scope="col">রোল</th><th scope="col">নাম</th><th scope="col">শ্রেণি</th><th scope="col">মোট</th><th scope="col">জিপিএ</th><th scope="col">মেধা</th><th scope="col">ইতিহাস</th></tr></thead><tbody>';
		foreach ( $pub_list as $r ) {
			$g = function ( $k ) use ( $r ) { return get_post_meta( $r->ID, $k, true ); };
			$sid = (int) $g( '_ut_student_id' );
			$name = $sid ? get_the_title( $sid ) : preg_replace( '/\s—.*$/u', '', $r->post_title );
			echo '<tr><td><input type="checkbox" name="ids[]" value="' . (int) $r->ID . '" aria-label="' . esc_attr( 'নির্বাচন: ' . $name ) . '"></td><td><b>' . esc_html( $g( '_ut_roll' ) ) . '</b></td><td>' . esc_html( $name ) . '</td><td>' . esc_html( $g( '_ut_class' ) ) . '</td><td>' . esc_html( $g( '_ut_total' ) ) . '</td><td><b>' . esc_html( $g( '_ut_gpa' ) ) . '</b></td><td>' . esc_html( uturn_merit_label( $g( '_ut_merit' ) ) ) . '</td><td>' . $audit_html( $r->ID ) . '</td></tr>';
		}
		echo '</tbody></table></div>';
		echo '<p><button class="button" type="submit" onclick="return confirm(\'নির্বাচিত ফলাফল আর্কাইভ করবেন? পাবলিক থেকে সরে যাবে।\')">🗄️ আর্কাইভ করুন</button></p></form></div>';
	}
	if ( $arch_list ) {
		echo '<div class="utd-card"><h2>🗄️ আর্কাইভ</h2>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php?action=uturn_result_archive&ut_shell=1' ) ) . '">';
		wp_nonce_field( 'uturn_result_publish' );
		echo '<input type="hidden" name="exam" value="' . esc_attr( $exam ) . '"><input type="hidden" name="year" aria-label="শিক্ষাবর্ষ" value="' . esc_attr( $year ) . '"><input type="hidden" name="cls" value="' . esc_attr( $cls ) . '"><input type="hidden" name="op" value="republish">';
		echo '<div class="table-wrap"><table class="widefat striped" aria-label="আর্কাইভকৃত ফলাফল"><thead><tr><th scope="col"><input type="checkbox" id="utRepAll" aria-label="সব নির্বাচন"></th><th scope="col">রোল</th><th scope="col">নাম</th><th scope="col">শ্রেণি</th><th scope="col">জিপিএ</th><th scope="col">ইতিহাস</th></tr></thead><tbody>';
		foreach ( $arch_list as $r ) {
			$g = function ( $k ) use ( $r ) { return get_post_meta( $r->ID, $k, true ); };
			$sid = (int) $g( '_ut_student_id' );
			$name = $sid ? get_the_title( $sid ) : preg_replace( '/\s—.*$/u', '', $r->post_title );
			echo '<tr><td><input type="checkbox" name="ids[]" value="' . (int) $r->ID . '" aria-label="' . esc_attr( 'নির্বাচন: ' . $name ) . '"></td><td><b>' . esc_html( $g( '_ut_roll' ) ) . '</b></td><td>' . esc_html( $name ) . '</td><td>' . esc_html( $g( '_ut_class' ) ) . '</td><td><b>' . esc_html( $g( '_ut_gpa' ) ) . '</b></td><td>' . $audit_html( $r->ID ) . '</td></tr>';
		}
		echo '</tbody></table></div>';
		echo '<p><button class="button button-primary" type="submit">🌐 পুনঃপ্রকাশ করুন</button></p></form></div>';
	}
	/* Tabulation sheet (published + pending) with A4 print. */
	$sheet = array();
	foreach ( $all as $r ) {
		if ( uturn_norm_num( get_post_meta( $r->ID, '_ut_year', true ) ) !== $yn ) {
			continue;
		}
		if ( 'draft' !== $r->post_status ) {
			$sheet[] = $r;
		}
	}
	if ( $sheet && '' !== $cls ) {
		$subs = array();
		$tfulls = array();
		$st = uturn_exam_setup_get( $cls, $exam, $year );
		if ( $st ) {
			foreach ( $st as $sr ) {
				$tfulls[ $sr['subject'] ] = $sr['full'];
			}
		}
		foreach ( $sheet as $r ) {
			foreach ( array_keys( uturn_result_marks( $r->ID ) ) as $sn ) {
				$subs[ $sn ] = true;
			}
			foreach ( uturn_result_fulls( $r->ID ) as $sn => $fl ) {
				if ( ! isset( $tfulls[ $sn ] ) ) {
					$tfulls[ $sn ] = $fl;
				}
			}
		}
		$subs = array_keys( $subs );
		$bnf = function_exists( 'uturn_bn' ) ? 'uturn_bn' : 'strval';
		$school = function_exists( 'uturn_opt' ) ? uturn_opt( 'school_name_bn', get_bloginfo( 'name' ) ) : get_bloginfo( 'name' );
		$addr = function_exists( 'uturn_opt' ) ? uturn_opt( 'address', '' ) : '';
		$land_url = add_query_arg( array( 'ut_print' => 'tabulation', 'cls' => $cls, 'exam' => $exam, 'year' => $year ), home_url( '/' ) );
		echo '<div class="utd-card noprint"><button class="button button-primary" onclick="window.print()">🖨️ ট্যাবুলেশন শিট প্রিন্ট (A4)</button> <a class="button" target="_blank" rel="noopener" href="' . esc_url( $land_url ) . '">🖨️ ল্যান্ডস্কেপ প্রিন্ট (A4 আড়াআড়ি)</a></div>';
		echo '<div class="utd-card print-doc"><div class="print-head"><b>' . esc_html( $school ) . '</b><span>' . esc_html( $addr ) . '</span><i>ট্যাবুলেশন শিট — ' . esc_html( $exams[ $exam ] ) . ' ' . esc_html( $year ) . ' · শ্রেণি: ' . esc_html( $cls ) . '</i></div>';
		echo '<div class="table-wrap"><table class="widefat striped print-table" aria-label="ট্যাবুলেশন শিট"><thead><tr><th scope="col">রোল</th><th scope="col">নাম</th>';
		foreach ( $subs as $sn ) {
			$fl = isset( $tfulls[ $sn ] ) ? $tfulls[ $sn ] : 100;
			echo '<th scope="col">' . esc_html( $sn ) . '<br><small>পূর্ণ ' . esc_html( call_user_func( $bnf, (string) $fl ) ) . '</small></th>';
		}
		echo '<th scope="col">মোট</th><th scope="col">জিপিএ</th><th scope="col">মেধা</th><th scope="col">ফল</th></tr></thead><tbody>';
		foreach ( $sheet as $r ) {
			$g = function ( $k ) use ( $r ) { return get_post_meta( $r->ID, $k, true ); };
			$sid = (int) $g( '_ut_student_id' );
			$name = $sid ? get_the_title( $sid ) : preg_replace( '/\s—.*$/u', '', $r->post_title );
			$mk = uturn_result_marks( $r->ID );
			echo '<tr><td><b>' . esc_html( $g( '_ut_roll' ) ) . '</b></td><td>' . esc_html( $name ) . '</td>';
			foreach ( $subs as $sn ) {
				echo '<td>' . esc_html( isset( $mk[ $sn ] ) ? $mk[ $sn ] : '—' ) . '</td>';
			}
			echo '<td><b>' . esc_html( $g( '_ut_total' ) ) . '</b></td><td>' . esc_html( $g( '_ut_gpa' ) ) . '</td><td>' . esc_html( uturn_merit_label( $g( '_ut_merit' ) ) ) . '</td><td>' . ( $g( '_ut_status' ) !== 'fail' ? 'উত্তীর্ণ' : 'অনুত্তীর্ণ' ) . '</td></tr>';
		}
		echo '</tbody></table></div>';
		echo '<div class="print-sign"><span>শ্রেণি শিক্ষক</span><span>পরীক্ষা নিয়ন্ত্রক</span><span>প্রধান শিক্ষক</span></div></div>';
	}
	?>
	<script>
	(function(){[['utPubAll'],['utArchAll'],['utRepAll']].forEach(function(pair){var a=document.getElementById(pair[0]);if(a){a.addEventListener('change',function(){var f=a.closest('form');f.querySelectorAll('input[name="ids[]"]').forEach(function(c){c.checked=a.checked;});});}});})();
	</script>
	<?php
}

add_action( 'admin_post_uturn_result_publish', 'uturn_result_publish' );
function uturn_result_publish() {
	if ( function_exists( 'eduturn_license_require' ) ) {
		eduturn_license_require( 'erp' );
	}
	$exam = isset( $_POST['exam'] ) ? sanitize_key( $_POST['exam'] ) : '';
	$year = isset( $_POST['year'] ) ? sanitize_text_field( wp_unslash( $_POST['year'] ) ) : '';
	$cls  = isset( $_POST['cls'] ) ? sanitize_text_field( wp_unslash( $_POST['cls'] ) ) : '';
	$back = function_exists( 'uturn_dash_view_url' ) ? uturn_dash_view_url( 'result-review' ) : admin_url( 'admin.php?page=eduturn-result-import' );
	$back = add_query_arg( array( 'exam' => $exam, 'year' => $year, 'cls' => $cls ), $back );
	if ( ! check_admin_referer( 'uturn_result_publish' ) || ( ! current_user_can( 'publish_ut_results' ) && ! current_user_can( 'manage_options' ) ) ) {
		wp_die( 'Unauthorized.' );
	}
	$ids = isset( $_POST['ids'] ) && is_array( $_POST['ids'] ) ? array_map( 'absint', $_POST['ids'] ) : array();
	$do_sms = ! empty( $_POST['sms'] ) && function_exists( 'uturn_notify_result' );
	$do_promo = ! empty( $_POST['promote'] ) && 'annual' === $exam;
	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 300 );
	}
	$n = 0;
	$promo = 0;
	$sets = array();
	foreach ( $ids as $id ) {
		$p = get_post( $id );
		if ( ! $p || 'ut_result' !== $p->post_type || 'pending' !== $p->post_status ) {
			continue;
		}
		wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' ) );
		update_post_meta( $id, '_ut_stage', 'published' );
		uturn_result_audit( $id, 'stage', '→ published' );
		$n++;
		$sets[ get_post_meta( $id, '_ut_exam', true ) . '|' . uturn_norm_num( get_post_meta( $id, '_ut_year', true ) ) . '|' . get_post_meta( $id, '_ut_class', true ) ] = array( get_post_meta( $id, '_ut_exam', true ), get_post_meta( $id, '_ut_year', true ), get_post_meta( $id, '_ut_class', true ) );
		if ( $do_sms ) {
			uturn_notify_result( get_post_meta( $id, '_ut_exam_bn', true ), get_post_meta( $id, '_ut_year', true ), get_post_meta( $id, '_ut_class', true ), get_post_meta( $id, '_ut_roll', true ), get_post_meta( $id, '_ut_gpa', true ), get_post_meta( $id, '_ut_status', true ) );
		}
		if ( $do_promo ) {
			$sid = (int) get_post_meta( $id, '_ut_student_id', true );
			if ( $sid ) {
				if ( get_post_meta( $id, '_ut_status', true ) === 'fail' ) {
					/* Hold back: stays in class with Retained status. */
					if ( in_array( uturn_student_status( $sid ), array( 'active', 'promoted' ), true ) ) {
						uturn_student_set_status( $sid, 'retained' );
					}
				} else {
					$from = get_post_meta( $sid, '_ut_class', true );
					$next = uturn_next_class( $from );
					if ( '' !== $next ) {
						uturn_promote_student( $sid, $next );
						uturn_student_set_status( $sid, 'promoted' );
						$promo++;
					} elseif ( in_array( uturn_student_status( $sid ), array( 'active', 'promoted', 'retained' ), true ) ) {
						uturn_student_set_status( $sid, 'graduated' );
						$promo++;
					}
				}
			}
		}
	}
	foreach ( $sets as $s ) {
		uturn_recalc_merits( $s[0], $s[1], $s[2] );
	}
	wp_safe_redirect( add_query_arg( array( 'pub' => $n, 'promo' => $promo ), $back ) );
	exit;
}

add_action( 'admin_post_uturn_result_sendback', 'uturn_result_sendback' );
function uturn_result_sendback() {
	if ( function_exists( 'eduturn_license_require' ) ) {
		eduturn_license_require( 'erp' );
	}
	$exam = isset( $_POST['exam'] ) ? sanitize_key( $_POST['exam'] ) : '';
	$year = isset( $_POST['year'] ) ? sanitize_text_field( wp_unslash( $_POST['year'] ) ) : '';
	$cls  = isset( $_POST['cls'] ) ? sanitize_text_field( wp_unslash( $_POST['cls'] ) ) : '';
	$back = function_exists( 'uturn_dash_view_url' ) ? uturn_dash_view_url( 'result-review' ) : admin_url( 'admin.php?page=eduturn-result-import' );
	$back = add_query_arg( array( 'exam' => $exam, 'year' => $year, 'cls' => $cls ), $back );
	if ( ! check_admin_referer( 'uturn_result_publish' ) || ( ! current_user_can( 'publish_ut_results' ) && ! current_user_can( 'manage_options' ) ) ) {
		wp_die( 'Unauthorized.' );
	}
	$ids = isset( $_POST['ids'] ) && is_array( $_POST['ids'] ) ? array_map( 'absint', $_POST['ids'] ) : array();
	$n = 0;
	foreach ( $ids as $id ) {
		$p = get_post( $id );
		if ( ! $p || 'ut_result' !== $p->post_type || 'pending' !== $p->post_status ) {
			continue;
		}
		wp_update_post( array( 'ID' => $id, 'post_status' => 'draft' ) );
		update_post_meta( $id, '_ut_stage', 'draft' );
		uturn_result_audit( $id, 'stage', '→ draft (sendback)' );
		$n++;
	}
	wp_safe_redirect( add_query_arg( 'sb', $n, $back ) );
	exit;
}

/* ================= VIEW: promotion ================= */
function uturn_dash_promotion() {
	if ( function_exists( 'uturn_dash_erp_or_lock' ) && ! uturn_dash_erp_or_lock() ) {
		return;
	}
	$bnf = function_exists( 'uturn_bn' ) ? 'uturn_bn' : 'strval';
	if ( isset( $_GET['done'] ) ) {
		echo '<div class="notice notice-success" role="status"><p>✅ ' . esc_html( wp_unslash( $_GET['done'] ) ) . '</p></div>';
	}
	/* --- A. Auto-promote from annual results --- */
	$exams = function_exists( 'uturn_exams' ) ? uturn_exams( false ) : array();
	echo '<div class="utd-card"><h2>🤖 স্বয়ংক্রিয় উন্নীতকরণ (বার্ষিক ফলাফল থেকে)</h2>';
	echo '<p class="utd-muted">নির্বাচিত পরীক্ষার <b>প্রকাশিত</b> ফলে যারা উত্তীর্ণ, তারা পরের শ্রেণিতে যাবে (অবস্থা: উন্নীত); অনুত্তীর্ণরা একই শ্রেণিতে Retained থাকবে। সর্বোচ্চ শ্রেণি থেকে উত্তীর্ণরা Graduated হবে।</p>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" onsubmit="return confirm(\'স্বয়ংক্রিয় উন্নীতকরণ চালাবেন?\')"><input type="hidden" name="action" value="uturn_promote_run"><input type="hidden" name="ut_shell" value="1"><input type="hidden" name="kind" value="auto">';
	wp_nonce_field( 'uturn_promote_run' );
	echo 'পরীক্ষা <select name="exam" aria-label="পরীক্ষা নির্বাচন">';
	foreach ( $exams as $slug => $bn ) {
		echo '<option value="' . esc_attr( $slug ) . '"' . selected( 'annual', $slug, false ) . '>' . esc_html( $bn ) . '</option>';
	}
	echo '</select> বছর <input type="text" name="year" aria-label="শিক্ষাবর্ষ" value="' . esc_attr( function_exists( 'uturn_bn' ) ? uturn_bn( date( 'Y' ) ) : date( 'Y' ) ) . '" style="width:7em" required> ';
	submit_button( 'উন্নীতকরণ চালান', 'primary', '', false );
	echo '</form></div>';
	/* --- B. Manual move / TC --- */
	$classes = array();
	if ( function_exists( 'uturn_class_terms' ) ) {
		foreach ( uturn_class_terms() as $t ) {
			$classes[] = $t->name;
		}
	}
	$cls = isset( $_GET['cls'] ) ? sanitize_text_field( wp_unslash( $_GET['cls'] ) ) : ( $classes ? $classes[0] : '' );
	echo '<div class="utd-card"><h2>✋ ম্যানুয়াল শ্রেণি পরিবর্তন / বিদ্যালয় ত্যাগ</h2>';
	echo '<form method="get" action="' . esc_url( uturn_dash_url() ) . '"><input type="hidden" name="view" value="promotion">শ্রেণি <select name="cls" aria-label="শ্রেণি নির্বাচন">';
	foreach ( $classes as $c ) {
		echo '<option' . selected( $cls, $c, false ) . '>' . esc_html( $c ) . '</option>';
	}
	echo '</select> ';
	submit_button( 'তালিকা দেখুন', 'secondary', '', false );
	echo '</form></div>';
	if ( '' === $cls ) {
		return;
	}
	$posts = get_posts( array( 'post_type' => 'ut_student', 'posts_per_page' => -1, 'meta_key' => '_ut_class', 'meta_value' => $cls ) );
	if ( ! $posts ) {
		echo '<div class="utd-card"><p class="utd-muted">এই শ্রেণিতে শিক্ষার্থী নেই।</p></div>';
		return;
	}
	$sts = uturn_student_statuses();
	echo '<div class="utd-card"><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="uturn_promote_run"><input type="hidden" name="ut_shell" value="1"><input type="hidden" name="kind" value="manual"><input type="hidden" name="cls" value="' . esc_attr( $cls ) . '">';
	wp_nonce_field( 'uturn_promote_run' );
	echo '<div class="table-wrap"><table class="widefat striped" aria-label="শ্রেণি উন্নীতকরণ তালিকা"><thead><tr><th scope="col"><input type="checkbox" id="utProAll" aria-label="সব নির্বাচন"></th><th scope="col">রোল</th><th scope="col">নাম</th><th scope="col">অবস্থা</th></tr></thead><tbody>';
	foreach ( $posts as $sp ) {
		$st = uturn_student_status( $sp->ID );
		echo '<tr><td><input type="checkbox" name="sids[]" value="' . (int) $sp->ID . '" aria-label="' . esc_attr( 'নির্বাচন: ' . $sp->post_title ) . '"></td><td><b>' . esc_html( get_post_meta( $sp->ID, '_ut_roll', true ) ) . '</b></td><td>' . esc_html( $sp->post_title ) . '</td><td>' . esc_html( $sts[ $st ] ?? $st ) . '</td></tr>';
	}
	echo '</tbody></table></div>';
	echo '<p>নির্বাচিতদের <select name="target" aria-label="লক্ষ্য শ্রেণি নির্বাচন">';
	foreach ( uturn_class_ladder() as $c ) {
		echo '<option' . selected( $cls, $c, false ) . '>' . esc_html( $c ) . '</option>';
	}
	echo '</select> শ্রেণিতে <button class="button button-primary" type="submit" name="op" value="move">⬆️ স্থানান্তর / উন্নীত করুন</button></p>';
	echo '<p><button class="button" type="submit" name="op" value="retain">📌 একই শ্রেণিতে রাখুন (Retained)</button> ';
	echo '<button class="button" type="submit" name="op" value="transfer" onclick="return confirm(\'বদলি/TC চিহ্নিত করবেন?\')">🔀 বদলি / TC</button> ';
	echo '<button class="button" type="submit" name="op" value="graduate">🎓 Graduated</button> ';
	echo '<button class="button" type="submit" name="op" value="reactivate">♻️ পুনরায় সক্রিয়</button> ';
	echo '<button class="button button-link-delete" type="submit" name="op" value="left" onclick="return confirm(\'নির্বাচিত শিক্ষার্থীদের “বিদ্যালয় ত্যাগ” হিসেবে চিহ্নিত করবেন? তাদের আইডি/ইতিহাস মুছবে না, শুধু সক্রিয় তালিকা থেকে বাদ যাবে।\')">🚪 বিদ্যালয় ত্যাগ</button></p></form></div>';
	?>
	<script>
	(function(){var a=document.getElementById('utProAll');if(a){a.addEventListener('change',function(){document.querySelectorAll('input[name="sids[]"]').forEach(function(c){c.checked=a.checked;});});}})();
	</script>
	<?php
}

add_action( 'admin_post_uturn_promote_run', 'uturn_promote_run' );
function uturn_promote_run() {
	if ( function_exists( 'eduturn_license_require' ) ) {
		eduturn_license_require( 'erp' );
	}
	$back = function_exists( 'uturn_dash_view_url' ) ? uturn_dash_view_url( 'promotion' ) : admin_url();
	if ( ! check_admin_referer( 'uturn_promote_run' ) || ( ! current_user_can( 'uturn_manage_academic' ) && ! current_user_can( 'manage_options' ) ) ) {
		wp_die( 'Unauthorized.' );
	}
	$kind = isset( $_POST['kind'] ) ? sanitize_key( $_POST['kind'] ) : '';
	if ( 'auto' === $kind ) {
		$exam = isset( $_POST['exam'] ) ? sanitize_key( $_POST['exam'] ) : 'annual';
		$year = isset( $_POST['year'] ) ? sanitize_text_field( wp_unslash( $_POST['year'] ) ) : '';
		$yn = uturn_norm_num( $year );
		$all = get_posts( array( 'post_type' => 'ut_result', 'posts_per_page' => -1, 'post_status' => 'publish', 'meta_query' => array( array( 'key' => '_ut_exam', 'value' => $exam ) ) ) );
		$up = 0;
		$stay = 0;
		$done_ids = array();
		foreach ( $all as $r ) {
			if ( uturn_norm_num( get_post_meta( $r->ID, '_ut_year', true ) ) !== $yn ) {
				continue;
			}
			$sid = (int) get_post_meta( $r->ID, '_ut_student_id', true );
			if ( ! $sid || isset( $done_ids[ $sid ] ) ) {
				continue;
			}
			$done_ids[ $sid ] = true;
			if ( get_post_meta( $r->ID, '_ut_status', true ) === 'fail' ) {
				if ( in_array( uturn_student_status( $sid ), array( 'active', 'promoted' ), true ) ) {
					uturn_student_set_status( $sid, 'retained' );
				}
				$stay++;
				continue;
			}
			$from = get_post_meta( $sid, '_ut_class', true );
			/* Promote only if still in the result's class (idempotent re-runs). */
			if ( $from !== get_post_meta( $r->ID, '_ut_class', true ) ) {
				continue;
			}
			$next = uturn_next_class( $from );
			if ( '' !== $next ) {
				uturn_promote_student( $sid, $next );
				uturn_student_set_status( $sid, 'promoted' );
				$up++;
			} elseif ( in_array( uturn_student_status( $sid ), array( 'active', 'promoted', 'retained' ), true ) ) {
				uturn_student_set_status( $sid, 'graduated' );
				$up++;
			}
		}
		wp_safe_redirect( add_query_arg( 'done', rawurlencode( "উন্নীত: {$up} জন · একই শ্রেণিতে থাকল: {$stay} জন।" ), $back ) );
		exit;
	}
	if ( 'manual' === $kind ) {
		$sids = isset( $_POST['sids'] ) && is_array( $_POST['sids'] ) ? array_map( 'absint', $_POST['sids'] ) : array();
		$op = isset( $_POST['op'] ) ? sanitize_key( $_POST['op'] ) : '';
		$target = isset( $_POST['target'] ) ? sanitize_text_field( wp_unslash( $_POST['target'] ) ) : '';
		$cls = isset( $_POST['cls'] ) ? sanitize_text_field( wp_unslash( $_POST['cls'] ) ) : '';
		$back = add_query_arg( 'cls', $cls, $back );
		$n = 0;
		foreach ( $sids as $sid ) {
			if ( 'left' === $op ) {
				if ( uturn_student_set_status( $sid, 'left' ) ) {
					$n++;
				}
			} elseif ( 'move' === $op && '' !== $target ) {
				if ( false !== uturn_promote_student( $sid, $target ) ) {
					uturn_student_set_status( $sid, 'promoted' );
					$n++;
				}
			} elseif ( in_array( $op, array( 'retain', 'transfer', 'graduate', 'reactivate' ), true ) ) {
				$map = array( 'retain' => 'retained', 'transfer' => 'transferred', 'graduate' => 'graduated', 'reactivate' => 'active' );
				if ( uturn_student_set_status( $sid, $map[ $op ] ) ) {
					$n++;
				}
			}
		}
		$msgs = array(
			'left' => "বিদ্যালয় ত্যাগ চিহ্নিত: {$n} জন।", 'move' => "শ্রেণি পরিবর্তন: {$n} জন → {$target}।",
			'retain' => "Retained: {$n} জন।", 'transfer' => "বদলি/TC: {$n} জন।",
			'graduate' => "Graduated: {$n} জন।", 'reactivate' => "পুনরায় সক্রিয়: {$n} জন।",
		);
		$msg = isset( $msgs[ $op ] ) ? $msgs[ $op ] : "সম্পন্ন: {$n} জন।";
		wp_safe_redirect( add_query_arg( 'done', rawurlencode( $msg ), $back ) );
		exit;
	}
	wp_safe_redirect( $back );
	exit;
}

/* ================= entry helpers: scoping + sections ================= */
/** Subjects (exact names) the current teacher may enter marks for.
 *  Empty array = full grid access (staff / class-teacher).
 */
function uturn_entry_scoped_subjects( $cls = '' ) {
	if ( current_user_can( 'publish_ut_results' ) || current_user_can( 'manage_options' ) ) {
		return array();
	}
	if ( ! function_exists( 'uturn_class_term_by_name' ) ) {
		return array();
	}
	$t = uturn_class_term_by_name( $cls );
	if ( ! $t ) {
		return array();
	}
	$uid = get_current_user_id();
	if ( function_exists( 'uturn_class_cteacher' ) && (int) uturn_class_cteacher( $t->term_id ) === $uid ) {
		return array();
	}
	$mine = array();
	if ( function_exists( 'uturn_class_subjects' ) ) {
		foreach ( uturn_class_subjects( $t->term_id ) as $row ) {
			if ( isset( $row['teacher'] ) && (int) $row['teacher'] === $uid && ! empty( $row['subject'] ) ) {
				$mine[] = $row['subject'];
			}
		}
	}
	return array_values( array_unique( $mine ) );
}

/** Distinct non-empty sections on the class roster. */
function uturn_entry_sections( $cls = '' ) {
	$out = array();
	foreach ( uturn_entry_students( $cls ) as $sp ) {
		$v = trim( (string) get_post_meta( $sp->ID, '_ut_section', true ) );
		if ( '' !== $v ) {
			$out[ $v ] = true;
		}
	}
	return array_keys( $out );
}

/* ================= result workflow (7 stages) + audit trail ================= */
function uturn_result_stages() {
	return array(
		'draft'      => '📝 খসড়া',
		'submitted'  => '📤 জমা দেওয়া',
		'review'     => '🔎 যাচাই চলছে',
		'correction' => '✏️ সংশোধন প্রয়োজন',
		'approved'   => '✅ অনুমোদিত',
		'published'  => '🌐 প্রকাশিত',
		'archived'   => '🗄️ আর্কাইভ',
	);
}

/** Allowed stage transitions (publish/archive go through dedicated handlers). */
function uturn_result_transitions() {
	return array(
		'submitted'  => array( 'review', 'correction', 'approved' ),
		'review'     => array( 'correction', 'approved', 'submitted' ),
		'correction' => array( 'submitted' ),
		'approved'   => array( 'review', 'submitted' ),
	);
}

function uturn_result_stage( $rid ) {
	$st = get_post_meta( (int) $rid, '_ut_stage', true );
	if ( '' !== $st ) {
		return $st;
	}
	$p = get_post( (int) $rid );
	if ( ! $p ) {
		return 'draft';
	}
	if ( 'publish' === $p->post_status ) {
		return 'published';
	}
	return 'draft' === $p->post_status ? 'draft' : 'submitted';
}

/** Move between review stages (queue stages always stay post_status=pending). */
function uturn_result_set_stage( $rid, $to, $note = '' ) {
	$rid = (int) $rid;
	$from = uturn_result_stage( $rid );
	$allowed = uturn_result_transitions();
	if ( $from === $to || ! isset( $allowed[ $from ] ) || ! in_array( $to, $allowed[ $from ], true ) ) {
		return false;
	}
	update_post_meta( $rid, '_ut_stage', $to );
	wp_update_post( array( 'ID' => $rid, 'post_status' => 'pending' ) );
	uturn_result_audit( $rid, 'stage', $from . ' → ' . $to . ( '' !== $note ? ' — ' . $note : '' ) );
	return true;
}

/** Append an audit entry to a result (capped at 50 rows). */
function uturn_result_audit( $rid, $action, $detail = '' ) {
	$log = get_post_meta( (int) $rid, '_ut_audit', true );
	if ( ! is_array( $log ) ) {
		$log = array();
	}
	$u = wp_get_current_user();
	$log[] = array(
		't' => current_time( 'mysql' ),
		'u' => $u && $u->ID ? $u->display_name . ' (#' . $u->ID . ')' : 'system',
		'a' => (string) $action,
		'd' => (string) $detail,
	);
	if ( count( $log ) > 50 ) {
		$log = array_slice( $log, -50 );
	}
	update_post_meta( (int) $rid, '_ut_audit', $log );
}

function uturn_can_review_results() {
	return current_user_can( 'publish_ut_results' ) || current_user_can( 'manage_options' );
}

/** Bulk stage-change handler (reviewers). */
add_action( 'admin_post_uturn_result_stage', 'uturn_result_stage_handle' );
function uturn_result_stage_handle() {
	if ( function_exists( 'eduturn_license_require' ) ) {
		eduturn_license_require( 'erp' );
	}
	$exam = isset( $_POST['exam'] ) ? sanitize_key( $_POST['exam'] ) : '';
	$year = isset( $_POST['year'] ) ? sanitize_text_field( wp_unslash( $_POST['year'] ) ) : '';
	$cls  = isset( $_POST['cls'] ) ? sanitize_text_field( wp_unslash( $_POST['cls'] ) ) : '';
	$back = function_exists( 'uturn_dash_view_url' ) ? uturn_dash_view_url( 'result-review' ) : admin_url();
	$back = add_query_arg( array( 'exam' => $exam, 'year' => $year, 'cls' => $cls ), $back );
	if ( ! check_admin_referer( 'uturn_result_publish' ) || ! uturn_can_review_results() ) {
		wp_die( 'Unauthorized.' );
	}
	$ids = isset( $_POST['ids'] ) && is_array( $_POST['ids'] ) ? array_map( 'absint', $_POST['ids'] ) : array();
	$to = isset( $_POST['stage_to'] ) ? sanitize_key( $_POST['stage_to'] ) : '';
	$note = isset( $_POST['stage_note'] ) ? sanitize_text_field( wp_unslash( $_POST['stage_note'] ) ) : '';
	$stages = uturn_result_stages();
	if ( ! isset( $stages[ $to ] ) ) {
		wp_safe_redirect( $back );
		exit;
	}
	$n = 0;
	foreach ( $ids as $rid ) {
		if ( $rid && uturn_result_set_stage( $rid, $to, $note ) ) {
			$n++;
		}
	}
	wp_safe_redirect( add_query_arg( 'staged', rawurlencode( $n . 'টি → ' . $stages[ $to ] ), $back ) );
	exit;
}

/** Archive / republish handler (reviewers). */
add_action( 'admin_post_uturn_result_archive', 'uturn_result_archive_handle' );
function uturn_result_archive_handle() {
	if ( function_exists( 'eduturn_license_require' ) ) {
		eduturn_license_require( 'erp' );
	}
	$exam = isset( $_POST['exam'] ) ? sanitize_key( $_POST['exam'] ) : '';
	$year = isset( $_POST['year'] ) ? sanitize_text_field( wp_unslash( $_POST['year'] ) ) : '';
	$cls  = isset( $_POST['cls'] ) ? sanitize_text_field( wp_unslash( $_POST['cls'] ) ) : '';
	$back = function_exists( 'uturn_dash_view_url' ) ? uturn_dash_view_url( 'result-review' ) : admin_url();
	$back = add_query_arg( array( 'exam' => $exam, 'year' => $year, 'cls' => $cls ), $back );
	if ( ! check_admin_referer( 'uturn_result_publish' ) || ! uturn_can_review_results() ) {
		wp_die( 'Unauthorized.' );
	}
	$ids = isset( $_POST['ids'] ) && is_array( $_POST['ids'] ) ? array_map( 'absint', $_POST['ids'] ) : array();
	$op = isset( $_POST['op'] ) ? sanitize_key( $_POST['op'] ) : '';
	$n = 0;
	$sets = array();
	foreach ( $ids as $rid ) {
		$p = get_post( $rid );
		if ( ! $p || 'ut_result' !== $p->post_type ) {
			continue;
		}
		$ex = get_post_meta( $rid, '_ut_exam', true );
		$yr = get_post_meta( $rid, '_ut_year', true );
		$cl = get_post_meta( $rid, '_ut_class', true );
		if ( 'archive' === $op && 'publish' === $p->post_status ) {
			wp_update_post( array( 'ID' => $rid, 'post_status' => 'draft' ) );
			update_post_meta( $rid, '_ut_stage', 'archived' );
			update_post_meta( $rid, '_ut_merit', '' );
			uturn_result_audit( $rid, 'stage', 'published → archived' );
			$sets[ $ex . '|' . uturn_norm_num( $yr ) . '|' . $cl ] = array( $ex, $yr, $cl );
			$n++;
		} elseif ( 'republish' === $op && 'archived' === uturn_result_stage( $rid ) ) {
			wp_update_post( array( 'ID' => $rid, 'post_status' => 'publish' ) );
			update_post_meta( $rid, '_ut_stage', 'published' );
			uturn_result_audit( $rid, 'stage', 'archived → published' );
			$sets[ $ex . '|' . uturn_norm_num( $yr ) . '|' . $cl ] = array( $ex, $yr, $cl );
			$n++;
		}
	}
	foreach ( $sets as $s ) {
		uturn_recalc_merits( $s[0], $s[1], $s[2] );
	}
	$msg = 'archive' === $op ? "আর্কাইভ: {$n}টি (পাবলিক থেকে সরানো হয়েছে)।" : "পুনঃপ্রকাশ: {$n}টি।";
	wp_safe_redirect( add_query_arg( 'archdone', rawurlencode( $msg ), $back ) );
	exit;
}
