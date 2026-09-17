<?php
/**
 * EduTurn — Topbar: phone + LIVE geo-location chip + portal links + lang toggle.
 */
$lang        = uturn_lang();
$notices_url = get_post_type_archive_link( 'ut_notice' ) ? get_post_type_archive_link( 'ut_notice' ) : home_url( '/notices/' );
?><div class="topbar"><div class="container">
<div class="topbar-left"><a href="tel:<?php echo esc_attr( uturn_opt( 'phone_href' ) ); ?>"><?php echo uturn_icon( 'phone' ); ?><span><?php echo esc_html( uturn_opt( 'phone' ) ); ?></span></a><?php if ( uturn_opt( 'geo_enabled', 1 ) ) : ?><span class="topbar-geo" id="topbar-geo" hidden><?php echo uturn_icon( 'pin' ); ?><span data-geo-text><?php echo esc_html( uturn_t( 'অবস্থান শনাক্ত হচ্ছে…', 'Detecting location…' ) ); ?></span></span><?php endif; ?></div>
<div class="topbar-right"><a href="<?php echo esc_url( $notices_url ); ?>"><?php echo esc_html( uturn_t( 'নোটিশ', 'Notice' ) ); ?></a><span class="sep">|</span><a href="<?php echo esc_url( uturn_portal_url( 'student' ) ); ?>"><?php echo esc_html( uturn_t( 'শিক্ষার্থী লগইন', 'Student Login' ) ); ?></a><span class="sep">|</span><a href="<?php echo esc_url( uturn_portal_url( 'teacher' ) ); ?>"><?php echo esc_html( uturn_t( 'শিক্ষক লগইন', 'Teacher Login' ) ); ?></a><span class="lang-toggle" role="group" aria-label="Language"><button type="button" data-lang="bn" class="<?php echo 'bn' === $lang ? 'active' : ''; ?>">বাংলা</button><button type="button" data-lang="en" class="<?php echo 'en' === $lang ? 'active' : ''; ?>">English</button></span></div>
</div></div>
