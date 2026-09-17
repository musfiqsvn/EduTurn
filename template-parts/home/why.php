<?php
/**
 * EduTurn — Home 06: Why-us features.
 */
$feats = array(
	array( 'cap', 'মানসম্মত শিক্ষা', 'জাতীয় কারিকুলামের পাশাপাশি দক্ষতাভিত্তিক আধুনিক পাঠদান পদ্ধতি।' ),
	array( 'users', 'অভিজ্ঞ শিক্ষক', 'প্রশিক্ষিত ও যত্নশীল ৮০+ শিক্ষক ও কর্মীর নিবেদিত টিম।' ),
	array( 'monitor', 'আধুনিক শ্রেণিকক্ষ', 'ইন্টার‌্যাক্টিভ ডিসপ্লে ও মাল্টিমিডিয়াসহ স্মার্ট ক্লাসরুম।' ),
	array( 'bulb', 'ডিজিটাল শিক্ষা', 'অনলাইন ফলাফল, ডিজিটাল হাজিরা ও ই-লার্নিং সাপোর্ট।' ),
	array( 'trophy', 'সহশিক্ষা কার্যক্রম', 'ক্রীড়া, বিতর্ক, সাংস্কৃতিক ও বিজ্ঞান ক্লাবের সমৃদ্ধ ভাণ্ডার।' ),
	array( 'shield', 'নিরাপদ পরিবেশ', 'সিসিটিভি নজরদারি, প্রশিক্ষিত নিরাপত্তাকর্মী ও স্বাস্থ্যসম্মত ক্যাম্পাস।' ),
);
?>
<section class="section section-soft" aria-labelledby="why-h">
  <div class="container">
    <div class="sec-head center reveal">
      <span class="eyebrow">কেন আমরাই সেরা</span>
      <h2 id="why-h">কেন বেছে নেবেন <?php echo esc_html( uturn_opt( 'school_name_bn' ) ); ?></h2>
      <p class="lead">আপনার সন্তানের সার্বিক বিকাশে আমরা দিচ্ছি আধুনিক শিক্ষার সব সুবিধা—এক ছাদের নিচে।</p>
    </div>
    <div class="features-grid" id="why-grid">
      <?php $i = 0; foreach ( $feats as $f ) : ?>
      <article class="feature reveal d<?php echo (int) ( $i % 4 ); ?>"><span class="fnum">0<?php echo (int) ( $i + 1 ); ?></span><span class="ficon"><?php echo uturn_icon( $f[0] ); ?></span><h3><?php echo esc_html( $f[1] ); ?></h3><p><?php echo esc_html( $f[2] ); ?></p></article>
      <?php $i++; endforeach; ?>
    </div>
  </div>
</section>
