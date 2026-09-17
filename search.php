<?php
/**
 * EduTurn — Search results.
 */
get_header();
?>
<main id="main">
<section class="page-hero"><div class="container page-hero-inner">
<ul class="breadcrumbs"><li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( uturn_t( 'হোম', 'Home' ) ); ?></a></li><li><?php echo esc_html( uturn_t( 'অনুসন্ধান', 'Search' ) ); ?></li></ul>
<h1><?php echo esc_html( uturn_t( 'অনুসন্ধান:', 'Search:' ) ); ?> <?php echo esc_html( get_search_query() ); ?></h1>
</div></section>
<section class="section"><div class="container">
<div class="notice-list">
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
<article class="notice-card"><div><h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3><p><?php echo esc_html( wp_trim_words( get_the_content(), 24 ) ); ?></p></div><a class="btn btn-outline btn-sm" href="<?php the_permalink(); ?>"><?php echo esc_html( uturn_t( 'বিস্তারিত', 'Details' ) ); ?></a></article>
<?php endwhile; else : ?>
<div class="empty-state"><h3><?php echo esc_html( uturn_t( 'কোনো ফলাফল পাওয়া যায়নি', 'No results found' ) ); ?></h3><p><?php echo esc_html( uturn_t( 'অন্য কীওয়ার্ড দিয়ে চেষ্টা করুন', 'Try a different keyword' ) ); ?></p></div>
<?php endif; ?>
</div>
</div></section>
</main>
<?php
get_footer();
