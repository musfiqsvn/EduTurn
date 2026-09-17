<?php
/**
 * EduTurn — Auto-scrolling notice ticker (server-rendered, seamless loop).
 */
$notices_url = get_post_type_archive_link( 'ut_notice' ) ? get_post_type_archive_link( 'ut_notice' ) : home_url( '/notices/' );
$q = new WP_Query(
	array( 'post_type' => 'ut_notice', 'posts_per_page' => max( 1, (int) uturn_opt( 'ticker_count', 8 ) ), 'post_status' => 'publish', 'no_found_rows' => true )
);
if ( ! $q->have_posts() ) {
	return;
}
$half = '';
while ( $q->have_posts() ) {
	$q->the_post();
	$date = get_post_meta( get_the_ID(), '_ut_date_bn', true );
	if ( ! $date ) {
		$date = uturn_bn_date( get_the_date( 'Y-m-d' ) );
	}
	$half .= '<a class="ticker-item" href="' . esc_url( get_permalink() ) . '"><span class="tk-date">' . esc_html( $date ) . '</span><span>' . esc_html( get_the_title() ) . '</span></a>';
}
wp_reset_postdata();
$dup = str_replace( '<a ', '<a tabindex="-1" ', $half );
?><div class="notice-ticker" role="region" aria-label="<?php echo esc_attr( uturn_t( 'সাম্প্রতিক নোটিশ', 'Latest notices' ) ); ?>"><div class="container ticker-inner"><a class="ticker-label" href="<?php echo esc_url( $notices_url ); ?>"><?php echo uturn_icon( 'bellfill' ); ?><span><?php echo esc_html( uturn_t( 'নোটিশ', 'Notice' ) ); ?></span></a><div class="ticker-view"><div class="ticker-track"><?php echo $half; // phpcs:ignore ?><?php echo $dup; // phpcs:ignore ?></div></div></div></div>
