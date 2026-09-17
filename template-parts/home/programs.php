<?php
/**
 * EduTurn — Home 07: Academic programs.
 */
$programs = array(
	array( 'p1', 'প্রাথমিক বিভাগ', 'প্লে গ্রুপ – ৫ম শ্রেণি', '01', array( 'খেলার ছলে শেখার আনন্দময় পদ্ধতি', 'বাংলা, ইংরেজি, গণিত ও নৈতিক শিক্ষা', 'ছবি আঁকা, সংগীত ও শরীরচর্চা' ), uturn_url( 'academic' ) . '#classes', '' ),
	array( 'p2', 'মাধ্যমিক বিভাগ', '৬ষ্ঠ – ১০ম শ্রেণি', '02', array( 'বিজ্ঞান, মানবিক ও ব্যবসায় শিক্ষা শাখা', 'ল্যাবভিত্তিক হাতে-কলমে শিক্ষা', 'এসএসসি প্রস্তুতিতে বিশেষ মডেল টেস্ট' ), uturn_url( 'academic' ) . '#classes', 'd1' ),
	array( 'p3', 'সহশিক্ষা কার্যক্রম', 'সকল শ্রেণি', '03', array( 'বিতর্ক, আবৃত্তি ও সাংস্কৃতিক ক্লাব', 'ক্রীড়া, স্কাউট ও বিজ্ঞান ক্লাব', 'প্রোগ্রামিং ও রোবটিক্স ক্লাব' ), uturn_url( 'students' ), 'd2' ),
);
?>
<section class="section" aria-labelledby="prog-h">
  <div class="container">
    <div class="sec-head-split reveal">
      <div class="sec-head"><span class="eyebrow">একাডেমিক প্রোগ্রাম</span>
        <h2 id="prog-h">প্রতিটি স্তরে যত্নশীল শিক্ষা</h2></div>
      <a class="btn btn-outline" href="<?php echo esc_url( uturn_url( 'academic' ) ); ?>">একাডেমিক তথ্য</a>
    </div>
    <div class="programs-grid">
      <?php foreach ( $programs as $p ) : ?>
      <article class="program reveal <?php echo esc_attr( $p[6] ); ?>">
        <div class="program-top <?php echo esc_attr( $p[0] ); ?>"><h3><?php echo esc_html( $p[1] ); ?></h3><span class="classes"><?php echo esc_html( $p[2] ); ?></span><span class="big"><?php echo esc_html( $p[3] ); ?></span></div>
        <div class="program-body"><ul><?php foreach ( $p[4] as $li ) : ?><li><?php echo esc_html( $li ); ?></li><?php endforeach; ?></ul><a class="btn btn-outline btn-sm" href="<?php echo esc_url( $p[5] ); ?>">বিস্তারিত</a></div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
