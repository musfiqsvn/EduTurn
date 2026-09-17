<?php
/**
 * EduTurn — পরিচালনা পর্ষদ (Governing Body) manager.
 * CPT ut_board; add/edit/delete reuse the generic CRUD engine,
 * this file adds ordering, one-click status, and a live frontend preview.
 */

defined( 'ABSPATH' ) || exit;

function uturn_board_members( $active_only = true ) {
	$q = new WP_Query(
		array(
			'post_type' => 'ut_board', 'posts_per_page' => -1,
			'post_status' => $active_only ? 'publish' : array( 'publish', 'draft' ),
			'orderby' => 'title', 'order' => 'ASC', 'no_found_rows' => true,
		)
	);
	/* PHP sort: members without an order still list (meta_key join would drop them). */
	$posts = $q->posts;
	usort(
		$posts,
		function ( $a, $b ) {
			$oa = get_post_meta( $a->ID, '_ut_order', true );
			$ob = get_post_meta( $b->ID, '_ut_order', true );
			$oa = $oa === '' ? 9999 : (int) $oa;
			$ob = $ob === '' ? 9999 : (int) $ob;
			if ( $oa === $ob ) {
				return strcmp( $a->post_title, $b->post_title );
			}
			return $oa < $ob ? -1 : 1;
		}
	);
	return $posts;
}

/** Single frontend card — shared by the homepage section + admin preview. */
function uturn_board_card_html( $p, $i = 0 ) {
	$id = $p->ID;
	$photo = function_exists( 'uturn_person_photo' )
		? uturn_person_photo( $p->post_title, function_exists( 'uturn_photo_url' ) ? uturn_photo_url( $id, '_ut_photo', 'ut-person' ) : '', get_post_meta( $id, '_ut_bg', true ), 'round-lg' )
		: '<div class="board-photo">' . esc_html( mb_substr( $p->post_title, 0, 1 ) ) . '</div>';
	$ex = (string) get_post_meta( $id, '_ut_excerpt', true );
	$h = '<article class="board-card reveal d' . ( (int) $i % 4 ) . '">' . $photo;
	$h .= '<h3>' . esc_html( $p->post_title ) . '</h3>';
	$h .= '<div class="desig">' . esc_html( get_post_meta( $id, '_ut_designation', true ) ) . '</div>';
	if ( $ex !== '' ) {
		$h .= '<p class="bio">' . esc_html( $ex ) . '</p>';
	}
	return $h . '</article>';
}

/* ================= shell view ================= */
function uturn_dash_board() {
	if ( ! current_user_can( 'edit_ut_boards' ) ) {
		echo '<div class="notice notice-error" role="alert"><p>অ্যাক্সেস নেই।</p></div>';
		return;
	}
	if ( isset( $_GET['saved'] ) ) {
		echo '<div class="notice notice-success" role="status"><p>✅ সংরক্ষণ করা হয়েছে।</p></div>';
	} elseif ( isset( $_GET['trashed'] ) ) {
		echo '<div class="notice notice-success" role="status"><p>আবর্জনায় পাঠানো হয়েছে।</p></div>';
	}
	$members = uturn_board_members( false );
	echo '<div class="utx-toolbar"><div><strong>' . esc_html( (string) count( $members ) ) . ' জন সদস্য</strong> <span class="utd-muted">· ওয়েবসাইটে শুধু সক্রিয়রা ক্রমানুসারে দেখাবে</span></div>';
	echo '<div><a class="button" href="' . esc_url( home_url( '/#board-section' ) ) . '" target="_blank" rel="noopener">🌐 ওয়েবসাইটে দেখুন</a> ';
	echo '<a class="button button-primary" href="' . esc_url( uturn_dash_view_url( 'edit', array( 'cpt' => 'ut_board' ) ) ) . '">＋ নতুন সদস্য</a></div></div>';
	if ( ! $members ) {
		echo '<div class="utx-empty"><div class="utx-empty-ic">🏛️</div><h3>এখনও কোনো সদস্য নেই</h3><p>সভাপতি, সহ-সভাপতি, সদস্য সচিব — পর্ষদের সবাইকে যোগ করুন।</p></div>';
	} else {
		echo '<div class="utx-grid">';
		$n = count( $members );
		foreach ( $members as $ix => $m ) {
			$pid = (int) get_post_meta( $m->ID, '_ut_photo', true );
			$img = $pid ? wp_get_attachment_image( $pid, 'thumbnail', false, array( 'class' => 'utx-avatar-lg' ) ) : '<span class="utx-avatar-lg utx-avatar-fb">' . esc_html( mb_substr( $m->post_title, 0, 1 ) ) . '</span>';
			$pub = $m->post_status === 'publish';
			echo '<div class="utd-card utx-mcard">' . $img;
			echo '<h3>' . esc_html( $m->post_title ) . '</h3><div class="utd-muted">' . esc_html( get_post_meta( $m->ID, '_ut_designation', true ) ) . '</div>';
			echo '<div class="utx-mrow"><span class="utd-pill ' . ( $pub ? 'green' : 'amber' ) . '">' . ( $pub ? 'সক্রিয়' : 'খসড়া' ) . '</span><span class="utd-muted">ক্রম: ' . esc_html( (string) ( get_post_meta( $m->ID, '_ut_order', true ) !== '' ? get_post_meta( $m->ID, '_ut_order', true ) : '—' ) ) . '</span></div>';
			echo '<div class="utx-actions">';
			if ( $ix > 0 ) {
				echo '<a class="button button-small" title="উপরে তুলুন" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?ut_shell=1&action=uturn_board_order&id=' . $m->ID . '&dir=up' ), 'uturn_board_' . $m->ID ) ) . '">↑</a> ';
			}
			if ( $ix < $n - 1 ) {
				echo '<a class="button button-small" title="নিচে নামান" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?ut_shell=1&action=uturn_board_order&id=' . $m->ID . '&dir=down' ), 'uturn_board_' . $m->ID ) ) . '">↓</a> ';
			}
			echo '<a class="button button-small" href="' . esc_url( uturn_dash_view_url( 'edit', array( 'cpt' => 'ut_board', 'id' => $m->ID ) ) ) . '">সম্পাদনা</a> ';
			echo '<a class="button button-small" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?ut_shell=1&action=uturn_board_toggle&id=' . $m->ID ), 'uturn_board_' . $m->ID ) ) . '">' . ( $pub ? 'লুকান' : 'দেখান' ) . '</a> ';
			echo '<a class="button button-small utx-danger" data-confirm="“' . esc_attr( $m->post_title ) . '” — মুছে ফেলবেন?" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?ut_shell=1&action=uturn_dash_delete&id=' . $m->ID ), 'uturn_dash_del_' . $m->ID ) ) . '">মুছুন</a>';
			echo '</div></div>';
		}
		echo '</div>';
	}
	/* live frontend preview */
	echo '<div class="utd-card"><h2>👁️ লাইভ প্রিভিউ — ওয়েবসাইটে যেমন দেখাবে</h2>';
	$live = uturn_board_members( true );
	if ( ! $live ) {
		echo '<p class="utd-muted">কোনো সক্রিয় সদস্য নেই — প্রিভিউ খালি।</p>';
	} else {
		echo '<div class="utx-preview"><div class="board-grid">';
		foreach ( $live as $i => $m ) {
			echo uturn_board_card_html( $m, $i ); // phpcs:ignore
		}
		echo '</div></div>';
	}
	echo '</div>';
}

/* ================= order + toggle ================= */
add_action( 'admin_post_uturn_board_order', 'uturn_board_order' );
function uturn_board_order() {
	$id = absint( $_GET['id'] ?? 0 );
	$dir = ( $_GET['dir'] ?? '' ) === 'down' ? 'down' : 'up';
	$p = $id ? get_post( $id ) : null;
	if ( ! $p || $p->post_type !== 'ut_board' || ! current_user_can( 'edit_post', $id ) || ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'uturn_board_' . $id ) ) {
		wp_die( 'Unauthorized.' );
	}
	$all = uturn_board_members( false );
	$ix = -1;
	foreach ( $all as $i => $m ) {
		if ( $m->ID === $id ) {
			$ix = $i;
			break;
		}
	}
	$jx = $dir === 'up' ? $ix - 1 : $ix + 1;
	if ( $ix >= 0 && isset( $all[ $jx ] ) ) {
		$a = get_post_meta( $id, '_ut_order', true );
		$b = get_post_meta( $all[ $jx ]->ID, '_ut_order', true );
		$a = $a === '' ? 999 : (int) $a;
		$b = $b === '' ? 999 : (int) $b;
		update_post_meta( $id, '_ut_order', $b );
		update_post_meta( $all[ $jx ]->ID, '_ut_order', $a );
		if ( $a === $b ) {
			/* Equal ranks: nudge the moved item past its neighbour. */
			update_post_meta( $id, '_ut_order', $dir === 'up' ? $a - 1 : $a + 1 );
		}
	}
	wp_safe_redirect( uturn_dash_view_url( 'board' ) );
	exit;
}

add_action( 'admin_post_uturn_board_toggle', 'uturn_board_toggle' );
function uturn_board_toggle() {
	$id = absint( $_GET['id'] ?? 0 );
	$p = $id ? get_post( $id ) : null;
	if ( ! $p || $p->post_type !== 'ut_board' || ! current_user_can( 'edit_post', $id ) || ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'uturn_board_' . $id ) ) {
		wp_die( 'Unauthorized.' );
	}
	wp_update_post( array( 'ID' => $id, 'post_status' => $p->post_status === 'publish' ? 'draft' : 'publish' ) );
	wp_safe_redirect( uturn_dash_view_url( 'board', array( 'saved' => 1 ) ) );
	exit;
}
