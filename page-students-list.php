<?php
/**
 * Template Name: Student Directory
 * (Auto-mapped to the "students-list" page slug as well.)
 * v1.21.0: class-first filtering — students render only after a class is
 * picked; section/group/search/pagination are all scoped to that class.
 */
get_header();
global $wpdb;
$EN = ( 'en' === uturn_lang() );

/* Classes that actually have published students (taxonomy order first). */
$have_classes = $wpdb->get_col(
	$wpdb->prepare(
		"SELECT DISTINCT m.meta_value FROM {$wpdb->postmeta} m INNER JOIN {$wpdb->posts} p ON p.ID = m.post_id WHERE p.post_type = %s AND p.post_status = 'publish' AND m.meta_key = '_ut_class' AND m.meta_value <> ''",
		'ut_student'
	)
);
$terms = function_exists( 'uturn_class_terms' ) ? uturn_class_terms() : get_terms( array( 'taxonomy' => 'ut_class', 'hide_empty' => false ) );
if ( is_wp_error( $terms ) ) {
	$terms = array();
}
$classes = array();
foreach ( $terms as $t ) {
	if ( in_array( $t->name, $have_classes, true ) ) {
		$classes[] = $t->name;
	}
}
foreach ( $have_classes as $hv ) {
	if ( ! in_array( $hv, $classes, true ) ) {
		$classes[] = $hv;
	}
}

/* Filters (all optional except class; invalid values reset to ''). */
$cls = isset( $_GET['cls'] ) ? sanitize_text_field( wp_unslash( $_GET['cls'] ) ) : '';
if ( ! in_array( $cls, $classes, true ) ) {
	$cls = '';
}
$sec = isset( $_GET['sec'] ) ? sanitize_text_field( wp_unslash( $_GET['sec'] ) ) : '';
$grp = isset( $_GET['grp'] ) ? sanitize_text_field( wp_unslash( $_GET['grp'] ) ) : '';
$q = isset( $_GET['q'] ) ? mb_substr( trim( sanitize_text_field( wp_unslash( $_GET['q'] ) ) ), 0, 60 ) : '';
$pg = isset( $_GET['pg'] ) ? max( 1, absint( $_GET['pg'] ) ) : 1;

$sections = array();
$groups = array();
$students_all = array();
if ( '' !== $cls ) {
	$sections = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT DISTINCT ms.meta_value FROM {$wpdb->postmeta} m INNER JOIN {$wpdb->posts} p ON p.ID = m.post_id INNER JOIN {$wpdb->postmeta} ms ON ms.post_id = p.ID AND ms.meta_key = '_ut_section' WHERE p.post_type = 'ut_student' AND p.post_status = 'publish' AND m.meta_key = '_ut_class' AND m.meta_value = %s AND ms.meta_value <> '' ORDER BY ms.meta_value ASC",
			$cls
		)
	);
	$groups = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT DISTINCT mg.meta_value FROM {$wpdb->postmeta} m INNER JOIN {$wpdb->posts} p ON p.ID = m.post_id INNER JOIN {$wpdb->postmeta} mg ON mg.post_id = p.ID AND mg.meta_key = '_ut_group' WHERE p.post_type = 'ut_student' AND p.post_status = 'publish' AND m.meta_key = '_ut_class' AND m.meta_value = %s AND mg.meta_value <> '' ORDER BY mg.meta_value ASC",
			$cls
		)
	);
	if ( ! in_array( $sec, $sections, true ) ) {
		$sec = '';
	}
	if ( ! in_array( $grp, $groups, true ) ) {
		$grp = '';
	}
	$mq = array( 'relation' => 'AND', array( 'key' => '_ut_class', 'value' => $cls, 'compare' => '=' ) );
	if ( '' !== $sec ) {
		$mq[] = array( 'key' => '_ut_section', 'value' => $sec, 'compare' => '=' );
	}
	if ( '' !== $grp ) {
		$mq[] = array( 'key' => '_ut_group', 'value' => $grp, 'compare' => '=' );
	}
	$base = array( 'post_type' => 'ut_student', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC', 'meta_query' => $mq, 'no_found_rows' => true );
	if ( '' !== $q ) {
		$by_title = get_posts( array_merge( $base, array( 's' => $q, 'fields' => 'ids' ) ) );
		$qv = array_unique( array_filter( array( $q, uturn_bn( $q ), function_exists( 'uturn_bn_to_en' ) ? uturn_bn_to_en( $q ) : $q ) ) );
		$or = array( 'relation' => 'OR' );
		foreach ( $qv as $v ) {
			$or[] = array( 'key' => '_ut_roll', 'value' => $v, 'compare' => 'LIKE' );
		}
		$mq2 = $mq;
		$mq2[] = $or;
		$by_roll = get_posts( array_merge( $base, array( 'meta_query' => $mq2, 'fields' => 'ids' ) ) );
		$ids = array_unique( array_merge( (array) $by_title, (array) $by_roll ) );
		$students_all = $ids ? get_posts(
			array( 'post_type' => 'ut_student', 'post_status' => 'publish', 'post__in' => $ids, 'orderby' => 'title', 'order' => 'ASC', 'posts_per_page' => -1, 'no_found_rows' => true )
		) : array();
	} else {
		$students_all = get_posts( $base );
	}
}

$per = 24;
$total = count( $students_all );
$pages = max( 1, (int) ceil( $total / $per ) );
$pg = min( $pg, $pages );
$page_items = array_slice( $students_all, ( $pg - 1 ) * $per, $per );
$base_url = get_permalink();
$keep = array();
if ( '' !== $cls ) {
	$keep['cls'] = $cls;
}
if ( '' !== $sec ) {
	$keep['sec'] = $sec;
}
if ( '' !== $grp ) {
	$keep['grp'] = $grp;
}
if ( '' !== $q ) {
	$keep['q'] = $q;
}
$num = function ( $n ) use ( $EN ) {
	return $EN ? (string) (int) $n : uturn_bn( $n );
};
?>
<main id="main">
<section class="page-hero"><div class="container page-hero-inner">
<ul class="breadcrumbs"><li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( uturn_t( 'হোম', 'Home' ) ); ?></a></li><li><?php echo esc_html( uturn_t( 'শিক্ষার্থী তালিকা', 'Student Directory' ) ); ?></li></ul>
<h1><?php echo esc_html( uturn_t( 'শিক্ষার্থী তালিকা', 'Student Directory' ) ); ?></h1><p><?php echo esc_html( uturn_t( 'শ্রেণি ও শাখা অনুযায়ী আমাদের শিক্ষার্থীদের খুঁজুন।', 'Find our students by class and section.' ) ); ?></p>
</div></section>
<section class="section"><div class="container">
<form class="filter-card" method="get" action="<?php echo esc_url( $base_url ); ?>" role="search" aria-label="<?php echo esc_attr( uturn_t( 'শিক্ষার্থী ফিল্টার', 'Student filters' ) ); ?>">
<div class="filter-row">
<label class="filter-field"><span><?php echo esc_html( uturn_t( 'শ্রেণি', 'Class' ) ); ?> *</span>
<select class="select" name="cls" id="st-class" required aria-label="<?php echo esc_attr( uturn_t( 'শ্রেণি নির্বাচন করুন', 'Select a class' ) ); ?>">
<option value=""><?php echo esc_html( uturn_t( '— শ্রেণি নির্বাচন করুন —', '— Select a class —' ) ); ?></option>
<?php foreach ( $classes as $c ) : ?><option value="<?php echo esc_attr( $c ); ?>"<?php selected( $cls, $c ); ?>><?php echo esc_html( $c ); ?></option><?php endforeach; ?>
</select></label>
<?php if ( '' !== $cls ) : ?>
<label class="filter-field"><span><?php echo esc_html( uturn_t( 'শাখা', 'Section' ) ); ?></span>
<select class="select" name="sec" aria-label="<?php echo esc_attr( uturn_t( 'শাখা', 'Section' ) ); ?>">
<option value=""><?php echo esc_html( uturn_t( 'সকল শাখা', 'All sections' ) ); ?></option>
<?php foreach ( $sections as $s ) : ?><option value="<?php echo esc_attr( $s ); ?>"<?php selected( $sec, $s ); ?>><?php echo esc_html( $s ); ?></option><?php endforeach; ?>
</select></label>
<?php if ( count( $groups ) ) : ?>
<label class="filter-field"><span><?php echo esc_html( uturn_t( 'বিভাগ', 'Group' ) ); ?></span>
<select class="select" name="grp" aria-label="<?php echo esc_attr( uturn_t( 'বিভাগ', 'Group' ) ); ?>">
<option value=""><?php echo esc_html( uturn_t( 'সকল বিভাগ', 'All groups' ) ); ?></option>
<?php foreach ( $groups as $g ) : ?><option value="<?php echo esc_attr( $g ); ?>"<?php selected( $grp, $g ); ?>><?php echo esc_html( $g ); ?></option><?php endforeach; ?>
</select></label>
<?php endif; ?>
<label class="filter-field filter-search"><span><?php echo esc_html( uturn_t( 'খুঁজুন', 'Search' ) ); ?></span>
<input class="input" type="search" name="q" value="<?php echo esc_attr( $q ); ?>" placeholder="<?php echo esc_attr( uturn_t( 'নাম বা রোল দিয়ে খুঁজুন…', 'Search by name or roll…' ) ); ?>" aria-label="<?php echo esc_attr( uturn_t( 'শিক্ষার্থী খুঁজুন', 'Search students' ) ); ?>">
</label>
<?php endif; ?>
<div class="filter-actions"><button class="btn btn-primary" type="submit"><?php echo esc_html( uturn_t( 'দেখুন', 'Show' ) ); ?></button>
<?php if ( '' !== $cls ) : ?><a class="btn btn-outline" href="<?php echo esc_url( $base_url ); ?>"><?php echo esc_html( uturn_t( 'রিসেট', 'Reset' ) ); ?></a><?php endif; ?></div>
</div>
</form>
<?php if ( '' === $cls ) : ?>
<div class="empty-state"><div class="big">🎒</div><h3><?php echo esc_html( uturn_t( 'শ্রেণি নির্বাচন করুন', 'Select a class' ) ); ?></h3><p><?php echo esc_html( uturn_t( 'উপরের তালিকা থেকে শ্রেণি বেছে নিন — সেই শ্রেণির শিক্ষার্থীদের দেখানো হবে।', 'Pick a class above — only that class’s students will be shown.' ) ); ?></p></div>
<?php else : ?>
<p class="muted" id="st-count" role="status"><?php echo esc_html( $num( $total ) . uturn_t( ' জন শিক্ষার্থী', ' students' ) . ' · ' . uturn_t( 'শ্রেণি', 'Class' ) . ' ' . $cls . ( '' !== $sec ? uturn_t( ', শাখা ', ', Section ' ) . $sec : '' ) . ( $pages > 1 ? ' · ' . uturn_t( 'পৃষ্ঠা', 'Page' ) . ' ' . $num( $pg ) . '/' . $num( $pages ) : '' ) ); ?></p>
<?php if ( ! count( $page_items ) ) : ?>
<div class="empty-state"><div class="big">🔍</div><h3><?php echo esc_html( uturn_t( 'কোনো শিক্ষার্থী পাওয়া যায়নি', 'No students found' ) ); ?></h3><p><?php echo esc_html( uturn_t( 'অন্য নাম, রোল বা ফিল্টার দিয়ে চেষ্টা করুন।', 'Try another name, roll or filter.' ) ); ?></p></div>
<?php else : ?>
<div class="teachers-grid" id="students-grid">
<?php
foreach ( $page_items as $s ) :
	$scls = get_post_meta( $s->ID, '_ut_class', true );
	$roll = get_post_meta( $s->ID, '_ut_roll', true );
	$ssec = get_post_meta( $s->ID, '_ut_section', true );
	?>
<article class="teacher-card person-card"><?php echo uturn_person_photo( $s->post_title, uturn_photo_url( $s->ID, '_ut_photo', 'ut-person' ), get_post_meta( $s->ID, '_ut_bg', true ), '' ); // phpcs:ignore ?><div class="p-body"><h3><?php echo esc_html( $s->post_title ); ?></h3><div class="desig"><?php echo esc_html( $scls ); ?> <?php echo esc_html( uturn_t( 'শ্রেণি', 'Class' ) ); ?><?php echo $ssec ? ' · ' . esc_html( uturn_t( 'শাখা', 'Section' ) ) . ' ' . esc_html( $ssec ) : ''; ?></div><div class="subj"><?php echo esc_html( uturn_t( 'রোল', 'Roll' ) ); ?>: <?php echo esc_html( $EN ? $roll : uturn_bn( $roll ) ); ?></div></div></article>
<?php endforeach; ?>
</div>
<?php if ( $pages > 1 ) : ?>
<nav class="pagination" aria-label="<?php echo esc_attr( uturn_t( 'পৃষ্ঠা', 'Pages' ) ); ?>">
<?php
	$win = array();
	foreach ( range( 1, $pages ) as $i ) {
		if ( 1 === $i || $pages === $i || abs( $i - $pg ) <= 2 ) {
			$win[] = $i;
		} elseif ( '…' !== end( $win ) ) {
			$win[] = '…';
		}
	}
if ( $pg > 1 ) {
	echo '<a href="' . esc_url( add_query_arg( array_merge( $keep, array( 'pg' => $pg - 1 ) ), $base_url ) ) . '" aria-label="' . esc_attr( uturn_t( 'আগের পৃষ্ঠা', 'Previous page' ) ) . '">‹</a>';
}
foreach ( $win as $i ) {
	if ( '…' === $i ) {
		echo '<span class="dots" aria-hidden="true">…</span>';
		continue;
	}
	echo '<a href="' . esc_url( add_query_arg( array_merge( $keep, array( 'pg' => $i ) ), $base_url ) ) . '"' . ( $i === $pg ? ' class="active" aria-current="page"' : '' ) . '>' . esc_html( $num( $i ) ) . '</a>';
}
if ( $pg < $pages ) {
	echo '<a href="' . esc_url( add_query_arg( array_merge( $keep, array( 'pg' => $pg + 1 ) ), $base_url ) ) . '" aria-label="' . esc_attr( uturn_t( 'পরের পৃষ্ঠা', 'Next page' ) ) . '">›</a>';
}
?>
</nav>
<?php endif; ?>
<?php endif; ?>
<?php endif; ?>
</div></section>
</main>
<script>
(function () {
  var c = document.getElementById('st-class');
  if (c) { c.addEventListener('change', function () { if (c.value) { c.form.submit(); } }); }
  var f = document.querySelector('.filter-card');
  if (f) {
    f.querySelectorAll('select[name="sec"],select[name="grp"]').forEach(function (s) {
      s.addEventListener('change', function () { f.submit(); });
    });
  }
})();
</script>
<?php
get_footer();
