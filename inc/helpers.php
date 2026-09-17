<?php
/**
 * EduTurn — Global helpers (escaping, i18n digits, icons, sections).
 */

defined( 'ABSPATH' ) || exit;

/* ---------- mbstring polyfills (hosts without the extension) ---------- */
if ( ! function_exists( 'mb_strlen' ) ) {
	function mb_strlen( $s, $enc = null ) { return strlen( utf8_decode( (string) $s ) ); }
}
if ( ! function_exists( 'mb_substr' ) ) {
	function mb_substr( $s, $start, $len = null, $enc = null ) {
		$chars = preg_split( '//u', (string) $s, -1, PREG_SPLIT_NO_EMPTY );
		return implode( '', array_slice( $chars, $start, null === $len ? null : $len ) );
	}
}
if ( ! function_exists( 'mb_strtolower' ) ) {
	function mb_strtolower( $s, $enc = null ) { return strtolower( (string) $s ); }
}

/* Dashboard options accessor lives in inc/options.php (uturn_opts/uturn_opt).
   NOTE: do NOT redeclare uturn_opt() here — PHP fatals on duplicates. */

/* ---------- Bengali digits & dates ---------- */
function uturn_bn( $v ) {
	return str_replace(
		array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ),
		array( '০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯' ),
		(string) $v
	);
}

/** Bengali digits -> ASCII (for roll/mobile/datetime comparisons). */
function uturn_bn_to_en( $v ) {
	return str_replace(
		array( '০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯' ),
		array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ),
		(string) $v
	);
}

/** Normalize + validate a BD mobile number.
 * Accepts 01XXXXXXXXX, 8801XXXXXXXXX, +880..., 10-digit 1XXXXXXXXX and
 * Bangla digits. Returns normalized 01XXXXXXXXX, or empty when invalid. */
function uturn_valid_phone( $raw ) {
	$p = str_replace( array( ' ', '-', '(', ')', '+' ), '', uturn_bn_to_en( (string) $raw ) );
	if ( preg_match( '/^(?:880|0)?(1[3-9]\d{8})$/', $p, $m ) ) {
		return '0' . $m[1];
	}
	return '';
}

/** Split a textarea/option blob into trimmed non-empty lines. */
function uturn_lines( $text ) {
	$out = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $text ) as $ln ) {
		$ln = trim( $ln );
		if ( '' !== $ln ) {
			$out[] = $ln;
		}
	}
	return $out;
}

/**
 * Resolve a media value: attachment ID -> URL, "assets/..." -> theme URI,
 * absolute URL -> as-is, empty -> fallback.
 */
function uturn_img( $v, $fallback = '' ) {
	if ( is_numeric( $v ) && $v > 0 ) {
		$url = wp_get_attachment_image_url( (int) $v, 'full' );
		return $url ? $url : $fallback;
	}
	if ( is_string( $v ) && '' !== $v ) {
		if ( 0 === strpos( $v, 'assets/' ) ) {
			return UTURN_URI . '/' . $v;
		}
		if ( 0 === strpos( $v, 'http' ) || 0 === strpos( $v, '/' ) ) {
			return $v;
		}
	}
	return $fallback;
}

function uturn_bn_date( $date = 'now' ) {
	if ( function_exists( 'uturn_lang' ) && 'en' === uturn_lang() ) {
		$ts = is_numeric( $date ) ? (int) $date : strtotime( (string) $date );
		if ( ! $ts ) {
			$ts = time();
		}
		return date( 'j F Y', $ts );
	}
	$months = array( 1 => 'জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর' );
	$ts     = is_numeric( $date ) ? (int) $date : strtotime( (string) $date );
	if ( ! $ts ) {
		$ts = time();
	}
	return uturn_bn( (int) date_i18n( 'j', $ts ) ) . ' ' . $months[ (int) date_i18n( 'n', $ts ) ] . ' ' . uturn_bn( date_i18n( 'Y', $ts ) );
}

/* ---------- Color math (derived palette shades) ---------- */
function uturn_shade( $hex, $percent ) {
	$hex = ltrim( (string) $hex, '#' );
	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	if ( ! preg_match( '/^[0-9a-fA-F]{6}$/', $hex ) ) {
		return '#0B4EA8';
	}
	$out = '#';
	for ( $i = 0; $i < 3; $i++ ) {
		$c  = hexdec( substr( $hex, $i * 2, 2 ) );
		$c  = $percent >= 0 ? $c + ( 255 - $c ) * $percent / 100 : $c * ( 1 + $percent / 100 );
		$out .= str_pad( dechex( max( 0, min( 255, (int) round( $c ) ) ) ), 2, '0', STR_PAD_LEFT );
	}
	return strtoupper( $out );
}

/* ---------- Server-side icon library (1:1 port of the JS IC set) ---------- */
function uturn_icon( $name ) {
	static $icons = null;
	if ( null === $icons ) {
		$st = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">';
		$icons = array(
			'phone'   => $st . '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.13.96.36 1.9.7 2.8a2 2 0 0 1-.45 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.45c.9.34 1.84.57 2.8.7A2 2 0 0 1 22 16.9Z"/></svg>',
			'search'  => $st . '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>',
			'menu'    => $st . '<path d="M4 7h16M4 12h16M4 17h16"/></svg>',
			'close'   => $st . '<path d="M6 6l12 12M18 6 6 18"/></svg>',
			'caret'   => '<svg class="caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="m6 9 6 6 6-6"/></svg>',
			'arrow'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>',
			'up'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 19V5m-6 6 6-6 6 6"/></svg>',
			'left'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 6l-6 6 6 6"/></svg>',
			'right'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M9 6l6 6-6 6"/></svg>',
			'check'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><path d="M20 6 9 17l-5-5"/></svg>',
			'tick'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6 9 17l-5-5"/></svg>',
			'pin'     => $st . '<path d="M12 21s7-5.5 7-11a7 7 0 1 0-14 0c0 5.5 7 11 7 11Z"/><circle cx="12" cy="10" r="2.6"/></svg>',
			'mail'    => $st . '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>',
			'clock'   => $st . '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
			'file'    => $st . '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5Z"/><path d="M14 3v5h5M9 13h6M9 17h4"/></svg>',
			'bell'    => $st . '<path d="M18 8a6 6 0 1 0-12 0c0 7-3 8-3 8h18s-3-1-3-8"/><path d="M10.3 21a2 2 0 0 0 3.4 0"/></svg>',
			'bellfill'=> '<svg viewBox="0 0 24 24" width="15" height="15" fill="currentColor" aria-hidden="true"><path d="M12 22c1.1 0 2-.9 2-2h-4a2 2 0 0 0 2 2zm6-6v-5a6 6 0 0 0-4.5-5.8V4a1.5 1.5 0 0 0-3 0v1.2A6 6 0 0 0 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>',
			'result'  => $st . '<circle cx="12" cy="9" r="6"/><path d="m8.5 14-1.8 7 5.3-3 5.3 3-1.8-7"/></svg>',
			'routine' => $st . '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/></svg>',
			'cal'     => $st . '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18M8 15h3"/></svg>',
			'book'    => $st . '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V4H6.5A2.5 2.5 0 0 0 4 6.5v13Z"/><path d="M4 19.5A2.5 2.5 0 0 0 6.5 22H20v-5"/></svg>',
			'users'   => $st . '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/></svg>',
			'cap'     => $st . '<path d="M12 3 2 8l10 5 10-5-10-5Z"/><path d="M6 10.5V15c0 1.5 2.7 3 6 3s6-1.5 6-3v-4.5"/></svg>',
			'bulb'    => $st . '<path d="M9 18h6M10 22h4"/><path d="M12 2a7 7 0 0 0-4 12.7c.6.5 1 1.4 1 2.3h6c0-.9.4-1.8 1-2.3A7 7 0 0 0 12 2Z"/></svg>',
			'shield'  => $st . '<path d="M12 2 4 6v6c0 5 3.4 8.4 8 10 4.6-1.6 8-5 8-10V6l-8-4Z"/><path d="m9 12 2 2 4-4"/></svg>',
			'flask'   => $st . '<path d="M9 3h6M10 3v6L4.5 19a2 2 0 0 0 1.8 3h11.4a2 2 0 0 0 1.8-3L14 9V3"/><path d="M7 15h10"/></svg>',
			'monitor' => $st . '<rect x="2" y="4" width="20" height="13" rx="2"/><path d="M8 21h8m-4-4v4"/></svg>',
			'trophy'  => $st . '<path d="M8 21h8m-4-4v4M7 4h10v5a5 5 0 0 1-10 0V4Z"/><path d="M7 6H4a1 1 0 0 0-1 1c0 2.5 2 4 4 4M17 6h3a1 1 0 0 1 1 1c0 2.5-2 4-4 4"/></svg>',
			'heart'   => $st . '<path d="M19 14c1.5-1.5 3-3.2 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.8 0-3 .5-4.5 2-1.5-1.5-2.7-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4 3 5.5l7 7 7-7Z"/></svg>',
			'chat'    => $st . '<path d="M21 12a8 8 0 0 1-8 8H4l2-3a8 8 0 1 1 15-5Z"/></svg>',
			'fb'      => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 22v-8h2.7l.4-3.2h-3.1V8.7c0-.9.3-1.6 1.7-1.6h1.6V4.2c-.3 0-1.2-.1-2.3-.1-2.3 0-3.9 1.4-3.9 4v2.7H7.8V14h2.8v8h2.9Z"/></svg>',
			'yt'      => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M23 12s0-3.4-.4-5A2.8 2.8 0 0 0 20.6 5C18.9 4.5 12 4.5 12 4.5s-6.9 0-8.6.5A2.8 2.8 0 0 0 1.4 7C1 8.6 1 12 1 12s0 3.4.4 5a2.8 2.8 0 0 0 2 2c1.7.5 8.6.5 8.6.5s6.9 0 8.6-.5a2.8 2.8 0 0 0 2-2c.4-1.6.4-5 .4-5ZM9.8 15.5v-7l6 3.5-6 3.5Z"/></svg>',
			'send'    => $st . '<path d="m22 2-7 20-4-9-9-4 20-7Z"/></svg>',
			'grid'    => $st . '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>',
			'logout'  => $st . '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>',
			'download'=> $st . '<path d="M12 3v12m0 0 4-4m-4 4-4-4"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>',
		);
	}
	return isset( $icons[ $name ] ) ? $icons[ $name ] : '';
}

/* ---------- Institutional identity ---------- */
function uturn_logo_url() {
	$id = (int) uturn_opt( 'logo_id', 0 );
	if ( $id ) {
		$url = wp_get_attachment_image_url( $id, 'full' );
		if ( $url ) {
			return $url;
		}
	}
	return UTURN_URI . '/assets/images/logo.svg';
}

function uturn_lang() {
	return ( isset( $_COOKIE['uturn_lang'] ) && 'en' === $_COOKIE['uturn_lang'] ) ? 'en' : 'bn';
}

function uturn_t( $bn, $en ) {
	return 'en' === uturn_lang() ? $en : $bn;
}

/** Canonical internal URL by page slug (falls back to a pretty guess pre-seed). */
function uturn_url( $slug ) {
	$p = get_page_by_path( $slug );
	if ( $p ) {
		return get_permalink( $p );
	}
	return home_url( '/' . trim( $slug, '/' ) . '/' );
}

/** Portal entry per role: dashboard when signed in, WP login (redirecting back) otherwise. */
function uturn_portal_url( $role ) {
	$slug   = 'student' === $role ? 'student-portal' : 'teacher-portal';
	$target = uturn_url( $slug );
	if ( is_user_logged_in() ) {
		return $target;
	}
	return wp_login_url( $target );
}

/** Static-parity body[data-page] key for nav-active + CSS hooks. */
function uturn_page_key() {
	if ( is_front_page() ) {
		return 'home';
	}
	if ( is_post_type_archive( 'ut_notice' ) || is_singular( 'ut_notice' ) || is_tax( 'ut_notice_cat' ) ) {
		return 'notices';
	}
	if ( is_post_type_archive( 'ut_event' ) || is_singular( 'ut_event' ) ) {
		return 'events';
	}
	if ( is_post_type_archive( 'ut_news' ) || is_singular( 'ut_news' ) ) {
		return 'news';
	}
	if ( is_post_type_archive( 'ut_album' ) || is_singular( 'ut_album' ) ) {
		return 'gallery';
	}
	if ( is_post_type_archive( 'ut_teacher' ) || is_singular( 'ut_teacher' ) ) {
		return 'teachers';
	}
	if ( is_page() ) {
		$slug = get_post_field( 'post_name', get_queried_object_id() );
		$map  = array(
			'about' => 'about', 'academic' => 'academic', 'admission' => 'admission', 'apply' => 'admission',
			'students' => 'students', 'students-list' => 'students', 'results' => 'academic', 'routine' => 'academic',
			'downloads' => 'students', 'contact' => 'contact', 'teachers' => 'teachers',
		);
		return isset( $map[ $slug ] ) ? $map[ $slug ] : $slug;
	}
	return '';
}

/* ---------- People photos (1:1 with JS personPhoto: .p-photo or .avatar) ---------- */
function uturn_initials( $name ) {
	$name  = preg_replace( '/^(মোঃ|মোসা\.|ড\.|ডা\.|ইঞ্জি\.|জনাব|আলহাজ্ব)\s*/u', '', (string) $name );
	$parts = preg_split( '/\s+/u', trim( $name ) );
	$a     = isset( $parts[0] ) ? mb_substr( $parts[0], 0, 1, 'UTF-8' ) : 'অ';
	$b     = isset( $parts[1] ) ? mb_substr( $parts[1], 0, 1, 'UTF-8' ) : '';
	return $a . $b;
}

/**
 * Resolve a person/cover image: featured image -> numeric attachment meta ->
 * theme-relative "assets/..." path -> absolute URL. Returns '' when none.
 */
function uturn_photo_url( $post_id, $meta_key = '_ut_photo', $size = 'ut-person' ) {
	if ( has_post_thumbnail( $post_id ) ) {
		$thumb_id = get_post_thumbnail_id( $post_id );
		$url = function_exists( 'uturn_ensure_image_size' ) && $thumb_id
			? uturn_ensure_image_size( (int) $thumb_id, $size )
			: get_the_post_thumbnail_url( $post_id, $size );
		if ( $url ) {
			return $url;
		}
	}
	$meta = get_post_meta( $post_id, $meta_key, true );
	if ( ( '' === $meta || '0' === (string) $meta ) && '_ut_photo' === $meta_key ) {
		$meta = get_post_meta( $post_id, '_ut_photo_id', true ); // legacy entity key
		if ( is_numeric( $meta ) && $meta > 0 ) {
			update_post_meta( $post_id, '_ut_photo', (int) $meta ); // self-heal: mirror forward
		}
	}
	if ( is_numeric( $meta ) && $meta > 0 ) {
		$url = function_exists( 'uturn_ensure_image_size' )
			? uturn_ensure_image_size( (int) $meta, $size )
			: wp_get_attachment_image_url( (int) $meta, $size );
		if ( $url ) {
			return $url;
		}
	}
	if ( is_string( $meta ) && '' !== $meta ) {
		if ( 0 === strpos( $meta, 'assets/' ) ) {
			return UTURN_URI . '/' . $meta;
		}
		if ( 0 === strpos( $meta, 'http' ) || 0 === strpos( $meta, '/' ) ) {
			return $meta;
		}
	}
	return '';
}

function uturn_person_photo( $name, $url, $bg = '#0B4EA8', $cls = '' ) {
	$cls = trim( ' ' . $cls );
	if ( $url ) {
		return '<span class="p-photo full' . esc_attr( $cls ) . '"><img src="' . esc_url( $url ) . '" alt="' . esc_attr( $name ) . '" loading="lazy"></span>';
	}
	return '<span class="avatar' . esc_attr( $cls ) . '" style="background:' . esc_attr( $bg ? $bg : '#0B4EA8' ) . '">' . esc_html( uturn_initials( $name ) ) . '</span>';
}

/* ---------- Homepage section registry (Module 1: sorting engine) ---------- */
function uturn_section_defs() {
	return array(
		'hero'         => 'হিরো (Hero)',
		'quick'        => 'দ্রুত সেবা (Quick Access)',
		'about'        => 'আমাদের কথা (About)',
		'stats'        => 'পরিসংখ্যান (Stats Band)',
		'principal'    => 'প্রধান শিক্ষকের বার্তা',
		'board'        => 'পরিচালনা পর্ষদ',
		'why'          => 'কেন আমরাই সেরা (Features)',
		'programs'     => 'একাডেমিক প্রোগ্রাম',
		'notices'      => 'নোটিশ বোর্ড',
		'admission'    => 'ভর্তি ব্যানার + কাউন্টডাউন',
		'facilities'   => 'ক্যাম্পাস সুবিধা',
		'events'       => 'ইভেন্টসমূহ',
		'achievements' => 'অর্জন ও স্বীকৃতি',
		'teachers'     => 'শিক্ষকমণ্ডলী প্রিভিউ',
		'stars'        => 'কৃতি শিক্ষার্থী',
		'gallery'      => 'গ্যালারি প্রিভিউ',
		'testimonials' => 'মতামত',
		'news'         => 'সংবাদ ও আপডেট',
	);
}

/** Ordered, visibility-filtered section ids for front-page.php. */
function uturn_home_sections() {
	$defs  = uturn_section_defs();
	$saved = uturn_opt( 'sections_order', array() );
	$out   = array();
	if ( is_array( $saved ) ) {
		foreach ( $saved as $row ) {
			if ( isset( $row['id'] ) && ! empty( $row['visible'] ) && preg_match( '/^[a-z0-9_-]+$/', $row['id'] )
				&& ( isset( $defs[ $row['id'] ] ) || file_exists( get_template_directory() . '/template-parts/home/' . $row['id'] . '.php' ) ) ) {
				$out[] = $row['id'];
			}
		}
	}
	if ( empty( $out ) ) {
		$out = array_keys( $defs ); // pre-dashboard default: everything, canonical order
	}
	return $out;
}

/* Never let a broken mail transport white-screen a flow: wp_mail() only
 * catches Exception, but a disabled mail() or a misbehaving SMTP plugin
 * throws Error — catch everything, log it, and keep going. */
function eduturn_safe_mail( $to, $subject, $body ) {
	if ( $to === '' || $to === null ) {
		return false;
	}
	try {
		return wp_mail( $to, $subject, $body );
	} catch ( Throwable $e ) {
		error_log( 'EduTurn mail failed: ' . $e->getMessage() );
		return false;
	}
}

/* Is outbound mail even possible? PHPMailer's default transport needs PHP
 * mail(); SMTP plugins override the mailer via phpmailer_init. */
function eduturn_mail_transport_ok() {
	if ( function_exists( 'mail' ) ) {
		return true;
	}
	return has_action( 'phpmailer_init' ) !== false;
}

/* Last-resort guard: when sending is provably impossible (mail() disabled and
 * nothing overrides the mailer), skip the send instead of white-screening.
 * Only acts when no other pre_wp_mail handler (API mailers) claimed the mail. */
add_filter( 'pre_wp_mail', 'eduturn_guard_wp_mail', 999, 2 );
function eduturn_guard_wp_mail( $pre, $atts ) {
	if ( null !== $pre || eduturn_mail_transport_ok() ) {
		return $pre;
	}
	$to = isset( $atts['to'] ) ? ( is_array( $atts['to'] ) ? implode( ',', $atts['to'] ) : (string) $atts['to'] ) : '';
	error_log( 'EduTurn: skipped email to ' . $to . ' — no mail transport (mail() disabled, no SMTP plugin).' );
	return false;
}

/* Users screens: explain BEFORE the white-screen hits. */
add_action( 'admin_notices', 'eduturn_users_mail_notice' );
function eduturn_users_mail_notice() {
	$pagenow = isset( $GLOBALS['pagenow'] ) ? $GLOBALS['pagenow'] : '';
	if ( ! in_array( $pagenow, array( 'users.php', 'user-new.php', 'user-edit.php', 'profile.php' ), true ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_users' ) && ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! function_exists( 'mail' ) && has_action( 'phpmailer_init' ) !== false ) {
		echo '<div class="notice notice-info" role="status"><p><b>EduTurn:</b> SMTP প্লাগইন ধরা পড়েছে, কিন্তু PHP <code>mail()</code> বন্ধ। User add / password change-এর আগে SMTP প্লাগইনের <b>Test Email</b> পাঠিয়ে নিশ্চিত হোন — ভাঙা SMTP মানেই critical error।</p></div>';
		return;
	}
	if ( ! eduturn_mail_transport_ok() ) {
		echo '<div class="notice notice-error" role="alert"><p><b>EduTurn:</b> 🚨 সার্ভারে <code>mail()</code> বন্ধ এবং কোনো SMTP প্লাগইন সক্রিয় নয় — password reset / Add User (email সহ) / password change-এ ইমেইল যাবে না। <b>WP Mail SMTP</b> বসিয়ে Gmail/হোস্টিং SMTP সেট করুন, তারপর Test Email পাঠান।</p></div>';
	}
}

/* Lost-password screen: honest note when reset emails can't go out. */
add_filter( 'login_message', 'eduturn_login_mail_notice' );
function eduturn_login_mail_notice( $message ) {
	if ( isset( $_GET['action'] ) && $_GET['action'] === 'lostpassword' && ! eduturn_mail_transport_ok() ) {
		$message .= '<div class="notice notice-warning inline" style="margin:0 0 16px"><p>⚠️ সার্ভারে ইমেইল ব্যবস্থা বন্ধ আছে — রিসেট লিংক ইমেইলে যাবে না। পাসওয়ার্ডের জন্য স্কুল অফিসে যোগাযোগ করুন।</p></div>';
	}
	return $message;
}

/* ================= v1.21.0: structured row editor =================
 * Replaces pipe-separated textarea entry ("a|b|c" lines) with labeled
 * per-field rows. STORAGE FORMAT UNCHANGED: save handlers implode rows
 * back to the exact same text (or same arrays), so every reader/display
 * keeps working byte-for-byte. Pipe chars typed in a cell are stripped
 * (they would corrupt the format) — newlines collapse to spaces. */

/** Column presets for every rows-managed key. Null = keep plain textarea. */
function uturn_rows_columns( $key ) {
	static $maps = null;
	if ( null === $maps ) {
		$grades = array( 'A+' => 'A+', 'A' => 'A', 'A-' => 'A-', 'B' => 'B', 'C' => 'C', 'D' => 'D', 'F' => 'F' );
		$days = array( 'রবি' => 'রবি', 'সোম' => 'সোম', 'মঙ্গল' => 'মঙ্গল', 'বুধ' => 'বুধ', 'বৃহস্পতিবার' => 'বৃহস্পতিবার', 'বৃহস্পতি' => 'বৃহস্পতি', 'বৃহ' => 'বৃহ', 'শুক্রবার' => 'শুক্রবার', 'শুক্র' => 'শুক্র', 'শনিবার' => 'শনিবার', 'শনি' => 'শনি' );
		$maps = array(
			/* Dashboard: exam routine grid (screenshot 3). */
			'exam' => array(
				array( 'label' => 'তারিখ', 'type' => 'text', 'ph' => '১২ নভেম্বর ২০২৬' ),
				array( 'label' => 'বার', 'type' => 'select', 'options' => $days ),
				array( 'label' => 'বিষয়', 'type' => 'text', 'ph' => 'বাংলা ২য় পত্র' ),
				array( 'label' => 'সময়', 'type' => 'text', 'ph' => 'সকাল ১০টা – দুপুর ১টা' ),
			),
			/* Meta box: result subject marks (screenshot 4). 4th col is optional. */
			'result_subjects' => array(
				array( 'label' => 'বিষয়', 'type' => 'text', 'ph' => 'বাংলা' ),
				array( 'label' => 'নম্বর', 'type' => 'number', 'step' => 'any', 'min' => '0', 'ph' => '৮২' ),
				array( 'label' => 'গ্রেড', 'type' => 'select', 'options' => $grades ),
				array( 'label' => 'পূর্ণমান', 'type' => 'number', 'ph' => '১০০ (খালি = ১০০)' ),
			),
			/* Meta box: album photo list, one value per row. */
			'photo_list' => array(
				array( 'label' => 'ছবি (মিডিয়া ID বা URL)', 'type' => 'text' ),
			),
			/* Site settings (options page + homepage editor share these). */
			'hero_meta' => array(
				array( 'label' => 'সংখ্যা', 'type' => 'text', 'ph' => '২৫+' ),
				array( 'label' => 'লেবেল', 'type' => 'text', 'ph' => 'বছরের অভিজ্ঞতা' ),
			),
			'stats_lines' => array(
				array( 'label' => 'সংখ্যা (ইংরেজিতে)', 'type' => 'text', 'ph' => '1500' ),
				array( 'label' => 'চিহ্ন', 'type' => 'text', 'ph' => '+ / %' ),
				array( 'label' => 'লেবেল', 'type' => 'text', 'ph' => 'শিক্ষার্থী' ),
			),
			'management_lines' => array(
				array( 'label' => 'নাম', 'type' => 'text' ),
				array( 'label' => 'পদবি', 'type' => 'text' ),
			),
			'seats_lines' => array(
				array( 'label' => 'শ্রেণি', 'type' => 'text' ),
				array( 'label' => 'আসন', 'type' => 'text' ),
				array( 'label' => 'বয়স', 'type' => 'text' ),
			),
			'fees_lines' => array(
				array( 'label' => 'শ্রেণি', 'type' => 'text' ),
				array( 'label' => 'ভর্তি ফি', 'type' => 'text' ),
				array( 'label' => 'মাসিক', 'type' => 'text' ),
			),
			'adm_dates_lines' => array(
				array( 'label' => 'ইভেন্ট', 'type' => 'text' ),
				array( 'label' => 'তারিখ', 'type' => 'text' ),
			),
			'calendar_lines' => array(
				array( 'label' => 'মাস', 'type' => 'text' ),
				array( 'label' => 'ইভেন্ট', 'type' => 'text' ),
			),
			'about_goals_lines' => array(
				array( 'label' => 'লক্ষ্য', 'type' => 'text' ),
			),
			'academic_programs_lines' => array(
				array( 'label' => 'আইকন', 'type' => 'text', 'ph' => '📚' ),
				array( 'label' => 'শিরোনাম', 'type' => 'text' ),
				array( 'label' => 'সাবটাইটেল', 'type' => 'text' ),
				array( 'label' => 'বৈশিষ্ট্য', 'type' => 'area', 'ph' => 'একাধিক হলে ; দিয়ে আলাদা করুন' ),
			),
			'academic_exams_lines' => array(
				array( 'label' => 'পরীক্ষা', 'type' => 'text' ),
				array( 'label' => 'মাস', 'type' => 'text' ),
				array( 'label' => 'শ্রেণি', 'type' => 'text' ),
			),
			'admission_process_lines' => array(
				array( 'label' => 'ধাপ', 'type' => 'text' ),
				array( 'label' => 'বিবরণ', 'type' => 'area' ),
			),
			'admission_docs_lines' => array(
				array( 'label' => 'কাগজপত্র', 'type' => 'text' ),
			),
			'students_features_lines' => array(
				array( 'label' => 'আইকন', 'type' => 'text', 'ph' => '📚' ),
				array( 'label' => 'শিরোনাম', 'type' => 'text' ),
				array( 'label' => 'বিবরণ', 'type' => 'area' ),
			),
			'students_clubs_lines' => array(
				array( 'label' => 'আইকন', 'type' => 'text', 'ph' => '🔭' ),
				array( 'label' => 'নাম', 'type' => 'text' ),
				array( 'label' => 'বিবরণ', 'type' => 'area' ),
			),
			'students_conduct_lines' => array(
				array( 'label' => 'আচরণবিধি', 'type' => 'text' ),
			),
		);
	}
	return isset( $maps[ $key ] ) ? $maps[ $key ] : null;
}

/** Implode posted rows back to pipe-text. Input must already be unslashed. */
function uturn_rows_to_text( $rows, $ncols = 0 ) {
	$lines = array();
	foreach ( (array) $rows as $r ) {
		$cells = array();
		$vals = array_values( (array) $r );
		if ( $ncols > 0 ) {
			$vals = array_slice( $vals, 0, $ncols );
		}
		foreach ( $vals as $c ) {
			$c = str_replace( '|', '', sanitize_text_field( (string) $c ) );
			$cells[] = $c;
		}
		while ( count( $cells ) && '' === end( $cells ) ) {
			array_pop( $cells );
		}
		if ( ! count( $cells ) ) {
			continue;
		}
		$lines[] = implode( '|', $cells );
	}
	return implode( "\n", $lines );
}

/** Render one labeled cell input/select/textarea. */
function uturn_rows_cell( $name, $col, $value ) {
	$type = isset( $col['type'] ) ? $col['type'] : 'text';
	$ph = isset( $col['ph'] ) ? ' placeholder="' . esc_attr( $col['ph'] ) . '"' : '';
	if ( 'select' === $type ) {
		$html = '<select name="' . esc_attr( $name ) . '">';
		$opts = isset( $col['options'] ) && is_array( $col['options'] ) ? $col['options'] : array();
		if ( '' !== $value && ! array_key_exists( $value, $opts ) ) {
			$html .= '<option value="' . esc_attr( $value ) . '" selected>' . esc_html( $value ) . '</option>';
		}
		foreach ( $opts as $v => $l ) {
			$html .= '<option value="' . esc_attr( $v ) . '"' . selected( $value, $v, false ) . '>' . esc_html( $l ) . '</option>';
		}
		return $html . '</select>';
	}
	if ( 'area' === $type ) {
		return '<textarea rows="2" name="' . esc_attr( $name ) . '"' . $ph . '>' . esc_textarea( $value ) . '</textarea>';
	}
	$extra = '';
	if ( 'number' === $type ) {
		$extra .= isset( $col['min'] ) ? ' min="' . esc_attr( $col['min'] ) . '"' : '';
		$extra .= isset( $col['step'] ) ? ' step="' . esc_attr( $col['step'] ) . '"' : '';
	} else {
		$type = 'text';
	}
	return '<input type="' . $type . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '"' . $ph . $extra . '>';
}

/**
 * Render the structured row editor.
 * $name = POST field base (rows post as $name[0][0..]), $text = stored pipe-text.
 */
function uturn_row_editor( $name, $text, $columns, $add_label = '＋ সারি যোগ করুন' ) {
	$ncols = count( $columns );
	if ( $ncols < 1 ) {
		return;
	}
	$rows = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $text ) as $ln ) {
		$ln = trim( $ln );
		if ( '' === $ln ) {
			continue;
		}
		$rows[] = array_slice( array_pad( array_values( array_map( 'trim', explode( '|', $ln ) ) ), $ncols, '' ), 0, $ncols );
	}
	if ( ! count( $rows ) ) {
		$rows[] = array_fill( 0, $ncols, '' );
	}
	static $assets_done = false;
	if ( ! $assets_done ) {
		$assets_done = true;
		echo '<style>.ut-rows-wrap{overflow-x:auto;margin:4px 0 2px}.ut-rows{border-collapse:collapse;min-width:100%}.ut-rows th{font-size:12px;text-transform:uppercase;letter-spacing:.02em;color:#0b2a5b;background:#f4f8ff;border:1px solid #d7e3f5;padding:8px 10px;text-align:left;white-space:nowrap}.ut-rows td{border:1px solid #e2ebfa;padding:6px;background:#fff;vertical-align:top}.ut-rows input[type=text],.ut-rows input[type=number],.ut-rows select,.ut-rows textarea{width:100%;max-width:100%;border:1.5px solid #c9dbef;border-radius:8px;padding:7px 9px;font-size:14px;font-family:inherit;background:#fbfdff;color:#1b2740;box-sizing:border-box}.ut-rows input:focus,.ut-rows select:focus,.ut-rows textarea:focus{border-color:#2f7fe0;outline:2px solid rgba(47,127,224,.25);background:#fff}.ut-rows textarea{min-height:44px;resize:vertical}.ut-rows td.ut-rows-x,.ut-rows th.ut-rows-x{width:44px;min-width:44px;text-align:center;background:#fff}.ut-rows-del{border:1px solid #f1c6c6;background:#fff5f5;color:#b3261e;border-radius:8px;width:32px;height:32px;cursor:pointer;font-size:15px;line-height:1}.ut-rows-del:hover{background:#b3261e;color:#fff}.ut-rows-wrap .screen-reader-text{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0)}</style>';
		echo "<script>document.addEventListener('click',function(e){var a=e.target.closest?e.target.closest('.ut-rows-add'):null;if(a){var w=a.closest('.ut-rows-wrap'),tb=w.querySelector('tbody'),last=tb.querySelector('tr:last-child'),mx=-1,i,m;tb.querySelectorAll('input,select,textarea').forEach(function(el){m=el.name.match(/\\[(\\d+)\\]\\[\\d+\\]$/);if(m)mx=Math.max(mx,+m[1]);});var nr=last.cloneNode(true),ni=mx+1;nr.querySelectorAll('input,select,textarea').forEach(function(el){el.name=el.name.replace(/\\[\\d+\\]\\[(\\d+)\\]$/,'['+ni+'][$1]');if(el.tagName==='SELECT'){el.selectedIndex=0;}else{el.value='';}});tb.appendChild(nr);var f=nr.querySelector('input,select,textarea');if(f)f.focus();return;}var d=e.target.closest?e.target.closest('.ut-rows-del'):null;if(d){var w2=d.closest('.ut-rows-wrap'),rows=w2.querySelectorAll('tbody tr');if(rows.length>1){d.closest('tr').remove();}else{d.closest('tr').querySelectorAll('input,select,textarea').forEach(function(el){if(el.tagName==='SELECT'){el.selectedIndex=0;}else{el.value='';}});}}});</script>";
	}
	$minw = $ncols * 140;
	echo '<div class="ut-rows-wrap"><table class="ut-rows" style="min-width:' . (int) $minw . 'px"><thead><tr>';
	foreach ( $columns as $c ) {
		echo '<th scope="col">' . esc_html( isset( $c['label'] ) ? $c['label'] : '' ) . '</th>';
	}
	echo '<th class="ut-rows-x" scope="col"><span class="screen-reader-text">মুছুন</span></th></tr></thead><tbody>';
	foreach ( $rows as $i => $r ) {
		echo '<tr>';
		foreach ( $columns as $c => $col ) {
			echo '<td>' . uturn_rows_cell( $name . '[' . $i . '][' . $c . ']', $col, isset( $r[ $c ] ) ? $r[ $c ] : '' ) . '</td>';
		}
		echo '<td class="ut-rows-x"><button type="button" class="ut-rows-del" title="সারি মুছুন" aria-label="সারি মুছুন">×</button></td></tr>';
	}
	echo '</tbody></table><p style="margin:8px 0 2px"><button type="button" class="button ut-rows-add">' . esc_html( $add_label ) . '</button></p></div>';
}
