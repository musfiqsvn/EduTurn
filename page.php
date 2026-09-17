<?php
/**
 * EduTurn — Generic page (inner-page detail ports plug in as page-{slug}.php).
 */
get_header();
the_post();
?>
<main id="main">
<section class="page-hero"><div class="container page-hero-inner">
<ul class="breadcrumbs"><li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( uturn_t( 'হোম', 'Home' ) ); ?></a></li><li><?php the_title(); ?></li></ul>
<h1><?php the_title(); ?></h1>
</div></section>
<section class="section"><div class="container">
<article class="article-card"><div class="article-body"><div class="prose">
<?php
if ( trim( get_the_content() ) ) {
	the_content();
} else {
	echo '<div class="empty-state"><h3>' . esc_html( uturn_t( 'শীঘ্রই আসছে', 'Coming Soon' ) ) . '</h3><p>' . esc_html( uturn_t( 'এই পেজের বিস্তারিত কনটেন্ট প্রস্তুত হচ্ছে। জরুরি তথ্যের জন্য নোটিশ বোর্ড দেখুন।', 'Detailed content for this page is being prepared. See the notice board for urgent info.' ) ) . '</p><p><a class="btn btn-primary btn-sm" href="' . esc_url( get_post_type_archive_link( 'ut_notice' ) ? get_post_type_archive_link( 'ut_notice' ) : home_url( '/notices/' ) ) . '">' . esc_html( uturn_t( 'নোটিশ বোর্ড', 'Notice Board' ) ) . '</a></p></div>';
}
?>
</div></div></article>
</div></section>
</main>
<?php
get_footer();
