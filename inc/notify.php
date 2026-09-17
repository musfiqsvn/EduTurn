<?php
/**
 * EduTurn — Notification engine: SMS + WhatsApp.
 * Manual compose, attendance auto-SMS (absent students), result auto-SMS.
 * Super Admin owns gateway/API config; School Admin sees status, toggles
 * automation, and sends manually. Test-mode default ON: nothing really sends.
 */

defined( 'ABSPATH' ) || exit;

/* ================= numbers ================= */
/** Normalize to 8801XXXXXXXXX or '' when invalid. */
function uturn_sms_to( $phone ) {
	$d = preg_replace( '/\D+/', '', (string) $phone );
	if ( strlen( $d ) === 11 && $d[0] === '0' ) {
		$d = '88' . $d;
	} elseif ( strlen( $d ) === 10 && $d[0] === '1' ) {
		$d = '880' . $d;
	}
	if ( ! preg_match( '/^8801[3-9]\d{8}$/', $d ) ) {
		return '';
	}
	return $d;
}

/* ================= state ================= */
function uturn_nt_on( $which ) {
	$o = uturn_opts();
	if ( $which === 'sms' ) {
		return ( $o['sms_enable'] ?? '0' ) === '1' && trim( (string) ( $o['sms_url'] ?? '' ) ) !== '';
	}
	if ( $which === 'wa' ) {
		return ( $o['wa_enable'] ?? '0' ) === '1' && trim( (string) ( $o['wa_phone_id'] ?? '' ) ) !== '' && trim( (string) ( $o['wa_token'] ?? '' ) ) !== '';
	}
	return false;
}

function uturn_nt_test() {
	$o = uturn_opts();
	return ( $o['sms_test_mode'] ?? '1' ) === '1';
}

function uturn_nt_pref() {
	$o = uturn_opts();
	return ( $o['nt_pref'] ?? 'wa' ) === 'sms' ? 'sms' : 'wa';
}

/* ================= log (option ring, last 200) ================= */
function uturn_notify_log( $channel, $to, $status, $info, $context = '' ) {
	uturn_notify_log_many( array( array( $channel, $to, $status, $info, $context ) ) );
}

function uturn_notify_log_many( $entries ) {
	$log = get_option( 'uturn_notify_log', array() );
	if ( ! is_array( $log ) ) {
		$log = array();
	}
	foreach ( array_reverse( $entries ) as $e ) {
		array_unshift(
			$log,
			array(
				't' => current_time( 'mysql' ), 'ch' => (string) $e[0], 'to' => (string) $e[1],
				'st' => (string) $e[2], 'info' => mb_substr( (string) $e[3], 0, 140 ), 'ctx' => (string) $e[4],
			)
		);
	}
	update_option( 'uturn_notify_log', array_slice( $log, 0, 200 ), false );
}

function uturn_notify_get_log( $limit = 40 ) {
	$log = get_option( 'uturn_notify_log', array() );
	return array_slice( is_array( $log ) ? $log : array(), 0, (int) $limit );
}

/* ================= senders (test-mode aware) ================= */
function uturn_send_sms( $to, $msg, $context = 'api' ) {
	if ( ! uturn_nt_on( 'sms' ) ) {
		return array( false, 'SMS বন্ধ বা API সেট নয়' );
	}
	$to = uturn_sms_to( $to );
	if ( $to === '' ) {
		return array( false, 'ভুল নম্বর' );
	}
	if ( uturn_nt_test() ) {
		uturn_notify_log( 'SMS', $to, 'TEST', $msg, $context );
		return array( true, 'TEST (আসলে পাঠানো হয়নি)' );
	}
	$o = uturn_opts();
	$url = str_replace(
		array( '{to}', '{message}', '{masking}' ),
		array( $to, rawurlencode( $msg ), rawurlencode( (string) ( $o['sms_masking'] ?? '' ) ) ),
		trim( (string) $o['sms_url'] )
	);
	$method = strtoupper( (string) ( $o['sms_method'] ?? 'GET' ) ) === 'POST' ? 'POST' : 'GET';
	$headers = array( 'Accept' => 'application/json' );
	foreach ( explode( "\n", (string) ( $o['sms_headers'] ?? '' ) ) as $line ) {
		if ( strpos( $line, ':' ) !== false ) {
			list( $hk, $hv ) = explode( ':', $line, 2 );
			$hk = trim( $hk );
			if ( $hk !== '' ) {
				$headers[ $hk ] = trim( $hv );
			}
		}
	}
	$args = array( 'timeout' => 12, 'headers' => $headers, 'reject_unsafe_urls' => false );
	if ( $method === 'POST' ) {
		$body = trim( (string) ( $o['sms_body'] ?? '' ) );
		if ( $body !== '' ) {
			$args['body'] = str_replace(
				array( '{to}', '{message}', '{masking}' ),
				array( $to, $msg, (string) ( $o['sms_masking'] ?? '' ) ),
				$body
			);
		}
		$res = wp_remote_post( $url, $args );
	} else {
		$res = wp_remote_get( $url, $args );
	}
	if ( is_wp_error( $res ) ) {
		$info = 'HTTP ত্রুটি: ' . $res->get_error_message();
		uturn_notify_log( 'SMS', $to, 'FAIL', $info, $context );
		return array( false, $info );
	}
	$code = (int) wp_remote_retrieve_response_code( $res );
	$rb = (string) wp_remote_retrieve_body( $res );
	$kw = trim( (string) ( $o['sms_success'] ?? '' ) );
	$ok = $code >= 200 && $code < 300 && ( $kw === '' || stripos( $rb, $kw ) !== false );
	$info = 'HTTP ' . $code . ( $ok ? '' : ' | ' . mb_substr( $rb, 0, 100 ) );
	uturn_notify_log( 'SMS', $to, $ok ? 'OK' : 'FAIL', $info, $context );
	return array( $ok, $info );
}

function uturn_send_wa( $to, $msg, $context = 'api' ) {
	if ( ! uturn_nt_on( 'wa' ) ) {
		return array( false, 'WhatsApp বন্ধ বা সেট নয়' );
	}
	$to = uturn_sms_to( $to );
	if ( $to === '' ) {
		return array( false, 'ভুল নম্বর' );
	}
	if ( uturn_nt_test() ) {
		uturn_notify_log( 'WA', $to, 'TEST', $msg, $context );
		return array( true, 'TEST (আসলে পাঠানো হয়নি)' );
	}
	$o = uturn_opts();
	$res = wp_remote_post(
		'https://graph.facebook.com/v21.0/' . trim( (string) $o['wa_phone_id'] ) . '/messages',
		array(
			'timeout' => 12,
			'headers' => array( 'Authorization' => 'Bearer ' . trim( (string) $o['wa_token'] ), 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( array( 'messaging_product' => 'whatsapp', 'to' => $to, 'type' => 'text', 'text' => array( 'body' => $msg ) ) ),
		)
	);
	if ( is_wp_error( $res ) ) {
		$info = 'HTTP ত্রুটি: ' . $res->get_error_message();
		uturn_notify_log( 'WA', $to, 'FAIL', $info, $context );
		return array( false, $info );
	}
	$code = (int) wp_remote_retrieve_response_code( $res );
	$jb = json_decode( (string) wp_remote_retrieve_body( $res ), true );
	$ok = $code >= 200 && $code < 300 && is_array( $jb ) && isset( $jb['messages'] );
	$info = $ok ? 'HTTP ' . $code . ' (mid ' . substr( (string) ( $jb['messages'][0]['id'] ?? '' ), 0, 18 ) . ')' : 'HTTP ' . $code . ' | ' . mb_substr( (string) wp_remote_retrieve_body( $res ), 0, 100 );
	uturn_notify_log( 'WA', $to, $ok ? 'OK' : 'FAIL', $info, $context );
	return array( $ok, $info );
}

/* ================= recipients ================= */
/** Returns array( mobile, whatsapp ) — whatsapp falls back to mobile. */
function uturn_notify_numbers( $user_id ) {
	$sms = (string) get_user_meta( $user_id, '_ut_phone', true );
	$wa = (string) get_user_meta( $user_id, '_ut_wa', true );
	if ( $wa === '' ) {
		$wa = $sms;
	}
	return array( $sms, $wa );
}

function uturn_notify_user( $user_id, $msg, $context = '' ) {
	list( $sms_raw, $wa_raw ) = uturn_notify_numbers( $user_id );
	$first = uturn_nt_pref();
	$order = $first === 'wa' ? array( 'wa', 'sms' ) : array( 'sms', 'wa' );
	foreach ( $order as $ch ) {
		$raw = $ch === 'wa' ? $wa_raw : $sms_raw;
		if ( uturn_nt_on( $ch ) && uturn_sms_to( $raw ) !== '' ) {
			$r = $ch === 'wa' ? uturn_send_wa( $raw, $msg, $context ) : uturn_send_sms( $raw, $msg, $context );
			if ( $r[0] ) {
				return array( true, strtoupper( $ch ) );
			}
		}
	}
	uturn_notify_log( '-', $sms_raw !== '' ? $sms_raw : ( 'uid:' . (int) $user_id ), 'SKIP', 'নম্বর/চ্যানেল নেই', $context );
	return array( false, 'SKIP' );
}

function uturn_notify_user_forced( $user_id, $msg, $ch, $context = '' ) {
	list( $sms_raw, $wa_raw ) = uturn_notify_numbers( $user_id );
	$raw = $ch === 'wa' ? $wa_raw : $sms_raw;
	if ( ! uturn_nt_on( $ch ) || uturn_sms_to( $raw ) === '' ) {
		uturn_notify_log( '-', $raw !== '' ? $raw : ( 'uid:' . (int) $user_id ), 'SKIP', strtoupper( $ch ) . ' চ্যানেলে পাঠানো যায়নি', $context );
		return array( false, 'SKIP' );
	}
	$r = $ch === 'wa' ? uturn_send_wa( $raw, $msg, $context ) : uturn_send_sms( $raw, $msg, $context );
	return array( $r[0], strtoupper( $ch ) );
}

/* ================= templates ================= */
function uturn_tpl( $key, $vars ) {
	$o = uturn_opts();
	$t = (string) ( $o[ $key ] ?? '' );
	foreach ( (array) $vars as $k => $v ) {
		$t = str_replace( '{' . $k . '}', (string) $v, $t );
	}
	return $t;
}

/* ================= class teachers ================= */
function uturn_class_teacher( $class ) {
	/* Canonical store: term meta (Class Manager). Falls back to teacher user-meta. */
	$t = get_term_by( 'name', $class, 'ut_class' );
	if ( $t && function_exists( 'uturn_class_cteacher' ) ) {
		$tid2 = uturn_class_cteacher( $t->term_id );
		if ( $tid2 && get_userdata( $tid2 ) ) {
			return $tid2;
		}
	}
	$q = new WP_User_Query( array( 'role' => 'uturn_teacher', 'meta_key' => '_ut_classteacher_of', 'meta_value' => $class, 'number' => 2, 'fields' => 'ID' ) );
	$ids = $q->get_results();
	return $ids ? (int) $ids[0] : 0;
}

function uturn_class_teacher_map() {
	$map = array();
	$terms = function_exists( 'uturn_class_terms' ) ? uturn_class_terms() : get_terms( array( 'taxonomy' => 'ut_class', 'hide_empty' => false ) );
	if ( is_wp_error( $terms ) ) {
		return $map;
	}
	foreach ( (array) $terms as $t ) {
		$tid = uturn_class_teacher( $t->name );
		$map[ $t->name ] = $tid ? get_userdata( $tid )->display_name : '';
	}
	return $map;
}

/* ================= attendance auto-SMS ================= */
add_action( 'save_post_ut_attendance', 'uturn_attendance_cpt_notify', 20, 3 );
function uturn_attendance_cpt_notify( $post_id, $post, $update ) {
	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}
	if ( get_post_meta( $post_id, '_ut_class', true ) === '' ) {
		return; // portal insert fires save_post before meta exists — portal calls us directly
	}
	uturn_notify_attendance( $post_id, get_current_user_id() );
}

function uturn_notify_attendance( $att_id, $actor_id = 0 ) {
	$o = uturn_opts();
	if ( ( $o['sms_attendance'] ?? '0' ) !== '1' ) {
		return array( false, 'auto-off' );
	}
	if ( ! uturn_nt_on( 'sms' ) && ! uturn_nt_on( 'wa' ) ) {
		return array( false, 'channels-off' );
	}
	if ( get_post_meta( $att_id, '_ut_absent_sent', true ) !== '' ) {
		return array( false, 'already-sent' ); // re-save never re-spams
	}
	$class = (string) get_post_meta( $att_id, '_ut_class', true );
	if ( $class === '' ) {
		return array( false, 'no-class' );
	}
	$ct = uturn_class_teacher( $class );
	$actor_ok = ( $ct && (int) $actor_id === $ct ) || user_can( (int) $actor_id, 'uturn_manage_sms' ) || ( ! $ct && user_can( (int) $actor_id, 'edit_ut_attendances' ) );
	if ( ! $actor_ok ) {
		uturn_notify_log( '-', $class, 'SKIP', 'শ্রেণি-শিক্ষক নন — SMS যায়নি', 'attendance' );
		return array( false, 'not-class-teacher' );
	}
	$present = array_filter( array_map( 'trim', explode( ',', (string) get_post_meta( $att_id, '_ut_present', true ) ) ) );
	$roster = function_exists( 'uturn_class_students' ) ? uturn_class_students( $class ) : array();
	$date = (string) get_post_meta( $att_id, '_ut_date', true );
	$school = (string) ( $o['school_name_bn'] ?? '' );
	$sent = 0;
	$skip = 0;
	foreach ( (array) $roster as $sp ) {
		if ( in_array( (string) $sp->ID, $present, true ) ) {
			continue; // only absent guardians get SMS
		}
		$uid = (int) get_post_meta( $sp->ID, '_ut_user_id', true );
		if ( ! $uid ) {
			$skip++;
			continue;
		}
		$msg = uturn_tpl( 'tpl_absent', array( 'name' => $sp->post_title, 'class' => $class, 'roll' => get_post_meta( $sp->ID, '_ut_roll', true ), 'date' => $date, 'school' => $school ) );
		list( $ok, ) = uturn_notify_user( $uid, $msg, 'attendance' );
		if ( $ok ) {
			$sent++;
		} else {
			$skip++;
		}
	}
	update_post_meta( $att_id, '_ut_absent_sent', current_time( 'mysql' ) . "|sent:$sent|skip:$skip" );
	return array( true, "sent:$sent|skip:$skip" );
}

/* ================= result auto-SMS ================= */
function uturn_notify_result( $exam_bn, $year, $class, $roll, $gpa, $status ) {
	$o = uturn_opts();
	if ( ( $o['sms_result'] ?? '0' ) !== '1' ) {
		return array( false, 'auto-off' );
	}
	if ( ! uturn_nt_on( 'sms' ) && ! uturn_nt_on( 'wa' ) ) {
		return array( false, 'channels-off' );
	}
	$q = new WP_User_Query(
		array(
			'role' => 'uturn_student', 'number' => 1,
			'meta_query' => array(
				array( 'key' => '_ut_class', 'value' => $class ),
				array( 'key' => '_ut_roll', 'value' => (string) $roll ),
			),
		)
	);
	$users = $q->get_results();
	if ( ! $users ) {
		uturn_notify_log( '-', "$class/$roll", 'SKIP', 'শিক্ষার্থী অ্যাকাউন্ট পাওয়া যায়নি', 'result' );
		return array( false, 'no-user' );
	}
	$msg = uturn_tpl(
		'tpl_result',
		array(
			'name' => $users[0]->display_name, 'class' => $class, 'roll' => $roll,
			'exam' => $exam_bn, 'year' => $year, 'gpa' => $gpa,
			'result' => $status === 'fail' ? 'অনুত্তীর্ণ' : 'উত্তীর্ণ',
			'school' => (string) ( $o['school_name_bn'] ?? '' ),
		)
	);
	return uturn_notify_user( (int) $users[0]->ID, $msg, 'result' );
}

/* ================= manual screen ================= */
add_action( 'admin_menu', 'uturn_sms_menu', 30 );
function uturn_sms_menu() {
	add_submenu_page( 'eduturn', 'SMS পাঠান', '📲 SMS পাঠান', 'uturn_manage_sms', 'eduturn-sms', 'uturn_sms_page' );
}

function uturn_sms_page() {
	eduturn_license_require( 'erp' );
	$o = uturn_opts();
	$can_cfg = current_user_can( 'uturn_manage_settings' );
	echo '<div class="wrap"><h1>📲 SMS ও হোয়াটসঅ্যাপ</h1>';
	foreach ( array( 'ok' => 'success', 'err' => 'error' ) as $k => $cls ) {
		if ( ! empty( $_GET[ 'uturn_sms_' . $k ] ) ) {
			echo '<div class="notice notice-' . $cls . '"><p>' . esc_html( wp_unslash( $_GET[ 'uturn_sms_' . $k ] ) ) . '</p></div>';
		}
	}
	if ( uturn_nt_test() ) {
		echo '<div class="notice notice-warning" role="status"><p><b>🧪 টেস্ট মোড চালু</b> — বার্তা আসলে পাঠানো হচ্ছে না, শুধু লগে জমা হচ্ছে। Super Admin সেটিংস → নোটিফিকেশন থেকে বন্ধ করবেন।</p></div>';
	}
	$pill = function ( $on, $label ) {
		return '<span style="display:inline-block;background:' . ( $on ? '#dcfce7;color:#166534' : '#fee2e2;color:#991b1b' ) . ';border-radius:99px;padding:3px 12px;font-weight:700;margin:2px">' . ( $on ? '● ' : '○ ' ) . esc_html( $label ) . '</span>';
	};
	echo '<div class="utm-card" style="background:#fff;border:1px solid #d7e4f5;border-radius:10px;padding:14px 16px;margin:12px 0"><h3 style="margin:0 0 8px">📡 চ্যানেল অবস্থা</h3>'
		. $pill( uturn_nt_on( 'sms' ), 'SMS' ) . ' ' . $pill( uturn_nt_on( 'wa' ), 'WhatsApp' ) . ' '
		. $pill( ( $o['sms_attendance'] ?? '0' ) === '1', 'হাজিরা অটো-SMS' ) . ' ' . $pill( ( $o['sms_result'] ?? '0' ) === '1', 'ফলাফল অটো-SMS' )
		. ( $can_cfg ? '<p><a class="button" href="' . esc_url( admin_url( 'admin.php?page=eduturn-settings&tab=sms' ) ) . '">⚙️ API / টেমপ্লেট সেটিংস</a></p>' : '<p class="description">API সেটিংস শুধু Super Admin বদলাতে পারেন (সেটিংস → নোটিফিকেশন)।</p>' )
		. '</div>';

	// toggles (both admins)
	echo '<div class="utm-card" style="background:#fff;border:1px solid #d7e4f5;border-radius:10px;padding:14px 16px;margin:12px 0"><h3 style="margin:0 0 8px">🔀 অটোমেশন চালু / বন্ধ</h3>'
		. '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="uturn_sms_toggles">' . wp_nonce_field( 'uturn_sms_toggles', '_wpnonce', true, false )
		. '<label style="margin-right:18px"><input type="checkbox" name="sms_attendance" value="1"' . checked( $o['sms_attendance'] ?? '0', '1', false ) . '> হাজিরা অটো-SMS (অনুপস্থিতদের অভিভাবকে)</label>'
		. '<label><input type="checkbox" name="sms_result" value="1"' . checked( $o['sms_result'] ?? '0', '1', false ) . '> ফলাফল অটো-SMS (ইমপোর্টের সময়)</label> '
		. '<button class="button button-primary">সংরক্ষণ</button></form></div>';

	// manual compose
	$terms = function_exists( 'uturn_class_terms' ) ? uturn_class_terms() : get_terms( array( 'taxonomy' => 'ut_class', 'hide_empty' => false ) );
	echo '<div class="utm-card" style="background:#fff;border:1px solid #d7e4f5;border-radius:10px;padding:14px 16px;margin:12px 0"><h3 style="margin:0 0 8px">✍️ ম্যানুয়াল বার্তা</h3>'
		. '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="uturn_sms_send">' . wp_nonce_field( 'uturn_sms_send', '_wpnonce', true, false )
		. '<table class="form-table"><tr><th scope="row">প্রাপক</th><td><select name="aud" style="min-width:260px">'
		. '<option value="students">সব শিক্ষার্থী (অভিভাবক নম্বর)</option><option value="teachers">সব শিক্ষক</option>';
	foreach ( (array) $terms as $t ) {
		if ( ! is_wp_error( $terms ) ) {
			echo '<option value="class:' . esc_attr( $t->name ) . '">শ্রেণি: ' . esc_html( $t->name ) . '</option>';
		}
	}
	echo '<option value="custom">কাস্টম নম্বর (নিচে লিখুন)</option></select></td></tr>'
		. '<tr><th scope="row">কাস্টম নম্বর</th><td><textarea name="custom" rows="2" cols="50" placeholder="প্রতি লাইনে একটি: 017XXXXXXXX"></textarea></td></tr>'
		. '<tr><th scope="row">চ্যানেল</th><td><select name="ch"><option value="auto">স্বয়ংক্রিয় (পছন্দ: ' . esc_html( uturn_nt_pref() === 'wa' ? 'WhatsApp → SMS' : 'SMS → WhatsApp' ) . ')</option><option value="sms">শুধু SMS</option><option value="wa">শুধু WhatsApp</option></select></td></tr>'
		. '<tr><th scope="row">বার্তা</th><td><textarea name="msg" rows="4" cols="60" maxlength="1000" required placeholder="সর্বোচ্চ ১০০০ অক্ষর"></textarea><p class="description">একবারে সর্বোচ্চ ৫০০ জনে যাবে। {name} {class} {roll} লিখলে প্রত্যেকের নিজের তথ্য বসবে।</p></td></tr></table>'
		. '<button class="button button-primary button-large">📤 পাঠান</button></form></div>';

	// class-teacher map
	echo '<div class="utm-card" style="background:#fff;border:1px solid #d7e4f5;border-radius:10px;padding:14px 16px;margin:12px 0"><h3 style="margin:0 0 8px">🏫 শ্রেণি-শিক্ষক তালিকা</h3>';
	$map = uturn_class_teacher_map();
	if ( $map ) {
		echo '<table class="widefat striped" style="max-width:480px"><thead><tr><th scope="col">শ্রেণি</th><th scope="col">শ্রেণি-শিক্ষক</th></tr></thead><tbody>';
		foreach ( $map as $cls => $tname ) {
			echo '<tr><td>' . esc_html( $cls ) . '</td><td>' . ( $tname !== '' ? esc_html( $tname ) : '<span style="color:#b32d2e">— সেট নয় —</span>' ) . '</td></tr>';
		}
		echo '</tbody></table><p class="description">শিক্ষকমণ্ডলী → সম্পাদনা → “শ্রেণি-শিক্ষকের দায়িত্ব” ঘর থেকে সেট করুন।</p>';
	} else {
		echo '<p class="description">এখনো কোনো শ্রেণি নেই।</p>';
	}
	echo '</div>';

	// log
	echo '<div class="utm-card" style="background:#fff;border:1px solid #d7e4f5;border-radius:10px;padding:14px 16px;margin:12px 0"><h3 style="margin:0 0 8px">🧾 পাঠানো লগ (সর্বশেষ ৪০)</h3>';
	$log = uturn_notify_get_log( 40 );
	if ( ! $log ) {
		echo '<p class="description">এখনো কিছু পাঠানো হয়নি।</p>';
	} else {
		echo '<div class="table-wrap"><table class="widefat striped" aria-label="পাঠানো বার্তার লগ"><thead><tr><th scope="col">সময়</th><th scope="col">চ্যানেল</th><th scope="col">প্রাপক</th><th scope="col">অবস্থা</th><th scope="col">বিস্তারিত</th><th scope="col">ধরন</th></tr></thead><tbody>';
		foreach ( $log as $r ) {
			$cls = $r['st'] === 'OK' ? 'color:#0a7b3c;font-weight:700' : ( $r['st'] === 'TEST' ? 'color:#8a5a00;font-weight:700' : 'color:#b32d2e' );
			echo '<tr><td>' . esc_html( $r['t'] ) . '</td><td><b>' . esc_html( $r['ch'] ) . '</b></td><td>' . esc_html( $r['to'] ) . '</td><td style="' . $cls . '">' . esc_html( $r['st'] ) . '</td><td>' . esc_html( $r['info'] ) . '</td><td>' . esc_html( $r['ctx'] ) . '</td></tr>';
		}
		echo '</tbody></table></div>';
	}
	if ( $can_cfg ) {
		echo '<p><a class="button" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=uturn_sms_clearlog' ), 'uturn_sms_clearlog' ) ) . '" onclick="return confirm(\'লগ মুছে যাবে। চালিয়ে যাবেন?\')">লগ মুছুন</a></p>';
	}
	echo '</div></div>';
}

/* ================= handlers ================= */
add_action( 'admin_post_uturn_sms_toggles', 'uturn_sms_toggles' );
function uturn_sms_toggles() {
	eduturn_license_require( 'erp' );
	if ( ! current_user_can( 'uturn_manage_sms' ) || ! check_admin_referer( 'uturn_sms_toggles' ) ) {
		wp_die( 'Unauthorized.' );
	}
	remove_filter( 'sanitize_option_' . UTURN_OPT, 'uturn_sanitize_options' ); // surgical key update, no re-sanitize
	$cur = get_option( UTURN_OPT, array() );
	if ( ! is_array( $cur ) ) {
		$cur = array();
	}
	$cur['sms_attendance'] = empty( $_POST['sms_attendance'] ) ? '0' : '1';
	$cur['sms_result'] = empty( $_POST['sms_result'] ) ? '0' : '1';
	update_option( UTURN_OPT, $cur );
	wp_safe_redirect( uturn_back( admin_url( 'admin.php?page=eduturn-sms&uturn_sms_ok=' . rawurlencode( 'অটোমেশন সেটিংস সংরক্ষিত।' ) ) ) );
	exit;
}

add_action( 'admin_post_uturn_sms_send', 'uturn_sms_send' );
function uturn_sms_send() {
	eduturn_license_require( 'erp' );
	$back = uturn_back( admin_url( 'admin.php?page=eduturn-sms' ) );
	if ( ! current_user_can( 'uturn_manage_sms' ) || ! check_admin_referer( 'uturn_sms_send' ) ) {
		wp_die( 'Unauthorized.' );
	}
	if ( ! uturn_nt_on( 'sms' ) && ! uturn_nt_on( 'wa' ) ) {
		wp_safe_redirect( $back . '&uturn_sms_err=' . rawurlencode( 'SMS ও WhatsApp দুটোই বন্ধ — Super Admin সেটিংস থেকে চালু করবেন।' ) );
		exit;
	}
	$msg = isset( $_POST['msg'] ) ? mb_substr( sanitize_textarea_field( wp_unslash( $_POST['msg'] ) ), 0, 1000 ) : '';
	if ( $msg === '' ) {
		wp_safe_redirect( $back . '&uturn_sms_err=' . rawurlencode( 'বার্তা খালি!' ) );
		exit;
	}
	$ch = isset( $_POST['ch'] ) && in_array( $_POST['ch'], array( 'sms', 'wa' ), true ) ? $_POST['ch'] : 'auto';
	$aud = isset( $_POST['aud'] ) ? sanitize_text_field( wp_unslash( $_POST['aud'] ) ) : 'students';
	$uids = array();
	if ( $aud === 'students' || $aud === 'teachers' ) {
		$q = new WP_User_Query( array( 'role' => $aud === 'students' ? 'uturn_student' : 'uturn_teacher', 'number' => 500, 'fields' => 'ID' ) );
		$uids = array_map( 'intval', (array) $q->get_results() );
	} elseif ( strpos( $aud, 'class:' ) === 0 ) {
		$cls = substr( $aud, 6 );
		if ( $cls !== '' ) {
			$q = new WP_User_Query( array( 'role' => 'uturn_student', 'number' => 500, 'fields' => 'ID', 'meta_key' => '_ut_class', 'meta_value' => $cls ) );
			$uids = array_map( 'intval', (array) $q->get_results() );
		}
	}
	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 300 );
	}
	$sent = 0;
	$skip = 0;
	$n = 0;
	foreach ( $uids as $uid ) {
		if ( $n++ >= 500 ) {
			break;
		}
		$u = get_userdata( $uid );
		$pm = str_replace( array( '{name}', '{class}', '{roll}' ), array( $u ? $u->display_name : '', get_user_meta( $uid, '_ut_class', true ), get_user_meta( $uid, '_ut_roll', true ) ), $msg );
		$r = $ch === 'auto' ? uturn_notify_user( $uid, $pm, 'manual' ) : uturn_notify_user_forced( $uid, $pm, $ch, 'manual' );
		if ( $r[0] ) {
			$sent++;
		} else {
			$skip++;
		}
	}
	if ( $aud === 'custom' ) {
		$lines = preg_split( '/[\r\n,;]+/', (string) ( isset( $_POST['custom'] ) ? wp_unslash( $_POST['custom'] ) : '' ) );
		foreach ( (array) $lines as $line ) {
			$to = uturn_sms_to( $line );
			if ( $to === '' ) {
				continue;
			}
			if ( $n++ >= 500 ) {
				break;
			}
			if ( $ch === 'wa' ) {
				$r = uturn_send_wa( $to, $msg, 'manual' );
			} elseif ( $ch === 'sms' ) {
				$r = uturn_send_sms( $to, $msg, 'manual' );
			} else {
				$r = uturn_nt_pref() === 'wa' && uturn_nt_on( 'wa' ) ? uturn_send_wa( $to, $msg, 'manual' ) : uturn_send_sms( $to, $msg, 'manual' );
				if ( ! $r[0] ) {
					$r = uturn_nt_pref() === 'wa' ? uturn_send_sms( $to, $msg, 'manual' ) : uturn_send_wa( $to, $msg, 'manual' );
				}
			}
			if ( $r[0] ) {
				$sent++;
			} else {
				$skip++;
			}
		}
	}
	$note = "পাঠানো: $sent জন" . ( $skip ? " · বাদ: $skip" : '' ) . ( uturn_nt_test() ? ' (টেস্ট মোড — আসলে যায়নি)' : '' );
	wp_safe_redirect( $back . '&uturn_sms_ok=' . rawurlencode( $note ) );
	exit;
}

add_action( 'admin_post_uturn_sms_clearlog', 'uturn_sms_clearlog' );
function uturn_sms_clearlog() {
	if ( ! current_user_can( 'uturn_manage_settings' ) || ! check_admin_referer( 'uturn_sms_clearlog' ) ) {
		wp_die( 'Unauthorized.' );
	}
	delete_option( 'uturn_notify_log' );
	wp_safe_redirect( uturn_back( admin_url( 'admin.php?page=eduturn-sms&uturn_sms_ok=' . rawurlencode( 'লগ মুছে ফেলা হয়েছে।' ) ) ) );
	exit;
}
