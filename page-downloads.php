<?php
/**
 * Template Name: Downloads (ডাউনলোড)
 */
defined( 'ABSPATH' ) || exit;
get_header();
$cats = get_terms( array( 'taxonomy' => 'ut_download_cat', 'hide_empty' => false ) );
$files = new WP_Query( array( 'post_type' => 'ut_download', 'posts_per_page' => -1 ) );
?>

<main id="main"><div class="container">
  <nav class="breadcrumb" aria-label="<?php echo esc_attr( uturn_t( 'ব্রেডক্রাম্ব', 'Breadcrumb' ) ); ?>"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( uturn_t( 'হোম', 'Home' ) ); ?></a><span>›</span><span><?php echo esc_html( uturn_t( 'ডাউনলোড', 'Downloads' ) ); ?></span></nav>
  <div class="page-head">
    <h1><?php echo esc_html( uturn_t( 'ডাউনলোড কর্নার', 'Download Corner' ) ); ?></h1>
    <p class="muted"><?php echo esc_html( uturn_t( 'ফরম, রুটিন, সিলেবাস ও অন্যান্য প্রয়োজনীয় ফাইল', 'Forms, routines, syllabi and other essential files' ) ); ?></p>
  </div>
  <div class="dl-toolbar">
    <input type="search" id="dlSearch" placeholder="<?php echo esc_attr( uturn_t( '🔍 ফাইল খুঁজুন...', '🔍 Search files...' ) ); ?>" aria-label="<?php echo esc_attr( uturn_t( 'ফাইল খুঁজুন', 'Search files' ) ); ?>">
  </div>
  <div class="tabs" id="dlTabs">
    <button class="tab active" data-cat=""><?php echo esc_html( uturn_t( 'সব', 'All' ) ); ?></button>
    <?php foreach ( $cats as $c ) : ?>
      <button class="tab" data-cat="<?php echo esc_attr( $c->slug ); ?>"><?php echo esc_html( $c->name ); ?></button>
    <?php endforeach; ?>
  </div>
  <div class="dl-list" id="dlList">
    <?php
    while ( $files->have_posts() ) : $files->the_post();
      $id = get_the_ID();
      $file = get_post_meta( $id, '_ut_file', true );
      $url = is_numeric( $file ) ? wp_get_attachment_url( (int) $file ) : ( preg_match( '#^https?://#', (string) $file ) ? $file : ( $file ? UTURN_URI . '/' . ltrim( $file, '/' ) : '' ) );
      $ft = get_post_meta( $id, '_ut_ftype', true );
      $terms = get_the_terms( $id, 'ut_download_cat' );
      $slug = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->slug : '';
      $cname = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';
      $icon = 'PDF' === $ft ? '📕' : ( 'DOC' === $ft ? '📘' : ( 'IMG' === $ft ? '🖼️' : '📎' ) );
      ?>
      <div class="dl-row" data-cat="<?php echo esc_attr( $slug ); ?>" data-title="<?php echo esc_attr( mb_strtolower( get_the_title() ) ); ?>">
        <span class="dl-icon"><?php echo esc_html( $icon ); ?></span>
        <div class="dl-info"><strong><?php the_title(); ?></strong><p class="muted small"><?php echo esc_html( $cname ); ?> · <?php echo esc_html( get_post_meta( $id, '_ut_date_bn', true ) ); ?> · <?php echo esc_html( $ft ); ?></p></div>
        <?php if ( $url ) : ?><a class="btn btn-outline btn-sm" href="<?php echo esc_url( $url ); ?>" download><?php echo esc_html( uturn_t( '⬇️ ডাউনলোড', '⬇️ Download' ) ); ?></a><?php endif; ?>
      </div>
    <?php endwhile; wp_reset_postdata(); ?>
  </div>
  <p class="muted" id="dlEmpty" style="display:none"><?php echo esc_html( uturn_t( 'কোনো ফাইল পাওয়া যায়নি।', 'No files found.' ) ); ?></p>
</div></main>

<script>
(function(){var q=document.getElementById('dlSearch'),cat='',rows=document.querySelectorAll('#dlList .dl-row');
  function f(){var s=q.value.trim().toLowerCase(),n=0;rows.forEach(function(r){
    var ok=(!cat||r.dataset.cat===cat)&&(!s||r.dataset.title.indexOf(s)>-1);r.style.display=ok?'':'none';if(ok)n++;});
    document.getElementById('dlEmpty').style.display=n?'none':'block';}
  q.addEventListener('input',f);
  document.querySelectorAll('#dlTabs .tab').forEach(function(t){t.addEventListener('click',function(){
    document.querySelectorAll('#dlTabs .tab').forEach(function(x){x.classList.remove('active');});t.classList.add('active');cat=t.dataset.cat;f();});});
})();
</script>
<?php get_footer(); ?>
