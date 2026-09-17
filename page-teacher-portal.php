<?php
/**
 * Template Name: Teacher Portal
 * Phase-1 portal: role-gated quick dashboard (full SMS dashboards = Phase 2).
 */
get_header();
$edu_lic_ok = function_exists( 'eduturn_license_can' ) && eduturn_license_can( 'erp' );
$user = wp_get_current_user();
$ok = is_user_logged_in() && in_array( 'uturn_teacher', (array) $user->roles, true );
$notices_url = get_post_type_archive_link( 'ut_notice' ) ? get_post_type_archive_link( 'ut_notice' ) : home_url( '/notices/' );
?>
<main id="main">
<section class="page-hero"><div class="container page-hero-inner">
<ul class="breadcrumbs"><li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( uturn_t( 'হোম', 'Home' ) ); ?></a></li><li><?php echo esc_html( uturn_t( 'শিক্ষক পোর্টাল', 'Teacher Portal' ) ); ?></li></ul>
<h1><?php echo esc_html( uturn_t( 'শিক্ষক পোর্টাল', 'Teacher Portal' ) ); ?></h1><p><?php echo $ok ? esc_html( uturn_t( 'স্বাগতম, ', 'Welcome, ' ) ) . esc_html( $user->display_name ) . '!' : esc_html( uturn_t( 'অনুগ্রহ করে আপনার শিক্ষক অ্যাকাউন্টে সাইন ইন করুন।', 'Please sign in with your teacher account.' ) ); ?></p>
</div></section>
<section class="section"><div class="container">
<?php if ( ! is_user_logged_in() ) : ?>
<?php uturn_portal_login_card( uturn_t( 'শিক্ষক সাইন ইন', 'Teacher Sign In' ), uturn_t( 'হাজিরা, শিক্ষার্থী আর ক্লাস — প্রতিদিনের কাজ সহজে।', 'Attendance, students and classes — daily work made easy.' ), 'teacher' ); ?>
<?php elseif ( ! $ok ) : ?>
<div class="empty-state"><h3><?php echo esc_html( uturn_t( 'অ্যাক্সেস নেই', 'No Access' ) ); ?></h3><p><?php echo esc_html( uturn_t( 'এই পোর্টাল শুধুমাত্র শিক্ষকদের জন্য।', 'This portal is for teachers only.' ) ); ?></p><p><a class="btn btn-outline btn-sm" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><?php echo esc_html( uturn_t( 'সাইন আউট', 'Sign Out' ) ); ?></a></p></div>
<?php elseif ( ! $edu_lic_ok ) : ?>
<?php $e = eduturn_license_effective( eduturn_license_state(), time() ); echo eduturn_license_lock_html( $e['code'] ); ?>
<?php else : ?>
<div class="quick-grid reveal">
<a class="quick-item" href="<?php echo esc_url( uturn_url( 'routine' ) ); ?>"><span class="qicon"><?php echo uturn_icon( 'routine' ); ?></span><?php echo esc_html( uturn_t( 'ক্লাস রুটিন', 'Class Routine' ) ); ?></a>
<a class="quick-item" href="<?php echo esc_url( $notices_url ); ?>"><span class="qicon"><?php echo uturn_icon( 'bell' ); ?></span><?php echo esc_html( uturn_t( 'নোটিশ', 'Notices' ) ); ?></a>
<a class="quick-item" href="<?php echo esc_url( uturn_url( 'students-list' ) ); ?>"><span class="qicon"><?php echo uturn_icon( 'users' ); ?></span><?php echo esc_html( uturn_t( 'শিক্ষার্থী তালিকা', 'Student Directory' ) ); ?></a>
<a class="quick-item" href="<?php echo esc_url( uturn_url( 'downloads' ) ); ?>"><span class="qicon"><?php echo uturn_icon( 'download' ); ?></span><?php echo esc_html( uturn_t( 'ডাউনলোড', 'Downloads' ) ); ?></a>
<a class="quick-item" href="<?php echo esc_url( function_exists( 'uturn_dash_view_url' ) ? uturn_dash_view_url( 'profile' ) : admin_url( 'profile.php' ) ); ?>"><span class="qicon"><?php echo uturn_icon( 'cap' ); ?></span><?php echo esc_html( uturn_t( 'প্রোফাইল', 'Profile' ) ); ?></a>
<a class="quick-item" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><span class="qicon"><?php echo uturn_icon( 'logout' ); ?></span><?php echo esc_html( uturn_t( 'সাইন আউট', 'Sign Out' ) ); ?></a>
</div>
<h2 class="section-title" style="margin-top:32px"><?php echo esc_html( uturn_t( '📅 হাজিরা নিন', '📅 Take Attendance' ) ); ?></h2>
<?php
$cls_terms = function_exists( 'uturn_class_terms' ) ? uturn_class_terms() : get_terms( array( 'taxonomy' => 'ut_class', 'hide_empty' => false ) );
$att_class = isset( $_GET['att_class'] ) ? sanitize_text_field( wp_unslash( $_GET['att_class'] ) ) : '';
$att_date = isset( $_GET['att_date'] ) ? sanitize_text_field( wp_unslash( $_GET['att_date'] ) ) : gmdate( 'Y-m-d' );
if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $att_date ) ) $att_date = gmdate( 'Y-m-d' );
?>
<div class="card">
<form method="get" action="<?php echo esc_url( get_permalink() ); ?>" class="routine-controls">
<div class="field"><label for="att-class-sel"><?php echo esc_html( uturn_t( 'শ্রেণি', 'Class' ) ); ?></label><select id="att-class-sel" name="att_class" onchange="this.form.submit()"><option value=""><?php echo esc_html( uturn_t( 'নির্বাচন করুন', 'Select' ) ); ?></option>
<?php foreach ( $cls_terms as $t ) : ?><option value="<?php echo esc_attr( $t->name ); ?>"<?php selected( $att_class, $t->name ); ?>><?php echo esc_html( $t->name ); ?> (<?php echo esc_html( uturn_bn( $t->count ) ); ?>)</option><?php endforeach; ?>
</select></div>
<div class="field"><label for="att-date-sel"><?php echo esc_html( uturn_t( 'তারিখ', 'Date' ) ); ?></label><input type="date" id="att-date-sel" name="att_date" value="<?php echo esc_attr( $att_date ); ?>" onchange="this.form.submit()"></div>
</form>
<?php if ( $att_class ) : $roster = uturn_class_students( $att_class ); $aid = uturn_find_attendance( $att_class, $att_date ); $marked = $aid ? array_filter( array_map( 'trim', explode( ',', (string) get_post_meta( $aid, '_ut_present', true ) ) ) ) : array(); ?>
<?php if ( $roster ) : ?>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
<input type="hidden" name="action" value="uturn_attendance">
<?php wp_nonce_field( 'uturn_attendance' ); ?>
<input type="hidden" name="att-class" value="<?php echo esc_attr( $att_class ); ?>">
<input type="hidden" name="att-date" value="<?php echo esc_attr( $att_date ); ?>">
<p class="muted"><?php echo esc_html( $att_class ); ?> · <?php echo esc_html( uturn_bn_date( $att_date ) ); ?> · <?php echo esc_html( uturn_t( 'মোট', 'Total' ) ); ?> <?php echo esc_html( 'en' === uturn_lang() ? count( $roster ) : uturn_bn( count( $roster ) ) ); ?> <?php echo esc_html( uturn_t( 'জন', 'students' ) ); ?> <?php echo $aid ? '· <span class="rc-pass">' . esc_html( uturn_t( 'ইতিমধ্যে নেওয়া হয়েছে (আপডেট করা যাবে)', 'Already taken (can update)' ) ) . '</span>' : ''; ?></p>
<div class="table-wrap"><table class="info-table"><thead><tr><th scope="col"><input type="checkbox" id="attAll" checked></th><th scope="col"><?php echo esc_html( uturn_t( 'নাম', 'Name' ) ); ?></th><th scope="col"><?php echo esc_html( uturn_t( 'রোল', 'Roll' ) ); ?></th><th scope="col"><?php echo esc_html( uturn_t( 'শাখা', 'Section' ) ); ?></th></tr></thead><tbody>
<?php foreach ( $roster as $sp ) : $on = $aid ? in_array( (string) $sp->ID, $marked, true ) : true; ?>
<tr><td><input type="checkbox" class="att-cb" name="present[]" value="<?php echo (int) $sp->ID; ?>"<?php checked( $on ); ?>></td><td><?php echo esc_html( $sp->post_title ); ?></td><td><?php echo esc_html( get_post_meta( $sp->ID, '_ut_roll', true ) ); ?></td><td><?php echo esc_html( get_post_meta( $sp->ID, '_ut_section', true ) ); ?></td></tr>
<?php endforeach; ?>
</tbody></table></div>
<p><button class="btn" type="submit"><?php echo esc_html( uturn_t( '✅ হাজিরা সংরক্ষণ করুন', '✅ Save Attendance' ) ); ?></button></p>
</form>
<script>(function(){var a=document.getElementById('attAll');if(a)a.addEventListener('change',function(){document.querySelectorAll('.att-cb').forEach(function(c){c.checked=a.checked;});});})();</script>
<?php else : ?><p class="muted"><?php echo esc_html( uturn_t( 'এই শ্রেণিতে কোনো শিক্ষার্থী নেই।', 'No students in this class.' ) ); ?></p><?php endif; ?>
<?php else : ?><p class="muted"><?php echo esc_html( uturn_t( 'উপরে শ্রেণি নির্বাচন করুন।', 'Select a class above.' ) ); ?></p><?php endif; ?>
</div>
<h2 class="section-title" style="margin-top:32px"><?php echo esc_html( uturn_t( '📊 শ্রেণিভিত্তিক শিক্ষার্থী', '📊 Students by Class' ) ); ?></h2>
<div class="table-wrap"><table class="info-table"><thead><tr><th scope="col"><?php echo esc_html( uturn_t( 'শ্রেণি', 'Class' ) ); ?></th><th scope="col"><?php echo esc_html( uturn_t( 'শিক্ষার্থী সংখ্যা', 'Students' ) ); ?></th></tr></thead><tbody>
<?php foreach ( $cls_terms as $t ) : ?><tr><td><?php echo esc_html( $t->name ); ?></td><td><?php echo esc_html( 'en' === uturn_lang() ? $t->count : uturn_bn( $t->count ) ); ?> <?php echo esc_html( uturn_t( 'জন', 'students' ) ); ?></td></tr><?php endforeach; ?>
</tbody></table></div>
<?php if ( function_exists( 'uturn_password_change_form' ) ) { uturn_password_change_form(); } ?>
<?php endif; ?>
</div></section>
</main>
<?php
get_footer();
