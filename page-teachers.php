<?php
/**
 * Template Name: Teachers & Staff Directory
 * (Auto-mapped to the "teachers" page slug as well.)
 */
get_header();
$teachers = get_posts( array( 'post_type' => 'ut_teacher', 'posts_per_page' => 200, 'post_status' => 'publish', 'orderby' => 'title', 'order' => 'ASC' ) );
$staff = get_posts( array( 'post_type' => 'ut_staff', 'posts_per_page' => 200, 'post_status' => 'publish', 'orderby' => 'title', 'order' => 'ASC' ) );
$subjects = $depts = $tdesigs = $sdesigs = array();
foreach ( $teachers as $t ) {
	$tsubj = function_exists( 'uturn_post_subjects_text' ) ? uturn_post_subjects_text( $t->ID ) : get_post_meta( $t->ID, '_ut_subject', true );
	foreach ( explode( ',', $tsubj ) as $sn ) {
		$sn = trim( $sn );
		if ( '' !== $sn ) {
			$subjects[] = $sn;
		}
	}
	$tdesigs[]  = get_post_meta( $t->ID, '_ut_designation', true );
	$dt = get_the_terms( $t->ID, 'ut_department' );
	if ( $dt && ! is_wp_error( $dt ) ) { $depts[] = $dt[0]->name; }
}
foreach ( $staff as $t ) { $sdesigs[] = get_post_meta( $t->ID, '_ut_designation', true ); }
$subjects = array_values( array_unique( array_filter( $subjects ) ) );
$depts = array_values( array_unique( array_filter( $depts ) ) );
$tdesigs = array_values( array_unique( array_filter( $tdesigs ) ) );
$sdesigs = array_values( array_unique( array_filter( $sdesigs ) ) );
$tel = function ( $p ) {
	$d = preg_replace( '/\D/', '', (string) $p );
	if ( 0 === strpos( $d, '880' ) ) { return '+' . $d; }
	if ( 0 === strpos( $d, '0' ) ) { return '+880' . substr( $d, 1 ); }
	return '+' . $d;
};
$show_ph = function_exists( 'uturn_opt' ) ? (string) uturn_opt( 'show_teacher_phone', '1' ) : '1';
?>
<main id="main">
<section class="page-hero"><div class="bg"><img src="<?php echo esc_url( UTURN_URI . '/assets/images/about-main.jpg' ); ?>" alt="" aria-hidden="true"></div>
<div class="container page-hero-inner">
<ul class="breadcrumbs"><li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( uturn_t( 'হোম', 'Home' ) ); ?></a></li><li><?php echo esc_html( uturn_t( 'শিক্ষক ও কর্মচারী', 'Teachers & Staff' ) ); ?></li></ul>
<h1><?php echo esc_html( uturn_t( 'শিক্ষক ও কর্মচারী', 'Teachers & Staff' ) ); ?></h1><p><?php echo esc_html( uturn_t( 'আমাদের গর্ব — অভিজ্ঞ শিক্ষকবৃন্দ ও নিবেদিত সহায়ক কর্মীবাহিনী।', 'Our pride — experienced teachers and dedicated support staff.' ) ); ?></p>
</div></section>
<section class="section"><div class="container">
<div class="tabs" role="tablist" aria-label="<?php echo esc_attr( uturn_t( 'তালিকার ধরন', 'Directory type' ) ); ?>">
<button class="tab-btn active" id="tab-t" role="tab" aria-selected="true"><?php echo esc_html( uturn_t( 'শিক্ষকমণ্ডলী', 'Teachers' ) ); ?> (<?php echo esc_html( 'en' === uturn_lang() ? count( $teachers ) : uturn_bn( count( $teachers ) ) ); ?>)</button>
<button class="tab-btn" id="tab-s" role="tab" aria-selected="false"><?php echo esc_html( uturn_t( 'কর্মচারীবৃন্দ', 'Staff' ) ); ?> (<?php echo esc_html( 'en' === uturn_lang() ? count( $staff ) : uturn_bn( count( $staff ) ) ); ?>)</button>
</div>
<div id="panel-t">
<div class="filter-row">
<input class="input" id="t-search" type="search" placeholder="<?php echo esc_attr( uturn_t( 'নাম বা বিষয় দিয়ে খুঁজুন…', 'Search by name or subject…' ) ); ?>" aria-label="<?php echo esc_attr( uturn_t( 'শিক্ষক খুঁজুন', 'Search teachers' ) ); ?>" style="flex:1;min-width:200px">
<select class="select" id="f-subject" data-attr="data-subj" aria-label="<?php echo esc_attr( uturn_t( 'বিষয় অনুযায়ী', 'By subject' ) ); ?>"><option value=""><?php echo esc_html( uturn_t( 'সকল বিষয়', 'All subjects' ) ); ?></option><?php foreach ( $subjects as $s ) : ?><option><?php echo esc_html( $s ); ?></option><?php endforeach; ?></select>
<select class="select" id="f-dept" data-attr="data-dept" aria-label="<?php echo esc_attr( uturn_t( 'বিভাগ অনুযায়ী', 'By department' ) ); ?>"><option value=""><?php echo esc_html( uturn_t( 'সকল বিভাগ', 'All departments' ) ); ?></option><?php foreach ( $depts as $d ) : ?><option><?php echo esc_html( $d ); ?></option><?php endforeach; ?></select>
<select class="select" id="f-desig" data-attr="data-desig" aria-label="<?php echo esc_attr( uturn_t( 'পদবি অনুযায়ী', 'By designation' ) ); ?>"><option value=""><?php echo esc_html( uturn_t( 'সকল পদবি', 'All designations' ) ); ?></option><?php foreach ( $tdesigs as $d ) : ?><option><?php echo esc_html( $d ); ?></option><?php endforeach; ?></select>
</div>
<p class="muted" id="t-count"></p>
<div class="teachers-grid" id="teachers-grid">
<?php foreach ( $teachers as $t ) :
	$subj = function_exists( 'uturn_post_subjects_text' ) ? uturn_post_subjects_text( $t->ID ) : get_post_meta( $t->ID, '_ut_subject', true );
	$des = get_post_meta( $t->ID, '_ut_designation', true );
	$dt = get_the_terms( $t->ID, 'ut_department' );
	$dep = ( $dt && ! is_wp_error( $dt ) ) ? $dt[0]->name : '';
	$ph = get_post_meta( $t->ID, '_ut_phone', true );
	?>
<article class="teacher-card person-card" data-search="<?php echo esc_attr( mb_strtolower( $t->post_title . ' ' . $subj . ' ' . $des ) ); ?>" data-subj="<?php echo esc_attr( $subj ); ?>" data-dept="<?php echo esc_attr( $dep ); ?>" data-desig="<?php echo esc_attr( $des ); ?>"><?php echo uturn_person_photo( $t->post_title, uturn_photo_url( $t->ID, '_ut_photo', 'ut-person' ), get_post_meta( $t->ID, '_ut_bg', true ), '' ); // phpcs:ignore ?><div class="p-body"><h3><?php echo esc_html( $t->post_title ); ?></h3><div class="desig"><?php echo esc_html( $des ); ?></div><div class="subj"><?php echo esc_html( $subj . ( $dep ? ' · ' . $dep : '' ) ); ?></div><?php if ( $ph && '1' === $show_ph ) : ?><div class="phone"><?php echo uturn_icon( 'phone' ); ?><a href="tel:<?php echo esc_attr( $tel( $ph ) ); ?>"><?php echo esc_html( 'en' === uturn_lang() ? $ph : uturn_bn( $ph ) ); ?></a></div><?php endif; ?><p class="mt-1"><a class="btn btn-outline btn-sm" href="<?php echo esc_url( get_permalink( $t ) ); ?>"><?php echo esc_html( uturn_t( 'প্রোফাইল দেখুন', 'View Profile' ) ); ?></a></p></div></article>
<?php endforeach; ?>
</div>
<div id="teachers-empty"></div>
</div>
<div id="panel-s" class="hide">
<div class="filter-row">
<input class="input" id="s-search" type="search" placeholder="<?php echo esc_attr( uturn_t( 'নাম বা পদবি দিয়ে খুঁজুন…', 'Search by name or designation…' ) ); ?>" aria-label="<?php echo esc_attr( uturn_t( 'কর্মচারী খুঁজুন', 'Search staff' ) ); ?>" style="flex:1;min-width:200px">
<select class="select" id="s-desig" data-attr="data-desig" aria-label="<?php echo esc_attr( uturn_t( 'পদবি অনুযায়ী', 'By designation' ) ); ?>"><option value=""><?php echo esc_html( uturn_t( 'সকল পদবি', 'All designations' ) ); ?></option><?php foreach ( $sdesigs as $d ) : ?><option><?php echo esc_html( $d ); ?></option><?php endforeach; ?></select>
</div>
<p class="muted" id="s-count"></p>
<div class="teachers-grid" id="staff-grid">
<?php foreach ( $staff as $t ) :
	$des = get_post_meta( $t->ID, '_ut_designation', true );
	$ph = get_post_meta( $t->ID, '_ut_phone', true );
	?>
<article class="teacher-card person-card" data-search="<?php echo esc_attr( mb_strtolower( $t->post_title . ' ' . $des ) ); ?>" data-desig="<?php echo esc_attr( $des ); ?>"><?php echo uturn_person_photo( $t->post_title, uturn_photo_url( $t->ID, '_ut_photo', 'ut-person' ), get_post_meta( $t->ID, '_ut_bg', true ), '' ); // phpcs:ignore ?><div class="p-body"><h3><?php echo esc_html( $t->post_title ); ?></h3><div class="desig"><?php echo esc_html( $des ); ?></div><?php if ( $ph && '1' === $show_ph ) : ?><div class="phone"><?php echo uturn_icon( 'phone' ); ?><a href="tel:<?php echo esc_attr( $tel( $ph ) ); ?>"><?php echo esc_html( 'en' === uturn_lang() ? $ph : uturn_bn( $ph ) ); ?></a></div><?php endif; ?></div></article>
<?php endforeach; ?>
</div>
<div id="staff-empty"></div>
</div>
</div></section>
</main>
<script>
(function () {
  var EN=<?php echo 'en' === uturn_lang() ? 'true' : 'false'; ?>;
  var BN = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
  var toBn = function (s) { return String(s).replace(/\d/g, function (d) { return BN[+d]; }); };
  var tabT = document.getElementById('tab-t'), tabS = document.getElementById('tab-s');
  function show(which) {
    var isT = 't' === which;
    tabT.classList.toggle('active', isT); tabS.classList.toggle('active', !isT);
    tabT.setAttribute('aria-selected', isT); tabS.setAttribute('aria-selected', !isT);
    document.getElementById('panel-t').classList.toggle('hide', !isT);
    document.getElementById('panel-s').classList.toggle('hide', isT);
  }
  tabT.addEventListener('click', function () { show('t'); });
  tabS.addEventListener('click', function () { show('s'); });
  function bind(grid, inputs, countEl, emptyEl, noun, emptyTitle, nounEn, emptyTitleEn) {
    var cards = Array.prototype.slice.call(document.querySelectorAll(grid + ' .teacher-card'));
    function render() {
      var q = document.querySelector(inputs[0]).value.trim().toLowerCase();
      var list = cards.filter(function (el) {
        if (q && el.getAttribute('data-search').indexOf(q) < 0) return false;
        for (var i = 1; i < inputs.length; i++) {
          var v = document.querySelector(inputs[i]).value;
          var attr = document.querySelector(inputs[i]).getAttribute('data-attr');
          if (v && !attr) return false;
          if (v && 'data-subj' === attr) {
            var parts = (el.getAttribute(attr) || '').split(',').map(function (x) { return x.trim(); });
            if (parts.indexOf(v) < 0) return false;
          } else if (v && el.getAttribute(attr) !== v) return false;
        }
        return true;
      });
      cards.forEach(function (el) { el.classList.add('hide'); });
      list.forEach(function (el) { el.classList.remove('hide'); });
      document.querySelector(countEl).textContent = EN ? (list.length + ' ' + nounEn + ' found') : (toBn(list.length) + ' জন ' + noun + ' পাওয়া গেছে');
      document.querySelector(emptyEl).innerHTML = list.length ? '' : '<div class="empty-state"><h3>' + (EN ? emptyTitleEn : emptyTitle) + '</h3><p>' + (EN ? 'Try another name or filter' : 'অন্য নাম বা ফিল্টার দিয়ে চেষ্টা করুন') + '</p></div>';
    }
    inputs.forEach(function (sel) { document.querySelector(sel).addEventListener('input', render); });
    render();
  }
  bind('#teachers-grid', ['#t-search', '#f-subject', '#f-dept', '#f-desig'], '#t-count', '#teachers-empty', 'শিক্ষক', 'কোনো শিক্ষক পাওয়া যায়নি', 'teachers', 'No teachers found');
  bind('#staff-grid', ['#s-search', '#s-desig'], '#s-count', '#staff-empty', 'কর্মচারী', 'কোনো কর্মচারী পাওয়া যায়নি', 'staff members', 'No staff found');
})();
</script>
<?php
get_footer();
