<?php
/**
 * EduTurn — Home 17: News preview.
 */
$news_url = get_post_type_archive_link( 'ut_news' ) ? get_post_type_archive_link( 'ut_news' ) : home_url( '/news/' );
$q = new WP_Query( array( 'post_type' => 'ut_news', 'posts_per_page' => 3, 'post_status' => 'publish', 'no_found_rows' => true ) );
if ( ! $q->have_posts() ) {
	return;
}
?>
<section class="section" aria-labelledby="news-h">
  <div class="container">
    <div class="sec-head-split reveal">
      <div class="sec-head"><span class="eyebrow">সর্বশেষ খবর</span>
        <h2 id="news-h">সংবাদ ও আপডেট</h2></div>
      <a class="btn btn-primary" href="<?php echo esc_url( $news_url ); ?>">সব সংবাদ</a>
    </div>
    <div class="news-grid" id="news-preview">
      <?php $i = 0; while ( $q->have_posts() ) : $q->the_post(); $id = get_the_ID();
			$img = uturn_photo_url( $id, '_ut_image', 'ut-card' );
			if ( ! $img ) { $img = UTURN_URI . '/assets/images/hero-campus.jpg'; }
			$ts = get_the_terms( $id, 'ut_news_cat' );
			$cn = ( $ts && ! is_wp_error( $ts ) ) ? $ts[0]->name : 'সংবাদ';
			$datebn = get_post_meta( $id, '_ut_date_bn', true );
			if ( ! $datebn ) { $datebn = uturn_bn_date( get_the_date( 'Y-m-d' ) ); }
			$ex = get_post_meta( $id, '_ut_excerpt', true );
			if ( ! $ex ) { $ex = wp_trim_words( get_the_content(), 20 ); }
			?>
      <article class="news-card<?php echo 0 === $i ? ' featured' : ''; ?> reveal d<?php echo (int) $i; ?>"><div class="nimg"><img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy"></div><div class="nbody"><span class="tag tag-news"><?php echo esc_html( $cn ); ?></span><h3><a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a></h3><p><?php echo esc_html( $ex ); ?></p><div class="news-meta"><span><?php echo esc_html( $datebn ); ?></span><span>·</span><a href="<?php echo esc_url( get_permalink() ); ?>">পড়ুন</a></div></div></article>
      <?php $i++; endwhile; wp_reset_postdata(); ?>
    </div>
  </div>
</section>
