<?php
/**
 * EduTurn — server-rendered footer (1:1 port of the static footer builder).
 */
$notices_url = get_post_type_archive_link( 'ut_notice' ) ? get_post_type_archive_link( 'ut_notice' ) : home_url( '/notices/' );
?>
<div id="footer-root"><footer class="site-footer"><div class="footer-main"><div class="container footer-grid">
<div class="footer-brand"><a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( uturn_opt( 'school_name_bn' ) ); ?>"><span class="brand-mark"><img src="<?php echo esc_url( uturn_logo_url() ); ?>" alt=""></span><span><span class="brand-name"><?php echo esc_html( uturn_opt( 'school_name_bn' ) ); ?></span></span></a>
<p class="mt-1"><?php echo esc_html( uturn_opt( 'footer_about' ) ); ?></p>
<div class="social-row"><a class="social-btn" href="<?php echo esc_url( uturn_opt( 'social_fb', '#' ) ); ?>" aria-label="Facebook" title="Facebook" rel="noopener"><?php echo uturn_icon( 'fb' ); ?></a><a class="social-btn" href="<?php echo esc_url( uturn_opt( 'social_yt', '#' ) ); ?>" aria-label="YouTube" title="YouTube" rel="noopener"><?php echo uturn_icon( 'yt' ); ?></a><a class="social-btn" href="<?php echo esc_url( uturn_url( 'contact' ) ); ?>" aria-label="Contact" title="Contact"><?php echo uturn_icon( 'send' ); ?></a></div></div>
<div><h3><?php echo esc_html( uturn_t( 'গুরুত্বপূর্ণ লিংক', 'Important Links' ) ); ?></h3><?php
	if ( has_nav_menu( 'ut_footer_links' ) ) {
		wp_nav_menu( array( 'theme_location' => 'ut_footer_links', 'container' => false, 'items_wrap' => '<ul class="footer-links">%3$s</ul>' ) );
	} else {
		echo '<ul class="footer-links">'
			. '<li><a href="' . esc_url( uturn_url( 'about' ) ) . '">' . uturn_t( 'আমাদের সম্পর্কে', 'About Us' ) . '</a></li><li><a href="' . esc_url( $notices_url ) . '">' . uturn_t( 'নোটিশ বোর্ড', 'Notice Board' ) . '</a></li><li><a href="' . esc_url( uturn_url( 'results' ) ) . '">' . uturn_t( 'ফলাফল', 'Results' ) . '</a></li><li><a href="' . esc_url( uturn_url( 'routine' ) ) . '">' . uturn_t( 'ক্লাস রুটিন', 'Class Routine' ) . '</a></li><li><a href="' . esc_url( uturn_url( 'downloads' ) ) . '">' . uturn_t( 'ডাউনলোড', 'Downloads' ) . '</a></li><li><a href="' . esc_url( home_url( '/gallery/' ) ) . '">' . uturn_t( 'গ্যালারি', 'Gallery' ) . '</a></li><li><a href="' . esc_url( uturn_url( 'teachers' ) ) . '">' . uturn_t( 'শিক্ষক ও কর্মচারী', 'Teachers & Staff' ) . '</a></li></ul>';
	}
?></div>
<div><h3><?php echo esc_html( uturn_t( 'একাডেমিক', 'Academic' ) ); ?></h3><?php
	if ( has_nav_menu( 'ut_footer_academic' ) ) {
		wp_nav_menu( array( 'theme_location' => 'ut_footer_academic', 'container' => false, 'items_wrap' => '<ul class="footer-links">%3$s</ul>' ) );
	} else {
		echo '<ul class="footer-links">'
			. '<li><a href="' . esc_url( uturn_url( 'academic' ) ) . '">' . uturn_t( 'একাডেমিক তথ্য', 'Academic Info' ) . '</a></li><li><a href="' . esc_url( uturn_url( 'admission' ) ) . '">' . uturn_t( 'ভর্তি তথ্য', 'Admission Info' ) . '</a></li><li><a href="' . esc_url( uturn_url( 'apply' ) ) . '">' . uturn_t( 'অনলাইন আবেদন', 'Apply Online' ) . '</a></li><li><a href="' . esc_url( uturn_url( 'teachers' ) ) . '">' . uturn_t( 'শিক্ষকমণ্ডলী', 'Faculty' ) . '</a></li><li><a href="' . esc_url( home_url( '/events/' ) ) . '">' . uturn_t( 'ইভেন্টসমূহ', 'Events' ) . '</a></li><li><a href="' . esc_url( home_url( '/news/' ) ) . '">' . uturn_t( 'সংবাদ', 'News' ) . '</a></li><li><a href="' . esc_url( uturn_url( 'students-list' ) ) . '">' . uturn_t( 'শিক্ষার্থী তালিকা', 'Student Directory' ) . '</a></li></ul>';
	}
?></div>
<div><h3><?php echo esc_html( uturn_t( 'যোগাযোগ', 'Contact' ) ); ?></h3><ul class="footer-contact">
<li><?php echo uturn_icon( 'pin' ); ?><span><?php echo esc_html( uturn_opt( 'address' ) ); ?></span></li>
<li><?php echo uturn_icon( 'phone' ); ?><span><a href="tel:<?php echo esc_attr( uturn_opt( 'phone_href' ) ); ?>"><?php echo esc_html( uturn_opt( 'phone' ) ); ?></a></span></li>
<li><?php echo uturn_icon( 'mail' ); ?><span><a href="mailto:<?php echo esc_attr( uturn_opt( 'email' ) ); ?>"><?php echo esc_html( uturn_opt( 'email' ) ); ?></a></span></li>
<li><?php echo uturn_icon( 'clock' ); ?><span><?php echo esc_html( uturn_opt( 'hours_short' ) ); ?></span></li>
</ul><a class="btn btn-outline-light btn-sm" href="<?php echo esc_url( uturn_portal_url( 'student' ) ); ?>"><?php echo esc_html( uturn_t( 'পোর্টাল লগইন', 'Portal Login' ) ); ?></a></div>
</div></div>
<div class="footer-bottom"><div class="container"><span>© <?php echo esc_html( 'en' === uturn_lang() ? date_i18n( 'Y' ) : uturn_bn( date_i18n( 'Y' ) ) ); ?> <?php echo esc_html( 'en' === uturn_lang() ? uturn_opt( 'school_name_en' ) : uturn_opt( 'school_name_bn' ) ); ?><?php echo 'en' === uturn_lang() ? '.' : '।'; ?> <?php echo esc_html( uturn_t( 'সর্বস্বত্ব সংরক্ষিত।', 'All rights reserved.' ) ); ?></span><span>Designed &amp; Developed by <a href="https://uturndigital.com.bd" target="_blank" rel="noopener" style="color:inherit">UTurn Digital Solutions</a></span></div></div></footer></div>
<?php get_template_part( 'template-parts/chrome/mobile-cta' ); ?>
<button id="back-top" class="back-top" aria-label="<?php echo esc_attr( uturn_t( 'উপরে যান', 'Back to top' ) ); ?>"><?php echo uturn_icon( 'up' ); ?></button>
<?php wp_footer(); ?>
</body>
</html>
