<?php
/**
 * EduTurn — Dashboard views for students, teachers (and shared reads).
 * Adapted from the portal templates into shell markup.
 */

defined( 'ABSPATH' ) || exit;

/** ERP gate for pupil/teacher content (mirrors the old portals). */
function uturn_dash_erp_or_lock() {
	if ( function_exists( 'eduturn_license_can' ) && eduturn_license_can( 'erp' ) ) {
		return true;
	}
	$e = eduturn_license_effective( eduturn_license_state(), time() );
	echo eduturn_license_lock_html( $e['code'] );
	return false;
}

/* ================= student ================= */
function uturn_dash_student_home() {
	$u = wp_get_current_user();
	echo '<div class="utd-card"><h2>👋 স্বাগতম, ' . esc_html( $u->display_name ) . '!</h2><p class="utd-muted">আজকের পড়াশোনার খবর এক নজরে।</p></div>';
	if ( ! uturn_dash_erp_or_lock() ) {
		return;
	}
	$st = uturn_my_student();
	$att = uturn_my_attendance( $st );
	$myres = uturn_my_results( $st );
	$myfees = uturn_my_fees( $st );
	$due = 0;
	foreach ( $myfees as $fp ) {
		if ( get_post_meta( $fp->ID, '_ut_status', true ) !== 'paid' ) {
			$due++;
		}
	}
	$bn = function_exists( 'uturn_bn' ) ? 'uturn_bn' : 'strval';
	echo '<div class="utd-stats">';
	echo '<a class="utd-stat" href="' . esc_url( uturn_dash_view_url( 'my-attendance' ) ) . '"><b>' . esc_html( call_user_func( $bn, $att['pct'] ) ) . '%</b><span>উপস্থিতির হার</span></a>';
	echo '<a class="utd-stat" href="' . esc_url( uturn_dash_view_url( 'my-results' ) ) . '"><b>' . esc_html( call_user_func( $bn, count( $myres ) ) ) . '</b><span>প্রকাশিত ফলাফল</span></a>';
	echo '<a class="utd-stat" href="' . esc_url( uturn_dash_view_url( 'my-fees' ) ) . '"><b>' . esc_html( call_user_func( $bn, $due ) ) . '</b><span>বকেয়া ফি</span></a>';
	echo '</div><div class="utd-cols"><div>';
	echo '<div class="utd-card"><h2>📅 হাজিরা সংক্ষেপ</h2><div class="utd-bar"><i style="width:' . (int) $att['pct'] . '%"></i></div><p class="utd-muted">' . esc_html( call_user_func( $bn, $att['present'] ) ) . '/' . esc_html( call_user_func( $bn, $att['total'] ) ) . ' দিন উপস্থিত · <a href="' . esc_url( uturn_dash_view_url( 'my-attendance' ) ) . '">বিস্তারিত</a></p></div>';
	if ( $myres ) {
		$rp = $myres[0];
		$g = function ( $k ) use ( $rp ) { return get_post_meta( $rp->ID, $k, true ); };
		echo '<div class="utd-card"><h2>🎓 সর্বশেষ ফলাফল</h2><p><b>' . esc_html( $g( '_ut_exam_bn' ) ) . ' · ' . esc_html( $g( '_ut_year' ) ) . '</b></p><p>জিপিএ <b>' . esc_html( $g( '_ut_gpa' ) ) . '</b> <span class="utd-pill ' . ( $g( '_ut_status' ) !== 'fail' ? 'green' : 'red' ) . '">' . ( $g( '_ut_status' ) !== 'fail' ? 'উত্তীর্ণ' : 'অনুত্তীর্ণ' ) . '</span></p><p><a href="' . esc_url( uturn_dash_view_url( 'my-results' ) ) . '">সব ফলাফল →</a></p></div>';
	}
	echo '</div><div>';
	$notices = get_posts( array( 'post_type' => 'ut_notice', 'posts_per_page' => 5, 'post_status' => 'publish' ) );
	echo '<div class="utd-card"><h2>📢 সাম্প্রতিক নোটিশ</h2><ul class="utd-list">';
	foreach ( $notices as $n ) {
		echo '<li><a href="' . esc_url( uturn_dash_view_url( 'notice', array( 'id' => $n->ID ) ) ) . '">' . esc_html( $n->post_title ) . '</a><br><small class="utd-muted">' . esc_html( mysql2date( 'j F Y', $n->post_date ) ) . '</small></li>';
	}
	if ( ! $notices ) {
		echo '<li>কোনো নোটিশ নেই।</li>';
	}
	echo '</ul><p><a href="' . esc_url( uturn_dash_view_url( 'snotices' ) ) . '">সব নোটিশ →</a></p></div>';
	echo '</div></div>';
}

function uturn_dash_my_results() {
	if ( ! uturn_dash_erp_or_lock() ) {
		return;
	}
	$myres = uturn_my_results( uturn_my_student() );
	$cum = function_exists( 'uturn_cumulative_gpa' ) ? uturn_cumulative_gpa( $myres ) : null;
	if ( $cum ) {
		$bnf = function_exists( 'uturn_bn' ) ? 'uturn_bn' : 'strval';
		echo '<div class="utd-card"><h2>📊 সামগ্রিক গড় (' . esc_html( call_user_func( $bnf, $cum['n'] ) ) . 'টি পরীক্ষা)</h2><p><strong>গড় জিপিএ ' . esc_html( call_user_func( $bnf, number_format( (float) $cum['avg'], 2 ) ) ) . '</strong> ' . ( $cum['fails'] > 0 ? '<span class="utd-pill red">অনুত্তীর্ণ ' . esc_html( call_user_func( $bnf, $cum['fails'] ) ) . 'টি</span>' : '<span class="utd-pill green">সবগুলোতে উত্তীর্ণ ✅</span>' ) . '</p></div>';
	}
	if ( ! $myres ) {
		echo '<div class="utd-card"><p class="utd-muted">এখনও কোনো ফলাফল প্রকাশিত হয়নি। প্রকাশ হলে এখানে দেখতে পাবেন।</p></div>';
		return;
	}
	foreach ( $myres as $rp ) {
		$g = function ( $k ) use ( $rp ) { return get_post_meta( $rp->ID, $k, true ); };
		$pass = $g( '_ut_status' ) !== 'fail';
		echo '<div class="utd-card"><h2>' . esc_html( $g( '_ut_exam_bn' ) ) . ' · ' . esc_html( $g( '_ut_year' ) ) . '</h2>';
		echo '<p class="utd-muted">শ্রেণি ' . esc_html( $g( '_ut_class' ) ) . ' · রোল ' . esc_html( $g( '_ut_roll' ) ) . ' · রেজি ' . esc_html( $g( '_ut_reg' ) ) . '</p>';
		echo '<p><span class="utd-pill ' . ( $pass ? 'green' : 'red' ) . '">' . ( $pass ? 'উত্তীর্ণ' : 'অনুত্তীর্ণ' ) . '</span> <strong>জিপিএ ' . esc_html( $g( '_ut_gpa' ) ) . '</strong> <button type="button" class="button button-small noprint" onclick="utPrintOne(this)">🖨️ এই ফলাফল প্রিন্ট</button></p>';
		echo '<details><summary>বিষয়ভিত্তিক নম্বর দেখুন</summary><table class="widefat striped" aria-label="বিষয়ভিত্তিক নম্বর"><thead><tr><th scope="col">বিষয়</th><th scope="col">নম্বর</th><th scope="col">গ্রেড</th></tr></thead><tbody>';
		foreach ( uturn_lines( $g( '_ut_subjects' ) ) as $ln ) {
			$sp = array_map( 'trim', explode( '|', $ln ) );
			if ( count( $sp ) < 3 ) {
				continue;
			}
			$mkt = $sp[1];
			if ( isset( $sp[3] ) && '' !== $sp[3] && '100' !== $sp[3] ) {
				$mkt .= '/' . $sp[3];
			}
			echo '<tr><td>' . esc_html( $sp[0] ) . '</td><td>' . esc_html( $mkt ) . '</td><td>' . esc_html( $sp[2] ) . '</td></tr>';
		}
		echo '</tbody></table></details></div>';
	}
	?>
	<script>
	function utPrintOne(b){
		var c=b.closest('.utd-card');if(!c)return;
		var dt=c.querySelector('details');var wasOpen=dt&&dt.open;
		if(dt)dt.open=true;
		c.classList.add('ut-printing');document.body.classList.add('ut-print-one');
		function done(){document.body.classList.remove('ut-print-one');c.classList.remove('ut-printing');if(dt)dt.open=wasOpen;window.removeEventListener('afterprint',done);}
		window.addEventListener('afterprint',done);
		window.print();setTimeout(done,1500);
	}
	</script>
	<?php
}

function uturn_dash_my_fees() {
	if ( ! uturn_dash_erp_or_lock() ) {
		return;
	}
	$myfees = uturn_my_fees( uturn_my_student() );
	if ( ! $myfees ) {
		echo '<div class="utd-card"><p class="utd-muted">কোনো ফি রেকর্ড পাওয়া যায়নি।</p></div>';
		return;
	}
	echo '<div class="utd-card"><table class="widefat striped" aria-label="ফি বিবরণ"><thead><tr><th scope="col">মাস</th><th scope="col">পরিমাণ</th><th scope="col">অবস্থা</th><th scope="col">মাধ্যম</th></tr></thead><tbody>';
	foreach ( $myfees as $fp ) {
		$paid = get_post_meta( $fp->ID, '_ut_status', true ) === 'paid';
		echo '<tr><td>' . esc_html( get_post_meta( $fp->ID, '_ut_month', true ) ) . '</td><td>' . esc_html( uturn_bn( get_post_meta( $fp->ID, '_ut_amount', true ) ) ) . ' টাকা</td><td><span class="utd-pill ' . ( $paid ? 'green' : 'red' ) . '">' . ( $paid ? 'পরিশোধিত' : 'বকেয়া' ) . '</span></td><td>' . esc_html( get_post_meta( $fp->ID, '_ut_method', true ) ? get_post_meta( $fp->ID, '_ut_method', true ) : '—' ) . '</td></tr>';
	}
	echo '</tbody></table></div>';
}

function uturn_dash_my_attendance() {
	if ( ! uturn_dash_erp_or_lock() ) {
		return;
	}
	$att = uturn_my_attendance( uturn_my_student() );
	$bn = function_exists( 'uturn_bn' ) ? 'uturn_bn' : 'strval';
	echo '<div class="utd-card"><h2>উপস্থিতির হার: ' . esc_html( call_user_func( $bn, $att['pct'] ) ) . '% <small class="utd-muted">(' . esc_html( call_user_func( $bn, $att['present'] ) ) . '/' . esc_html( call_user_func( $bn, $att['total'] ) ) . ' দিন)</small></h2><div class="utd-bar"><i style="width:' . (int) $att['pct'] . '%"></i></div>';
	if ( $att['recent'] ) {
		echo '<ul class="utd-att">';
		foreach ( $att['recent'] as $a ) {
			echo '<li><span>' . esc_html( uturn_bn_date( $a['date'] ) ) . '</span><span class="utd-pill ' . ( $a['in'] ? 'green' : 'red' ) . '">' . ( $a['in'] ? 'উপস্থিত' : 'অনুপস্থিত' ) . '</span></li>';
		}
		echo '</ul>';
	} else {
		echo '<p class="utd-muted">এখনও হাজিরা রেকর্ড হয়নি।</p>';
	}
	echo '</div>';
}

/* ================= teacher ================= */
function uturn_dash_teacher_home() {
	$u = wp_get_current_user();
	echo '<div class="utd-card"><h2>👋 স্বাগতম, ' . esc_html( $u->display_name ) . '!</h2><p class="utd-muted">আজকের শ্রেণি কার্যক্রম এখান থেকে।</p></div>';
	if ( ! uturn_dash_erp_or_lock() ) {
		return;
	}
	$today = current_time( 'Y-m-d' );
	$taken = get_posts( array( 'post_type' => 'ut_attendance', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_ut_date', 'meta_value' => $today ) );
	$bn = function_exists( 'uturn_bn' ) ? 'uturn_bn' : 'strval';
	echo '<div class="utd-stats">';
	echo '<a class="utd-stat" href="' . esc_url( uturn_dash_view_url( 'take-attendance' ) ) . '"><b>' . esc_html( call_user_func( $bn, count( $taken ) ) ) . '</b><span>আজ হাজিরা (শ্রেণি)</span></a>';
	echo '<a class="utd-stat" href="' . esc_url( uturn_dash_view_url( 'my-students' ) ) . '"><b>→</b><span>শিক্ষার্থী তালিকা</span></a>';
	echo '<a class="utd-stat" href="' . esc_url( uturn_dash_view_url( 'snotices' ) ) . '"><b>→</b><span>নোটিশ বোর্ড</span></a>';
	echo '</div>';
	$cls_terms = function_exists( 'uturn_class_terms' ) ? uturn_class_terms() : get_terms( array( 'taxonomy' => 'ut_class', 'hide_empty' => false ) );
	if ( ! is_wp_error( $cls_terms ) && $cls_terms ) {
		echo '<div class="utd-card"><h2>📊 শ্রেণিভিত্তিক শিক্ষার্থী</h2><table class="widefat striped" aria-label="শ্রেণিভিত্তিক শিক্ষার্থী"><thead><tr><th scope="col">শ্রেণি</th><th scope="col">শিক্ষার্থী</th></tr></thead><tbody>';
		foreach ( $cls_terms as $t ) {
			echo '<tr><td>' . esc_html( $t->name ) . '</td><td>' . esc_html( call_user_func( $bn, $t->count ) ) . ' জন</td></tr>';
		}
		echo '</tbody></table></div>';
	}
}

function uturn_dash_take_attendance() {
	if ( ! uturn_dash_erp_or_lock() ) {
		return;
	}
	if ( isset( $_GET['uturn_msg'] ) && $_GET['uturn_msg'] === 'attendance-saved' ) {
		echo '<div class="notice notice-success" role="status"><p>✅ হাজিরা সংরক্ষণ করা হয়েছে।</p></div>';
	} elseif ( isset( $_GET['uturn_msg'] ) ) {
		echo '<div class="notice notice-error" role="alert"><p>⚠️ ফর্মে সমস্যা হয়েছে। আবার চেষ্টা করুন।</p></div>';
	}
	$cls_terms = function_exists( 'uturn_class_terms' ) ? uturn_class_terms() : get_terms( array( 'taxonomy' => 'ut_class', 'hide_empty' => false ) );
	$scope = function_exists( 'uturn_scoped_classes' ) ? uturn_scoped_classes() : null;
	$att_class = isset( $_GET['att_class'] ) ? sanitize_text_field( wp_unslash( $_GET['att_class'] ) ) : '';
	if ( is_array( $scope ) && '' !== $att_class && ! in_array( $att_class, $scope, true ) ) {
		$att_class = '';
	}
	$att_date = isset( $_GET['att_date'] ) ? sanitize_text_field( wp_unslash( $_GET['att_date'] ) ) : current_time( 'Y-m-d' );
	if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $att_date ) ) {
		$att_date = current_time( 'Y-m-d' );
	}
	echo '<div class="utd-card"><form method="get" action="' . esc_url( uturn_dash_url() ) . '"><input type="hidden" name="view" value="take-attendance">';
	echo '<table class="form-table"><tr><th scope="row">শ্রেণি</th><td><select name="att_class" onchange="this.form.submit()" aria-label="শ্রেণি নির্বাচন"><option value="">নির্বাচন করুন</option>';
	if ( ! is_wp_error( $cls_terms ) ) {
		foreach ( $cls_terms as $t ) {
			if ( is_array( $scope ) && ! in_array( $t->name, $scope, true ) ) {
				continue;
			}
			echo '<option value="' . esc_attr( $t->name ) . '"' . selected( $att_class, $t->name, false ) . '>' . esc_html( $t->name ) . ' (' . esc_html( uturn_bn( $t->count ) ) . ')</option>';
		}
	}
	echo '</select></td></tr><tr><th scope="row">তারিখ</th><td><input type="date" name="att_date" aria-label="তারিখ নির্বাচন" value="' . esc_attr( $att_date ) . '" onchange="this.form.submit()"></td></tr></table></form></div>';
	if ( ! $att_class ) {
		echo '<div class="utd-card"><p class="utd-muted">উপরে শ্রেণি নির্বাচন করুন।</p></div>';
		return;
	}
	$roster = uturn_class_students( $att_class );
	$aid = uturn_find_attendance( $att_class, $att_date );
	$marked = $aid ? array_filter( array_map( 'trim', explode( ',', (string) get_post_meta( $aid, '_ut_present', true ) ) ) ) : array();
	if ( ! $roster ) {
		echo '<div class="utd-card"><p class="utd-muted">এই শ্রেণিতে কোনো শিক্ষার্থী নেই।</p></div>';
		return;
	}
	$ct_hint = '';
	if ( function_exists( 'uturn_class_teacher' ) ) {
		$ct_id = uturn_class_teacher( $att_class );
		if ( $ct_id ) {
			$ct_u = get_userdata( $ct_id );
			$ct_hint = ' · 🏫 শ্রেণি-শিক্ষক: ' . ( $ct_u ? $ct_u->display_name : '' );
		}
	}
	$is_office = (bool) array_intersect( array( 'uturn_school_admin', 'uturn_headmaster' ), (array) wp_get_current_user()->roles );
	echo '<div class="utd-card"><h2>' . esc_html( $att_class ) . ' · ' . esc_html( uturn_bn_date( $att_date ) ) . ' · মোট ' . esc_html( uturn_bn( count( $roster ) ) ) . ' জন' . $ct_hint . ( $aid ? ' · <span class="utd-ok">ইতিমধ্যে নেওয়া (আপডেট করা যাবে)</span>' : '' ) . '</h2>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="uturn_attendance"><input type="hidden" name="ut_shell" value="1">';
	wp_nonce_field( 'uturn_attendance' );
	echo '<input type="hidden" name="att-class" value="' . esc_attr( $att_class ) . '"><input type="hidden" name="att-date" value="' . esc_attr( $att_date ) . '">';
	if ( $is_office ) {
		echo '<p class="notice notice-info" style="margin:0 0 10px"><span style="display:block;padding:6px 0">📩 সংরক্ষণ করলে অনুপস্থিতদের অভিভাবকের কাছে SMS যাবে। <label><input type="checkbox" name="no_sms" value="1"> এবার SMS পাঠাবেন না</label></span></p>';
	}
	echo '<div class="table-wrap"><table class="widefat striped" aria-label="উপস্থিতি তালিকা"><thead><tr><th scope="col"><input type="checkbox" id="attAll" checked aria-label="সব নির্বাচন"></th><th scope="col">নাম</th><th scope="col">রোল</th><th scope="col">শাখা</th></tr></thead><tbody>';
	foreach ( $roster as $sp ) {
		$on = $aid ? in_array( (string) $sp->ID, $marked, true ) : true;
		echo '<tr><td><input type="checkbox" class="att-cb" name="present[]" value="' . (int) $sp->ID . '"' . checked( $on, true, false ) . '></td><td>' . esc_html( $sp->post_title ) . '</td><td>' . esc_html( get_post_meta( $sp->ID, '_ut_roll', true ) ) . '</td><td>' . esc_html( get_post_meta( $sp->ID, '_ut_section', true ) ) . '</td></tr>';
	}
	echo '</tbody></table></div><p><button class="button button-primary" type="submit">✅ হাজিরা সংরক্ষণ করুন</button></p></form></div>';
	/* Printable attendance sheet (A4). */
	$school = function_exists( 'uturn_opt' ) ? uturn_opt( 'school_name_bn', get_bloginfo( 'name' ) ) : get_bloginfo( 'name' );
	$addr = function_exists( 'uturn_opt' ) ? uturn_opt( 'address', '' ) : '';
	echo '<div class="utd-card noprint"><button class="button button-primary" onclick="window.print()">🖨️ হাজিরা শিট প্রিন্ট (A4)</button></div>';
	echo '<div class="utd-card print-doc"><div class="print-head"><b>' . esc_html( $school ) . '</b><span>' . esc_html( $addr ) . '</span><i>দৈনিক হাজিরা শিট — শ্রেণি: ' . esc_html( $att_class ) . ' · তারিখ: ' . esc_html( function_exists( 'uturn_bn_date' ) ? uturn_bn_date( $att_date ) : $att_date ) . '</i></div>';
	echo '<div class="table-wrap"><table class="widefat striped print-table" aria-label="উপস্থিতি শিট"><thead><tr><th scope="col">রোল</th><th scope="col">নাম</th><th scope="col">শাখা</th><th scope="col">উপস্থিত</th><th scope="col">অনুপস্থিত</th><th scope="col">মন্তব্য</th></tr></thead><tbody>';
	$pc = 0;
	foreach ( $roster as $sp ) {
		$on = $aid ? in_array( (string) $sp->ID, $marked, true ) : true;
		if ( $on ) { $pc++; }
		echo '<tr><td><b>' . esc_html( get_post_meta( $sp->ID, '_ut_roll', true ) ) . '</b></td><td>' . esc_html( $sp->post_title ) . '</td><td>' . esc_html( get_post_meta( $sp->ID, '_ut_section', true ) ) . '</td><td style="text-align:center">' . ( $on ? '✔' : '☐' ) . '</td><td style="text-align:center">' . ( $on ? '☐' : '✔' ) . '</td><td></td></tr>';
	}
	echo '</tbody></table></div>';
	echo '<p>মোট: ' . esc_html( function_exists( 'uturn_bn' ) ? uturn_bn( count( $roster ) ) : count( $roster ) ) . ' · উপস্থিত: ' . esc_html( function_exists( 'uturn_bn' ) ? uturn_bn( $pc ) : $pc ) . ' · অনুপস্থিত: ' . esc_html( function_exists( 'uturn_bn' ) ? uturn_bn( count( $roster ) - $pc ) : count( $roster ) - $pc ) . '</p>';
	echo '<div class="print-sign"><span>শ্রেণি-শিক্ষকের স্বাক্ষর</span><span>প্রধান শিক্ষকের স্বাক্ষর ও সিল</span></div></div>';
	echo "<script>(function(){var a=document.getElementById('attAll');if(a)a.addEventListener('change',function(){document.querySelectorAll('.att-cb').forEach(function(c){c.checked=a.checked;});});})();</script>";
}

function uturn_dash_my_students() {
	if ( ! uturn_dash_erp_or_lock() ) {
		return;
	}
	$cls_terms = function_exists( 'uturn_class_terms' ) ? uturn_class_terms() : get_terms( array( 'taxonomy' => 'ut_class', 'hide_empty' => false ) );
	$scope = function_exists( 'uturn_scoped_classes' ) ? uturn_scoped_classes() : null;
	$cls = isset( $_GET['cls'] ) ? sanitize_text_field( wp_unslash( $_GET['cls'] ) ) : '';
	if ( is_array( $scope ) && '' !== $cls && ! in_array( $cls, $scope, true ) ) {
		echo '<div class="utd-card"><p class="utd-muted">⚠️ এই শ্রেণি আপনার নির্ধারিত তালিকায় নেই।</p></div>';
		return;
	}
	echo '<div class="utd-card"><form method="get" action="' . esc_url( uturn_dash_url() ) . '"><input type="hidden" name="view" value="my-students">';
	echo 'শ্রেণি <select name="cls" onchange="this.form.submit()" aria-label="শ্রেণি নির্বাচন"><option value="">সব শ্রেণি</option>';
	if ( ! is_wp_error( $cls_terms ) ) {
		foreach ( $cls_terms as $t ) {
			if ( is_array( $scope ) && ! in_array( $t->name, $scope, true ) ) {
				continue;
			}
			echo '<option value="' . esc_attr( $t->name ) . '"' . selected( $cls, $t->name, false ) . '>' . esc_html( $t->name ) . '</option>';
		}
	}
	echo '</select></form></div>';
	$args = array( 'post_type' => 'ut_student', 'posts_per_page' => 200, 'orderby' => 'title', 'order' => 'ASC' );
	if ( $cls !== '' ) {
		$args['meta_key'] = '_ut_class';
		$args['meta_value'] = $cls;
	} elseif ( is_array( $scope ) ) {
		/* Scoped teachers: own classes only (never the whole school). */
		$args['meta_query'] = array( array( 'key' => '_ut_class', 'value' => $scope ? $scope : array( '__none__' ), 'compare' => 'IN' ) );
	}
	$roster = get_posts( $args );
	echo '<div class="utd-card"><table class="widefat striped" aria-label="শিক্ষার্থী তালিকা"><thead><tr><th scope="col">নাম</th><th scope="col">শ্রেণি</th><th scope="col">রোল</th><th scope="col">শাখা</th><th scope="col">অভিভাবক</th></tr></thead><tbody>';
	foreach ( $roster as $sp ) {
		echo '<tr><td>' . esc_html( $sp->post_title ) . '</td><td>' . esc_html( get_post_meta( $sp->ID, '_ut_class', true ) ) . '</td><td>' . esc_html( get_post_meta( $sp->ID, '_ut_roll', true ) ) . '</td><td>' . esc_html( get_post_meta( $sp->ID, '_ut_section', true ) ) . '</td><td>' . esc_html( get_post_meta( $sp->ID, '_ut_guardian', true ) ) . '</td></tr>';
	}
	if ( ! $roster ) {
		echo '<tr><td colspan="5">কোনো শিক্ষার্থী নেই।</td></tr>';
	}
	echo '</tbody></table></div>';
}

function uturn_dash_results_view() {
	if ( ! uturn_dash_erp_or_lock() ) {
		return;
	}
	$scope = function_exists( 'uturn_scoped_classes' ) ? uturn_scoped_classes() : null;
	$cls = isset( $_GET['cls'] ) ? sanitize_text_field( wp_unslash( $_GET['cls'] ) ) : '';
	$exam = isset( $_GET['exam'] ) ? sanitize_key( $_GET['exam'] ) : '';
	if ( is_array( $scope ) && '' !== $cls && ! in_array( $cls, $scope, true ) ) {
		echo '<div class="utd-card"><p class="utd-muted">⚠️ এই শ্রেণি আপনার নির্ধারিত তালিকায় নেই।</p></div>';
		return;
	}
	$cls_terms = function_exists( 'uturn_class_terms' ) ? uturn_class_terms() : get_terms( array( 'taxonomy' => 'ut_class', 'hide_empty' => false ) );
	$exams = function_exists( 'uturn_exams' ) ? uturn_exams( false ) : array();
	echo '<div class="utd-card"><form method="get" action="' . esc_url( uturn_dash_url() ) . '"><input type="hidden" name="view" value="results-view">';
	echo '<label>শ্রেণি <select name="cls"><option value="">— সব —</option>';
	if ( ! is_wp_error( $cls_terms ) ) {
		foreach ( $cls_terms as $t ) {
			if ( is_array( $scope ) && ! in_array( $t->name, $scope, true ) ) {
				continue;
			}
			echo '<option' . selected( $cls, $t->name, false ) . '>' . esc_html( $t->name ) . '</option>';
		}
	}
	echo '</select></label> <label>পরীক্ষা <select name="exam"><option value="">— সব —</option>';
	foreach ( $exams as $slug => $bn ) {
		echo '<option value="' . esc_attr( $slug ) . '"' . selected( $exam, $slug, false ) . '>' . esc_html( $bn ) . '</option>';
	}
	echo '</select></label> ';
	submit_button( 'ফলাফল দেখুন', 'secondary', '', false );
	echo '</form></div>';
	$mq = array();
	if ( $cls !== '' ) {
		$mq[] = array( 'key' => '_ut_class', 'value' => $cls );
	} elseif ( is_array( $scope ) ) {
		$mq[] = array( 'key' => '_ut_class', 'value' => $scope ? $scope : array( '__none__' ), 'compare' => 'IN' );
	}
	if ( $exam !== '' && isset( $exams[ $exam ] ) ) {
		$mq[] = array( 'key' => '_ut_exam', 'value' => $exam );
	}
	$rows = get_posts( array( 'post_type' => 'ut_result', 'posts_per_page' => 200, 'post_status' => 'publish', 'meta_query' => $mq, 'orderby' => 'meta_value', 'meta_key' => '_ut_roll', 'order' => 'ASC' ) );
	echo '<div class="utd-card"><table class="widefat striped" aria-label="ফলাফল তালিকা"><thead><tr><th scope="col">রোল</th><th scope="col">পরীক্ষা</th><th scope="col">জিপিএ</th><th scope="col">ফল</th></tr></thead><tbody>';
	foreach ( $rows as $rp ) {
		$pass = get_post_meta( $rp->ID, '_ut_status', true ) !== 'fail';
		echo '<tr><td>' . esc_html( get_post_meta( $rp->ID, '_ut_roll', true ) ) . '</td><td>' . esc_html( get_post_meta( $rp->ID, '_ut_exam_bn', true ) ) . ' ' . esc_html( get_post_meta( $rp->ID, '_ut_year', true ) ) . '</td><td>' . esc_html( get_post_meta( $rp->ID, '_ut_gpa', true ) ) . '</td><td><span class="utd-pill ' . ( $pass ? 'green' : 'red' ) . '">' . ( $pass ? 'উত্তীর্ণ' : 'অনুত্তীর্ণ' ) . '</span></td></tr>';
	}
	if ( ! $rows ) {
		echo '<tr><td colspan="4">কোনো ফলাফল নেই।</td></tr>';
	}
	echo '</tbody></table></div>';
}

/* ================= shared reads ================= */
function uturn_dash_routine_view() {
	$R = function_exists( 'uturn_routines' ) ? uturn_routines() : array();
	$days = function_exists( 'uturn_routine_days' ) ? uturn_routine_days() : array();
	$classes = array_keys( (array) ( $R['classes'] ?? array() ) );
	$cls = isset( $_GET['cls'] ) ? sanitize_text_field( wp_unslash( $_GET['cls'] ) ) : ( $classes ? $classes[0] : '' );
	$mine = '';
	if ( in_array( 'uturn_student', (array) wp_get_current_user()->roles, true ) ) {
		$stp = uturn_my_student();
		$mine = $stp ? get_post_meta( $stp->ID, '_ut_class', true ) : '';
		if ( $mine !== '' && ! isset( $_GET['cls'] ) ) {
			$cls = $mine;
		}
	}
	echo '<div class="utd-card"><form method="get" action="' . esc_url( uturn_dash_url() ) . '"><input type="hidden" name="view" value="routine-view">শ্রেণি <select name="cls" onchange="this.form.submit()" aria-label="শ্রেণি নির্বাচন">';
	foreach ( $classes as $c ) {
		echo '<option value="' . esc_attr( $c ) . '"' . selected( $cls, $c, false ) . '>' . esc_html( $c ) . '</option>';
	}
	echo '</select></form></div>';
	$grid = $cls !== '' ? ( (array) ( $R['classes'] ?? array() ) ) : array();
	$grid = isset( $grid[ $cls ] ) ? (array) $grid[ $cls ] : array();
	$periods = (array) ( $R['periods'] ?? array() );
	if ( ! $grid || ! $periods ) {
		echo '<div class="utd-card"><p class="utd-muted">এই শ্রেণির রুটিন এখনও তৈরি হয়নি।</p></div>';
		return;
	}
	echo '<div class="utd-card"><table class="widefat striped" aria-label="ক্লাস রুটিন"><thead><tr><th scope="col">দিন \\ পিরিয়ড</th>';
	foreach ( $periods as $i => $pn ) {
		$tm = isset( $R['times'][ $i ] ) ? $R['times'][ $i ] : '';
		echo '<th scope="col">' . esc_html( $pn ) . ( $tm !== '' ? '<br><small>' . esc_html( $tm ) . '</small>' : '' ) . '</th>';
	}
	echo '</tr></thead><tbody>';
	$tmap = (array) ( $R['teachers'] ?? array() );
	foreach ( $days as $di => $dn ) {
		$row = isset( $grid[ $di ] ) ? (array) $grid[ $di ] : array();
		$dkey = isset( $row[0] ) && $row[0] !== '' ? $row[0] : $dn;
		echo '<tr><td><b>' . esc_html( $dkey ) . '</b></td>';
		foreach ( $periods as $pi => $pn ) {
			$cell = isset( $row[ $pi + 1 ] ) && $row[ $pi + 1 ] !== '' ? $row[ $pi + 1 ] : '—';
			$tnm = '';
			$tid2 = isset( $tmap[ $cls ][ $dkey ][ $pi ] ) ? (int) $tmap[ $cls ][ $dkey ][ $pi ] : 0;
			if ( $tid2 ) {
				$tu = get_userdata( $tid2 );
				$tnm = $tu ? $tu->display_name : '';
			}
			echo '<td>' . esc_html( $cell ) . ( $tnm !== '' ? '<br><small class="utd-muted">শিক্ষক: ' . esc_html( $tnm ) . '</small>' : '' ) . '</td>';
		}
		echo '</tr>';
	}
	echo '</tbody></table></div>';
}

function uturn_dash_downloads_view() {
	$q = new WP_Query( array( 'post_type' => 'ut_download', 'posts_per_page' => 50, 'post_status' => 'publish' ) );
	echo '<div class="utd-card"><table class="widefat striped" aria-label="নোটিশ তালিকা"><thead><tr><th scope="col">শিরোনাম</th><th scope="col">ধরন</th><th scope="col"></th></tr></thead><tbody>';
	foreach ( $q->posts as $p ) {
		$f = get_post_meta( $p->ID, '_ut_file', true );
		$url = $f ? ( is_numeric( $f ) ? wp_get_attachment_url( (int) $f ) : $f ) : '';
		echo '<tr><td><b>' . esc_html( $p->post_title ) . '</b></td><td>' . esc_html( get_post_meta( $p->ID, '_ut_ftype', true ) ? get_post_meta( $p->ID, '_ut_ftype', true ) : '—' ) . '</td><td>' . ( $url ? '<a class="button button-secondary" href="' . esc_url( $url ) . '" target="_blank" rel="noopener">ডাউনলোড</a>' : '—' ) . '</td></tr>';
	}
	if ( ! $q->posts ) {
		echo '<tr><td colspan="3">কোনো ফাইল নেই।</td></tr>';
	}
	echo '</tbody></table></div>';
}
/* ================= VIEW: fee receipt (print) ================= */
function uturn_dash_fee_receipt() {
	if ( function_exists( 'uturn_dash_erp_or_lock' ) && ! uturn_dash_erp_or_lock() ) {
		return;
	}
	$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
	$fee = $id ? get_post( $id ) : null;
	if ( ! $fee || 'ut_fee' !== $fee->post_type ) {
		echo '<div class="utd-card"><p class="utd-muted">ফি রেকর্ড পাওয়া যায়নি।</p></div>';
		return;
	}
	$g = function ( $k ) use ( $id ) { return get_post_meta( $id, $k, true ); };
	$sid = (int) $g( '_ut_student_id' );
	$name = preg_replace( '/\s—.*$/u', '', $fee->post_title );
	$code = $sid && function_exists( 'uturn_student_code' ) ? uturn_student_code( $sid ) : '';
	$cls = $sid ? get_post_meta( $sid, '_ut_class', true ) : '';
	$roll = $sid ? get_post_meta( $sid, '_ut_roll', true ) : '';
	$paid = 'paid' === $g( '_ut_status' );
	$bnf = function_exists( 'uturn_bn' ) ? 'uturn_bn' : 'strval';
	$school = function_exists( 'uturn_opt' ) ? uturn_opt( 'school_name_bn', get_bloginfo( 'name' ) ) : get_bloginfo( 'name' );
	$addr = function_exists( 'uturn_opt' ) ? uturn_opt( 'address', '' ) : '';
	echo '<div class="utd-card noprint"><button class="button button-primary" onclick="window.print()">🖨️ রসিদ প্রিন্ট</button> <a class="button" href="' . esc_url( uturn_dash_view_url( 'fees' ) ) . '">← ফি রেকর্ডে ফিরুন</a></div>';
	echo '<div class="utd-card print-doc"><div class="print-head"><b>' . esc_html( $school ) . '</b><span>' . esc_html( $addr ) . '</span><i>ফি আদায় রসিদ</i></div>';
	echo '<p>রসিদ নং: <b>' . esc_html( call_user_func( $bnf, $id ) ) . '</b> · তারিখ: <b>' . esc_html( $g( '_ut_date' ) !== '' ? $g( '_ut_date' ) : current_time( 'Y-m-d' ) ) . '</b> · অবস্থা: <b>' . ( $paid ? 'পরিশোধিত ✅' : 'বকেয়া ⏳' ) . '</b></p>';
	echo '<div class="table-wrap"><table class="widefat striped print-table" aria-label="ফি আদায় রসিদ"><tbody>';
	echo '<tr><td>শিক্ষার্থীর নাম</td><th scope="row">' . esc_html( $name ) . '</th><td>স্টুডেন্ট ID</td><th scope="row">' . esc_html( $code !== '' ? $code : '—' ) . '</th></tr>';
	echo '<tr><td>শ্রেণি / রোল</td><th scope="row">' . esc_html( $cls . ' / ' . $roll ) . '</th><td>মাস</td><th scope="row">' . esc_html( $g( '_ut_month' ) ) . '</th></tr>';
	echo '<tr><td>পরিমাণ</td><th scope="row">৳ ' . esc_html( call_user_func( $bnf, $g( '_ut_amount' ) ) ) . '</th><td>মাধ্যম</td><th scope="row">' . esc_html( $g( '_ut_method' ) !== '' ? $g( '_ut_method' ) : '—' ) . '</th></tr>';
	echo '</tbody></table></div>';
	echo '<div class="print-sign"><span>আদায়কারীর স্বাক্ষর</span><span>অভিভাবকের স্বাক্ষর</span><span>প্রধান শিক্ষকের স্বাক্ষর ও সিল</span></div></div>';
}

/* ================= VIEW: student profile sheet (print) ================= */
function uturn_dash_student_sheet() {
	if ( function_exists( 'uturn_dash_erp_or_lock' ) && ! uturn_dash_erp_or_lock() ) {
		return;
	}
	$sid = isset( $_GET['sid'] ) ? absint( $_GET['sid'] ) : 0;
	$sp = $sid ? get_post( $sid ) : null;
	if ( ! $sp || 'ut_student' !== $sp->post_type ) {
		/* Picker: class then student. */
		$classes = array();
		if ( function_exists( 'uturn_class_terms' ) ) {
			foreach ( uturn_class_terms() as $t ) { $classes[] = $t->name; }
		}
		$cls = isset( $_GET['cls'] ) ? sanitize_text_field( wp_unslash( $_GET['cls'] ) ) : '';
		echo '<div class="utd-card"><form method="get" action="' . esc_url( uturn_dash_url() ) . '"><input type="hidden" name="view" value="student-sheet">';
		echo 'শ্রেণি <select name="cls" onchange="this.form.submit()" aria-label="শ্রেণি নির্বাচন"><option value="">নির্বাচন করুন</option>';
		foreach ( $classes as $c ) { echo '<option' . selected( $cls, $c, false ) . '>' . esc_html( $c ) . '</option>'; }
		echo '</select></form></div>';
		if ( '' !== $cls && function_exists( 'uturn_entry_students' ) ) {
			echo '<div class="utd-card"><div class="table-wrap"><table class="widefat striped" aria-label="শ্রেণির শিক্ষার্থী"><thead><tr><th scope="col">রোল</th><th scope="col">নাম</th><th scope="col">স্টুডেন্ট ID</th><th scope="col"></th></tr></thead><tbody>';
			foreach ( uturn_entry_students( $cls ) as $row ) {
				$url = add_query_arg( 'sid', $row->ID, uturn_dash_view_url( 'student-sheet' ) );
				echo '<tr><td><b>' . esc_html( get_post_meta( $row->ID, '_ut_roll', true ) ) . '</b></td><td>' . esc_html( $row->post_title ) . '</td><td>' . esc_html( function_exists( 'uturn_student_code' ) ? uturn_student_code( $row->ID ) : '' ) . '</td><td><a class="button button-small" href="' . esc_url( $url ) . '">প্রোফাইল খুলুন</a></td></tr>';
			}
			echo '</tbody></table></div></div>';
		}
		return;
	}
	$g = function ( $k ) use ( $sp ) { return get_post_meta( $sp->ID, $k, true ); };
	$sts = function_exists( 'uturn_student_statuses' ) ? uturn_student_statuses() : array();
	$st = $g( '_ut_status' ) !== '' ? $g( '_ut_status' ) : 'active';
	$school = function_exists( 'uturn_opt' ) ? uturn_opt( 'school_name_bn', get_bloginfo( 'name' ) ) : get_bloginfo( 'name' );
	$addr = function_exists( 'uturn_opt' ) ? uturn_opt( 'address', '' ) : '';
	$pid = (int) $g( '_ut_photo' );
	if ( ! $pid ) { $pid = (int) $g( '_ut_photo_id' ); }
	echo '<div class="utd-card noprint"><button class="button button-primary" onclick="window.print()">🖨️ প্রোফাইল প্রিন্ট (A4)</button> <a class="button" href="' . esc_url( uturn_dash_view_url( 'student-sheet' ) ) . '">← অন্য শিক্ষার্থী</a></div>';
	echo '<div class="utd-card print-doc"><div class="print-head"><b>' . esc_html( $school ) . '</b><span>' . esc_html( $addr ) . '</span><i>শিক্ষার্থী প্রোফাইল</i></div>';
	echo '<div style="display:flex;gap:18px;align-items:center;margin:0 0 12px">';
	if ( $pid ) { echo wp_get_attachment_image( $pid, 'thumbnail', false, array( 'style' => 'width:90px;height:90px;object-fit:cover;border:1px solid #ccc' ) ); }
	echo '<div><h2 style="margin:0">' . esc_html( $sp->post_title ) . '</h2><p class="utd-muted" style="margin:2px 0 0">স্টুডেন্ট ID: <b>' . esc_html( $g( '_ut_student_code' ) ) . '</b> · অবস্থা: <b>' . esc_html( isset( $sts[ $st ] ) ? $sts[ $st ] : $st ) . '</b></p></div></div>';
	echo '<div class="table-wrap"><table class="widefat striped print-table" aria-label="শিক্ষার্থী প্রোফাইল"><tbody>';
	echo '<tr><td>শ্রেণি / শাখা / রোল</td><th scope="row">' . esc_html( $g( '_ut_class' ) . ' / ' . $g( '_ut_section' ) . ' / ' . $g( '_ut_roll' ) ) . '</th><td>অভিভাবক</td><th scope="row">' . esc_html( $g( '_ut_guardian' ) !== '' ? $g( '_ut_guardian' ) : '—' ) . '</th></tr>';
	echo '<tr><td>মোবাইল</td><th scope="row">' . esc_html( $g( '_ut_phone' ) !== '' ? $g( '_ut_phone' ) : '—' ) . '</th><td>জন্মতারিখ / রক্ত</td><th scope="row">' . esc_html( ( $g( '_ut_dob' ) !== '' ? $g( '_ut_dob' ) : '—' ) . ' / ' . ( $g( '_ut_bg' ) !== '' ? $g( '_ut_bg' ) : '—' ) ) . '</th></tr>';
	$hist = $g( '_ut_class_history' );
	if ( is_array( $hist ) && $hist ) {
		$hs = array();
		foreach ( $hist as $hy => $hc ) { $hs[] = $hy . ' → ' . $hc; }
		echo '<tr><td>শ্রেণি ইতিহাস</td><th scope="row" colspan="3">' . esc_html( implode( ' · ', $hs ) ) . '</th></tr>';
	}
	echo '</tbody></table></div>';
	$res = get_posts( array( 'post_type' => 'ut_result', 'posts_per_page' => -1, 'post_status' => 'publish', 'meta_key' => '_ut_student_id', 'meta_value' => $sp->ID ) );
	if ( $res ) {
		echo '<h3>প্রকাশিত ফলাফল</h3><div class="table-wrap"><table class="widefat striped print-table" aria-label="প্রকাশিত ফলাফল"><thead><tr><th scope="col">পরীক্ষা</th><th scope="col">বছর</th><th scope="col">শ্রেণি</th><th scope="col">মোট</th><th scope="col">জিপিএ</th><th scope="col">মেধা</th><th scope="col">ফল</th></tr></thead><tbody>';
		foreach ( $res as $r ) {
			$rg = function ( $k ) use ( $r ) { return get_post_meta( $r->ID, $k, true ); };
			echo '<tr><td>' . esc_html( $rg( '_ut_exam_bn' ) ) . '</td><td>' . esc_html( $rg( '_ut_year' ) ) . '</td><td>' . esc_html( $rg( '_ut_class' ) ) . '</td><td>' . esc_html( $rg( '_ut_total' ) ) . '</td><td><b>' . esc_html( $rg( '_ut_gpa' ) ) . '</b></td><td>' . esc_html( function_exists( 'uturn_merit_label' ) ? uturn_merit_label( $rg( '_ut_merit' ) ) : $rg( '_ut_merit' ) ) . '</td><td>' . ( 'fail' === $rg( '_ut_status' ) ? 'অনুত্তীর্ণ' : 'উত্তীর্ণ' ) . '</td></tr>';
		}
		echo '</tbody></table></div>';
	}
	echo '<div class="print-sign"><span>শ্রেণি-শিক্ষকের স্বাক্ষর</span><span>প্রধান শিক্ষকের স্বাক্ষর ও সিল</span></div></div>';
}
