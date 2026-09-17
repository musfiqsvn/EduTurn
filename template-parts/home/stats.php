<?php
/**
 * EduTurn — Home 04: Stats band (animated counters via data-count).
 */
$stats = array();
foreach ( preg_split( '/\r?\n/', (string) uturn_opt( 'stats_lines', '' ) ) as $line ) {
	$line = trim( $line );
	if ( '' === $line ) {
		continue;
	}
	$p = explode( '|', $line );
	$stats[] = array( 'v' => trim( $p[0] ), 's' => isset( $p[1] ) ? trim( $p[1] ) : '', 'l' => isset( $p[2] ) ? trim( $p[2] ) : '' );
}
if ( ! $stats ) {
	return;
}
?>
<section class="section stats-band" aria-label="প্রতিষ্ঠানের পরিসংখ্যান">
  <div class="container stats-grid" id="stats-grid"><?php $i = 0; foreach ( $stats as $s ) : ?><div class="stat reveal d<?php echo (int) ( $i % 4 ); ?>"><div class="stat-num"><span data-count="<?php echo esc_attr( $s['v'] ); ?>">০</span><span class="suffix"><?php echo esc_html( $s['s'] ); ?></span></div><div class="stat-label"><?php echo esc_html( $s['l'] ); ?></div></div><?php $i++; endforeach; ?></div>
</section>
