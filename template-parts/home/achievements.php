<?php
/**
 * EduTurn — Home 12: Achievements + board strip.
 */
$q = new WP_Query( array( 'post_type' => 'ut_achievement', 'posts_per_page' => 4, 'post_status' => 'publish', 'no_found_rows' => true ) );
?>
<section class="section section-dark" aria-labelledby="ach-h">
  <div class="container">
    <div class="sec-head center reveal">
      <span class="eyebrow">অর্জন ও স্বীকৃতি</span>
      <h2 id="ach-h">সাফল্যে আমাদের গর্ব</h2>
      <p class="lead">বোর্ড পরীক্ষা থেকে জাতীয় প্রতিযোগিতা—সর্বত্র আমাদের শিক্ষার্থীদের উজ্জ্বল পদচিহ্ন।</p>
    </div>
    <div class="ach-grid" id="ach-grid">
      <?php $i = 0; while ( $q->have_posts() ) : $q->the_post(); ?>
      <div class="ach-card reveal d<?php echo (int) ( $i % 4 ); ?>"><span class="aicon"><?php echo uturn_icon( 'trophy' ); ?></span><strong><?php echo esc_html( get_post_meta( get_the_ID(), '_ut_num', true ) ); ?></strong><span><?php echo esc_html( get_the_title() ); ?></span></div>
      <?php $i++; endwhile; wp_reset_postdata(); ?>
    </div>
    <div class="board-strip reveal">
      <span class="trophy"><?php echo uturn_icon( 'trophy' ); ?></span>
      <div><?php echo wp_kses_post( uturn_opt( 'board_strip' ) ); ?> <br><span class="muted small">টানা ৮ বছর ধরে শতভাগ পাসের ধারাবাহিকতা বজায় রেখেছে আমাদের প্রতিষ্ঠান।</span></div>
    </div>
  </div>
</section>
