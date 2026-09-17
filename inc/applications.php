<?php
/**
 * EduTurn — Admission application engine.
 * Frontend wizard POST -> validated ut_application CPT + media attachments,
 * admin review list w/ status workflow + CSV export. Zero plugins.
 */

defined( 'ABSPATH' ) || exit;

/** Class whitelist shared by form + validator. */
function uturn_apply_classes() {
	return array( 'প্লে', 'নার্সারি', 'কেজি', '১ম', '২য়', '৩য়', '৪র্থ', '৫ম', '৬ষ্ঠ', '৭ম', '৮ম', '৯ম' );
}

function uturn_app_statuses() {
	return array( 'pending' => 'অপেক্ষমান', 'approved' => 'অনুমোদিত', 'rejected' => 'বাতিল' );
}

/* ---------- Frontend submission ---------- */
add_action( 'admin_post_nopriv_uturn_apply', 'uturn_apply_submit' );
add_action( 'admin_post_uturn_apply', 'uturn_apply_submit' );
function uturn_apply_submit() {
	$back = uturn_url( 'apply' );
	if ( ! isset( $_POST['uturn_apply_nonce'] ) || ! wp_verify_nonce( $_POST['uturn_apply_nonce'], 'uturn_apply' ) ) {
		wp_safe_redirect( $back . '?uturn_msg=form-error' );
		exit;
	}
	if ( ! empty( $_POST['a_web'] ) ) { // honeypot
		wp_safe_redirect( $back );
		exit;
	}
	$p = array_map( 'sanitize_text_field', wp_unslash( $_POST ) );
	$mob = uturn_valid_phone( isset( $p['g-mobile'] ) ? $p['g-mobile'] : '' );
	$ok_class = in_array( isset( $p['s-class'] ) ? $p['s-class'] : '', uturn_apply_classes(), true );
	$ok = $ok_class
		&& isset( $_FILES['s-photo'] ) && UPLOAD_ERR_OK === $_FILES['s-photo']['error']
		&& isset( $p['s-name'] ) && mb_strlen( $p['s-name'] ) >= 3
		&& ! empty( $p['s-dob'] ) && ! empty( $p['s-gender'] )
		&& isset( $p['s-address'] ) && mb_strlen( $p['s-address'] ) >= 5
		&& isset( $p['g-father'] ) && mb_strlen( $p['g-father'] ) >= 3
		&& isset( $p['g-mother'] ) && mb_strlen( $p['g-mother'] ) >= 3
		&& '' !== $mob
		&& ( empty( $p['g-email'] ) || is_email( $p['g-email'] ) );
	if ( ! $ok ) {
		wp_safe_redirect( $back . '?uturn_msg=app-error' );
		exit;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$store = function ( $key, $images_only ) {
		if ( ! isset( $_FILES[ $key ] ) || UPLOAD_ERR_OK !== $_FILES[ $key ]['error'] ) {
			return 0;
		}
		if ( $_FILES[ $key ]['size'] > 102400 ) {
			return -1; // too big
		}
		$mimes = $images_only ? array( 'jpg|jpeg|jpe' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp' ) : array( 'jpg|jpeg|jpe' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'pdf' => 'application/pdf' );
		$file = wp_handle_upload( $_FILES[ $key ], array( 'test_form' => false, 'mimes' => $mimes ) );
		if ( isset( $file['error'] ) ) {
			return -2;
		}
		return wp_insert_attachment( array( 'post_title' => sanitize_file_name( $_FILES[ $key ]['name'] ), 'post_status' => 'inherit', 'post_mime_type' => $file['type'] ), $file['file'] );
	};

	$photo = $store( 's-photo', true );
	if ( $photo <= 0 ) {
		wp_safe_redirect( $back . '?uturn_msg=app-error' );
		exit;
	}
	wp_update_attachment_metadata( $photo, wp_generate_attachment_metadata( $photo, get_attached_file( $photo ) ) );
	$docs = array();
	$docmap = array( 'f-birth' => 'জন্ম নিবন্ধন', 'f-result' => 'পূর্ববর্তী ফলাফল', 'f-nid' => 'পিতা/মাতার এনআইডি' );
	foreach ( $docmap as $key => $title ) {
		$id = $store( $key, false );
		if ( $id > 0 ) {
			if ( wp_attachment_is_image( $id ) ) {
				wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, get_attached_file( $id ) ) );
			}
			$docs[] = array( 'key' => $key, 'title' => $title, 'id' => $id );
		}
	}
	$id = wp_insert_post(
		array(
			'post_title'  => wp_slash( $p['s-name'] . ' — ' . $p['s-class'] ),
			'post_type'   => 'ut_application',
			'post_status' => 'publish',
		)
	);
	if ( ! $id || is_wp_error( $id ) ) {
		wp_safe_redirect( $back . '?uturn_msg=form-error' );
		exit;
	}
	$ref = 'UT-' . gmdate( 'Y' ) . '-' . str_pad( $id, 6, '0', STR_PAD_LEFT );
	$meta = array(
		'_ut_ref' => $ref, '_ut_status' => 'pending', '_ut_class' => $p['s-class'],
		'_ut_name_en' => isset( $p['s-name-en'] ) ? $p['s-name-en'] : '', '_ut_dob' => $p['s-dob'],
		'_ut_gender' => $p['s-gender'], '_ut_birthreg' => isset( $p['s-birthreg'] ) ? $p['s-birthreg'] : '',
		'_ut_blood' => isset( $p['s-blood'] ) ? $p['s-blood'] : '', '_ut_address' => $p['s-address'],
		'_ut_father' => $p['g-father'], '_ut_mother' => $p['g-mother'],
		'_ut_occupation' => isset( $p['g-occupation'] ) ? $p['g-occupation'] : '', '_ut_mobile' => $mob,
		'_ut_email' => isset( $p['g-email'] ) ? $p['g-email'] : '', '_ut_relation' => isset( $p['g-relation'] ) ? $p['g-relation'] : '',
		'_ut_school' => isset( $p['a-school'] ) ? $p['a-school'] : '', '_ut_lastclass' => isset( $p['a-lastclass'] ) ? $p['a-lastclass'] : '',
		'_ut_lastresult' => isset( $p['a-result'] ) ? $p['a-result'] : '', '_ut_year' => isset( $p['a-year'] ) ? $p['a-year'] : '',
		'_ut_photo' => $photo, '_ut_docs' => wp_json_encode( $docs ),
	);
	foreach ( $meta as $k => $v ) {
		update_post_meta( $id, $k, $v );
	}
	wp_safe_redirect( $back . '?app=' . rawurlencode( $ref ) );
	exit;
}

/* ---------- Admin review list ---------- */
add_filter( 'manage_ut_application_posts_columns', 'uturn_app_columns' );
function uturn_app_columns( $cols ) {
	return array(
		'cb' => $cols['cb'], 'ref' => 'রেফারেন্স', 'photo' => 'ছবি', 'title' => 'শিক্ষার্থী',
		'class' => 'শ্রেণি', 'mobile' => 'মোবাইল', 'status' => 'অবস্থা', 'date' => 'তারিখ',
	);
}
add_action( 'manage_ut_application_posts_custom_column', 'uturn_app_column', 10, 2 );
function uturn_app_column( $col, $id ) {
	if ( 'ref' === $col ) {
		echo '<code>' . esc_html( get_post_meta( $id, '_ut_ref', true ) ) . '</code>';
	} elseif ( 'photo' === $col ) {
		$pid = (int) get_post_meta( $id, '_ut_photo', true );
		echo $pid ? wp_get_attachment_image( $pid, 'thumbnail', false, array( 'style' => 'width:40px;height:40px;object-fit:cover;border-radius:8px' ) ) : '—';
	} elseif ( 'class' === $col ) {
		echo esc_html( get_post_meta( $id, '_ut_class', true ) );
	} elseif ( 'mobile' === $col ) {
		echo esc_html( get_post_meta( $id, '_ut_mobile', true ) );
	} elseif ( 'status' === $col ) {
		$st = get_post_meta( $id, '_ut_status', true );
		$map = uturn_app_statuses();
		$color = 'approved' === $st ? '#0a7b3d' : ( 'rejected' === $st ? '#b3261e' : '#9a6a00' );
		echo '<span style="background:#eef4ff;border:1px solid ' . esc_attr( $color ) . ';color:' . esc_attr( $color ) . ';padding:2px 8px;border-radius:20px;font-weight:600">' . esc_html( isset( $map[ $st ] ) ? $map[ $st ] : $st ) . '</span>';
	}
}
add_action( 'restrict_manage_posts', 'uturn_app_filters' );
function uturn_app_filters() {
	$screen = get_current_screen();
	if ( ! $screen || 'ut_application' !== $screen->post_type ) {
		return;
	}
	$st = isset( $_GET['ut_status'] ) ? sanitize_key( $_GET['ut_status'] ) : '';
	echo '<select name="ut_status"><option value="">সব অবস্থা</option>';
	foreach ( uturn_app_statuses() as $k => $label ) {
		echo '<option value="' . esc_attr( $k ) . '"' . selected( $st, $k, false ) . '>' . esc_html( $label ) . '</option>';
	}
	echo '</select>';
	$cl = isset( $_GET['ut_class'] ) ? sanitize_text_field( wp_unslash( $_GET['ut_class'] ) ) : '';
	echo '<select name="ut_class"><option value="">সব শ্রেণি</option>';
	foreach ( uturn_apply_classes() as $c ) {
		echo '<option value="' . esc_attr( $c ) . '"' . selected( $cl, $c, false ) . '>' . esc_html( $c ) . '</option>';
	}
	echo '</select>';
}
add_action( 'pre_get_posts', 'uturn_app_filter_query' );
function uturn_app_filter_query( $q ) {
	if ( ! is_admin() || ! $q->is_main_query() || 'ut_application' !== $q->get( 'post_type' ) ) {
		return;
	}
	if ( ! empty( $_GET['ut_status'] ) ) {
		$q->set( 'meta_key', '_ut_status' );
		$q->set( 'meta_value', sanitize_key( $_GET['ut_status'] ) );
	}
	if ( ! empty( $_GET['ut_class'] ) ) {
		$mq = (array) $q->get( 'meta_query', array() );
		$mq[] = array( 'key' => '_ut_class', 'value' => sanitize_text_field( wp_unslash( $_GET['ut_class'] ) ) );
		$q->set( 'meta_query', $mq );
	}
}

/* Row actions: approve / reject. */
add_filter( 'post_row_actions', 'uturn_app_row_actions', 10, 2 );
function uturn_app_row_actions( $actions, $post ) {
	if ( 'ut_application' !== $post->post_type || ! current_user_can( 'edit_post', $post->ID ) ) {
		return $actions;
	}
	$base = admin_url( 'admin-post.php?action=uturn_app_status&id=' . $post->ID );
	$actions['ut_approve'] = '<a style="color:#0a7b3d" href="' . esc_url( wp_nonce_url( $base . '&to=approved', 'uturn_app_status' ) ) . '">অনুমোদন</a>';
	$actions['ut_reject'] = '<a style="color:#b3261e" href="' . esc_url( wp_nonce_url( $base . '&to=rejected', 'uturn_app_status' ) ) . '">বাতিল</a>';
	return $actions;
}
add_action( 'admin_post_uturn_app_status', 'uturn_app_status' );
function uturn_app_status() {
	eduturn_license_require( 'erp' );
	$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
	$to = isset( $_GET['to'] ) ? sanitize_key( $_GET['to'] ) : '';
	if ( ! $id || ! isset( uturn_app_statuses()[ $to ] ) || ! check_admin_referer( 'uturn_app_status' ) || ! current_user_can( 'edit_post', $id ) ) {
		wp_die( 'অনুমতি নেই।' );
	}
	update_post_meta( $id, '_ut_status', $to );
	wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'edit.php?post_type=ut_application' ) );
	exit;
}

/* Review box on the edit screen. */
add_action( 'add_meta_boxes', 'uturn_app_review_box' );
function uturn_app_review_box() {
	if ( ! eduturn_license_can( 'erp' ) ) { $e = eduturn_license_effective( eduturn_license_state(), time() ); echo eduturn_license_lock_html( $e['code'] ); return; }
	add_meta_box( 'eduturn-app-review', 'আবেদন পর্যালোচনা', 'uturn_app_review_render', 'ut_application', 'normal', 'high' );
}
function uturn_app_review_render( $post ) {
	$g = function ( $k ) use ( $post ) {
		return get_post_meta( $post->ID, $k, true );
	};
	$pid = (int) $g( '_ut_photo' );
	echo '<div style="display:flex;gap:16px;flex-wrap:wrap">';
	echo $pid ? wp_get_attachment_image( $pid, 'medium', false, array( 'style' => 'width:140px;height:160px;object-fit:cover;border-radius:10px' ) ) : '';
	echo '<table class="form-table" style="flex:1;min-width:280px;margin:0">';
	$rows = array(
		'রেফারেন্স' => $g( '_ut_ref' ), 'শ্রেণি' => $g( '_ut_class' ), 'নাম (ইংরেজি)' => $g( '_ut_name_en' ),
		'জন্ম তারিখ' => $g( '_ut_dob' ), 'লিঙ্গ' => $g( '_ut_gender' ), 'জন্ম নিবন্ধন' => $g( '_ut_birthreg' ),
		'রক্তের গ্রুপ' => $g( '_ut_blood' ), 'ঠিকানা' => $g( '_ut_address' ), 'পিতা' => $g( '_ut_father' ),
		'মাতা' => $g( '_ut_mother' ), 'পেশা' => $g( '_ut_occupation' ), 'মোবাইল' => $g( '_ut_mobile' ),
		'ইমেইল' => $g( '_ut_email' ), 'সম্পর্ক' => $g( '_ut_relation' ), 'পূর্ববর্তী বিদ্যালয়' => $g( '_ut_school' ),
		'সর্বশেষ শ্রেণি' => $g( '_ut_lastclass' ), 'সর্বশেষ ফলাফল' => $g( '_ut_lastresult' ), 'পাসের সন' => $g( '_ut_year' ),
	);
	foreach ( $rows as $label => $v ) {
		echo '<tr><th style="padding:4px 10px 4px 0">' . esc_html( $label ) . '</th><td style="padding:4px 0">' . esc_html( $v ? $v : '—' ) . '</td></tr>';
	}
	$docs = json_decode( (string) $g( '_ut_docs' ), true );
	echo '<tr><th style="padding:4px 10px 4px 0">সংযুক্তি</th><td style="padding:4px 0">';
	if ( $docs ) {
		foreach ( $docs as $d ) {
			$url = wp_get_attachment_url( $d['id'] );
			echo $url ? '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html( $d['title'] ) . '</a><br>' : '';
		}
	} else {
		echo '—';
	}
	echo '</td></tr></table></div>';
}

/* ---------- CSV export ---------- */
add_action( 'admin_post_uturn_app_export', 'uturn_app_export' );
function uturn_app_export() {
	eduturn_license_require( 'erp' );
	if ( ! check_admin_referer( 'uturn_app_export' ) || ! current_user_can( 'edit_ut_applications' ) ) {
		wp_die( 'অনুমতি নেই।' );
	}
	$posts = get_posts( array( 'post_type' => 'ut_application', 'posts_per_page' => -1, 'orderby' => 'ID', 'order' => 'ASC' ) );
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=applications-' . gmdate( 'Y-m-d' ) . '.csv' );
	echo "\xEF\xBB\xBF";
	$out = fopen( 'php://output', 'w' );
	fputcsv( $out, array( 'Ref', 'Name', 'Class', 'Gender', 'DOB', 'Mobile', 'Father', 'Mother', 'Status', 'Date' ) );
	foreach ( $posts as $p ) {
		$g = function ( $k ) use ( $p ) {
			return get_post_meta( $p->ID, $k, true );
		};
		fputcsv(
			$out,
			array( $g( '_ut_ref' ), preg_replace( '/\s—.*$/u', '', $p->post_title ), $g( '_ut_class' ), $g( '_ut_gender' ), $g( '_ut_dob' ), $g( '_ut_mobile' ), $g( '_ut_father' ), $g( '_ut_mother' ), $g( '_ut_status' ), $p->post_date )
		);
	}
	fclose( $out );
	exit;
}
