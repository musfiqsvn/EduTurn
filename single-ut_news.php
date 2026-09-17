<?php
/**
 * EduTurn — News single: article + recent sidebar + JSON-LD.
 */
defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) : the_post();
	$id = get_the_ID();
	$cover = uturn_img( get_post_meta( $id, '_ut_image', true ), UTURN_URI . '/assets/images/news-1.jpg' );
	$terms = get_the_terms( $id, 'ut_news_cat' );
	$cname = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';
	?>

<main id="main"><div class="container">
  <nav class="breadcrumb" aria-label="<?php echo esc_attr( uturn_t( 'ব্রেডক্রাম্ব', 'Breadcrumb' ) ); ?>"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( uturn_t( 'হোম', 'Home' ) ); ?></a><span>›</span><a href="<?php echo esc_url( get_post_type_archive_link( 'ut_news' ) ); ?>"><?php echo esc_html( uturn_t( 'সংবাদ', 'News' ) ); ?></a><span>›</span><span><?php the_title(); ?></span></nav>
  <article class="single-layout">
    <div class="single-main print-doc">
      <div class="print-head"><b><?php echo esc_html( 'en' === uturn_lang() ? uturn_opt( 'school_name_en' ) : uturn_opt( 'school_name_bn' ) ); ?></b><span><?php echo esc_html( uturn_opt( 'address' ) ); ?></span><i><?php echo esc_html( uturn_t( 'সংবাদ বিজ্ঞপ্তি', 'News Release' ) ); ?></i></div>
      <img class="single-cover" src="<?php echo esc_url( $cover ); ?>" alt="<?php the_title_attribute(); ?>" fetchpriority="high">
      <?php if ( $cname ) : ?><span class="badge-soft"><?php echo esc_html( $cname ); ?></span><?php endif; ?>
      <h1><?php the_title(); ?></h1>
      <p class="muted">📅 <?php echo esc_html( get_post_meta( $id, '_ut_date_bn', true ) ); ?> · ✍️ <?php echo esc_html( 'en' === uturn_lang() ? uturn_opt( 'school_name_en' ) : uturn_opt( 'school_name_bn' ) ); ?></p>
      <div class="prose"><?php the_content(); ?></div>
      <p><button class="btn btn-outline" onclick="window.print()">🖨️ <?php echo esc_html( uturn_t( 'প্রিন্ট', 'Print' ) ); ?></button> <a class="btn btn-outline" href="<?php echo esc_url( get_post_type_archive_link( 'ut_news' ) ); ?>"><?php echo esc_html( uturn_t( '← সব সংবাদ', '← All News' ) ); ?></a></p>
    </div>
    <aside class="single-side">
      <div class="card">
        <h3>📰 <?php echo esc_html( uturn_t( 'সাম্প্রতিক সংবাদ', 'Recent News' ) ); ?></h3>
        <ul class="link-list">
          <?php
          $recent = new WP_Query( array( 'post_type' => 'ut_news', 'posts_per_page' => 5, 'post__not_in' => array( $id ) ) );
          while ( $recent->have_posts() ) : $recent->the_post();
            ?>
            <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
          <?php endwhile; wp_reset_postdata(); ?>
        </ul>
      </div>
      <div class="help-card">
        <h3>🔗 <?php echo esc_html( uturn_t( 'দ্রুত লিংক', 'Quick Links' ) ); ?></h3>
        <ul class="link-list">
          <li><a href="<?php echo esc_url( uturn_url( 'notices' ) ); ?>"><?php echo esc_html( uturn_t( 'নোটিশ বোর্ড', 'Notice Board' ) ); ?></a></li>
          <li><a href="<?php echo esc_url( get_post_type_archive_link( 'ut_event' ) ); ?>"><?php echo esc_html( uturn_t( 'ইভেন্টসমূহ', 'Events' ) ); ?></a></li>
          <li><a href="<?php echo esc_url( uturn_url( 'admission' ) ); ?>"><?php echo esc_html( uturn_t( 'ভর্তি তথ্য', 'Admission Info' ) ); ?></a></li>
          <li><a href="<?php echo esc_url( uturn_url( 'results' ) ); ?>"><?php echo esc_html( uturn_t( 'ফলাফল', 'Results' ) ); ?></a></li>
        </ul>
      </div>
    </aside>
  </article>
</div></main>

<script type="application/ld+json">
<?php
echo wp_json_encode(
	array(
		'@context' => 'https://schema.org', '@type' => 'NewsArticle',
		'headline' => get_the_title(), 'image' => $cover,
		'datePublished' => get_the_date( 'c' ), 'description' => get_post_meta( $id, '_ut_excerpt', true ),
		'author' => array( '@type' => 'Organization', 'name' => uturn_opt( 'school_name_bn' ) ),
	)
);
?>
</script>
<?php endwhile; ?>
<?php get_footer(); ?>
