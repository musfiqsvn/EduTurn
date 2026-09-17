<?php
/**
 * EduTurn — Identity row (auto-fit brand) + sticky nav row + notice ticker.
 */
$name = uturn_t( uturn_opt( 'school_name_bn' ), uturn_opt( 'school_name_en' ) );
?><header class="site-header"><div class="container identity-row">
<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( uturn_opt( 'school_name_bn' ) ); ?>"><span class="brand-mark"><img src="<?php echo esc_url( uturn_logo_url() ); ?>" alt=""></span><span class="brand-name"><?php echo esc_html( $name ); ?></span></a>
<div class="header-actions"><button class="icon-btn" id="search-btn" aria-label="Search"><?php echo uturn_icon( 'search' ); ?></button><a class="btn btn-primary header-cta" href="<?php echo esc_url( uturn_url( 'apply' ) ); ?>"><?php echo esc_html( uturn_t( 'অনলাইনে ভর্তি', 'Apply Online' ) ); ?></a><button class="icon-btn hamburger" id="menu-btn" aria-label="Menu" aria-expanded="false"><?php echo uturn_icon( 'menu' ); ?></button></div>
</div>
<nav class="nav-row" aria-label="Primary"><div class="container"><ul class="main-nav"><?php
	if ( has_nav_menu( 'ut_primary' ) ) {
		wp_nav_menu( array( 'theme_location' => 'ut_primary', 'container' => false, 'items_wrap' => '%3$s', 'walker' => new UTurn_Nav_Walker() ) );
	} else {
		echo uturn_fallback_menu( false ); // phpcs:ignore
	}
?></ul></div></nav>
<?php get_template_part( 'template-parts/chrome/ticker' ); ?>
</header>
