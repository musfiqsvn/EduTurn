<?php
/**
 * EduTurn — Mobile drawer (server-rendered; JS binds, never rebuilds).
 */
?><div class="drawer-backdrop" id="drawer-bg"></div>
<aside class="drawer" id="drawer" aria-label="Mobile navigation">
<div class="drawer-head"><img src="<?php echo esc_url( uturn_logo_url() ); ?>" alt="" style="width:44px;height:44px;border-radius:10px;display:block"><button class="icon-btn" id="drawer-close" aria-label="Close" style="background:rgba(255,255,255,.12);border-color:transparent;color:#fff"><?php echo uturn_icon( 'close' ); ?></button></div>
<div class="drawer-body"><ul class="mnav"><?php
	if ( has_nav_menu( 'ut_primary' ) ) {
		wp_nav_menu( array( 'theme_location' => 'ut_primary', 'container' => false, 'items_wrap' => '%3$s', 'walker' => new UTurn_Drawer_Walker() ) );
	} else {
		echo uturn_fallback_menu( true ); // phpcs:ignore
	}
?></ul></div>
<div class="drawer-foot"><a class="btn btn-primary btn-block" href="<?php echo esc_url( uturn_url( 'apply' ) ); ?>"><?php echo esc_html( uturn_t( 'অনলাইনে ভর্তি', 'Apply Online' ) ); ?></a><div style="display:flex;gap:.5rem"><a class="btn btn-outline btn-sm" style="flex:1" href="<?php echo esc_url( uturn_portal_url( 'student' ) ); ?>"><?php echo esc_html( uturn_t( 'শিক্ষার্থী লগইন', 'Student Login' ) ); ?></a><a class="btn btn-outline btn-sm" style="flex:1" href="<?php echo esc_url( uturn_portal_url( 'teacher' ) ); ?>"><?php echo esc_html( uturn_t( 'শিক্ষক লগইন', 'Teacher Login' ) ); ?></a></div></div>
</aside>
