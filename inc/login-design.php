<?php
/**
 * EduTurn — modern portal login experience.
 * Split-screen portal cards with inline Bangla errors and show/hide password,
 * plus Bangla-ized wp-login errors. (wp-login visuals live in setup.php.)
 * No behavior change to authentication itself.
 */

defined( 'ABSPATH' ) || exit;

/* ---------- assets ---------- */
add_action( 'login_enqueue_scripts', 'uturn_login_brand_assets' );
function uturn_login_brand_assets() {
	wp_enqueue_style( 'uturn-login', get_template_directory_uri() . '/assets/css/login.css', array(), UTURN_VERSION );
	if ( function_exists( 'uturn_font_url' ) ) {
		wp_enqueue_style( 'uturn-login-fonts', uturn_font_url(), array(), null );
	}
}

add_action( 'wp_enqueue_scripts', 'uturn_portal_login_assets' );
function uturn_portal_login_assets() {
	if ( ! is_user_logged_in() && is_page_template( array( 'page-student-portal.php', 'page-teacher-portal.php' ) ) ) {
		wp_enqueue_style( 'uturn-login', get_template_directory_uri() . '/assets/css/login.css', array(), UTURN_VERSION );
	}
}

/* ---------- wp-login: Bangla errors (visuals live in setup.php) ---------- */
/* Bangla-ize the common login errors (keeps WP styling + any links intact). */
add_filter( 'wp_login_errors', 'uturn_login_errors_bn' );
function uturn_login_errors_bn( $errors ) {
	if ( ! ( $errors instanceof WP_Error ) ) {
		return $errors;
	}
	$full = array(
		'empty_username' => 'ইউজারনেম বা মোবাইল নম্বর দিন।',
		'empty_password' => 'পাসওয়ার্ড দিন।',
	);
	foreach ( $full as $code => $bn ) {
		if ( isset( $errors->errors[ $code ] ) ) {
			$errors->errors[ $code ] = array( '<strong>ত্রুটি:</strong> ' . $bn );
		}
	}
	foreach ( array( 'incorrect_password', 'invalid_username' ) as $code ) {
		if ( isset( $errors->errors[ $code ] ) ) {
			foreach ( $errors->errors[ $code ] as $i => $msg ) {
				$errors->errors[ $code ][ $i ] = 'ইউজারনেম/মোবাইল বা পাসওয়ার্ড ভুল হয়েছে।<br>' . $msg;
			}
		}
	}
	return $errors;
}

/* ---------- portal split card ---------- */
function uturn_portal_login_card( $title, $subtitle, $role ) {
	$code = sanitize_key( $_GET['login'] ?? '' );
	$msgs = array(
		'failed' => 'ইউজারনেম/মোবাইল বা পাসওয়ার্ড ভুল হয়েছে। আবার চেষ্টা করুন।',
		'empty'  => 'ইউজারনেম ও পাসওয়ার্ড দুটোই দিন।',
	);
	$other       = $role === 'student' ? 'teacher' : 'student';
	$other_label = $role === 'student' ? 'শিক্ষক' : 'শিক্ষার্থী';
	$points      = $role === 'student'
		? array( 'রেজাল্ট ও প্রগ্রেস রিপোর্ট', 'ক্লাস রুটিন ও নোটিশ', 'ফি ও পেমেন্ট তথ্য' )
		: array( 'অনলাইনে হাজিরা নিন', 'শিক্ষার্থী তালিকা দেখুন', 'রুটিন, নোটিশ ও ডাউনলোড' );
	$logo = get_template_directory_uri() . '/assets/images/logo.svg';
	$elogo = get_template_directory_uri() . '/assets/images/eduturn-logo.png';
	?>
	<div class="ut-loginwrap">
		<div class="ut-login-side">
			<img class="ut-login-eduturn" src="<?php echo esc_url( $elogo ); ?>" alt="EduTurn">
			<img class="ut-login-logo" src="<?php echo esc_url( $logo ); ?>" alt="" width="72" height="72">
			<h2><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h2>
			<p><?php echo esc_html( $subtitle ); ?></p>
			<ul class="ut-login-points">
				<?php foreach ( $points as $pt ) : ?><li><?php echo esc_html( $pt ); ?></li><?php endforeach; ?>
			</ul>
			<svg class="ut-login-wave" viewBox="0 0 400 60" preserveAspectRatio="none" aria-hidden="true"><path d="M0,32 C60,60 120,0 200,28 C280,56 340,10 400,34 L400,60 L0,60 Z" fill="rgba(255,255,255,.14)"/></svg>
		</div>
		<div class="ut-login-main">
			<h3><?php echo esc_html( $title ); ?></h3>
			<?php if ( isset( $msgs[ $code ] ) ) : ?>
				<div class="ut-login-err" role="alert"><?php echo esc_html( $msgs[ $code ] ); ?></div>
			<?php endif; ?>
			<?php
			$GLOBALS['ut_portal_form'] = $role;
			wp_login_form( array(
				'redirect'       => get_permalink(),
				'label_username' => 'ইউজারনেম / মোবাইল',
				'label_password' => 'পাসওয়ার্ড',
				'label_remember' => 'মনে রাখুন',
				'label_log_in'   => 'সাইন ইন →',
			) );
			unset( $GLOBALS['ut_portal_form'] );
			?>
			<p class="ut-login-alt"><?php echo esc_html( $other_label ); ?> হলে <a href="<?php echo esc_url( uturn_url( $other . '-portal' ) ); ?>">এখানে সাইন ইন করুন</a> · <a href="<?php echo esc_url( home_url( '/' ) ); ?>">← হোমে ফিরুন</a></p>
		</div>
	</div>
	<script>(function(){var p=document.querySelector('.ut-login-main #user_pass');if(!p||p.dataset.tg)return;p.dataset.tg='1';var b=document.createElement('button');b.type='button';b.className='ut-pwtoggle';b.textContent='দেখুন';b.setAttribute('aria-label','পাসওয়ার্ড দেখুন');b.onclick=function(){var s=p.type==='password';p.type=s?'text':'password';b.textContent=s?'লুকান':'দেখুন';};p.parentNode.appendChild(b);})();</script>
	<?php if ( function_exists( 'uturn_dev_credit' ) ) : ?>
		<div class="ut-login-credit light"><?php echo uturn_dev_credit( 'light' ); ?></div>
	<?php endif; ?>
	<?php
}

/* Developer credit under wp-login (dark gradient -> white logo). */
add_action( 'login_footer', 'uturn_login_credit' );
function uturn_login_credit() {
	if ( function_exists( 'uturn_dev_credit' ) ) {
		echo '<div class="ut-login-credit">' . uturn_dev_credit( 'dark' ) . '</div>';
	}
}

/* Marker (redirect-back trigger) + mobile hint inside portal forms only. */
add_action( 'login_form_middle', 'uturn_portal_form_marker' );
function uturn_portal_form_marker() {
	if ( empty( $GLOBALS['ut_portal_form'] ) ) {
		return;
	}
	echo '<input type="hidden" name="ut_portal" value="' . esc_attr( $GLOBALS['ut_portal_form'] ) . '">';
	echo '<p class="ut-login-hint">📱 মোবাইল নম্বর দিয়েও সাইন ইন করা যাবে</p>';
}

/* Same-host check for portal redirects (open-redirect safe). */
function uturn_portal_back_url() {
	if ( empty( $_POST['ut_portal'] ) || empty( $_POST['redirect_to'] ) ) {
		return '';
	}
	$to   = esc_url_raw( wp_unslash( $_POST['redirect_to'] ) );
	$host = wp_parse_url( $to, PHP_URL_HOST );
	$home = wp_parse_url( home_url(), PHP_URL_HOST );
	if ( ! $host || strcasecmp( $host, $home ) !== 0 ) {
		return '';
	}
	return $to;
}

/* Wrong credentials → back to the portal with an inline error. */
add_action( 'wp_login_failed', 'uturn_portal_login_failed' );
function uturn_portal_login_failed() {
	$to = uturn_portal_back_url();
	if ( $to === '' ) {
		return;
	}
	wp_safe_redirect( add_query_arg( 'login', 'failed', $to ) );
	exit;
}

/* Empty username/password never reach wp_login_failed — catch them here. */
add_filter( 'authenticate', 'uturn_portal_login_empty', 30, 3 );
function uturn_portal_login_empty( $user, $username, $password ) {
	if ( wp_doing_ajax() || $username !== '' || $password !== '' ) {
		return $user;
	}
	$to = uturn_portal_back_url();
	if ( $to === '' ) {
		return $user;
	}
	wp_safe_redirect( add_query_arg( 'login', 'empty', $to ) );
	exit;
}
