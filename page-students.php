<?php
/**
 * Template Name: Students Info (শিক্ষার্থী কর্নার)
 * Static info hub (features + clubs) — the directory lives at /students-list.
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>

<main id="main"><div class="container">
  <nav class="breadcrumb" aria-label="<?php echo esc_attr( uturn_t( 'ব্রেডক্রাম্ব', 'Breadcrumb' ) ); ?>"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( uturn_t( 'হোম', 'Home' ) ); ?></a><span>›</span><span><?php echo esc_html( uturn_t( 'শিক্ষার্থী কর্নার', 'Student Corner' ) ); ?></span></nav>
  <div class="page-head">
    <h1><?php echo esc_html( uturn_t( 'শিক্ষার্থী কর্নার', 'Student Corner' ) ); ?></h1>
    <p class="muted"><?php echo esc_html( uturn_t( 'সুবিধা, ক্লাব ও সহশিক্ষা কার্যক্রম — এক নজরে', 'Facilities, clubs and co-curriculars — at a glance' ) ); ?></p>
  </div>

  <h2 class="section-title">🌟 <?php echo esc_html( uturn_t( 'শিক্ষার্থী সুবিধা', 'Student Facilities' ) ); ?></h2>
  <div class="feature-grid">
    <?php foreach ( uturn_lines( uturn_opt( 'students_features_lines' ) ) as $ln ) : $fp = array_map( 'trim', explode( '|', $ln ) ); if ( count( $fp ) < 3 ) continue; ?>
      <div class="card feature-card"><div class="feat-icon"><?php echo esc_html( $fp[0] ); ?></div><h3><?php echo esc_html( $fp[1] ); ?></h3><p class="muted small"><?php echo esc_html( $fp[2] ); ?></p></div>
    <?php endforeach; ?>
  </div>

  <h2 class="section-title" style="margin-top:32px">🎭 <?php echo esc_html( uturn_t( 'ক্লাব ও সহশিক্ষা কার্যক্রম', 'Clubs & Co-curriculars' ) ); ?></h2>
  <div class="feature-grid">
    <?php foreach ( uturn_lines( uturn_opt( 'students_clubs_lines' ) ) as $ln ) : $cp = array_map( 'trim', explode( '|', $ln ) ); if ( count( $cp ) < 3 ) continue; ?>
      <div class="card feature-card"><div class="feat-icon"><?php echo esc_html( $cp[0] ); ?></div><h3><?php echo esc_html( $cp[1] ); ?></h3><p class="muted small"><?php echo esc_html( $cp[2] ); ?></p></div>
    <?php endforeach; ?>
  </div>

  <div class="grid grid-2" style="margin-top:28px">
    <div class="card">
      <h3>📜 <?php echo esc_html( uturn_t( 'আচরণবিধি সংক্ষেপ', 'Code of Conduct' ) ); ?></h3>
      <ul class="tick-list"><?php foreach ( uturn_lines( uturn_opt( 'students_conduct_lines' ) ) as $cc ) : ?><li><?php echo esc_html( $cc ); ?></li><?php endforeach; ?></ul>
    </div>
    <div class="help-card">
      <h3><?php echo esc_html( uturn_t( 'শিক্ষার্থী পোর্টাল', 'Student Portal' ) ); ?></h3>
      <p class="muted"><?php echo esc_html( uturn_t( 'নিজের ফলাফল, হাজিরা ও ফি দেখতে পোর্টালে লগইন করুন।', 'Log in to the portal to view your results, attendance and fees.' ) ); ?></p>
      <div class="cta-btns"><a class="btn" href="<?php echo esc_url( uturn_url( 'student-portal' ) ); ?>"><?php echo esc_html( uturn_t( 'পোর্টালে যান', 'Go to Portal' ) ); ?></a><a class="btn btn-outline" href="<?php echo esc_url( uturn_url( 'results' ) ); ?>"><?php echo esc_html( uturn_t( 'ফলাফল দেখুন', 'View Results' ) ); ?></a></div>
    </div>
  </div>
</div></main>
<?php get_footer(); ?>
