<?php
/**
 * EduTurn — Album single: masonry grid + theme lightbox + other albums.
 */
defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) : the_post();
	$id = get_the_ID();
	$photos = array();
	foreach ( uturn_lines( get_post_meta( $id, '_ut_photos', true ) ) as $ln ) {
		$photos[] = uturn_img( $ln );
	}
	$cover = uturn_img( get_post_meta( $id, '_ut_cover', true ) );
	if ( $cover && ! in_array( $cover, $photos, true ) ) {
		array_unshift( $photos, $cover );
	}
	?>

<main id="main"><div class="container">
  <nav class="breadcrumb" aria-label="<?php echo esc_attr( uturn_t( 'ব্রেডক্রাম্ব', 'Breadcrumb' ) ); ?>"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( uturn_t( 'হোম', 'Home' ) ); ?></a><span>›</span><a href="<?php echo esc_url( get_post_type_archive_link( 'ut_album' ) ); ?>"><?php echo esc_html( uturn_t( 'গ্যালারি', 'Gallery' ) ); ?></a><span>›</span><span><?php the_title(); ?></span></nav>
  <div class="page-head">
    <h1><?php the_title(); ?></h1>
    <p class="muted"><?php echo esc_html( get_post_meta( $id, '_ut_date_bn', true ) ); ?> · <?php echo esc_html( 'en' === uturn_lang() ? count( $photos ) . ' photos' : uturn_bn( count( $photos ) ) . 'টি ছবি' ); ?></p>
  </div>
  <?php if ( get_the_content() ) : ?><div class="card prose" style="margin-bottom:20px"><?php the_content(); ?></div><?php endif; ?>
  <div class="masonry" id="masonry">
    <?php foreach ( $photos as $i => $src ) : ?>
      <figure><a data-lightbox="album" data-full="<?php echo esc_url( $src ); ?>" data-caption="<?php the_title_attribute(); ?> — <?php echo esc_attr( $i + 1 ); ?>" href="<?php echo esc_url( $src ); ?>"><img src="<?php echo esc_url( $src ); ?>" alt="<?php the_title_attribute(); ?> — <?php echo esc_attr( $i + 1 ); ?>" loading="lazy"></a></figure>
    <?php endforeach; ?>
  </div>

  <h2 class="section-title" style="margin-top:32px">📷 <?php echo esc_html( uturn_t( 'অন্যান্য অ্যালবাম', 'Other Albums' ) ); ?></h2>
  <div class="archive-grid">
    <?php
    $others = new WP_Query( array( 'post_type' => 'ut_album', 'posts_per_page' => 3, 'post__not_in' => array( $id ) ) );
    while ( $others->have_posts() ) : $others->the_post();
      $oid = get_the_ID();
      ?>
      <a class="album-card" href="<?php the_permalink(); ?>">
        <img src="<?php echo esc_url( uturn_img( get_post_meta( $oid, '_ut_cover', true ), UTURN_URI . '/assets/images/campus.jpg' ) ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
        <span class="ab"><h3><?php the_title(); ?></h3><span class="meta"><?php echo esc_html( get_post_meta( $oid, '_ut_date_bn', true ) ); ?></span></span>
      </a>
    <?php endwhile; wp_reset_postdata(); ?>
  </div>
  <p style="margin-top:16px"><a class="btn btn-outline" href="<?php echo esc_url( get_post_type_archive_link( 'ut_album' ) ); ?>"><?php echo esc_html( uturn_t( '← সব অ্যালবাম', '← All Albums' ) ); ?></a></p>
</div></main>
<?php endwhile; ?>
<?php get_footer(); ?>
