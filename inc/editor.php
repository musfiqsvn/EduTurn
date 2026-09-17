<?php
/**
 * EduTurn — Visual homepage editor.
 * Split screen: section controls (left) + live website preview (right).
 * Saves through the existing options row + sections_order engine —
 * zero new storage, zero new routes on the frontend.
 */

defined( 'ABSPATH' ) || exit;

/** Editable option fields per homepage section. */
function uturn_editor_fields() {
	return array(
		'hero' => array(
			array( 'k' => 'hero_bg_id', 't' => 'media', 'l' => 'পেছনের ছবি' ),
			array( 'k' => 'hero_eyebrow', 't' => 'text', 'l' => 'ব্যাজ লেখা' ),
			array( 'k' => 'hero_title', 't' => 'text', 'l' => 'শিরোনাম' ),
			array( 'k' => 'hero_lead', 't' => 'area', 'l' => 'সাবটেক্সট' ),
			array( 'k' => 'hero_meta', 't' => 'rows', 'l' => 'পরিসংখ্যান' ),
		),
		'about' => array(
			array( 'k' => 'established', 't' => 'text', 'l' => 'প্রতিষ্ঠার বছর' ),
		),
		'stats' => array(
			array( 'k' => 'stats_lines', 't' => 'rows', 'l' => 'পরিসংখ্যান' ),
		),
		'principal' => array(
			array( 'k' => 'principal_name', 't' => 'text', 'l' => 'নাম' ),
			array( 'k' => 'principal_title', 't' => 'text', 'l' => 'পদবি' ),
			array( 'k' => 'principal_photo', 't' => 'text', 'l' => 'ছবির URL' ),
			array( 'k' => 'principal_msg', 't' => 'area', 'l' => 'সংক্ষিপ্ত বাণী' ),
		),
		'admission' => array(
			array( 'k' => 'adm_session', 't' => 'text', 'l' => 'শিক্ষাবর্ষ' ),
			array( 'k' => 'deadline_bn', 't' => 'text', 'l' => 'শেষ তারিখ (বাংলা)' ),
			array( 'k' => 'adm_deadline', 't' => 'text', 'l' => 'কাউন্টডাউন (YYYY-MM-DD)' ),
		),
		'achievements' => array(
			array( 'k' => 'board_strip', 't' => 'area', 'l' => 'বোর্ড ফলাফল স্ট্রিপ' ),
		),
		'notices' => array(
			array( 'k' => 'ticker_count', 't' => 'text', 'l' => 'টিকারে নোটিশ সংখ্যা' ),
		),
		'contact' => array(
			array( 'k' => 'phone', 't' => 'text', 'l' => 'ফোন' ),
			array( 'k' => 'email', 't' => 'text', 'l' => 'ইমেইল' ),
			array( 'k' => 'address', 't' => 'text', 'l' => 'ঠিকানা' ),
			array( 'k' => 'hours', 't' => 'text', 'l' => 'অফিস সময়' ),
			array( 'k' => 'map_embed', 't' => 'area', 'l' => 'গুগল ম্যাপ এমবেড' ),
		),
	);
}

/** Content-driven sections link to their manager instead of text fields. */
function uturn_editor_links() {
	return array(
		'notices' => array( 'notices', 'নোটিশ ম্যানেজ করুন' ),
		'news' => array( 'news', 'সংবাদ ম্যানেজ করুন' ),
		'events' => array( 'events', 'ইভেন্ট ম্যানেজ করুন' ),
		'gallery' => array( 'albums', 'গ্যালারি ম্যানেজ করুন' ),
		'testimonials' => array( 'testimonials', 'মতামত ম্যানেজ করুন' ),
		'teachers' => array( 'teachers', 'শিক্ষক ম্যানেজ করুন' ),
		'board' => array( 'board', 'পর্ষদ ম্যানেজ করুন' ),
		'stars' => array( 'students', 'কৃতি শিক্ষার্থী (⭐ ফিল্ড)' ),
	);
}

function uturn_editor_pool() {
	$defs = uturn_section_defs();
	$pool = array();
	foreach ( glob( get_template_directory() . '/template-parts/home/*.php' ) as $f ) {
		$id = basename( $f, '.php' );
		if ( ! isset( $defs[ $id ] ) ) {
			$pool[ $id ] = ucfirst( $id ) . ' (কাস্টম)';
		}
	}
	return $pool;
}

function uturn_dash_editor() {
	if ( ! current_user_can( 'uturn_manage_settings' ) ) {
		echo '<div class="notice notice-error" role="alert"><p>অ্যাক্সেস নেই।</p></div>';
		return;
	}
	if ( isset( $_GET['saved'] ) ) {
		echo '<div class="notice notice-success" role="status"><p>✅ সংরক্ষণ করা হয়েছে — প্রিভিউ হালনাগাদ।</p></div>';
	}
	$o = uturn_opts();
	$defs = uturn_section_defs();
	$pool = uturn_editor_pool();
	$fields = uturn_editor_fields();
	$links = uturn_editor_links();
	$cur = array();
	foreach ( (array) uturn_opt( 'sections_order', array() ) as $i => $row ) {
		if ( isset( $row['id'] ) && isset( $defs[ $row['id'] ] ) ) {
			$cur[ $row['id'] ] = $i + 1;
		}
	}
	$has_saved = ! empty( $cur );
	$i = 0;
	$ordered = array();
	foreach ( $defs as $id => $label ) {
		$i++;
		$ordered[ $id ] = array( 'label' => $label, 'vis' => $has_saved ? isset( $cur[ $id ] ) : true, 'pos' => isset( $cur[ $id ] ) ? $cur[ $id ] : $i );
	}
	uasort( $ordered, function ( $a, $b ) { return $a['pos'] - $b['pos']; } );

	echo '<div class="utx-ed">';
	/* LEFT: controls */
	echo '<form class="utx-ed-left" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	echo '<input type="hidden" name="action" value="uturn_editor_save"><input type="hidden" name="ut_shell" value="1">';
	wp_nonce_field( 'uturn_editor_save' );
	echo '<div class="utx-ed-note">👁️ ডানে লাইভ ওয়েবসাইট — লেখা বদলে <b>সংরক্ষণ</b> চাপুন, প্রিভিউ নিজে নিজে হালনাগাদ হবে।</div>';
	echo '<div class="utx-ed-list">';
	foreach ( $ordered as $id => $r ) {
		echo '<details class="utx-ed-sec" data-sec="' . esc_attr( $id ) . '"' . ( $id === 'hero' ? ' open' : '' ) . '>';
		echo '<summary><span class="utx-ed-move"><button type="button" data-ed-move="up" title="উপরে">▲</button><button type="button" data-ed-move="down" title="নিচে">▼</button></span>';
		echo '<label class="utx-eye" title="দেখাও/লুকাও"><input type="checkbox" name="sec_vis[' . esc_attr( $id ) . ']" value="1"' . checked( $r['vis'], true, false ) . '><span>👁️</span></label>';
		echo '<input type="hidden" name="sec_pos[' . esc_attr( $id ) . ']" value="' . (int) $r['pos'] . '">';
		echo '<b>' . esc_html( $r['label'] ) . '</b>' . ( $r['vis'] ? '' : ' <span class="utd-pill amber">লুকানো</span>' ) . '</summary>';
		echo '<div class="utx-ed-body">';
		foreach ( ( $fields[ $id ] ?? array() ) as $f ) {
			$val = $o[ $f['k'] ] ?? '';
			$nm = esc_attr( UTURN_OPT . '[' . $f['k'] . ']' );
			if ( $f['t'] === 'media' ) {
				$prev = $val ? wp_get_attachment_image( (int) $val, 'thumbnail', false, array( 'style' => 'max-width:90px;height:auto;display:block;margin-bottom:6px' ) ) : '';
				echo '<p><label>' . esc_html( $f['l'] ) . '</label><br><span class="eduturn-media-prev">' . $prev . '</span><input type="hidden" class="eduturn-media-id utx-ed-field" name="' . $nm . '" value="' . (int) $val . '"> <button type="button" class="button button-small eduturn-media-btn">ছবি বেছে নিন</button> <button type="button" class="button-link eduturn-media-clear">সরান</button></p>';
			} elseif ( $f['t'] === 'rows' ) {
				$rcols = uturn_rows_columns( $f['k'] );
				if ( $rcols ) {
					echo '<div class="utx-ed-rows"><p style="margin:0 0 4px;font-weight:600">' . esc_html( $f['l'] ) . '</p>';
					uturn_row_editor( UTURN_OPT . '[' . $f['k'] . ']', $val, $rcols );
					echo '</div>';
				} else {
					echo '<p><label>' . esc_html( $f['l'] ) . '<textarea class="large-text utx-ed-field" rows="3" name="' . $nm . '">' . esc_textarea( $val ) . '</textarea></label></p>';
				}
			} elseif ( $f['t'] === 'area' ) {
				echo '<p><label>' . esc_html( $f['l'] ) . '<textarea class="large-text utx-ed-field" rows="3" name="' . $nm . '">' . esc_textarea( $val ) . '</textarea></label></p>';
			} else {
				echo '<p><label>' . esc_html( $f['l'] ) . '<input type="text" class="large-text utx-ed-field" name="' . $nm . '" value="' . esc_attr( $val ) . '"></label></p>';
			}
		}
		if ( isset( $links[ $id ] ) ) {
			echo '<p><a class="button button-small" href="' . esc_url( uturn_dash_view_url( $links[ $id ][0] ) ) . '">' . esc_html( $links[ $id ][1] ) . ' →</a></p>';
		}
		if ( empty( $fields[ $id ] ) && ! isset( $links[ $id ] ) ) {
			echo '<p class="utd-muted">এই সেকশনে শুধু দেখাও/লুকাও + ক্রম বদলানো যায়।</p>';
		}
		echo '</div></details>';
	}
	echo '</div>';
	if ( $pool ) {
		echo '<div class="utd-card"><h3>➕ সেকশন যোগ করুন</h3>';
		foreach ( $pool as $id => $label ) {
			echo '<label style="display:block"><input type="checkbox" name="sec_add[]" value="' . esc_attr( $id ) . '"> ' . esc_html( $label ) . '</label>';
		}
		echo '<p class="utd-muted">টিক দিয়ে সংরক্ষণ করুন — শেষে যোগ হবে।</p></div>';
	}
	echo '<div class="utx-ed-savebar"><button class="button button-primary button-large" type="submit">💾 সংরক্ষণ ও প্রিভিউ হালনাগাদ</button></div>';
	echo '</form>';
	/* RIGHT: live preview */
	echo '<div class="utx-ed-right"><div class="utx-ed-devices"><b>লাইভ প্রিভিউ</b><span class="utx-sep"></span>';
	echo '<button type="button" class="button button-small on" data-device="desktop">🖥️ ডেস্কটপ</button> ';
	echo '<button type="button" class="button button-small" data-device="tablet">📱 ট্যাবলেট</button> ';
	echo '<button type="button" class="button button-small" data-device="mobile">📲 মোবাইল</button> ';
	echo '<a class="button button-small" href="' . esc_url( home_url( '/' ) ) . '" target="_blank" rel="noopener">↗ নতুন ট্যাবে</a></div>';
	echo '<div class="utx-ed-framewrap"><iframe id="utx-ed-frame" src="' . esc_url( add_query_arg( 'uturn_preview', time(), home_url( '/' ) ) ) . '" title="লাইভ প্রিভিউ"></iframe></div></div>';
	echo '</div>';
}

add_action( 'admin_post_uturn_editor_save', 'uturn_editor_save' );
function uturn_editor_save() {
	if ( ! current_user_can( 'uturn_manage_settings' ) || ! check_admin_referer( 'uturn_editor_save' ) ) {
		wp_die( 'Unauthorized.' );
	}
	$in = isset( $_POST[ UTURN_OPT ] ) && is_array( $_POST[ UTURN_OPT ] ) ? wp_unslash( $_POST[ UTURN_OPT ] ) : array();
	$out = (array) get_option( UTURN_OPT, array() );
	$allowed_text = array( 'hero_eyebrow', 'hero_title', 'established', 'principal_name', 'principal_title', 'principal_photo', 'adm_session', 'deadline_bn', 'adm_deadline', 'ticker_count', 'phone', 'email', 'address', 'hours' );
	$allowed_area = array( 'hero_lead', 'hero_meta', 'stats_lines', 'principal_msg', 'board_strip', 'map_embed' );
	foreach ( $allowed_text as $k ) {
		if ( isset( $in[ $k ] ) ) {
			$out[ $k ] = sanitize_text_field( $in[ $k ] );
		}
	}
	foreach ( $allowed_area as $k ) {
		if ( ! isset( $in[ $k ] ) ) {
			continue;
		}
		$rcols = uturn_rows_columns( $k );
		if ( $rcols && is_array( $in[ $k ] ) ) {
			$out[ $k ] = uturn_rows_to_text( $in[ $k ], count( $rcols ) );
		} else {
			$out[ $k ] = $k === 'map_embed' ? wp_kses_post( $in[ $k ] ) : sanitize_textarea_field( $in[ $k ] );
		}
	}
	if ( isset( $in['hero_bg_id'] ) ) {
		$out['hero_bg_id'] = absint( $in['hero_bg_id'] );
	}
	/* sections order + visibility (+ pool adds) */
	$defs = uturn_section_defs();
	foreach ( (array) ( $_POST['sec_add'] ?? array() ) as $aid ) {
		$aid = sanitize_key( $aid );
		if ( $aid && file_exists( get_template_directory() . '/template-parts/home/' . $aid . '.php' ) && ! isset( $defs[ $aid ] ) ) {
			$defs[ $aid ] = ucfirst( $aid );
		}
	}
	$vis = isset( $_POST['sec_vis'] ) ? (array) $_POST['sec_vis'] : array();
	$pos = isset( $_POST['sec_pos'] ) ? (array) $_POST['sec_pos'] : array();
	/* Pool adds come without vis/pos — force visible at the end. */
	foreach ( (array) ( $_POST['sec_add'] ?? array() ) as $aid ) {
		$aid = sanitize_key( $aid );
		if ( isset( $defs[ $aid ] ) ) {
			$vis[ $aid ] = '1';
			$pos[ $aid ] = 500;
		}
	}
	$order = array();
	foreach ( $defs as $id => $label ) {
		if ( ! empty( $vis[ $id ] ) ) {
			$order[] = array( 'id' => $id, 'pos' => isset( $pos[ $id ] ) ? (int) $pos[ $id ] : 99 );
		}
	}
	usort( $order, function ( $a, $b ) { return $a['pos'] - $b['pos']; } );
	$out['sections_order'] = array_map( function ( $r ) { return array( 'id' => $r['id'], 'visible' => 1 ); }, $order );
	update_option( UTURN_OPT, $out );
	wp_safe_redirect( uturn_dash_view_url( 'editor', array( 'saved' => 1 ) ) );
	exit;
}
