<?php
/**
 * EduTurn — Single notice (article + attachment + sidebars).
 */
get_header();
the_post();
$id = get_the_ID();
$ts = get_the_terms( $id, 'ut_notice_cat' );
$slug = ( $ts && ! is_wp_error( $ts ) ) ? $ts[0]->slug : 'general';
$tn = ( $ts && ! is_wp_error( $ts ) ) ? $ts[0]->name : 'সাধারণ';
$datebn = get_post_meta( $id, '_ut_date_bn', true );
if ( ! $datebn ) { $datebn = uturn_bn_date( get_the_date( 'Y-m-d' ) ); }
$att = get_post_meta( $id, '_ut_attachment', true );
if ( is_numeric( $att ) && $att > 0 ) { $att = wp_get_attachment_url( (int) $att ); }
elseif ( is_string( $att ) && 0 === strpos( $att, 'assets/' ) ) { $att = UTURN_URI . '/' . $att; }
$archive = get_post_type_archive_link( 'ut_notice' );
$cats = get_terms( array( 'taxonomy' => 'ut_notice_cat', 'hide_empty' => false ) );
if ( is_wp_error( $cats ) ) { $cats = array(); }
$recent = get_posts( array( 'post_type' => 'ut_notice', 'posts_per_page' => 5, 'post_status' => 'publish', 'exclude' => array( $id ) ) );
?>
<main id="main">
<section class="page-hero"><div class="container page-hero-inner">
<ul class="breadcrumbs"><li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( uturn_t( 'হোম', 'Home' ) ); ?></a></li><li><a href="<?php echo esc_url( $archive ); ?>"><?php echo esc_html( uturn_t( 'নোটিশ', 'Notice' ) ); ?></a></li><li><?php echo esc_html( uturn_t( 'বিস্তারিত', 'Details' ) ); ?></li></ul>
<h1 id="n-title"><?php the_title(); ?></h1><p id="n-sub"><?php echo esc_html( $datebn ); ?></p>
</div></section>
<section class="section"><div class="container">
<div class="article-layout">
<article class="article-card"><div class="article-body">
<div class="article-meta"><span id="n-cat"><span class="tag tag-<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $tn ); ?></span></span><span id="n-date"><?php echo esc_html( uturn_t( 'প্রকাশ:', 'Published:' ) ); ?> <?php echo esc_html( $datebn ); ?></span></div>
<div class="prose" id="n-body"><?php the_content(); ?></div>
<div id="n-attach"><?php if ( $att ) : ?><div class="attach-box"><?php echo uturn_icon( 'download' ); ?><div style="flex:1"><strong><?php echo esc_html( uturn_t( 'সংযুক্তি', 'Attachment' ) ); ?></strong><br><span class="muted small"><?php echo esc_html( uturn_t( 'PDF ডকুমেন্ট', 'PDF document' ) ); ?></span></div><a class="btn btn-primary btn-sm" href="<?php echo esc_url( $att ); ?>" download><?php echo esc_html( uturn_t( 'ডাউনলোড', 'Download' ) ); ?></a></div><?php endif; ?></div>
</div></article>
<aside>
<div class="sidebar-card"><h3><?php echo esc_html( uturn_t( 'সাম্প্রতিক নোটিশ', 'Recent Notices' ) ); ?></h3><div id="recent"><?php foreach ( $recent as $r ) :
	$rdate = get_post_meta( $r->ID, '_ut_date_bn', true );
	if ( ! $rdate ) { $rdate = uturn_bn_date( get_the_date( 'Y-m-d', $r ) ); }
	?><a class="side-link" href="<?php echo esc_url( get_permalink( $r ) ); ?>"><span class="dot"></span><span><?php echo esc_html( $r->post_title ); ?><br><span class="muted small"><?php echo esc_html( $rdate ); ?></span></span></a><?php endforeach; ?></div></div>
<div class="sidebar-card"><h3><?php echo esc_html( uturn_t( 'ক্যাটাগরি', 'Categories' ) ); ?></h3><div id="cats"><?php foreach ( $cats as $c ) : ?><a class="side-link" href="<?php echo esc_url( add_query_arg( 'cat', $c->slug, $archive ) ); ?>"><span class="dot"></span><?php echo esc_html( $c->name ); ?></a><?php endforeach; ?></div></div>
</aside>
</div>
</div></section>
</main>
<?php
get_footer();
