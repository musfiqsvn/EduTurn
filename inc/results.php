<?php
/**
 * EduTurn — Results engine: ut_result CPT + CSV import admin page
 * + frontend lookup dataset helper. Zero plugins.
 */

defined( 'ABSPATH' ) || exit;

function uturn_exams_seed() {
	return array(
		array( 'slug' => 'half-yearly', 'name' => 'অর্ধ-বার্ষিক পরীক্ষা', 'active' => 1 ),
		array( 'slug' => 'annual', 'name' => 'বার্ষিক পরীক্ষা', 'active' => 1 ),
		array( 'slug' => 'first-term', 'name' => '১ম সাময়িক পরীক্ষা', 'active' => 1 ),
		array( 'slug' => 'test', 'name' => 'নির্বাচনী পরীক্ষা', 'active' => 1 ),
	);
}
/** All exam rows (school-configurable via the Exams view). */
function uturn_exam_rows() {
	$rows = get_option( 'uturn_exams', null );
	if ( null === $rows ) {
		$rows = uturn_exams_seed();
		update_option( 'uturn_exams', $rows, false );
	}
	$out = array();
	foreach ( (array) $rows as $r ) {
		if ( ! is_array( $r ) || empty( $r['slug'] ) || empty( $r['name'] ) ) {
			continue;
		}
		$out[] = array( 'slug' => sanitize_key( $r['slug'] ), 'name' => (string) $r['name'], 'active' => empty( $r['active'] ) ? 0 : 1 );
	}
	return $out;
}
/**
 * slug => name map. Active-only by default (for NEW entry dropdowns);
 * pass false when validating EXISTING data (old results stay viewable
 * even after their exam is retired).
 */
function uturn_exams( $active_only = true ) {
	$out = array();
	foreach ( uturn_exam_rows() as $r ) {
		if ( $active_only && ! $r['active'] ) {
			continue;
		}
		$out[ $r['slug'] ] = $r['name'];
	}
	if ( ! $out ) {
		$out = array( 'annual' => 'বার্ষিক পরীক্ষা' );
	}
	return $out;
}
/** How many results reference one exam slug (any status). */
function uturn_exam_usage( $slug ) {
	$q = new WP_Query(
		array(
			'post_type' => 'ut_result', 'posts_per_page' => 1, 'fields' => 'ids',
			'post_status' => 'any', 'meta_key' => '_ut_exam', 'meta_value' => $slug,
		)
	);
	return (int) $q->found_posts;
}

function uturn_result_classes() {
	return array( 'প্লে', 'নার্সারি', 'কেজি', '১ম', '২য়', '৩য়', '৪র্থ', '৫ম', '৬ষ্ঠ', '৭ম', '৮ম', '৯ম', '১০ম' );
}

/* ---------- Admin: import page ---------- */
add_action( 'admin_menu', 'uturn_results_menu', 30 );
function uturn_results_menu() {
	add_submenu_page( 'eduturn', 'ফলাফল ইমপোর্ট', 'ফলাফল ইমপোর্ট', 'publish_ut_results', 'eduturn-result-import', 'uturn_result_import_page' );
}

function uturn_result_import_page() {
	eduturn_license_require( 'erp' );
	if ( ! current_user_can( 'publish_ut_results' ) ) {
		wp_die( 'অনুমতি নেই।' );
	}
	$msg = '';
	if ( isset( $_GET['imported'] ) ) {
		$msg = '<div class="notice notice-success" role="status"><p>ইমপোর্ট সম্পন্ন: ' . absint( $_GET['imported'] ) . 'টি নতুন, ' . absint( isset( $_GET['updated'] ) ? $_GET['updated'] : 0 ) . 'টি আপডেট, ' . absint( isset( $_GET['skipped'] ) ? $_GET['skipped'] : 0 ) . 'টি বাদ।';
		if ( isset( $_GET['sms_sent'] ) && ( absint( $_GET['sms_sent'] ) + absint( isset( $_GET['sms_skip'] ) ? $_GET['sms_skip'] : 0 ) > 0 ) ) {
			$msg .= ' SMS: ' . absint( $_GET['sms_sent'] ) . 'টি পাঠানো, ' . absint( $_GET['sms_skip'] ) . 'টি বাদ।';
		}
		$msg .= '</p></div>';
	}
	echo '<div class="wrap"><h1>ফলাফল ইমপোর্ট (CSV)</h1>' . $msg
		. '<p>কলাম ক্রম: <code>exam, year, class, roll, reg, name, gpa, status, subjects</code> — যেখানে <code>exam</code> ∈ ' . esc_html( implode( ' / ', array_keys( uturn_exams( false ) ) ) ) . ', <code>status</code> ∈ pass / fail, <code>subjects</code> = <code>বাংলা|82|A+;ইংরেজি|78|A</code> ফরম্যাটে। একই exam+year+class+roll থাকলে আপডেট হবে।</p>'
		. '<p><a class="button" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=uturn_result_sample' ), 'uturn_result_sample' ) ) . '">নমুনা CSV ডাউনলোড</a></p>'
		. '<form method="post" enctype="multipart/form-data" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">'
		. '<input type="hidden" name="action" value="uturn_result_import">' . wp_nonce_field( 'uturn_result_import', '_wpnonce', true, false )
		. '<p><label><input type="checkbox" name="result_sms" value="1"' . checked( function_exists( 'uturn_opt' ) ? uturn_opt( 'sms_result', '0' ) : '0', '1', false ) . '> ফলাফল SMS-ও পাঠাও (অভিভাবকদের)</label></p>'
		. '<p><input type="file" name="result_csv" accept=".csv" required> <button class="button button-primary">ইমপোর্ট করুন</button></p></form></div>';
}

add_action( 'admin_post_uturn_result_sample', 'uturn_result_sample' );
function uturn_result_sample() {
	eduturn_license_require( 'erp' );
	if ( ! check_admin_referer( 'uturn_result_sample' ) || ! current_user_can( 'publish_ut_results' ) ) {
		wp_die( 'অনুমতি নেই।' );
	}
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=result-sample.csv' );
	echo "\xEF\xBB\xBF";
	$out = fopen( 'php://output', 'w' );
	fputcsv( $out, array( 'exam', 'year', 'class', 'roll', 'reg', 'name', 'gpa', 'status', 'subjects' ), ',', '"', '' );
	fputcsv( $out, array( 'half-yearly', '২০২৬', 'দশম', '১০১', 'REG-2026-0101', 'ডেমো শিক্ষার্থী (ক)', '৪.৮৮', 'pass', 'বাংলা|82|A+;ইংরেজি|78|A;গণিত|95|A+' ), ',', '"', '' );
	fclose( $out );
	exit;
}

add_action( 'admin_post_uturn_result_import', 'uturn_result_import' );
function uturn_result_import() {
	eduturn_license_require( 'erp' );
	if ( ! check_admin_referer( 'uturn_result_import' ) || ! current_user_can( 'publish_ut_results' ) ) {
		wp_die( 'অনুমতি নেই।' );
	}
	$new = 0;
	$upd = 0;
	$skip = 0;
	if ( isset( $_FILES['result_csv'] ) && UPLOAD_ERR_OK === $_FILES['result_csv']['error'] ) {
		$fh = fopen( $_FILES['result_csv']['tmp_name'], 'r' );
		if ( $fh ) {
			$first = true;
			while ( ( $row = fgetcsv( $fh, 0, ',', '"', '' ) ) !== false ) {
				if ( $first ) {
					$first = false;
					if ( isset( $row[0] ) && 'exam' === strtolower( trim( $row[0], "\xEF\xBB\xBF \t" ) ) ) {
						continue; // header row
					}
				}
				$row = array_map( 'trim', $row );
				if ( count( $row ) < 9 || '' === $row[3] || '' === $row[5] ) {
					$skip++;
					continue;
				}
				list( $exam, $year, $class, $roll, $reg, $name, $gpa, $status, $subs ) = $row;
				$exams = uturn_exams( false );
				if ( ! isset( $exams[ $exam ] ) ) {
					$skip++;
					continue;
				}
				$status = 'fail' === strtolower( $status ) ? 'fail' : 'pass';
				$sub_lines = array();
				foreach ( explode( ';', $subs ) as $chunk ) {
					$parts = array_map( 'trim', explode( '|', $chunk ) );
					if ( count( $parts ) >= 3 && '' !== $parts[0] ) {
						$sub_lines[] = $parts[0] . '|' . $parts[1] . '|' . $parts[2];
					}
				}
				$link_sid = 0;
			$match = get_posts( array( 'post_type' => 'ut_student', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_query' => array( array( 'key' => '_ut_class', 'value' => $class ), array( 'key' => '_ut_roll', 'value' => $roll ) ) ) );
			if ( $match ) { $link_sid = (int) $match[0]; }
			$found = array();
			if ( $link_sid && function_exists( 'uturn_find_result' ) ) {
				/* Same duplicate rule as the graphical grid (student-linked). */
				$fr = uturn_find_result( $link_sid, $exam, $year );
				if ( $fr ) { $found = array( $fr->ID ); }
			}
			if ( ! $found ) {
				$found = get_posts(
					array(
						'post_type' => 'ut_result', 'posts_per_page' => 1, 'fields' => 'ids',
						'meta_query' => array(
							array( 'key' => '_ut_exam', 'value' => $exam ),
							array( 'key' => '_ut_year', 'value' => $year ),
							array( 'key' => '_ut_class', 'value' => $class ),
							array( 'key' => '_ut_roll', 'value' => $roll ),
						),
					)
				);
			}
			$notify_rows[] = array( $exams[ $exam ], $year, $class, $roll, $gpa, $status );
			$merit_sets[ $exam . '|' . $class . '|' . ( function_exists( 'uturn_norm_num' ) ? uturn_norm_num( $year ) : $year ) ] = array( $exam, $year, $class );
			$imp_total = 0;
			foreach ( $sub_lines as $sl ) {
				$pp = explode( '|', $sl );
				if ( isset( $pp[1] ) && is_numeric( $pp[1] ) ) { $imp_total += (float) $pp[1]; }
			}
			$meta = array(
					'_ut_exam' => $exam, '_ut_exam_bn' => $exams[ $exam ], '_ut_year' => $year,
					'_ut_student_id' => $link_sid,
					'_ut_student_code' => ( $link_sid && function_exists( 'uturn_student_code' ) ) ? uturn_student_code( $link_sid ) : '',
					'_ut_section' => $link_sid ? get_post_meta( $link_sid, '_ut_section', true ) : '',
					'_ut_stage' => 'published',
					'_ut_class' => $class, '_ut_roll' => $roll, '_ut_reg' => $reg,
					'_ut_total' => $imp_total,
					'_ut_gpa' => $gpa, '_ut_status' => $status, '_ut_subjects' => implode( "\n", $sub_lines ),
				);
				if ( $found ) {
					wp_update_post( array( 'ID' => $found[0], 'post_title' => wp_slash( $name . ' — ' . $exams[ $exam ] . ' ' . $year ) ) );
					foreach ( $meta as $k => $v ) {
						update_post_meta( $found[0], $k, $v );
					}
					if ( function_exists( 'uturn_result_audit' ) ) { uturn_result_audit( $found[0], 'csv_import', 'update' ); }
					$upd++;
				} else {
					$id = wp_insert_post( array( 'post_title' => wp_slash( $name . ' — ' . $exams[ $exam ] . ' ' . $year ), 'post_type' => 'ut_result', 'post_status' => 'publish' ) );
					if ( $id && ! is_wp_error( $id ) ) {
						foreach ( $meta as $k => $v ) {
							update_post_meta( $id, $k, $v );
						}
						if ( function_exists( 'uturn_result_audit' ) ) { uturn_result_audit( $id, 'csv_import', 'new' ); }
						$new++;
					} else {
						$skip++;
					}
				}
			}
			fclose( $fh );
		}
	}
	if ( ! empty( $merit_sets ) && function_exists( 'uturn_recalc_merits' ) ) {
		foreach ( $merit_sets as $ms ) { uturn_recalc_merits( $ms[0], $ms[1], $ms[2] ); }
	}
	$sms_sent = 0;
	$sms_skip = 0;
	if ( ! empty( $_POST['result_sms'] ) && function_exists( 'uturn_notify_result' ) ) {
		if ( function_exists( 'set_time_limit' ) ) {
			@set_time_limit( 300 );
		}
		foreach ( (array) ( isset( $notify_rows ) ? $notify_rows : array() ) as $nr ) {
			list( $ok, ) = uturn_notify_result( $nr[0], $nr[1], $nr[2], $nr[3], $nr[4], $nr[5] );
			if ( $ok ) {
				$sms_sent++;
			} else {
				$sms_skip++;
			}
		}
	}
	wp_safe_redirect( uturn_back( admin_url( 'admin.php?page=eduturn-result-import&imported=' . $new . '&updated=' . $upd . '&skipped=' . $skip . '&sms_sent=' . $sms_sent . '&sms_skip=' . $sms_skip ) ) );
	exit;
}

/* ---------- Frontend dataset ---------- */
function uturn_results_dataset() {
	$rows = array();
	$posts = get_posts( array( 'post_type' => 'ut_result', 'posts_per_page' => -1, 'orderby' => 'ID', 'order' => 'ASC' ) );
	foreach ( $posts as $p ) {
		$g = function ( $k ) use ( $p ) {
			return get_post_meta( $p->ID, $k, true );
		};
		$subs = array();
		foreach ( uturn_lines( $g( '_ut_subjects' ) ) as $ln ) {
			$parts = array_map( 'trim', explode( '|', $ln ) );
			if ( count( $parts ) >= 3 ) {
				$subs[] = array( $parts[0], $parts[1], $parts[2], isset( $parts[3] ) ? $parts[3] : '', uturn_grade_point( $parts[2] ) );
			}
		}
		$rows[] = array(
			'exam' => $g( '_ut_exam' ), 'examBn' => $g( '_ut_exam_bn' ), 'year' => $g( '_ut_year' ),
			'cls' => $g( '_ut_class' ), 'roll' => $g( '_ut_roll' ), 'reg' => $g( '_ut_reg' ),
			'name' => preg_replace( '/\s—.*$/u', '', $p->post_title ), 'subjects' => $subs,
			'gpa' => $g( '_ut_gpa' ), 'status' => $g( '_ut_status' ) ? $g( '_ut_status' ) : 'pass',
		);
	}
	return $rows;
}

/** Grade point for a letter grade via the active scale (individual-result GP column). */
function uturn_grade_point( $grade ) {
	$grade = strtoupper( trim( (string) $grade ) );
	if ( function_exists( 'uturn_grade_scale' ) ) {
		foreach ( uturn_grade_scale() as $r ) {
			if ( strtoupper( (string) ( $r['grade'] ?? '' ) ) === $grade ) {
				return (string) ( $r['point'] ?? '' );
			}
		}
	}
	return '';
}
/** Student photo URL by result post (code-linked ut_student). */
function uturn_result_photo_url( $result_id ) {
	$code = get_post_meta( (int) $result_id, '_ut_student_code', true );
	if ( '' === $code ) {
		return '';
	}
	$sp = get_posts( array( 'post_type' => 'ut_student', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_ut_student_code', 'meta_value' => $code ) );
	if ( ! $sp || ! function_exists( 'uturn_photo_url' ) ) {
		return '';
	}
	return (string) uturn_photo_url( (int) $sp[0] );
}
/* ---------- Frontend: secure single-result lookup (no bulk dataset in page source) ---------- */
function uturn_result_years() {
	global $wpdb;
	$years = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE pm.meta_key = %s AND p.post_type = %s AND p.post_status = %s ORDER BY pm.meta_value DESC",
			'_ut_year', 'ut_result', 'publish'
		)
	);
	$years = array_values( array_filter( array_map( 'trim', (array) $years ) ) );
	if ( ! $years ) {
		$years = array( '২০২৬', '২০২৫' );
	}
	return $years;
}

/* Siblings = same student's other published results in the same year. */
function uturn_result_siblings( $p ) {
	$sid  = (int) get_post_meta( $p->ID, '_ut_student_id', true );
	$year = get_post_meta( $p->ID, '_ut_year', true );
	/* Raw values: siblings share identical stored formatting (BN digits included). */
	$mq = array( array( 'key' => '_ut_year', 'value' => $year ) );
	if ( $sid ) {
		$mq[] = array( 'key' => '_ut_student_id', 'value' => $sid );
	} else {
		$mq[] = array( 'key' => '_ut_class', 'value' => get_post_meta( $p->ID, '_ut_class', true ) );
		$mq[] = array( 'key' => '_ut_roll', 'value' => get_post_meta( $p->ID, '_ut_roll', true ) );
	}
	$rows = get_posts( array( 'post_type' => 'ut_result', 'posts_per_page' => 20, 'post_status' => 'publish', 'meta_query' => $mq ) );
	return $rows ? $rows : array( $p );
}

/** Cumulative GPA across a student's published results (same list passed in). */
function uturn_cumulative_gpa( $posts ) {
	$sum = 0.0; $n = 0; $fails = 0; $total = 0.0; $has_total = false;
	foreach ( (array) $posts as $p ) {
		$id = is_object( $p ) ? $p->ID : (int) $p;
		if ( ! $id ) { continue; }
		$raw = get_post_meta( $id, '_ut_gpa', true );
		$raw = function_exists( 'uturn_norm_num' ) ? uturn_norm_num( $raw ) : trim( (string) $raw );
		if ( is_numeric( $raw ) ) { $sum += (float) $raw; $n++; }
		if ( get_post_meta( $id, '_ut_status', true ) === 'fail' ) { $fails++; }
		$t = get_post_meta( $id, '_ut_total', true );
		if ( is_numeric( $t ) ) { $total += (float) $t; $has_total = true; }
	}
	if ( $n < 2 ) { return null; }
	return array( 'avg' => round( $sum / $n, 2 ), 'n' => $n, 'fails' => $fails, 'total' => $has_total ? $total : null );
}

add_action( 'wp_ajax_uturn_result_lookup', 'uturn_result_lookup_ajax' );
add_action( 'wp_ajax_nopriv_uturn_result_lookup', 'uturn_result_lookup_ajax' );
function uturn_result_lookup_ajax() {
	$fail = function () {
		wp_send_json( array( 'ok' => false ) );
	};
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'uturn_result_lookup' ) ) {
		$fail();
	}
	/* Gentle per-IP rate limit: 40 lookups / 5 minutes. */
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? (string) $_SERVER['REMOTE_ADDR'] : 'x';
	$key = 'utrl_' . md5( $ip );
	$n   = (int) get_transient( $key );
	if ( $n >= 40 ) {
		wp_send_json( array( 'ok' => false, 'limited' => true ) );
	}
	set_transient( $key, $n + 1, 5 * MINUTE_IN_SECONDS );

	$norm = function ( $v ) {
		$v = function_exists( 'uturn_bn_to_en' ) ? uturn_bn_to_en( $v ) : $v;
		return trim( (string) $v );
	};
	{
		$exam = isset( $_POST['exam'] ) ? sanitize_key( $_POST['exam'] ) : '';
		$year = $norm( isset( $_POST['year'] ) ? wp_unslash( $_POST['year'] ) : '' );
		$cls  = isset( $_POST['cls'] ) ? sanitize_text_field( wp_unslash( $_POST['cls'] ) ) : '';
		$roll = $norm( isset( $_POST['roll'] ) ? wp_unslash( $_POST['roll'] ) : '' );
		$code = strtoupper( trim( isset( $_POST['code'] ) ? sanitize_text_field( wp_unslash( $_POST['code'] ) ) : '' ) );
		$exams = uturn_exams( false );
		if ( ! isset( $exams[ $exam ] ) || $year === '' ) {
			$fail();
		}
		$by_code = (bool) preg_match( '/^STU-\d{4,}$/', $code );
		if ( ! $by_code && ( $cls === '' || $roll === '' ) ) {
			$fail();
		}
		$mq = array( array( 'key' => '_ut_exam', 'value' => $exam ) );
		if ( $by_code ) {
			$mq[] = array( 'key' => '_ut_student_code', 'value' => $code );
		} else {
			$mq[] = array( 'key' => '_ut_class', 'value' => $cls );
		}
		$cands = get_posts(
			array(
				'post_type' => 'ut_result', 'posts_per_page' => 20, 'post_status' => 'publish',
				'meta_query' => $mq,
			)
		);
		$p = null;
		foreach ( $cands as $c ) {
			$y = $norm( get_post_meta( $c->ID, '_ut_year', true ) );
			if ( $y !== $year ) {
				continue;
			}
			if ( $by_code ) {
				$p = $c;
				break;
			}
			$r = $norm( get_post_meta( $c->ID, '_ut_roll', true ) );
			if ( $r === $roll ) {
				$p = $c;
				break;
			}
		}
		if ( ! $p ) {
			$fail();
		}
	}
	$g = function ( $k ) use ( $p ) {
		return get_post_meta( $p->ID, $k, true );
	};
	$subs = array();
	foreach ( uturn_lines( $g( '_ut_subjects' ) ) as $ln ) {
		$parts = array_map( 'trim', explode( '|', $ln ) );
		if ( count( $parts ) >= 3 ) {
			$subs[] = array( $parts[0], $parts[1], $parts[2], isset( $parts[3] ) ? $parts[3] : '', uturn_grade_point( $parts[2] ) );
		}
	}
	wp_send_json(
		array(
			'ok' => true,
			'row' => array(
				'exam' => $g( '_ut_exam' ), 'examBn' => $g( '_ut_exam_bn' ), 'year' => $g( '_ut_year' ),
				'cls' => $g( '_ut_class' ), 'roll' => $g( '_ut_roll' ), 'reg' => $g( '_ut_reg' ),
				'name' => preg_replace( '/\s—.*$/u', '', $p->post_title ), 'subjects' => $subs,
				'gpa' => $g( '_ut_gpa' ), 'status' => $g( '_ut_status' ) ? $g( '_ut_status' ) : 'pass',
				'total' => $g( '_ut_total' ), 'merit' => $g( '_ut_merit' ), 'code' => $g( '_ut_student_code' ),
				'photo' => uturn_result_photo_url( $p->ID ),
				'cum' => uturn_cumulative_gpa( uturn_result_siblings( $p ) ),
			),
		)
	);
}
