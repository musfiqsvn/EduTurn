<?php
/**
 * EduTurn — Academic module (shell-native).
 * Class manager (ut_class terms + term meta), teacher↔class subject map,
 * assignments + online tests + student submissions + grading,
 * Bangla admit-card builder. No wp-admin needed.
 */

defined( 'ABSPATH' ) || exit;

/* ================= helpers ================= */
function uturn_class_terms( $active_only = true ) {
	$t = get_terms( array( 'taxonomy' => 'ut_class', 'hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC' ) );
	if ( is_wp_error( $t ) ) {
		return array();
	}
	$out = array();
	foreach ( (array) $t as $term ) {
		if ( $active_only && get_term_meta( $term->term_id, 'ut_archived', true ) ) {
			continue;
		}
		$out[] = $term;
	}
	usort(
		$out,
		function ( $a, $b ) {
			$oa = (int) get_term_meta( $a->term_id, 'ut_order', true );
			$ob = (int) get_term_meta( $b->term_id, 'ut_order', true );
			if ( $oa === $ob ) {
				return strcmp( $a->name, $b->name );
			}
			return $oa - $ob;
		}
	);
	return $out;
}
/** Short code shown beside the class name (e.g. C6). Falls back to ''. */
function uturn_class_code( $term_id ) {
	return (string) get_term_meta( (int) $term_id, 'ut_code', true );
}
/** True when the class owns students or results (must archive, never hard-delete). */
function uturn_class_has_data( $term ) {
	$term = is_object( $term ) ? $term : get_term( (int) $term, 'ut_class' );
	if ( ! $term || is_wp_error( $term ) ) {
		return false;
	}
	if ( (int) $term->count > 0 ) {
		return true;
	}
	$q = new WP_Query(
		array(
			'post_type' => 'ut_result', 'posts_per_page' => 1, 'fields' => 'ids',
			'post_status' => 'any', 'meta_key' => '_ut_class', 'meta_value' => $term->name,
		)
	);
	return (int) $q->found_posts > 0;
}

function uturn_class_term_by_name( $name ) {
	$t = get_term_by( 'name', $name, 'ut_class' );
	return $t ? $t : null;
}

/** Class teacher user ID stored on the term (canonical). */
function uturn_class_cteacher( $term_id ) {
	return (int) get_term_meta( (int) $term_id, 'ut_cteacher', true );
}

/** Subject map: array of array( 'subject' => .., 'teacher' => uid ). */
function uturn_class_subjects( $term_id ) {
	$m = get_term_meta( (int) $term_id, 'ut_csubjects', true );
	return is_array( $m ) ? array_values( $m ) : array();
}

function uturn_teachers_list() {
	$q = new WP_User_Query( array( 'role' => 'uturn_teacher', 'number' => -1, 'orderby' => 'display_name', 'order' => 'ASC', 'fields' => array( 'ID', 'display_name' ) ) );
	$out = array();
	foreach ( (array) $q->get_results() as $u ) {
		$out[ (int) $u->ID ] = $u->display_name;
	}
	return $out;
}

/** Classes (term names) where $uid is class-teacher or a subject-teacher. */
function uturn_teacher_classes( $uid ) {
	$uid = (int) $uid;
	$out = array();
	foreach ( uturn_class_terms() as $t ) {
		$role = array();
		if ( uturn_class_cteacher( $t->term_id ) === $uid ) {
			$role[] = 'শ্রেণি-শিক্ষক';
		}
		foreach ( uturn_class_subjects( $t->term_id ) as $s ) {
			if ( (int) ( $s['teacher'] ?? 0 ) === $uid && ( $s['subject'] ?? '' ) !== '' ) {
				$role[] = $s['subject'];
			}
		}
		if ( $role ) {
			$out[ $t->name ] = $role;
		}
	}
	return $out;
}

function uturn_assignment_types() {
	return array( 'assignment' => 'বাড়ির কাজ / অ্যাসাইনমেন্ট', 'online-test' => 'অনলাইন টেস্ট' );
}

function uturn_assignment_due_label( $due ) {
	if ( ! preg_match( '/^\\d{4}-\\d{2}-\\d{2}$/', (string) $due ) ) {
		return '—';
	}
	$today = current_time( 'Y-m-d' );
	if ( $due < $today ) {
		return '⏰ শেষ (' . ( function_exists( 'uturn_bn_date' ) ? uturn_bn_date( $due ) : $due ) . ')';
	}
	return ( function_exists( 'uturn_bn_date' ) ? uturn_bn_date( $due ) : $due );
}

/** Can current user manage this assignment (author or SA/HM/super)? */
function uturn_can_manage_assignment( $post ) {
	if ( current_user_can( 'edit_others_ut_assignments' ) || current_user_can( 'manage_options' ) ) {
		return true;
	}
	return (int) $post->post_author === get_current_user_id() && current_user_can( 'edit_ut_assignments' );
}

function uturn_submission_of( $assignment_id, $student_uid ) {
	$f = get_posts( array( 'post_type' => 'ut_submission', 'posts_per_page' => 1, 'author' => (int) $student_uid, 'meta_key' => '_ut_assignment_id', 'meta_value' => (int) $assignment_id ) );
	return $f ? $f[0] : null;
}

function uturn_submission_count( $assignment_id ) {
	$q = new WP_Query( array( 'post_type' => 'ut_submission', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_ut_assignment_id', 'meta_value' => (int) $assignment_id ) );
	return (int) $q->found_posts;
}

function uturn_academic_msg() {
	if ( empty( $_GET['uturn_msg'] ) ) {
		return;
	}
	$map = array(
		'class-saved' => array( 'success', 'শ্রেণি সংরক্ষণ করা হয়েছে।' ),
		'class-deleted' => array( 'success', 'শ্রেণি মুছে ফেলা হয়েছে।' ),
		'class-error' => array( 'error', 'শ্রেণির নাম দিন।' ),
		'class-archived' => array( 'success', 'শ্রেণি আর্কাইভ করা হয়েছে (ডেটা নিরাপদ)।' ),
		'class-restored' => array( 'success', 'শ্রেণি পুনরায় সক্রিয় করা হয়েছে।' ),
		'exam-saved' => array( 'success', 'পরীক্ষা সংরক্ষণ করা হয়েছে।' ),
		'exam-deleted' => array( 'success', 'পরীক্ষা মুছে ফেলা হয়েছে।' ),
		'exam-used' => array( 'error', 'এই পরীক্ষার ফলাফল আছে — মোছা যাবে না।' ),
		'assignment-saved' => array( 'success', 'অ্যাসাইনমেন্ট সংরক্ষণ করা হয়েছে।' ),
		'assignment-deleted' => array( 'success', 'অ্যাসাইনমেন্ট মুছে ফেলা হয়েছে।' ),
		'assignment-error' => array( 'error', 'শিরোনাম ও শ্রেণি আবশ্যক।' ),
		'submitted' => array( 'success', 'জমা দেওয়া হয়েছে। শুভকামনা!' ),
		'submit-error' => array( 'error', 'উত্তর বা ফাইল — যেকোনো একটি দিন।' ),
		'file-error' => array( 'error', 'ফাইল 2MB-এর মধ্যে JPG/PNG/PDF হতে হবে।' ),
		'graded' => array( 'success', 'মূল্যায়ন সংরক্ষণ করা হয়েছে।' ),
	);
	$code = sanitize_key( wp_unslash( $_GET['uturn_msg'] ) );
	if ( isset( $map[ $code ] ) ) {
		echo '<div class="notice notice-' . esc_attr( $map[ $code ][0] ) . '"><p>' . esc_html( $map[ $code ][1] ) . '</p></div>';
	}
}

/* ================= 1. Class manager ================= */
function uturn_dash_classes() {
	if ( function_exists( 'uturn_dash_erp_or_lock' ) && ! uturn_dash_erp_or_lock() ) {
		return;
	}
	uturn_academic_msg();
	$sub = isset( $_GET['sub'] ) ? sanitize_key( $_GET['sub'] ) : 'list';
	if ( $sub === 'form' ) {
		uturn_class_form();
		return;
	}
	$terms = uturn_class_terms( false );
	echo '<div class="utd-card utd-bar-row"><a class="button button-primary" href="' . esc_url( uturn_dash_view_url( 'classes', array( 'sub' => 'form' ) ) ) . '">＋ নতুন শ্রেণি</a>';
	echo '<span class="utd-muted">মোট ' . esc_html( function_exists( 'uturn_bn' ) ? uturn_bn( count( $terms ) ) : count( $terms ) ) . 'টি শ্রেণি</span></div>';
	echo '<div class="utd-card"><table class="widefat striped" aria-label="শ্রেণি তালিকা"><thead><tr><th scope="col">শ্রেণি</th><th scope="col">কোড</th><th scope="col">শিক্ষার্থী</th><th scope="col">শ্রেণি-শিক্ষক</th><th scope="col">বিষয়-শিক্ষক</th><th scope="col">অবস্থা</th><th scope="col">অ্যাকশন</th></tr></thead><tbody>';
	if ( ! $terms ) {
		echo '<tr><td colspan="7">কোনো শ্রেণি নেই — উপরে ＋ নতুন শ্রেণি চাপুন।</td></tr>';
	}
	foreach ( $terms as $t ) {
		$ct = uturn_class_cteacher( $t->term_id );
		$ctu = $ct ? get_userdata( $ct ) : null;
		$subs = array();
		foreach ( uturn_class_subjects( $t->term_id ) as $s ) {
			if ( ( $s['subject'] ?? '' ) === '' ) {
				continue;
			}
			$tu = (int) ( $s['teacher'] ?? 0 ) ? get_userdata( (int) $s['teacher'] ) : null;
			$subs[] = $s['subject'] . ( $tu ? ' — ' . $tu->display_name : '' );
		}
		$edit = uturn_dash_view_url( 'classes', array( 'sub' => 'form', 'term_id' => $t->term_id ) );
		$del = wp_nonce_url( admin_url( 'admin-post.php?action=uturn_class_delete&term_id=' . $t->term_id . '&ut_shell=1' ), 'uturn_class_delete_' . $t->term_id );
		$res = wp_nonce_url( admin_url( 'admin-post.php?action=uturn_class_restore&term_id=' . $t->term_id . '&ut_shell=1' ), 'uturn_class_delete_' . $t->term_id );
		$arch = (bool) get_term_meta( $t->term_id, 'ut_archived', true );
		$code = uturn_class_code( $t->term_id );
		$hasdata = uturn_class_has_data( $t );
		echo '<tr><td><b>' . esc_html( $t->name ) . '</b></td>';
		echo '<td>' . ( $code !== '' ? '<code>' . esc_html( $code ) . '</code>' : '<span class="utd-muted">—</span>' ) . '</td>';
		echo '<td>' . esc_html( function_exists( 'uturn_bn' ) ? uturn_bn( $t->count ) : $t->count ) . ' জন</td>';
		echo '<td>' . ( $ctu ? '🏫 ' . esc_html( $ctu->display_name ) : '<span class="utd-muted">—</span>' ) . '</td>';
		echo '<td>' . ( $subs ? esc_html( implode( ' · ', $subs ) ) : '<span class="utd-muted">—</span>' ) . '</td>';
		echo '<td>' . ( $arch ? '<span class="utd-pill amber">আর্কাইভ</span>' : '<span class="utd-pill green">সক্রিয়</span>' ) . '</td>';
		echo '<td><a href="' . esc_url( $edit ) . '">সম্পাদনা</a>';
		if ( $arch ) {
			echo ' | <a href="' . esc_url( $res ) . '">পুনরায় চালু</a>';
		} elseif ( $hasdata ) {
			echo ' | <a style="color:#b32d2e" href="' . esc_url( $del ) . '" onclick="return confirm(\'এই শ্রেণির ডেটা আছে — আর্কাইভ হবে (মুছবে না)। চালিয়ে যাবেন?\')">আর্কাইভ</a>';
		} else {
			echo ' | <a style="color:#b32d2e" href="' . esc_url( $del ) . '" onclick="return confirm(\'শ্রেণি মুছবেন?\')">মুছুন</a>';
		}
		echo '</td></tr>';
	}
	echo '</tbody></table></div>';
}

function uturn_class_form() {
	$tid = isset( $_GET['term_id'] ) ? absint( $_GET['term_id'] ) : 0;
	$term = $tid ? get_term( $tid, 'ut_class' ) : null;
	if ( $tid && ( ! $term || is_wp_error( $term ) ) ) {
		echo '<div class="utd-card"><p>শ্রেণি পাওয়া যায়নি।</p></div>';
		return;
	}
	$ct = $term ? uturn_class_cteacher( $term->term_id ) : 0;
	$rows = $term ? uturn_class_subjects( $term->term_id ) : array();
	if ( ! $rows ) {
		$rows = array( array( 'subject' => '', 'teacher' => 0 ) );
	}
	$teachers = uturn_teachers_list();
	echo '<div class="utd-card"><h2>' . ( $term ? 'শ্রেণি সম্পাদনা: ' . esc_html( $term->name ) : 'নতুন শ্রেণি' ) . '</h2>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="uturn_class_save"><input type="hidden" name="ut_shell" value="1"><input type="hidden" name="term_id" value="' . (int) $tid . '">';
	wp_nonce_field( 'uturn_class_save' );
	echo '<table class="form-table">';
	echo '<tr><th scope="row">শ্রেণির নাম *</th><td><input type="text" class="regular-text" name="cname" value="' . esc_attr( $term ? $term->name : '' ) . '" required placeholder="যেমন: ষষ্ঠ, ৭ম, দশম (বিজ্ঞান)"></td></tr>';
	echo '<tr><th scope="row">কোড</th><td><input type="text" class="regular-text" name="ccode" value="' . esc_attr( $term ? uturn_class_code( $term->term_id ) : '' ) . '" placeholder="যেমন: C6 (ঐচ্ছিক)"></td></tr>';
	echo '<tr><th scope="row">ক্রম</th><td><input type="number" name="cord" value="' . esc_attr( $term ? (string) (int) get_term_meta( $term->term_id, 'ut_order', true ) : '0' ) . '" style="width:7em" aria-label="তালিকায় ক্রম"><p class="description">তালিকা ও ড্রপডাউনে ছোট সংখ্যা আগে দেখাবে।</p></td></tr>';
	echo '<tr><th scope="row">শ্রেণি-শিক্ষক</th><td><select name="cteacher" aria-label="শ্রেণি-শিক্ষক নির্বাচন"><option value="0">— নির্বাচন করুন —</option>';
	foreach ( $teachers as $id => $nm ) {
		echo '<option value="' . (int) $id . '"' . selected( $ct, $id, false ) . '>' . esc_html( $nm ) . '</option>';
	}
	echo '</select><p class="description">যিনি ১ম পিরিয়ড/হাজিরা নেবেন — তাঁর নেওয়া হাজিরাতেই অনুপস্থিতদের SMS যাবে।</p></td></tr>';
	echo '</table>';
	echo '<h3>বিষয়ভিত্তিক শিক্ষক (কোন শিক্ষক কোন বিষয় পড়াবেন)</h3>';
	echo '<div class="table-wrap"><table class="widefat striped" id="utSubTbl" style="max-width:720px" aria-label="বিষয় তালিকা"><thead><tr><th scope="col">বিষয়</th><th scope="col">শিক্ষক</th><th scope="col"></th></tr></thead><tbody>';
	foreach ( $rows as $r ) {
		echo '<tr><td><input type="text" name="subjects[]" value="' . esc_attr( $r['subject'] ?? '' ) . '" placeholder="যেমন: গণিত" style="width:100%"></td><td><select name="subteachers[]" style="width:100%"><option value="0">—</option>';
		foreach ( $teachers as $id => $nm ) {
			echo '<option value="' . (int) $id . '"' . selected( (int) ( $r['teacher'] ?? 0 ), (int) $id, false ) . '>' . esc_html( $nm ) . '</option>';
		}
		echo '</select></td><td><button type="button" class="button utSubDel">✕</button></td></tr>';
	}
	echo '</tbody></table></div><p><button type="button" class="button" id="utSubAdd">＋ বিষয় যোগ করুন</button></p>';
	submit_button( $term ? 'হালনাগাদ করুন' : 'শ্রেণি যোগ করুন' );
	echo '</form></div>';
	echo "<script>(function(){var t=document.getElementById('utSubTbl');if(!t)return;document.getElementById('utSubAdd').addEventListener('click',function(){var b=t.querySelector('tbody');var r=b.rows[0].cloneNode(true);r.querySelectorAll('input').forEach(function(i){i.value='';});r.querySelectorAll('select').forEach(function(s){s.selectedIndex=0;});b.appendChild(r);});t.addEventListener('click',function(e){if(e.target.classList.contains('utSubDel')){var b=t.querySelector('tbody');if(b.rows.length>1)e.target.closest('tr').remove();}});})();</script>";
}

add_action( 'admin_post_uturn_class_save', 'uturn_class_save' );
function uturn_class_save() {
	if ( function_exists( 'eduturn_license_require' ) ) {
		eduturn_license_require( 'erp' );
	}
	$back = function_exists( 'uturn_dash_view_url' ) ? uturn_dash_view_url( 'classes' ) : admin_url();
	if ( ! check_admin_referer( 'uturn_class_save' ) || ! current_user_can( 'uturn_manage_academic' ) ) {
		wp_die( 'অনুমতি নেই।' );
	}
	$in = wp_unslash( $_POST );
	$name = sanitize_text_field( $in['cname'] ?? '' );
	$tid = absint( $in['term_id'] ?? 0 );
	if ( $name === '' ) {
		wp_safe_redirect( add_query_arg( 'uturn_msg', 'class-error', $back ) );
		exit;
	}
	if ( $tid && get_term( $tid, 'ut_class' ) ) {
		wp_update_term( $tid, 'ut_class', array( 'name' => $name ) );
	} else {
		$ex = term_exists( $name, 'ut_class' );
		if ( $ex ) {
			$tid = (int) ( is_array( $ex ) ? $ex['term_id'] : $ex );
		} else {
			$nw = wp_insert_term( $name, 'ut_class' );
			if ( is_wp_error( $nw ) ) {
				wp_safe_redirect( add_query_arg( 'uturn_msg', 'class-error', $back ) );
				exit;
			}
			$tid = (int) $nw['term_id'];
		}
	}
	update_term_meta( $tid, 'ut_code', sanitize_text_field( $in['ccode'] ?? '' ) );
	update_term_meta( $tid, 'ut_order', absint( $in['cord'] ?? 0 ) );
	/* Class teacher: term meta (canonical) + teacher user-meta (legacy SMS rule). */
	$new_ct = absint( $in['cteacher'] ?? 0 );
	$old_ct = uturn_class_cteacher( $tid );
	if ( $old_ct && $old_ct !== $new_ct ) {
		delete_user_meta( $old_ct, '_ut_classteacher_of' );
	}
	update_term_meta( $tid, 'ut_cteacher', $new_ct );
	if ( $new_ct && get_userdata( $new_ct ) ) {
		update_user_meta( $new_ct, '_ut_classteacher_of', $name );
	}
	/* Subject map. */
	$subs = array();
	$sn = (array) ( $in['subjects'] ?? array() );
	$st = (array) ( $in['subteachers'] ?? array() );
	$seen = array();
	foreach ( $sn as $i => $s ) {
		$s = str_replace( '|', '', sanitize_text_field( $s ) );
		if ( $s === '' || isset( $seen[ $s ] ) ) {
			continue;
		}
		$seen[ $s ] = true;
		$subs[] = array( 'subject' => $s, 'teacher' => absint( $st[ $i ] ?? 0 ) );
	}
	update_term_meta( $tid, 'ut_csubjects', $subs );
	wp_safe_redirect( add_query_arg( 'uturn_msg', 'class-saved', $back ) );
	exit;
}

add_action( 'admin_post_uturn_class_restore', 'uturn_class_restore' );
function uturn_class_restore() {
	if ( function_exists( 'eduturn_license_require' ) ) {
		eduturn_license_require( 'erp' );
	}
	$back = function_exists( 'uturn_dash_view_url' ) ? uturn_dash_view_url( 'classes' ) : admin_url();
	$tid = absint( $_GET['term_id'] ?? 0 );
	if ( ! $tid || ! current_user_can( 'uturn_manage_academic' ) || ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'uturn_class_delete_' . $tid ) ) {
		wp_die( 'অনুমতি নেই।' );
	}
	delete_term_meta( $tid, 'ut_archived' );
	wp_safe_redirect( add_query_arg( 'uturn_msg', 'class-restored', $back ) );
	exit;
}
add_action( 'admin_post_uturn_class_delete', 'uturn_class_delete' );
function uturn_class_delete() {
	if ( function_exists( 'eduturn_license_require' ) ) {
		eduturn_license_require( 'erp' );
	}
	$back = function_exists( 'uturn_dash_view_url' ) ? uturn_dash_view_url( 'classes' ) : admin_url();
	$tid = absint( $_GET['term_id'] ?? 0 );
	if ( ! $tid || ! current_user_can( 'uturn_manage_academic' ) || ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'uturn_class_delete_' . $tid ) ) {
		wp_die( 'অনুমতি নেই।' );
	}
	if ( uturn_class_has_data( $tid ) ) {
		update_term_meta( $tid, 'ut_archived', 1 );
		wp_safe_redirect( add_query_arg( 'uturn_msg', 'class-archived', $back ) );
		exit;
	}
	$old_ct = uturn_class_cteacher( $tid );
	if ( $old_ct ) {
		delete_user_meta( $old_ct, '_ut_classteacher_of' );
	}
	wp_delete_term( $tid, 'ut_class' );
	wp_safe_redirect( add_query_arg( 'uturn_msg', 'class-deleted', $back ) );
	exit;
}

/* ================= 2. Assignments (teacher create · SA/HM monitor) ================= */
function uturn_dash_assignments() {
	if ( function_exists( 'uturn_dash_erp_or_lock' ) && ! uturn_dash_erp_or_lock() ) {
		return;
	}
	uturn_academic_msg();
	$sub = isset( $_GET['sub'] ) ? sanitize_key( $_GET['sub'] ) : 'list';
	if ( $sub === 'form' ) {
		uturn_assignment_form();
		return;
	}
	if ( $sub === 'subs' ) {
		uturn_assignment_submissions();
		return;
	}
	$mine_only = ! current_user_can( 'edit_others_ut_assignments' );
	$f_teacher = isset( $_GET['f_teacher'] ) ? absint( $_GET['f_teacher'] ) : 0;
	$f_class = isset( $_GET['f_class'] ) ? sanitize_text_field( wp_unslash( $_GET['f_class'] ) ) : '';
	$args = array( 'post_type' => 'ut_assignment', 'posts_per_page' => 100, 'orderby' => 'date', 'order' => 'DESC', 'post_status' => 'any' );
	if ( $mine_only ) {
		$args['author'] = get_current_user_id();
	} elseif ( $f_teacher ) {
		$args['author'] = $f_teacher;
	}
	if ( $f_class !== '' ) {
		$args['meta_key'] = '_ut_class';
		$args['meta_value'] = $f_class;
	}
	$rows = get_posts( $args );
	echo '<div class="utd-card utd-bar-row"><a class="button button-primary" href="' . esc_url( uturn_dash_view_url( 'assignments', array( 'sub' => 'form' ) ) ) . '">＋ নতুন অ্যাসাইনমেন্ট / টেস্ট</a>';
	if ( ! $mine_only ) {
		echo '<form method="get" action="' . esc_url( uturn_dash_url() ) . '" style="display:inline"><input type="hidden" name="view" value="assignments">শিক্ষক <select name="f_teacher" onchange="this.form.submit()"><option value="0">সবাই</option>';
		foreach ( uturn_teachers_list() as $id => $nm ) {
			echo '<option value="' . (int) $id . '"' . selected( $f_teacher, $id, false ) . '>' . esc_html( $nm ) . '</option>';
		}
		echo '</select> শ্রেণি <select name="f_class" onchange="this.form.submit()"><option value="">সব</option>';
		foreach ( uturn_class_terms() as $t ) {
			echo '<option value="' . esc_attr( $t->name ) . '"' . selected( $f_class, $t->name, false ) . '>' . esc_html( $t->name ) . '</option>';
		}
		echo '</select></form>';
	}
	echo '</div>';
	$types = uturn_assignment_types();
	echo '<div class="utd-card"><table class="widefat striped" aria-label="অ্যাসাইনমেন্ট তালিকা"><thead><tr><th scope="col">শিরোনাম</th><th scope="col">ধরন</th><th scope="col">শ্রেণি</th><th scope="col">শিক্ষক</th><th scope="col">শেষ তারিখ</th><th scope="col">জমা</th><th scope="col">অ্যাকশন</th></tr></thead><tbody>';
	if ( ! $rows ) {
		echo '<tr><td colspan="7">কোনো অ্যাসাইনমেন্ট নেই।</td></tr>';
	}
	foreach ( $rows as $p ) {
		$g = function ( $k ) use ( $p ) { return get_post_meta( $p->ID, $k, true ); };
		$au = get_userdata( (int) $p->post_author );
		$cls = $g( '_ut_class' );
		$total = $cls !== '' && function_exists( 'uturn_class_students' ) ? count( uturn_class_students( $cls ) ) : 0;
		$got = uturn_submission_count( $p->ID );
		$subs_url = uturn_dash_view_url( 'assignments', array( 'sub' => 'subs', 'id' => $p->ID ) );
		$edit_url = uturn_dash_view_url( 'assignments', array( 'sub' => 'form', 'id' => $p->ID ) );
		$del_url = wp_nonce_url( admin_url( 'admin-post.php?action=uturn_assignment_delete&id=' . $p->ID . '&ut_shell=1' ), 'uturn_assignment_delete_' . $p->ID );
		$can = uturn_can_manage_assignment( $p );
		echo '<tr><td><b>' . esc_html( $p->post_title ) . '</b>' . ( $p->post_status !== 'publish' ? ' <span class="utd-muted">(খসড়া)</span>' : '' ) . '</td>';
		echo '<td>' . esc_html( $types[ $g( '_ut_type' ) ] ?? $g( '_ut_type' ) ) . '</td><td>' . esc_html( $cls ) . '</td>';
		echo '<td>' . esc_html( $au ? $au->display_name : '—' ) . '</td><td>' . esc_html( uturn_assignment_due_label( $g( '_ut_due' ) ) ) . '</td>';
		echo '<td><a href="' . esc_url( $subs_url ) . '">' . esc_html( function_exists( 'uturn_bn' ) ? uturn_bn( $got ) . '/' . uturn_bn( $total ) : "$got/$total" ) . '</a></td>';
		echo '<td><a href="' . esc_url( $subs_url ) . '">জমা দেখুন</a>' . ( $can ? ' | <a href="' . esc_url( $edit_url ) . '">সম্পাদনা</a> | <a style="color:#b32d2e" href="' . esc_url( $del_url ) . '" onclick="return confirm(\'মুছবেন? জমাগুলোও মুছে যাবে।\')">মুছুন</a>' : '' ) . '</td></tr>';
	}
	echo '</tbody></table></div>';
}

function uturn_assignment_form() {
	$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
	$p = $id ? get_post( $id ) : null;
	if ( $id && ( ! $p || $p->post_type !== 'ut_assignment' || ! uturn_can_manage_assignment( $p ) ) ) {
		echo '<div class="utd-card"><p>অ্যাসাইনমেন্ট পাওয়া যায়নি বা অনুমতি নেই।</p></div>';
		return;
	}
	$g = function ( $k ) use ( $p ) { return $p ? get_post_meta( $p->ID, $k, true ) : ''; };
	$mine_classes = array_keys( uturn_teacher_classes( get_current_user_id() ) );
	echo '<div class="utd-card"><h2>' . ( $p ? 'সম্পাদনা' : 'নতুন অ্যাসাইনমেন্ট / অনলাইন টেস্ট' ) . '</h2>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="uturn_assignment_save"><input type="hidden" name="ut_shell" value="1"><input type="hidden" name="id" value="' . (int) $id . '">';
	wp_nonce_field( 'uturn_assignment_save' );
	echo '<table class="form-table">';
	echo '<tr><th scope="row">শিরোনাম *</th><td><input type="text" class="large-text" name="atitle" value="' . esc_attr( $p ? $p->post_title : '' ) . '" required placeholder="যেমন: অধ্যায় ৫ — অনুশীলনী ৫.২"></td></tr>';
	echo '<tr><th scope="row">ধরন</th><td><select name="atype">';
	foreach ( uturn_assignment_types() as $k => $lbl ) {
		echo '<option value="' . esc_attr( $k ) . '"' . selected( $g( '_ut_type' ), $k, false ) . '>' . esc_html( $lbl ) . '</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><th scope="row">শ্রেণি *</th><td><select name="aclass" required><option value="">— নির্বাচন করুন —</option>';
	foreach ( uturn_class_terms() as $t ) {
		$mark = in_array( $t->name, $mine_classes, true ) ? ' ★' : '';
		echo '<option value="' . esc_attr( $t->name ) . '"' . selected( $g( '_ut_class' ), $t->name, false ) . '>' . esc_html( $t->name ) . $mark . '</option>';
	}
	echo '</select><p class="description">★ = আপনার পড়ানো শ্রেণি।</p></td></tr>';
	echo '<tr><th scope="row">বিষয়</th><td><input type="text" class="regular-text" name="asubject" value="' . esc_attr( $g( '_ut_subject' ) ) . '" placeholder="যেমন: গণিত"></td></tr>';
	echo '<tr><th scope="row">নির্দেশনা</th><td><textarea class="large-text" rows="5" name="ainstructions" placeholder="শিক্ষার্থীরা কী করবে, কীভাবে জমা দেবে…">' . esc_textarea( $p ? $p->post_content : '' ) . '</textarea></td></tr>';
	$att = (int) $g( '_ut_file_id' );
	echo '<tr><th scope="row">সংযুক্তি</th><td>' . ( $att ? '<p><a href="' . esc_url( wp_get_attachment_url( $att ) ) . '" target="_blank" rel="noopener">বর্তমান ফাইল দেখুন</a></p>' : '' ) . '<input type="hidden" class="eduturn-media-id" name="afile" value="' . (int) $att . '"><button type="button" class="button eduturn-media-btn">ফাইল বেছে নিন</button> <button type="button" class="button eduturn-media-clear">সরান</button><span class="eduturn-media-prev"></span></td></tr>';
	echo '<tr><th scope="row">শেষ তারিখ</th><td><input type="date" name="adue" value="' . esc_attr( $g( '_ut_due' ) ) . '"></td></tr>';
	echo '<tr><th scope="row">পূর্ণ নম্বর</th><td><input type="number" name="amarks" min="0" max="1000" value="' . esc_attr( $g( '_ut_total' ) !== '' ? $g( '_ut_total' ) : '20' ) . '" style="width:8em"></td></tr>';
	echo '<tr><th scope="row">অবস্থা</th><td><select name="astatus"><option value="publish"' . selected( $p ? $p->post_status : 'publish', 'publish', false ) . '>প্রকাশিত (শিক্ষার্থী দেখবে)</option><option value="draft"' . selected( $p ? $p->post_status : '', 'draft', false ) . '>খসড়া</option></select></td></tr>';
	echo '</table>';
	submit_button( $p ? 'হালনাগাদ করুন' : 'প্রকাশ করুন' );
	echo '</form></div>';
}

add_action( 'admin_post_uturn_assignment_save', 'uturn_assignment_save' );
function uturn_assignment_save() {
	if ( function_exists( 'eduturn_license_require' ) ) {
		eduturn_license_require( 'erp' );
	}
	$back = function_exists( 'uturn_dash_view_url' ) ? uturn_dash_view_url( 'assignments' ) : admin_url();
	if ( ! check_admin_referer( 'uturn_assignment_save' ) || ! current_user_can( 'edit_ut_assignments' ) ) {
		wp_die( 'অনুমতি নেই।' );
	}
	$in = wp_unslash( $_POST );
	$id = absint( $in['id'] ?? 0 );
	$title = sanitize_text_field( $in['atitle'] ?? '' );
	$class = sanitize_text_field( $in['aclass'] ?? '' );
	if ( $title === '' || $class === '' ) {
		wp_safe_redirect( add_query_arg( 'uturn_msg', 'assignment-error', $back ) );
		exit;
	}
	if ( $id ) {
		$old = get_post( $id );
		if ( ! $old || $old->post_type !== 'ut_assignment' || ! uturn_can_manage_assignment( $old ) ) {
			wp_die( 'অনুমতি নেই।' );
		}
	}
	$type = in_array( ( $in['atype'] ?? '' ), array( 'assignment', 'online-test' ), true ) ? $in['atype'] : 'assignment';
	$pid = wp_insert_post(
		array(
			'ID' => $id, 'post_type' => 'ut_assignment', 'post_title' => wp_slash( $title ),
			'post_content' => wp_slash( sanitize_textarea_field( $in['ainstructions'] ?? '' ) ),
			'post_status' => ( ( $in['astatus'] ?? '' ) === 'draft' ) ? 'draft' : 'publish',
			'meta_input' => array(
				'_ut_type' => $type, '_ut_class' => $class,
				'_ut_subject' => sanitize_text_field( $in['asubject'] ?? '' ),
				'_ut_file_id' => absint( $in['afile'] ?? 0 ),
				'_ut_due' => preg_match( '/^\\d{4}-\\d{2}-\\d{2}$/', ( $in['adue'] ?? '' ) ) ? $in['adue'] : '',
				'_ut_total' => absint( $in['amarks'] ?? 20 ),
			),
		),
		true
	);
	if ( is_wp_error( $pid ) || ! $pid ) {
		wp_safe_redirect( add_query_arg( 'uturn_msg', 'assignment-error', $back ) );
		exit;
	}
	wp_safe_redirect( add_query_arg( 'uturn_msg', 'assignment-saved', $back ) );
	exit;
}

add_action( 'admin_post_uturn_assignment_delete', 'uturn_assignment_delete' );
function uturn_assignment_delete() {
	if ( function_exists( 'eduturn_license_require' ) ) {
		eduturn_license_require( 'erp' );
	}
	$back = function_exists( 'uturn_dash_view_url' ) ? uturn_dash_view_url( 'assignments' ) : admin_url();
	$id = absint( $_GET['id'] ?? 0 );
	$p = $id ? get_post( $id ) : null;
	if ( ! $p || $p->post_type !== 'ut_assignment' || ! uturn_can_manage_assignment( $p ) || ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'uturn_assignment_delete_' . $id ) ) {
		wp_die( 'অনুমতি নেই।' );
	}
	$subs = get_posts( array( 'post_type' => 'ut_submission', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_ut_assignment_id', 'meta_value' => $id ) );
	foreach ( $subs as $sid ) {
		wp_delete_post( $sid, true );
	}
	wp_delete_post( $id, true );
	wp_safe_redirect( add_query_arg( 'uturn_msg', 'assignment-deleted', $back ) );
	exit;
}

/* -------- submissions (teacher/SA/HM grading inbox) -------- */
function uturn_assignment_submissions() {
	$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
	$p = $id ? get_post( $id ) : null;
	if ( ! $p || $p->post_type !== 'ut_assignment' ) {
		echo '<div class="utd-card"><p>অ্যাসাইনমেন্ট পাওয়া যায়নি।</p></div>';
		return;
	}
	$can_grade = uturn_can_manage_assignment( $p );
	$g = function ( $k ) use ( $p ) { return get_post_meta( $p->ID, $k, true ); };
	$types = uturn_assignment_types();
	echo '<div class="utd-card"><p><a href="' . esc_url( uturn_dash_view_url( 'assignments' ) ) . '">← সব অ্যাসাইনমেন্ট</a></p>';
	echo '<h2>' . esc_html( $p->post_title ) . '</h2><p class="utd-muted">' . esc_html( $types[ $g( '_ut_type' ) ] ?? '' ) . ' · শ্রেণি ' . esc_html( $g( '_ut_class' ) ) . ' · ' . esc_html( $g( '_ut_subject' ) ) . ' · পূর্ণ নম্বর ' . esc_html( $g( '_ut_total' ) ) . ' · শেষ: ' . esc_html( uturn_assignment_due_label( $g( '_ut_due' ) ) ) . '</p></div>';
	$subs = get_posts( array( 'post_type' => 'ut_submission', 'posts_per_page' => -1, 'meta_key' => '_ut_assignment_id', 'meta_value' => $id, 'orderby' => 'date', 'order' => 'ASC' ) );
	if ( ! $subs ) {
		echo '<div class="utd-card"><p class="utd-muted">এখনও কেউ জমা দেয়নি।</p></div>';
		return;
	}
	foreach ( $subs as $s ) {
		$stu = get_userdata( (int) $s->post_author );
		$roll = $stu ? get_user_meta( $stu->ID, '_ut_roll', true ) : '';
		$marks = get_post_meta( $s->ID, '_ut_marks', true );
		$fb = get_post_meta( $s->ID, '_ut_feedback', true );
		$fid = (int) get_post_meta( $s->ID, '_ut_file_id', true );
		echo '<div class="utd-card"><h3>' . esc_html( $stu ? $stu->display_name : '—' ) . ' <small class="utd-muted">রোল ' . esc_html( $roll ) . ' · ' . esc_html( mysql2date( 'j F, g:i a', $s->post_date ) ) . '</small> ';
		echo $marks !== '' ? '<span class="utd-pill green">মূল্যায়িত: ' . esc_html( $marks ) . '/' . esc_html( $g( '_ut_total' ) ) . '</span>' : '<span class="utd-pill red">মূল্যায়ন বাকি</span>';
		echo '</h3>';
		$txt = get_post_meta( $s->ID, '_ut_text', true );
		if ( $txt !== '' ) {
			echo '<div class="utd-answer">' . nl2br( esc_html( $txt ) ) . '</div>';
		}
		if ( $fid && wp_get_attachment_url( $fid ) ) {
			echo '<p><a class="button button-secondary" href="' . esc_url( wp_get_attachment_url( $fid ) ) . '" target="_blank" rel="noopener">📎 সংযুক্তি দেখুন</a></p>';
		}
		if ( $can_grade ) {
			echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="uturn_grade_save"><input type="hidden" name="ut_shell" value="1"><input type="hidden" name="sid" value="' . (int) $s->ID . '">';
			wp_nonce_field( 'uturn_grade_save' );
			echo 'প্রাপ্ত নম্বর <input type="number" name="marks" min="0" max="' . esc_attr( (int) $g( '_ut_total' ) ) . '" value="' . esc_attr( $marks ) . '" style="width:7em"> / ' . esc_html( $g( '_ut_total' ) );
			echo ' &nbsp; মন্তব্য <input type="text" name="feedback" value="' . esc_attr( $fb ) . '" style="width:40%" placeholder="যেমন: ভালো হয়েছে, ৩নং আবার দেখো">';
			echo ' <button class="button button-primary" type="submit">সংরক্ষণ</button></form>';
		} elseif ( $fb !== '' ) {
			echo '<p class="utd-muted">শিক্ষকের মন্তব্য: ' . esc_html( $fb ) . '</p>';
		}
		echo '</div>';
	}
}

add_action( 'admin_post_uturn_grade_save', 'uturn_grade_save' );
function uturn_grade_save() {
	if ( function_exists( 'eduturn_license_require' ) ) {
		eduturn_license_require( 'erp' );
	}
	$sid = absint( $_POST['sid'] ?? 0 );
	$s = $sid ? get_post( $sid ) : null;
	$back = function_exists( 'uturn_dash_view_url' ) ? uturn_dash_view_url( 'assignments' ) : admin_url();
	if ( ! $s || $s->post_type !== 'ut_submission' || ! check_admin_referer( 'uturn_grade_save' ) ) {
		wp_die( 'অনুমতি নেই।' );
	}
	$a = get_post( (int) get_post_meta( $sid, '_ut_assignment_id', true ) );
	if ( ! $a || ! uturn_can_manage_assignment( $a ) ) {
		wp_die( 'অনুমতি নেই।' );
	}
	$back = uturn_dash_view_url( 'assignments', array( 'sub' => 'subs', 'id' => $a->ID ) );
	update_post_meta( $sid, '_ut_marks', sanitize_text_field( wp_unslash( $_POST['marks'] ?? '' ) ) );
	update_post_meta( $sid, '_ut_feedback', sanitize_text_field( wp_unslash( $_POST['feedback'] ?? '' ) ) );
	update_post_meta( $sid, '_ut_graded_by', get_current_user_id() );
	update_post_meta( $sid, '_ut_graded_at', current_time( 'mysql' ) );
	wp_safe_redirect( add_query_arg( 'uturn_msg', 'graded', $back ) );
	exit;
}

/* ================= 3. Student: view + submit ================= */
function uturn_dash_my_assignments() {
	if ( function_exists( 'uturn_dash_erp_or_lock' ) && ! uturn_dash_erp_or_lock() ) {
		return;
	}
	uturn_academic_msg();
	$sub = isset( $_GET['sub'] ) ? sanitize_key( $_GET['sub'] ) : 'list';
	if ( $sub === 'view' ) {
		uturn_student_assignment_view();
		return;
	}
	$stp = function_exists( 'uturn_my_student' ) ? uturn_my_student() : null;
	$class = $stp ? get_post_meta( $stp->ID, '_ut_class', true ) : '';
	if ( $class === '' ) {
		echo '<div class="utd-card"><p class="utd-muted">তোমার শ্রেণি নির্ধারিত হয়নি — অফিসে জানাও।</p></div>';
		return;
	}
	$rows = get_posts( array( 'post_type' => 'ut_assignment', 'posts_per_page' => 100, 'post_status' => 'publish', 'meta_key' => '_ut_class', 'meta_value' => $class, 'orderby' => 'date', 'order' => 'DESC' ) );
	$types = uturn_assignment_types();
	$uid = get_current_user_id();
	echo '<div class="utd-card"><table class="widefat striped" aria-label="অ্যাসাইনমেন্ট"><thead><tr><th scope="col">শিরোনাম</th><th scope="col">ধরন</th><th scope="col">বিষয়</th><th scope="col">শেষ তারিখ</th><th scope="col">অবস্থা</th><th scope="col"></th></tr></thead><tbody>';
	if ( ! $rows ) {
		echo '<tr><td colspan="6">এই শ্রেণির জন্য এখনও কোনো অ্যাসাইনমেন্ট নেই।</td></tr>';
	}
	foreach ( $rows as $p ) {
		$g = function ( $k ) use ( $p ) { return get_post_meta( $p->ID, $k, true ); };
		$subm = uturn_submission_of( $p->ID, $uid );
		$marks = $subm ? get_post_meta( $subm->ID, '_ut_marks', true ) : '';
		if ( $subm && $marks !== '' ) {
			$st = '<span class="utd-pill green">মূল্যায়িত: ' . esc_html( $marks ) . '/' . esc_html( $g( '_ut_total' ) ) . '</span>';
		} elseif ( $subm ) {
			$st = '<span class="utd-pill blue">জমা দিয়েছ ✓</span>';
		} else {
			$st = '<span class="utd-pill red">বাকি</span>';
		}
		echo '<tr><td><b>' . esc_html( $p->post_title ) . '</b></td><td>' . esc_html( $types[ $g( '_ut_type' ) ] ?? '' ) . '</td><td>' . esc_html( $g( '_ut_subject' ) ) . '</td><td>' . esc_html( uturn_assignment_due_label( $g( '_ut_due' ) ) ) . '</td><td>' . $st . '</td>';
		echo '<td><a class="button button-secondary" href="' . esc_url( uturn_dash_view_url( 'my-assignments', array( 'sub' => 'view', 'id' => $p->ID ) ) ) . '">খুলুন</a></td></tr>';
	}
	echo '</tbody></table></div>';
}

function uturn_student_assignment_view() {
	$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
	$p = $id ? get_post( $id ) : null;
	$stp = function_exists( 'uturn_my_student' ) ? uturn_my_student() : null;
	$class = $stp ? get_post_meta( $stp->ID, '_ut_class', true ) : '';
	if ( ! $p || $p->post_type !== 'ut_assignment' || $p->post_status !== 'publish' || get_post_meta( $p->ID, '_ut_class', true ) !== $class ) {
		echo '<div class="utd-card"><p>অ্যাসাইনমেন্ট পাওয়া যায়নি।</p></div>';
		return;
	}
	$g = function ( $k ) use ( $p ) { return get_post_meta( $p->ID, $k, true ); };
	$types = uturn_assignment_types();
	$uid = get_current_user_id();
	$subm = uturn_submission_of( $p->ID, $uid );
	$marks = $subm ? get_post_meta( $subm->ID, '_ut_marks', true ) : '';
	$fb = $subm ? get_post_meta( $subm->ID, '_ut_feedback', true ) : '';
	echo '<div class="utd-card"><p><a href="' . esc_url( uturn_dash_view_url( 'my-assignments' ) ) . '">← সব অ্যাসাইনমেন্ট</a></p>';
	echo '<h2>' . esc_html( $p->post_title ) . '</h2><p class="utd-muted">' . esc_html( $types[ $g( '_ut_type' ) ] ?? '' ) . ' · ' . esc_html( $g( '_ut_subject' ) ) . ' · পূর্ণ নম্বর ' . esc_html( $g( '_ut_total' ) ) . ' · শেষ: ' . esc_html( uturn_assignment_due_label( $g( '_ut_due' ) ) ) . '</p>';
	if ( trim( $p->post_content ) !== '' ) {
		echo '<div class="utd-answer">' . nl2br( esc_html( $p->post_content ) ) . '</div>';
	}
	$fid = (int) $g( '_ut_file_id' );
	if ( $fid && wp_get_attachment_url( $fid ) ) {
		echo '<p><a class="button button-secondary" href="' . esc_url( wp_get_attachment_url( $fid ) ) . '" target="_blank" rel="noopener">📎 প্রশ্ন/শিট ডাউনলোড</a></p>';
	}
	echo '</div>';
	if ( $subm && $marks !== '' ) {
		echo '<div class="utd-card"><h3>🎉 মূল্যায়ন হয়ে গেছে: ' . esc_html( $marks ) . ' / ' . esc_html( $g( '_ut_total' ) ) . '</h3>';
		if ( $fb !== '' ) {
			echo '<p>শিক্ষকের মন্তব্য: <b>' . esc_html( $fb ) . '</b></p>';
		}
		echo '</div>';
	}
	echo '<div class="utd-card"><h3>' . ( $subm ? 'আবার জমা দাও (আগেরটা বদলে যাবে)' : 'উত্তর জমা দাও' ) . '</h3>';
	echo '<form method="post" enctype="multipart/form-data" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="uturn_submission_save"><input type="hidden" name="ut_shell" value="1"><input type="hidden" name="aid" value="' . (int) $p->ID . '">';
	wp_nonce_field( 'uturn_submission_save' );
	echo '<p><textarea class="large-text" rows="6" name="answer" placeholder="এখানে উত্তর লেখো…">' . esc_textarea( $subm ? get_post_meta( $subm->ID, '_ut_text', true ) : '' ) . '</textarea></p>';
	$sfid = $subm ? (int) get_post_meta( $subm->ID, '_ut_file_id', true ) : 0;
	if ( $sfid && wp_get_attachment_url( $sfid ) ) {
		echo '<p class="utd-muted">বর্তমান ফাইল: <a href="' . esc_url( wp_get_attachment_url( $sfid ) ) . '" target="_blank" rel="noopener">দেখুন</a> (নতুন দিলে বদলে যাবে)</p>';
	}
	echo '<p>অথবা ফাইল (খাতার ছবি/PDF, সর্বোচ্চ 2MB): <input type="file" name="sfile" accept=".jpg,.jpeg,.png,.pdf"></p>';
	echo '<p><button class="button button-primary button-large" type="submit">📤 জমা দিন</button></p></form></div>';
}

add_action( 'admin_post_uturn_submission_save', 'uturn_submission_save' );
function uturn_submission_save() {
	if ( function_exists( 'eduturn_license_require' ) ) {
		eduturn_license_require( 'erp' );
	}
	$aid = absint( $_POST['aid'] ?? 0 );
	$a = $aid ? get_post( $aid ) : null;
	$back = function_exists( 'uturn_dash_view_url' ) ? uturn_dash_view_url( 'my-assignments' ) : home_url( '/' );
	if ( ! $a || $a->post_type !== 'ut_assignment' || ! check_admin_referer( 'uturn_submission_save' ) || ! current_user_can( 'edit_ut_submissions' ) ) {
		wp_die( 'অনুমতি নেই।' );
	}
	$back = uturn_dash_view_url( 'my-assignments', array( 'sub' => 'view', 'id' => $aid ) );
	$uid = get_current_user_id();
	/* Students may only submit to their own class's published assignments. */
	if ( ! current_user_can( 'edit_others_ut_submissions' ) ) {
		$stp = function_exists( 'uturn_my_student' ) ? uturn_my_student() : null;
		$class = $stp ? get_post_meta( $stp->ID, '_ut_class', true ) : '';
		if ( $a->post_status !== 'publish' || $class === '' || get_post_meta( $aid, '_ut_class', true ) !== $class ) {
			wp_die( 'অনুমতি নেই।' );
		}
	}
	$text = sanitize_textarea_field( wp_unslash( $_POST['answer'] ?? '' ) );
	$fid = 0;
	if ( ! empty( $_FILES['sfile']['name'] ) ) {
		$f = $_FILES['sfile'];
		$ok_ext = array( 'jpg', 'jpeg', 'png', 'pdf' );
		$ext = strtolower( pathinfo( $f['name'], PATHINFO_EXTENSION ) );
		if ( $f['error'] !== UPLOAD_ERR_OK || $f['size'] > 2 * 1024 * 1024 || ! in_array( $ext, $ok_ext, true ) ) {
			wp_safe_redirect( add_query_arg( 'uturn_msg', 'file-error', $back ) );
			exit;
		}
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$up = wp_handle_upload( $f, array( 'test_form' => false ) );
		if ( isset( $up['error'] ) || empty( $up['file'] ) ) {
			wp_safe_redirect( add_query_arg( 'uturn_msg', 'file-error', $back ) );
			exit;
		}
		$fid = wp_insert_attachment( array( 'post_mime_type' => $up['type'], 'post_title' => sanitize_file_name( $f['name'] ), 'post_status' => 'inherit' ), $up['file'], 0 );
		if ( $fid && ! is_wp_error( $fid ) ) {
			wp_update_attachment_metadata( $fid, wp_generate_attachment_metadata( $fid, $up['file'] ) );
		}
	}
	if ( $text === '' && ! $fid ) {
		$ex = uturn_submission_of( $aid, $uid );
		if ( ! $ex ) {
			wp_safe_redirect( add_query_arg( 'uturn_msg', 'submit-error', $back ) );
			exit;
		}
	}
	$ex = uturn_submission_of( $aid, $uid );
	$stu = get_userdata( $uid );
	$meta = array( '_ut_assignment_id' => $aid, '_ut_text' => $text );
	if ( $fid ) {
		$meta['_ut_file_id'] = $fid;
	}
	if ( $ex ) {
		/* Re-submit clears the old grade — teacher re-checks. */
		foreach ( $meta as $k => $v ) {
			update_post_meta( $ex->ID, $k, $v );
		}
		delete_post_meta( $ex->ID, '_ut_marks' );
		delete_post_meta( $ex->ID, '_ut_feedback' );
		wp_update_post( array( 'ID' => $ex->ID, 'post_date' => current_time( 'mysql' ), 'post_date_gmt' => current_time( 'mysql', 1 ) ) );
	} else {
		wp_insert_post(
			array(
				'post_type' => 'ut_submission', 'post_status' => 'publish',
				'post_title' => wp_slash( ( $stu ? $stu->display_name : '' ) . ' — ' . $a->post_title ),
				'post_author' => $uid, 'meta_input' => $meta,
			)
		);
	}
	wp_safe_redirect( add_query_arg( 'uturn_msg', 'submitted', $back ) );
	exit;
}

/* ================= 4. Teacher: my classes ================= */
function uturn_dash_my_classes() {
	if ( function_exists( 'uturn_dash_erp_or_lock' ) && ! uturn_dash_erp_or_lock() ) {
		return;
	}
	$map = uturn_teacher_classes( get_current_user_id() );
	if ( ! $map ) {
		echo '<div class="utd-card"><p class="utd-muted">তোমাকে এখনও কোনো শ্রেণিতে যুক্ত করা হয়নি — অফিসে জানাও।</p></div>';
		return;
	}
	$bn = function_exists( 'uturn_bn' ) ? 'uturn_bn' : 'strval';
	echo '<div class="utd-card"><table class="widefat striped" aria-label="আমার শ্রেণি"><thead><tr><th scope="col">শ্রেণি</th><th scope="col">তোমার ভূমিকা</th><th scope="col">শিক্ষার্থী</th><th scope="col"></th></tr></thead><tbody>';
	foreach ( $map as $cls => $roles ) {
		$n = function_exists( 'uturn_class_students' ) ? count( uturn_class_students( $cls ) ) : 0;
		echo '<tr><td><b>' . esc_html( $cls ) . '</b></td><td>' . esc_html( implode( ', ', $roles ) ) . '</td><td>' . esc_html( call_user_func( $bn, $n ) ) . ' জন</td>';
		echo '<td><a href="' . esc_url( uturn_dash_view_url( 'routine-view', array( 'cls' => $cls ) ) ) . '">রুটিন</a> · <a href="' . esc_url( uturn_dash_view_url( 'take-attendance', array( 'att_class' => $cls ) ) ) . '">হাজিরা</a></td></tr>';
	}
	echo '</tbody></table></div>';
}

/* ================= 5. Admit-card builder (Bangla, printable) ================= */
function uturn_dash_admit_cards() {
	if ( function_exists( 'uturn_dash_erp_or_lock' ) && ! uturn_dash_erp_or_lock() ) {
		return;
	}
	$exam = isset( $_GET['exam'] ) ? sanitize_text_field( wp_unslash( $_GET['exam'] ) ) : '';
	$cls = isset( $_GET['cls'] ) ? sanitize_text_field( wp_unslash( $_GET['cls'] ) ) : '';
	$etype = isset( $_GET['etype'] ) ? sanitize_text_field( wp_unslash( $_GET['etype'] ) ) : '';
	$scope = function_exists( 'uturn_scoped_classes' ) ? uturn_scoped_classes() : null;
	if ( is_array( $scope ) && '' !== $cls && ! in_array( $cls, $scope, true ) ) {
		echo '<div class="utd-card"><p class="utd-muted">⚠️ এই শ্রেণি আপনার নির্ধারিত তালিকায় নেই।</p></div>';
		return;
	}
	echo '<div class="utd-card noprint"><form method="get" action="' . esc_url( uturn_dash_url() ) . '"><input type="hidden" name="view" value="admit-cards">';
	echo '<table class="form-table"><tr><th scope="row">পরীক্ষার নাম *</th><td><input type="text" class="large-text" name="exam" value="' . esc_attr( $exam ) . '" placeholder="যেমন: বার্ষিক পরীক্ষা ২০২৬" required></td></tr>';
	echo '<tr><th scope="row">শ্রেণি *</th><td><select name="cls" required aria-label="শ্রেণি নির্বাচন"><option value="">— নির্বাচন করুন —</option>';
	foreach ( uturn_class_terms() as $t ) {
		if ( is_array( $scope ) && ! in_array( $t->name, $scope, true ) ) {
			continue;
		}
		echo '<option value="' . esc_attr( $t->name ) . '"' . selected( $cls, $t->name, false ) . '>' . esc_html( $t->name ) . ' (' . esc_html( function_exists( 'uturn_bn' ) ? uturn_bn( $t->count ) : $t->count ) . ')</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><th scope="row">অতিরিক্ত লাইন</th><td><input type="text" class="large-text" name="etype" value="' . esc_attr( $etype ) . '" placeholder="যেমন: সময়: সকাল ১০টা — পরীক্ষার্থীকে ৩০ মিনিট আগে আসতে হবে"></td></tr></table>';
	submit_button( 'কার্ড তৈরি করুন', 'primary', '', false );
	echo '</form></div>';
	if ( $exam === '' || $cls === '' ) {
		return;
	}
	$roster = function_exists( 'uturn_class_students' ) ? uturn_class_students( $cls ) : array();
	if ( ! $roster ) {
		echo '<div class="utd-card"><p class="utd-muted">এই শ্রেণিতে কোনো শিক্ষার্থী নেই।</p></div>';
		return;
	}
	$school = function_exists( 'uturn_opt' ) ? uturn_opt( 'school_name_bn', get_bloginfo( 'name' ) ) : get_bloginfo( 'name' );
	$addr = function_exists( 'uturn_opt' ) ? uturn_opt( 'address_bn', '' ) : '';
	$logo = get_template_directory_uri() . '/assets/images/logo.svg';
	echo '<div class="utd-card noprint"><button class="button button-primary button-large" onclick="window.print()">🖨️ প্রিন্ট করুন</button> <span class="utd-muted">মোট ' . esc_html( function_exists( 'uturn_bn' ) ? uturn_bn( count( $roster ) ) : count( $roster ) ) . 'টি কার্ড</span></div>';
	echo '<div class="utd-admits print-doc">';
	foreach ( $roster as $sp ) {
		$g = function ( $k ) use ( $sp ) { return get_post_meta( $sp->ID, $k, true ); };
		$photo = (int) $g( '_ut_photo_id' );
		$img = $photo ? wp_get_attachment_image_url( $photo, 'thumbnail' ) : '';
		echo '<div class="utd-admit"><div class="utd-admit-head"><img src="' . esc_url( $logo ) . '" alt=""><div><b>' . esc_html( $school ) . '</b><span>' . esc_html( $addr ) . '</span><i>প্রবেশপত্র — ' . esc_html( $exam ) . '</i></div>';
		echo $img ? '<img class="ph" src="' . esc_url( $img ) . '" alt="">' : '<span class="ph none">ছবি</span>';
		echo '</div><table><tr><td>নাম</td><th scope="row">' . esc_html( $sp->post_title ) . '</th><td>শ্রেণি</td><th scope="row">' . esc_html( $g( '_ut_class' ) ) . '</th></tr>';
		echo '<tr><td>রোল</td><th scope="row">' . esc_html( function_exists( 'uturn_bn' ) ? uturn_bn( $g( '_ut_roll' ) ) : $g( '_ut_roll' ) ) . '</th><td>শাখা</td><th scope="row">' . esc_html( $g( '_ut_section' ) ? $g( '_ut_section' ) : '—' ) . '</th></tr>';
		echo '<tr><td>অভিভাবক</td><th scope="row">' . esc_html( $g( '_ut_guardian' ) ? $g( '_ut_guardian' ) : '—' ) . '</th><td>রেজি নং</td><th scope="row">' . esc_html( $g( '_ut_reg' ) ? $g( '_ut_reg' ) : '—' ) . '</th></tr></table>';
		if ( $etype !== '' ) {
			echo '<p class="note">' . esc_html( $etype ) . '</p>';
		}
		echo '<div class="sigs"><span>পরীক্ষার্থীর স্বাক্ষর</span><span>শ্রেণি-শিক্ষকের স্বাক্ষর</span><span>প্রধান শিক্ষকের স্বাক্ষর ও সিল</span></div></div>';
	}
	echo '</div>';
	/* Exam schedule (if built in routine builder) prints after the cards. */
	if ( function_exists( 'uturn_routines' ) ) {
		$R = uturn_routines();
		if ( ! empty( $R['exam'] ) ) {
			echo '<div class="utd-admit sched"><b>পরীক্ষার রুটিন' . ( $R['exam_title'] !== '' ? ' — ' . esc_html( $R['exam_title'] ) : '' ) . '</b><table><tr><td><b>তারিখ</b></td><td><b>বার</b></td><td><b>বিষয়</b></td><td><b>সময়</b></td></tr>';
			foreach ( (array) $R['exam'] as $er ) {
				$er = array_values( (array) $er );
				echo '<tr><td>' . esc_html( $er[0] ?? '' ) . '</td><td>' . esc_html( $er[1] ?? '' ) . '</td><td>' . esc_html( $er[2] ?? '' ) . '</td><td>' . esc_html( $er[3] ?? '' ) . '</td></tr>';
			}
			echo '</table></div>';
		}
	}
}
/* ================= VIEW: exams ================= */
function uturn_dash_exams() {
	if ( function_exists( 'uturn_dash_erp_or_lock' ) && ! uturn_dash_erp_or_lock() ) {
		return;
	}
	uturn_academic_msg();
	$rows = function_exists( 'uturn_exam_rows' ) ? uturn_exam_rows() : array();
	$bnf = function_exists( 'uturn_bn' ) ? 'uturn_bn' : 'strval';
	echo '<div class="utd-card"><h2>📝 পরীক্ষাসমূহ</h2><p class="utd-muted">নতুন পরীক্ষা যোগ করুন (যেমন: মাসিক পরীক্ষা, প্রি-টেস্ট)। নিষ্ক্রিয় পরীক্ষা নতুন এন্ট্রিতে দেখাবে না, তবে পুরনো ফলাফল দেখা যাবে। ফলাফল আছে এমন পরীক্ষা মোছা যাবে না।</p>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="uturn_exam_save"><input type="hidden" name="ut_shell" value="1">';
	wp_nonce_field( 'uturn_exam_save' );
	echo 'পরীক্ষার নাম <input type="text" name="ename" required class="regular-text" placeholder="যেমন: মাসিক পরীক্ষা" aria-label="পরীক্ষার নাম"> ';
	submit_button( 'পরীক্ষা যোগ করুন', 'primary', '', false );
	echo '</form></div>';
	echo '<div class="utd-card"><div class="table-wrap"><table class="widefat striped" aria-label="পরীক্ষা তালিকা"><thead><tr><th scope="col">পরীক্ষা</th><th scope="col">কী</th><th scope="col">ফলাফল</th><th scope="col">অবস্থা</th><th scope="col">অ্যাকশন</th></tr></thead><tbody>';
	foreach ( $rows as $r ) {
		$slug = $r['slug'];
		$use = function_exists( 'uturn_exam_usage' ) ? uturn_exam_usage( $slug ) : 0;
		echo '<tr><td><strong>' . esc_html( $r['name'] ) . '</strong></td><td><code>' . esc_html( $slug ) . '</code></td>';
		echo '<td><span class="utd-pill">' . esc_html( call_user_func( $bnf, $use ) ) . 'টি</span></td>';
		echo '<td>' . ( $r['active'] ? '<span class="utd-pill green">সক্রিয়</span>' : '<span class="utd-pill amber">নিষ্ক্রিয়</span>' ) . '</td><td>';
		echo '<a class="button button-small" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?ut_shell=1&action=uturn_exam_toggle&slug=' . $slug ), 'uturn_exam_' . $slug ) ) . '">' . ( $r['active'] ? 'নিষ্ক্রিয়' : 'সক্রিয়' ) . '</a> ';
		if ( $use ) {
			echo '<span class="utd-muted" title="এই পরীক্ষার ফলাফল আছে">🔒 ব্যবহৃত</span>';
		} else {
			echo '<a class="button button-small utx-danger" data-confirm="“' . esc_attr( $r['name'] ) . '” মুছে ফেলবেন?" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?ut_shell=1&action=uturn_exam_delete&slug=' . $slug ), 'uturn_exam_' . $slug ) ) . '">মুছুন</a>';
		}
		echo '</td></tr>';
	}
	echo '</tbody></table></div></div>';
}
add_action( 'admin_post_uturn_exam_save', 'uturn_exam_save' );
function uturn_exam_save() {
	if ( function_exists( 'eduturn_license_require' ) ) {
		eduturn_license_require( 'erp' );
	}
	$back = function_exists( 'uturn_dash_view_url' ) ? uturn_dash_view_url( 'exams' ) : admin_url();
	if ( ! check_admin_referer( 'uturn_exam_save' ) || ! current_user_can( 'uturn_manage_academic' ) ) {
		wp_die( 'অনুমতি নেই।' );
	}
	$name = sanitize_text_field( wp_unslash( $_POST['ename'] ?? '' ) );
	if ( '' === $name ) {
		wp_safe_redirect( $back );
		exit;
	}
	$rows = uturn_exam_rows();
	$slugs = array_column( $rows, 'slug' );
	$try = sanitize_key( $name );
	if ( '' === $try || in_array( $try, $slugs, true ) ) {
		$i = count( $rows ) + 1;
		do {
			$try = 'exam-' . $i;
			$i++;
		} while ( in_array( $try, $slugs, true ) );
	}
	$rows[] = array( 'slug' => $try, 'name' => $name, 'active' => 1 );
	update_option( 'uturn_exams', $rows, false );
	wp_safe_redirect( add_query_arg( 'uturn_msg', 'exam-saved', $back ) );
	exit;
}
add_action( 'admin_post_uturn_exam_toggle', 'uturn_exam_toggle' );
function uturn_exam_toggle() {
	if ( function_exists( 'eduturn_license_require' ) ) {
		eduturn_license_require( 'erp' );
	}
	$back = function_exists( 'uturn_dash_view_url' ) ? uturn_dash_view_url( 'exams' ) : admin_url();
	$slug = sanitize_key( $_GET['slug'] ?? '' );
	if ( '' === $slug || ! current_user_can( 'uturn_manage_academic' ) || ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'uturn_exam_' . $slug ) ) {
		wp_die( 'অনুমতি নেই।' );
	}
	$rows = uturn_exam_rows();
	foreach ( $rows as &$r ) {
		if ( $r['slug'] === $slug ) {
			$r['active'] = $r['active'] ? 0 : 1;
		}
	}
	unset( $r );
	update_option( 'uturn_exams', $rows, false );
	wp_safe_redirect( add_query_arg( 'uturn_msg', 'exam-saved', $back ) );
	exit;
}
add_action( 'admin_post_uturn_exam_delete', 'uturn_exam_delete' );
function uturn_exam_delete() {
	if ( function_exists( 'eduturn_license_require' ) ) {
		eduturn_license_require( 'erp' );
	}
	$back = function_exists( 'uturn_dash_view_url' ) ? uturn_dash_view_url( 'exams' ) : admin_url();
	$slug = sanitize_key( $_GET['slug'] ?? '' );
	if ( '' === $slug || ! current_user_can( 'uturn_manage_academic' ) || ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'uturn_exam_' . $slug ) ) {
		wp_die( 'অনুমতি নেই।' );
	}
	if ( uturn_exam_usage( $slug ) ) {
		wp_safe_redirect( add_query_arg( 'uturn_msg', 'exam-used', $back ) );
		exit;
	}
	$rows = array_values( array_filter( uturn_exam_rows(), function ( $r ) use ( $slug ) { return $r['slug'] !== $slug; } ) );
	update_option( 'uturn_exams', $rows, false );
	wp_safe_redirect( add_query_arg( 'uturn_msg', 'exam-deleted', $back ) );
	exit;
}

/* ================= VIEW: grading scale ================= */
function uturn_dash_grading() {
	if ( function_exists( 'uturn_dash_erp_or_lock' ) && ! uturn_dash_erp_or_lock() ) {
		return;
	}
	if ( isset( $_GET['saved'] ) ) {
		echo '<div class="notice notice-success" role="status"><p>✅ গ্রেডিং স্কেল সংরক্ষণ করা হয়েছে। নতুন এন্ট্রি এই স্কেলে হিসাব হবে।</p></div>';
	}
	$scale = function_exists( 'uturn_grade_scale' ) ? uturn_grade_scale() : array();
	echo '<div class="utd-card"><h2>📊 গ্রেডিং স্কেল (শতকরা হার %)</h2>';
	echo '<p class="utd-muted">প্রতিটি সারিতে <b>সর্বনিম্ন শতকরা হার</b>, <b>গ্রেড</b> ও <b>পয়েন্ট</b> দিন। প্রাপ্ত নম্বরকে পূর্ণমানের শতকরায় বদলে উপর থেকে নিচে মিলিয়ে গ্রেড নির্ধারণ হবে — ৫০ বা ১০০, সব পূর্ণমানেই একই স্কেল। শেষ সারি (F) অপরিবর্তনীয় নিরাপত্তা-মান।</p>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="uturn_grading_save"><input type="hidden" name="ut_shell" value="1">';
	wp_nonce_field( 'uturn_grading_save' );
	echo '<div class="table-wrap"><table class="widefat striped" aria-label="গ্রেডিং স্কেল"><thead><tr><th scope="col">#</th><th scope="col">সর্বনিম্ন নম্বর</th><th scope="col">গ্রেড</th><th scope="col">পয়েন্ট</th></tr></thead><tbody>';
	$i = 0;
	foreach ( $scale as $r ) {
		$i++;
		$last = $i === count( $scale );
		echo '<tr><td>' . (int) $i . '</td>';
		echo '<td><input type="number" name="scale[' . (int) $i . '][min]" value="' . esc_attr( $r['min'] ) . '" min="0" max="100" step="1" style="width:7em" required' . ( $last ? ' readonly' : '' ) . '></td>';
		echo '<td><input type="text" name="scale[' . (int) $i . '][grade]" value="' . esc_attr( $r['grade'] ) . '" maxlength="4" style="width:6em" required' . ( $last ? ' readonly' : '' ) . '></td>';
		echo '<td><input type="number" name="scale[' . (int) $i . '][point]" value="' . esc_attr( $r['point'] ) . '" min="0" max="5" step="0.25" style="width:7em" required' . ( $last ? ' readonly' : '' ) . '></td></tr>';
	}
	echo '</tbody></table></div>';
	$rank_by = get_option( 'uturn_rank_by', 'total' ) === 'gpa' ? 'gpa' : 'total';
	echo '<p><label><b>🏆 মেধাক্রম হবে</b> <select name="rank_by"><option value="total"' . selected( $rank_by, 'total', false ) . '>মোট নম্বরের ভিত্তিতে</option><option value="gpa"' . selected( $rank_by, 'gpa', false ) . '>GPA-এর ভিত্তিতে</option></select></label> <span class="utd-muted">(পরের প্রকাশ/পুনর্গণনা থেকে কার্যকর)</span></p>';
	echo '<p><button class="button button-primary" type="submit">💾 স্কেল সংরক্ষণ</button> ';
	echo '<button class="button" type="submit" name="reset" value="1" onclick="return confirm(\'বোর্ড-মান স্কেলে ফিরে যাবেন?\')">↩️ ডিফল্টে ফেরত</button></p></form></div>';
}

add_action( 'admin_post_uturn_grading_save', 'uturn_grading_save' );
function uturn_grading_save() {
	if ( function_exists( 'eduturn_license_require' ) ) {
		eduturn_license_require( 'erp' );
	}
	$back = function_exists( 'uturn_dash_view_url' ) ? uturn_dash_view_url( 'grading' ) : admin_url();
	if ( ! check_admin_referer( 'uturn_grading_save' ) || ( ! current_user_can( 'uturn_manage_academic' ) && ! current_user_can( 'manage_options' ) ) ) {
		wp_die( 'Unauthorized.' );
	}
	if ( ! empty( $_POST['reset'] ) ) {
		delete_option( 'uturn_grade_scale' );
		wp_safe_redirect( add_query_arg( 'saved', '1', $back ) );
		exit;
	}
	$rank_by = isset( $_POST['rank_by'] ) && $_POST['rank_by'] === 'gpa' ? 'gpa' : 'total';
	update_option( 'uturn_rank_by', $rank_by, false );
	$rows = isset( $_POST['scale'] ) && is_array( $_POST['scale'] ) ? array_values( $_POST['scale'] ) : array();
	$out = array();
	foreach ( $rows as $r ) {
		if ( ! is_array( $r ) ) {
			continue;
		}
		$mn = isset( $r['min'] ) ? max( 0, min( 100, (int) $r['min'] ) ) : 0;
		$gr = isset( $r['grade'] ) ? substr( sanitize_text_field( wp_unslash( $r['grade'] ) ), 0, 4 ) : '';
		$pt = isset( $r['point'] ) ? max( 0, min( 5, round( (float) $r['point'], 2 ) ) ) : 0;
		if ( '' === $gr ) {
			continue;
		}
		$out[] = array( 'min' => $mn, 'grade' => $gr, 'point' => $pt );
	}
	if ( count( $out ) < 2 ) {
		wp_safe_redirect( $back );
		exit;
	}
	usort( $out, function ( $a, $b ) { return $b['min'] <=> $a['min']; } );
	$last = end( $out );
	if ( (int) $last['min'] !== 0 ) {
		$out[] = array( 'min' => 0, 'grade' => 'F', 'point' => 0.00 );
	}
	update_option( 'uturn_grade_scale', $out, false );
	wp_safe_redirect( add_query_arg( 'saved', '1', $back ) );
	exit;
}
