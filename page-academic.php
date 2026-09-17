<?php
/**
 * Template Name: Academic (একাডেমিক)
 */
defined( 'ABSPATH' ) || exit;
get_header();
$cal = array();
foreach ( uturn_lines( uturn_opt( 'calendar_lines' ) ) as $ln ) {
	$p = array_map( 'trim', explode( '|', $ln ) );
	if ( '' !== $p[0] ) {
		$cal[] = array( 'm' => $p[0], 'e' => isset( $p[1] ) ? $p[1] : '' );
	}
}
$cls_terms = function_exists( 'uturn_class_terms' ) ? uturn_class_terms() : get_terms( array( 'taxonomy' => 'ut_class', 'hide_empty' => false ) );
if ( is_wp_error( $cls_terms ) ) {
	$cls_terms = array();
}
$year_now = function_exists( 'uturn_bn' ) ? uturn_bn( (int) current_time( 'Y' ) ) : current_time( 'Y' );
?>

<main id="main"><div class="container">
  <nav class="breadcrumb" aria-label="<?php echo esc_attr( uturn_t( 'ব্রেডক্রাম্ব', 'Breadcrumb' ) ); ?>"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( uturn_t( 'হোম', 'Home' ) ); ?></a><span>›</span><span><?php echo esc_html( uturn_t( 'একাডেমিক', 'Academics' ) ); ?></span></nav>
  <div class="page-head">
    <h1><?php echo esc_html( uturn_t( 'একাডেমিক কার্যক্রম', 'Academic Activities' ) ); ?></h1>
    <p class="muted"><?php echo esc_html( uturn_t( 'পাঠ্যক্রম, মূল্যায়ন পদ্ধতি ও শিক্ষাবর্ষের কর্মসূচি', 'Curriculum, evaluation system and the academic year programme' ) ); ?></p>
  </div>

  <?php if ( $cls_terms ) : ?>
  <h2 class="section-title" id="classes">🏫 <?php echo esc_html( uturn_t( 'শ্রেণিসমূহ', 'Classes' ) ); ?></h2>
  <div class="chip-row" style="margin-bottom:8px">
    <?php foreach ( $cls_terms as $t ) : ?>
      <span class="chip"><?php echo esc_html( $t->name ); ?></span>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <h2 class="section-title">📖 <?php echo esc_html( uturn_t( 'আমাদের প্রোগ্রামসমূহ', 'Our Programmes' ) ); ?></h2>
  <div class="program-grid">
    <?php foreach ( uturn_lines( uturn_opt( 'academic_programs_lines' ) ) as $ln ) : $pp = array_map( 'trim', explode( '|', $ln ) ); if ( count( $pp ) < 4 ) continue; ?>
      <div class="card program-card"><div class="prog-icon"><?php echo esc_html( $pp[0] ); ?></div><h3><?php echo esc_html( $pp[1] ); ?></h3><p class="muted"><?php echo esc_html( $pp[2] ); ?></p><ul class="tick-list"><?php foreach ( explode( ';', $pp[3] ) as $ft ) : $ft = trim( $ft ); if ( '' === $ft ) continue; ?><li><?php echo esc_html( $ft ); ?></li><?php endforeach; ?></ul></div>
    <?php endforeach; ?>
  </div>

  <div class="grid grid-2" style="margin-top:28px">
    <div class="card prose">
      <h2>📋 <?php echo esc_html( uturn_t( 'পাঠ্যক্রম', 'Curriculum' ) ); ?></h2>
      <?php echo wpautop( esc_html( uturn_opt( 'academic_curriculum' ) ) ); ?>
    </div>
    <div class="card prose">
      <h2>📝 <?php echo esc_html( uturn_t( 'মূল্যায়ন পদ্ধতি', 'Evaluation System' ) ); ?></h2>
      <?php echo wpautop( esc_html( uturn_opt( 'academic_evaluation' ) ) ); ?>
    </div>
  </div>

  <h2 class="section-title" style="margin-top:32px">🗓️ <?php echo esc_html( uturn_t( 'পরীক্ষার সময়সূচি', 'Exam Schedule' ) ); ?></h2>
  <div class="table-wrap">
    <table class="info-table">
      <thead><tr><th scope="col"><?php echo esc_html( uturn_t( 'পরীক্ষা', 'Exam' ) ); ?></th><th scope="col"><?php echo esc_html( uturn_t( 'সম্ভাব্য সময়', 'Probable Time' ) ); ?></th><th scope="col"><?php echo esc_html( uturn_t( 'অন্তর্ভুক্ত শ্রেণি', 'Included Classes' ) ); ?></th></tr></thead>
      <tbody>
        <?php foreach ( uturn_lines( uturn_opt( 'academic_exams_lines' ) ) as $ln ) : $er = array_map( 'trim', explode( '|', $ln ) ); if ( '' === $er[0] ) continue; ?>
          <tr><td><?php echo esc_html( $er[0] ); ?></td><td><?php echo esc_html( isset( $er[1] ) ? $er[1] : '' ); ?></td><td><?php echo esc_html( isset( $er[2] ) ? $er[2] : '' ); ?></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <h2 class="section-title" id="calendar" style="margin-top:32px">📅 <?php echo esc_html( uturn_t( 'একাডেমিক ক্যালেন্ডার', 'Academic Calendar' ) ); ?> <?php echo esc_html( 'en' === uturn_lang() ? current_time( 'Y' ) : $year_now ); ?></h2>
  <div class="table-wrap">
    <table class="info-table">
      <thead><tr><th scope="col"><?php echo esc_html( uturn_t( 'মাস', 'Month' ) ); ?></th><th scope="col"><?php echo esc_html( uturn_t( 'কর্মসূচি', 'Programme' ) ); ?></th></tr></thead>
      <tbody>
        <?php foreach ( $cal as $c ) : ?>
          <tr><td><strong><?php echo esc_html( $c['m'] ); ?></strong></td><td><?php echo esc_html( $c['e'] ); ?></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="apply-cta">
    <div><h2><?php echo esc_html( uturn_opt( 'adm_title' ) ); ?></h2><p><?php echo esc_html( uturn_opt( 'adm_sub' ) ); ?></p></div>
    <div class="cta-btns"><a class="btn btn-light" href="<?php echo esc_url( uturn_url( 'apply' ) ); ?>"><?php echo esc_html( uturn_t( 'অনলাইনে আবেদন করুন', 'Apply Online' ) ); ?></a><a class="btn btn-outline-light" href="<?php echo esc_url( uturn_url( 'routine' ) ); ?>"><?php echo esc_html( uturn_t( 'ক্লাস রুটিন', 'Class Routine' ) ); ?></a></div>
  </div>
</div></main>
<?php get_footer(); ?>
