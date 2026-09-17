<?php
/**
 * EduTurn — Home 18: FAQ (category filter + accordion).
 */
$terms = get_terms( array( 'taxonomy' => 'ut_faq_cat', 'hide_empty' => false ) );
if ( is_wp_error( $terms ) ) {
	$terms = array();
}
$q = new WP_Query( array( 'post_type' => 'ut_faq', 'posts_per_page' => 30, 'post_status' => 'publish', 'no_found_rows' => true ) );
if ( ! $q->have_posts() ) {
	return;
}
?>
<section class="section section-soft" aria-labelledby="faq-h">
  <div class="container">
    <div class="sec-head center reveal">
      <span class="eyebrow">সাধারণ জিজ্ঞাসা</span>
      <h2 id="faq-h">আপনার প্রশ্নের উত্তর</h2>
    </div>
    <div class="faq-layout reveal">
      <div class="faq-cats" id="faq-cats" role="tablist" aria-label="জিজ্ঞাসার ধরন"><button class="faq-cat-btn active" data-fcat="all">সব</button><?php foreach ( $terms as $t ) : ?><button class="faq-cat-btn" data-fcat="<?php echo esc_attr( $t->slug ); ?>"><?php echo esc_html( $t->name ); ?></button><?php endforeach; ?></div>
      <div class="accordion" id="faq-list">
        <?php while ( $q->have_posts() ) : $q->the_post();
			$ts = get_the_terms( get_the_ID(), 'ut_faq_cat' );
			$slug = ( $ts && ! is_wp_error( $ts ) ) ? $ts[0]->slug : 'general';
			?>
        <div class="acc-item" data-cat="<?php echo esc_attr( $slug ); ?>"><button class="acc-q" type="button"><?php echo esc_html( get_the_title() ); ?><span class="plus">+</span></button><div class="acc-a"><div class="acc-a-inner"><?php echo esc_html( get_the_content() ); ?></div></div></div>
        <?php endwhile; wp_reset_postdata(); ?>
      </div>
    </div>
  </div>
</section>
<script>
(function () {
  var tabs = document.querySelectorAll('#faq-cats [data-fcat]');
  var items = Array.prototype.slice.call(document.querySelectorAll('#faq-list .acc-item'));
  tabs.forEach(function (b) {
    b.addEventListener('click', function () {
      tabs.forEach(function (x) { x.classList.remove('active'); });
      b.classList.add('active');
      var cat = b.getAttribute('data-fcat');
      items.forEach(function (el) { el.classList.toggle('hide', 'all' !== cat && el.getAttribute('data-cat') !== cat); });
    });
  });
})();
</script>
