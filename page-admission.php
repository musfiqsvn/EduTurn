<?php
/**
 * Template Name: Admission (ভর্তি তথ্য)
 */
defined( 'ABSPATH' ) || exit;
get_header();
$parse3 = function ( $key ) {
	$out = array();
	foreach ( uturn_lines( uturn_opt( $key ) ) as $ln ) {
		$p = array_map( 'trim', explode( '|', $ln ) );
		if ( '' !== $p[0] ) {
			$out[] = array( $p[0], isset( $p[1] ) ? $p[1] : '', isset( $p[2] ) ? $p[2] : '' );
		}
	}
	return $out;
};
$seats = $parse3( 'seats_lines' );
$fees = $parse3( 'fees_lines' );
$dates = $parse3( 'adm_dates_lines' );
?>

<main id="main"><div class="container">
  <nav class="breadcrumb" aria-label="<?php echo esc_attr( uturn_t( 'ব্রেডক্রাম্ব', 'Breadcrumb' ) ); ?>"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( uturn_t( 'হোম', 'Home' ) ); ?></a><span>›</span><span><?php echo esc_html( uturn_t( 'ভর্তি', 'Admission' ) ); ?></span></nav>
  <div class="admission-banner">
    <div>
      <span class="badge-soft"><?php echo esc_html( uturn_opt( 'adm_session' ) ); ?></span>
      <h1><?php echo esc_html( uturn_opt( 'adm_title' ) ); ?></h1>
      <p><?php echo esc_html( uturn_opt( 'adm_sub' ) ); ?></p>
      <p>⏰ <?php echo esc_html( uturn_t( 'আবেদনের শেষ তারিখ:', 'Application deadline:' ) ); ?> <strong><?php echo esc_html( uturn_opt( 'deadline_bn' ) ); ?></strong> <span id="admCount" class="count-pill" data-deadline="<?php echo esc_attr( uturn_opt( 'adm_deadline' ) ); ?>" data-lang="<?php echo esc_attr( uturn_lang() ); ?>"></span></p>
      <div class="cta-btns"><a class="btn btn-light" href="<?php echo esc_url( uturn_url( 'apply' ) ); ?>"><?php echo esc_html( uturn_t( 'অনলাইনে আবেদন করুন', 'Apply Online' ) ); ?></a><a class="btn btn-outline-light" href="<?php echo esc_url( uturn_url( 'downloads' ) ); ?>"><?php echo esc_html( uturn_t( 'ভর্তি ফরম ডাউনলোড', 'Download Admission Form' ) ); ?></a></div>
    </div>
  </div>

  <h2 class="section-title" id="guideline" style="margin-top:32px">📝 <?php echo esc_html( uturn_t( 'ভর্তি প্রক্রিয়া', 'Admission Process' ) ); ?></h2>
  <ol class="process-steps">
    <?php $step_i = 0; foreach ( uturn_lines( uturn_opt( 'admission_process_lines' ) ) as $ln ) : $sp = array_map( 'trim', explode( '|', $ln ) ); if ( '' === $sp[0] ) continue; $step_i++; ?>
      <li><span class="step-n"><?php echo esc_html( 'en' === uturn_lang() ? $step_i : uturn_bn( $step_i ) ); ?></span><div><strong><?php echo esc_html( $sp[0] ); ?></strong><p class="muted small"><?php echo esc_html( isset( $sp[1] ) ? $sp[1] : '' ); ?></p></div></li>
    <?php endforeach; ?>
  </ol>

  <div class="grid grid-2" style="margin-top:28px">
    <div>
      <h2 class="section-title">💺 <?php echo esc_html( uturn_t( 'শ্রেণিভিত্তিক আসন', 'Class-wise Seats' ) ); ?></h2>
      <div class="table-wrap"><table class="info-table"><thead><tr><th scope="col"><?php echo esc_html( uturn_t( 'শ্রেণি', 'Class' ) ); ?></th><th scope="col"><?php echo esc_html( uturn_t( 'আসন', 'Seats' ) ); ?></th><th scope="col"><?php echo esc_html( uturn_t( 'বয়স', 'Age' ) ); ?></th></tr></thead>
      <tbody><?php foreach ( $seats as $s ) : ?><tr><td><?php echo esc_html( $s[0] ); ?></td><td><?php echo esc_html( $s[1] ); ?></td><td><?php echo esc_html( $s[2] ); ?></td></tr><?php endforeach; ?></tbody></table></div>
    </div>
    <div>
      <h2 class="section-title">💰 <?php echo esc_html( uturn_t( 'ফি তালিকা', 'Fee Chart' ) ); ?></h2>
      <div class="table-wrap"><table class="info-table"><thead><tr><th scope="col"><?php echo esc_html( uturn_t( 'শ্রেণি', 'Class' ) ); ?></th><th scope="col"><?php echo esc_html( uturn_t( 'ভর্তি ফি', 'Admission Fee' ) ); ?></th><th scope="col"><?php echo esc_html( uturn_t( 'মাসিক বেতন', 'Monthly Tuition' ) ); ?></th></tr></thead>
      <tbody><?php foreach ( $fees as $f ) : ?><tr><td><?php echo esc_html( $f[0] ); ?></td><td><?php echo esc_html( $f[1] ); ?></td><td><?php echo esc_html( $f[2] ); ?></td></tr><?php endforeach; ?></tbody></table></div>
    </div>
  </div>

  <h2 class="section-title" style="margin-top:32px">📅 <?php echo esc_html( uturn_t( 'গুরুত্বপূর্ণ তারিখ', 'Important Dates' ) ); ?></h2>
  <div class="table-wrap"><table class="info-table"><thead><tr><th scope="col"><?php echo esc_html( uturn_t( 'ইভেন্ট', 'Event' ) ); ?></th><th scope="col"><?php echo esc_html( uturn_t( 'তারিখ', 'Date' ) ); ?></th></tr></thead>
  <tbody><?php foreach ( $dates as $d ) : ?><tr><td><?php echo esc_html( $d[0] ); ?></td><td><?php echo esc_html( $d[1] ); ?></td></tr><?php endforeach; ?></tbody></table></div>

  <div class="grid grid-2" style="margin-top:28px">
    <div class="card">
      <h2>📎 <?php echo esc_html( uturn_t( 'প্রয়োজনীয় কাগজপত্র', 'Required Documents' ) ); ?></h2>
      <ul class="tick-list"><?php foreach ( uturn_lines( uturn_opt( 'admission_docs_lines' ) ) as $dc ) : ?><li><?php echo esc_html( $dc ); ?></li><?php endforeach; ?></ul>
    </div>
    <div>
      <h2 class="section-title">❓ <?php echo esc_html( uturn_t( 'ভর্তি বিষয়ক জিজ্ঞাসা', 'Admission FAQs' ) ); ?></h2>
      <div class="faq-list">
        <?php
        $faqs = new WP_Query( array( 'post_type' => 'ut_faq', 'posts_per_page' => -1, 'tax_query' => array( array( 'taxonomy' => 'ut_faq_cat', 'field' => 'slug', 'terms' => array( 'admission', 'fees' ) ) ) ) );
        while ( $faqs->have_posts() ) : $faqs->the_post();
          ?>
          <details class="faq"><summary><?php the_title(); ?></summary><div class="faq-body"><?php the_content(); ?></div></details>
        <?php endwhile; wp_reset_postdata(); ?>
      </div>
    </div>
  </div>
</div></main>

<script>
(function(){var el=document.getElementById('admCount');if(!el||!el.dataset.deadline)return;
  var en=el.dataset.lang==='en';
  var diff=Math.ceil((new Date(el.dataset.deadline+'T23:59:59')-new Date())/864e5);
  el.textContent=diff>0?(en?('Only '+diff+' days left'):('আর মাত্র '+diff+' দিন বাকি')):(diff===0?(en?'Last day today!':'আজই শেষ দিন!'):(en?'Applications closed — wait for the next session':'আবেদন বন্ধ — পরবর্তী সেশনের জন্য অপেক্ষা করুন'));})();
</script>
<?php get_footer(); ?>
