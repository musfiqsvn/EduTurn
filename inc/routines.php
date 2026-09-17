<?php
/**
 * EduTurn — Routine builder: class-period grid + exam schedule,
 * stored in one `uturn_routines` option. Zero plugins.
 * Cap: uturn_manage_routines (Super Admin + School Admin).
 */

defined( 'ABSPATH' ) || exit;

function uturn_routine_days() {
	return array( 'রবি', 'সোম', 'মঙ্গল', 'বুধ', 'বৃহস্পতি' );
}

/** Frontend getter — always returns a complete shape. */
function uturn_routines() {
	$r = get_option( 'uturn_routines', array() );
	if ( ! is_array( $r ) ) {
		$r = array();
	}
	return array(
		'periods'    => isset( $r['periods'] ) ? array_values( (array) $r['periods'] ) : array(),
		'times'      => isset( $r['times'] ) ? array_values( (array) $r['times'] ) : array(),
		'classes'    => isset( $r['classes'] ) ? (array) $r['classes'] : array(),
		'teachers'   => isset( $r['teachers'] ) ? (array) $r['teachers'] : array(),
		'updated'    => isset( $r['updated'] ) ? $r['updated'] : '',
		'exam_title' => isset( $r['exam_title'] ) && '' !== $r['exam_title'] ? $r['exam_title'] : uturn_opt( 'exam_title' ),
		'exam'       => isset( $r['exam'] ) ? array_values( (array) $r['exam'] ) : array(),
	);
}

/* ---------- Admin: builder page ---------- */
add_action( 'admin_menu', 'uturn_routines_menu', 31 );
function uturn_routines_menu() {
	add_submenu_page( 'eduturn', 'রুটিন বিল্ডার', 'রুটিন বিল্ডার', 'uturn_manage_routines', 'eduturn-routines', 'uturn_routines_page' );
}

function uturn_routines_page() {
	eduturn_license_require( 'erp' );
	if ( ! current_user_can( 'uturn_manage_routines' ) ) {
		wp_die( 'অনুমতি নেই।' );
	}
	$r = uturn_routines();
	$days = uturn_routine_days();
	$periods = $r['periods'] ? $r['periods'] : array( '১ম', '২য়', '৩য়', '৪র্থ', '৫ম', '৬ষ্ঠ', '৭ম' );
	$times = $r['times'] ? $r['times'] : array_fill( 0, count( $periods ), '' );
	$classes = $r['classes'] ? $r['classes'] : array( 'ষষ্ঠ' => array() );
	echo '<div class="wrap"><h1>রুটিন বিল্ডার</h1>';
	if ( isset( $_GET['saved'] ) ) {
		echo '<div class="notice notice-success" role="status"><p>রুটিন সংরক্ষিত হয়েছে।</p></div>';
	}
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">'
		. '<input type="hidden" name="action" value="uturn_routines_save">' . wp_nonce_field( 'uturn_routines_save', '_wpnonce', true, false );
	echo '<h2>পিরিয়ড</h2><table class="widefat striped" id="utPerTbl" style="max-width:760px"><thead><tr><th scope="col">#</th><th scope="col">পিরিয়ড নাম</th><th scope="col">সময়</th><th scope="col"></th></tr></thead><tbody>';
	foreach ( $periods as $i => $pn ) {
		echo '<tr><td>' . ( $i + 1 ) . '</td>'
			. '<td><input type="text" name="periods[]" value="' . esc_attr( $pn ) . '" style="width:100%"></td>'
			. '<td><input type="text" name="times[]" value="' . esc_attr( isset( $times[ $i ] ) ? $times[ $i ] : '' ) . '" placeholder="যেমন: ১০:০০–১০:৪৫" style="width:100%"></td>'
			. '<td><button type="button" class="button utPerDel">✕</button></td></tr>';
	}
	echo '</tbody></table><p><button type="button" class="button" id="utPerAdd">＋ পিরিয়ড যোগ করুন</button> <span class="description">পিরিয়ড যোগ/কমালে সংরক্ষণ করুন — গ্রিড নিজে নিজে মিলে যাবে।</span></p>';
	echo "<script>(function(){var t=document.getElementById('utPerTbl');if(!t)return;var ren=function(){Array.prototype.forEach.call(t.querySelectorAll('tbody tr'),function(r,i){r.cells[0].textContent=i+1;});};document.getElementById('utPerAdd').addEventListener('click',function(){var b=t.querySelector('tbody');var r=b.rows[b.rows.length-1].cloneNode(true);r.querySelectorAll('input').forEach(function(i){i.value='';});b.appendChild(r);ren();});t.addEventListener('click',function(e){if(e.target.classList.contains('utPerDel')){var b=t.querySelector('tbody');if(b.rows.length>1){e.target.closest('tr').remove();ren();}}});})();</script>";
	echo '<h2>শ্রেণিভিত্তিক ক্লাস রুটিন</h2>';
	$all_terms = function_exists( 'uturn_class_terms' ) ? uturn_class_terms() : get_terms( array( 'taxonomy' => 'ut_class', 'hide_empty' => false ) );
	echo '<p><label>নতুন শ্রেণি যোগ: <input type="text" name="new_class" list="ut-rt-classes" placeholder="যেমন: সপ্তম"> (সংরক্ষণ করলে খালি গ্রিড তৈরি হবে)</label>';
	if ( ! is_wp_error( $all_terms ) && $all_terms ) {
		echo '<datalist id="ut-rt-classes">';
		foreach ( $all_terms as $tt ) {
			echo '<option value="' . esc_attr( $tt->name ) . '">';
		}
		echo '</datalist>';
	}
	echo '</p>';
	echo '<p><label>সর্বশেষ হালনাগাদ লেবেল: <input type="text" name="updated" value="' . esc_attr( $r['updated'] ) . '"></label></p>';
	$all_teachers = function_exists( 'uturn_teachers_list' ) ? uturn_teachers_list() : array();
	$tmap_all = isset( $r['teachers'] ) ? (array) $r['teachers'] : array();
	foreach ( $classes as $cls => $rows ) {
		$map = array();
		foreach ( (array) $rows as $row ) {
			$row = array_values( (array) $row );
			if ( $row ) {
				$map[ $row[0] ] = array_slice( $row, 1 );
			}
		}
		/* This class's subject-teacher links (from Class Manager) come first. */
		$cterm = get_term_by( 'name', $cls, 'ut_class' );
		$link_ids = array();
		if ( $cterm && function_exists( 'uturn_class_subjects' ) ) {
			foreach ( uturn_class_subjects( $cterm->term_id ) as $s ) {
				if ( (int) ( $s['teacher'] ?? 0 ) ) {
					$link_ids[ (int) $s['teacher'] ] = $s['subject'];
				}
			}
		}
		echo '<h3>' . esc_html( $cls ) . ' <label style="font-weight:400;font-size:12px"><input type="checkbox" name="del_class[]" value="' . esc_attr( $cls ) . '"> মুছুন</label></h3>';
		echo '<div class="table-wrap"><table class="widefat striped" aria-label="সাপ্তাহিক রুটিন গ্রিড"><thead><tr><th scope="col">দিন</th>';
		foreach ( $periods as $pn ) {
			echo '<th scope="col">' . esc_html( $pn ) . '</th>';
		}
		echo '</tr></thead><tbody>';
		foreach ( $days as $d ) {
			echo '<tr><td><strong>' . esc_html( $d ) . '</strong></td>';
			for ( $i = 0; $i < count( $periods ); $i++ ) {
				$v = isset( $map[ $d ][ $i ] ) ? $map[ $d ][ $i ] : '';
				$tv = isset( $tmap_all[ $cls ][ $d ][ $i ] ) ? (int) $tmap_all[ $cls ][ $d ][ $i ] : 0;
				echo '<td><input type="text" name="grid[' . esc_attr( $cls ) . '][' . esc_attr( $d ) . '][]" value="' . esc_attr( $v ) . '" placeholder="বিষয়" style="width:100%;margin-bottom:2px">';
				echo '<select name="tgrid[' . esc_attr( $cls ) . '][' . esc_attr( $d ) . '][]" style="width:100%;max-width:100%"><option value="0">— শিক্ষক —</option>';
				foreach ( $link_ids as $lid => $lsub ) {
					$nm = isset( $all_teachers[ $lid ] ) ? $all_teachers[ $lid ] : '';
					if ( $nm === '' ) {
						continue;
					}
					echo '<option value="' . (int) $lid . '"' . selected( $tv, $lid, false ) . '>★ ' . esc_html( $nm . ' (' . $lsub . ')' ) . '</option>';
				}
				foreach ( $all_teachers as $tid2 => $tnm ) {
					if ( isset( $link_ids[ $tid2 ] ) ) {
						continue;
					}
					echo '<option value="' . (int) $tid2 . '"' . selected( $tv, $tid2, false ) . '>' . esc_html( $tnm ) . '</option>';
				}
				echo '</select></td>';
			}
			echo '</tr>';
		}
		echo '</tbody></table></div>';
	}
	echo '<h2>পরীক্ষার রুটিন</h2><table class="form-table">';
	echo '<tr><th scope="row">শিরোনাম</th><td><input type="text" name="exam_title" value="' . esc_attr( $r['exam_title'] ) . '" class="large-text"></td></tr>';
	$exam_lines = array();
	foreach ( (array) $r['exam'] as $er ) {
		$er = array_values( (array) $er );
		$exam_lines[] = implode( '|', $er );
	}
	echo '<tr><th scope="row">সারি (প্রতিটি পরীক্ষা আলাদা সারিতে)</th><td>';
	uturn_row_editor( 'exam_grid', implode( "\n", $exam_lines ), uturn_rows_columns( 'exam' ), '＋ পরীক্ষা যোগ করুন' );
	echo '</td></tr>';
	echo '</table><p><button class="button button-primary button-large">রুটিন সংরক্ষণ করুন</button></p></form></div>';
}

add_action( 'admin_post_uturn_routines_save', 'uturn_routines_save' );
function uturn_routines_save() {
	eduturn_license_require( 'erp' );
	if ( ! check_admin_referer( 'uturn_routines_save' ) || ! current_user_can( 'uturn_manage_routines' ) ) {
		wp_die( 'অনুমতি নেই।' );
	}
	$in = wp_unslash( $_POST );
	$periods = array_values( array_filter( array_map( 'sanitize_text_field', (array) ( isset( $in['periods'] ) ? $in['periods'] : array() ) ) ) );
	$times = array_values( array_map( 'sanitize_text_field', (array) ( isset( $in['times'] ) ? $in['times'] : array() ) ) );
	$classes = array();
	$days = uturn_routine_days();
	$del = array_map( 'sanitize_text_field', (array) ( isset( $in['del_class'] ) ? $in['del_class'] : array() ) );
	foreach ( (array) ( isset( $in['grid'] ) ? $in['grid'] : array() ) as $cls => $dayrows ) {
		$cls = sanitize_text_field( $cls );
		if ( in_array( $cls, $del, true ) ) {
			continue;
		}
		$rows = array();
		foreach ( $days as $d ) {
			$cells = isset( $dayrows[ $d ] ) ? array_values( array_map( 'sanitize_text_field', (array) $dayrows[ $d ] ) ) : array_fill( 0, count( $periods ), '' );
			$rows[] = array_merge( array( $d ), $cells );
		}
		$classes[ $cls ] = $rows;
	}
	if ( ! empty( $in['new_class'] ) ) {
		$nc = sanitize_text_field( $in['new_class'] );
		if ( ! isset( $classes[ $nc ] ) ) {
			$rows = array();
			foreach ( $days as $d ) {
				$rows[] = array_merge( array( $d ), array_fill( 0, count( $periods ), '' ) );
			}
			$classes[ $nc ] = $rows;
		}
	}
	$tmap = array();
	foreach ( (array) ( isset( $in['tgrid'] ) ? $in['tgrid'] : array() ) as $cls => $dayrows ) {
		$cls = sanitize_text_field( $cls );
		if ( in_array( $cls, $del, true ) ) {
			continue;
		}
		foreach ( $days as $d ) {
			$tmap[ $cls ][ $d ] = array_values( array_map( 'absint', (array) ( isset( $dayrows[ $d ] ) ? $dayrows[ $d ] : array() ) ) );
		}
	}
	$exam = array();
	if ( isset( $in['exam_grid'] ) && is_array( $in['exam_grid'] ) ) {
		foreach ( (array) $in['exam_grid'] as $grow ) {
			$cells = array();
			foreach ( array_slice( array_values( (array) $grow ), 0, 4 ) as $gc ) {
				$cells[] = str_replace( '|', '', sanitize_text_field( (string) $gc ) );
			}
			$cells = array_slice( array_pad( $cells, 4, '' ), 0, 4 );
			if ( '' !== $cells[0] ) {
				$exam[] = $cells;
			}
		}
	} else {
		foreach ( explode( "\n", isset( $in['exam_rows'] ) ? $in['exam_rows'] : '' ) as $ln ) {
			$parts = array_map( 'trim', explode( '|', $ln ) );
			if ( count( $parts ) >= 4 && '' !== $parts[0] ) {
				$exam[] = array_slice( $parts, 0, 4 );
			}
		}
	}
	update_option(
		'uturn_routines',
		array(
			'periods'    => $periods,
			'times'      => $times,
			'classes'    => $classes,
			'teachers'   => $tmap,
			'updated'    => sanitize_text_field( isset( $in['updated'] ) ? $in['updated'] : '' ),
			'exam_title' => sanitize_text_field( isset( $in['exam_title'] ) ? $in['exam_title'] : '' ),
			'exam'       => $exam,
		)
	);
	wp_safe_redirect( uturn_back( admin_url( 'admin.php?page=eduturn-routines&saved=1' ) ) );
	exit;
}
