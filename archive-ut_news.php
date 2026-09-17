<?php
/**
 * EduTurn — News archive: search + category filter + grid.
 */
defined( 'ABSPATH' ) || exit;
get_header();
$cats = get_terms( array( 'taxonomy' => 'ut_news_cat', 'hide_empty' => false ) );
?>

<main id="main"><div class="container">
  <nav class="breadcrumb" aria-label="<?php echo esc_attr( uturn_t( 'ব্রেডক্রাম্ব', 'Breadcrumb' ) ); ?>"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( uturn_t( 'হোম', 'Home' ) ); ?></a><span>›</span><span><?php echo esc_html( uturn_t( 'সংবাদ', 'News' ) ); ?></span></nav>
  <div class="page-head">
    <h1><?php echo esc_html( uturn_t( 'সকল সংবাদ', 'All News' ) ); ?></h1>
    <p class="muted"><?php echo esc_html( uturn_t( 'ক্যাম্পাসের সর্বশেষ খবর ও ঘোষণা', 'Latest campus news and announcements' ) ); ?></p>
  </div>
  <div class="dl-toolbar">
    <input type="search" id="newsSearch" placeholder="<?php echo esc_attr( uturn_t( '🔍 সংবাদ খুঁজুন...', '🔍 Search news...' ) ); ?>" aria-label="<?php echo esc_attr( uturn_t( 'সংবাদ খুঁজুন', 'Search news' ) ); ?>">
    <select id="newsCat" aria-label="<?php echo esc_attr( uturn_t( 'ক্যাটাগরি', 'Category' ) ); ?>">
      <option value=""><?php echo esc_html( uturn_t( 'সব ক্যাটাগরি', 'All categories' ) ); ?></option>
      <?php foreach ( $cats as $c ) : ?>
        <option value="<?php echo esc_attr( $c->slug ); ?>"><?php echo esc_html( $c->name ); ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="news-grid news-archive" id="newsGrid">
    <?php
    while ( have_posts() ) : the_post();
      $id = get_the_ID();
      $cover = uturn_img( get_post_meta( $id, '_ut_image', true ), UTURN_URI . '/assets/images/news-1.jpg' );
      $terms = get_the_terms( $id, 'ut_news_cat' );
      $slug = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->slug : '';
      ?>
      <article class="news-card" data-cat="<?php echo esc_attr( $slug ); ?>" data-title="<?php echo esc_attr( mb_strtolower( get_the_title() ) ); ?>">
        <a class="nimg" href="<?php the_permalink(); ?>"><img src="<?php echo esc_url( $cover ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy"></a>
        <div class="nbody"><h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3><p class="meta-line"><?php echo esc_html( get_post_meta( $id, '_ut_date_bn', true ) ); ?></p><p><?php echo esc_html( get_post_meta( $id, '_ut_excerpt', true ) ); ?></p></div>
      </article>
    <?php endwhile; ?>
  </div>
  <p class="muted" id="newsEmpty" style="display:none"><?php echo esc_html( uturn_t( 'কোনো সংবাদ পাওয়া যায়নি।', 'No news found.' ) ); ?></p>
  <?php the_posts_pagination( array( 'prev_text' => uturn_t( '← আগের', '← Previous' ), 'next_text' => uturn_t( 'পরের →', 'Next →' ) ) ); ?>
</div></main>

<script>
(function(){var q=document.getElementById('newsSearch'),c=document.getElementById('newsCat'),cards=document.querySelectorAll('#newsGrid .news-card');
  function f(){var s=q.value.trim().toLowerCase(),cat=c.value,n=0;cards.forEach(function(r){
    var ok=(!cat||r.dataset.cat===cat)&&(!s||(r.dataset.title||'').indexOf(s)>-1);r.style.display=ok?'':'none';if(ok)n++;});
    document.getElementById('newsEmpty').style.display=n?'none':'block';}
  q.addEventListener('input',f);c.addEventListener('change',f);
})();
</script>
<?php get_footer(); ?>
