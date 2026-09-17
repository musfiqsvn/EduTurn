<?php
/**
 * EduTurn — Automatic person-photo pipeline.
 *
 * Whatever image an admin picks (any size, any orientation, old or new
 * upload), the frontend always gets a correct, fast, uniformly-cropped
 * photo:
 *  1. New uploads are capped at 2000px wide (library stays lean).
 *  2. Missing theme sizes are generated on demand (self-healing).
 *  3. Images smaller than the target fall back to the original file
 *     (CSS object-fit crops them visually) instead of vanishing.
 *  4. The legacy `_ut_photo_id` key is honoured + backfilled to `_ut_photo`.
 */

defined( 'ABSPATH' ) || exit;

/* ---------- 1. Cap huge originals at upload time ---------- */
add_action( 'add_attachment', 'uturn_photo_cap_original', 5 );
function uturn_photo_cap_original( $att_id ) {
	if ( ! wp_attachment_is_image( $att_id ) ) {
		return;
	}
	$file = get_attached_file( $att_id );
	if ( ! $file || ! file_exists( $file ) ) {
		return;
	}
	$editor = wp_get_image_editor( $file );
	if ( is_wp_error( $editor ) ) {
		return;
	}
	$size = $editor->get_size();
	if ( empty( $size['width'] ) || $size['width'] <= 2000 ) {
		return;
	}
	$saved = $editor->resize( 2000, 2000, false );
	if ( is_wp_error( $saved ) ) {
		return;
	}
	$editor->save( $file );
}

/**
 * Ensure a registered size exists for an attachment; generate it when the
 * file is big enough, otherwise return the original. Always returns a URL
 * (or '') — never a dead size link.
 */
function uturn_ensure_image_size( $att_id, $size = 'ut-person' ) {
	$att_id = absint( $att_id );
	if ( ! $att_id || ! wp_attachment_is_image( $att_id ) ) {
		return '';
	}
	/* NOTE: image_downsize() falls back to the FULL file when a size is missing
	 * ($src[3] = is_intermediate false) — so a bare URL check is not enough. */
	$src = wp_get_attachment_image_src( $att_id, $size );
	if ( $src && ! empty( $src[0] ) && ! empty( $src[3] ) ) {
		$meta  = wp_get_attachment_metadata( $att_id );
		$sfile = ( is_array( $meta ) && isset( $meta['sizes'][ $size ]['file'] ) ) ? $meta['sizes'][ $size ]['file'] : '';
		if ( '' === $sfile ) {
			return $src[0];
		}
		$up   = wp_get_upload_dir();
		$base = trailingslashit( dirname( trailingslashit( $up['basedir'] ) . get_post_meta( $att_id, '_wp_attached_file', true ) ) );
		if ( file_exists( $base . $sfile ) ) {
			return $src[0];
		}
		/* Stale meta (file deleted) — fall through and rebuild it. */
	}
	/* Size missing (pre-theme upload, external import, …) — try to build it. */
	$file = get_attached_file( $att_id );
	if ( ! $file || ! file_exists( $file ) ) {
		return '';
	}
	$dims = uturn_photo_size_dims( $size );
	if ( ! $dims ) {
		return (string) wp_get_attachment_url( $att_id );
	}
	list( $w, $h, $crop ) = $dims;
	$info = @getimagesize( $file );
	if ( ! $info || $info[0] < 1 || $info[1] < 1 ) {
		return '';
	}
	if ( $info[0] < $w || $info[1] < $h ) {
		/* Too small to crop — serve the original; CSS object-fit handles it. */
		return (string) wp_get_attachment_url( $att_id );
	}
	if ( ! function_exists( 'image_make_intermediate_size' ) ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
	}
	$made = image_make_intermediate_size( $file, $w, $h, $crop );
	if ( ! $made || is_wp_error( $made ) || empty( $made['file'] ) ) {
		return (string) wp_get_attachment_url( $att_id );
	}
	$meta = wp_get_attachment_metadata( $att_id );
	if ( ! is_array( $meta ) ) {
		$meta = array();
	}
	if ( ! isset( $meta['sizes'] ) || ! is_array( $meta['sizes'] ) ) {
		$meta['sizes'] = array();
	}
	$meta['sizes'][ $size ] = $made;
	wp_update_attachment_metadata( $att_id, $meta );
	$upload = wp_get_upload_dir();
	$base   = trailingslashit( dirname( get_post_meta( $att_id, '_wp_attached_file', true ) ) );
	if ( '.' === trim( $base, '/' ) ) {
		$base = '';
	}
	return trailingslashit( $upload['baseurl'] ) . $base . $made['file'];
}

function uturn_photo_size_dims( $size ) {
	global $_wp_additional_image_sizes;
	if ( isset( $_wp_additional_image_sizes[ $size ] ) ) {
		$s = $_wp_additional_image_sizes[ $size ];
		return array( (int) $s['width'], (int) $s['height'], (bool) $s['crop'] );
	}
	$core = array(
		'thumbnail' => array( (int) get_option( 'thumbnail_size_w', 150 ), (int) get_option( 'thumbnail_size_h', 150 ), (bool) get_option( 'thumbnail_crop', 1 ) ),
		'medium'    => array( (int) get_option( 'medium_size_w', 300 ), (int) get_option( 'medium_size_h', 300 ), false ),
		'large'     => array( (int) get_option( 'large_size_w', 1024 ), (int) get_option( 'large_size_h', 1024 ), false ),
	);
	if ( isset( $core[ $size ] ) ) {
		return $core[ $size ];
	}
	return null;
}

/* ---------- 4. One-time backfill: _ut_photo_id → _ut_photo ---------- */
add_action( 'admin_init', 'uturn_backfill_person_photos', 20 );
function uturn_backfill_person_photos() {
	if ( get_option( 'uturn_photo_backfill_v1' ) ) {
		return;
	}
	$types = array( 'ut_teacher', 'ut_student', 'ut_staff', 'ut_board' );
	foreach ( $types as $t ) {
		$ids = get_posts(
			array( 'post_type' => $t, 'posts_per_page' => 500, 'fields' => 'ids', 'post_status' => 'any' )
		);
		foreach ( (array) $ids as $pid ) {
			$cur = get_post_meta( $pid, '_ut_photo', true );
			$leg = get_post_meta( $pid, '_ut_photo_id', true );
			if ( ( '' === $cur || '0' === (string) $cur ) && is_numeric( $leg ) && $leg > 0 ) {
				update_post_meta( $pid, '_ut_photo', (int) $leg );
			}
			$fin = get_post_meta( $pid, '_ut_photo', true );
			if ( is_numeric( $fin ) && $fin > 0 ) {
				uturn_ensure_image_size( (int) $fin, 'ut-person' );
			}
		}
	}
	update_option( 'uturn_photo_backfill_v1', 1, false );
}
