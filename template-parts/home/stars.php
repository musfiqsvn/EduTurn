<?php
/**
 * EduTurn — Home 14: Star students.
 */
$q = new WP_Query(
	array( 'post_type' => 'ut_student', 'posts_per_page' => 4, 'post_status' => 'publish', 'no_found_rows' => true, 'meta_key' => '_ut_star', 'meta_value' => '1' )
);
if ( ! $q->have_posts() ) {
	return;
}
?>
<section class="section section-soft" aria-labelledby="stars-h">
  <div class="container">
    <div class="sec-head center reveal">
      <span class="eyebrow">কৃতি শিক্ষার্থী</span>
      <h2 id="stars-h">আমাদের উজ্জ্বল তারকারা</h2>
    </div>
    <div class="stu-grid" id="stars-grid">
      <?php $i = 0; while ( $q->have_posts() ) : $q->the_post(); $id = get_the_ID();
			$cls = (string) get_post_meta( $id, '_ut_class', true );
			if ( '' !== $cls && false === strpos( $cls, 'শ্রেণি' ) ) {
				$cls .= ' শ্রেণি';
			}
			$bg = get_post_meta( $id, '_ut_bg', true );
			?>
      <article class="stu-card reveal d<?php echo (int) $i; ?>"><div class="s-top"><div class="avatar" style="background:<?php echo esc_attr( $bg ? $bg : '#0B4EA8' ); ?>;width:84px;height:84px;font-size:1.7rem"><?php echo esc_html( uturn_initials( get_the_title() ) ); ?></div><h3><?php echo esc_html( get_the_title() ); ?></h3><div class="cls"><?php echo esc_html( $cls ); ?></div></div><div class="s-bot"><span class="medal"><?php echo uturn_icon( 'trophy' ); ?><?php echo esc_html( get_post_meta( $id, '_ut_star_text', true ) ); ?></span></div></article>
      <?php $i++; endwhile; wp_reset_postdata(); ?>
    </div>
  </div>
</section>
