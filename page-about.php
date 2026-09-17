<?php
/**
 * Template Name: About (আমাদের সম্পর্কে)
 */
defined( 'ABSPATH' ) || exit;
get_header();
$photo = uturn_opt( 'principal_photo' );
if ( ! $photo ) {
	$photo = UTURN_URI . '/assets/images/principal.jpg';
}
$mgmt = array();
foreach ( uturn_lines( uturn_opt( 'management_lines' ) ) as $ln ) {
	$p = array_map( 'trim', explode( '|', $ln ) );
	if ( '' !== $p[0] ) {
		$mgmt[] = array( 'name' => $p[0], 'role' => isset( $p[1] ) ? $p[1] : '', 'bg' => isset( $p[2] ) ? $p[2] : '#0B4EA8' );
	}
}
$board = function_exists( 'uturn_board_members' ) ? uturn_board_members( true ) : array();
$est   = uturn_opt( 'established', '2001' );
?>

<main id="main"><div class="container">
  <nav class="breadcrumb" aria-label="<?php echo esc_attr( uturn_t( 'ব্রেডক্রাম্ব', 'Breadcrumb' ) ); ?>"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( uturn_t( 'হোম', 'Home' ) ); ?></a><span>›</span><span><?php echo esc_html( uturn_t( 'আমাদের সম্পর্কে', 'About Us' ) ); ?></span></nav>
  <div class="page-head">
    <h1><?php echo esc_html( uturn_t( 'আমাদের সম্পর্কে', 'About Us' ) ); ?></h1>
    <p class="muted"><?php echo esc_html( uturn_lang() === 'en' ? uturn_opt( 'school_name_en' ) : uturn_opt( 'school_name_bn' ) ); ?> — <?php echo esc_html( uturn_t( uturn_bn( $est ) . ' সাল থেকে মানসম্মত শিক্ষায় অগ্রণী', 'Leading quality education since ' . $est ) ); ?></p>
  </div>

  <div class="grid grid-2">
    <div class="card prose" id="history">
      <h2>🏫 <?php echo esc_html( uturn_t( 'আমাদের ইতিহাস', 'Our History' ) ); ?></h2>
      <?php echo wpautop( esc_html( str_replace( '%SCHOOL%', uturn_opt( 'school_name_bn' ), uturn_opt( 'about_history' ) ) ) ); ?>
    </div>
    <div class="card prose" id="mission">
      <h2>🎯 <?php echo esc_html( uturn_t( 'লক্ষ্য ও উদ্দেশ্য', 'Vision & Mission' ) ); ?></h2>
      <p><strong><?php echo esc_html( uturn_t( 'রূপকল্প:', 'Vision:' ) ); ?></strong> <?php echo esc_html( uturn_opt( 'about_vision' ) ); ?></p>
      <p><strong><?php echo esc_html( uturn_t( 'অভিলক্ষ্য:', 'Mission:' ) ); ?></strong> <?php echo esc_html( uturn_opt( 'about_mission' ) ); ?></p>
      <ul class="tick-list"><?php foreach ( uturn_lines( uturn_opt( 'about_goals_lines' ) ) as $gl ) : ?><li><?php echo esc_html( $gl ); ?></li><?php endforeach; ?></ul>
    </div>
  </div>

  <h2 class="section-title" id="principal" style="margin-top:32px">👨‍🏫 <?php echo esc_html( uturn_t( 'প্রধান শিক্ষকের বাণী', "Head Teacher\'s Message" ) ); ?></h2>
  <div class="card principal-full">
    <div class="principal-photo"><img src="<?php echo esc_url( $photo ); ?>" alt="<?php echo esc_attr( uturn_opt( 'principal_name' ) ); ?>"></div>
    <div>
      <h3><?php echo esc_html( uturn_opt( 'principal_name' ) ); ?></h3>
      <p class="muted"><?php echo esc_html( uturn_opt( 'principal_title' ) ); ?> · <?php echo esc_html( uturn_opt( 'principal_edu' ) ); ?></p>
      <?php echo wpautop( esc_html( uturn_opt( 'principal_full' ) ) ); ?>
    </div>
  </div>

  <h2 class="section-title" id="management" style="margin-top:32px">🧑‍💼 <?php echo esc_html( uturn_t( 'পরিচালনা পর্ষদ', 'Governing Body' ) ); ?></h2>
  <?php if ( $board ) : ?>
  <div class="board-grid">
    <?php foreach ( $board as $i => $m ) { echo uturn_board_card_html( $m, $i ); // phpcs:ignore ?>
    <?php } ?>
  </div>
  <?php else : ?>
  <div class="mgmt-grid">
    <?php foreach ( $mgmt as $m ) : ?>
      <div class="mgmt-card">
        <span class="monogram" style="background:<?php echo esc_attr( $m['bg'] ); ?>"><?php echo esc_html( uturn_initials( $m['name'] ) ); ?></span>
        <div><strong><?php echo esc_html( $m['name'] ); ?></strong><p class="muted small"><?php echo esc_html( $m['role'] ); ?></p></div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <h2 class="section-title" style="margin-top:32px">🏆 <?php echo esc_html( uturn_t( 'আমাদের অর্জন', 'Our Achievements' ) ); ?></h2>
  <div class="ach-grid ach-light">
    <?php
    $ach = new WP_Query( array( 'post_type' => 'ut_achievement', 'posts_per_page' => -1 ) );
    while ( $ach->have_posts() ) : $ach->the_post();
      ?>
      <div class="ach-card"><div class="ach-num"><?php echo esc_html( get_post_meta( get_the_ID(), '_ut_num', true ) ); ?></div><h3><?php the_title(); ?></h3><p class="muted small"><?php echo esc_html( wp_strip_all_tags( get_the_content() ) ); ?> · <?php echo esc_html( get_post_meta( get_the_ID(), '_ut_year', true ) ); ?></p></div>
    <?php endwhile; wp_reset_postdata(); ?>
  </div>

  <div class="apply-cta">
    <div><h2><?php echo esc_html( uturn_opt( 'adm_title' ) ); ?></h2><p><?php echo esc_html( uturn_opt( 'adm_sub' ) ); ?></p></div>
    <div class="cta-btns"><a class="btn btn-light" href="<?php echo esc_url( uturn_url( 'apply' ) ); ?>"><?php echo esc_html( uturn_t( 'অনলাইনে আবেদন করুন', 'Apply Online' ) ); ?></a><a class="btn btn-outline-light" href="<?php echo esc_url( uturn_url( 'admission' ) ); ?>"><?php echo esc_html( uturn_t( 'ভর্তি তথ্য', 'Admission Info' ) ); ?></a></div>
  </div>
</div></main>
<?php get_footer(); ?>
