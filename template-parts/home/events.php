<?php
/**
 * EduTurn — Home 11: Events carousel.
 */
$events_url = get_post_type_archive_link( 'ut_event' ) ? get_post_type_archive_link( 'ut_event' ) : home_url( '/events/' );
$q = new WP_Query( array( 'post_type' => 'ut_event', 'posts_per_page' => 6, 'post_status' => 'publish', 'no_found_rows' => true ) );
?>
<section class="section" aria-labelledby="events-h">
  <div class="container">
    <div class="sec-head-split reveal">
      <div class="sec-head"><span class="eyebrow">আসন্ন আয়োজন</span>
        <h2 id="events-h">ইভেন্টসমূহ</h2></div>
      <a class="btn btn-outline" href="<?php echo esc_url( $events_url ); ?>">সব ইভেন্ট</a>
    </div>
    <div class="carousel-wrap reveal">
      <button class="car-btn prev" data-car-prev aria-label="আগের"><?php echo uturn_icon( 'left' ); ?></button>
      <div class="carousel" data-carousel id="events-car">
        <?php while ( $q->have_posts() ) : $q->the_post(); $id = get_the_ID();
			$img = uturn_photo_url( $id, '_ut_image', 'ut-card' );
			if ( ! $img ) { $img = UTURN_URI . '/assets/images/event-culture.jpg'; }
			$datebn = get_post_meta( $id, '_ut_date_bn', true );
			if ( ! $datebn ) { $datebn = uturn_bn_date( get_the_date( 'Y-m-d' ) ); }
			$ex = get_post_meta( $id, '_ut_excerpt', true );
			if ( ! $ex ) { $ex = wp_trim_words( get_the_content(), 20 ); }
			?>
        <article class="event-card"><div class="eimg"><img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy"><span class="edate"><?php echo esc_html( $datebn ); ?></span></div><div class="ebody"><span class="tag tag-event">ইভেন্ট</span><h3 style="margin-top:.5rem"><?php echo esc_html( get_the_title() ); ?></h3><p><?php echo esc_html( $ex ); ?></p><span class="eloc"><?php echo uturn_icon( 'pin' ); ?><?php echo esc_html( get_post_meta( $id, '_ut_location', true ) ); ?> · <?php echo esc_html( get_post_meta( $id, '_ut_time', true ) ); ?></span><a class="btn btn-outline btn-sm" style="align-self:flex-start" href="<?php echo esc_url( get_permalink() ); ?>">বিস্তারিত</a></div></article>
        <?php endwhile; wp_reset_postdata(); ?>
      </div>
      <button class="car-btn next" data-car-next aria-label="পরের"><?php echo uturn_icon( 'right' ); ?></button>
    </div>
  </div>
</section>
