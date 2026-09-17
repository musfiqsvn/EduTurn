<?php
/**
 * EduTurn — Teacher profile: card + info table + bio + colleagues.
 */
defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) : the_post();
	$id = get_the_ID();
	$g = function ( $k ) use ( $id ) {
		return get_post_meta( $id, $k, true );
	};
	$photo = function_exists( 'uturn_photo_url' ) ? uturn_photo_url( $id, '_ut_photo', 'ut-person' ) : uturn_img( $g( '_ut_photo' ), '' );
	$bg = $g( '_ut_bg' ) ? $g( '_ut_bg' ) : '#0B4EA8';
	$terms = get_the_terms( $id, 'ut_department' );
	$dept = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';
	$show_ph = function_exists( 'uturn_opt' ) ? (string) uturn_opt( 'show_teacher_phone', '1' ) : '1';
	$subj_text = function_exists( 'uturn_post_subjects_text' ) ? uturn_post_subjects_text( $id ) : $g( '_ut_subject' );
	?>

<main id="main"><div class="container">
  <nav class="breadcrumb" aria-label="<?php echo esc_attr( uturn_t( 'ব্রেডক্রাম্ব', 'Breadcrumb' ) ); ?>"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( uturn_t( 'হোম', 'Home' ) ); ?></a><span>›</span><a href="<?php echo esc_url( uturn_url( 'teachers' ) ); ?>"><?php echo esc_html( uturn_t( 'শিক্ষক', 'Teachers' ) ); ?></a><span>›</span><span><?php the_title(); ?></span></nav>
  <article class="single-layout">
    <div class="single-main">
      <div class="card teacher-profile">
        <?php if ( $photo ) : ?>
          <img class="tp-photo" src="<?php echo esc_url( $photo ); ?>" alt="<?php the_title_attribute(); ?>">
        <?php else : ?>
          <span class="monogram monogram-xl" style="background:<?php echo esc_attr( $bg ); ?>"><?php echo esc_html( uturn_initials( get_the_title() ) ); ?></span>
        <?php endif; ?>
        <div>
          <h1><?php the_title(); ?></h1>
          <p class="muted"><?php echo esc_html( $g( '_ut_designation' ) ); ?><?php echo $dept ? ' · ' . esc_html( $dept ) : ''; ?></p>
          <?php if ( $subj_text ) : ?><span class="badge-soft">📖 <?php echo esc_html( $subj_text ); ?></span><?php endif; ?>
        </div>
      </div>
      <div class="table-wrap" style="margin-top:20px">
        <table class="info-table">
          <tbody>
            <tr><th scope="row"><?php echo esc_html( uturn_t( 'বিষয়', 'Subject' ) ); ?></th><td><?php echo esc_html( $subj_text ? $subj_text : '—' ); ?></td></tr>
            <tr><th scope="row"><?php echo esc_html( uturn_t( 'শিক্ষাগত যোগ্যতা', 'Education' ) ); ?></th><td><?php echo esc_html( $g( '_ut_education' ) ? $g( '_ut_education' ) : '—' ); ?></td></tr>
            <tr><th scope="row"><?php echo esc_html( uturn_t( 'অভিজ্ঞতা', 'Experience' ) ); ?></th><td><?php echo esc_html( $g( '_ut_experience' ) ? $g( '_ut_experience' ) : '—' ); ?></td></tr>
            <tr><th scope="row"><?php echo esc_html( uturn_t( 'বিভাগ', 'Department' ) ); ?></th><td><?php echo esc_html( $dept ? $dept : '—' ); ?></td></tr>
            <?php if ( '1' === $show_ph ) : ?>
            <tr><th scope="row"><?php echo esc_html( uturn_t( 'মোবাইল', 'Mobile' ) ); ?></th><td><?php echo $g( '_ut_phone' ) ? '<a href="tel:' . esc_attr( $g( '_ut_phone' ) ) . '">' . esc_html( $g( '_ut_phone' ) ) . '</a>' : '—'; ?></td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <?php if ( get_the_content() ) : ?>
        <h3 style="margin-top:20px"><?php echo esc_html( uturn_t( 'পরিচিতি', 'Profile' ) ); ?></h3>
        <div class="card prose"><?php the_content(); ?></div>
      <?php endif; ?>
    </div>
    <aside class="single-side">
      <div class="card">
        <h3>👥 <?php echo esc_html( uturn_t( 'অন্যান্য শিক্ষক', 'Other Teachers' ) ); ?></h3>
        <ul class="link-list">
          <?php
          $others = new WP_Query( array( 'post_type' => 'ut_teacher', 'posts_per_page' => 5, 'post__not_in' => array( $id ), 'orderby' => 'rand' ) );
          while ( $others->have_posts() ) : $others->the_post();
            ?>
            <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a><br><small class="muted"><?php echo esc_html( get_post_meta( get_the_ID(), '_ut_designation', true ) ); ?></small></li>
          <?php endwhile; wp_reset_postdata(); ?>
        </ul>
      </div>
      <div class="help-card">
        <h3>🔗 <?php echo esc_html( uturn_t( 'দ্রুত লিংক', 'Quick Links' ) ); ?></h3>
        <ul class="link-list">
          <li><a href="<?php echo esc_url( uturn_url( 'teachers' ) ); ?>"><?php echo esc_html( uturn_t( 'সকল শিক্ষক', 'All Teachers' ) ); ?></a></li>
          <li><a href="<?php echo esc_url( uturn_url( 'routine' ) ); ?>"><?php echo esc_html( uturn_t( 'ক্লাস রুটিন', 'Class Routine' ) ); ?></a></li>
          <li><a href="<?php echo esc_url( uturn_url( 'academic' ) ); ?>"><?php echo esc_html( uturn_t( 'একাডেমিক', 'Academics' ) ); ?></a></li>
        </ul>
      </div>
    </aside>
  </article>
</div></main>
<?php endwhile; ?>
<?php get_footer(); ?>
