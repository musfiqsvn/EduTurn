<?php
/**
 * EduTurn — Home 09: Admission banner + live countdown (fed by SITE_DATA).
 */
?>
<section class="section" aria-labelledby="adm-h">
  <div class="container">
    <div class="admission-banner reveal">
      <div class="bg"><img src="<?php echo esc_url( UTURN_URI . '/assets/images/hero-campus.jpg' ); ?>" alt="" loading="lazy" aria-hidden="true"></div>
      <div class="admission-inner">
        <div>
          <span class="eyebrow" style="color:#8FBDF0"><?php echo esc_html( uturn_opt( 'adm_session', '২০২৭ শিক্ষাবর্ষ' ) ); ?></span>
          <h2 id="adm-h">ভর্তি চলছে</h2>
          <p>আপনার সন্তানের শিক্ষাজীবনের নতুন অধ্যায় শুরু হোক আমাদের সঙ্গে। প্লে গ্রুপ থেকে নবম শ্রেণি পর্যন্ত সীমিত আসনে অনলাইন আবেদন চলছে।</p>
          <div class="hero-actions">
            <a class="btn btn-accent" href="<?php echo esc_url( uturn_url( 'apply' ) ); ?>">অনলাইনে আবেদন করুন</a>
            <a class="btn btn-outline-light" href="<?php echo esc_url( uturn_url( 'admission' ) ); ?>">ভর্তি নির্দেশিকা</a>
          </div>
        </div>
        <div class="deadline-box" id="countdown">
          <span class="lbl">আবেদনের শেষ তারিখ</span>
          <div style="font-size:1.3rem;font-weight:800" id="deadline-date"><?php echo esc_html( uturn_opt( 'deadline_bn' ) ); ?></div>
          <div class="countdown" aria-label="সময় বাকি">
            <div class="cell"><strong data-cd="d">০</strong><span>দিন</span></div>
            <div class="cell"><strong data-cd="h">০</strong><span>ঘণ্টা</span></div>
            <div class="cell"><strong data-cd="m">০</strong><span>মিনিট</span></div>
            <div class="cell"><strong data-cd="s">০</strong><span>সেকেন্ড</span></div>
          </div>
          <span class="small" style="color:#C4D8EC">আসন সীমিত — আজই আবেদন করুন</span>
        </div>
      </div>
    </div>
  </div>
</section>
