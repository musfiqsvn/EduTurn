<?php
/**
 * EduTurn — Idempotent demo-content installer ("ডেমো কনটেন্ট" page).
 * Reads assets/seed/demo.json, guards every stage with an option flag,
 * and re-runs safely (title-based upsert, no duplicates).
 */

defined( 'ABSPATH' ) || exit;

function uturn_demo_path() {
	return UTURN_DIR . '/assets/seed/demo.json';
}

function uturn_seeder_page() {
	if ( isset( $_POST['uturn_seed_run'] ) && check_admin_referer( 'uturn_seed_run' ) && current_user_can( 'manage_options' ) ) {
		$log = uturn_run_seeder();
		echo '<div class="notice notice-success" role="status"><p><strong>সিডার সম্পন্ন।</strong></p><ul style="margin:0 0 0 18px;list-style:disc">';
		foreach ( $log as $line ) {
			echo '<li>' . esc_html( $line ) . '</li>';
		}
		echo '</ul></div>';
	}
	echo '<div class="wrap"><h1>ডেমো কনটেন্ট</h1>'
		. '<p>আলোকিত বিদ্যানিকেতনের ১:১ ডেমো ডেটাসেট ইনস্টল করুন — শিক্ষক, শিক্ষার্থী, নোটিশ, ইভেন্ট, ফলাফল, রুটিন, ভর্তি তথ্য, ইউজার ও মেনু। বারবার চালালেও ডুপ্লিকেট হবে না।</p>'
		. '<form method="post">' . wp_nonce_field( 'uturn_seed_run', '_wpnonce', true, false )
		. '<p><button class="button button-primary button-large" name="uturn_seed_run" value="1">ডেমো ডেটা ইনস্টল / রি-সিঙ্ক</button></p></form>';
	$flag = get_option( 'uturn_seed_done', array() );
	if ( $flag ) {
		echo '<p class="description">সর্বশেষ সফল রান: ' . esc_html( isset( $flag['at'] ) ? $flag['at'] : '—' ) . ' · সংস্করণ ' . esc_html( isset( $flag['v'] ) ? $flag['v'] : '—' ) . '</p>';
	}
	echo '</div>';
}

function uturn_run_seeder() {
	$log = array();
	if ( ! file_exists( uturn_demo_path() ) ) {
		return array( 'ত্রুটি: demo.json পাওয়া যায়নি।' );
	}
	$demo = json_decode( file_get_contents( uturn_demo_path() ), true );
	if ( ! is_array( $demo ) ) {
		return array( 'ত্রুটি: demo.json পার্স করা যায়নি।' );
	}
	$count_taxa = uturn_seed_taxonomies( $demo );
	$log[] = "ট্যাক্সোনমি টার্ম: {$count_taxa}টি";
	$log[] = 'কনটেন্ট: ' . uturn_seed_content( $demo ) . 'টি পোস্ট (আপসার্ট)';
	$log[] = 'ফলাফল ডাটাবেজ: ' . uturn_seed_results( $demo ) . 'টি';
	$log[] = 'রুটিন: ' . uturn_seed_routines( $demo );
	$log[] = 'ইউজার: ' . uturn_seed_users( $demo ) . ' জন (আপসার্ট)';
	$log[] = 'ফি রেকর্ড: ' . uturn_seed_fees( $demo ) . 'টি';
	$log[] = 'ব্যাকফিল: ' . uturn_seed_backfill( $demo );
	$log[] = 'মেনু: ' . ( uturn_seed_menu() ? 'প্রাইমারি মেনু প্রস্তুত' : 'মেনু ইতিমধ্যে আছে' );
	$log[] = 'পেজ: ' . uturn_seed_pages();
	$log[] = 'সাইট পরিচয়: ' . uturn_seed_identity();
	flush_rewrite_rules();
	$log[] = 'পার্মালিংক রুল রিফ্রেশ করা হয়েছে';
	update_option( 'uturn_seed_done', array( 'at' => current_time( 'mysql' ), 'v' => UTURN_SEED_VERSION ) );
	return $log;
}

/** Normalize Bengali digits to ASCII (for ISO dates). */
function uturn_seed_ascii( $s ) {
	return str_replace( array( '০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯' ), array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ), (string) $s );
}

/** Seed body may be a string OR a paragraph array — normalize to HTML. */
function uturn_seed_body( $v ) {
	if ( is_array( $v ) ) {
		$v = implode( "\n\n", array_map( 'strval', $v ) );
	}
	return wpautop( (string) $v );
}

/* ---------- Stage 1: taxonomies ---------- */
function uturn_seed_taxonomies( $demo ) {
	$n = 0;
	$insert = function ( $tax, $terms ) use ( &$n ) {
		foreach ( (array) $terms as $name ) {
			if ( ! term_exists( $name, $tax ) ) {
				wp_insert_term( $name, $tax );
				$n++;
			}
		}
	};
	$insert( 'ut_notice_cat', array_keys( isset( $demo['cats'] ) ? $demo['cats'] : array() ) );
	$insert( 'ut_event_cat', array( 'অনুষ্ঠান', 'প্রতিযোগিতা', 'ছুটি' ) );
	$insert( 'ut_news_cat', array( 'ক্যাম্পাস', 'একাডেমিক', 'ক্রীড়া' ) );
	$insert( 'ut_gallery_cat', array( 'ক্যাম্পাস', 'ইভেন্ট', 'ক্রীড়া' ) );
	$insert( 'ut_download_cat', array( 'ফরম', 'রুটিন', 'সিলেবাস', 'অন্যান্য' ) );
	$insert( 'ut_faq_cat', array( 'admission' => 'ভর্তি', 'fees' => 'ফি', 'general' => 'সাধারণ' ) );
	$insert( 'ut_department', array( 'প্রশাসন', 'বিজ্ঞান', 'গণিত', 'ভাষা', 'আইসিটি' ) );
	$insert( 'ut_class', array( '৬ষ্ঠ', '৭ম', '৮ম', '৯ম', '১০ম' ) );
	return $n;
}

/* Title lookup without the deprecated get_page_by_title() (WP 6.2+). */
function uturn_seed_by_title( $title, $type ) {
	global $wpdb;
	$id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type = %s AND post_status != 'trash' LIMIT 1", $title, $type ) );
	return $id ? get_post( (int) $id ) : null;
}

/* ---------- Stage 2: posts (title-upsert) ---------- */
function uturn_seed_post( $type, $title, $content = '', $meta = array(), $tax = array(), $date = null, $status = 'publish' ) {
	$args = array(
		'post_title'   => wp_slash( $title ),
		'post_content' => wp_slash( $content ),
		'post_type'    => $type,
		'post_status'  => $status,
	);
	if ( $date ) {
		$args['post_date'] = $date;
	}
	$existing = uturn_seed_by_title( $title, $type  );
	if ( $existing ) {
		$args['ID'] = $existing->ID;
		unset( $args['post_status'] ); /* re-seed never flips a manually published row back */
		$id = wp_update_post( $args );
	} else {
		$id = wp_insert_post( $args );
	}
	if ( ! $id || is_wp_error( $id ) ) {
		return 0;
	}
	foreach ( $meta as $k => $v ) {
		update_post_meta( $id, $k, $v );
	}
	foreach ( $tax as $taxonomy => $terms ) {
		$terms = array_values( array_filter( (array) $terms, function ( $t ) { return $t !== '' && $t !== null; } ) );
		if ( $terms ) {
			wp_set_object_terms( $id, $terms, $taxonomy, false );
		}
	}
	return $id;
}

function uturn_seed_content( $demo ) {
	$n = 0;
	/* Demo datasets use short keys (cls/desig/dept/img/dateBn) or long keys
	 * (class/designation/department/image/date) — accept both, warn never. */
	$g = function ( $r, $k, $k2 = null ) { return isset( $r[ $k ] ) ? $r[ $k ] : ( $k2 !== null && isset( $r[ $k2 ] ) ? $r[ $k2 ] : '' ); };
	foreach ( (array) ( isset( $demo['notices'] ) ? $demo['notices'] : array() ) as $nc ) {
		$n += (int) (bool) uturn_seed_post( 'ut_notice', $g( $nc, 'title' ), wpautop( $g( $nc, 'content' ) ), array( '_ut_excerpt' => $g( $nc, 'excerpt' ) ), array( 'ut_notice_cat' => $g( $nc, 'cat' ) ) );
	}
	foreach ( (array) ( isset( $demo['events'] ) ? $demo['events'] : array() ) as $e ) {
		$iso = uturn_seed_ascii( $g( $e, 'dateISO' ) );
		$n += (int) (bool) uturn_seed_post( 'ut_event', $g( $e, 'title' ), uturn_seed_body( $g( $e, 'desc' ) !== '' ? $g( $e, 'desc' ) : $g( $e, 'excerpt' ) ), array( '_ut_date' => $iso, '_ut_date_bn' => $g( $e, 'dateBn', 'date' ), '_ut_time' => $g( $e, 'time' ), '_ut_location' => $g( $e, 'location' ), '_ut_excerpt' => $g( $e, 'excerpt' ), '_ut_image' => $g( $e, 'image', 'img' ) ), array( 'ut_event_cat' => $g( $e, 'cat' ) ) );
	}
	foreach ( (array) ( isset( $demo['news'] ) ? $demo['news'] : array() ) as $nw ) {
		$n += (int) (bool) uturn_seed_post( 'ut_news', $g( $nw, 'title' ), uturn_seed_body( $g( $nw, 'body' ) !== '' ? $g( $nw, 'body' ) : $g( $nw, 'excerpt' ) ), array( '_ut_date_bn' => $g( $nw, 'dateBn', 'date' ), '_ut_excerpt' => $g( $nw, 'excerpt' ), '_ut_image' => $g( $nw, 'image', 'img' ) ), array( 'ut_news_cat' => $g( $nw, 'cat' ) ) );
	}
	$sub_by_name = array();
	if ( function_exists( 'uturn_subjects_all' ) ) {
		foreach ( uturn_subjects_all() as $ss ) {
			$sub_by_name[ $ss['name'] ] = (int) $ss['id'];
		}
	}
	foreach ( (array) ( isset( $demo['teachers'] ) ? $demo['teachers'] : array() ) as $t ) {
		$snames = array( $g( $t, 'subject' ) );
		foreach ( (array) ( isset( $t['subjects'] ) ? $t['subjects'] : array() ) as $xs ) {
			$snames[] = $xs;
		}
		$sids = array();
		foreach ( $snames as $sn ) {
			if ( '' !== $sn && isset( $sub_by_name[ $sn ] ) && ! in_array( $sub_by_name[ $sn ], $sids, true ) ) {
				$sids[] = $sub_by_name[ $sn ];
			}
		}
		$n += (int) (bool) uturn_seed_post( 'ut_teacher', $g( $t, 'name' ), '', array( '_ut_designation' => $g( $t, 'desig', 'designation' ), '_ut_subject' => $g( $t, 'subject' ), '_ut_subject_ids' => $sids, '_ut_subject_id' => $sids ? $sids[0] : 0, '_ut_phone' => $g( $t, 'phone' ), '_ut_education' => '', '_ut_experience' => '', '_ut_bg' => $g( $t, 'bg' ), '_ut_photo' => $g( $t, 'photo' ) ), array( 'ut_department' => $g( $t, 'dept', 'department' ) ) );
	}
	foreach ( (array) ( isset( $demo['students'] ) ? $demo['students'] : array() ) as $s ) {
		$cls = $g( $s, 'cls', 'class' );
		$new_sid = uturn_seed_post( 'ut_student', $g( $s, 'name' ), '', array( '_ut_class' => $cls, '_ut_roll' => $g( $s, 'roll' ), '_ut_section' => $g( $s, 'sec', 'section' ), '_ut_guardian' => $g( $s, 'guardian' ), '_ut_phone' => $g( $s, 'phone' ), '_ut_dob' => $g( $s, 'dob' ), '_ut_bg' => $g( $s, 'bg' ), '_ut_star' => ! empty( $s['star'] ) ? '1' : '', '_ut_star_text' => $g( $s, 'star_text' ) ), array( 'ut_class' => $cls ) );
		$n += (int) (bool) $new_sid;
		if ( $new_sid && function_exists( 'uturn_ensure_student_code' ) ) {
			uturn_ensure_student_code( $new_sid );
		}
	}
	foreach ( (array) ( isset( $demo['albums'] ) ? $demo['albums'] : array() ) as $a ) {
		$photos = isset( $a['photos'] ) ? (array) $a['photos'] : array();
		$n += (int) (bool) uturn_seed_post( 'ut_album', $g( $a, 'title' ), wpautop( $g( $a, 'desc' ) ), array( '_ut_date_bn' => $g( $a, 'dateBn', 'date' ), '_ut_cover' => $g( $a, 'cover' ), '_ut_photos' => implode( "\n", $photos ) ), array( 'ut_gallery_cat' => $g( $a, 'cat' ) ) );
	}
	foreach ( (array) ( isset( $demo['downloads'] ) ? $demo['downloads'] : array() ) as $d ) {
		$n += (int) (bool) uturn_seed_post( 'ut_download', $g( $d, 'name', 'title' ), '', array( '_ut_file' => $g( $d, 'file' ), '_ut_ftype' => $g( $d, 'type' ), '_ut_date_bn' => $g( $d, 'dateBn', 'date' ) ), array( 'ut_download_cat' => $g( $d, 'cat' ) ) );
	}
	foreach ( (array) ( isset( $demo['faqs'] ) ? $demo['faqs'] : array() ) as $f ) {
		$n += (int) (bool) uturn_seed_post( 'ut_faq', $g( $f, 'q' ), wpautop( $g( $f, 'a' ) ), array(), array( 'ut_faq_cat' => $g( $f, 'cat' ) ) );
	}
	foreach ( (array) ( isset( $demo['testimonials'] ) ? $demo['testimonials'] : array() ) as $t ) {
		$n += (int) (bool) uturn_seed_post( 'ut_testimonial', $g( $t, 'name' ), wpautop( $g( $t, 'text' ) ), array( '_ut_role' => $g( $t, 'role' ), '_ut_category' => $g( $t, 'cat' ), '_ut_bg' => $g( $t, 'bg' ) ), array(), $g( $t, 'post_date' ) !== '' ? $g( $t, 'post_date' ) : null );
	}
	foreach ( (array) ( isset( $demo['achievements'] ) ? $demo['achievements'] : array() ) as $a ) {
		$n += (int) (bool) uturn_seed_post( 'ut_achievement', $g( $a, 'title' ), wpautop( $g( $a, 'desc' ) ), array( '_ut_num' => $g( $a, 'num' ), '_ut_year' => $g( $a, 'year' ) ) );
	}
	return $n;
}

/* ---------- Stage 3: results database ---------- */
function uturn_seed_results( $demo ) {
	$n = 0;
	foreach ( (array) ( isset( $demo['results'] ) ? $demo['results'] : array() ) as $r ) {
		$lines = array();
		foreach ( (array) ( isset( $r['subjects'] ) ? $r['subjects'] : array() ) as $s ) {
			$lines[] = $s[0] . '|' . $s[1] . '|' . $s[2];
		}
		$title = $r['name'] . ' — ' . $r['examBn'] . ' ' . $r['year'];
		$n += (int) (bool) uturn_seed_post(
			'ut_result', $title, '',
			array(
				'_ut_exam' => $r['exam'], '_ut_exam_bn' => $r['examBn'], '_ut_year' => $r['year'],
				'_ut_class' => $r['cls'], '_ut_roll' => $r['roll'], '_ut_reg' => $r['reg'],
				'_ut_gpa' => $r['gpa'], '_ut_status' => $r['status'], '_ut_subjects' => implode( "\n", $lines ),
				'_ut_stage' => 'draft',
			),
			array(), null, 'draft'
		);
	}
	return $n;
}

/* ---------- Stage 4: routines option ---------- */
function uturn_seed_routines( $demo ) {
	if ( get_option( 'uturn_routines' ) ) {
		return 'আগের কাস্টম রুটিন অপরিবর্তিত';
	}
	update_option(
		'uturn_routines',
		array(
			'periods'    => isset( $demo['periods'] ) ? $demo['periods'] : array(),
			'times'      => isset( $demo['periodTime'] ) ? $demo['periodTime'] : array(),
			'classes'    => isset( $demo['classRoutines'] ) ? $demo['classRoutines'] : array(),
			'updated'    => 'সেপ্টেম্বর ২০২৬',
			'exam_title' => isset( $demo['examRoutineTitle'] ) ? $demo['examRoutineTitle'] : '',
			'exam'       => isset( $demo['examRoutine'] ) ? $demo['examRoutine'] : array(),
		)
	);
	return 'ক্লাস + পরীক্ষার রুটিন ইনস্টল';
}

/* ---------- Stage 5: demo fee records ---------- */
function uturn_seed_fees( $demo ) {
	$students = get_posts( array( 'post_type' => 'ut_student', 'posts_per_page' => 3, 'orderby' => 'ID', 'order' => 'ASC', 'fields' => 'ids' ) );
	if ( ! $students ) {
		return 0;
	}
	$n = 0;
	foreach ( $students as $sid ) {
		$name = get_the_title( $sid );
		$user = get_user_by( 'login', sanitize_title( $name ) );
		if ( ! $user ) {
			$u = get_users( array( 'search' => $name, 'search_columns' => array( 'display_name' ), 'number' => 1 ) );
			$user = $u ? $u[0] : null;
		}
		$uid = $user ? $user->ID : 0;
		$n += (int) (bool) uturn_seed_post( 'ut_fee', $name . ' — সেপ্টেম্বর ২০২৬', '', array( '_ut_student_id' => $sid, '_ut_user_id' => $uid, '_ut_month' => 'সেপ্টেম্বর ২০২৬', '_ut_amount' => '1600', '_ut_status' => 'paid', '_ut_method' => 'ক্যাশ', '_ut_date' => '2026-09-05' ) );
		$n += (int) (bool) uturn_seed_post( 'ut_fee', $name . ' — অক্টোবর ২০২৬', '', array( '_ut_student_id' => $sid, '_ut_user_id' => $uid, '_ut_month' => 'অক্টোবর ২০২৬', '_ut_amount' => '1600', '_ut_status' => 'due', '_ut_method' => '', '_ut_date' => '' ) );
	}
	return $n;
}

/* ---------- Stage 6: backfill (teacher bios, event ISO dates) ---------- */
function uturn_seed_backfill( $demo ) {
	$n = 0;
	foreach ( (array) ( isset( $demo['teacherExtra'] ) ? $demo['teacherExtra'] : array() ) as $name => $x ) {
		$p = uturn_seed_by_title( $name, 'ut_teacher'  );
		if ( ! $p ) {
			continue;
		}
		if ( ! get_post_meta( $p->ID, '_ut_education', true ) && ! empty( $x['education'] ) ) {
			update_post_meta( $p->ID, '_ut_education', $x['education'] );
			$n++;
		}
		if ( ! get_post_meta( $p->ID, '_ut_experience', true ) && ! empty( $x['experience'] ) ) {
			update_post_meta( $p->ID, '_ut_experience', $x['experience'] );
			$n++;
		}
		if ( ! empty( $x['bio'] ) && false === strpos( $p->post_content, mb_substr( $x['bio'], 0, 12 ) ) ) {
			wp_update_post( array( 'ID' => $p->ID, 'post_content' => wp_slash( wpautop( $x['bio'] ) ) ) );
			$n++;
		}
	}
	foreach ( (array) ( isset( $demo['events'] ) ? $demo['events'] : array() ) as $e ) {
		$p = uturn_seed_by_title( $e['title'], 'ut_event'  );
		if ( $p && ! get_post_meta( $p->ID, '_ut_date', true ) && ! empty( $e['dateISO'] ) ) {
			update_post_meta( $p->ID, '_ut_date', uturn_seed_ascii( $e['dateISO'] ) );
			$n++;
		}
	}
	return $n . 'টি ফিল্ড আপডেট';
}

/* ---------- Stage 7: users (upsert, never reset passwords) ---------- */
function uturn_seed_users( $demo ) {
	$n = 0;
	$ensure = function ( $login, $email, $role, $name, $pass ) use ( &$n ) {
		$u = get_user_by( 'login', $login );
		if ( $u ) {
			$u->set_role( $role );
			wp_update_user( array( 'ID' => $u->ID, 'display_name' => $name ) );
			$n++;
			return $u->ID;
		}
		$id = wp_insert_user(
			array(
				'user_login' => $login, 'user_email' => $email, 'user_pass' => $pass,
				'display_name' => $name, 'role' => $role,
			)
		);
		if ( ! is_wp_error( $id ) ) {
			$n++;
			return $id;
		}
		return 0;
	};

	$staff_id = $ensure( 'office', 'office@alokitobiddyaniketon.edu.bd', 'uturn_staff', 'অফিস সহকারী', 'office123' );
	$ensure( 'schooladmin', 'admin@alokitobiddyaniketon.edu.bd', 'uturn_school_admin', 'স্কুল অ্যাডমিন', 'school123' );
	foreach ( (array) ( isset( $demo['teachers'] ) ? $demo['teachers'] : array() ) as $i => $t ) {
		if ( $i >= 5 ) {
			break;
		}
		$login = 'teacher' . ( $i + 1 );
		$uid = $ensure( $login, $login . '@alokitobiddyaniketon.edu.bd', 'uturn_teacher', isset( $t['name'] ) ? $t['name'] : $login, 'teacher123' );
		if ( $uid ) {
			$p = uturn_seed_by_title( $t['name'], 'ut_teacher'  );
			if ( $p ) {
				update_post_meta( $p->ID, '_ut_user_id', $uid );
				update_user_meta( $uid, '_ut_teacher_post', $p->ID );
				$psids = get_post_meta( $p->ID, '_ut_subject_ids', true );
				if ( is_array( $psids ) && $psids ) {
					$psids = array_values( array_map( 'absint', $psids ) );
					update_user_meta( $uid, '_ut_subject_ids', $psids );
					update_user_meta( $uid, '_ut_subject_id', $psids[0] );
					update_user_meta( $uid, '_ut_subject', function_exists( 'uturn_subject_name' ) ? uturn_subject_name( $psids[0] ) : '' );
				} else {
					$psn = get_post_meta( $p->ID, '_ut_subject', true );
					if ( '' !== $psn ) {
						update_user_meta( $uid, '_ut_subject', $psn );
					}
				}
			}
		}
	}
	foreach ( (array) ( isset( $demo['students'] ) ? $demo['students'] : array() ) as $i => $s ) {
		if ( $i >= 5 ) {
			break;
		}
		$login = 'student' . ( $i + 1 );
		$uid = $ensure( $login, $login . '@alokitobiddyaniketon.edu.bd', 'uturn_student', isset( $s['name'] ) ? $s['name'] : $login, 'student123' );
		if ( $uid ) {
			$p = uturn_seed_by_title( $s['name'], 'ut_student'  );
			if ( $p ) {
				update_post_meta( $p->ID, '_ut_user_id', $uid );
				update_user_meta( $uid, '_ut_student_post', $p->ID );
				update_user_meta( $uid, '_ut_class', isset( $s['cls'] ) ? $s['cls'] : ( isset( $s['class'] ) ? $s['class'] : '' ) );
				update_user_meta( $uid, '_ut_roll', isset( $s['roll'] ) ? $s['roll'] : '' );
			}
		}
	}
	return $n;
}

/* ---------- Stage 8: primary menu ---------- */
function uturn_seed_menu() {
	$locs = get_theme_mod( 'nav_menu_locations', array() );
	if ( ! empty( $locs['ut_primary'] ) && wp_get_nav_menu_object( $locs['ut_primary'] ) ) {
		return false;
	}
	/* Reuse a previously seeded menu instead of piling up duplicates. */
	$existing = wp_get_nav_menu_object( 'প্রাইমারি মেনু' );
	$menu_id  = $existing ? (int) $existing->term_id : 0;
	if ( ! $menu_id ) {
		$menu_id = wp_create_nav_menu( 'প্রাইমারি মেনু' );
	}
	if ( is_wp_error( $menu_id ) || ! $menu_id ) {
		return false;
	}
	$items = array(
		array( 'হোম', '/' ), array( 'আমাদের সম্পর্কে', '/about' ), array( 'একাডেমিক', '/academic' ),
		array( 'ভর্তি', '/admission' ), array( 'নোটিশ', '/notices' ), array( 'ফলাফল', '/results' ),
		array( 'রুটিন', '/routine' ), array( 'শিক্ষক', '/teachers' ), array( 'গ্যালারি', '/gallery' ),
		array( 'যোগাযোগ', '/contact' ),
	);
	foreach ( $items as $i => $it ) {
		wp_update_nav_menu_item(
			$menu_id, 0,
			array(
				'menu-item-title' => $it[0], 'menu-item-url' => home_url( $it[1] ),
				'menu-item-status' => 'publish', 'menu-item-type' => 'custom', 'menu-item-position' => $i + 1,
			)
		);
	}
	$locs['ut_primary'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locs );
	return true;
}

/* ---------- Stage 9: essential pages (slug-upsert, never duplicates) ---------- */
function uturn_essential_pages() {
	return array(
		'home'           => array( 'হোম', '' ),
		'about'          => array( 'আমাদের সম্পর্কে', '' ),
		'academic'       => array( 'একাডেমিক', '' ),
		'admission'      => array( 'ভর্তি তথ্য', '' ),
		'apply'          => array( 'ভর্তি আবেদন', '' ),
		'results'        => array( 'ফলাফল', '' ),
		'routine'        => array( 'ক্লাস রুটিন', '' ),
		'downloads'      => array( 'ডাউনলোড', '' ),
		'contact'        => array( 'যোগাযোগ', '' ),
		'students'       => array( 'শিক্ষার্থী কর্নার', '' ),
		'students-list'  => array( 'শিক্ষার্থী ডিরেক্টরি', 'page-students-list.php' ),
		'student-portal' => array( 'শিক্ষার্থী পোর্টাল', '' ),
		'teacher-portal' => array( 'শিক্ষক পোর্টাল', '' ),
		// notices/news/events/gallery/teachers load from CPT archives — no pages needed.
	);
}

function uturn_seed_pages() {
	$made = 0;
	foreach ( uturn_essential_pages() as $slug => $def ) {
		if ( get_page_by_path( $slug, OBJECT, 'page' ) ) {
			continue;
		}
		$id = wp_insert_post( array( 'post_type' => 'page', 'post_title' => $def[0], 'post_name' => $slug, 'post_status' => 'publish', 'post_content' => '' ) );
		if ( $id && ! is_wp_error( $id ) ) {
			if ( $def[1] !== '' ) {
				update_post_meta( $id, '_wp_page_template', $def[1] );
			}
			$made++;
		}
	}
	$front = get_page_by_path( 'home', OBJECT, 'page' );
	if ( $front && get_option( 'show_on_front' ) !== 'page' ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $front->ID );
	}
	return $made ? $made . 'টি পেজ তৈরি' : 'সব পেজ আগেই আছে';
}

/* ---------- Stage 10: site identity (only when still WP defaults) ---------- */
function uturn_seed_identity() {
	$did  = array();
	$name = get_option( 'blogname' );
	if ( in_array( $name, array( 'My Blog', 'My WordPress Blog', '' ), true ) ) {
		update_option( 'blogname', 'আলোকিত বিদ্যানিকেতন' );
		$did[] = 'নাম';
	}
	$desc = get_option( 'blogdescription' );
	if ( in_array( $desc, array( 'Just another WordPress site', 'My WordPress Blog', '' ), true ) ) {
		update_option( 'blogdescription', 'আলোকিত ভবিষ্যতের পথে' );
		$did[] = 'ট্যাগলাইন';
	}
	if ( get_option( 'timezone_string' ) === '' && (int) get_option( 'gmt_offset' ) === 0 ) {
		update_option( 'timezone_string', 'Asia/Dhaka' );
		$did[] = 'টাইমজোন';
	}
	$sample = get_page_by_path( 'sample-page', OBJECT, 'page' );
	if ( $sample ) {
		wp_trash_post( $sample->ID );
		$did[] = 'স্যাম্পল পেজ পরিষ্কার';
	}
	$hello = get_page_by_path( 'hello-world', OBJECT, 'post' );
	if ( $hello ) {
		wp_trash_post( $hello->ID );
		$did[] = 'ডিফল্ট পোস্ট পরিষ্কার';
	}
	return $did ? implode( ', ', $did ) : 'আগেই ঠিক আছে';
}
/* One-time: demote pre-v1.16 published demo results to draft (never public). */
add_action( 'init', 'uturn_demo_results_demote', 31 );
function uturn_demo_results_demote() {
	if ( is_admin() || get_option( 'uturn_demo_demote_v1', '' ) === 'done' ) {
		return;
	}
	global $wpdb;
	$ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type='ut_result' AND post_status='publish' AND post_title LIKE '%ডেমো%'" );
	foreach ( $ids as $id ) {
		wp_update_post( array( 'ID' => (int) $id, 'post_status' => 'draft' ) );
		update_post_meta( (int) $id, '_ut_stage', 'draft' );
		update_post_meta( (int) $id, '_ut_merit', '' );
	}
	update_option( 'uturn_demo_demote_v1', 'done', false );
}
