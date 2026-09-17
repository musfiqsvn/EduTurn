<?php
/**
 * EduTurn — Shared social icon row (inline SVG, no emoji).
 */
defined( 'ABSPATH' ) || exit;
$links = array(
	'social_fb' => array( 'Facebook', 'M13 22v-8h3l.5-4H13V7.5c0-1 .3-1.5 1.6-1.5H17V2.2C16.4 2.1 15.3 2 14.1 2 11.1 2 9 3.7 9 6.9V10H6v4h3v8h4z' ),
	'social_yt' => array( 'YouTube', 'M23 7.2s-.2-1.6-.9-2.3c-.9-.9-1.9-.9-2.4-1C16.6 3.6 12 3.6 12 3.6s-4.6 0-7.7.3c-.5.1-1.5.1-2.4 1-.7.7-.9 2.3-.9 2.3S.8 9.1.8 11v1.8c0 1.9.2 3.8.2 3.8s.2 1.6.9 2.3c.9.9 2 .9 2.6 1 1.9.2 7.5.3 7.5.3s4.6 0 7.7-.4c.5-.1 1.5-.1 2.4-1 .7-.7.9-2.3.9-2.3s.2-1.9.2-3.8V11c0-1.9-.2-3.8-.2-3.8zM9.9 15V8.4l6.2 3.3L9.9 15z' ),
	'social_x'  => array( 'X', 'M18.9 2H22l-7 8L23.3 22h-6.5l-5.1-6.1L6 22H2.9l7.5-8.6L1 2h6.7l4.6 5.6L18.9 2zm-1.1 18h1.7L7.6 3.9H5.8L17.8 20z' ),
	'social_wa' => array( 'WhatsApp', 'M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm5.4 14.1c-.2.7-1.3 1.3-1.8 1.4-.5 0-1 .2-3.4-.7-2.9-1.2-4.7-4.1-4.9-4.3-.1-.2-1.1-1.5-1.1-2.9s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5s.8 1.9.8 2c.1.1.1.3 0 .5l-.4.5c-.2.2-.3.4-.1.7.2.3.9 1.5 2 2.4 1.4 1.2 2.5 1.6 2.9 1.8.3.2.5.1.7-.1l1-1.2c.2-.3.4-.2.7-.1l2 1c.3.1.5.2.6.4 0 .1 0 .5-.3 1.1z' ),
);
echo '<div class="socials">';
foreach ( $links as $key => $s ) {
	$url = uturn_opt( $key );
	if ( ! $url ) {
		continue;
	}
	echo '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener" aria-label="' . esc_attr( $s[0] ) . '" title="' . esc_attr( $s[0] ) . '"><svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="' . esc_attr( $s[1] ) . '"/></svg></a>';
}
echo '</div>';
