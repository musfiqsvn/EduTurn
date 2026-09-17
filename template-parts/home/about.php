<?php
/**
 * EduTurn — Home 03: About snapshot (years computed from established option).
 */
$name  = uturn_opt( 'school_name_bn' );
$est   = (int) uturn_opt( 'established', '2001' );
$years = max( 1, (int) date_i18n( 'Y' ) - $est );
$items = array(
	array( 'প্রতিষ্ঠানের ইতিহাস:', uturn_bn( $est ) . ' সালে ১২০ জন শিক্ষার্থী নিয়ে যাত্রা শুরু, আজ ১৫০০+ শিক্ষার্থীর পরিবার।' ),
	array( 'শিক্ষা দর্শন:', 'আনন্দময় শিক্ষা, নৈতিকতা ও সৃজনশীলতার সমন্বিত বিকাশ।' ),
	array( 'লক্ষ্য:', 'প্রতিটি শিক্ষার্থীকে আত্মবিশ্বাসী, দক্ষ ও দায়িত্বশীল নাগরিক হিসেবে গড়ে তোলা।' ),
	array( 'বিশেষত্ব:', 'স্মার্ট ক্লাসরুম, ডিজিটাল ফলাফল ব্যবস্থা ও শক্তিশালী সহশিক্ষা কার্যক্রম।' ),
);
?>
<section class="section" aria-labelledby="about-h">
  <div class="container about-grid">
    <div class="about-media reveal">
      <div class="about-badge"><strong><?php echo esc_html( uturn_bn( $years ) ); ?>+</strong>বছরের<br>ঐতিহ্য</div>
      <div class="main-img"><img src="<?php echo esc_url( UTURN_URI . '/assets/images/about-main.jpg' ); ?>" alt="শ্রেণিকক্ষে পাঠদানরত শিক্ষক ও শিক্ষার্থীরা" loading="lazy"></div>
      <div class="float-img"><img src="<?php echo esc_url( UTURN_URI . '/assets/images/about-small.jpg' ); ?>" alt="লাইব্রেরিতে বই পড়ছে শিক্ষার্থীরা" loading="lazy"></div>
    </div>
    <div class="reveal d1">
      <span class="eyebrow">আমাদের কথা</span>
      <h2 id="about-h">এক নজরে আমাদের প্রতিষ্ঠান</h2>
      <p class="muted"><?php echo esc_html( uturn_bn( $est ) ); ?> সালে প্রতিষ্ঠার পর থেকে <?php echo esc_html( $name ); ?> মানসম্মত শিক্ষা ও চরিত্র গঠনে নিরলসভাবে কাজ করে আসছে। আমাদের শিক্ষা দর্শনের মূলে রয়েছে—প্রতিটি শিশুই সম্ভাবনাময়, প্রয়োজন শুধু সঠিক দিকনির্দেশনা ও যত্ন।</p>
      <ul class="about-list">
        <?php foreach ( $items as $li ) : ?>
        <li><span class="tick"><?php echo uturn_icon( 'tick' ); ?></span><span><strong><?php echo esc_html( $li[0] ); ?></strong> <?php echo esc_html( $li[1] ); ?></span></li>
        <?php endforeach; ?>
      </ul>
      <a class="btn btn-primary mt-1" href="<?php echo esc_url( uturn_url( 'about' ) ); ?>">আরও জানুন</a>
    </div>
  </div>
</section>
