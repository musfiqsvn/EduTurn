<?php
/**
 * Template Name: Student Portal
 * Phase-1 portal: role-gated quick dashboard (full SMS dashboards = Phase 2).
 */
get_header();
$edu_lic_ok = function_exists( 'eduturn_license_can' ) && eduturn_license_can( 'erp' );
$user = wp_get_current_user();
$ok = is_user_logged_in() && in_array( 'uturn_student', (array) $user->roles, true );
$notices_url = get_post_type_archive_link( 'ut_notice' ) ? get_post_type_archive_link( 'ut_notice' ) : home_url( '/notices/' );
?>
<main id="main">
<section class="page-hero"><div class="container page-hero-inner">
<ul class="breadcrumbs"><li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( uturn_t( 'হোম', 'Home' ) ); ?></a></li><li><?php echo esc_html( uturn_t( 'শিক্ষার্থী পোর্টাল', 'Student Portal' ) ); ?></li></ul>
<h1><?php echo esc_html( uturn_t( 'শিক্ষার্থী পোর্টাল', 'Student Portal' ) ); ?></h1><p><?php echo $ok ? esc_html( uturn_t( 'স্বাগতম, ', 'Welcome, ' ) ) . esc_html( $user->display_name ) . '!' : esc_html( uturn_t( 'অনুগ্রহ করে আপনার শিক্ষার্থী অ্যাকাউন্টে সাইন ইন করুন।', 'Please sign in with your student account.' ) ); ?></p>
</div></section>
<section class="section"><div class="container">
<?php if ( ! is_user_logged_in() ) : ?>
<?php uturn_portal_login_card( uturn_t( 'শিক্ষার্থী সাইন ইন', 'Student Sign In' ), uturn_t( 'রেজাল্ট, রুটিন, নোটিশ আর ফি — সব এক জায়গায়।', 'Results, routine, notices and fees — all in one place.' ), 'student' ); ?>
<?php elseif ( ! $ok ) : ?>
<div class="empty-state"><h3><?php echo esc_html( uturn_t( 'অ্যাক্সেস নেই', 'No Access' ) ); ?></h3><p><?php echo esc_html( uturn_t( 'এই পোর্টাল শুধুমাত্র শিক্ষার্থীদের জন্য।', 'This portal is for students only.' ) ); ?></p><p><a class="btn btn-outline btn-sm" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><?php echo esc_html( uturn_t( 'সাইন আউট', 'Sign Out' ) ); ?></a></p></div>
<?php elseif ( ! $edu_lic_ok ) : ?>
<?php $e = eduturn_license_effective( eduturn_license_state(), time() ); echo eduturn_license_lock_html( $e['code'] ); ?>
<?php else : ?>
<div class="quick-grid reveal">
<a class="quick-item" href="<?php echo esc_url( uturn_url( 'results' ) ); ?>"><span class="qicon"><?php echo uturn_icon( 'result' ); ?></span><?php echo esc_html( uturn_t( 'আমার ফলাফল', 'My Results' ) ); ?></a>
<a class="quick-item" href="<?php echo esc_url( $notices_url ); ?>"><span class="qicon"><?php echo uturn_icon( 'bell' ); ?></span><?php echo esc_html( uturn_t( 'নোটিশ', 'Notices' ) ); ?></a>
<a class="quick-item" href="<?php echo esc_url( uturn_url( 'routine' ) ); ?>"><span class="qicon"><?php echo uturn_icon( 'routine' ); ?></span><?php echo esc_html( uturn_t( 'ক্লাস রুটিন', 'Class Routine' ) ); ?></a>
<a class="quick-item" href="<?php echo esc_url( uturn_url( 'downloads' ) ); ?>"><span class="qicon"><?php echo uturn_icon( 'download' ); ?></span><?php echo esc_html( uturn_t( 'ডাউনলোড', 'Downloads' ) ); ?></a>
<a class="quick-item" href="<?php echo esc_url( function_exists( 'uturn_dash_view_url' ) ? uturn_dash_view_url( 'profile' ) : admin_url( 'profile.php' ) ); ?>"><span class="qicon"><?php echo uturn_icon( 'users' ); ?></span><?php echo esc_html( uturn_t( 'প্রোফাইল', 'Profile' ) ); ?></a>
<a class="quick-item" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><span class="qicon"><?php echo uturn_icon( 'logout' ); ?></span><?php echo esc_html( uturn_t( 'সাইন আউট', 'Sign Out' ) ); ?></a>
</div>
<?php
$st = uturn_my_student();
$myres = uturn_my_results( $st );
$myfees = uturn_my_fees( $st );
$att = uturn_my_attendance( $st );
?>
<h2 class="section-title" style="margin-top:32px"><?php echo esc_html( uturn_t( '🎓 আমার ফলাফল', '🎓 My Results' ) ); ?></h2>
<?php if ( $myres ) : ?>
<div class="portal-cards">
<?php foreach ( $myres as $rp ) : $g = function ( $k ) use ( $rp ) { return get_post_meta( $rp->ID, $k, true ); }; $pass = 'fail' !== $g( '_ut_status' ); ?>
<div class="card result-mini">
<div style="display:flex;gap:10px;align-items:center"><?php $uph = function_exists( 'uturn_result_photo_url' ) ? uturn_result_photo_url( $rp->ID ) : ''; if ( $uph ) : ?><img src="<?php echo esc_url( $uph ); ?>" alt="" style="width:52px;height:52px;object-fit:cover;border-radius:50%"><?php endif; ?><div><strong><?php echo esc_html( $g( '_ut_exam_bn' ) ); ?> · <?php echo esc_html( $g( '_ut_year' ) ); ?></strong><p class="muted small"><?php echo esc_html( uturn_t( 'শ্রেণি', 'Class' ) ); ?> <?php echo esc_html( $g( '_ut_class' ) ); ?> · <?php echo esc_html( uturn_t( 'রোল', 'Roll' ) ); ?> <?php echo esc_html( $g( '_ut_roll' ) ); ?> · <?php echo esc_html( uturn_t( 'রেজি', 'Reg' ) ); ?> <?php echo esc_html( $g( '_ut_reg' ) ); ?></p></div></div>
<div class="gpa-strip"><span class="rc-<?php echo $pass ? 'pass' : 'fail'; ?>"><?php echo $pass ? esc_html( uturn_t( 'উত্তীর্ণ', 'Passed' ) ) : esc_html( uturn_t( 'অনুত্তীর্ণ', 'Failed' ) ); ?></span><strong><?php echo esc_html( uturn_t( 'জিপিএ', 'GPA' ) ); ?> <?php echo esc_html( $g( '_ut_gpa' ) ); ?></strong></div>
<details><summary><?php echo esc_html( uturn_t( 'বিষয়ভিত্তিক নম্বর দেখুন', 'View subject-wise marks' ) ); ?></summary><div class="table-wrap"><table class="info-table"><thead><tr><th scope="col"><?php echo esc_html( uturn_t( 'বিষয়', 'Subject' ) ); ?></th><th scope="col"><?php echo esc_html( uturn_t( 'নম্বর', 'Marks' ) ); ?></th><th scope="col"><?php echo esc_html( uturn_t( 'গ্রেড', 'Grade' ) ); ?></th><th scope="col"><?php echo esc_html( uturn_t( 'পয়েন্ট', 'GP' ) ); ?></th></tr></thead><tbody>
<?php foreach ( uturn_lines( $g( '_ut_subjects' ) ) as $ln ) : $sp = array_map( 'trim', explode( '|', $ln ) ); if ( count( $sp ) < 3 ) continue; $mkt = $sp[1]; if ( isset( $sp[3] ) && '' !== $sp[3] && '100' !== $sp[3] ) { $mkt .= '/' . $sp[3]; } ?>
<tr><td><?php echo esc_html( $sp[0] ); ?></td><td><?php echo esc_html( $mkt ); ?></td><td><?php echo esc_html( $sp[2] ); ?></td><td><?php echo esc_html( function_exists( 'uturn_grade_point' ) ? uturn_grade_point( $sp[2] ) : '' ); ?></td></tr>
<?php endforeach; ?>
</tbody></table></div></details>
</div>
<?php endforeach; ?>
</div>
<?php else : ?><p class="muted"><?php echo esc_html( uturn_t( 'এখনও কোনো ফলাফল প্রকাশিত হয়নি। প্রকাশ হলে এখানে দেখতে পাবেন।', 'No results published yet. They will appear here once published.' ) ); ?></p><?php endif; ?>
<h2 class="section-title" style="margin-top:32px"><?php echo esc_html( uturn_t( '💰 আমার ফি', '💰 My Fees' ) ); ?></h2>
<?php if ( $myfees ) : ?>
<div class="table-wrap"><table class="info-table"><thead><tr><th scope="col"><?php echo esc_html( uturn_t( 'মাস', 'Month' ) ); ?></th><th scope="col"><?php echo esc_html( uturn_t( 'পরিমাণ', 'Amount' ) ); ?></th><th scope="col"><?php echo esc_html( uturn_t( 'অবস্থা', 'Status' ) ); ?></th><th scope="col"><?php echo esc_html( uturn_t( 'মাধ্যম', 'Method' ) ); ?></th></tr></thead><tbody>
<?php foreach ( $myfees as $fp ) : $paid = 'paid' === get_post_meta( $fp->ID, '_ut_status', true ); ?>
<tr><td><?php echo esc_html( get_post_meta( $fp->ID, '_ut_month', true ) ); ?></td><td><?php echo esc_html( uturn_bn( get_post_meta( $fp->ID, '_ut_amount', true ) ) ); ?> <?php echo esc_html( uturn_t( 'টাকা', 'BDT' ) ); ?></td><td><span class="rc-<?php echo $paid ? 'pass' : 'fail'; ?>"><?php echo $paid ? esc_html( uturn_t( 'পরিশোধিত', 'Paid' ) ) : esc_html( uturn_t( 'বকেয়া', 'Due' ) ); ?></span></td><td><?php echo esc_html( get_post_meta( $fp->ID, '_ut_method', true ) ? get_post_meta( $fp->ID, '_ut_method', true ) : '—' ); ?></td></tr>
<?php endforeach; ?>
</tbody></table></div>
<?php else : ?><p class="muted"><?php echo esc_html( uturn_t( 'কোনো ফি রেকর্ড পাওয়া যায়নি।', 'No fee records found.' ) ); ?></p><?php endif; ?>
<h2 class="section-title" style="margin-top:32px"><?php echo esc_html( uturn_t( '📅 আমার হাজিরা', '📅 My Attendance' ) ); ?></h2>
<div class="card"><h3 class="mt-0"><?php echo esc_html( uturn_t( 'উপস্থিতির হার', 'Attendance rate' ) ); ?>: <?php echo esc_html( 'en' === uturn_lang() ? $att['pct'] : uturn_bn( $att['pct'] ) ); ?>% <small class="muted">(<?php echo esc_html( 'en' === uturn_lang() ? ( $att['present'] . '/' . $att['total'] . ' days' ) : ( uturn_bn( $att['present'] ) . '/' . uturn_bn( $att['total'] ) . ' দিন' ) ); ?>)</small></h3>
<?php if ( $att['recent'] ) : ?><ul class="att-list"><?php foreach ( $att['recent'] as $a ) : ?><li><span><?php echo esc_html( uturn_bn_date( $a['date'] ) ); ?></span><span class="rc-<?php echo $a['in'] ? 'pass' : 'fail'; ?>"><?php echo $a['in'] ? esc_html( uturn_t( 'উপস্থিত', 'Present' ) ) : esc_html( uturn_t( 'অনুপস্থিত', 'Absent' ) ); ?></span></li><?php endforeach; ?></ul><?php else : ?><p class="muted"><?php echo esc_html( uturn_t( 'এখনও হাজিরা রেকর্ড হয়নি।', 'No attendance recorded yet.' ) ); ?></p><?php endif; ?></div>
<?php if ( function_exists( 'uturn_password_change_form' ) ) { uturn_password_change_form(); } ?>
<?php endif; ?>
</div></section>
</main>
<?php
get_footer();
