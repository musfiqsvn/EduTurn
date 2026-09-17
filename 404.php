<?php
/**
 * EduTurn — 404.
 */
get_header();
?>
<main id="main">
<section class="section"><div class="container">
<div class="sec-head center">
<span class="eyebrow"><?php echo esc_html( 'en' === uturn_lang() ? '404' : '৪০৪' ); ?></span>
<h1><?php echo esc_html( uturn_t( 'পেজটি খুঁজে পাওয়া যায়নি', 'Page not found' ) ); ?></h1>
<p class="lead"><?php echo esc_html( uturn_t( 'লিংকটি ভুল হতে পারে অথবা পেজটি সরিয়ে ফেলা হয়েছে।', 'The link may be wrong or the page has been removed.' ) ); ?></p>
<p><a class="btn btn-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( uturn_t( 'হোমে ফিরে যান', 'Back to Home' ) ); ?></a> <a class="btn btn-outline" href="<?php echo esc_url( uturn_url( 'contact' ) ); ?>"><?php echo esc_html( uturn_t( 'যোগাযোগ করুন', 'Contact Us' ) ); ?></a></p>
</div>
</div></section>
</main>
<?php
get_footer();
