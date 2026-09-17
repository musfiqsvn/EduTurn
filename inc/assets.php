<?php
/**
 * EduTurn — Asset pipeline: fonts, 1:1 stylesheet, dynamic palette,
 * SITE_DATA bridge (replaces data.js), geo tracker, admin tooling.
 */

defined( 'ABSPATH' ) || exit;

function uturn_font_url() {
	$bn_map = array(
		'tiro-bangla'   => 'Tiro+Bangla:ital@0;1',
		'hind-siliguri' => 'Hind+Siliguri:wght@400;500;600;700',
		'noto-bengali'  => 'Noto+Sans+Bengali:wght@400;500;600;700',
		'baloo-da'      => 'Baloo+Da+2:wght@400;500;600;700;800',
	);
	$en_map = array(
		'inter'       => 'Inter:wght@400;500;600;700;800',
		'public-sans' => 'Public+Sans:wght@400;500;600;700;800',
	);
	$bn = isset( $bn_map[ uturn_opt( 'font_bn', 'tiro-bangla' ) ] ) ? $bn_map[ uturn_opt( 'font_bn', 'tiro-bangla' ) ] : $bn_map['tiro-bangla'];
	$en = isset( $en_map[ uturn_opt( 'font_en', 'inter' ) ] ) ? $en_map[ uturn_opt( 'font_en', 'inter' ) ] : $en_map['inter'];
	$GLOBALS['uturn_font_stack'] = array( uturn_opt( 'font_en', 'inter' ), uturn_opt( 'font_bn', 'tiro-bangla' ) );
	return 'https://fonts.googleapis.com/css2?family=' . $en . '&family=' . $bn . '&display=swap';
}

/** Dynamic :root palette override (mutates primary/secondary/accent live). */
function uturn_palette_css() {
	$p  = uturn_opt( 'color_primary', '#0B4EA8' );
	$s  = uturn_opt( 'color_secondary', '#14324A' );
	$a  = uturn_opt( 'color_accent', '#1B7FC2' );
	$en = isset( $GLOBALS['uturn_font_stack'][0] ) ? $GLOBALS['uturn_font_stack'][0] : 'inter';
	$bn = isset( $GLOBALS['uturn_font_stack'][1] ) ? $GLOBALS['uturn_font_stack'][1] : 'tiro-bangla';
	$en_fam = array( 'inter' => 'Inter', 'public-sans' => 'Public Sans' );
	$bn_fam = array( 'tiro-bangla' => 'Tiro Bangla', 'hind-siliguri' => 'Hind Siliguri', 'noto-bengali' => 'Noto Sans Bengali', 'baloo-da' => 'Baloo Da 2' );
	$en_f = isset( $en_fam[ $en ] ) ? $en_fam[ $en ] : 'Inter';
	$bn_f = isset( $bn_fam[ $bn ] ) ? $bn_fam[ $bn ] : 'Tiro Bangla';
	return ":root{--primary:{$p};--primary-600:" . uturn_shade( $p, 18 ) . ";--primary-700:" . uturn_shade( $p, -14 )
		. ";--primary-900:" . uturn_shade( $p, -42 ) . ";--primary-soft:" . uturn_shade( $p, 88 )
		. ";--secondary:{$s};--accent:{$a};--accent-soft:" . uturn_shade( $a, 86 ) . ";--accent-line:" . uturn_shade( $a, 45 )
		. "}body{font-family:'{$en_f}','{$bn_f}',system-ui,-apple-system,'Segoe UI',sans-serif!important}";
}

add_action( 'wp_enqueue_scripts', 'uturn_enqueue' );
function uturn_enqueue() {
	$css_path = UTURN_DIR . '/assets/css/eduturn.css';
	$js_path  = UTURN_DIR . '/assets/js/eduturn.js';
	$css_ver  = file_exists( $css_path ) ? filemtime( $css_path ) : UTURN_VERSION;
	$js_ver   = file_exists( $js_path ) ? filemtime( $js_path ) : UTURN_VERSION;

	wp_enqueue_style( 'eduturn-fonts', uturn_font_url(), array(), null );
	wp_enqueue_style( 'eduturn', UTURN_URI . '/assets/css/eduturn.css', array(), $css_ver );
	wp_add_inline_style( 'eduturn', uturn_palette_css() );
	$custom = trim( (string) uturn_opt( 'custom_css', '' ) );
	if ( '' !== $custom ) {
		wp_add_inline_style( 'eduturn', $custom );
	}

	wp_enqueue_script( 'eduturn', UTURN_URI . '/assets/js/eduturn.js', array(), $js_ver, true );
	wp_add_inline_script(
		'eduturn',
		'window.SITE_DATA=' . wp_json_encode( uturn_site_data(), JSON_UNESCAPED_UNICODE ) . ';'
		. 'window.UTURN=' . wp_json_encode(
			array(
				'home'        => home_url( '/' ),
				'lang'        => uturn_lang(),
				'tickerSpeed' => (int) uturn_opt( 'ticker_speed', 45 ),
			)
		) . ';window.UTURN_TICKER_SPEED=' . (int) uturn_opt( 'ticker_speed', 45 ) . ';',
		'before'
	);
	if ( uturn_opt( 'geo_enabled', 1 ) ) {
		wp_enqueue_script( 'eduturn-geo', UTURN_URI . '/assets/js/eduturn-geo.js', array(), $js_ver, true );
	}
}

/* LCP: preload the front-page hero image (same source the hero uses). */
add_action( 'wp_head', 'uturn_preload_hero', 1 );
function uturn_preload_hero() {
	if ( ! is_front_page() ) {
		return;
	}
	$bg_id = (int) uturn_opt( 'hero_bg_id', 0 );
	$bg = $bg_id ? wp_get_attachment_image_url( $bg_id, 'ut-hero' ) : UTURN_URI . '/assets/images/hero-campus.jpg';
	if ( $bg ) {
		echo '<link rel="preload" as="image" fetchpriority="high" href="' . esc_url( $bg ) . '">' . "\n";
	}
}

/**
 * SITE_DATA bridge: live WP queries shaped EXACTLY like the static data.js
 * collections the frontend JS already consumes (search, countdown, chrome).
 */
function uturn_site_data() {
	$cached = get_transient( 'uturn_site_data' );
	if ( is_array( $cached ) ) {
		return $cached;
	}
	$data = array(
		'brand'         => array(
			'nameBn'     => uturn_opt( 'school_name_bn', 'আলোকিত বিদ্যানিকেতন' ),
			'nameEn'     => uturn_opt( 'school_name_en', 'Alokito Biddyaniketon' ),
			'tagline'    => uturn_opt( 'tagline', 'আলোকিত ভবিষ্যতের পথে' ),
			'address'    => uturn_opt( 'address', 'বাড়ি ১২, রোড ০৭, ধানমন্ডি, ঢাকা-১২০৫' ),
			'phone'      => uturn_opt( 'phone', '+৮৮০ ৯৬১১-২৩৪৫৬৭' ),
			'phoneHref'  => uturn_opt( 'phone_href', '+8809611234567' ),
			'email'      => uturn_opt( 'email', 'info@alokitobiddyaniketon.edu.bd' ),
			'hours'      => uturn_opt( 'hours', 'রবি–বৃহস্পতি: সকাল ৮টা – বিকেল ৪টা' ),
			'hoursShort' => uturn_opt( 'hours_short', 'রবি–বৃহস্পতি, সকাল ৮টা–বিকেল ৪টা' ),
		),
		'admissionInfo' => array(
			'deadline'   => uturn_opt( 'adm_deadline', '2026-12-15' ) . 'T23:59:59+06:00',
			'deadlineBn' => uturn_opt( 'deadline_bn', '১৫ ডিসেম্বর ২০২৬' ),
		),
		'notices'       => array(),
		'events'        => array(),
		'news'          => array(),
		'teachers'      => array(),
		'downloads'     => array(),
	);

	$q = new WP_Query( array( 'post_type' => 'ut_notice', 'posts_per_page' => 12, 'post_status' => 'publish', 'no_found_rows' => true ) );
	foreach ( $q->posts as $p ) {
		$terms = get_the_terms( $p->ID, 'ut_notice_cat' );
		$data['notices'][] = array(
			'title'    => get_the_title( $p ),
			'url'      => get_permalink( $p ),
			'date'     => get_the_date( 'Y-m-d', $p ),
			'dateBn'   => get_post_meta( $p->ID, '_ut_date_bn', true ) ? get_post_meta( $p->ID, '_ut_date_bn', true ) : uturn_bn_date( get_the_date( 'Y-m-d', $p ) ),
			'excerpt'  => get_post_meta( $p->ID, '_ut_excerpt', true ) ? get_post_meta( $p->ID, '_ut_excerpt', true ) : wp_trim_words( $p->post_content, 24 ),
			'category' => ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->slug : 'general',
		);
	}
	wp_reset_postdata();

	$q = new WP_Query( array( 'post_type' => 'ut_event', 'posts_per_page' => 8, 'post_status' => 'publish', 'no_found_rows' => true ) );
	foreach ( $q->posts as $p ) {
		$data['events'][] = array(
			'title'    => get_the_title( $p ),
			'url'      => get_permalink( $p ),
			'dateBn'   => get_post_meta( $p->ID, '_ut_date_bn', true ) ? get_post_meta( $p->ID, '_ut_date_bn', true ) : uturn_bn_date( get_the_date( 'Y-m-d', $p ) ),
			'excerpt'  => get_post_meta( $p->ID, '_ut_excerpt', true ) ? get_post_meta( $p->ID, '_ut_excerpt', true ) : wp_trim_words( $p->post_content, 20 ),
		);
	}
	wp_reset_postdata();

	$q = new WP_Query( array( 'post_type' => 'ut_news', 'posts_per_page' => 8, 'post_status' => 'publish', 'no_found_rows' => true ) );
	foreach ( $q->posts as $p ) {
		$data['news'][] = array(
			'title'   => get_the_title( $p ),
			'url'     => get_permalink( $p ),
			'excerpt' => get_post_meta( $p->ID, '_ut_excerpt', true ) ? get_post_meta( $p->ID, '_ut_excerpt', true ) : wp_trim_words( $p->post_content, 20 ),
		);
	}
	wp_reset_postdata();

	$q = new WP_Query( array( 'post_type' => 'ut_teacher', 'posts_per_page' => 20, 'post_status' => 'publish', 'orderby' => 'title', 'order' => 'ASC', 'no_found_rows' => true ) );
	foreach ( $q->posts as $p ) {
		$data['teachers'][] = array(
			'name'        => get_the_title( $p ),
			'url'         => get_permalink( $p ),
			'subject'     => get_post_meta( $p->ID, '_ut_subject', true ),
			'designation' => get_post_meta( $p->ID, '_ut_designation', true ),
		);
	}
	wp_reset_postdata();

	$q = new WP_Query( array( 'post_type' => 'ut_download', 'posts_per_page' => 20, 'post_status' => 'publish', 'no_found_rows' => true ) );
	foreach ( $q->posts as $p ) {
		$terms = get_the_terms( $p->ID, 'ut_download_cat' );
		$data['downloads'][] = array(
			'name'     => get_the_title( $p ),
			'url'      => uturn_url( 'downloads' ),
			'category' => ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '',
		);
	}
	wp_reset_postdata();

	set_transient( 'uturn_site_data', $data, 12 * HOUR_IN_SECONDS );
	return $data;
}

/* Flush the SITE_DATA cache whenever content or dashboard options change. */
add_action( 'save_post', function () { delete_transient( 'uturn_site_data' ); } );
add_action( 'update_option_' . UTURN_OPT, function () { delete_transient( 'uturn_site_data' ); } );

/* ---------- Admin tooling (dashboard + SMS screens only) ---------- */
add_action( 'admin_enqueue_scripts', 'uturn_admin_assets' );
function uturn_admin_assets( $hook ) {
	if ( false === strpos( (string) $hook, 'uturn' ) ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_style( 'eduturn-admin', UTURN_URI . '/assets/css/admin.css', array(), UTURN_VERSION );
	wp_enqueue_script( 'eduturn-admin', UTURN_URI . '/assets/js/admin.js', array( 'jquery', 'jquery-ui-sortable', 'wp-color-picker' ), UTURN_VERSION, true );
}

/* ---------- Speed: defer JS, preconnect fonts, cut dead weight ---------- */
add_filter( 'script_loader_tag', 'uturn_defer_front_scripts', 10, 2 );
function uturn_defer_front_scripts( $tag, $handle ) {
	if ( is_admin() ) {
		return $tag;
	}
	if ( in_array( $handle, array( 'eduturn', 'eduturn-geo' ), true ) ) {
		return str_replace( ' src=', ' defer src=', $tag );
	}
	return $tag;
}
add_filter( 'wp_resource_hints', 'uturn_resource_hints', 10, 2 );
function uturn_resource_hints( $urls, $relation ) {
	if ( 'preconnect' === $relation && ! is_admin() ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' => 'anonymous' );
	}
	return $urls;
}
add_action( 'init', 'uturn_trim_weight' );
function uturn_trim_weight() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
}
add_action( 'wp_default_scripts', 'uturn_no_jq_migrate' );
function uturn_no_jq_migrate( $scripts ) {
	if ( is_admin() || ! isset( $scripts->registered['jquery'] ) ) {
		return;
	}
	$jq = $scripts->registered['jquery'];
	if ( $jq->deps ) {
		$jq->deps = array_diff( $jq->deps, array( 'jquery-migrate' ) );
	}
}
