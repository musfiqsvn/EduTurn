<?php
/**
 * EduTurn — Gallery archive (album grid + category tabs).
 */
defined( 'ABSPATH' ) || exit;
get_header();
$cats = get_terms( array( 'taxonomy' => 'ut_gallery_cat', 'hide_empty' => false ) );
?>

<main id="main"><div class="container">
  <nav class="breadcrumb" aria-label="<?php echo esc_attr( uturn_t( 'ব্রেডক্রাম্ব', 'Breadcrumb' ) ); ?>"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( uturn_t( 'হোম', 'Home' ) ); ?></a><span>›</span><span><?php echo esc_html( uturn_t( 'গ্যালারি', 'Gallery' ) ); ?></span></nav>
  <div class="page-head">
    <h1><?php echo esc_html( uturn_t( 'ফটো গ্যালারি', 'Photo Gallery' ) ); ?></h1>
    <p class="muted"><?php echo esc_html( uturn_t( 'ক্যাম্পাস, ইভেন্ট ও স্মরণীয় মুহূর্তের অ্যালবাম', 'Albums of campus, events and memorable moments' ) ); ?></p>
  </div>
  <div class="tabs" id="galTabs">
    <button class="tab active" data-cat=""><?php echo esc_html( uturn_t( 'সব', 'All' ) ); ?></button>
    <?php foreach ( $cats as $c ) : ?>
      <button class="tab" data-cat="<?php echo esc_attr( $c->slug ); ?>"><?php echo esc_html( $c->name ); ?></button>
    <?php endforeach; ?>
  </div>
  <div class="archive-grid" id="albumGrid">
    <?php
    while ( have_posts() ) : the_post();
      $id = get_the_ID();
      $cover = uturn_img( get_post_meta( $id, '_ut_cover', true ), UTURN_URI . '/assets/images/campus.jpg' );
      $terms = get_the_terms( $id, 'ut_gallery_cat' );
      $slug = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->slug : '';
      $cname = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';
      $count = count( uturn_lines( get_post_meta( $id, '_ut_photos', true ) ) );
      ?>
      <a class="album-card" data-cat="<?php echo esc_attr( $slug ); ?>" href="<?php the_permalink(); ?>">
        <img src="<?php echo esc_url( $cover ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
        <span class="ab"><h3><?php the_title(); ?></h3><span class="meta"><?php echo esc_html( $cname ); ?> · <?php echo esc_html( 'en' === uturn_lang() ? $count . ' photos' : uturn_bn( $count ) . 'টি ছবি' ); ?> · <?php echo esc_html( get_post_meta( $id, '_ut_date_bn', true ) ); ?></span></span>
      </a>
    <?php endwhile; ?>
  </div>
  <p class="muted" id="galEmpty" style="display:none"><?php echo esc_html( uturn_t( 'এই ক্যাটাগরিতে কোনো অ্যালবাম নেই।', 'No albums in this category.' ) ); ?></p>
  <?php the_posts_pagination( array( 'prev_text' => uturn_t( '← আগের', '← Previous' ), 'next_text' => uturn_t( 'পরের →', 'Next →' ) ) ); ?>
</div></main>

<script>
(function(){var cat='',cards=document.querySelectorAll('#albumGrid .album-card');
  function f(){var n=0;cards.forEach(function(c){var ok=!cat||c.dataset.cat===cat;c.style.display=ok?'':'none';if(ok)n++;});
    document.getElementById('galEmpty').style.display=n?'none':'block';}
  document.querySelectorAll('#galTabs .tab').forEach(function(t){t.addEventListener('click',function(){
    document.querySelectorAll('#galTabs .tab').forEach(function(x){x.classList.remove('active');});t.classList.add('active');cat=t.dataset.cat;f();});});
})();
</script>
<?php get_footer(); ?>
