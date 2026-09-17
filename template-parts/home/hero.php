<?php
/**
 * EduTurn — Home 01: Hero (dashboard-driven).
 */
$bg_id = (int) uturn_opt( 'hero_bg_id', 0 );
$bg    = $bg_id ? wp_get_attachment_image_url( $bg_id, 'ut-hero' ) : UTURN_URI . '/assets/images/hero-campus.jpg';
$meta  = array();
foreach ( preg_split( '/\r?\n/', (string) uturn_opt( 'hero_meta', '' ) ) as $line ) {
	$line = trim( $line );
	if ( '' === $line ) {
		continue;
	}
	$parts  = explode( '|', $line, 2 );
	$meta[] = array( trim( $parts[0] ), isset( $parts[1] ) ? trim( $parts[1] ) : '' );
}
?>
<section class="hero" aria-label="<?php echo esc_attr( uturn_t( 'স্বাগতম', 'Welcome' ) ); ?>">
  <div class="hero-bg"><img src="<?php echo esc_url( $bg ); ?>" alt="<?php echo esc_attr( uturn_opt( 'school_name_bn' ) ); ?> ক্যাম্পাস" fetchpriority="high"></div>
  <span class="hero-orb o1" aria-hidden="true"></span><span class="hero-orb o2" aria-hidden="true"></span>
  <div class="container">
    <div class="hero-inner">
      <span class="eyebrow"><?php echo esc_html( uturn_opt( 'hero_eyebrow' ) ); ?></span>
      <h1><?php echo esc_html( uturn_opt( 'hero_title' ) ); ?></h1>
      <p class="lead"><?php echo esc_html( uturn_opt( 'hero_lead' ) ); ?></p>
      <div class="hero-actions">
        <a class="btn btn-accent" href="<?php echo esc_url( uturn_url( 'apply' ) ); ?>"><?php echo esc_html( uturn_t( 'অনলাইনে ভর্তি', 'Apply Online' ) ); ?>
          <?php echo uturn_icon( 'arrow' ); ?></a>
        <a class="btn btn-outline-light" href="<?php echo esc_url( uturn_url( 'about' ) ); ?>"><?php echo esc_html( uturn_t( 'আমাদের সম্পর্কে', 'About Us' ) ); ?></a>
      </div>
      <div class="hero-meta">
        <?php foreach ( array_slice( $meta, 0, 3 ) as $m ) : ?>
        <div><strong><?php echo esc_html( $m[0] ); ?></strong><?php echo esc_html( $m[1] ); ?></div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
