<?php
/**
 * EduTurn — Home 15: Gallery preview (lightbox-bound figures).
 */
$gallery_url = get_post_type_archive_link( 'ut_album' ) ? get_post_type_archive_link( 'ut_album' ) : home_url( '/gallery/' );
$figs = array();
$q = new WP_Query( array( 'post_type' => 'ut_album', 'posts_per_page' => 6, 'post_status' => 'publish', 'no_found_rows' => true ) );
while ( $q->have_posts() && count( $figs ) < 8 ) {
	$q->the_post();
	$id    = get_the_ID();
	$lines = array();
	$cover = get_post_meta( $id, '_ut_cover', true );
	if ( $cover ) {
		$lines[] = $cover;
	}
	foreach ( preg_split( '/\r?\n/', (string) get_post_meta( $id, '_ut_photos', true ) ) as $ln ) {
		$ln = trim( $ln );
		if ( '' !== $ln ) {
			$lines[] = $ln;
		}
	}
	foreach ( $lines as $ln ) {
		if ( count( $figs ) >= 8 ) {
			break;
		}
		if ( is_numeric( $ln ) ) {
			$src = wp_get_attachment_image_url( (int) $ln, 'ut-card' );
		} elseif ( 0 === strpos( $ln, 'assets/' ) ) {
			$src = UTURN_URI . '/' . $ln;
		} else {
			$src = $ln;
		}
		if ( $src ) {
			$figs[] = array( $src, get_the_title() );
		}
	}
}
wp_reset_postdata();
if ( ! $figs ) {
	$fb = array(
		array( 'hero-campus.jpg', 'ক্যাম্পাস' ), array( 'about-main.jpg', 'শ্রেণিকক্ষ' ),
		array( 'event-sports.jpg', 'ক্রীড়া' ), array( 'event-culture.jpg', 'সাংস্কৃতিক অনুষ্ঠান' ),
		array( 'facility-science.jpg', 'বিজ্ঞান ল্যাব' ), array( 'facility-library.jpg', 'লাইব্রেরি' ),
		array( 'facility-playground.jpg', 'খেলার মাঠ' ), array( 'about-small.jpg', 'পাঠাভ্যাস' ),
	);
	foreach ( $fb as $f ) {
		$figs[] = array( UTURN_URI . '/assets/images/' . $f[0], $f[1] );
	}
}
?>
<section class="section" aria-labelledby="gal-h">
  <div class="container">
    <div class="sec-head-split reveal">
      <div class="sec-head"><span class="eyebrow">ক্যাম্পাস ঝলক</span>
        <h2 id="gal-h">গ্যালারি</h2></div>
      <a class="btn btn-outline" href="<?php echo esc_url( $gallery_url ); ?>">সব ছবি দেখুন</a>
    </div>
    <div class="masonry reveal" id="gallery-preview">
      <?php foreach ( $figs as $g ) : ?>
      <figure data-lightbox="home" data-caption="<?php echo esc_attr( $g[1] ); ?>" tabindex="0" role="button" aria-label="<?php echo esc_attr( $g[1] ); ?> বড় করে দেখুন"><img src="<?php echo esc_url( $g[0] ); ?>" alt="<?php echo esc_attr( $g[1] ); ?>" loading="lazy"><figcaption><?php echo esc_html( $g[1] ); ?></figcaption></figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<script>
(function () {
  document.querySelectorAll('#gallery-preview figure').forEach(function (f) {
    f.addEventListener('keydown', function (e) { if ('Enter' === e.key) f.click(); });
  });
})();
</script>
