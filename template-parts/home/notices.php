<?php
/**
 * EduTurn — Home 08: Notice board (server-rendered tabs + filter).
 */
$notices_url = get_post_type_archive_link( 'ut_notice' ) ? get_post_type_archive_link( 'ut_notice' ) : home_url( '/notices/' );
$terms = get_terms( array( 'taxonomy' => 'ut_notice_cat', 'hide_empty' => false ) );
if ( is_wp_error( $terms ) ) {
	$terms = array();
}
$q = new WP_Query( array( 'post_type' => 'ut_notice', 'posts_per_page' => 15, 'post_status' => 'publish', 'no_found_rows' => true ) );
$cards = array();
while ( $q->have_posts() ) {
	$q->the_post();
	$id    = get_the_ID();
	$ts    = get_the_terms( $id, 'ut_notice_cat' );
	$slug  = ( $ts && ! is_wp_error( $ts ) ) ? $ts[0]->slug : 'general';
	$tname = ( $ts && ! is_wp_error( $ts ) ) ? $ts[0]->name : 'সাধারণ';
	$datebn = get_post_meta( $id, '_ut_date_bn', true );
	if ( ! $datebn ) {
		$datebn = uturn_bn_date( get_the_date( 'Y-m-d' ) );
	}
	$dw    = explode( ' ', $datebn );
	$day   = array_shift( $dw );
	$rest  = implode( ' ', $dw );
	$ex    = get_post_meta( $id, '_ut_excerpt', true );
	if ( ! $ex ) {
		$ex = wp_trim_words( get_the_content(), 24 );
	}
	$cards[] = array( $slug, $tname, $day, $rest, get_the_title(), $ex, get_permalink() );
}
wp_reset_postdata();
?>
<section class="section section-soft" aria-labelledby="notice-h">
  <div class="container">
    <div class="sec-head-split reveal">
      <div class="sec-head"><span class="eyebrow">ঘোষণা ও বিজ্ঞপ্তি</span>
        <h2 id="notice-h">নোটিশ বোর্ড</h2></div>
      <a class="btn btn-primary" href="<?php echo esc_url( $notices_url ); ?>">সকল নোটিশ</a>
    </div>
    <div class="tabs reveal" id="notice-tabs" role="tablist" aria-label="নোটিশ ক্যাটাগরি"><button class="tab-btn active" data-ncat="all" role="tab" aria-selected="true">সব</button><?php foreach ( $terms as $t ) : ?><button class="tab-btn" data-ncat="<?php echo esc_attr( $t->slug ); ?>" role="tab" aria-selected="false"><?php echo esc_html( $t->name ); ?></button><?php endforeach; ?></div>
    <div class="notice-list reveal" id="notice-list">
      <?php foreach ( $cards as $c ) : ?>
      <article class="notice-card" data-cat="<?php echo esc_attr( $c[0] ); ?>"><div class="notice-date"><strong><?php echo esc_html( $c[2] ); ?></strong><span><?php echo esc_html( $c[3] ); ?></span></div><div><div class="notice-meta"><span class="tag tag-<?php echo esc_attr( $c[0] ); ?>"><?php echo esc_html( $c[1] ); ?></span></div><h3><a href="<?php echo esc_url( $c[6] ); ?>"><?php echo esc_html( $c[4] ); ?></a></h3><p><?php echo esc_html( $c[5] ); ?></p></div><a class="btn btn-outline btn-sm" href="<?php echo esc_url( $c[6] ); ?>">বিস্তারিত</a></article>
      <?php endforeach; ?>
      <?php if ( ! $cards ) : ?><div class="empty-state"><h3>কোনো নোটিশ প্রকাশিত হয়নি</h3><p>শীঘ্রই নতুন নোটিশ আসছে</p></div><?php endif; ?>
    </div>
  </div>
</section>
<script>
(function () {
  var tabs = document.querySelectorAll('#notice-tabs [data-ncat]');
  var cards = Array.prototype.slice.call(document.querySelectorAll('#notice-list .notice-card'));
  function apply(cat) {
    var shown = 0;
    cards.forEach(function (el) {
      var ok = ('all' === cat || el.getAttribute('data-cat') === cat) && shown < 5;
      el.classList.toggle('hide', !ok);
      if (ok) shown++;
    });
  }
  tabs.forEach(function (b) {
    b.addEventListener('click', function () {
      tabs.forEach(function (x) { x.classList.remove('active'); x.setAttribute('aria-selected', 'false'); });
      b.classList.add('active'); b.setAttribute('aria-selected', 'true');
      apply(b.getAttribute('data-ncat'));
    });
  });
  apply('all');
})();
</script>
