<?php
/**
 * EduTurn — Theme setup, white-labeling, structured data.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', 'uturn_setup' );
function uturn_setup() {
	load_theme_textdomain( 'eduturn', UTURN_DIR . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support(
		'html5',
		array( 'search-form', 'gallery', 'caption', 'style', 'script' )
	);
	add_theme_support(
		'custom-logo',
		array( 'height' => 62, 'width' => 62, 'flex-height' => true, 'flex-width' => true )
	);
	add_image_size( 'ut-person', 400, 400, true );
	add_image_size( 'ut-card', 800, 600, true );
	add_image_size( 'ut-hero', 1920, 900, true );
	register_nav_menus(
		array(
			'ut_primary'        => __( 'Primary Menu (Nav Row)', 'eduturn' ),
			'ut_footer_links'   => __( 'Footer: Important Links', 'eduturn' ),
			'ut_footer_academic'=> __( 'Footer: Academic', 'eduturn' ),
		)
	);
	$GLOBALS['content_width'] = 1200;
}

/* Body classes: framework hook + sticky mobile-CTA offset (mirrors static JS). */
add_filter( 'body_class', 'uturn_body_class' );
function uturn_body_class( $classes ) {
	$classes[] = 'uturn';
	if ( apply_filters( 'uturn_mobile_cta', true ) ) {
		$classes[] = 'has-mobile-cta';
	}
	return $classes;
}

/* ---------- Backend + login white-labeling (UTurn Digital Solutions) ---------- */
add_filter( 'admin_footer_text', 'uturn_admin_footer' );
function uturn_admin_footer() {
	return '<span id="footer-thankyou">Powered by <a href="https://uturndigital.com.bd" target="_blank" rel="noopener">UTurn Digital Solutions</a> — EduTurn v' . UTURN_VERSION . '</span>';
}

add_filter( 'login_headerurl', function () { return 'https://uturndigital.com.bd'; } );
add_filter( 'login_headertext', function () { return 'UTurn Digital Solutions — EduTurn'; } );

add_action( 'login_enqueue_scripts', 'uturn_login_brand' );
function uturn_login_brand() {
	$logo = esc_url( uturn_logo_url() );
	echo '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Tiro+Bangla:ital@0;1&display=swap">';
	echo '<style>'
		. 'body.login{background:radial-gradient(1100px 520px at 85% -10%,#1B7FC2 0%,rgba(27,127,194,0) 60%),radial-gradient(900px 480px at 8% 110%,#123E7C 0%,rgba(18,62,124,0) 55%),linear-gradient(135deg,#0B2440 0%,#0B4EA8 55%,#1B7FC2 100%);min-height:100vh;font-family:Inter,Tiro Bangla,system-ui,sans-serif}'
		. '#login{width:min(400px,92vw);margin:auto;padding:5vh 0 48px}'
		. '#login h1 a{background-image:url(' . $logo . ');background-size:contain;background-repeat:no-repeat;width:96px;height:96px;border-radius:24px;box-shadow:0 16px 40px rgba(0,0,0,.35);margin-bottom:14px}'
		. '.login h1{text-align:center}.login .uturn-tag{text-align:center;color:#DCEBFb;font-size:15px;margin:6px 0 2px}'
		. '.login .uturn-school{text-align:center;color:#fff;font-weight:800;font-size:23px;line-height:1.35;margin:0 0 18px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-family:\'Tiro Bangla\',Inter,system-ui,sans-serif}'
		. '.login form#loginform,.login form#registerform,.login form#lostpasswordform{background:rgba(255,255,255,.97);border:0;border-radius:18px;padding:28px 28px 24px;box-shadow:0 24px 70px rgba(0,20,60,.45)}'
		. '.login label{color:#14324A;font-weight:600;font-size:14px}'
		. '.login input[type=text],.login input[type=password],.login input[type=email]{border:1.5px solid #C9DBEF;border-radius:10px;padding:9px 12px;font-size:15px;background:#F4F8FD;margin-top:4px}'
		. '.login input[type=text]:focus,.login input[type=password]:focus{border-color:#0B4EA8;box-shadow:0 0 0 3px rgba(11,78,168,.15);outline:0}'
		. '.login.wp-core-ui .button-primary{background:linear-gradient(135deg,#0B4EA8,#1B7FC2);border:0;border-radius:10px;font-size:16px;font-weight:700;padding:7px 0;width:100%;height:auto;float:none;text-shadow:none;box-shadow:0 10px 24px rgba(11,78,168,.35);transition:.2s}'
		. '.login.wp-core-ui .button-primary:hover{filter:brightness(1.08);transform:translateY(-1px)}'
		. '.login #nav,.login #backtoblog{text-align:center;margin-top:16px}'
		. '.login #nav a,.login #backtoblog a,.login .language-switcher label{color:#EAF3FD!important;text-decoration:none;font-size:14px}'
		. '.login #nav a:hover,.login #backtoblog a:hover{text-decoration:underline}'
		. '.login .message,.login .success,.login #login_error{border-radius:12px;border:0;box-shadow:0 10px 30px rgba(0,20,60,.35)}'
		. '.login .uturn-links{text-align:center;margin-top:14px;color:#DCEBFB;font-size:13.5px}'
		. '.login .uturn-links a{color:#fff;font-weight:600;margin:0 6px;text-decoration:none;border-bottom:1px dotted rgba(255,255,255,.6)}'
		. '</style>';
}
add_action( 'login_footer', 'uturn_login_links' );
function uturn_login_links() {
	$sp = function_exists( 'uturn_url' ) ? uturn_url( 'student-portal' ) : home_url( '/student-portal/' );
	$tp = function_exists( 'uturn_url' ) ? uturn_url( 'teacher-portal' ) : home_url( '/teacher-portal/' );
	echo '<div class="uturn-links"><a href="' . esc_url( home_url( '/' ) ) . '">🏠 হোম</a>·<a href="' . esc_url( $sp ) . '">🎓 শিক্ষার্থী পোর্টাল</a>·<a href="' . esc_url( $tp ) . '">👩‍🏫 শিক্ষক পোর্টাল</a></div>';
	$school = function_exists( 'uturn_opt' ) ? uturn_opt( 'school_name_bn', get_bloginfo( 'name' ) ) : get_bloginfo( 'name' );
	echo '<script>(function(){var h=document.querySelector(".login h1");if(!h)return;var SN=' . wp_json_encode( $school ) . ';var tag=document.querySelector(".uturn-tag");if(!tag){tag=document.createElement("p");tag.className="uturn-tag";tag.textContent="\\u09b8\\u09cd\\u0995\\u09c1\\u09b2 \\u09ae\\u09cd\\u09af\\u09be\\u09a8\\u09c7\\u099c\\u09ae\\u09c7\\u09a8\\u09cd\\u099f \\u09b8\\u09bf\\u09b8\\u09cd\\u099f\\u09c7\\u09ae";h.after(tag);}var el=document.querySelector(".uturn-school");if(!el){el=document.createElement("div");el.className="uturn-school";el.textContent=SN;tag.after(el);}var box=document.getElementById("login");function fit(){if(!el||!box)return;var w=box.clientWidth;if(w<=0)return;var lo=12,hi=23;el.style.fontSize=hi+"px";if(el.scrollWidth<=w)return;while(hi-lo>0.5){var m=(lo+hi)/2;el.style.fontSize=m+"px";if(el.scrollWidth>w)hi=m;else lo=m;}el.style.fontSize=lo+"px";}fit();window.addEventListener("resize",fit);if(document.fonts&&document.fonts.ready)document.fonts.ready.then(fit);})();</script>';
}

/* ---------- Head extras: theme-color + dynamic School JSON-LD ---------- */
add_action( 'wp_head', 'uturn_head_extras', 5 );
function uturn_head_extras() {
	echo '<meta name="theme-color" content="' . esc_attr( uturn_opt( 'color_primary', '#0B4EA8' ) ) . '">' . "\n";
	$schema = array(
		'@context'      => 'https://schema.org',
		'@type'         => 'School',
		'name'          => uturn_opt( 'school_name_bn', 'আলোকিত বিদ্যানিকেতন' ),
		'alternateName' => uturn_opt( 'school_name_en', 'Alokito Biddyaniketon' ),
		'slogan'        => uturn_opt( 'tagline', 'আলোকিত ভবিষ্যতের পথে' ),
		'url'           => home_url( '/' ),
		'logo'          => UTURN_URI . '/assets/images/logo.svg',
		'foundingDate'  => uturn_opt( 'established', '2001' ),
		'telephone'     => uturn_opt( 'phone_href', '+8809611234567' ),
		'email'         => uturn_opt( 'email', 'info@alokitobiddyaniketon.edu.bd' ),
		'address'       => array(
			'@type'         => 'PostalAddress',
			'streetAddress' => uturn_opt( 'address', 'বাড়ি ১২, রোড ০৭, ধানমন্ডি' ),
			'addressLocality'=> 'ঢাকা',
			'postalCode'    => '1205',
			'addressCountry'=> 'BD',
		),
	);
	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}

/* ---------- First-run setup + self-heal (pages, identity, rewrite rules) ---------- */
add_action( 'after_switch_theme', 'uturn_theme_activated' );
function uturn_theme_activated() {
	if ( function_exists( 'uturn_register_roles' ) ) {
		uturn_register_roles();
	}
	if ( function_exists( 'uturn_run_seeder' ) ) {
		uturn_run_seeder();
	}
	flush_rewrite_rules();
}

/* Fallback: theme updated (not switched) or activation seed never ran. */
add_action( 'admin_init', 'uturn_maybe_first_seed' );
function uturn_maybe_first_seed() {
	if ( ! current_user_can( 'manage_options' ) || wp_doing_ajax() || wp_doing_cron() ) {
		return;
	}
	if ( get_option( 'uturn_seed_done' ) ) {
		return;
	}
	if ( function_exists( 'uturn_run_seeder' ) ) {
		uturn_run_seeder();
	}
	flush_rewrite_rules();
}

/* Self-heal: missing essential pages (e.g. after update) — checked twice daily. */
add_action( 'admin_init', 'uturn_ensure_pages' );
function uturn_ensure_pages() {
	if ( ! current_user_can( 'manage_options' ) || wp_doing_ajax() || wp_doing_cron() ) {
		return;
	}
	if ( get_transient( 'uturn_pages_ok' ) ) {
		return;
	}
	$missing = array();
	foreach ( uturn_essential_pages() as $slug => $def ) {
		if ( ! get_page_by_path( $slug, OBJECT, 'page' ) ) {
			$missing[] = $slug;
		}
	}
	if ( $missing ) {
		uturn_seed_pages();
		uturn_seed_identity();
		flush_rewrite_rules();
		set_transient( 'uturn_pages_healed', count( $missing ), 300 );
	}
	set_transient( 'uturn_pages_ok', 1, 12 * HOUR_IN_SECONDS );
}

add_action( 'admin_notices', 'uturn_setup_notices' );
function uturn_setup_notices() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$healed = get_transient( 'uturn_pages_healed' );
	if ( $healed ) {
		echo '<div class="notice notice-success" role="status"><p><b>EduTurn:</b> ' . (int) $healed . 'টি প্রয়োজনীয় পেজ স্বয়ংক্রিয়ভাবে তৈরি হয়েছে। সব লিংক এখন কাজ করবে।</p></div>';
	}
	if ( isset( $_GET['uturn_tool'] ) ) {
		$m = array( 'flushed' => 'পার্মালিংক রুল রিফ্রেশ করা হয়েছে।', 'plain' => 'জরুরি ফলব্যাক: Plain পার্মালিংক চালু হয়েছে। হোস্টিং-এ mod_rewrite ঠিক করে Settings > Permalinks থেকে Post name-এ ফিরে যান।' );
		if ( isset( $m[ $_GET['uturn_tool'] ] ) ) {
			echo '<div class="notice notice-success" role="status"><p><b>EduTurn:</b> ' . esc_html( $m[ $_GET['uturn_tool'] ] ) . '</p></div>';
		}
	}
	/* Pretty permalinks selected but server cannot rewrite → every inner page 404s. */
	$pretty = (string) get_option( 'permalink_structure' );
	if ( $pretty !== '' && function_exists( 'got_url_rewrite' ) && ! got_url_rewrite() ) {
		$flush = wp_nonce_url( admin_url( 'admin-post.php?action=uturn_flush_rules' ), 'uturn_tools' );
		$plain = wp_nonce_url( admin_url( 'admin-post.php?action=uturn_plain_links' ), 'uturn_tools' );
		echo '<div class="notice notice-error" role="alert"><p><b>EduTurn সতর্কতা:</b> সার্ভারে URL rewrite (mod_rewrite) পাওয়া যাচ্ছে না — তাই ভেতরের সব পেজে ৪০৪ আসতে পারে। '
			. 'হোস্টিং সাপোর্টকে বলুন: <code>mod_rewrite চালু + AllowOverride All</code>। '
			. '<a class="button button-small" href="' . esc_url( $flush ) . '">নিয়ম রিফ্রেশ করুন</a> '
			. '<a class="button button-small" href="' . esc_url( $plain ) . '" onclick="return confirm(\'সাময়িকভাবে Plain লিংকে যাবেন?\')">জরুরি: Plain লিংক চালু করুন</a></p></div>';
	}
}

add_action( 'admin_post_uturn_flush_rules', 'uturn_tool_flush' );
function uturn_tool_flush() {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'uturn_tools' ) ) {
		wp_die( 'Unauthorized.' );
	}
	flush_rewrite_rules();
	wp_safe_redirect( admin_url( 'admin.php?page=eduturn-seeder&uturn_tool=flushed' ) );
	exit;
}

add_action( 'admin_post_uturn_plain_links', 'uturn_tool_plain' );
function uturn_tool_plain() {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'uturn_tools' ) ) {
		wp_die( 'Unauthorized.' );
	}
	update_option( 'permalink_structure', '' );
	flush_rewrite_rules();
	wp_safe_redirect( admin_url( 'admin.php?page=eduturn-seeder&uturn_tool=plain' ) );
	exit;
}

/* Expose the BN/EN toggle to JS: <html class="ut-en"> in English mode. */
add_filter( 'language_attributes', 'uturn_lang_html_class' );
function uturn_lang_html_class( $attr ) {
	if ( function_exists( 'uturn_lang' ) && 'en' === uturn_lang() ) {
		$attr .= ' class="ut-en"';
	}
	return $attr;
}

/* Favicon fallback: use the logo mark when no Site Icon is set. */
add_action( 'wp_head', 'uturn_favicon_fallback', 1 );
function uturn_favicon_fallback() {
	if ( function_exists( 'has_site_icon' ) && has_site_icon() ) {
		return;
	}
	echo '<link rel="icon" href="' . esc_url( UTURN_URI . '/assets/images/logo.svg' ) . '" type="image/svg+xml">' . "\n";
}
/* One-time DB index for meta lookups (results/entry/lookup scale with rows). */
add_action( 'init', 'uturn_db_index_once', 40 );
function uturn_db_index_once() {
	if ( is_admin() || get_option( 'uturn_db_index_v1', '' ) === 'done' ) {
		return;
	}
	global $wpdb;
	$has = $wpdb->get_var( "SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{$wpdb->postmeta}' AND INDEX_NAME='ut_meta_kv'" );
	if ( ! $has ) {
		$wpdb->query( "CREATE INDEX ut_meta_kv ON {$wpdb->postmeta} (meta_key(191), meta_value(191))" );
	}
	update_option( 'uturn_db_index_v1', 'done', false );
}
