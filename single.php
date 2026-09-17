<?php
/**
 * EduTurn — Generic single (events/news/teacher detail ports override).
 */
get_header();
the_post();
?>
<main id="main">
<section class="page-hero"><div class="container page-hero-inner">
<ul class="breadcrumbs"><li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( uturn_t( 'হোম', 'Home' ) ); ?></a></li><li><?php the_title(); ?></li></ul>
<h1><?php the_title(); ?></h1><p><?php echo esc_html( uturn_bn_date( get_the_date( 'Y-m-d' ) ) ); ?></p>
</div></section>
<section class="section"><div class="container">
<article class="article-card"><div class="article-body"><div class="prose"><?php the_content(); ?></div></div></article>
</div></section>
</main>
<?php
get_footer();
