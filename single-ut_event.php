<?php
/**
 * EduTurn — Event single: cover + details + gallery + JSON-LD.
 */
defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) : the_post();
	$id = get_the_ID();
	$cover = uturn_img( get_post_meta( $id, '_ut_image', true ), UTURN_URI . '/assets/images/event-science.jpg' );
	$terms = get_the_terms( $id, 'ut_event_cat' );
	$cname = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';
	$gal = array( UTURN_URI . '/assets/images/event-sports.jpg', UTURN_URI . '/assets/images/event-culture.jpg', UTURN_URI . '/assets/images/campus.jpg' );
	?>

<main id="main"><div class="container">
  <nav class="breadcrumb" aria-label="<?php echo esc_attr( uturn_t( 'ব্রেডক্রাম্ব', 'Breadcrumb' ) ); ?>"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( uturn_t( 'হোম', 'Home' ) ); ?></a><span>›</span><a href="<?php echo esc_url( get_post_type_archive_link( 'ut_event' ) ); ?>"><?php echo esc_html( uturn_t( 'ইভেন্ট', 'Events' ) ); ?></a><span>›</span><span><?php the_title(); ?></span></nav>
  <article class="single-layout">
    <div class="single-main">
      <img class="single-cover" src="<?php echo esc_url( $cover ); ?>" alt="<?php the_title_attribute(); ?>" fetchpriority="high">
      <?php if ( $cname ) : ?><span class="badge-soft"><?php echo esc_html( $cname ); ?></span><?php endif; ?>
      <h1><?php the_title(); ?></h1>
      <p class="muted">📅 <?php echo esc_html( get_post_meta( $id, '_ut_date_bn', true ) ); ?> · 🕒 <?php echo esc_html( get_post_meta( $id, '_ut_time', true ) ); ?> · 📍 <?php echo esc_html( get_post_meta( $id, '_ut_location', true ) ); ?></p>
      <div class="prose"><?php the_content(); ?></div>
      <h3>📷 <?php echo esc_html( uturn_t( 'গ্যালারি', 'Gallery' ) ); ?></h3>
      <div class="mini-gallery">
        <?php foreach ( $gal as $g ) : ?>
          <a href="<?php echo esc_url( $g ); ?>" target="_blank" rel="noopener"><img src="<?php echo esc_url( $g ); ?>" alt="<?php echo esc_attr( uturn_t( 'ইভেন্ট ছবি', 'Event photo' ) ); ?>" loading="lazy"></a>
        <?php endforeach; ?>
      </div>
    </div>
    <aside class="single-side">
      <div class="help-card">
        <h3>📌 ইভেন্ট তথ্য</h3>
        <p><strong><?php echo esc_html( uturn_t( 'তারিখ:', 'Date:' ) ); ?></strong> <?php echo esc_html( get_post_meta( $id, '_ut_date_bn', true ) ); ?></p>
        <p><strong><?php echo esc_html( uturn_t( 'সময়:', 'Time:' ) ); ?></strong> <?php echo esc_html( get_post_meta( $id, '_ut_time', true ) ); ?></p>
        <p><strong><?php echo esc_html( uturn_t( 'স্থান:', 'Venue:' ) ); ?></strong> <?php echo esc_html( get_post_meta( $id, '_ut_location', true ) ); ?></p>
        <a class="btn btn-block" href="<?php echo esc_url( uturn_url( 'contact' ) ); ?>"><?php echo esc_html( uturn_t( 'অংশগ্রহণে যোগাযোগ', 'Contact to Join' ) ); ?></a>
      </div>
      <div class="card">
        <h3>🔗 <?php echo esc_html( uturn_t( 'অন্যান্য ইভেন্ট', 'Other Events' ) ); ?></h3>
        <ul class="link-list">
          <?php
          $others = new WP_Query( array( 'post_type' => 'ut_event', 'posts_per_page' => 4, 'post__not_in' => array( $id ) ) );
          while ( $others->have_posts() ) : $others->the_post();
            ?>
            <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
          <?php endwhile; wp_reset_postdata(); ?>
        </ul>
      </div>
    </aside>
  </article>
</div></main>

<script type="application/ld+json">
<?php
echo wp_json_encode(
	array(
		'@context' => 'https://schema.org', '@type' => 'Event', 'name' => get_the_title(),
		'startDate' => get_post_meta( $id, '_ut_date', true ),
		'location' => array( '@type' => 'Place', 'name' => get_post_meta( $id, '_ut_location', true ) ),
		'image' => $cover, 'description' => get_post_meta( $id, '_ut_excerpt', true ),
	)
);
?>
</script>
<?php endwhile; ?>
<?php get_footer(); ?>
