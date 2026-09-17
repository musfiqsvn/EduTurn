<?php
/**
 * EduTurn — Home 13: Teachers preview.
 */
$q = new WP_Query( array( 'post_type' => 'ut_teacher', 'posts_per_page' => 4, 'post_status' => 'publish', 'orderby' => 'title', 'order' => 'ASC', 'no_found_rows' => true ) );
?>
<section class="section" aria-labelledby="teach-h">
  <div class="container">
    <div class="sec-head-split reveal">
      <div class="sec-head"><span class="eyebrow">শিক্ষকমণ্ডলী</span>
        <h2 id="teach-h">অভিজ্ঞ ও নিবেদিত শিক্ষক</h2></div>
      <a class="btn btn-primary" href="<?php echo esc_url( uturn_url( 'teachers' ) ); ?>">সকল শিক্ষক দেখুন</a>
    </div>
    <div class="teachers-grid" id="teachers-preview">
      <?php $i = 0; while ( $q->have_posts() ) : $q->the_post(); $id = get_the_ID(); ?>
      <article class="teacher-card reveal d<?php echo (int) $i; ?>"><?php echo uturn_person_photo( get_the_title(), uturn_photo_url( $id, '_ut_photo', 'ut-person' ), get_post_meta( $id, '_ut_bg', true ), 'round-lg' ); // phpcs:ignore ?><h3><?php echo esc_html( get_the_title() ); ?></h3><div class="desig"><?php echo esc_html( get_post_meta( $id, '_ut_designation', true ) ); ?></div><div class="subj"><?php echo esc_html( get_post_meta( $id, '_ut_subject', true ) ); ?></div><a class="btn btn-outline btn-sm" href="<?php echo esc_url( get_permalink() ); ?>">প্রোফাইল</a></article>
      <?php $i++; endwhile; wp_reset_postdata(); ?>
    </div>
  </div>
</section>
