<?php
/**
 * Template Name: Contact (যোগাযোগ)
 */
defined( 'ABSPATH' ) || exit;
get_header();
$embed = uturn_opt( 'map_embed' );
?>

<main id="main"><div class="container">
  <nav class="breadcrumb" aria-label="<?php echo esc_attr( uturn_t( 'ব্রেডক্রাম্ব', 'Breadcrumb' ) ); ?>"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( uturn_t( 'হোম', 'Home' ) ); ?></a><span>›</span><span><?php echo esc_html( uturn_t( 'যোগাযোগ', 'Contact' ) ); ?></span></nav>
  <div class="page-head">
    <h1><?php echo esc_html( uturn_t( 'যোগাযোগ করুন', 'Contact Us' ) ); ?></h1>
    <p class="muted"><?php echo esc_html( uturn_t( 'যেকোনো তথ্যের জন্য ফোন, ইমেইল বা সরাসরি অফিসে আসুন', 'Call, email or visit the office directly for any information' ) ); ?></p>
  </div>

  <div class="contact-grid">
    <div class="card contact-info">
      <h2>📍 <?php echo esc_html( uturn_t( 'বিদ্যালয়ের ঠিকানা', 'School Address' ) ); ?></h2>
      <p><strong><?php echo esc_html( uturn_lang() === 'en' ? uturn_opt( 'school_name_en' ) : uturn_opt( 'school_name_bn' ) ); ?></strong></p>
      <p class="muted"><?php echo esc_html( uturn_opt( 'address' ) ); ?><br><?php echo esc_html( uturn_opt( 'address_en' ) ); ?></p>
      <p>📞 <a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', uturn_opt( 'phone' ) ) ); ?>"><?php echo esc_html( uturn_opt( 'phone' ) ); ?></a></p>
      <p>✉️ <a href="mailto:<?php echo esc_attr( uturn_opt( 'email' ) ); ?>"><?php echo esc_html( uturn_opt( 'email' ) ); ?></a></p>
      <p class="muted">🕒 <?php echo esc_html( uturn_opt( 'hours' ) ); ?></p>
      <?php get_template_part( 'template-parts/chrome/socials' ); ?>
      <p style="margin-top:12px"><a class="btn btn-outline" href="https://www.google.com/maps/search/?api=1&query=<?php echo rawurlencode( uturn_opt( 'address_en' ) ); ?>" target="_blank" rel="noopener">🗺️ <?php echo esc_html( uturn_t( 'দিকনির্দেশনা নিন', 'Get Directions' ) ); ?></a></p>
    </div>
    <div class="card">
      <h2>✍️ <?php echo esc_html( uturn_t( 'বার্তা পাঠান', 'Send a Message' ) ); ?></h2>
      <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="contact-form">
        <input type="hidden" name="action" value="uturn_contact">
        <?php wp_nonce_field( 'uturn_contact', 'uturn_contact_nonce' ); ?>
        <input type="text" name="c_web" value="" style="position:absolute;left:-9999px" tabindex="-1" autocomplete="off" aria-hidden="true">
        <div class="form-grid">
          <div class="field"><label for="c-name"><?php echo esc_html( uturn_t( 'আপনার নাম *', 'Your Name *' ) ); ?></label><input id="c-name" name="name" required minlength="3"></div>
          <div class="field"><label for="c-phone"><?php echo esc_html( uturn_t( 'মোবাইল *', 'Mobile *' ) ); ?></label><input id="c-phone" name="phone" required inputmode="tel" placeholder="01XXXXXXXXX"></div>
        </div>
        <div class="field"><label for="c-email"><?php echo esc_html( uturn_t( 'ইমেইল', 'Email' ) ); ?></label><input id="c-email" name="email" type="email"></div>
        <div class="field"><label for="c-subject"><?php echo esc_html( uturn_t( 'বিষয়', 'Subject' ) ); ?></label><input id="c-subject" name="subject" placeholder="<?php echo esc_attr( uturn_t( 'যেমন: ভর্তি সংক্রান্ত', 'e.g. About admission' ) ); ?>"></div>
        <div class="field"><label for="c-msg"><?php echo esc_html( uturn_t( 'বার্তা *', 'Message *' ) ); ?></label><textarea id="c-msg" name="message" rows="4" required minlength="5"></textarea></div>
        <button class="btn btn-block" type="submit">📩 <?php echo esc_html( uturn_t( 'বার্তা পাঠান', 'Send Message' ) ); ?></button>
      </form>
    </div>
  </div>

  <div class="map-wrap">
    <?php if ( $embed ) : ?>
      <?php echo $embed; ?>
    <?php else : ?>
      <iframe title="<?php echo esc_attr( uturn_t( 'গুগল ম্যাপ', 'Google Map' ) ); ?>" src="https://maps.google.com/maps?q=Dhanmondi,Dhaka,Bangladesh&t=&z=14&ie=UTF8&iwloc=&output=embed" loading="lazy" style="border:0;width:100%;height:380px"></iframe>
    <?php endif; ?>
  </div>
</div></main>
<?php get_footer(); ?>
