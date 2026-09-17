<?php
/**
 * EduTurn — Home 05: Principal's message.
 */
$ph = (string) uturn_opt( 'principal_photo', '' );
if ( is_numeric( $ph ) && $ph > 0 ) {
	$ph = wp_get_attachment_image_url( (int) $ph, 'ut-person' );
} elseif ( 0 === strpos( $ph, 'assets/' ) ) {
	$ph = UTURN_URI . '/' . $ph;
}
if ( ! $ph ) {
	$ph = UTURN_URI . '/assets/images/principal.jpg';
}
$name = uturn_opt( 'principal_name' );
?>
<section class="section" aria-labelledby="principal-h">
  <div class="container principal-grid">
    <div class="principal-photo reveal">
      <img src="<?php echo esc_url( $ph ); ?>" alt="প্রধান শিক্ষক <?php echo esc_attr( $name ); ?>" loading="lazy">
      <div class="principal-card"><strong><?php echo esc_html( $name ); ?></strong><span><?php echo esc_html( uturn_opt( 'principal_title' ) ); ?>, <?php echo esc_html( uturn_opt( 'school_name_bn' ) ); ?></span></div>
    </div>
    <div class="reveal d1">
      <span class="eyebrow">প্রধান শিক্ষকের বার্তা</span>
      <h2 id="principal-h">আলোকিত মানুষ গড়াই আমাদের অঙ্গীকার</h2>
      <span class="quote-mark" aria-hidden="true">“</span>
      <p class="principal-msg" id="principal-short"><?php echo esc_html( uturn_opt( 'principal_msg' ) ); ?></p>
      <div class="principal-sign"><?php echo esc_html( $name ); ?><span><?php echo esc_html( uturn_opt( 'principal_title' ) ); ?></span></div>
      <a class="btn btn-outline mt-1" href="<?php echo esc_url( uturn_url( 'about' ) ); ?>#principal">সম্পূর্ণ বার্তা পড়ুন</a>
    </div>
  </div>
</section>
