<?php
/**
 * EduTurn — Events archive: upcoming spotlight + full grid.
 */
defined( 'ABSPATH' ) || exit;
get_header();
$today = gmdate( 'Y-m-d' );
$all = new WP_Query( array( 'post_type' => 'ut_event', 'posts_per_page' => -1 ) );
$upcoming = array();
$past = array();
while ( $all->have_posts() ) : $all->the_post();
	$d = get_post_meta( get_the_ID(), '_ut_date', true );
	if ( $d && $d >= $today ) {
		$upcoming[] = get_post();
	} else {
		$past[] = get_post();
	}
endwhile;
wp_reset_postdata();
$bydate = function ( $a, $b ) {
	return strcmp( get_post_meta( $a->ID, '_ut_date', true ), get_post_meta( $b->ID, '_ut_date', true ) );
};
usort( $upcoming, $bydate );
$card = function ( $p ) {
	$id = $p->ID;
	$cover = uturn_img( get_post_meta( $id, '_ut_image', true ), UTURN_URI . '/assets/images/event-science.jpg' );
	?>
	<article class="news-card">
		<a class="nimg" href="<?php echo esc_url( get_permalink( $id ) ); ?>"><img src="<?php echo esc_url( $cover ); ?>" alt="<?php echo esc_attr( $p->post_title ); ?>" loading="lazy"><span class="date-badge"><?php echo esc_html( get_post_meta( $id, '_ut_date_bn', true ) ); ?></span></a>
		<div class="nbody"><h3><a href="<?php echo esc_url( get_permalink( $id ) ); ?>"><?php echo esc_html( $p->post_title ); ?></a></h3><p>📍 <?php echo esc_html( get_post_meta( $id, '_ut_location', true ) ); ?> · 🕒 <?php echo esc_html( get_post_meta( $id, '_ut_time', true ) ); ?></p><p><?php echo esc_html( get_post_meta( $id, '_ut_excerpt', true ) ); ?></p></div>
	</article>
	<?php
};
?>

<main id="main"><div class="container">
  <nav class="breadcrumb" aria-label="<?php echo esc_attr( uturn_t( 'ব্রেডক্রাম্ব', 'Breadcrumb' ) ); ?>"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( uturn_t( 'হোম', 'Home' ) ); ?></a><span>›</span><span><?php echo esc_html( uturn_t( 'ইভেন্ট', 'Events' ) ); ?></span></nav>
  <div class="page-head">
    <h1><?php echo esc_html( uturn_t( 'ইভেন্টসমূহ', 'Events' ) ); ?></h1>
    <p class="muted"><?php echo esc_html( uturn_t( 'আসন্ন অনুষ্ঠান ও বিগত ইভেন্টের আর্কাইভ', 'Upcoming programs and past event archive' ) ); ?></p>
  </div>
  <?php if ( $upcoming ) : ?>
    <h2 class="section-title">🔔 <?php echo esc_html( uturn_t( 'আসন্ন ইভেন্ট', 'Upcoming Events' ) ); ?></h2>
    <div class="news-grid news-archive">
      <?php foreach ( $upcoming as $p ) { $card( $p ); } ?>
    </div>
  <?php endif; ?>
  <?php if ( $past ) : ?>
    <h2 class="section-title" style="margin-top:32px">📜 <?php echo esc_html( uturn_t( 'সকল ইভেন্ট', 'All Events' ) ); ?></h2>
    <div class="news-grid news-archive">
      <?php foreach ( $past as $p ) { $card( $p ); } ?>
    </div>
  <?php endif; ?>
  <?php if ( ! $upcoming && ! $past ) : ?><p class="muted"><?php echo esc_html( uturn_t( 'কোনো ইভেন্ট পাওয়া যায়নি।', 'No events found.' ) ); ?></p><?php endif; ?>
</div></main>
<?php get_footer(); ?>
