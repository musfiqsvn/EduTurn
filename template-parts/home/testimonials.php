<?php
/**
 * EduTurn — Home 16: Testimonials (tabs + carousel).
 */
$tcats = array( 'all' => 'সব', 'parent' => 'অভিভাবক', 'student' => 'শিক্ষার্থী', 'alumni' => 'প্রাক্তন শিক্ষার্থী' );
$q = new WP_Query( array( 'post_type' => 'ut_testimonial', 'posts_per_page' => 12, 'post_status' => 'publish', 'no_found_rows' => true ) );
if ( ! $q->have_posts() ) {
	return;
}
?>
<section class="section section-soft" aria-labelledby="testi-h">
  <div class="container">
    <div class="sec-head center reveal">
      <span class="eyebrow">মতামত</span>
      <h2 id="testi-h">তাঁরা যা বললেন</h2>
    </div>
    <div class="tabs reveal center" id="testi-tabs" style="justify-content:center" role="tablist" aria-label="মতামতের ধরন"><?php $first = true; foreach ( $tcats as $k => $l ) : ?><button class="tab-btn<?php echo $first ? ' active' : ''; ?>" data-tcat="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $l ); ?></button><?php $first = false; endforeach; ?></div>
    <div class="carousel-wrap reveal">
      <button class="car-btn prev" data-car-prev aria-label="আগের"><?php echo uturn_icon( 'left' ); ?></button>
      <div class="carousel" data-carousel id="testi-car" style="grid-auto-columns:minmax(300px,380px)">
        <?php while ( $q->have_posts() ) : $q->the_post(); $id = get_the_ID();
			$cat = get_post_meta( $id, '_ut_category', true );
			if ( ! $cat ) { $cat = 'parent'; }
			$bg = get_post_meta( $id, '_ut_bg', true );
			?>
        <article class="testi-card" data-cat="<?php echo esc_attr( $cat ); ?>"><div class="stars" aria-label="৫ স্টার রেটিং">★★★★★</div><blockquote>“<?php echo esc_html( get_the_content() ); ?>”</blockquote><div class="testi-who"><span class="avatar" style="background:<?php echo esc_attr( $bg ? $bg : '#0B4EA8' ); ?>"><?php echo esc_html( uturn_initials( get_the_title() ) ); ?></span><span><strong><?php echo esc_html( get_the_title() ); ?></strong><span><?php echo esc_html( get_post_meta( $id, '_ut_role', true ) ); ?></span></span></div></article>
        <?php endwhile; wp_reset_postdata(); ?>
      </div>
      <button class="car-btn next" data-car-next aria-label="পরের"><?php echo uturn_icon( 'right' ); ?></button>
    </div>
  </div>
</section>
<script>
(function () {
  var tabs = document.querySelectorAll('#testi-tabs [data-tcat]');
  var cards = Array.prototype.slice.call(document.querySelectorAll('#testi-car .testi-card'));
  tabs.forEach(function (b) {
    b.addEventListener('click', function () {
      tabs.forEach(function (x) { x.classList.remove('active'); });
      b.classList.add('active');
      var cat = b.getAttribute('data-tcat');
      cards.forEach(function (el) { el.classList.toggle('hide', 'all' !== cat && el.getAttribute('data-cat') !== cat); });
    });
  });
})();
</script>
