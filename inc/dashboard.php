<?php
/**
 * EduTurn — Command-center dashboard (role-aware) + admin menu organization.
 * Parent menu is visible to every logged-in user; every item enforces its own cap.
 */

defined( 'ABSPATH' ) || exit;

/* ============ stats (10-min cache) ============ */
function uturn_dash_stats() {
	$cached = get_transient( 'uturn_dash_stats' );
	if ( is_array( $cached ) ) {
		return $cached;
	}
	$users = count_users();
	$roles = isset( $users['avail_roles'] ) ? $users['avail_roles'] : array();
	$npub  = wp_count_posts( 'ut_notice' );
	$pend  = get_posts( array( 'post_type' => 'ut_application', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_ut_status', 'meta_value' => 'pending' ) );
	$due   = get_posts( array( 'post_type' => 'ut_fee', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_ut_status', 'meta_value' => 'due' ) );
	$total = 0;
	foreach ( (array) $due as $fid ) {
		$total += (float) get_post_meta( $fid, '_ut_amount', true );
	}
	$today = get_posts( array( 'post_type' => 'ut_attendance', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_ut_date', 'meta_value' => current_time( 'Y-m-d' ) ) );
	$ym = current_time( 'Y-m' );
	$paid = get_posts( array( 'post_type' => 'ut_fee', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_ut_status', 'meta_value' => 'paid' ) );
	$ctotal = 0;
	$ccount = 0;
	foreach ( (array) $paid as $fid ) {
		if ( substr( (string) get_post_meta( $fid, '_ut_date', true ), 0, 7 ) === $ym ) {
			$ccount++;
			$ctotal += (float) get_post_meta( $fid, '_ut_amount', true );
		}
	}
	$st = array(
		'students' => isset( $roles['uturn_student'] ) ? (int) $roles['uturn_student'] : 0,
		'teachers' => isset( $roles['uturn_teacher'] ) ? (int) $roles['uturn_teacher'] : 0,
		'notices'  => $npub && isset( $npub->publish ) ? (int) $npub->publish : 0,
		'pending'  => count( (array) $pend ),
		'due'      => count( (array) $due ),
		'due_total'=> $total,
		'att_today'=> count( (array) $today ),
		'collected'=> $ccount,
		'collected_total'=> $ctotal,
	);
	set_transient( 'uturn_dash_stats', $st, 10 * MINUTE_IN_SECONDS );
	return $st;
}

function uturn_role_label() {
	$u = wp_get_current_user();
	if ( current_user_can( 'manage_options' ) ) {
		return 'সুপার অ্যাডমিন';
	}
	$r = (array) $u->roles;
	if ( in_array( 'uturn_headmaster', $r, true ) ) {
		return 'প্রধান শিক্ষক';
	}
	if ( in_array( 'uturn_school_admin', $r, true ) ) {
		return 'স্কুল অ্যাডমিন';
	}
	if ( in_array( 'uturn_accountant', $r, true ) ) {
		return 'হিসাবরক্ষক';
	}
	if ( in_array( 'uturn_staff', $r, true ) ) {
		return 'স্টাফ';
	}
	if ( in_array( 'uturn_teacher', $r, true ) ) {
		return 'শিক্ষক';
	}
	if ( in_array( 'uturn_student', $r, true ) ) {
		return 'শিক্ষার্থী';
	}
	return 'ব্যবহারকারী';
}

/* ============ module registry ============ */
function uturn_dash_groups() {
	$au = function ( $p ) { return admin_url( $p ); };
	$sh = function ( $v ) { return function_exists( 'uturn_dash_view_url' ) ? uturn_dash_view_url( $v ) : admin_url( 'admin.php?page=eduturn' ); };
	return array(
		array(
			'title' => '🎓 শিক্ষা ব্যবস্থাপনা', 'items' => array(
				array( 'শিক্ষার্থীবৃন্দ', 'ছাত্র যোগ / সম্পাদনা / পাসওয়ার্ড রিসেট', $au( 'admin.php?page=eduturn-students' ), 'uturn_manage_academic', 'graduation-cap', 'students' ),
				array( 'শিক্ষকমণ্ডলী', 'শিক্ষক যোগ / সম্পাদনা / পাসওয়ার্ড রিসেট', $au( 'admin.php?page=eduturn-teachers' ), 'uturn_manage_academic', 'businessman', 'teachers' ),
				array( 'কর্মচারীবৃন্দ', 'অফিস কর্মচারী ব্যবস্থাপনা', $au( 'admin.php?page=eduturn-staffs' ), 'uturn_manage_academic', 'id', '' ),
				array( 'হাজিরা রেকর্ড', 'শ্রেণিভিত্তিক হাজিরা ইতিহাস', $au( 'edit.php?post_type=ut_attendance' ), 'edit_ut_attendances', 'schedule', '' ),
				array( 'রুটিন বিল্ডার', 'ক্লাস + পরীক্ষার রুটিন তৈরি', $au( 'admin.php?page=eduturn-routines' ), 'uturn_manage_routines', 'clock', '' ),
				array( 'ফলাফল ইমপোর্ট', 'CSV দিয়ে একসাথে ফলাফল তোলা', $au( 'admin.php?page=eduturn-result-import' ), 'edit_ut_results', 'upload', '' ),
				array( 'ফলাফল ডাটাবেজ', 'প্রকাশিত ফলাফল দেখুন / সম্পাদনা', $au( 'edit.php?post_type=ut_result' ), 'edit_ut_results', 'chart-bar', '' ),
				array( 'ফি রেকর্ড', 'মাসিক ফি: পরিশোধিত / বকেয়া', $au( 'edit.php?post_type=ut_fee' ), 'edit_ut_fees', 'money-alt', 'due' ),
				array( 'বিষয়সমূহ', 'বিষয় যোগ / বিভাগ-সংযোগ / সক্রিয়', $sh( 'subjects' ), 'uturn_manage_academic', 'book', '' ),
				array( 'বিভাগসমূহ', 'বিজ্ঞান / মানবিক / ব্যবসায় শিক্ষা', $sh( 'groups' ), 'uturn_manage_academic', 'category', '' ),
			),
		),
		array(
			'title' => '📢 ওয়েবসাইট কনটেন্ট', 'items' => array(
				array( 'নোটিশ', 'ঘোষণা ও বিজ্ঞপ্তি প্রকাশ', $au( 'edit.php?post_type=ut_notice' ), 'edit_ut_notices', 'megaphone', 'notices' ),
				array( 'সংবাদ', 'ক্যাম্পাস সংবাদ', $au( 'edit.php?post_type=ut_news' ), 'edit_ut_news_items', 'media-document', '' ),
				array( 'ইভেন্ট', 'অনুষ্ঠান ও ছুটির তালিকা', $au( 'edit.php?post_type=ut_event' ), 'edit_ut_events', 'calendar-alt', '' ),
				array( 'গ্যালারি', 'ছবির অ্যালবাম', $au( 'edit.php?post_type=ut_album' ), 'edit_ut_albums', 'format-gallery', '' ),
				array( 'ডাউনলোড', 'ফরম / সিলেবাস / রুটিন ফাইল', $au( 'edit.php?post_type=ut_download' ), 'edit_ut_downloads', 'download', '' ),
				array( 'জিজ্ঞাসা', 'সাধারণ প্রশ্নোত্তর', $au( 'edit.php?post_type=ut_faq' ), 'edit_ut_faqs', 'editor-help', '' ),
				array( 'মতামত', 'অভিভাবক / শিক্ষার্থীর মতামত', $au( 'edit.php?post_type=ut_testimonial' ), 'edit_ut_testimonials', 'format-quote', '' ),
				array( 'অর্জন', 'প্রতিষ্ঠানের অর্জনসমূহ', $au( 'edit.php?post_type=ut_achievement' ), 'edit_ut_achievements', 'awards', '' ),
				array( 'পরিচালনা পর্ষদ', 'সদস্য / ক্রম / লাইভ প্রিভিউ', $sh( 'board' ), 'edit_ut_boards', 'bank', '' ),
			),
		),
		array(
			'title' => '📝 ভর্তি ও যোগাযোগ', 'items' => array(
				array( 'ভর্তি আবেদন', 'অনলাইন আবেদন যাচাই + CSV', $au( 'edit.php?post_type=ut_application' ), 'edit_ut_applications', 'clipboard', 'pending' ),
				array( 'ইনবক্স', 'যোগাযোগ পেজের বার্তা', $au( 'edit.php?post_type=ut_message' ), 'edit_ut_messages', 'email-alt', '' ),
				array( 'SMS পাঠান', 'ম্যানুয়াল SMS/হোয়াটসঅ্যাপ + লগ', $au( 'admin.php?page=eduturn-sms' ), 'uturn_manage_sms', 'smartphone', '' ),
			),
		),
		array(
			'title' => '⚙️ সিস্টেম', 'items' => array(
				array( 'সেটিংস', 'নাম / লোগো / রং / হোমপেজ', $au( 'admin.php?page=eduturn-settings' ), 'uturn_manage_settings', 'admin-generic', '' ),
				array( 'হোমপেজ সাজান', 'হিরো / পরিসংখ্যান / সেকশন ক্রম', $au( 'admin.php?page=eduturn-settings&tab=home' ), 'uturn_manage_settings', 'layout', '' ),
				array( 'ভিজ্যুয়াল এডিটর', 'লাইভ প্রিভিউসহ হোমপেজ সম্পাদনা', $sh( 'editor' ), 'uturn_manage_settings', 'welcome-view-site', '' ),
				array( 'ডেমো কনটেন্ট', 'ডেমো ডেটা ইনস্টল / রি-সিঙ্ক', $au( 'admin.php?page=eduturn-seeder' ), 'manage_options', 'database', '' ),
				array( 'লাইসেন্স', 'সাবস্ক্রিপশন অবস্থা ও নবায়ন', $au( 'admin.php?page=eduturn-license' ), 'manage_options', 'lock', '' ),
				array( 'ব্যবহারকারী', 'শিক্ষক / হিসাবরক্ষক / স্টাফ অ্যাকাউন্ট', $au( 'users.php' ), 'list_users', 'admin-users', '' ),
			),
		),
	);
}

/* ============ the page ============ */
function uturn_dashboard_page() {
	if ( function_exists( 'uturn_is_shell_user' ) && uturn_is_shell_user() && ! current_user_can( 'manage_options' ) ) {
		wp_safe_redirect( uturn_dash_url() );
		exit;
	}
	$st   = uturn_dash_stats();
	$bn   = function_exists( 'uturn_bn' ) ? 'uturn_bn' : 'strval';
	$school = function_exists( 'uturn_opt' ) ? uturn_opt( 'school_name_bn', 'আলোকিত বিদ্যানিকেতন' ) : get_bloginfo( 'name' );
	$is_super = current_user_can( 'manage_options' );
	$is_head = in_array( 'uturn_headmaster', (array) wp_get_current_user()->roles, true );
	echo '<style>'
		. '.udash-head{background:linear-gradient(135deg,#0B2440,#0B4EA8 60%,#1B7FC2);color:#fff;border-radius:14px;padding:26px 28px;margin:12px 20px 0 0;display:flex;gap:18px;align-items:center;flex-wrap:wrap}'
		. '.udash-head h1{color:#fff;margin:0;font-size:26px}.udash-head p{margin:4px 0 0;opacity:.85}'
		. '.udash-pill{background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.35);border-radius:999px;padding:5px 14px;font-size:13px;margin-left:auto;white-space:nowrap}'
		. '.udash-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin:16px 20px 0 0}'
		. '.udash-stat{background:#fff;border:1px solid #d7e4f5;border-top:4px solid #0B4EA8;border-radius:12px;padding:14px 16px;text-decoration:none;display:block;color:#14324A}'
		. 'a.udash-stat:hover{box-shadow:0 8px 22px rgba(11,78,168,.15);transform:translateY(-1px)}'
		. '.udash-stat b{font-size:26px;display:block;color:#0B4EA8}.udash-stat span{font-size:13px;color:#4a6572}'
		. '.udash-grid{display:grid;grid-template-columns:1fr 320px;gap:16px;margin:16px 20px 0 0;align-items:start}'
		. '@media(max-width:1100px){.udash-grid{grid-template-columns:1fr}}'
		. '.udash-group{background:#fff;border:1px solid #d7e4f5;border-radius:12px;padding:18px;margin-bottom:16px}'
		. '.udash-group h2{margin:0 0 12px;font-size:17px;color:#0B2440}'
		. '.udash-mods{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px}'
		. '.udash-mod{display:flex;gap:10px;align-items:flex-start;border:1px solid #e3edf7;border-radius:10px;padding:11px 12px;text-decoration:none;color:#14324A;background:#f8fbff}'
		. '.udash-mod:hover{border-color:#0B4EA8;box-shadow:0 6px 16px rgba(11,78,168,.12)}'
		. '.udash-mod .dashicons{color:#0B4EA8;font-size:26px;width:26px;height:26px;margin-top:2px}'
		. '.udash-mod b{font-size:14px;display:block}.udash-mod small{color:#5b7a8c;font-size:12px}'
		. '.udash-badge{background:#b32d2e;color:#fff;border-radius:999px;font-size:11px;padding:1px 8px;margin-left:6px;vertical-align:2px}'
		. '.udash-badge.blue{background:#0B4EA8}'
		. '.udash-side{background:#fff;border:1px solid #d7e4f5;border-radius:12px;padding:18px;margin-bottom:16px}'
		. '.udash-side h3{margin:0 0 10px;font-size:15px;color:#0B2440}'
		. '.udash-side ul{margin:0;padding:0;list-style:none}.udash-side li{padding:7px 0;border-bottom:1px dashed #e3edf7;font-size:13px}.udash-side li:last-child{border:0}'
		. '.udash-ok{color:#0a7b3c;font-weight:700}.udash-warn{color:#b32d2e;font-weight:700}'
		. '</style>';

	echo '<div class="udash-head"><div><h1>🏫 ' . esc_html( $school ) . '</h1><p>EduTurn কমান্ড সেন্টার · ' . esc_html( wp_date( 'l, j F Y' ) ) . '</p></div>'
		. '<span class="udash-pill">👤 ' . esc_html( uturn_role_label() ) . ' · v' . esc_html( defined( 'UTURN_VERSION' ) ? UTURN_VERSION : '' ) . '</span></div>';

	// stat cards (cap-filtered)
	$cards = array(
		array( 'শিক্ষার্থী', $st['students'], 'admin.php?page=eduturn-students', 'uturn_manage_academic' ),
		array( 'শিক্ষক', $st['teachers'], 'admin.php?page=eduturn-teachers', 'uturn_manage_academic' ),
		array( 'নোটিশ', $st['notices'], 'edit.php?post_type=ut_notice', 'edit_ut_notices' ),
		array( 'অপেক্ষমান আবেদন', $st['pending'], 'edit.php?post_type=ut_application', 'edit_ut_applications' ),
		array( 'বকেয়া ফি', $st['due'], 'edit.php?post_type=ut_fee', 'edit_ut_fees' ),
		array( 'এ মাসে আদায় (৳)', $st['collected_total'], 'edit.php?post_type=ut_fee', 'edit_ut_fees' ),
		array( 'আজ হাজিরা (শ্রেণি)', $st['att_today'], 'edit.php?post_type=ut_attendance', 'edit_ut_attendances' ),
	);
	echo '<div class="udash-stats">';
	foreach ( $cards as $c ) {
		if ( ! current_user_can( $c[3] ) ) {
			continue;
		}
		echo '<a class="udash-stat" href="' . esc_url( admin_url( $c[2] ) ) . '"><b>' . esc_html( call_user_func( $bn, $c[1] ) ) . '</b><span>' . esc_html( $c[0] ) . '</span></a>';
	}
	echo '</div>';

	echo '<div class="udash-grid"><div>';
	$shown = 0;
	foreach ( uturn_dash_groups() as $g ) {
		$items = array_filter( $g['items'], function ( $it ) { return current_user_can( $it[3] ); } );
		if ( ! $items ) {
			continue;
		}
		$shown += count( $items );
		echo '<div class="udash-group"><h2>' . esc_html( $g['title'] ) . '</h2><div class="udash-mods">';
		foreach ( $items as $it ) {
			$badge = '';
			if ( $it[5] !== '' && ! empty( $st[ $it[5] ] ) ) {
				$badge = ' <span class="udash-badge' . ( $it[5] === 'pending' || $it[5] === 'due' ? '' : ' blue' ) . '">' . esc_html( call_user_func( $bn, $st[ $it[5] ] ) ) . '</span>';
			}
			echo '<a class="udash-mod" href="' . esc_url( $it[2] ) . '"><span class="dashicons dashicons-' . esc_attr( $it[4] ) . '"></span><span><b>' . esc_html( $it[0] ) . $badge . '</b><small>' . esc_html( $it[1] ) . '</small></span></a>';
		}
		echo '</div></div>';
	}
	if ( ! $shown ) {
		$sp = function_exists( 'uturn_url' ) ? uturn_url( 'student-portal' ) : home_url( '/student-portal/' );
		$tp = function_exists( 'uturn_url' ) ? uturn_url( 'teacher-portal' ) : home_url( '/teacher-portal/' );
		echo '<div class="udash-group"><h2>👋 স্বাগতম</h2><p>অ্যাডমিন কাজের অনুমতি নেই — অনুগ্রহ করে আপনার পোর্টাল ব্যবহার করুন:</p><p><a class="button button-primary" href="' . esc_url( $sp ) . '">🎓 শিক্ষার্থী পোর্টাল</a> <a class="button button-primary" href="' . esc_url( $tp ) . '">👩‍🏫 শিক্ষক পোর্টাল</a></p></div>';
	}
	echo '</div><div>';

	// action-needed
	if ( current_user_can( 'edit_ut_applications' ) ) {
		$apps = get_posts( array( 'post_type' => 'ut_application', 'posts_per_page' => 5, 'meta_key' => '_ut_status', 'meta_value' => 'pending', 'orderby' => 'date', 'order' => 'DESC' ) );
		echo '<div class="udash-side"><h3>⏳ করণীয় — ভর্তি আবেদন</h3>';
		if ( $apps ) {
			echo '<ul>';
			foreach ( $apps as $a ) {
				echo '<li><a href="' . esc_url( get_edit_post_link( $a->ID ) ) . '">' . esc_html( $a->post_title ) . '</a><br><small style="color:#5b7a8c">' . esc_html( get_post_meta( $a->ID, '_ut_class', true ) ) . ' · ' . esc_html( mysql2date( 'j F', $a->post_date ) ) . '</small></li>';
			}
			echo '</ul><p><a class="button button-small" href="' . esc_url( admin_url( 'edit.php?post_type=ut_application' ) ) . '">সব আবেদন দেখুন</a></p>';
		} else {
			echo '<p class="udash-ok">✅ কোনো অপেক্ষমান আবেদন নেই।</p>';
		}
		echo '</div>';
	}

	// license (super + headmaster can view; only super opens the page)
	if ( ( $is_super || $is_head ) && function_exists( 'eduturn_license_effective' ) ) {
		$lst = eduturn_license_state();
		$eff = eduturn_license_effective( $lst, time() );
		$lbl = array( 'active' => 'সক্রিয়', 'expired' => 'মেয়াদোত্তীর্ণ', 'suspended' => 'স্থগিত', 'revoked' => 'বাতিল', 'trial_expired' => 'ট্রায়াল শেষ', 'link_dead' => 'সংযোগ বিচ্ছিন্ন', 'inactive' => 'সক্রিয় নয়' );
		$txt = isset( $lbl[ $eff['code'] ] ) ? $lbl[ $eff['code'] ] : $eff['code'];
		$cls = $eff['code'] === 'active' ? 'udash-ok' : 'udash-warn';
		echo '<div class="udash-side"><h3>🔑 লাইসেন্স</h3><p>অবস্থা: <span class="' . $cls . '">' . esc_html( $txt ) . '</span>';
		if ( $eff['code'] === 'active' && $eff['remaining'] !== null ) {
			echo '<br>বাকি: <b>' . esc_html( call_user_func( $bn, $eff['remaining'] ) ) . ' দিন</b>';
		}
		if ( $is_super ) {
			echo '</p><p><a class="button button-small" href="' . esc_url( admin_url( 'admin.php?page=eduturn-license' ) ) . '">লাইসেন্স পেজ</a></p></div>';
		} else {
			echo '</p><p><small>নবায়ন / অ্যাক্টিভেশন: সুপার অ্যাডমিন করবেন।</small></p></div>';
		}
	}

	// system status (super only)
	if ( $is_super ) {
		$pages_ok = 0;
		if ( function_exists( 'uturn_essential_pages' ) ) {
			foreach ( uturn_essential_pages() as $slug => $def ) {
				if ( get_page_by_path( $slug, OBJECT, 'page' ) ) {
					$pages_ok++;
				}
			}
		}
		$pretty = get_option( 'permalink_structure' ) ? 'Pretty' : 'Plain';
		echo '<div class="udash-side"><h3>🖥️ সিস্টেম অবস্থা</h3><ul>'
			. '<li>EduTurn v' . esc_html( defined( 'UTURN_VERSION' ) ? UTURN_VERSION : '—' ) . ' · WP ' . esc_html( get_bloginfo( 'version' ) ) . ' · PHP ' . esc_html( PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION ) . '</li>'
			. '<li>পেজ: ' . (int) $pages_ok . '/13 প্রস্তুত · লিংক: ' . esc_html( $pretty ) . '</li>'
			. '<li><a href="' . esc_url( home_url( '/' ) ) . '" target="_blank">🌐 সাইট দেখুন</a> · <a href="' . esc_url( admin_url( 'admin.php?page=eduturn-seeder' ) ) . '">ডেমো রি-সিঙ্ক</a></li>'
			. '</ul></div>';
	}
	echo '</div></div>';
}

/* ============ submenu order ============ */
add_action( 'admin_menu', 'uturn_reorder_eduturn_menu', 999 );
function uturn_reorder_eduturn_menu() {
	global $submenu;
	if ( empty( $submenu['eduturn'] ) ) {
		return;
	}
	$order = array(
		'eduturn' => 1, 'eduturn-settings' => 2, 'eduturn-homepage' => 3,
		'eduturn-students' => 10, 'eduturn-teachers' => 11, 'eduturn-staffs' => 12,
		'edit.php?post_type=ut_attendance' => 13, 'eduturn-routines' => 14,
		'eduturn-result-import' => 15, 'edit.php?post_type=ut_result' => 16, 'edit.php?post_type=ut_fee' => 17,
		'edit.php?post_type=ut_application' => 20, 'edit.php?post_type=ut_message' => 21, 'eduturn-sms' => 22,
		'edit.php?post_type=ut_notice' => 30, 'edit.php?post_type=ut_news' => 31,
		'edit.php?post_type=ut_event' => 32, 'edit.php?post_type=ut_album' => 33,
		'edit.php?post_type=ut_download' => 34, 'edit.php?post_type=ut_faq' => 35,
		'edit.php?post_type=ut_testimonial' => 36, 'edit.php?post_type=ut_achievement' => 37,
		'eduturn-seeder' => 90,
	);
	$i = 0;
	foreach ( $submenu['eduturn'] as &$it ) {
		$slug = isset( $it[2] ) ? $it[2] : '';
		$it['_ord'] = isset( $order[ $slug ] ) ? $order[ $slug ] : 900;
		$it['_idx'] = $i++;
	}
	unset( $it );
	usort( $submenu['eduturn'], function ( $a, $b ) {
		return ( $a['_ord'] <=> $b['_ord'] ) ?: ( $a['_idx'] <=> $b['_idx'] );
	} );
	foreach ( $submenu['eduturn'] as &$it ) {
		unset( $it['_ord'], $it['_idx'] );
	}
	unset( $it );
}

/* ============ school staff land on the EduTurn dashboard, not WP index ============ */
add_action( 'admin_init', 'uturn_schooladmin_start' );
function uturn_schooladmin_start() {
	if ( wp_doing_ajax() || wp_doing_cron() || ! is_admin() ) {
		return;
	}
	$pagenow = isset( $GLOBALS['pagenow'] ) ? $GLOBALS['pagenow'] : '';
	if ( 'index.php' !== $pagenow || current_user_can( 'manage_options' ) ) {
		return;
	}
	$u = wp_get_current_user();
	$r = (array) $u->roles;
	if ( array_intersect( array( 'uturn_headmaster', 'uturn_school_admin', 'uturn_accountant', 'uturn_staff' ), $r ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=eduturn' ) );
		exit;
	}
}

/* ============ declutter WP dashboard for non-super users ============ */
add_action( 'wp_dashboard_setup', 'uturn_trim_wp_dashboard' );
function uturn_trim_wp_dashboard() {
	if ( current_user_can( 'manage_options' ) ) {
		return;
	}
	foreach ( array( 'dashboard_activity', 'dashboard_quick_press', 'dashboard_primary', 'dashboard_right_now', 'dashboard_site_health', 'dashboard_php_nag', 'welcome-panel' ) as $box ) {
		remove_meta_box( $box, 'dashboard', 'normal' );
		remove_meta_box( $box, 'dashboard', 'side' );
	}
	remove_action( 'welcome_panel', 'wp_welcome_panel' );
}

/* No duplicate dashboards: custom roles never see WP's Dashboard menu. */
add_action( 'admin_menu', 'uturn_hide_wp_dashboard_menu', 999 );
function uturn_hide_wp_dashboard_menu() {
	if ( ! current_user_can( 'manage_options' ) ) {
		remove_menu_page( 'index.php' );
	}
}
