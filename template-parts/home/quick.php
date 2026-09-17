<?php
/**
 * EduTurn — Home 02: Quick access grid.
 */
$notices_url = get_post_type_archive_link( 'ut_notice' ) ? get_post_type_archive_link( 'ut_notice' ) : home_url( '/notices/' );
$items = array(
	array( 'cap', 'ভর্তি আবেদন', uturn_url( 'apply' ) ),
	array( 'result', 'ফলাফল', uturn_url( 'results' ) ),
	array( 'routine', 'ক্লাস রুটিন', uturn_url( 'routine' ) ),
	array( 'file', 'পরীক্ষার রুটিন', uturn_url( 'routine' ) . '#exam' ),
	array( 'bell', 'নোটিশ', $notices_url ),
	array( 'cal', 'একাডেমিক ক্যালেন্ডার', uturn_url( 'academic' ) . '#calendar' ),
	array( 'download', 'ডাউনলোড', uturn_url( 'downloads' ) ),
	array( 'phone', 'যোগাযোগ', uturn_url( 'contact' ) ),
);
?>
<section class="quick" aria-label="<?php echo esc_attr( uturn_t( 'দ্রুত সেবা', 'Quick services' ) ); ?>">
  <div class="container">
    <div class="quick-grid reveal">
      <?php foreach ( $items as $it ) : ?>
      <a class="quick-item" href="<?php echo esc_url( $it[2] ); ?>"><span class="qicon"><?php echo uturn_icon( $it[0] ); ?></span><?php echo esc_html( $it[1] ); ?></a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
