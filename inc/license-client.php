<?php
/**
 * EduTurn — License Client (SMS + ERP subscription enforcement).
 * Activation / signed check-ins / grace cache / PHP-level gates / SSO consumer.
 * All validation is server-side PHP. No license secret ever touches JS.
 */
define( 'EDUTURN_LICENSE_CLIENT', '1.0.0' );

/* ============ pure decision engine (harness-safe, no WP calls) ============ */
function eduturn_license_effective( array $st, $now ) {
	$now = (int) $now;
	if ( empty( $st['activated'] ) ) {
		return array( 'erp' => false, 'site' => true, 'code' => 'inactive', 'remaining' => null );
	}
	$life = ! empty( $st['lifetime'] );
	$exp  = (int) ( $st['expires_at'] ?? 0 );
	$rem  = $life ? null : (int) floor( ( $exp - $now ) / 86400 );
	$status = (string) ( $st['status'] ?? 'active' );
	if ( $status === 'revoked' ) {
		return array( 'erp' => false, 'site' => false, 'code' => 'revoked', 'remaining' => $rem );
	}
	if ( $status === 'suspended' ) {
		return array( 'erp' => false, 'site' => ! empty( $st['site'] ), 'code' => 'suspended', 'remaining' => $rem );
	}
	$trial = (int) ( $st['trial_ends'] ?? 0 );
	if ( $trial > 0 && $trial < $now ) {
		return array( 'erp' => false, 'site' => ! empty( $st['site'] ), 'code' => 'trial_expired', 'remaining' => $rem );
	}
	if ( ! $life && $exp > 0 && $exp < $now ) {
		return array( 'erp' => false, 'site' => ! empty( $st['site'] ), 'code' => 'expired', 'remaining' => $rem );
	}
	if ( $status === 'expired' ) {
		return array( 'erp' => false, 'site' => ! empty( $st['site'] ), 'code' => 'expired', 'remaining' => $rem );
	}
	if ( ! empty( $st['link_dead'] ) && $now > (int) ( $st['grace_until'] ?? 0 ) ) {
		return array( 'erp' => false, 'site' => true, 'code' => 'link_dead', 'remaining' => $rem );
	}
	$feat = isset( $st['features'] ) && is_array( $st['features'] ) ? $st['features'] : array( 'erp' );
	return array( 'erp' => ! empty( $st['erp'] ) && in_array( 'erp', $feat, true ), 'site' => ! empty( $st['site'] ), 'code' => 'active', 'remaining' => $rem );
}

/* ============ state ============ */
function eduturn_license_defaults() {
	return array(
		'activated' => 0, 'email' => '', 'key_last4' => '', 'instance_id' => '', 'secret' => '',
		'status' => 'inactive', 'erp' => 0, 'site' => 1, 'plan' => 'standard', 'features' => array( 'erp' ),
		'expires_at' => 0, 'lifetime' => 0, 'trial_ends' => 0, 'last_check' => 0, 'last_ok' => 0, 'check_hours' => 0,
		'grace_until' => 0, 'link_dead' => 0, 'renew_url' => '', 'message' => '', 'sent' => array(),
	);
}

function eduturn_license_state() {
	$st = get_option( 'eduturn_license', array() );
	return array_merge( eduturn_license_defaults(), is_array( $st ) ? $st : array() );
}

function eduturn_license_save( $st ) {
	update_option( 'eduturn_license', $st, false );
}

function eduturn_license_server() {
	$url = function_exists( 'get_option' ) ? get_option( 'eduturn_license_server', '' ) : '';
	if ( ! is_string( $url ) || $url === '' ) {
		$url = 'https://uturndigital.com.bd';
	}
	return apply_filters( 'eduturn_license_server_url', $url );
}

function eduturn_license_domain() {
	$host = strtolower( (string) parse_url( home_url(), PHP_URL_HOST ) );
	return preg_replace( '/^www\./', '', $host );
}

function eduturn_license_can( $area = 'erp' ) {
	$eff = eduturn_license_effective( eduturn_license_state(), time() );
	return $area === 'site' ? $eff['site'] : $eff['erp'];
}

/* ============ signed transport ============ */
/* Central may run plain permalinks (no /wp-json/ rewrite) — retry via ?rest_route=. */
function eduturn_license_http( $path, $args ) {
	$base = trailingslashit( eduturn_license_server() );
	$res  = wp_remote_post( $base . 'wp-json/eduturn-license/v1/' . $path, $args );
	if ( ! is_wp_error( $res ) ) {
		$body = ltrim( (string) wp_remote_retrieve_body( $res ) );
		if ( $body === '' || ( $body[0] !== '{' && $body[0] !== '[' ) ) {
			$res = wp_remote_post( $base . '?rest_route=/eduturn-license/v1/' . $path, $args );
		}
	}
	return $res;
}

/** Verify a signed central response. Server signs json($data) WITHOUT server_time/nonce/sig. */
function eduturn_license_verify_block( $data, $secret, $domain ) {
	if ( ! is_array( $data ) || ! isset( $data['server_time'], $data['nonce'], $data['sig'] ) || ! is_string( $data['sig'] ) ) {
		return false;
	}
	$payload = $data;
	unset( $payload['sig'], $payload['server_time'], $payload['nonce'] );
	$expect = hash_hmac( 'sha256', (int) $data['server_time'] . "\n" . (string) $data['nonce'] . "\n" . strtolower( trim( (string) $domain ) ) . "\n" . hash( 'sha256', (string) wp_json_encode( $payload ) ), (string) $secret );
	if ( ! hash_equals( $expect, strtolower( $data['sig'] ) ) ) {
		return false;
	}
	return abs( time() - (int) $data['server_time'] ) <= 600;
}

function eduturn_license_post( $path, $extra = array(), $timeout = 15 ) {
	$st = eduturn_license_state();
	if ( empty( $st['secret'] ) || empty( $st['instance_id'] ) ) {
		return new WP_Error( 'inactive', 'License not activated.' );
	}
	$domain = eduturn_license_domain();
	$body   = wp_json_encode( array_merge( array( 'instance_id' => $st['instance_id'], 'domain' => $domain ), $extra ) );
	$ts     = time();
	$nonce  = bin2hex( random_bytes( 16 ) );
	$sig    = hash_hmac( 'sha256', $ts . "\n" . $nonce . "\n" . $domain . "\n" . hash( 'sha256', $body ), $st['secret'] );
	$res    = eduturn_license_http( $path, array(
		'timeout'   => $timeout,
		'sslverify' => apply_filters( 'eduturn_license_sslverify', true ),
		'headers'   => array( 'Content-Type' => 'application/json', 'X-EDU-TS' => $ts, 'X-EDU-Nonce' => $nonce, 'X-EDU-Sig' => $sig ),
		'body'      => $body,
	) );
	if ( is_wp_error( $res ) ) {
		return $res;
	}
	$code = (int) wp_remote_retrieve_response_code( $res );
	$raw  = (string) wp_remote_retrieve_body( $res );
	$data = json_decode( $raw, true );
	if ( ! is_array( $data ) ) {
		return new WP_Error( 'bad_response', 'License server gave an unreadable response.' );
	}
	if ( $code >= 400 ) {
		return new WP_Error( $data['code'] ?? 'server', $data['message'] ?? ( 'License server error ' . $code ) );
	}
	// verify mutual signature
	if ( ! isset( $data['server_time'], $data['nonce'], $data['sig'] ) ) {
		return new WP_Error( 'unsigned', 'License server response is not signed.' );
	}
	if ( ! eduturn_license_verify_block( $data, $st['secret'], $domain ) ) {
		return new WP_Error( 'sig', 'License server signature invalid.' );
	}
	return $data;
}

/* ============ activation / check / deactivate ============ */
function eduturn_license_activate( $email, $key ) {
	$email = sanitize_email( $email );
	$key   = trim( (string) $key );
	if ( ! is_email( $email ) || $key === '' ) {
		return new WP_Error( 'args', 'Purchase email and activation code are required.' );
	}
	$iid    = bin2hex( random_bytes( 12 ) );
	$secret = bin2hex( random_bytes( 32 ) );
	$domain = eduturn_license_domain();
	$res    = eduturn_license_http( 'activate', array(
		'timeout'   => 20,
		'sslverify' => apply_filters( 'eduturn_license_sslverify', true ),
		'headers'   => array( 'Content-Type' => 'application/json' ),
		'body'      => wp_json_encode( array(
			'email' => $email, 'license_key' => $key, 'domain' => $domain,
			'instance_id' => $iid, 'instance_secret' => $secret,
			'school_name' => get_bloginfo( 'name' ), 'telemetry' => eduturn_license_telemetry(),
		) ),
	) );
	if ( is_wp_error( $res ) ) {
		return new WP_Error( 'net', 'Could not reach the license server: ' . $res->get_error_message() );
	}
	$code = (int) wp_remote_retrieve_response_code( $res );
	$data = json_decode( (string) wp_remote_retrieve_body( $res ), true );
	if ( $code >= 400 || ! is_array( $data ) || empty( $data['ok'] ) ) {
		return new WP_Error( $data['code'] ?? 'activate', $data['message'] ?? 'Activation failed.' );
	}
	// verify activation response signature (keyed by the secret we just generated)
	if ( ! eduturn_license_verify_block( $data, $secret, $domain ) ) {
		return new WP_Error( 'sig', 'License server signature invalid.' );
	}
	$st = eduturn_license_defaults();
	$st['activated']   = 1;
	$st['email']       = $email;
	$st['key_last4']   = substr( preg_replace( '/[^A-Z0-9]/i', '', strtoupper( $key ) ), -4 );
	$st['instance_id'] = $iid;
	$st['secret']      = $secret;
	eduturn_license_apply_block( $st, $data );
	eduturn_license_save( $st );
	eduturn_license_ensure_cron();
	return true;
}

function eduturn_license_apply_block( &$st, $data ) {
	foreach ( array( 'status', 'plan', 'renew_url', 'message' ) as $k ) {
		if ( isset( $data[ $k ] ) ) {
			$st[ $k ] = is_scalar( $data[ $k ] ) ? (string) $data[ $k ] : $st[ $k ];
		}
	}
	$st['erp']        = empty( $data['erp'] ) ? 0 : 1;
	$st['site']       = empty( $data['site'] ) ? 0 : 1;
	$st['lifetime']   = empty( $data['lifetime'] ) ? 0 : 1;
	$st['expires_at'] = (int) ( $data['expires_at'] ?? 0 );
	$st['trial_ends'] = (int) ( $data['trial_ends'] ?? 0 );
	if ( ! empty( $data['check_hours'] ) ) {
		$st['check_hours'] = max( 1, (int) $data['check_hours'] );
	}
	if ( isset( $data['features'] ) && is_array( $data['features'] ) ) {
		$st['features'] = array_values( array_map( 'sanitize_key', $data['features'] ) );
	}
	$st['last_check'] = time();
	$st['last_ok']    = time();
	$st['link_dead']  = 0;
	$grace            = isset( $data['grace_days'] ) ? max( 1, (int) $data['grace_days'] ) : 7;
	$grace            = apply_filters( 'eduturn_license_grace_days', $grace );
	$st['grace_until'] = time() + $grace * 86400;
}

function eduturn_license_check( $force = false ) {
	$st = eduturn_license_state();
	if ( empty( $st['activated'] ) ) {
		return $st;
	}
	$hours = ! empty( $st['check_hours'] ) ? (int) $st['check_hours'] : 6;
	$hours = max( 1, min( $hours, 6 ) ); // enforcement latency cap: switches land within ≤6h
	$hours = apply_filters( 'eduturn_license_check_hours', $hours );
	if ( ! $force && $st['last_check'] > time() - $hours * 3600 ) {
		return $st;
	}
	if ( get_transient( 'edu_lic_checking' ) ) {
		return $st;
	}
	set_transient( 'edu_lic_checking', 1, 120 );
	$res = eduturn_license_post( 'check', array( 'telemetry' => eduturn_license_telemetry() ) );
	delete_transient( 'edu_lic_checking' );
	$st = eduturn_license_state(); // re-read (post may be slow)
	if ( is_wp_error( $res ) ) {
		$st['last_check'] = time();
		$ecode = $res->get_error_code();
		if ( $ecode === 'unknown' ) {
			// License deleted/reset on the server — a deliberate vendor action → fail closed.
			$st['status']  = 'revoked';
			$st['erp']     = 0;
			$st['site']    = 0;
			$st['message'] = 'This installation was removed or reset on the license server. Please re-activate from the License page or contact support.';
		} elseif ( in_array( $ecode, array( 'domain', 'sig' ), true ) ) {
			// transport/DNS failures keep cached status (grace); auth failures surface
			$st['message'] = $res->get_error_message();
		} else {
			$st['link_dead'] = $st['last_ok'] > 0 ? 1 : 0;
		}
		eduturn_license_save( $st );
		return $st;
	}
	eduturn_license_apply_block( $st, $res );
	eduturn_license_save( $st );
	return $st;
}

function eduturn_license_deactivate() {
	$st = eduturn_license_state();
	if ( ! empty( $st['activated'] ) ) {
		eduturn_license_post( 'deactivate', array(), 8 ); // best effort
	}
	$keep = eduturn_license_defaults();
	$keep['email'] = $st['email'];
	eduturn_license_save( $keep );
	wp_clear_scheduled_hook( 'eduturn_license_check_cron' );
	wp_clear_scheduled_hook( 'eduturn_license_notify_cron' );
}

/* ============ cron ============ */
function eduturn_license_ensure_cron() {
	// v2 (1.8.1): hourly enforcement checks so central switches land in ~1h.
	if ( (int) get_option( 'eduturn_license_cron_v', 0 ) < 2 ) {
		wp_clear_scheduled_hook( 'eduturn_license_check_cron' );
		wp_clear_scheduled_hook( 'eduturn_license_notify_cron' );
		update_option( 'eduturn_license_cron_v', 2, false );
	}
	if ( ! wp_next_scheduled( 'eduturn_license_check_cron' ) ) {
		wp_schedule_event( time() + 600, 'hourly', 'eduturn_license_check_cron' );
	}
	if ( ! wp_next_scheduled( 'eduturn_license_notify_cron' ) ) {
		wp_schedule_event( time() + 900, 'daily', 'eduturn_license_notify_cron' );
	}
}

function eduturn_license_cron_check() {
	update_option( 'eduturn_license_last_cronrun', time(), false );
	eduturn_license_check( true );
}

function eduturn_license_thresholds() {
	return array( 30, 15, 7, 3, 1, 0 );
}

function eduturn_license_cron_notify() {
	update_option( 'eduturn_license_last_cronrun', time(), false );
	$st = eduturn_license_check( false );
	if ( empty( $st['activated'] ) ) {
		return;
	}
	$eff = eduturn_license_effective( $st, time() );
	$rem = $eff['remaining']; // null = lifetime
	if ( $rem === null || ! in_array( $eff['code'], array( 'active', 'expired' ), true ) ) {
		return;
	}
	$fire = $rem <= 0 ? 0 : null;
	foreach ( eduturn_license_thresholds() as $t ) {
		if ( $t > 0 && $rem <= $t ) {
			$fire = $t;
			break;
		}
	}
	if ( $fire === null ) {
		return;
	}
	$sig = $st['expires_at'] . ':' . $fire;
	if ( in_array( $sig, (array) $st['sent'], true ) ) {
		return;
	}
	$st['sent'][] = $sig;
	$st['sent']   = array_slice( $st['sent'], -20 );
	eduturn_license_save( $st );
	$school  = get_bloginfo( 'name' );
	$subject = $fire === 0 ? "[$school] EduTurn subscription expired" : "[$school] EduTurn expires in $fire day(s)";
	$body    = $fire === 0
		? "Your EduTurn subscription has expired. ERP features are now locked (all data is safe). Renew here: {$st['renew_url']}"
		: "Your EduTurn subscription expires in $fire day(s). Renew to avoid interruption: {$st['renew_url']}";
	eduturn_safe_mail( $st['email'], $subject, $body );
	do_action( 'eduturn_license_sms', $fire, $st ); // future SMS/WhatsApp gateway hook
}

/* ============ gates ============ */
function eduturn_license_require( $area = 'erp' ) {
	if ( eduturn_license_can( $area ) ) {
		return true;
	}
	$st  = eduturn_license_state();
	$eff = eduturn_license_effective( $st, time() );
	wp_die( eduturn_license_lock_html( $eff['code'], $st ), 'EduTurn License', array( 'response' => 403 ) );
}

function eduturn_license_lock_html( $code, $st = null ) {
	$st  = $st ?: eduturn_license_state();
	$msg = $st['message'] !== '' ? $st['message'] : eduturn_license_default_message( $code );
	$ren = $st['renew_url'] !== '' ? $st['renew_url'] : 'https://uturndigital.com.bd/eduturn-renew/';
	$lic = admin_url( 'admin.php?page=eduturn-license' );
	$can = function_exists( 'current_user_can' ) && current_user_can( 'manage_options' );
	$foot = $can
		? '<a href="' . esc_url( $lic ) . '">License details</a> · <a href="' . esc_url( admin_url() ) . '">Dashboard</a>'
		: 'Please ask your school administrator to activate the license. <a href="' . esc_url( admin_url() ) . '">Dashboard</a>';
	return '<div style="max-width:560px;margin:40px auto;font-family:sans-serif;border:1px solid #d7e4f5;border-top:6px solid #0B4EA8;border-radius:12px;padding:28px;text-align:center;background:#fff">'
		. '<div style="font-size:44px">🔒</div><h2 style="color:#0B4EA8">EduTurn Subscription</h2>'
		. '<p style="font-size:16px">' . esc_html( $msg ) . '</p>'
		. '<p><a href="' . esc_url( $ren ) . '" style="display:inline-block;background:#0B4EA8;color:#fff;padding:10px 26px;border-radius:8px;text-decoration:none">Renew Subscription</a></p>'
		. '<p>' . $foot . '</p>'
		. '<p style="color:#64748b;font-size:13px">All school data remains safe and untouched.</p></div>';
}

function eduturn_license_default_message( $code ) {
	$map = array(
		'expired'       => 'Your EduTurn subscription has expired. Please renew your subscription to continue using the School Management System.',
		'trial_expired' => 'Your EduTurn trial has ended. Please subscribe to continue using the School Management System.',
		'suspended'     => 'Your EduTurn subscription has been suspended. Please contact support.',
		'revoked'       => 'This EduTurn license has been revoked. Please contact support.',
		'link_dead'     => 'EduTurn could not verify your subscription (license server unreachable). Please check your internet connection, then re-check from the License page.',
		'inactive'      => 'EduTurn requires an active license. Please activate with your purchase email and activation code.',
	);
	return $map[ $code ] ?? 'EduTurn license check failed.';
}

/* ============ frontend suspend gate ============ */
function eduturn_license_frontend_gate() {
	if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}
	if ( isset( $_GET['edu_sso'] ) ) {
		return; // SSO consumer handles it
	}
	if ( in_array( $GLOBALS['pagenow'] ?? '', array( 'wp-login.php', 'wp-register.php' ), true ) ) {
		return;
	}
	eduturn_license_check( false ); // throttled: any visit pulls the latest switches promptly
	if ( eduturn_license_can( 'site' ) ) {
		return;
	}
	if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
		add_action( 'wp_footer', 'eduturn_license_bypass_banner' );
		return; // admins can preview/fix
	}
	status_header( 503 );
	header( 'Retry-After: 3600' );
	echo '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Temporarily unavailable</title></head>'
		. '<body style="margin:0;font-family:sans-serif;background:#eef3f9"><div style="max-width:520px;margin:80px auto;background:#fff;border-top:6px solid #0B4EA8;border-radius:12px;padding:36px;text-align:center">'
		. '<div style="font-size:48px">🏫</div><h1 style="color:#0B4EA8">' . esc_html( get_bloginfo( 'name' ) ) . '</h1>'
		. '<p style="font-size:17px">This website is temporarily unavailable due to a subscription issue.<br>Please contact the school office. All data is safe.</p>'
		. '<p style="color:#64748b;font-size:13px">Powered by EduTurn School Management System</p></div></body></html>';
	exit;
}

/** Floating notice for admins previewing a locked site (so "off" tests aren't confusing). */
function eduturn_license_bypass_banner() {
	echo '<div style="position:fixed;left:12px;bottom:12px;z-index:99999;max-width:360px;background:#7f1d1d;color:#fff;font:600 13px/1.5 sans-serif;padding:12px 16px;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.35)">🔒 লক সক্রিয় — দর্শকরা বন্ধ স্ক্রিন দেখছে।<br><span style="font-weight:400;opacity:.9">আপনি অ্যাডমিন বলে সাইট দেখতে পাচ্ছেন। (EduTurn → 🔑 License)</span></div>';
}

/* ============ SSO consumer (?edu_sso=token) ============ */
function eduturn_license_sso_consume() {
	if ( is_admin() || empty( $_GET['edu_sso'] ) ) {
		return;
	}
	$tok = sanitize_text_field( $_GET['edu_sso'] );
	if ( ! ctype_alnum( $tok ) ) {
		wp_die( 'Invalid login token.' );
	}
	$st = eduturn_license_state();
	if ( empty( $st['activated'] ) ) {
		wp_die( 'License not activated.' );
	}
	$res = eduturn_license_post( 'sso-validate', array( 'token' => $tok ), 15 );
	if ( is_wp_error( $res ) || empty( $res['ok'] ) ) {
		wp_die( esc_html( is_wp_error( $res ) ? $res->get_error_message() : 'Login token rejected.' ) );
	}
	$user = false;
	if ( ! empty( $res['login_email'] ) ) {
		$u = get_user_by( 'email', $res['login_email'] );
		if ( $u && in_array( 'administrator', (array) $u->roles, true ) ) {
			$user = $u;
		}
	}
	if ( ! $user ) {
		$admins = get_users( array( 'role' => 'administrator', 'number' => 1, 'orderby' => 'ID', 'order' => 'ASC' ) );
		$user   = $admins ? $admins[0] : false;
	}
	if ( ! $user ) {
		wp_die( 'No local administrator found.' );
	}
	wp_set_current_user( $user->ID );
	wp_set_auth_cookie( $user->ID, true );
	$log   = get_option( 'eduturn_sso_log', array() );
	$log[] = array( 'at' => current_time( 'mysql' ), 'login' => $user->user_login, 'ip' => substr( $_SERVER['REMOTE_ADDR'] ?? '', 0, 45 ), 'from' => 'central' );
	update_option( 'eduturn_sso_log', array_slice( (array) $log, -20 ), false );
	do_action( 'eduturn_remote_login', $user->ID );
	wp_safe_redirect( admin_url() );
	exit;
}

/* ============ telemetry (counts + health only, no PII) ============ */
function eduturn_license_telemetry() {
	global $wp_version, $wpdb;
	$students = 0;
	$teachers = 0;
	$users    = 0;
	if ( function_exists( 'count_users' ) ) {
		$cu = count_users();
		$users = (int) ( $cu['total_users'] ?? 0 );
		foreach ( (array) ( $cu['avail_roles_counts'] ?? array() ) as $role => $n ) {
			if ( stripos( (string) $role, 'student' ) !== false ) {
				$students += (int) $n;
			}
			if ( stripos( (string) $role, 'teacher' ) !== false ) {
				$teachers += (int) $n;
			}
		}
	}
	$db = 'unknown';
	if ( isset( $wpdb ) ) {
		$db = $wpdb->get_var( 'SELECT 1' ) === '1' ? 'ok' : 'fail';
	}
	$last = (int) get_option( 'eduturn_license_last_cronrun', 0 );
	return array(
		'wp' => isset( $wp_version ) ? $wp_version : '', 'php' => PHP_VERSION,
		'theme' => defined( 'UTURN_VERSION' ) ? UTURN_VERSION : '', 'url' => home_url(),
		'ssl' => is_ssl() ? 'yes' : 'no', 'cron' => $last ? human_time_diff( $last ) . ' ago' : 'never',
		'db' => $db, 'sms' => ( $students + $teachers ) > 0 ? 'active' : 'empty',
		'students' => $students, 'teachers' => $teachers, 'users' => $users,
	);
}

/* ============ admin UI ============ */
function eduturn_license_menu() {
	// Top-level (not submenu): zero dependence on the parent menu chain,
	// so the page can never 403 due to parent registration/cap issues.
	add_menu_page( 'EduTurn License', '🔑 License', 'manage_options', 'eduturn-license', 'eduturn_license_page', 'dashicons-lock', 26 );
}

function eduturn_license_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Forbidden.' );
	}
	if ( isset( $_GET['check'] ) && check_admin_referer( 'edu_lic_check' ) ) {
		eduturn_license_check( true );
		wp_safe_redirect( admin_url( 'admin.php?page=eduturn-license&lic_msg=' . rawurlencode( 'Status refreshed from license server.' ) ) );
		exit;
	}
	if ( isset( $_POST['edu_lic'] ) && check_admin_referer( 'edu_lic_form' ) ) {
		$op = sanitize_key( $_POST['edu_lic'] );
		if ( $op === 'activate' ) {
			$r = eduturn_license_activate( $_POST['email'] ?? '', $_POST['key'] ?? '' );
			if ( is_wp_error( $r ) ) {
				echo '<div class="notice notice-error" role="alert"><p>' . esc_html( $r->get_error_message() ) . '</p></div>';
			} else {
				echo '<div class="notice notice-success" role="status"><p>EduTurn activated successfully.</p></div>';
			}
		} elseif ( $op === 'server' ) {
			$url = isset( $_POST['server_url'] ) ? esc_url_raw( wp_unslash( (string) $_POST['server_url'] ) ) : '';
			if ( $url === '' ) {
				delete_option( 'eduturn_license_server' );
			} else {
				update_option( 'eduturn_license_server', $url, false );
			}
			echo '<div class="notice notice-success" role="status"><p>License server updated.</p></div>';
		} elseif ( $op === 'deactivate' ) {
			eduturn_license_deactivate();
			echo '<div class="notice notice-success" role="status"><p>License deactivated on this site. Data untouched.</p></div>';
		}
	}
	if ( ! empty( $_GET['lic_msg'] ) ) {
		echo '<div class="notice notice-success" role="status"><p>' . esc_html( wp_unslash( $_GET['lic_msg'] ) ) . '</p></div>';
	}
	$st  = eduturn_license_state();
	$eff = eduturn_license_effective( $st, time() );
	$lbl = array( 'active' => 'Active', 'expired' => 'Expired', 'suspended' => 'Suspended', 'revoked' => 'Revoked', 'trial_expired' => 'Trial Expired', 'link_dead' => 'Unreachable', 'inactive' => 'Not Activated' );
	echo '<div class="wrap"><h1>🔑 EduTurn License</h1>';
	echo '<div style="background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:16px;max-width:720px"><h2 style="margin-top:0">Status: ' . esc_html( $lbl[ $eff['code'] ] ?? $eff['code'] ) . '</h2>';
	if ( ! empty( $st['activated'] ) ) {
		echo '<p>Email: <b>' . esc_html( $st['email'] ) . '</b> · Key: <code>••••' . esc_html( $st['key_last4'] ) . '</code> · Plan: <b>' . esc_html( $st['plan'] ) . '</b></p>';
		echo '<p>Expires: <b>' . ( $st['lifetime'] ? 'Lifetime' : ( $st['expires_at'] ? esc_html( gmdate( 'Y-m-d', $st['expires_at'] ) ) . ' (' . (int) $eff['remaining'] . ' day(s) left)' : '—' ) ) . '</b> · Last check: ' . ( $st['last_check'] ? esc_html( human_time_diff( $st['last_check'] ) . ' ago' ) : '—' ) . '</p>';
		echo '<p>ERP: <b>' . ( $eff['erp'] ? 'Enabled' : 'LOCKED' ) . '</b> · Website: <b>' . ( $eff['site'] ? 'Enabled' : 'Disabled' ) . '</b></p>';
		echo '<p><a class="button" href="' . esc_url( wp_nonce_url( admin_url( 'admin.php?page=eduturn-license&check=1' ), 'edu_lic_check' ) ) . '">↻ Check now</a> ';
		if ( $st['renew_url'] !== '' ) {
			echo '<a class="button button-primary" href="' . esc_url( $st['renew_url'] ) . '" target="_blank">Renew Subscription</a>';
		}
		echo '</p><form method="post" style="margin-top:10px">' . wp_nonce_field( 'edu_lic_form', '_wpnonce', true, false ) . '<input type="hidden" name="edu_lic" value="deactivate"><button class="button" onclick="return confirm(\'Deactivate on this site? Data stays safe.\')">Deactivate</button></form>';
	} else {
		echo '<form method="post">' . wp_nonce_field( 'edu_lic_form', '_wpnonce', true, false ) . '<input type="hidden" name="edu_lic" value="activate">';
		echo '<p>Purchase / registered email<br><input class="regular-text" type="email" name="email" required></p>';
		echo '<p>Activation code<br><input class="regular-text" name="key" placeholder="EDU-XXXXXX-XXXXXX-XXXXXX" required></p>';
		echo '<p><button class="button button-primary">Activate EduTurn</button></p></form>';
	}
	echo '<hr><form method="post">' . wp_nonce_field( 'edu_lic_form', '_wpnonce', true, false ) . '<input type="hidden" name="edu_lic" value="server">';
	echo '<p>License server URL<br><input class="regular-text" type="url" name="server_url" value="' . esc_attr( eduturn_license_server() ) . '"></p>';
	echo '<p><button class="button">Save Server</button> <span class="description">Change only if UTurn support asks (e.g. staging server).</span></p></form>';
	echo '</div>';
	$log = get_option( 'eduturn_sso_log', array() );
	if ( $log ) {
		echo '<h3>Remote logins (central support)</h3><table class="widefat striped" style="max-width:720px"><tr><th scope="row">Time</th><th scope="row">Local login</th><th scope="row">IP</th></tr>';
		foreach ( array_reverse( (array) $log ) as $l ) {
			echo '<tr><td>' . esc_html( $l['at'] ) . '</td><td>' . esc_html( $l['login'] ) . '</td><td>' . esc_html( $l['ip'] ) . '</td></tr>';
		}
		echo '</table>';
	}
	echo '</div>';
}

function eduturn_license_admin_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$st  = eduturn_license_state();
	$eff = eduturn_license_effective( $st, time() );
	$url = admin_url( 'admin.php?page=eduturn-license' );
	if ( $eff['code'] === 'inactive' ) {
		echo '<div class="notice notice-warning" role="status"><p><b>EduTurn is not activated.</b> ERP features are locked. <a href="' . esc_url( $url ) . '">Activate now</a></p></div>';
	} elseif ( $eff['code'] === 'active' && $eff['remaining'] !== null && $eff['remaining'] <= 30 ) {
		echo '<div class="notice notice-warning" role="status"><p><b>EduTurn expires in ' . (int) $eff['remaining'] . ' day(s).</b> <a href="' . esc_url( $st['renew_url'] ?: $url ) . '">Renew</a></p></div>';
	} elseif ( in_array( $eff['code'], array( 'expired', 'trial_expired', 'suspended', 'revoked' ), true ) ) {
		echo '<div class="notice notice-error" role="alert"><p><b>EduTurn ERP is locked:</b> ' . esc_html( eduturn_license_default_message( $eff['code'] ) ) . ' <a href="' . esc_url( $url ) . '">License details</a></p></div>';
	} elseif ( $eff['code'] === 'link_dead' ) {
		echo '<div class="notice notice-error" role="alert"><p><b>EduTurn could not reach the license server</b> (grace expired). Check connectivity, then <a href="' . esc_url( $url ) . '">re-check</a>.</p></div>';
	}
}

/* ============ hooks ============ */
add_action( 'admin_menu', 'eduturn_license_menu', 5 );
add_action( 'admin_notices', 'eduturn_license_admin_notice' );
add_action( 'template_redirect', 'eduturn_license_frontend_gate', 1 );
add_action( 'init', 'eduturn_license_sso_consume', 1 );
add_action( 'eduturn_license_check_cron', 'eduturn_license_cron_check' );
add_action( 'eduturn_license_notify_cron', 'eduturn_license_cron_notify' );
add_action( 'init', 'eduturn_license_ensure_cron' );
add_action( 'admin_init', 'eduturn_license_admin_throttle' );
function eduturn_license_admin_throttle() {
	eduturn_license_check( false ); // 6h-throttled backup for unreliable WP-Cron
}
