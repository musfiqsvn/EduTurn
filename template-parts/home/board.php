<?php
/**
 * EduTurn — Home: পরিচালনা পর্ষদ (Governing Body).
 */
$members = function_exists( 'uturn_board_members' ) ? uturn_board_members( true ) : array();
if ( ! $members ) {
	return;
}
?>
<section class="section" id="board-section" aria-labelledby="board-h">
  <div class="container">
    <div class="sec-head reveal"><span class="eyebrow"><?php echo esc_html( uturn_t( 'পরিচালনা পর্ষদ', 'Governing Body' ) ); ?></span>
      <h2 id="board-h"><?php echo esc_html( uturn_t( 'যাঁদের নেতৃত্বে আমরা', 'Those Who Lead Us' ) ); ?></h2></div>
    <div class="board-grid">
      <?php foreach ( $members as $i => $m ) { echo uturn_board_card_html( $m, $i ); // phpcs:ignore ?>
      <?php } ?>
    </div>
  </div>
</section>
