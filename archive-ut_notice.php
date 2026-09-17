<?php
/**
 * EduTurn — Notice board archive (search + category + month filter, pager).
 */
get_header();
$terms = get_terms( array( 'taxonomy' => 'ut_notice_cat', 'hide_empty' => false ) );
if ( is_wp_error( $terms ) ) {
	$terms = array();
}
$posts = get_posts( array( 'post_type' => 'ut_notice', 'posts_per_page' => 200, 'post_status' => 'publish' ) );
?>
<main id="main">
<section class="page-hero"><div class="container page-hero-inner">
<ul class="breadcrumbs"><li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( uturn_t( 'হোম', 'Home' ) ); ?></a></li><li><?php echo esc_html( uturn_t( 'নোটিশ', 'Notices' ) ); ?></li></ul>
<h1><?php echo esc_html( uturn_t( 'নোটিশ বোর্ড', 'Notice Board' ) ); ?></h1><p><?php echo esc_html( uturn_t( 'সব ঘোষণা ও বিজ্ঞপ্তি — ক্যাটাগরি ও তারিখ অনুযায়ী খুঁজুন।', 'All announcements and circulars — filter by category and date.' ) ); ?></p>
</div></section>
<section class="section"><div class="container">
<div class="filter-row">
<input class="input" id="n-search" type="search" placeholder="<?php echo esc_attr( uturn_t( 'নোটিশ খুঁজুন…', 'Search notices…' ) ); ?>" aria-label="<?php echo esc_attr( uturn_t( 'নোটিশ খুঁজুন', 'Search notices' ) ); ?>" style="flex:1;min-width:200px">
<select class="select" id="n-cat" aria-label="<?php echo esc_attr( uturn_t( 'ক্যাটাগরি', 'Category' ) ); ?>"><option value="all"><?php echo esc_html( uturn_t( 'সব', 'All' ) ); ?></option><?php foreach ( $terms as $t ) : ?><option value="<?php echo esc_attr( $t->slug ); ?>"><?php echo esc_html( $t->name ); ?></option><?php endforeach; ?></select>
<input class="input" id="n-date" type="month" aria-label="<?php echo esc_attr( uturn_t( 'মাস অনুযায়ী', 'By month' ) ); ?>" value="<?php echo esc_attr( date_i18n( 'Y-m' ) ); ?>">
</div>
<div class="tabs" id="n-tabs" role="tablist" aria-label="<?php echo esc_attr( uturn_t( 'ক্যাটাগরি', 'Category' ) ); ?>"><button class="tab-btn active" data-c="all"><?php echo esc_html( uturn_t( 'সব', 'All' ) ); ?></button><?php foreach ( $terms as $t ) : ?><button class="tab-btn" data-c="<?php echo esc_attr( $t->slug ); ?>"><?php echo esc_html( $t->name ); ?></button><?php endforeach; ?></div>
<div class="notice-list" id="n-list">
<?php foreach ( $posts as $p ) :
	$ts = get_the_terms( $p->ID, 'ut_notice_cat' );
	$slug = ( $ts && ! is_wp_error( $ts ) ) ? $ts[0]->slug : 'general';
	$tn = ( $ts && ! is_wp_error( $ts ) ) ? $ts[0]->name : 'সাধারণ';
	$datebn = get_post_meta( $p->ID, '_ut_date_bn', true );
	if ( ! $datebn ) { $datebn = uturn_bn_date( get_the_date( 'Y-m-d', $p ) ); }
	$dw = explode( ' ', $datebn );
	$day = array_shift( $dw );
	$ex = get_post_meta( $p->ID, '_ut_excerpt', true );
	if ( ! $ex ) { $ex = wp_trim_words( $p->post_content, 24 ); }
	?>
<article class="notice-card" data-cat="<?php echo esc_attr( $slug ); ?>" data-date="<?php echo esc_attr( get_the_date( 'Y-m-d', $p ) ); ?>" data-search="<?php echo esc_attr( mb_strtolower( $p->post_title . ' ' . $ex ) ); ?>"><div class="notice-date"><strong><?php echo esc_html( $day ); ?></strong><span><?php echo esc_html( implode( ' ', $dw ) ); ?></span></div><div><div class="notice-meta"><span class="tag tag-<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $tn ); ?></span></div><h3><a href="<?php echo esc_url( get_permalink( $p ) ); ?>"><?php echo esc_html( $p->post_title ); ?></a></h3><p><?php echo esc_html( $ex ); ?></p></div><a class="btn btn-outline btn-sm" href="<?php echo esc_url( get_permalink( $p ) ); ?>" aria-label="<?php echo esc_attr( uturn_t( 'বিস্তারিত', 'Details' ) . ': ' . $p->post_title ); ?>"><?php echo esc_html( uturn_t( 'বিস্তারিত', 'Details' ) ); ?></a></article>
<?php endforeach; ?>
</div>
<div class="pagination" id="n-pages"></div>
</div></section>
</main>
<script>
(function () {
  var ENL = document.documentElement.classList.contains('ut-en');
  var BN = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
  var toBn = function (s) { return String(s).replace(/\d/g, function (d) { return ENL ? d : BN[+d]; }); };
  var qs = new URLSearchParams(location.search);
  var cat = qs.get('cat') || 'all', page = 1, per = 6;
  var cards = Array.prototype.slice.call(document.querySelectorAll('#n-list .notice-card'));
  var sel = document.getElementById('n-cat'), search = document.getElementById('n-search'), month = document.getElementById('n-date');
  if (sel.querySelector('option[value="' + cat + '"]')) sel.value = cat;
  function syncTabs() {
    document.querySelectorAll('#n-tabs [data-c]').forEach(function (b) { b.classList.toggle('active', b.getAttribute('data-c') === cat); });
  }
  function render() {
    syncTabs();
    var q = search.value.trim().toLowerCase(), m = month.value;
    var list = cards.filter(function (el) {
      return ('all' === cat || el.getAttribute('data-cat') === cat)
        && (!q || el.getAttribute('data-search').indexOf(q) > -1)
        && (!m || el.getAttribute('data-date').indexOf(m) === 0);
    });
    var pages = Math.max(1, Math.ceil(list.length / per));
    page = Math.min(page, pages);
    cards.forEach(function (el) { el.classList.add('hide'); });
    list.slice((page - 1) * per, page * per).forEach(function (el) { el.classList.remove('hide'); });
    var pg = document.getElementById('n-pages');
    var btns = '';
    for (var i = 1; i <= pages; i++) {
      btns += '<button class="' + (i === page ? 'active' : '') + '" data-p="' + i + '">' + toBn(i) + '</button>';
    }
    pg.innerHTML = pages > 1 ? btns : '';
    pg.querySelectorAll('[data-p]').forEach(function (b) {
      b.addEventListener('click', function () { page = +b.getAttribute('data-p'); render(); window.scrollTo({ top: 0, behavior: 'smooth' }); });
    });
  }
  document.querySelectorAll('#n-tabs [data-c]').forEach(function (b) {
    b.addEventListener('click', function () { cat = b.getAttribute('data-c'); page = 1; sel.value = cat; render(); });
  });
  search.addEventListener('input', function () { page = 1; render(); });
  month.addEventListener('change', function () { page = 1; render(); });
  sel.addEventListener('change', function () { cat = sel.value; page = 1; render(); });
  render();
})();
</script>
<?php
get_footer();
