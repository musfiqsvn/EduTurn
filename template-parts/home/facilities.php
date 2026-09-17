<?php
/**
 * EduTurn — Home 10: Campus facilities.
 */
$img = function ( $f ) { return UTURN_URI . '/assets/images/' . $f; };
$cards = array(
	array( $img( 'about-main.jpg' ), 'স্মার্ট ক্লাসরুম', 'Smart Classroom', 'স্মার্ট ক্লাসরুম', 'ইন্টার‌্যাক্টিভ ডিসপ্লেসহ ২০টি ডিজিটাল শ্রেণিকক্ষ', '', '' ),
	array( $img( 'facility-library.jpg' ), 'কেন্দ্রীয় লাইব্রেরি', 'Library', 'সমৃদ্ধ লাইব্রেরি', '১২,০০০+ বই ও ডিজিটাল রিসোর্স কর্নার', 'd1', '' ),
	array( $img( 'facility-science.jpg' ), 'বিজ্ঞান ল্যাব', 'Science Lab', 'বিজ্ঞান ল্যাব', 'পদার্থ, রসায়ন ও জীববিজ্ঞান ল্যাব', 'd2', '' ),
	array( $img( 'facility-computer.jpg' ), 'কম্পিউটার ল্যাব', 'Computer Lab', 'কম্পিউটার ল্যাব', '৬০টি কম্পিউটার ও প্রোগ্রামিং ক্লাব', 'd3', '' ),
	array( $img( 'facility-playground.jpg' ), 'খেলার মাঠ', 'Playground', 'বিশাল খেলার মাঠ', 'ফুটবল, ক্রিকেট ও অ্যাথলেটিক্স সুবিধা', '', '' ),
	array( $img( 'event-culture.jpg' ), 'অডিটোরিয়াম', 'Auditorium', 'অডিটোরিয়াম', '৫০০ আসনের আধুনিক মিলনায়তন', 'd1', '' ),
	array( '', 'নামাজ কক্ষ', 'Prayer Room', 'নামাজ কক্ষ', 'ছেলে ও মেয়েদের জন্য পৃথক সুবিধা', 'd2', 'background:#072C56' ),
	array( '', 'ক্যাফেটেরিয়া', 'Cafeteria', 'ক্যাফেটেরিয়া', 'স্বাস্থ্যকর ও পুষ্টিকর খাবার', 'd3', 'background:#14324A' ),
);
?>
<section class="section section-soft" aria-labelledby="fac-h">
  <div class="container">
    <div class="sec-head center reveal">
      <span class="eyebrow">ক্যাম্পাস সুবিধা</span>
      <h2 id="fac-h">আধুনিক ক্যাম্পাস, সমৃদ্ধ সুবিধা</h2>
      <p class="lead">শিক্ষা, গবেষণা, খেলাধুলা ও আধ্যাত্মিক বিকাশ—সবকিছুর জন্য পরিকল্পিত ক্যাম্পাস।</p>
    </div>
    <div class="fac-grid">
      <?php foreach ( $cards as $c ) : ?>
      <article class="fac-card reveal <?php echo esc_attr( $c[5] ); ?>"<?php echo $c[6] ? ' style="' . esc_attr( $c[6] ) . '"' : ''; ?>><?php if ( $c[0] ) : ?><img src="<?php echo esc_url( $c[0] ); ?>" alt="<?php echo esc_attr( $c[1] ); ?>" loading="lazy"><div class="fac-body"><?php else : ?><div class="fac-body" style="padding-top:3rem"><?php endif; ?><span class="fac-bn"><?php echo esc_html( $c[2] ); ?></span><h3><?php echo esc_html( $c[3] ); ?></h3><p><?php echo esc_html( $c[4] ); ?></p></div></article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
